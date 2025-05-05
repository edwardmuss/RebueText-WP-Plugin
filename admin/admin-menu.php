<?php
function rebuetext_admin_menu()
{
    // Main Menu
    add_menu_page(
        'Rebuetext SMS Settings',   // Page Title
        'Rebuetext SMS',            // Menu Title
        'manage_options',           // Capability
        'rebuetext-settings',       // Menu Slug
        'rebuetext_settings_page',  // Function to Display Page
        'dashicons-email',          // Icon
        25                          // Position
    );

    // SMS Settings Submenu
    add_submenu_page(
        'rebuetext-settings', // Parent slug
        'Woo SMS Integrations',
        'Woo SMS Integrations',
        'manage_options',
        'rebuetext-settings',
        'rebuetext_settings_page'
    );

    // CF7 SMS Settings Submenu Under RebueText
    add_submenu_page(
        'rebuetext-settings',
        'CF7 Form Integrations',
        'CF7 Form Integrations',
        'manage_options',
        'rebuetext-form-integrations',
        'rebuetext_form_integrations_page'
    );

    // Add the submenu page that will redirect
    add_submenu_page(
        'rebuetext-settings', // Parent slug (your main plugin menu)
        'Gravity Forms Integration', // Page title
        'Gravity Forms', // Menu title (you can shorten it for the menu)
        'manage_options', // Capability
        'rebuetext-gf-integrations-redirect', // **Use a different slug for the redirect page**
        'rebuetext_gravityforms_redirect_to_gf' // Callback function for redirection
    );

    // SMS Logs Submenu
    add_submenu_page(
        'rebuetext-settings',
        'SMS Logs',
        'SMS Logs',
        'manage_options',
        'rebuetext-sms-logs',
        'rebuetext_sms_logs_page'
    );

    // SMS Balance
    add_submenu_page(
        'rebuetext-settings',
        'SMS Balance',
        'SMS Balance',
        'manage_options',
        'rebuetext-sms-balance',
        'rebuetext_sms_balance_page'
    );

    // Under Woocommerce menu
    add_submenu_page(
        'woocommerce',
        'SMS Logs',
        'SMS Logs',
        'manage_options',
        'rebuetext-sms-logs',
        'rebuetext_sms_logs_page'
    );

    // CF7 SMS Settings Submenu Under CF7
    add_submenu_page(
        'wpcf7',
        'RebueText Integrations',
        'RebueText Integrations',
        'manage_options',
        'rebuetext-form-integrations',
        'rebuetext_form_integrations_page'
    );

    // show an extra tab in the Contact Form 7 form editor UI
    add_filter('wpcf7_editor_panels', 'rebuetext_cf7_add_sms_tab');
    function rebuetext_cf7_add_sms_tab($panels)
    {
        $panels['rebuetext-sms-panel'] = [
            'title' => __('Rebuetext SMS', 'rebuetext_cf7_sms'),
            'callback' => 'rebuetext_cf7_sms_panel_callback'
        ];
        return $panels;
    }

    // Hook into the Gravity Forms Settings UI
    add_filter('gform_form_settings_menu', 'rebuetext_add_sms_tab_to_gf');
    function rebuetext_add_sms_tab_to_gf($tabs)
    {
        $tabs[] = [
            'name'  => 'rebuetext_sms',
            'label' => 'RebueText SMS',
            'icon'  => 'dashicons-email'
        ];
        return $tabs;
    }
}

add_action('admin_menu', 'rebuetext_admin_menu');
