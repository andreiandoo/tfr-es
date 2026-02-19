<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Hooks {
    // === REST ===
    public static function register_routes() {
        register_rest_route('edu/v1', '/enqueue/teacher', [
            'methods'  => 'POST',
            'callback' => [__CLASS__, 'enqueue_teacher'],
            'permission_callback' => [__CLASS__, 'auth_api_key']
        ]);
        register_rest_route('edu/v1', '/enqueue/generation', [
            'methods'  => 'POST',
            'callback' => [__CLASS__, 'enqueue_generation'],
            'permission_callback' => [__CLASS__, 'auth_api_key']
        ]);
        register_rest_route('edu/v1', '/enqueue/student', [
            'methods'  => 'POST',
            'callback' => [__CLASS__, 'enqueue_student'],
            'permission_callback' => [__CLASS__, 'auth_api_key']
        ]);
    }

    public static function auth_api_key($request) {
        $api_key = get_option('edustart_api_key');
        $hdr = isset($_SERVER['HTTP_X_API_KEY']) ? trim(sanitize_text_field($_SERVER['HTTP_X_API_KEY'])) : '';
        if (!$api_key || !$hdr || !hash_equals($api_key, $hdr)) {
            return new WP_Error('forbidden', 'Invalid API key', ['status' => 403]);
        }
        return true;
    }

    // ==== MAPPERS ====

    public static function map_teacher_payload(int $user_id): ?array {
        $u = get_userdata($user_id);
        if (!$u) return null;
        // first/last
        $first = get_user_meta($user_id, 'first_name', true);
        $last  = get_user_meta($user_id, 'last_name', true);
        $email = $u->user_email;
        // school (name/city/county) — folosim prima din assigned_school_ids, dacă există
        $assigned = get_user_meta($user_id, 'assigned_school_ids', true);
        $school_block = ['name'=>'', 'city'=>'', 'county'=>''];
        if (is_array($assigned) && !empty($assigned)) {
            $school_id = (int) $assigned[0];
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT name, city_id FROM {$wpdb->prefix}edu_schools WHERE id=%d", $school_id));
            if ($row) {
                $school_block['name'] = $row->name;
                if (!empty($row->city_id)) {
                    $c = $wpdb->get_row($wpdb->prepare("SELECT name, county_id FROM {$wpdb->prefix}edu_cities WHERE id=%d", $row->city_id));
                    if ($c) {
                        $school_block['city'] = $c->name;
                        $county = $wpdb->get_row($wpdb->prepare("SELECT name FROM {$wpdb->prefix}edu_counties WHERE id=%d", $c->county_id));
                        if ($county) $school_block['county'] = $county->name;
                    }
                }
            }
        }
        $meta = [
            'phone'   => (string)get_user_meta($user_id, 'phone', true),
            'status'  => (string)get_user_meta($user_id, 'user_status_profesor', true),
            'levels'  => (array)get_user_meta($user_id, 'nivel_predare', true),
            'subjects'=> (array)get_user_meta($user_id, 'materia_predata', true),
        ];
        $secure = EduStart_Cube_Identity::ensure_secure_key('teacher', $user_id);
        return [
            'teacher_id' => $secure,
            'email'      => $email,
            'first_name' => $first ?: $u->display_name,
            'last_name'  => $last ?: '',
            'meta'       => $meta,
            'school'     => $school_block
        ];
    }

    public static function map_generation_payload(int $generation_id): ?array {
        global $wpdb;
        $g = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}edu_generations WHERE id=%d", $generation_id));
        if (!$g) return null;
        $secure_gen = EduStart_Cube_Identity::ensure_secure_key('generation', $generation_id);
        $secure_teacher = EduStart_Cube_Identity::ensure_secure_key('teacher', (int)$g->professor_id);
        // class_labels_json este listă JSON în DB
        $labels = json_decode($g->class_labels_json, true);
        if (!is_array($labels)) $labels = [];
        return [
            'generation_id' => $secure_gen,
            'name'          => $g->name,
            'level'         => $g->level,
            'year'          => (int)$g->year,
            'class_label'   => $g->class_label,
            'class_labels'  => $labels,
            'teacher_id'    => $secure_teacher
        ];
    }

    public static function map_student_payload(int $student_id): ?array {
        global $wpdb;
        $s = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}edu_students WHERE id=%d", $student_id));
        if (!$s) return null;
        $secure_student  = EduStart_Cube_Identity::ensure_secure_key('student', $student_id);
        $secure_teacher  = EduStart_Cube_Identity::ensure_secure_key('teacher', (int)$s->professor_id);
        $secure_gen      = EduStart_Cube_Identity::ensure_secure_key('generation', (int)$s->generation_id);

        $meta = [
            'sit_abs'   => $s->sit_abs,
            'frecventa' => $s->frecventa,
            'bursa'     => $s->bursa,
            'dif_limba' => $s->dif_limba
        ];
        return [
            'student_id'   => $secure_student,
            'first_name'   => $s->first_name,
            'last_name'    => $s->last_name,
            'age'          => (int)$s->age,
            'gender'       => $s->gender,
            'generation_id'=> $secure_gen,
            'class_label'  => $s->class_label,
            'teacher_id'   => $secure_teacher,
            'meta'         => $meta
        ];
    }

    // === REST handlers (enqueues) ===

    // POST /edu/v1/enqueue/teacher { "user_id": 5 }
    public static function enqueue_teacher($request) {
        $d = json_decode($request->get_body(), true);
        $uid = (int)($d['user_id'] ?? 0);
        if ($uid<=0) return new WP_REST_Response(['ok'=>false,'error'=>'user_id missing'], 422);
        $payload = self::map_teacher_payload($uid);
        if (!$payload) return new WP_REST_Response(['ok'=>false,'error'=>'user not found'], 404);
        $ok = EduStart_Cube_Outbox::enqueue('teacher.upsert', 'teacher', $payload['teacher_id'], $payload);
        return new WP_REST_Response(['ok'=>$ok, 'payload'=>$payload], $ok?200:500);
    }

    // POST /edu/v1/enqueue/generation { "generation_id": 7 }
    public static function enqueue_generation($request) {
        $d = json_decode($request->get_body(), true);
        $gid = (int)($d['generation_id'] ?? 0);
        if ($gid<=0) return new WP_REST_Response(['ok'=>false,'error'=>'generation_id missing'], 422);
        $payload = self::map_generation_payload($gid);
        if (!$payload) return new WP_REST_Response(['ok'=>false,'error'=>'generation not found'], 404);
        $ok = EduStart_Cube_Outbox::enqueue('generation.upsert', 'generation', $payload['generation_id'], $payload);
        return new WP_REST_Response(['ok'=>$ok, 'payload'=>$payload], $ok?200:500);
    }

    // POST /edu/v1/enqueue/student { "student_id": 11 }
    public static function enqueue_student($request) {
        $d = json_decode($request->get_body(), true);
        $sid = (int)($d['student_id'] ?? 0);
        if ($sid<=0) return new WP_REST_Response(['ok'=>false,'error'=>'student_id missing'], 422);
        $payload = self::map_student_payload($sid);
        if (!$payload) return new WP_REST_Response(['ok'=>false,'error'=>'student not found'], 404);
        $ok = EduStart_Cube_Outbox::enqueue('student.upsert', 'student', $payload['student_id'], $payload);
        return new WP_REST_Response(['ok'=>$ok, 'payload'=>$payload], $ok?200:500);
    }

    // === Admin view (Outbox tools) ===
    public static function render_outbox_tools() {
        if (!current_user_can('manage_options')) return;
        $base = admin_url('options-general.php?page=edustart-cube-settings');
        include plugin_dir_path(__FILE__) . '../admin/views/outbox-tools.php';
    }
}
