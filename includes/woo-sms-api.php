<?php

if (!defined('ABSPATH')) exit;

function rebuetext_send_order_sms($order_id, $old_status, $new_status)
{
    $enabled_statuses = get_option('rebuetext_enabled_statuses', []);
    if (!in_array('wc-' . $new_status, $enabled_statuses)) {
        return;
    }

    $order = wc_get_order($order_id);
    $api_token = get_option('rebuetext_api_token');
    $sender_id = get_option('rebuetext_sender_id');
    $admin_phone = get_option('rebuetext_admin_phone', '');
    $billing_phone = $order->get_billing_phone();

    // All available merge tags
    $values = [
        'billing_first_name'    => $order->get_billing_first_name(),
        'billing_last_name'     => $order->get_billing_last_name(),
        'billing_company'       => $order->get_billing_company(),
        'billing_address'       => $order->get_billing_address_1(),
        'billing_country'       => $order->get_shipping_country(),
        'billing_city'          => $order->get_billing_city(),
        'billing_state'         => $order->get_billing_state(),
        'billing_email'         => $order->get_billing_email(),
        'billing_phone'         => $order->get_billing_phone(),
        'payment_method'        => $order->get_payment_method(),
        'payment_method_title'  => $order->get_payment_method_title(),
        'date_created'          => $order->get_date_created(),
        'date_modified'         => $order->get_date_modified(),
        'date_completed'        => $order->get_date_completed(),
        'date_paid'             => $order->get_date_paid(),
        'order_id'              => $order->get_id(),
        'order_number'          => $order->get_order_number(),
        'order_total'           => $order->get_total(),
        'order_discount'        => $order->get_discount_total(),
        'order_currency'        => $order->get_currency(),
        'status'                => $order->get_status()
    ];

    $customer_templates = get_option('rebuetext_customer_templates', []);
    $admin_templates = get_option('rebuetext_admin_templates', []);

    $customer_message = isset($customer_templates['wc-' . $new_status]) ? $customer_templates['wc-' . $new_status] : '';
    $admin_message = isset($admin_templates['wc-' . $new_status]) ? $admin_templates['wc-' . $new_status] : '';

    foreach ($values as $key => $value) {
        $customer_message = str_replace("{{$key}}", $value, $customer_message);
        $admin_message = str_replace("{{$key}}", $value, $admin_message);
    }

    $headers = [
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
        'Authorization' => 'Bearer ' . $api_token,
    ];

    $customer_sms_data = [
        'sender'     => $sender_id,
        'message'    => $customer_message,
        'phone'      => $billing_phone,
        'correlator' => 'order_' . $order_id
    ];

    $response = wp_remote_post('https://rebuetext.com/api/v1/send-sms', [
        'method'    => 'POST',
        'body'      => json_encode($customer_sms_data),
        'headers'   => $headers,
        'timeout'   => 30,
    ]);

    // Log SMS Response
    rebuetext_log_sms($sender_id, $billing_phone, $customer_message, $response);

    if (!empty($admin_phone) && $admin_message) {
        $admin_sms_data = [
            'sender'     => $sender_id,
            'message'    => $admin_message,
            'phone'      => $admin_phone,
            'correlator' => 'order_admin_' . $order_id
        ];

        $response = wp_remote_post('https://rebuetext.com/api/v1/send-sms', [
            'method'    => 'POST',
            'body'      => json_encode($admin_sms_data),
            'headers'   => $headers,
            'timeout'   => 30,
        ]);

        rebuetext_log_sms($sender_id, $admin_phone, $admin_message, $response);
    }
}

function rebuetext_log_sms($sender_id, $phone, $message, $response)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rebuetext_sms_logs';

    $status = is_wp_error($response) ? 'Failed' : 'Sent';
    $response_body = !is_wp_error($response) ? wp_remote_retrieve_body($response) : $response->get_error_message();

    $wpdb->insert($table_name, [
        'phone'    => $phone,
        'message'  => $message,
        'sender'   => $sender_id,
        'status'   => $status,
        'response' => $response_body,
        'sent_at'  => current_time('mysql')
    ]);
}
