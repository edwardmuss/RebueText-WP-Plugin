<?php

if (!defined('ABSPATH')) exit;

function rebuetext_send_order_sms($order_id, $old_status, $new_status)
{
    $status_key = 'wc-' . $new_status;
    $enabled_statuses = get_option('rebuetext_enabled_statuses', []);

    // Check if this status is enabled at all
    if (!in_array($status_key, $enabled_statuses)) {
        return;
    }

    $order = wc_get_order($order_id);

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
        'date_created'          => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '',
        'date_modified'         => $order->get_date_modified() ? $order->get_date_modified()->date('Y-m-d H:i:s') : '',
        'date_completed'        => $order->get_date_completed() ? $order->get_date_completed()->date('Y-m-d H:i:s') : '',
        'date_paid'             => $order->get_date_paid() ? $order->get_date_paid()->date('Y-m-d H:i:s') : '',
        'order_id'              => $order->get_id(),
        'order_number'          => $order->get_order_number(),
        'order_total'           => $order->get_total(),
        'order_discount'        => $order->get_discount_total(),
        'order_currency'        => $order->get_currency(),
        'status'                => $order->get_status()
    ];

    // Fetch routing settings
    $channels    = get_option('rebuetext_channels', []);
    $wa_mappings = get_option('rebuetext_wa_mappings', []);
    $wa_sender   = get_option('rebuetext_wa_phone'); // New WA Phone Number

    // Fallback to SMS if channels array is empty (for backward compatibility with old settings)
    $customer_channels = $channels[$status_key]['customer'] ?? ['sms'];
    $admin_channels    = $channels[$status_key]['admin'] ?? ['sms'];


    /* =========================================================
     * 1. CUSTOMER NOTIFICATIONS
     * ========================================================= */
    if ($billing_phone) {

        // A. Send Customer SMS
        if (in_array('sms', $customer_channels)) {
            $customer_templates = get_option('rebuetext_customer_templates', []);
            $customer_message   = $customer_templates[$status_key] ?? '';

            if (!empty($customer_message)) {
                foreach ($values as $key => $value) {
                    $customer_message = str_replace("{{$key}}", $value, $customer_message);
                }
                rebuetext_send_sms($billing_phone, $customer_message, 'order_' . $order_id);
            }
        }

        // B. Send Customer WhatsApp
        if (in_array('whatsapp', $customer_channels) && !empty($wa_sender)) {
            $wa_config = $wa_mappings[$status_key]['customer'] ?? null;

            if ($wa_config && !empty($wa_config['template'])) {
                $parts = explode('|', $wa_config['template']);
                $tpl_name = $parts[0];
                $tpl_lang = $parts[1] ?? 'en_US';

                // Parse the dynamic variables
                $parsed_vars = [];
                if (!empty($wa_config['vars'])) {
                    foreach ($wa_config['vars'] as $raw_var) {
                        $parsed_var = $raw_var;
                        foreach ($values as $key => $value) {
                            $parsed_var = str_replace("{{$key}}", $value, $parsed_var);
                        }
                        $parsed_vars[] = $parsed_var;
                    }
                }

                // Dispatch to WA API
                rebuetext_send_whatsapp_template(
                    $wa_sender,
                    $billing_phone,
                    $tpl_name,
                    $tpl_lang,
                    $parsed_vars,
                    null, // header (can be added later if needed)
                    'wa_order_' . $order_id
                );
            }
        }
    }


    /* =========================================================
     * 2. ADMIN NOTIFICATIONS
     * ========================================================= */
    if ($admin_phone) {

        // A. Send Admin SMS
        if (in_array('sms', $admin_channels)) {
            $admin_templates = get_option('rebuetext_admin_templates', []);
            $admin_message   = $admin_templates[$status_key] ?? '';

            if (!empty($admin_message)) {
                foreach ($values as $key => $value) {
                    $admin_message = str_replace("{{$key}}", $value, $admin_message);
                }
                rebuetext_send_sms($admin_phone, $admin_message, 'order_admin_' . $order_id);
            }
        }

        // B. Send Admin WhatsApp
        if (in_array('whatsapp', $admin_channels) && !empty($wa_sender)) {
            $wa_config = $wa_mappings[$status_key]['admin'] ?? null;

            if ($wa_config && !empty($wa_config['template'])) {
                $parts = explode('|', $wa_config['template']);
                $tpl_name = $parts[0];
                $tpl_lang = $parts[1] ?? 'en_US';

                // Parse the dynamic variables
                $parsed_vars = [];
                if (!empty($wa_config['vars'])) {
                    foreach ($wa_config['vars'] as $raw_var) {
                        $parsed_var = $raw_var;
                        foreach ($values as $key => $value) {
                            $parsed_var = str_replace("{{$key}}", $value, $parsed_var);
                        }
                        $parsed_vars[] = $parsed_var;
                    }
                }

                // Dispatch to WA API
                rebuetext_send_whatsapp_template(
                    $wa_sender,
                    $admin_phone,
                    $tpl_name,
                    $tpl_lang,
                    $parsed_vars,
                    null,
                    'wa_order_admin_' . $order_id
                );
            }
        }
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
