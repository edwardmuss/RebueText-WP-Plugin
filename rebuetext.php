<?php

/**
 * Plugin Name: RebueText
 * Plugin URI: https://rebuetext.com/plugins
 * Description: Sends SMS notifications for WooCommerce order status changes. Receive messages from Contact Forms 7, Gravity Forms and Wp Forms
 * Version: 1.0
 * Author: RebueText
 * Author URI: https://rebuetext.com
 * License: GPL2
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('REBUETEXT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REBUETEXT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('REBUETEXT_DB_VERSION', '1.2'); // Set the database version here

// Use require_once for critical files so the plugin gracefully fatals if a file is missing
require_once(REBUETEXT_PLUGIN_DIR . 'admin/settings-page.php');
require_once(REBUETEXT_PLUGIN_DIR . 'admin/admin-menu.php');
require_once(REBUETEXT_PLUGIN_DIR . 'admin/sms-logs.php');
require_once(REBUETEXT_PLUGIN_DIR . 'includes/woo-sms-api.php');
require_once(REBUETEXT_PLUGIN_DIR . 'includes/installer.php');
require_once(REBUETEXT_PLUGIN_DIR . 'includes/functions.php');

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
    // Load only on your plugin page or CF7 form edit page
    if (
        $hook === 'toplevel_page_rebuetext-settings' || // your main settings page
        $hook === 'contact_page_rebuetext-form-integrations' || // your CF7 submenu
        (isset($_GET['page']) && $_GET['page'] === 'rebuetext-settings')
    ) {
        wp_enqueue_style('rebuetext-bootstrap', REBUETEXT_PLUGIN_URL . 'assets/css/bootstrap.min.css');
        wp_enqueue_style('rebuetext-admin-style', REBUETEXT_PLUGIN_URL . 'assets/css/admin-style.css', array(), filemtime(REBUETEXT_PLUGIN_DIR . 'assets/css/admin-style.css'));
    }
}

// Load Bootstrap JS and Custom JS
add_action('admin_enqueue_scripts', 'rebuetext_enqueue_admin_scripts');
function rebuetext_enqueue_admin_scripts($hook)
{
    if (
        $hook === 'toplevel_page_rebuetext-settings' ||
        $hook === 'contact_page_rebuetext-form-integrations' ||
        (isset($_GET['page']) && $_GET['page'] === 'wpcf7')
    ) {
        // Bootstrap JS 
        wp_enqueue_script('rebuetext-bootstrap-js', REBUETEXT_PLUGIN_URL . 'assets/js/bootstrap.bundle.min.js', array('jquery'), null, true);

        // Custom Admin JS
        wp_enqueue_script('rebuetext-admin-js', REBUETEXT_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), null, true);

        // Explicitly localize the AJAX URL to prevent 400 Bad Request errors
        wp_localize_script('rebuetext-admin-js', 'rebuetext_globals', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
}

add_action('admin_enqueue_scripts', 'rebuetext_enqueue_admin_assets');
function rebuetext_enqueue_admin_assets($hook)
{
    // Highly Recommended: Only load on your specific plugin page
    if (isset($_GET['page']) && $_GET['page'] !== 'rebuetext-sms-logs') return;

    // Enqueue JS
    wp_enqueue_script(
        'rebuetext-sms-logs',
        REBUETEXT_PLUGIN_URL . 'assets/js/sms-logs.js',
        array(),
        '1.0.0',
        array('strategy' => 'defer')
    );

    // Enqueue CSS (Move your inline modal styles here)
    wp_add_inline_style('wp-admin', '
        #response-modal { 
            display:none; position:fixed; top:50%; left:50%; 
            transform:translate(-50%, -50%); background:white; 
            padding:20px; border-radius:5px; box-shadow:0px 0px 10px rgba(0,0,0,0.2);
            z-index: 9999;
        }
    ');
}

// Hook into WooCommerce order status change
add_action('woocommerce_order_status_changed', 'rebuetext_send_order_sms', 10, 3);

// Create SMS logs table ON ACTIVATION ONLY
register_activation_hook(__FILE__, 'rebuetext_create_sms_logs_table');
