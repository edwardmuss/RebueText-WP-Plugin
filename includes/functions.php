<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly
if (is_admin()) {
    add_action('wp_ajax_save_rebuetext_settings', 'save_rebuetext_settings');
    add_action('admin_init', 'rebuetext_register_form_sms_settings');

    function save_rebuetext_settings()
    {
        check_ajax_referer('rebuetext_nonce', 'security');

        parse_str($_POST['data'], $settings_data);

        update_option('rebuetext_api_token', sanitize_text_field($settings_data['rebuetext_api_token']));
        update_option('rebuetext_sender_id', sanitize_text_field($settings_data['rebuetext_sender_id']));
        update_option('rebuetext_admin_phone', sanitize_text_field($settings_data['rebuetext_admin_phone']));
        update_option('rebuetext_enabled_statuses', isset($settings_data['rebuetext_enabled_statuses']) ? array_map('sanitize_text_field', $settings_data['rebuetext_enabled_statuses']) : []);
        update_option('rebuetext_customer_templates', isset($settings_data['rebuetext_customer_templates']) ? array_map('sanitize_textarea_field', $settings_data['rebuetext_customer_templates']) : []);
        update_option('rebuetext_admin_templates', isset($settings_data['rebuetext_admin_templates']) ? array_map('sanitize_textarea_field', $settings_data['rebuetext_admin_templates']) : []);

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
}

// SMS function
function rebuetext_send_sms($phone, $message, $correlator = 'custom')
{
    $api_token = sanitize_text_field(get_option('rebuetext_api_token'));
    $sender_id = sanitize_text_field(get_option('rebuetext_sender_id'));

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

    $response = wp_remote_post('https://rebuetext.com/api/v1/send-sms', [
        'method'    => 'POST',
        'body'      => wp_json_encode($sms_data),
        'headers'   => $headers,
        'timeout'   => 30,
    ]);

    rebuetext_log_sms($sender_id, $phone, $message, $response);
}

// Save the CF7 settings on form save
add_action('wpcf7_save_contact_form', 'rebuetext_cf7_sms_save_settings');
function rebuetext_cf7_sms_save_settings($cf7)
{
    if (!isset($_POST['wpcf7si-settings'])) return;

    $form_id = $cf7->id();
    $settings = array_map('sanitize_text_field', $_POST['wpcf7si-settings']);

    update_option('wpcf7_rebuetext_sms_' . $form_id, $settings);
}

// Send SMS After for CF7 Form Submission
add_action('wpcf7_mail_sent', 'rebuetext_cf7_send_sms_after_submission');
function rebuetext_cf7_send_sms_after_submission($form)
{
    $form_id = method_exists($form, 'id') ? $form->id() : $form->id;
    $submission = WPCF7_Submission::get_instance();

    if (!$submission) return;

    $posted_data = $submission->get_posted_data();

    // Retrieve per-form settings or fallback to defaults
    $options = get_option('wpcf7_rebuetext_sms_' . $form_id, []);
    $default_options = [
        'phone' => sanitize_text_field(get_option('rebuetext_admin_phone', '')),  // fallback admin number
        'message' => 'New form submitted: [your-name] - [your-message]',
        'visitorNumber' => '[your-phone]',                  // fallback visitor number tag
        'visitorMessage' => 'Thank you [your-name], we received your message.'
    ];

    $config = wp_parse_args($options, $default_options);

    // Parse message fields
    $admin_number     = rebuetext_cf7_parse_tags($config['phone'], $form, $posted_data);
    $admin_message    = rebuetext_cf7_parse_tags($config['message'], $form, $posted_data);
    $visitor_number   = rebuetext_cf7_parse_tags($config['visitorNumber'], $form, $posted_data);
    $visitor_message  = rebuetext_cf7_parse_tags($config['visitorMessage'], $form, $posted_data);

    // Send to admin
    if (!empty($admin_number) && !empty($admin_message)) {
        rebuetext_send_sms($admin_number, $admin_message, 'cf7_admin_' . $form_id);
    }

    // Send to visitor
    if (!empty($visitor_number) && !empty($visitor_message)) {
        rebuetext_send_sms($visitor_number, $visitor_message, 'cf7_visitor_' . $form_id);
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
