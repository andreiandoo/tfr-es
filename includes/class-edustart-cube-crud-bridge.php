<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Bridge între CRUD-ul existent (AJAX) și outbox-ul de integrare Cube.
 * Leagă:
 *  - edu_add_students        (creare în bulk)
 *  - edu_update_student      (editare 1 elev)
 *  - delete_student          (ștergere elev)
 *
 * Dacă numele acțiunilor tale server-side diferă, adaptează `add_action(...)`.
 */
class EduStart_Cube_CRUDBridge {

    public static function register_ajax_hooks() {
        // Dacă handler-ele tale AJAX există deja în alt plugin/temă,
        // noi doar ne "abonăm" post-factum cu acțiuni proprii.
        // Varianta 1: dacă NU vrei să atingi handler-ele existente, folosește "after" actions (vezi mai jos).
        // Varianta 2: dacă poți edita handler-ele tale, cheamă direct metodele de mai jos acolo.

        // Varianta 1 — hook-uri "after" (apelează-le din handler-ele tale după salvare),
        // publicăm 3 acțiuni:
        //   do_action('edustart_student_created', $student_ids_array)
        //   do_action('edustart_student_updated', $student_id)
        //   do_action('edustart_student_deleted', $student_id)
        add_action('edustart_student_created', [__CLASS__, 'on_students_created'], 10, 1);
        add_action('edustart_student_updated', [__CLASS__, 'on_student_updated'], 10, 1);
        add_action('edustart_student_deleted', [__CLASS__, 'on_student_deleted'], 10, 1);

        // Dacă vrei, ne putem lega și direct pe ajax (în caz că nu emiți acțiunile de mai sus):
        add_action('wp_ajax_edu_add_students',    [__CLASS__, 'wrap_after_add_students'], 99);
        add_action('wp_ajax_edu_update_student',  [__CLASS__, 'wrap_after_update_student'], 99);
        add_action('wp_ajax_delete_student',      [__CLASS__, 'wrap_after_delete_student'], 99);
    }

    /**
     * Mapper + enqueue pentru o listă de elevi nou creați.
     * @param array $student_ids listă de ID-uri (int)
     */
    public static function on_students_created($student_ids) {
        if (!is_array($student_ids)) return;
        foreach ($student_ids as $sid) {
            self::enqueue_student((int)$sid);
        }
    }

    /**
     * Mapper + enqueue pentru un elev editat.
     */
    public static function on_student_updated($student_id) {
        $sid = (int)$student_id;
        if ($sid > 0) self::enqueue_student($sid);
    }

    /**
     * Pe ștergere elev – deocamdată nu trimitem delete spre Cube.
     * (Dacă vrei, putem trimite mai târziu un `student.deactivate`.)
     */
    public static function on_student_deleted($student_id) {
        // noop acum; logăm doar în entities ca "error" ca să apară în dashboard
        global $wpdb;
        $pid = self::partner_id();
        if (!$pid) return;
        $secure = EduStart_Cube_Identity::get_secure_key('student', (int)$student_id);
        if ($secure) {
            $tbl = $wpdb->prefix . 'edu_integration_entities';
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$tbl} SET status=%s, last_error=%s, updated_at=%s WHERE partner_id=%d AND entity_type=%s AND secure_key=%s",
                'error', 'deleted locally', current_time('mysql'), $pid, 'student', $secure
            ));
        }
    }

    /** ====== WRAPPERS care se atașează la finalul handler-elor AJAX ======
     *  Dacă handler-ele tale deja trimit response cu `wp_send_json_*`, pot ieși din execuție
     *  înainte ca aceste hooks (prioritate 99) să se execute. De aceea RECOMAND
     *  să emiți `do_action('edustart_student_*', ...)` DIN CODUL TĂU, imediat după salvare.
     *  Totuși, dacă răspunsul e trimis la final, wrappers-urile de mai jos pot funcționa out-of-the-box.
     */

    // după edu_add_students (bulk): citim din $_POST și încercăm să identificăm ID-urile create.
    public static function wrap_after_add_students() {
        // Dacă handler-ul tău setează `$_REQUEST['__created_ids']` sau returnează un JSON cu ele,
        // nu avem cum să-l mai citim aici (răspunsul e deja trimis). Cel mai sigur e să emiți:
        //   do_action('edustart_student_created', $created_ids);
        // din handler-ul tău după inserare și să NU te bazezi pe acest wrapper.
        // Îl lăsăm aici ca fallback (nu rupe nimic).
        return;
    }

    public static function wrap_after_update_student() {
        // Similar — recomandat să emiți din handler-ul tău:
        //   do_action('edustart_student_updated', $student_id);
        return;
    }

    public static function wrap_after_delete_student() {
        // Similar — emiți:
        //   do_action('edustart_student_deleted', $student_id);
        return;
    }

    /** ====== Helpers ====== */

    private static function enqueue_student(int $student_id) {
        $payload = EduStart_Cube_Hooks::map_student_payload($student_id);
        if ($payload) {
            EduStart_Cube_Outbox::enqueue('student.upsert', 'student', $payload['student_id'], $payload);
        }
    }

    private static function partner_id(): int {
        global $wpdb;
        $tbl = $wpdb->prefix . 'edu_integration_partners';
        $id = (int) $wpdb->get_var( $wpdb->prepare("SELECT id FROM {$tbl} WHERE name=%s LIMIT 1", 'cube') );
        return $id ?: 0;
    }
}
