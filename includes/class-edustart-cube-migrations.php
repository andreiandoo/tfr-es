<?php
if (!defined('ABSPATH')) { exit; }

class EduStart_Cube_Migrations {
    public static function install_or_upgrade() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        $tbl_identity = $wpdb->prefix . 'edu_identity_keys';
        $tbl_partners = $wpdb->prefix . 'edu_integration_partners';
        $tbl_entities = $wpdb->prefix . 'edu_integration_entities';
        $tbl_outbox   = $wpdb->prefix . 'edu_integration_outbox';
        $tbl_inbox    = $wpdb->prefix . 'edu_integration_inbox'; // există deja minim, îl extindem
        $tbl_tokens   = $wpdb->prefix . 'edu_integration_tokens';

        $sql_partners = "CREATE TABLE {$tbl_partners} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(64) NOT NULL,
            base_url VARCHAR(255) NOT NULL,
            auth_type ENUM('oauth2_cc') NOT NULL DEFAULT 'oauth2_cc',
            client_id VARCHAR(128) NULL,
            client_secret VARCHAR(256) NULL,
            hmac_secret VARCHAR(128) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_name (name)
        ) {$charset};";

        $sql_entities = "CREATE TABLE {$tbl_entities} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            partner_id BIGINT UNSIGNED NOT NULL,
            entity_type ENUM('teacher','student','generation') NOT NULL,
            local_id BIGINT UNSIGNED NOT NULL,
            secure_key CHAR(26) NOT NULL,
            remote_id VARCHAR(128) NULL,
            status ENUM('pending','synced','error') NOT NULL DEFAULT 'pending',
            last_error TEXT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_partner_entity (partner_id,entity_type,secure_key),
            KEY k_partner_status (partner_id,status),
            KEY k_entity_local (entity_type,local_id)
        ) {$charset};";

        $sql_outbox = "CREATE TABLE {$tbl_outbox} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            partner_id BIGINT UNSIGNED NOT NULL,
            event_type VARCHAR(64) NOT NULL,
            entity_type ENUM('teacher','student','generation') NOT NULL,
            secure_key CHAR(26) NOT NULL,
            payload_json LONGTEXT NOT NULL,
            idempotency_key CHAR(36) NOT NULL,
            attempts INT NOT NULL DEFAULT 0,
            next_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending','sent','error') NOT NULL DEFAULT 'pending',
            last_error TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY k_status_next (status,next_attempt_at),
            KEY k_partner_event (partner_id,event_type)
        ) {$charset};";

        // Inbox extins cu coloane suplimentare (dacă nu există, dbDelta le adaugă)
        $sql_inbox = "CREATE TABLE {$tbl_inbox} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            partner_id BIGINT UNSIGNED NULL,
            event_type VARCHAR(64) NOT NULL,
            idempotency_key CHAR(36) NULL,
            signature_valid TINYINT(1) NOT NULL DEFAULT 0,
            payload_json LONGTEXT NOT NULL,
            error_message TEXT NULL,
            status ENUM('received','processed','error') NOT NULL DEFAULT 'received',
            processed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY k_status (status),
            KEY k_event_created (event_type,created_at)
        ) {$charset};";

        $sql_tokens = "CREATE TABLE {$tbl_tokens} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            partner_id BIGINT UNSIGNED NOT NULL,
            access_token TEXT NOT NULL,
            expires_at DATETIME NOT NULL,
            scope VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_partner (partner_id)
        ) {$charset};";

        // Identity (în caz că nu există — main l-a creat deja)
        $sql_identity = "CREATE TABLE {$tbl_identity} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            entity_type ENUM('teacher','student','generation') NOT NULL,
            local_id BIGINT UNSIGNED NOT NULL,
            secure_key CHAR(26) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_entity_secure (entity_type,secure_key),
            UNIQUE KEY uk_entity_local (entity_type,local_id),
            KEY k_entity_type (entity_type)
        ) {$charset};";

        dbDelta($sql_identity);
        dbDelta($sql_partners);
        dbDelta($sql_entities);
        dbDelta($sql_outbox);
        dbDelta($sql_inbox);
        dbDelta($sql_tokens);

        // seed partner 'cube' dacă lipsește
        $exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM {$tbl_partners} WHERE name=%s LIMIT 1", 'cube') );
        if (!$exists) {
            $wpdb->insert($tbl_partners, [
                'name' => 'cube',
                'base_url' => 'https://cube.example.com',
                'auth_type' => 'oauth2_cc',
                'client_id' => '',
                'client_secret' => '',
                'hmac_secret' => get_option('edustart_webhook_secret'),
                'is_active' => 1,
                'created_at' => current_time('mysql')
            ]);
        }
    }
}
