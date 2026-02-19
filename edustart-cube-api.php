<?php
/**
 * Plugin Name: EduStart ↔ Cube API (Bootstrap)
 * Description: REST API bootstrap for EduStart: health GET, secured webhook POST (numeracy.result), simple API-key auth for testing, identity key map, and wp_edu_results writer.
 * Version: 0.1.0
 * Author: EduStart
 */

if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_API {
    const OPT_API_KEY = 'edustart_api_key';              // for test GET/POST endpoints (X-Api-Key)
    const OPT_WEBHOOK_SECRET = 'edustart_webhook_secret';// HMAC secret shared with Cube for webhook
    const INBOX_TABLE = 'wp_edu_integration_inbox';
    const IDENTITY_TABLE = 'wp_edu_identity_keys';

    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'on_activate']);
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function on_activate() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Create inbox table
        $charset_collate = $wpdb->get_charset_collate();
        $inbox = $wpdb->prefix . 'edu_integration_inbox';
        $identity = $wpdb->prefix . 'edu_identity_keys';

        $sql1 = "CREATE TABLE {$inbox} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(64) NOT NULL,
            idempotency_key CHAR(36) NULL,
            signature_valid TINYINT(1) NOT NULL DEFAULT 0,
            payload_json LONGTEXT NOT NULL,
            processed_at DATETIME NULL,
            status ENUM('received','processed','error') NOT NULL DEFAULT 'received',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY k_status (status),
            KEY k_created (created_at)
        ) {$charset_collate};";

        $sql2 = "CREATE TABLE {$identity} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            entity_type ENUM('teacher','student','generation') NOT NULL,
            local_id BIGINT UNSIGNED NOT NULL,
            secure_key CHAR(26) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_entity_secure (entity_type, secure_key),
            UNIQUE KEY uk_entity_local (entity_type, local_id)
        ) {$charset_collate};";

        dbDelta($sql1);
        dbDelta($sql2);

        // Seed secrets if empty
        if (!get_option(self::OPT_API_KEY)) {
            add_option(self::OPT_API_KEY, wp_generate_password(32, false, false));
        }
        if (!get_option(self::OPT_WEBHOOK_SECRET)) {
            add_option(self::OPT_WEBHOOK_SECRET, wp_generate_password(48, true, true));
        }
    }

    public function register_routes() {
        register_rest_route('edu/v1', '/health', [
            'methods'  => 'GET',
            'callback' => [$this, 'handle_health'],
            'permission_callback' => [$this, 'auth_api_key']
        ]);

        register_rest_route('edu/v1', '/webhooks/numeracy/result', [
            'methods'  => 'POST',
            'callback' => [$this, 'handle_webhook_numeracy_result'],
            'permission_callback' => '__return_true' // We'll validate HMAC inside
        ]);

        // Simple echo endpoint for POST testing with API key
        register_rest_route('edu/v1', '/test/echo', [
            'methods'  => 'POST',
            'callback' => [$this, 'handle_test_echo'],
            'permission_callback' => [$this, 'auth_api_key']
        ]);
    }

    /* ---------- AUTH HELPERS ---------- */

    public function auth_api_key($request) {
        $api_key = get_option(self::OPT_API_KEY);
        $hdr = isset($_SERVER['HTTP_X_API_KEY']) ? sanitize_text_field($_SERVER['HTTP_X_API_KEY']) : null;
        if (!$api_key || !$hdr || !hash_equals($api_key, $hdr)) {
            return new WP_Error('forbidden', 'Invalid API key', ['status' => 403]);
        }
        return true;
    }

    private function verify_hmac($body, $timestamp, $signature) {
        $secret = get_option(self::OPT_WEBHOOK_SECRET);
        if (!$secret) { return false; }

        // Reject if timestamp drift > 300s
        $now = time();
        if (abs($now - intval($timestamp)) > 300) return false;

        $base = $body . '|' . $timestamp;
        $calc = hash_hmac('sha256', $base, $secret);
        return hash_equals($calc, $signature);
    }

    /* ---------- HANDLERS ---------- */

    public function handle_health($request) {
        return new WP_REST_Response([
            'ok' => true,
            'ts' => time(),
            'site' => get_bloginfo('name')
        ], 200);
    }

    public function handle_test_echo($request) {
        $data = json_decode($request->get_body(), true);
        if (!is_array($data)) $data = ['raw' => $request->get_body()];
        return new WP_REST_Response([
            'received' => $data,
            'note' => 'Auth OK via X-Api-Key'
        ], 200);
    }

    public function handle_webhook_numeracy_result($request) {
        global $wpdb;
        $inbox = $wpdb->prefix . 'edu_integration_inbox';

        $body = $request->get_body();
        $sig  = isset($_SERVER['HTTP_X_SIGNATURE']) ? sanitize_text_field($_SERVER['HTTP_X_SIGNATURE']) : '';
        $ts   = isset($_SERVER['HTTP_X_TIMESTAMP']) ? sanitize_text_field($_SERVER['HTTP_X_TIMESTAMP']) : '';

        $sig_ok = $this->verify_hmac($body, $ts, $sig);
        $status = $sig_ok ? 'received' : 'error';

        // store raw inbox
        $wpdb->insert($inbox, [
            'event_type'      => 'numeracy.result',
            'idempotency_key' => null,
            'signature_valid' => $sig_ok ? 1 : 0,
            'payload_json'    => $body,
            'status'          => $status,
            'created_at'      => current_time('mysql')
        ]);

        if (!$sig_ok) {
            return new WP_REST_Response(['ok' => false, 'error' => 'Invalid signature or timestamp'], 401);
        }

        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            return new WP_REST_Response(['ok' => false, 'error' => 'Invalid JSON'], 400);
        }

        // Minimal validation
        $required = ['student_id','professor_id','modul','scores','data_completarii','modul_type','status','completion','results'];
        foreach ($required as $k) {
            if (!array_key_exists($k, $payload)) {
                return new WP_REST_Response(['ok' => false, 'error' => 'Missing field: '.$k], 422);
            }
        }

        // Resolve secure_key -> local IDs
        $student_local_id  = $this->resolve_local_id('student', $payload['student_id']);
        $prof_local_id     = $this->resolve_local_id('teacher', $payload['professor_id']);

        if (!$student_local_id || !$prof_local_id) {
            return new WP_REST_Response(['ok' => false, 'error' => 'Unknown student_id or professor_id'], 404);
        }

        // Build wp_edu_results row
        $table_results = $wpdb->prefix . 'edu_results';

        $results_json = wp_json_encode(array_merge($payload['results'] ?? [], [
            'scores' => $payload['scores'],
            'data_completarii' => $payload['data_completarii'],
            'source' => 'cube'
        ]), JSON_UNESCAPED_UNICODE);

        // Build PHP serialized "score" from scores
        $scores = $payload['scores'];
        if (!is_array($scores)) $scores = [];
        $score_serialized = maybe_serialize($scores);

        $data = [
            'student_id'   => intval($student_local_id),
            'modul_type'   => 'num',
            'modul_id'     => 0,
            'status'       => sanitize_text_field($payload['status']),
            'results'      => $results_json,
            'created_at'   => current_time('mysql'),
            'professor_id' => intval($prof_local_id),
            'class_id'     => null,
            'score'        => $score_serialized,
            'completion'   => intval($payload['completion']),
            'modul'        => sanitize_text_field($payload['modul']),
        ];

        $ok = $wpdb->insert($table_results, $data);
        if ($ok === false) {
            return new WP_REST_Response(['ok' => false, 'error' => 'DB insert failed'], 500);
        }

        // Mark inbox processed
        $wpdb->update($inbox, [
            'status' => 'processed',
            'processed_at' => current_time('mysql')
        ], ['id' => $wpdb->insert_id]);

        return new WP_REST_Response(['ok' => true, 'insert_id' => $wpdb->insert_id], 200);
    }

    private function resolve_local_id($entity_type, $secure_key) {
        global $wpdb;
        $identity = $wpdb->prefix . 'edu_identity_keys';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT local_id FROM {$identity} WHERE entity_type=%s AND secure_key=%s LIMIT 1",
            $entity_type, $secure_key
        ));
        return $row ? intval($row->local_id) : null;
    }
}

new EduStart_Cube_API();


// === bootstrap includes (add at end of file) ===
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-migrations.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-identity.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-rest-identity.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-admin.php';

// run extra migrations on activate/upgrade
register_activation_hook(__FILE__, ['EduStart_Cube_Migrations', 'install_or_upgrade']);

// init identity rest routes (after rest_api_init from main)
add_action('init', function () {
    EduStart_Cube_Rest_Identity::register_routes();
});

// admin page
add_action('admin_menu', ['EduStart_Cube_Admin', 'register_menu']);


// ==== PUSH to Cube (OAuth2 CC + Outbox + Cron) ====
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-client.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-outbox.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-hooks.php';

// Cron: procesează outbox la fiecare 2 minute
add_filter('cron_schedules', function($s) {
    if (!isset($s['every_2_minutes'])) {
        $s['every_2_minutes'] = ['interval' => 120, 'display' => 'Every 2 Minutes'];
    }
    return $s;
});
if (!wp_next_scheduled('edustart_cube_outbox_process')) {
    wp_schedule_event(time()+60, 'every_2_minutes', 'edustart_cube_outbox_process');
}
add_action('edustart_cube_outbox_process', ['EduStart_Cube_Outbox', 'process_queue']);

// REST pentru enqueue (test & integrare cu UI-ul tău existent)
add_action('rest_api_init', ['EduStart_Cube_Hooks', 'register_routes']);

// extindem pagina de admin cu secțiune Outbox/Push
add_action('admin_menu', function() {
    add_submenu_page(
        null,
        'Outbox Tools',
        'Outbox Tools',
        'manage_options',
        'edustart-cube-outbox-tools',
        ['EduStart_Cube_Hooks', 'render_outbox_tools']
    );
});


// === AUTOHOOKS: legăm direct pe fluxuri WP (user_register, profile_update etc.) ===
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-autohooks.php';
add_action('init', ['EduStart_Cube_AutoHooks', 'init']);

// === SCANNER: fallback pentru tabele custom (generații/elevi) ===
require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-scanner.php';

// Cron pentru scanner (la 5 minute)
add_filter('cron_schedules', function($s) {
    if (!isset($s['every_5_minutes'])) {
        $s['every_5_minutes'] = ['interval' => 300, 'display' => 'Every 5 Minutes'];
    }
    return $s;
});
if (!wp_next_scheduled('edustart_cube_scanner_run')) {
    wp_schedule_event(time()+120, 'every_5_minutes', 'edustart_cube_scanner_run');
}
add_action('edustart_cube_scanner_run', ['EduStart_Cube_Scanner', 'run']);

require_once plugin_dir_path(__FILE__) . 'includes/class-edustart-cube-crud-bridge.php';
add_action('init', ['EduStart_Cube_CRUDBridge', 'register_ajax_hooks']);
