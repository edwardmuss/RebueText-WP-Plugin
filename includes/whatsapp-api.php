<?php
if (!defined('ABSPATH')) exit;

/**
 * Fetch synchronized WhatsApp templates from the configured environment.
 */
function rebuetext_fetch_whatsapp_templates()
{
    $api_token = get_option('rebuetext_api_token');
    if (!$api_token) return [];

    // Cache responses for 10 minutes to protect your Laravel server from over-pings
    $cached = get_transient('rebuetext_wa_templates_cache');
    if ($cached !== false) {
        return $cached;
    }

    $endpoint = rebuetext_get_api_base_url() . '/whatsapp/templates?status=APPROVED';

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_token,
            'Accept'        => 'application/json',
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $templates = isset($body['status']) && $body['status'] ? $body['data'] : [];

    set_transient('rebuetext_wa_templates_cache', $templates, 10 * MINUTE_IN_SECONDS);

    return $templates;
}

/**
 * Dispatches a payload block to the WhatsApp Template Delivery Engine.
 */
function rebuetext_send_whatsapp_template($sender_phone, $recipient_phone, $template_name, $language, $body_vars = [], $header = null, $correlator = '')
{
    $api_token = get_option('rebuetext_api_token');
    if (!$api_token) return false;

    $endpoint = rebuetext_get_api_base_url() . '/whatsapp/send-template';

    // Structure array-first batch frame to match the API requirements
    $payload = [
        [
            'sender_phone'  => $sender_phone,
            'phone'         => $recipient_phone,
            'template_name' => $template_name,
            'language'      => $language,
            'correlator'    => $correlator,
        ]
    ];

    if (!empty($body_vars)) {
        $payload[0]['body_variables'] = $body_vars;
    }

    if (!empty($header)) {
        $payload[0]['header'] = $header;
    }

    $response = wp_remote_post($endpoint, [
        'method'    => 'POST',
        'body'      => wp_json_encode($payload),
        'headers'   => [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $api_token,
        ],
        'timeout'   => 30,
    ]);

    // Re-use your existing logging function to display outcomes inside the log viewer!
    if (function_exists('rebuetext_log_sms')) {
        $msg_preview = "[WhatsApp Template: {$template_name}] " . implode(', ', $body_vars);
        rebuetext_log_sms("WA: " . $sender_phone, $recipient_phone, $msg_preview, $response);
    }

    return $response;
}
