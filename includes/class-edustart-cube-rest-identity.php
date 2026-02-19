<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Rest_Identity {
    public static function register_routes() {
        add_action('rest_api_init', function () {
            register_rest_route('edu/v1', '/identity/ensure', [
                'methods'  => 'POST',
                'callback' => [__CLASS__, 'handle_ensure'],
                'permission_callback' => [__CLASS__, 'auth_api_key']
            ]);

            register_rest_route('edu/v1', '/identity/backfill', [
                'methods'  => 'POST',
                'callback' => [__CLASS__, 'handle_backfill'],
                'permission_callback' => [__CLASS__, 'auth_api_key']
            ]);

            register_rest_route('edu/v1', '/identity/resolve', [
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'handle_resolve'],
                'permission_callback' => [__CLASS__, 'auth_api_key']
            ]);
        });
    }

    public static function auth_api_key($request) {
        $api_key = get_option('edustart_api_key');
        $hdr = isset($_SERVER['HTTP_X_API_KEY']) ? sanitize_text_field($_SERVER['HTTP_X_API_KEY']) : null;
        if (!$api_key || !$hdr || !hash_equals($api_key, $hdr)) {
            return new WP_Error('forbidden', 'Invalid API key', ['status' => 403]);
        }
        return true;
    }

    // POST /identity/ensure  { "entity_type": "student|teacher|generation", "local_id": 11 }
    public static function handle_ensure($request) {
        $data = json_decode($request->get_body(), true);
        if (!is_array($data)) { return new WP_REST_Response(['ok'=>false,'error'=>'Invalid JSON'], 400); }
        $type = isset($data['entity_type']) ? sanitize_text_field($data['entity_type']) : '';
        $local_id = isset($data['local_id']) ? intval($data['local_id']) : 0;
        if (!in_array($type, ['teacher','student','generation'], true) || $local_id<=0) {
            return new WP_REST_Response(['ok'=>false,'error'=>'Bad params'], 422);
        }
        $key = EduStart_Cube_Identity::ensure_secure_key($type, $local_id);
        return new WP_REST_Response(['ok'=>true,'secure_key'=>$key], 200);
    }

    // POST /identity/backfill  (creează chei pentru toate entitățile)
    public static function handle_backfill($request) {
        $sum = EduStart_Cube_Identity::backfill_all();
        return new WP_REST_Response(['ok'=>true,'summary'=>$sum], 200);
    }

    // GET /identity/resolve?entity_type=student&secure_key=...
    public static function handle_resolve($request) {
        $type = sanitize_text_field($request->get_param('entity_type'));
        $key  = sanitize_text_field($request->get_param('secure_key'));
        if (!in_array($type, ['teacher','student','generation'], true) || empty($key)) {
            return new WP_REST_Response(['ok'=>false,'error'=>'Bad params'], 422);
        }
        $id = EduStart_Cube_Identity::resolve_local_id($type, $key);
        if (!$id) return new WP_REST_Response(['ok'=>false,'found'=>false], 404);
        return new WP_REST_Response(['ok'=>true,'found'=>true,'local_id'=>$id], 200);
    }
}
