<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Scanner {
    public static function run() {
        self::scan_teachers();
        self::scan_generations();
        self::scan_students();
    }

    private static function partner_id(): int {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_integration_partners';
        $id = (int) $wpdb->get_var( $wpdb->prepare("SELECT id FROM {$tbl} WHERE name=%s LIMIT 1", 'cube') );
        return $id ?: 0;
    }

    private static function scan_teachers() {
        $pid = self::partner_id(); if (!$pid) return;

        // toți userii cu rol profesor
        $users = get_users(['role'=>'profesor','fields'=>['ID'],'number'=>-1]);
        foreach ($users as $u) {
            $sec = EduStart_Cube_Identity::ensure_secure_key('teacher', (int)$u->ID);
            if (!self::needs_sync('teacher', $sec, $pid)) continue;

            $payload = EduStart_Cube_Hooks::map_teacher_payload((int)$u->ID);
            if ($payload) {
                EduStart_Cube_Outbox::enqueue('teacher.upsert', 'teacher', $payload['teacher_id'], $payload);
                self::mark_pending('teacher', $sec, $pid, (int)$u->ID);
            }
        }
    }

    private static function scan_generations() {
        global $wpdb;
        $pid = self::partner_id(); if (!$pid) return;

        $rows = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}edu_generations");
        foreach ($rows as $gid) {
            $sec = EduStart_Cube_Identity::ensure_secure_key('generation', (int)$gid);
            if (!self::needs_sync('generation', $sec, $pid)) continue;

            $payload = EduStart_Cube_Hooks::map_generation_payload((int)$gid);
            if ($payload) {
                EduStart_Cube_Outbox::enqueue('generation.upsert', 'generation', $payload['generation_id'], $payload);
                self::mark_pending('generation', $sec, $pid, (int)$gid);
            }
        }
    }

    private static function scan_students() {
        global $wpdb;
        $pid = self::partner_id(); if (!$pid) return;

        $rows = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}edu_students");
        foreach ($rows as $sid) {
            $sec = EduStart_Cube_Identity::ensure_secure_key('student', (int)$sid);
            if (!self::needs_sync('student', $sec, $pid)) continue;

            $payload = EduStart_Cube_Hooks::map_student_payload((int)$sid);
            if ($payload) {
                EduStart_Cube_Outbox::enqueue('student.upsert', 'student', $payload['student_id'], $payload);
                self::mark_pending('student', $sec, $pid, (int)$sid);
            }
        }
    }

    private static function needs_sync(string $type, string $secure_key, int $partner_id): bool {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_integration_entities';
        $status = $wpdb->get_var( $wpdb->prepare(
            "SELECT status FROM {$tbl} WHERE partner_id=%d AND entity_type=%s AND secure_key=%s LIMIT 1",
            $partner_id, $type, $secure_key
        ));
        // dacă nu există sau e 'error' => încercăm
        return !$status || $status === 'error';
    }

    private static function mark_pending(string $type, string $secure_key, int $partner_id, int $local_id) {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_integration_entities';
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$tbl} WHERE partner_id=%d AND entity_type=%s AND secure_key=%s LIMIT 1",
            $partner_id, $type, $secure_key
        ));
        if ($exists) {
            $wpdb->update($tbl, [
                'status' => 'pending',
                'updated_at' => current_time('mysql')
            ], ['id' => $exists]);
        } else {
            $wpdb->insert($tbl, [
                'partner_id'  => $partner_id,
                'entity_type' => $type,
                'local_id'    => $local_id,
                'secure_key'  => $secure_key,
                'status'      => 'pending',
                'created_at'  => current_time('mysql'),
                'updated_at'  => current_time('mysql')
            ]);
        }
    }
}
