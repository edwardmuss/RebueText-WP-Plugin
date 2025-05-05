<?php

/**
 * Plugin Name: RebueText
 * Plugin URI: https://rebuetext.com/plugins
 * Description: Sends SMS notifications for WooCommerce order status changes. Receive messages from Contact Forms 7, Gravity Forms and Wp Forms
 * Version: 1.0
 * Author: Edward Muss
 * Author URI: https://edwardmuss.tech
 * License: GPL2
 */

if (!defined('ABSPATH')) exit;

define('REBUETEXT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REBUETEXT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('REBUETEXT_DB_VERSION', '1.2'); // Set the database version here

include_once(REBUETEXT_PLUGIN_DIR . 'admin/settings-page.php');
include_once(REBUETEXT_PLUGIN_DIR . 'admin/admin-menu.php');
include_once(REBUETEXT_PLUGIN_DIR . 'admin/sms-logs.php');
include_once(REBUETEXT_PLUGIN_DIR . 'includes/woo-sms-api.php');
include_once(REBUETEXT_PLUGIN_DIR . 'includes/installer.php');
include_once(REBUETEXT_PLUGIN_DIR . 'includes/functions.php');

// Add Links Below the Plugin (under "Deactivate")
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rebuetext_plugin_action_links');
function rebuetext_plugin_action_links($links)
{
    $custom_links = [
        '<a href="admin.php?page=rebuetext-settings">Settings</a>',
        '<a href="https://rebuetext.com/docs/1.0" target="_blank">Docs</a>',
    ];
    return array_merge($custom_links, $links);
}

// Add Meta Links on the Right (Author/Version row)
add_filter('plugin_row_meta', 'rebuetext_plugin_row_meta', 10, 2);
function rebuetext_plugin_row_meta($links, $file)
{
    if ($file === plugin_basename(__FILE__)) {
        $meta_links = [
            '<a href="https://rebuetext.com/contact-us" target="_blank">Support</a>',
            '<a href="https://rebuetext.com/pricing" target="_blank">Pricing</a>',
            '<a href="https://github.com/edwardmuss/RebueText-WP-Plugin" target="_blank">Github</a>',
        ];
        $links = array_merge($links, $meta_links);
    }
    return $links;
}

// Load Bootstrap and custom CSS
add_action('admin_enqueue_scripts', 'rebuetext_enqueue_admin_styles');
function rebuetext_enqueue_admin_styles($hook)
{
    // echo ('Hook: ' . $hook);
    // Load only on your plugin page or CF7 form edit page
    if (
        $hook === 'toplevel_page_rebuetext-settings' || // your main settings page
        $hook === 'contact_page_rebuetext-form-integrations' || // your CF7 submenu
        (isset($_GET['page']) && $_GET['page'] === 'rebuetext-settings')
    ) {
        wp_enqueue_style('rebuetext-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_style('rebuetext-admin-style', REBUETEXT_PLUGIN_URL . 'assets/css/admin-style.css');
    }
}

// Load Bootstrap JS and Custom JS
add_action('admin_enqueue_scripts', 'rebuetext_enqueue_admin_scripts');
function rebuetext_enqueue_admin_scripts($hook)
{
    if (
        $hook === 'toplevel_page_rebuetext-settings' || // your main settings page
        $hook === 'contact_page_rebuetext-form-integrations' || // your CF7 submenu
        (isset($_GET['page']) && $_GET['page'] === 'wpcf7')
    ) {
        // Bootstrap JS (Ensure jQuery is available)
        wp_enqueue_script('rebuetext-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);

        // Custom Admin JS
        wp_enqueue_script('rebuetext-admin-js', REBUETEXT_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), null, true);
    }
}

// Hook into WooCommerce order status change
add_action('woocommerce_order_status_changed', 'rebuetext_send_order_sms', 10, 3);

// Create SMS logs table
// register_activation_hook(__FILE__, 'rebuetext_create_sms_logs_table');

rebuetext_create_sms_logs_table();
