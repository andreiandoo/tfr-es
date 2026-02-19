<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Identity {
    /**
     * Generează ULID (26 chars, Crockford base32, time-ordered).
     */
    public static function ulid(): string {
        // Simplu ULID generator potrivit pentru PHP 7/8
        $time = microtime(true);
        $time_ms = (int) round($time * 1000);
        $time_str = self::encodeTime($time_ms, 10);
        $rand_str = self::encodeRandom(16);
        return $time_str . $rand_str;
    }
    private static function encodeTime($time_ms, $len) {
        $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $str = '';
        while ($len-- > 0) {
            $str = $alphabet[$time_ms % 32] . $str;
            $time_ms = (int) floor($time_ms / 32);
        }
        return $str;
    }
    private static function encodeRandom($len) {
        $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $bytes = random_bytes($len);
        $str = '';
        for ($i=0; $i<$len; $i++) {
            $str .= $alphabet[ord($bytes[$i]) % 32];
        }
        return $str;
    }

    /**
     * Asigură existența unei chei securizate pentru (teacher|student|generation, local_id).
     * Returnează secure_key.
     */
    public static function ensure_secure_key(string $entity_type, int $local_id): string {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_identity_keys';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT secure_key FROM {$tbl} WHERE entity_type=%s AND local_id=%d LIMIT 1",
            $entity_type, $local_id
        ));
        if ($row && !empty($row->secure_key)) {
            return $row->secure_key;
        }
        $ulid = self::ulid();
        $wpdb->insert($tbl, [
            'entity_type' => $entity_type,
            'local_id'    => $local_id,
            'secure_key'  => $ulid,
            'created_at'  => current_time('mysql')
        ]);
        return $ulid;
    }

    /**
     * Backfill: creează chei pentru toate entitățile existente.
     * Returnează un sumar cu numărul de chei noi per tip.
     */
    public static function backfill_all(): array {
        $out = [
            'teacher_created'    => 0,
            'student_created'    => 0,
            'generation_created' => 0
        ];
        // TEACHERS (role: profesor)
        $args = [
            'role'    => 'profesor',
            'fields'  => ['ID'],
            'number'  => -1
        ];
        $users = get_users($args);
        foreach ($users as $u) {
            $before = self::get_secure_key('teacher', (int)$u->ID);
            if (!$before) {
                self::ensure_secure_key('teacher', (int)$u->ID);
                $out['teacher_created']++;
            }
        }

        // GENERATIONS
        global $wpdb;
        $gens = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}edu_generations");
        foreach ($gens as $gid) {
            $before = self::get_secure_key('generation', (int)$gid);
            if (!$before) {
                self::ensure_secure_key('generation', (int)$gid);
                $out['generation_created']++;
            }
        }

        // STUDENTS
        $students = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}edu_students");
        foreach ($students as $sid) {
            $before = self::get_secure_key('student', (int)$sid);
            if (!$before) {
                self::ensure_secure_key('student', (int)$sid);
                $out['student_created']++;
            }
        }
        return $out;
    }

    public static function get_secure_key(string $entity_type, int $local_id): ?string {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_identity_keys';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT secure_key FROM {$tbl} WHERE entity_type=%s AND local_id=%d LIMIT 1",
            $entity_type, $local_id
        ));
        return $row ? $row->secure_key : null;
    }

    public static function resolve_local_id(string $entity_type, string $secure_key): ?int {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_identity_keys';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT local_id FROM {$tbl} WHERE entity_type=%s AND secure_key=%s LIMIT 1",
            $entity_type, $secure_key
        ));
        return $row ? (int)$row->local_id : null;
    }
}
