<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Admin {
    public static function register_menu() {
        add_options_page(
            'EduStart ↔ Cube',
            'EduStart ↔ Cube',
            'manage_options',
            'edustart-cube-settings',
            [__CLASS__, 'render_settings']
        );
    }

    public static function render_settings() {
        if (!current_user_can('manage_options')) { return; }

        // regen keys
        if (isset($_POST['ec_regen_api']) && check_admin_referer('ec_keys')) {
            update_option('edustart_api_key', wp_generate_password(32, false, false));
            echo '<div class="updated"><p>API Key regenerată.</p></div>';
        }
        if (isset($_POST['ec_regen_webhook']) && check_admin_referer('ec_keys')) {
            update_option('edustart_webhook_secret', wp_generate_password(48, true, true));
            echo '<div class="updated"><p>Webhook secret regenerat.</p></div>';
        }

        $api_key = get_option('edustart_api_key');
        $webhook = get_option('edustart_webhook_secret');
        $base    = get_site_url();
        $health  = $base . '/wp-json/edu/v1/health';
        $echo    = $base . '/wp-json/edu/v1/test/echo';
        $webh    = $base . '/wp-json/edu/v1/webhooks/numeracy/result';

        include plugin_dir_path(__FILE__) . '../admin/views/settings-page.php';
    }
}
