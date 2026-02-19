<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Client {
    const OPT_PARTNER = 'edustart_cube_partner_settings'; // array: base_url, client_id, client_secret
    const TOKENS_TBL  = 'edu_integration_tokens';
    const PARTNERS_TBL= 'edu_integration_partners';

    public static function get_settings(): array {
        $o = get_option(self::OPT_PARTNER, []);
        $defaults = ['base_url' => '', 'client_id' => '', 'client_secret' => ''];
        return wp_parse_args($o, $defaults);
    }

    public static function save_settings(array $data) {
        update_option(self::OPT_PARTNER, [
            'base_url' => rtrim(sanitize_text_field($data['base_url'] ?? ''), '/'),
            'client_id' => sanitize_text_field($data['client_id'] ?? ''),
            'client_secret' => sanitize_text_field($data['client_secret'] ?? ''),
        ]);
    }

    public static function oauth_token(): ?string {
        global $wpdb;
        $tbl_tokens = $wpdb->prefix . self::TOKENS_TBL;
        $tbl_partners = $wpdb->prefix . self::PARTNERS_TBL;

        $partner_id = (int) $wpdb->get_var( $wpdb->prepare("SELECT id FROM {$tbl_partners} WHERE name=%s LIMIT 1", 'cube') );
        if (!$partner_id) return null;

        // read cached token
        $row = $wpdb->get_row( $wpdb->prepare("SELECT access_token, expires_at FROM {$tbl_tokens} WHERE partner_id=%d LIMIT 1", $partner_id) );
        if ($row && strtotime($row->expires_at) > (time()+60)) {
            return $row->access_token;
        }

        // request new token
        $s = self::get_settings();
        if (!$s['base_url'] || !$s['client_id'] || !$s['client_secret']) return null;

        $resp = wp_remote_post($s['base_url'].'/oauth2/token', [
            'timeout' => 20,
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body'    => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $s['client_id'],
                'client_secret' => $s['client_secret']
            ])
        ]);
        if (is_wp_error($resp)) return null;

        $code = wp_remote_retrieve_response_code($resp);
        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if ($code!==200 || !is_array($body) || empty($body['access_token'])) return null;

        $ttl = !empty($body['expires_in']) ? (int)$body['expires_in'] : 3600;
        $exp = gmdate('Y-m-d H:i:s', time()+$ttl);

        if ($row) {
            $wpdb->update($tbl_tokens, [
                'access_token' => $body['access_token'],
                'expires_at'   => $exp,
                'scope'        => isset($body['scope']) ? sanitize_text_field($body['scope']) : null
            ], ['partner_id' => $partner_id]);
        } else {
            $wpdb->insert($tbl_tokens, [
                'partner_id'   => $partner_id,
                'access_token' => $body['access_token'],
                'expires_at'   => $exp,
                'scope'        => isset($body['scope']) ? sanitize_text_field($body['scope']) : null,
                'created_at'   => current_time('mysql')
            ]);
        }
        return $body['access_token'];
    }

    public static function post_json(string $path, array $payload, array &$err = null): ?array {
        $s = self::get_settings();
        $token = self::oauth_token();
        if (!$token) { $err = ['code'=>'no_token','msg'=>'Missing OAuth2 token']; return null; }

        $url = $s['base_url'] . $path;
        $resp = wp_remote_post($url, [
            'timeout' => 20,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer '.$token,
                'X-Idempotency-Key' => wp_generate_uuid4()
            ],
            'body' => wp_json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
        if (is_wp_error($resp)) { $err = ['code'=>'wp_error','msg'=>$resp->get_error_message()]; return null; }

        $code = wp_remote_retrieve_response_code($resp);
        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if ($code>=200 && $code<300) return is_array($body) ? $body : [];

        $err = ['code'=>'http_'.$code, 'msg'=> is_array($body)?json_encode($body):wp_remote_retrieve_body($resp)];
        return null;
    }
}
