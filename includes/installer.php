<?php

if (!defined('ABSPATH')) exit;

function rebuetext_create_sms_logs_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rebuetext_sms_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        phone VARCHAR(20) NOT NULL,
        sender VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(20) NOT NULL,
        response TEXT,
        sent_at DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Store the latest database version using the constant
    update_option('rebuetext_db_version', REBUETEXT_DB_VERSION);
}

// Check if table needs an update
function rebuetext_check_db_update()
{
    $current_version = get_option('rebuetext_db_version', '1.0'); // Default version if not set

    if ($current_version < REBUETEXT_DB_VERSION) {
        rebuetext_create_sms_logs_table(); // Apply updates
    }
}
add_action('admin_init', 'rebuetext_check_db_update'); // Runs in admin panel
