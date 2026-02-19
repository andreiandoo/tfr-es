<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_AutoHooks {
    public static function init() {
        // Profesor nou creat
        add_action('user_register', [__CLASS__, 'on_user_register'], 20);
        // Profesor actualizat (meta sau profil)
        add_action('profile_update', [__CLASS__, 'on_profile_update'], 20, 2);
        add_action('updated_user_meta', [__CLASS__, 'on_user_meta_change'], 20, 4);
        add_action('added_user_meta', [__CLASS__, 'on_user_meta_change'], 20, 4);
    }

    // ===== PUBLIC APIs (le poți apela din codul tău existent) =====
    public static function on_generation_saved(int $generation_id) {
        $payload = EduStart_Cube_Hooks::map_generation_payload($generation_id);
        if ($payload) {
            EduStart_Cube_Outbox::enqueue('generation.upsert', 'generation', $payload['generation_id'], $payload);
        }
    }
    public static function on_student_saved(int $student_id) {
        $payload = EduStart_Cube_Hooks::map_student_payload($student_id);
        if ($payload) {
            EduStart_Cube_Outbox::enqueue('student.upsert', 'student', $payload['student_id'], $payload);
        }
    }

    // ===== WP hooks pentru profesori =====

    public static function on_user_register($user_id) {
        if (!self::is_profesor($user_id)) return;
        $payload = EduStart_Cube_Hooks::map_teacher_payload((int)$user_id);
        if ($payload) {
            EduStart_Cube_Outbox::enqueue('teacher.upsert', 'teacher', $payload['teacher_id'], $payload);
        }
    }

    public static function on_profile_update($user_id, $old_user_data) {
        if (!self::is_profesor($user_id)) return;
        $payload = EduStart_Cube_Hooks::map_teacher_payload((int)$user_id);
        if ($payload) {
            EduStart_Cube_Outbox::enqueue('teacher.upsert', 'teacher', $payload['teacher_id'], $payload);
        }
    }

    public static function on_user_meta_change($meta_id, $user_id, $meta_key, $_meta_value) {
        // dacă se schimbă meta-uri relevante, trimitem upsert
        if (!self::is_profesor($user_id)) return;
        $relevant = ['phone','user_status_profesor','nivel_predare','materia_predata','assigned_school_ids','first_name','last_name'];
        if (!in_array($meta_key, $relevant, true)) return;

        $payload = EduStart_Cube_Hooks::map_teacher_payload((int)$user_id);
        if ($payload) {
            EduStart_Cube_Outbox::enqueue('teacher.upsert', 'teacher', $payload['teacher_id'], $payload);
        }
    }

    private static function is_profesor($user_id): bool {
        $user = get_userdata($user_id);
        if (!$user) return false;
        return isset($user->roles) && in_array('profesor', (array)$user->roles, true);
        // alternativ: verifică meta wp_capabilities conținând profesor
    }
}
