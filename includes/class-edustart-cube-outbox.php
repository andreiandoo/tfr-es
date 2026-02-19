<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Outbox {
    const OUTBOX_TBL = 'edu_integration_outbox';
    const ENT_TBL    = 'edu_integration_entities';
    const PARTNERS_TBL = 'edu_integration_partners';

    public static function enqueue(string $event_type, string $entity_type, string $secure_key, array $payload): bool {
        global $wpdb;
        $tbl = $wpdb->prefix . self::OUTBOX_TBL;
        $partner_id = (int) $wpdb->get_var( $wpdb->prepare("SELECT id FROM {$wpdb->prefix}".self::PARTNERS_TBL." WHERE name=%s LIMIT 1", 'cube') );
        if (!$partner_id) return false;

        // asigurăm în entities că e pending
        self::ensure_entity_status($partner_id, $entity_type, $secure_key, 'pending', $payload);

        return (bool)$wpdb->insert($tbl, [
            'partner_id'     => $partner_id,
            'event_type'     => sanitize_text_field($event_type),
            'entity_type'    => sanitize_text_field($entity_type),
            'secure_key'     => sanitize_text_field($secure_key),
            'payload_json'   => wp_json_encode($payload, JSON_UNESCAPED_UNICODE),
            'idempotency_key'=> wp_generate_uuid4(),
            'attempts'       => 0,
            'next_attempt_at'=> current_time('mysql'),
            'status'         => 'pending',
            'created_at'     => current_time('mysql'),
            'updated_at'     => current_time('mysql')
        ]);
    }

    public static function process_queue() {
        global $wpdb;
        $tbl = $wpdb->prefix . self::OUTBOX_TBL;
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$tbl} WHERE status=%s AND next_attempt_at<=%s ORDER BY id ASC LIMIT 25",
            'pending', current_time('mysql')
        ));
        if (!$rows) return;

        foreach ($rows as $row) {
            $payload = json_decode($row->payload_json, true) ?: [];
            $err = null;
            $ok = self::deliver($row->event_type, $payload, $err);
            if ($ok) {
                $wpdb->update($tbl, [
                    'status' => 'sent',
                    'updated_at' => current_time('mysql')
                ], ['id' => $row->id]);

                self::update_entity_status((int)$row->partner_id, $row->entity_type, $row->secure_key, 'synced');
            } else {
                $attempts = (int)$row->attempts + 1;
                $delay = min( 60*60*4, pow(2, $attempts) * 60 ); // 1m,2m,4m,... max 4h
                $status = 'pending';
                if ($attempts >= 10) {
                    $status = 'error'; // lăsăm scanner-ul/ops să intervină
                    self::update_entity_status((int)$row->partner_id, $row->entity_type, $row->secure_key, 'error', $err);
                }
                $wpdb->update($tbl, [
                    'status' => $status,
                    'attempts' => $attempts,
                    'last_error' => is_array($err)? json_encode($err) : (string)$err,
                    'next_attempt_at' => gmdate('Y-m-d H:i:s', time()+$delay),
                    'updated_at' => current_time('mysql')
                ], ['id' => $row->id]);
            }
        }
    }

    private static function deliver(string $event_type, array $payload, &$err = null): bool {
        switch ($event_type) {
            case 'teacher.upsert':
                $resp = EduStart_Cube_Client::post_json('/api/v1/teachers/upsert', $payload, $err);
                return $resp !== null;
            case 'generation.upsert':
                $resp = EduStart_Cube_Client::post_json('/api/v1/generations/upsert', $payload, $err);
                return $resp !== null;
            case 'student.upsert':
                $resp = EduStart_Cube_Client::post_json('/api/v1/students/upsert', $payload, $err);
                return $resp !== null;
            default:
                $err = ['code'=>'unknown_event','msg'=>$event_type];
                return false;
        }
    }

    private static function ensure_entity_status(int $partner_id, string $type, string $secure_key, string $status, array $payload = []) {
        global $wpdb;
        $tbl = $wpdb->prefix . self::ENT_TBL;
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$tbl} WHERE partner_id=%d AND entity_type=%s AND secure_key=%s LIMIT 1",
            $partner_id, $type, $secure_key
        ));
        if ($exists) {
            $wpdb->update($tbl, [
                'status' => $status,
                'updated_at' => current_time('mysql')
            ], ['id' => $exists]);
        } else {
            // local_id pentru debug (îl rezolvăm din identity_keys)
            $local_id = null;
            $local_id = self::resolve_local_id($type, $secure_key);
            $wpdb->insert($tbl, [
                'partner_id'  => $partner_id,
                'entity_type' => $type,
                'local_id'    => $local_id ?: 0,
                'secure_key'  => $secure_key,
                'status'      => $status,
                'created_at'  => current_time('mysql'),
                'updated_at'  => current_time('mysql')
            ]);
        }
    }

    private static function update_entity_status(int $partner_id, string $type, string $secure_key, string $status, $err = null) {
        global $wpdb;
        $tbl = $wpdb->prefix . self::ENT_TBL;
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$tbl} SET status=%s, last_error=%s, updated_at=%s WHERE partner_id=%d AND entity_type=%s AND secure_key=%s",
            $status,
            is_array($err)? json_encode($err) : (string)$err,
            current_time('mysql'),
            $partner_id, $type, $secure_key
        ));
    }

    private static function resolve_local_id(string $entity_type, string $secure_key): ?int {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_identity_keys';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT local_id FROM {$tbl} WHERE entity_type=%s AND secure_key=%s LIMIT 1",
            $entity_type, $secure_key
        ));
        return $row ? (int)$row->local_id : null;
    }
}
