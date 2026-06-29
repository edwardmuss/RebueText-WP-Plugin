<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

if (is_admin()) {
    // Corrected action prefix
    add_action('wp_ajax_rebuetext_save_settings', 'rebuetext_save_settings');
    add_action('admin_init', 'rebuetext_register_form_sms_settings');
    add_action('wp_ajax_rebuetext_refresh_templates', 'rebuetext_refresh_templates_callback');

    /**
     * Recursively sanitize multi-dimensional arrays
     */
    function rebuetext_sanitize_array($array)
    {
        $sanitized = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sanitized[sanitize_text_field($key)] = rebuetext_sanitize_array($value);
            } else {
                $sanitized[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }
        return $sanitized;
    }

    function rebuetext_save_settings()
    {
        // Authorization check
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        // Proper nonce sanitization and verification
        $nonce = isset($_POST['security']) ? sanitize_text_field(wp_unslash($_POST['security'])) : '';
        if (!wp_verify_nonce($nonce, 'rebuetext_nonce')) {
            wp_send_json_error('Invalid security token', 403);
        }

        // Do NOT sanitize the raw serialized string here, as it destroys URL-encoded arrays and textareas.
        // Unslash it, parse it, and then sanitize the individual elements below.
        $raw_data = isset($_POST['data']) ? wp_unslash($_POST['data']) : '';
        parse_str($raw_data, $settings_data);

        // Sanitize and update individual elements safely
        update_option('rebuetext_api_env', isset($settings_data['rebuetext_api_env']) ? sanitize_text_field($settings_data['rebuetext_api_env']) : 'production');
        update_option('rebuetext_api_token', isset($settings_data['rebuetext_api_token']) ? sanitize_text_field($settings_data['rebuetext_api_token']) : '');
        update_option('rebuetext_sender_id', isset($settings_data['rebuetext_sender_id']) ? sanitize_text_field($settings_data['rebuetext_sender_id']) : '');
        update_option('rebuetext_wa_phone', isset($settings_data['rebuetext_wa_phone']) ? sanitize_text_field($settings_data['rebuetext_wa_phone']) : '');
        update_option('rebuetext_admin_phone', isset($settings_data['rebuetext_admin_phone']) ? sanitize_text_field($settings_data['rebuetext_admin_phone']) : '');

        $statuses = isset($settings_data['rebuetext_enabled_statuses']) && is_array($settings_data['rebuetext_enabled_statuses']) ? array_map('sanitize_text_field', $settings_data['rebuetext_enabled_statuses']) : [];
        update_option('rebuetext_enabled_statuses', $statuses);

        $customer_templates = isset($settings_data['rebuetext_customer_templates']) && is_array($settings_data['rebuetext_customer_templates']) ? array_map('sanitize_textarea_field', $settings_data['rebuetext_customer_templates']) : [];
        update_option('rebuetext_customer_templates', $customer_templates);

        $channels = isset($settings_data['rebuetext_channels']) && is_array($settings_data['rebuetext_channels']) ? rebuetext_sanitize_array($settings_data['rebuetext_channels']) : [];
        update_option('rebuetext_channels', $channels);

        $wa_mappings = isset($settings_data['rebuetext_wa_mappings']) && is_array($settings_data['rebuetext_wa_mappings']) ? rebuetext_sanitize_array($settings_data['rebuetext_wa_mappings']) : [];
        update_option('rebuetext_wa_mappings', $wa_mappings);

        $admin_templates = isset($settings_data['rebuetext_admin_templates']) && is_array($settings_data['rebuetext_admin_templates']) ? array_map('sanitize_textarea_field', $settings_data['rebuetext_admin_templates']) : [];
        update_option('rebuetext_admin_templates', $admin_templates);

        wp_send_json_success('Settings saved successfully!');
    }

    function rebuetext_register_form_sms_settings()
    {
        // Register settings with sanitization callbacks
        register_setting(
            'rebuetext_form_sms',
            'rebuetext_cf7_sms_enabled',
            ['sanitize_callback' => 'absint']
        );
        register_setting(
            'rebuetext_form_sms',
            'rebuetext_gf_sms_enabled',
            ['sanitize_callback' => 'absint']
        );
        register_setting(
            'rebuetext_form_sms',
            'rebuetext_cf7_sms_template',
            ['sanitize_callback' => 'sanitize_textarea_field']
        );
        register_setting(
            'rebuetext_form_sms',
            'rebuetext_gf_sms_template',
            ['sanitize_callback' => 'sanitize_textarea_field']
        );

        add_settings_section('cf7_sms', 'Contact Form 7 SMS', null, 'rebuetext-form-integrations');
        add_settings_field('cf7_enabled', 'Enable CF7 SMS', function () {
            echo '<input type="checkbox" name="rebuetext_cf7_sms_enabled" value="1" ' . checked(1, get_option('rebuetext_cf7_sms_enabled'), false) . ' />';
        }, 'rebuetext-form-integrations', 'cf7_sms');
        add_settings_field('cf7_template', 'CF7 SMS Template', function () {
            echo '<textarea name="rebuetext_cf7_sms_template" rows="4" style="width:100%;">' . esc_textarea(get_option('rebuetext_cf7_sms_template')) . '</textarea>';
        }, 'rebuetext-form-integrations', 'cf7_sms');

        add_settings_section('gf_sms', 'Gravity Forms SMS', null, 'rebuetext-form-integrations');
        add_settings_field('gf_enabled', 'Enable GF SMS', function () {
            echo '<input type="checkbox" name="rebuetext_gf_sms_enabled" value="1" ' . checked(1, get_option('rebuetext_gf_sms_enabled'), false) . ' />';
        }, 'rebuetext-form-integrations', 'gf_sms');
        add_settings_field('gf_template', 'GF SMS Template', function () {
            echo '<textarea name="rebuetext_gf_sms_template" rows="4" style="width:100%;">' . esc_textarea(get_option('rebuetext_gf_sms_template')) . '</textarea>';
        }, 'rebuetext-form-integrations', 'gf_sms');
    }

    function rebuetext_refresh_templates_callback()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        // Delete transient to force full external lookup
        delete_transient('rebuetext_wa_templates_cache');

        $templates = rebuetext_fetch_whatsapp_templates();

        wp_send_json_success([
            'message'   => 'Templates synced successfully!',
            'templates' => $templates
        ]);
    }
}

/**
 * Get the correct API base URL depending on the selected environment.
 */
function rebuetext_get_api_base_url()
{
    $env = get_option('rebuetext_api_env', 'production');

    if ($env === 'local') {
        return 'http://127.0.0.1:8000/api/v1'; // Local Laravel environment
    }

    return 'https://rebuetext.com/api/v1'; // Production environment
}

// SMS function
function rebuetext_send_sms($phone, $message, $correlator = 'custom')
{
    $api_token = sanitize_text_field(get_option('rebuetext_api_token'));
    $sender_id = sanitize_text_field(get_option('rebuetext_sender_id'));
    $endpoint = rebuetext_get_api_base_url() . '/send-sms';

    if (empty($phone) || empty($message)) return;

    $headers = [
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
        'Authorization' => 'Bearer ' . $api_token,
    ];

    $sms_data = [
        'sender'     => $sender_id,
        'message'    => sanitize_text_field($message),
        'phone'      => sanitize_text_field($phone),
        'correlator' => sanitize_key($correlator)
    ];

    $response = wp_remote_post($endpoint, [
        'method'    => 'POST',
        'body'      => wp_json_encode($sms_data),
        'headers'   => $headers,
        'timeout'   => 30,
    ]);

    if (function_exists('rebuetext_log_sms')) {
        rebuetext_log_sms($sender_id, $phone, $message, $response);
    }
}

// Save the CF7 settings on form save
add_action('wpcf7_save_contact_form', 'rebuetext_cf7_sms_save_settings');
function rebuetext_cf7_sms_save_settings($cf7)
{
    // Nonce check for CF7 form save using proper sanitization
    $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
    if (!wp_verify_nonce($nonce, 'wpcf7_save_contact_form_' . $cf7->id())) {
        return;
    }

    if (!isset($_POST['wpcf7si-settings']) || !is_array($_POST['wpcf7si-settings'])) return;

    $form_id = $cf7->id();

    // Use our recursive sanitizer so arrays (like checkboxes and WA vars) are saved properly!
    $settings = rebuetext_sanitize_array($_POST['wpcf7si-settings']);

    // Prefixed option name correctly
    update_option('rebuetext_cf7_sms_data_' . $form_id, $settings);
}

// Send Omnichannel Notifications After for CF7 Form Submission
add_action('wpcf7_mail_sent', 'rebuetext_cf7_send_sms_after_submission');
function rebuetext_cf7_send_sms_after_submission($form)
{
    $form_id = method_exists($form, 'id') ? $form->id() : $form->id;
    $submission = WPCF7_Submission::get_instance();

    if (!$submission) return;

    $posted_data = $submission->get_posted_data();

    // Retrieve per-form settings
    $options = get_option('rebuetext_cf7_sms_data_' . $form_id, []);
    $default_options = [
        'phone'               => get_option('rebuetext_admin_phone', ''),
        'message'             => '',
        'visitorNumber'       => '',
        'visitorMessage'      => '',
        'admin_channels'      => [], // e.g. ['sms', 'whatsapp']
        'visitor_channels'    => [],
        'wa_admin_template'   => '',
        'wa_admin_vars'       => [],
        'wa_visitor_template' => '',
        'wa_visitor_vars'     => []
    ];

    $config = wp_parse_args($options, $default_options);
    $wa_sender = get_option('rebuetext_wa_phone'); // Configured in general settings


    /* =========================================================
     * 1. ADMIN NOTIFICATIONS
     * ========================================================= */
    $admin_number = rebuetext_cf7_parse_tags($config['phone'], $form, $posted_data);

    if (!empty($admin_number)) {

        // A. Send Admin SMS
        if (in_array('sms', $config['admin_channels']) && !empty($config['message'])) {
            $admin_message = rebuetext_cf7_parse_tags($config['message'], $form, $posted_data);
            rebuetext_send_sms($admin_number, $admin_message, 'cf7_admin_' . $form_id);
        }

        // B. Send Admin WhatsApp
        if (in_array('whatsapp', $config['admin_channels']) && !empty($wa_sender) && !empty($config['wa_admin_template'])) {
            $parts = explode('|', $config['wa_admin_template']);
            $tpl_name = $parts[0];
            $tpl_lang = $parts[1] ?? 'en_US';

            // Parse CF7 tags inside the WhatsApp array variables
            $parsed_vars = [];
            if (!empty($config['wa_admin_vars'])) {
                foreach ($config['wa_admin_vars'] as $raw_var) {
                    $parsed_vars[] = rebuetext_cf7_parse_tags($raw_var, $form, $posted_data);
                }
            }

            rebuetext_send_whatsapp_template(
                $wa_sender,
                $admin_number,
                $tpl_name,
                $tpl_lang,
                $parsed_vars,
                null,
                'cf7_wa_admin_' . $form_id
            );
        }
    }


    /* =========================================================
     * 2. VISITOR NOTIFICATIONS
     * ========================================================= */
    $visitor_number = rebuetext_cf7_parse_tags($config['visitorNumber'], $form, $posted_data);

    if (!empty($visitor_number)) {

        // A. Send Visitor SMS
        if (in_array('sms', $config['visitor_channels']) && !empty($config['visitorMessage'])) {
            $visitor_message = rebuetext_cf7_parse_tags($config['visitorMessage'], $form, $posted_data);
            rebuetext_send_sms($visitor_number, $visitor_message, 'cf7_visitor_' . $form_id);
        }

        // B. Send Visitor WhatsApp
        if (in_array('whatsapp', $config['visitor_channels']) && !empty($wa_sender) && !empty($config['wa_visitor_template'])) {
            $parts = explode('|', $config['wa_visitor_template']);
            $tpl_name = $parts[0];
            $tpl_lang = $parts[1] ?? 'en_US';

            // Parse CF7 tags inside the WhatsApp array variables
            $parsed_vars = [];
            if (!empty($config['wa_visitor_vars'])) {
                foreach ($config['wa_visitor_vars'] as $raw_var) {
                    $parsed_vars[] = rebuetext_cf7_parse_tags($raw_var, $form, $posted_data);
                }
            }

            rebuetext_send_whatsapp_template(
                $wa_sender,
                $visitor_number,
                $tpl_name,
                $tpl_lang,
                $parsed_vars,
                null,
                'cf7_wa_visitor_' . $form_id
            );
        }
    }
}

// Helper function to replace tags
function rebuetext_cf7_parse_tags($template, $form, $posted_data)
{
    // Replace CF7 tags manually
    if (preg_match_all('/\[(.+?)\]/', $template, $matches)) {
        foreach ($matches[1] as $tag) {
            $replacement = isset($posted_data[$tag]) ? sanitize_text_field($posted_data[$tag]) : '';
            $template = str_replace("[$tag]", $replacement, $template);
        }
    }
    return trim($template);
}

add_action('admin_init', 'rebuetext_register_gf_settings');

function rebuetext_register_gf_settings()
{
    // Register settings with sanitization callbacks
    register_setting(
        'rebuetext_gf_settings',
        'rebuetext_gf_admin_message',
        ['sanitize_callback' => 'sanitize_textarea_field']
    );
    register_setting(
        'rebuetext_gf_settings',
        'rebuetext_gf_visitor_message',
        ['sanitize_callback' => 'sanitize_textarea_field']
    );

    add_settings_section('rebuetext_gf_main_section', 'SMS Templates', null, 'rebuetext-gf-integrations');

    add_settings_field('admin_message', 'Admin SMS Template', function () {
        echo '<textarea name="rebuetext_gf_admin_message" rows="3" style="width:100%">' . esc_textarea(get_option('rebuetext_gf_admin_message')) . '</textarea>';
    }, 'rebuetext-gf-integrations', 'rebuetext_gf_main_section');

    add_settings_field('visitor_message', 'Visitor SMS Template', function () {
        echo '<textarea name="rebuetext_gf_visitor_message" rows="3" style="width:100%">' . esc_textarea(get_option('rebuetext_gf_visitor_message')) . '</textarea>';
    }, 'rebuetext-gf-integrations', 'rebuetext_gf_main_section');
}

// Send SMS for Gravity forms
add_action('gform_after_submission', 'rebuetext_gf_send_sms_on_submit', 10, 2);
function rebuetext_gf_send_sms_on_submit($entry, $form)
{
    $form_id = $form['id'] ?? null;
    if (!$form_id) {
        return;
    }

    // Fetch saved settings
    $settings = get_option("rebuetext_gf_form_settings_{$form_id}", []);
    if (empty($settings['enabled'])) {
        return;
    }

    $visitor_message_template = $settings['visitor_message'] ?? '';
    $admin_message_template   = $settings['admin_message'] ?? '';
    $phone_field_id           = $settings['phone_field_id'] ?? '';

    // Fetch visitor phone from entry using field ID
    $phone_number = rgar($entry, $phone_field_id);
    if (!$phone_number) {
        return;
    }

    // Sanitize phone number
    $phone_number = sanitize_text_field($phone_number);

    // Replace merge tags in messages
    $visitor_message = GFCommon::replace_variables($visitor_message_template, $form, $entry);
    $admin_message   = GFCommon::replace_variables($admin_message_template, $form, $entry);

    // Sanitize messages
    $visitor_message = sanitize_text_field($visitor_message);
    $admin_message   = sanitize_text_field($admin_message);

    // Send visitor SMS
    if (!empty($visitor_message)) {
        rebuetext_send_sms($phone_number, $visitor_message, "gf_form_{$form_id}");
    }

    // Send admin SMS
    $admin_phone = sanitize_text_field(get_option('rebuetext_admin_phone'));
    if (!empty($admin_phone) && !empty($admin_message)) {
        rebuetext_send_sms($admin_phone, $admin_message, "gf_admin_{$form_id}");
    }
}
