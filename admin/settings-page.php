<?php
if (!defined('ABSPATH')) exit;

function rebuetext_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'rebuetext'));
    }

    $statuses = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [];
    $enabled_statuses = get_option('rebuetext_enabled_statuses', []);
    $customer_templates = get_option('rebuetext_customer_templates', []);
    $admin_templates = get_option('rebuetext_admin_templates', []);
    $admin_phone = get_option('rebuetext_admin_phone', '');

    $api_env = get_option('rebuetext_api_env', 'production');
    $channels = get_option('rebuetext_channels', []);
    $wa_mappings = get_option('rebuetext_wa_mappings', []);
    $wa_templates = function_exists('rebuetext_fetch_whatsapp_templates') ? rebuetext_fetch_whatsapp_templates() : []
?>
    <div class="wrap">
        <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

        <h1 class="wp-heading-inline"><?php esc_html_e('Rebuetext SMS Settings', 'rebuetext'); ?></h1>
        <hr class="wp-header-end">

        <div id="rebuetext-settings-container" class="container-fluid px-0">
            <form id="rebuetext-settings-form">
                <?php wp_nonce_field('rebuetext_nonce', 'rebuetext_ajax_nonce'); ?>

                <div class="card mb-4 w-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><?php esc_html_e('API Configuration', 'rebuetext'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="rebuetext_api_token" class="form-label"><?php esc_html_e('API Token', 'rebuetext'); ?></label>
                                <input type="password" class="form-control" name="rebuetext_api_token"
                                    value="<?php echo esc_attr(get_option('rebuetext_api_token')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rebuetext_sender_id" class="form-label"><?php esc_html_e('SMS Sender ID', 'rebuetext'); ?></label>
                                <input type="text" class="form-control" name="rebuetext_sender_id"
                                    value="<?php echo esc_attr(get_option('rebuetext_sender_id')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rebuetext_wa_phone" class="form-label text-success"><i class="dashicons dashicons-whatsapp"></i> <?php esc_html_e('WhatsApp Sender Phone', 'rebuetext'); ?></label>
                                <input type="text" class="form-control" name="rebuetext_wa_phone"
                                    value="<?php echo esc_attr(get_option('rebuetext_wa_phone')); ?>" placeholder="e.g. 254741226412">
                            </div>
                            <div class="col-md-6">
                                <label for="rebuetext_admin_phone" class="form-label"><?php esc_html_e('Admin Phone Number', 'rebuetext'); ?></label>
                                <input type="text" class="form-control" name="rebuetext_admin_phone"
                                    value="<?php echo esc_attr($admin_phone); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rebuetext_api_env" class="form-label"><?php esc_html_e('API Environment', 'rebuetext'); ?></label>
                                <select class="form-select" name="rebuetext_api_env" id="rebuetext_api_env">
                                    <option value="production" <?php selected($api_env, 'production'); ?>><?php esc_html_e('Production (rebuetext.com)', 'rebuetext'); ?></option>
                                    <option value="local" <?php selected($api_env, 'local'); ?>><?php esc_html_e('Local Development (127.0.0.1)', 'rebuetext'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($statuses)): ?>
                    <div class="card mb-4 w-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><?php esc_html_e('Enable SMS for Statuses', 'rebuetext'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="toggle-all-statuses">
                                    <?php esc_html_e('Select All', 'rebuetext'); ?>
                                </button>
                            </div>
                            <div class="row g-3">
                                <?php foreach ($statuses as $key => $status) { ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-check form-switch rebuetext-switch-group" data-bs-toggle="tooltip" title="<?php echo esc_attr(sprintf(__('Enable SMS for %s', 'rebuetext'), $status)); ?>">
                                            <input class="form-check-input status-switch" type="checkbox" role="switch"
                                                id="status_<?php echo esc_attr($key); ?>"
                                                name="rebuetext_enabled_statuses[]"
                                                value="<?php echo esc_attr($key); ?>"
                                                <?php echo in_array($key, $enabled_statuses) ? 'checked' : ''; ?>>
                                            <label class="form-check-label d-flex align-items-center gap-2" for="status_<?php echo esc_attr($key); ?>">
                                                <span><?php echo esc_html($status); ?></span>
                                                <span class="status-icon"><?php echo in_array($key, $enabled_statuses) ? '✅' : '❌'; ?></span>
                                            </label>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 w-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><?php esc_html_e('SMS Templates', 'rebuetext'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="templatesAccordion">
                                <?php foreach ($statuses as $key => $status) { ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo esc_attr($key); ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?php echo esc_attr($key); ?>"
                                                aria-expanded="false" aria-controls="collapse<?php echo esc_attr($key); ?>">
                                                <?php echo esc_html($status); ?>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo esc_attr($key); ?>" class="accordion-collapse collapse"
                                            aria-labelledby="heading<?php echo esc_attr($key); ?>"
                                            data-bs-parent="#templatesAccordion">
                                            <div class="accordion-body bg-white">

                                                <div class="p-3 mb-3 border rounded">
                                                    <h6 class="fw-bold text-primary mb-3">Customer Notification</h6>
                                                    <div class="mb-3 pb-2 border-bottom">
                                                        <label class="form-check form-check-inline">
                                                            <input class="form-check-input channel-toggle" type="checkbox"
                                                                name="rebuetext_channels[<?php echo esc_attr($key); ?>][customer][]" value="sms"
                                                                data-target="sms-config-<?php echo esc_attr($key); ?>-customer"
                                                                <?php echo in_array('sms', $channels[$key]['customer'] ?? ['sms']) ? 'checked' : ''; ?>>
                                                            <?php esc_html_e('Send SMS', 'rebuetext'); ?>
                                                        </label>
                                                        <label class="form-check form-check-inline">
                                                            <input class="form-check-input channel-toggle" type="checkbox"
                                                                name="rebuetext_channels[<?php echo esc_attr($key); ?>][customer][]" value="whatsapp"
                                                                data-target="wa-config-<?php echo esc_attr($key); ?>-customer"
                                                                <?php echo in_array('whatsapp', $channels[$key]['customer'] ?? []) ? 'checked' : ''; ?>>
                                                            <?php esc_html_e('Send WhatsApp', 'rebuetext'); ?>
                                                        </label>
                                                    </div>

                                                    <div id="sms-config-<?php echo esc_attr($key); ?>-customer" class="mb-3 channel-config">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-muted small fw-bold mb-0"><i class="dashicons dashicons-email-alt"></i> <?php esc_html_e('SMS Template', 'rebuetext'); ?></label>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <span class="dashicons dashicons-tag" style="line-height: 1.5; font-size: 14px; width: 14px; height: 14px;"></span> Insert Tag
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow sms-tag-dropdown" style="max-height: 250px; overflow-y: auto;">
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <textarea class="form-control" rows="2" name="rebuetext_customer_templates[<?php echo esc_attr($key); ?>]"><?php echo isset($customer_templates[$key]) ? esc_textarea($customer_templates[$key]) : ''; ?></textarea>
                                                    </div>

                                                    <div id="wa-config-<?php echo esc_attr($key); ?>-customer" class="mb-3 channel-config p-3 bg-light rounded border">
                                                        <label class="form-label text-success small fw-bold"><i class="dashicons dashicons-whatsapp"></i> <?php esc_html_e('WhatsApp Template', 'rebuetext'); ?></label>
                                                        <?php
                                                        $selected_wa_tpl = $wa_mappings[$key]['customer']['template'] ?? '';
                                                        $saved_vars = wp_json_encode($wa_mappings[$key]['customer']['vars'] ?? []);
                                                        ?>
                                                        <select class="form-select wa-template-selector mb-3"
                                                            name="rebuetext_wa_mappings[<?php echo esc_attr($key); ?>][customer][template]"
                                                            data-container="wa-vars-<?php echo esc_attr($key); ?>-customer"
                                                            data-saved-vars='<?php echo esc_attr($saved_vars); ?>'>
                                                            <option value="">-- <?php esc_html_e('Select an Approved Template', 'rebuetext'); ?> --</option>
                                                            <?php foreach ($wa_templates as $tpl): ?>
                                                                <option value="<?php echo esc_attr($tpl['name'] . '|' . $tpl['language']); ?>" <?php selected($selected_wa_tpl, $tpl['name'] . '|' . $tpl['language']); ?>>
                                                                    <?php echo esc_html($tpl['name'] . ' (' . $tpl['language'] . ')'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div id="wa-vars-<?php echo esc_attr($key); ?>-customer" class="wa-vars-container row g-2"></div>
                                                    </div>
                                                </div>

                                                <div class="p-3 border rounded bg-light">
                                                    <h6 class="fw-bold text-dark mb-3">Admin Notification</h6>
                                                    <div class="mb-3 pb-2 border-bottom">
                                                        <label class="form-check form-check-inline">
                                                            <input class="form-check-input channel-toggle" type="checkbox"
                                                                name="rebuetext_channels[<?php echo esc_attr($key); ?>][admin][]" value="sms"
                                                                data-target="sms-config-<?php echo esc_attr($key); ?>-admin"
                                                                <?php echo in_array('sms', $channels[$key]['admin'] ?? ['sms']) ? 'checked' : ''; ?>>
                                                            <?php esc_html_e('Send SMS', 'rebuetext'); ?>
                                                        </label>
                                                        <label class="form-check form-check-inline">
                                                            <input class="form-check-input channel-toggle" type="checkbox"
                                                                name="rebuetext_channels[<?php echo esc_attr($key); ?>][admin][]" value="whatsapp"
                                                                data-target="wa-config-<?php echo esc_attr($key); ?>-admin"
                                                                <?php echo in_array('whatsapp', $channels[$key]['admin'] ?? []) ? 'checked' : ''; ?>>
                                                            <?php esc_html_e('Send WhatsApp', 'rebuetext'); ?>
                                                        </label>
                                                    </div>

                                                    <div id="sms-config-<?php echo esc_attr($key); ?>-admin" class="mb-3 channel-config">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label text-muted small fw-bold mb-0"><i class="dashicons dashicons-email-alt"></i> <?php esc_html_e('Admin SMS Template', 'rebuetext'); ?></label>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <span class="dashicons dashicons-tag" style="line-height: 1.5; font-size: 14px; width: 14px; height: 14px;"></span> Insert Tag
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow sms-tag-dropdown" style="max-height: 250px; overflow-y: auto;">
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <textarea class="form-control" rows="2" name="rebuetext_admin_templates[<?php echo esc_attr($key); ?>]"><?php echo isset($admin_templates[$key]) ? esc_textarea($admin_templates[$key]) : ''; ?></textarea>
                                                    </div>

                                                    <div id="wa-config-<?php echo esc_attr($key); ?>-admin" class="mb-3 channel-config p-3 bg-white rounded border">
                                                        <label class="form-label text-success small fw-bold"><i class="dashicons dashicons-whatsapp"></i> <?php esc_html_e('Admin WhatsApp Template', 'rebuetext'); ?></label>
                                                        <?php
                                                        $selected_wa_tpl_admin = $wa_mappings[$key]['admin']['template'] ?? '';
                                                        $saved_vars_admin = wp_json_encode($wa_mappings[$key]['admin']['vars'] ?? []);
                                                        ?>
                                                        <select class="form-select wa-template-selector mb-3"
                                                            name="rebuetext_wa_mappings[<?php echo esc_attr($key); ?>][admin][template]"
                                                            data-container="wa-vars-<?php echo esc_attr($key); ?>-admin"
                                                            data-saved-vars='<?php echo esc_attr($saved_vars_admin); ?>'>
                                                            <option value="">-- <?php esc_html_e('Select an Approved Template', 'rebuetext'); ?> --</option>
                                                            <?php foreach ($wa_templates as $tpl): ?>
                                                                <option value="<?php echo esc_attr($tpl['name'] . '|' . $tpl['language']); ?>" <?php selected($selected_wa_tpl_admin, $tpl['name'] . '|' . $tpl['language']); ?>>
                                                                    <?php echo esc_html($tpl['name'] . ' (' . $tpl['language'] . ')'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div id="wa-vars-<?php echo esc_attr($key); ?>-admin" class="wa-vars-container row g-2"></div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="status-message mb-3"></div>
                <button type="submit" id="save-settings-btn" class="btn btn-primary-2"><?php esc_html_e('Save Settings', 'rebuetext'); ?></button>
            </form>
        </div>
    </div>
<?php
}

function rebuetext_cf7_sms_panel_callback($form)
{
    $form_id = $form->id();

    // Fetch saved data with our new WhatsApp/Channel fields included
    $data = get_option('rebuetext_cf7_sms_data_' . $form_id, [
        'phone'               => '',
        'message'             => '',
        'visitorNumber'       => '',
        'visitorMessage'      => '',
        'admin_channels'      => ['sms'],
        'visitor_channels'    => ['sms'],
        'wa_admin_template'   => '',
        'wa_admin_vars'       => [],
        'wa_visitor_template' => '',
        'wa_visitor_vars'     => []
    ]);

    $wa_templates = function_exists('rebuetext_fetch_whatsapp_templates') ? rebuetext_fetch_whatsapp_templates() : [];
?>
    <div id="cf7si-sms-sortables" class="meta-box-sortables ui-sortable p-4">

        <div class="notice notice-info mb-4">
            <p><?php esc_html_e("You can use CF7 mail-tags like [your-name] or [your-phone] in any of the fields below.", "rebuetext"); ?></p>
            <p><strong>Available tags:</strong> <?php $form->suggest_mail_tags(); ?></p>
        </div>

        <div class="p-3 mb-4 border rounded bg-light">
            <h4 class="fw-bold text-dark mb-3"><?php esc_html_e("Admin Notifications", "rebuetext"); ?></h4>

            <table class="form-table mb-3">
                <tr>
                    <th><label class="fw-bold"><?php esc_html_e("To (Admin Phone):", "rebuetext"); ?></label></th>
                    <td>
                        <input type="text" name="wpcf7si-settings[phone]" class="regular-text" value="<?php echo esc_attr($data['phone']); ?>">
                        <p class="description"><?php esc_html_e("Use CF7 tags like [your-phone] or raw numbers.", "rebuetext"); ?></p>
                    </td>
                </tr>
            </table>

            <div class="mb-3 pb-2 border-bottom">
                <label class="form-check form-check-inline">
                    <input class="form-check-input channel-toggle" type="checkbox" name="wpcf7si-settings[admin_channels][]" value="sms" data-target="cf7-sms-admin" <?php echo in_array('sms', $data['admin_channels']) ? 'checked' : ''; ?>>
                    <?php esc_html_e('Send SMS', 'rebuetext'); ?>
                </label>
                <label class="form-check form-check-inline">
                    <input class="form-check-input channel-toggle" type="checkbox" name="wpcf7si-settings[admin_channels][]" value="whatsapp" data-target="cf7-wa-admin" <?php echo in_array('whatsapp', $data['admin_channels']) ? 'checked' : ''; ?>>
                    <?php esc_html_e('Send WhatsApp', 'rebuetext'); ?>
                </label>
            </div>

            <div id="cf7-sms-admin" class="mb-3 channel-config">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label text-muted small fw-bold mb-0"><i class="dashicons dashicons-email-alt"></i> <?php esc_html_e('SMS Message Body:', "rebuetext"); ?></label>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Insert Tag">
                            <span class="dashicons dashicons-tag" style="line-height: 1.5; font-size: 14px; width: 14px; height: 14px;"></span> Insert Tag
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow sms-tag-dropdown" style="max-height: 250px; overflow-y: auto;">
                        </ul>
                    </div>
                </div>
                <textarea name="wpcf7si-settings[message]" rows="4" class="form-control"><?php echo esc_textarea($data['message']); ?></textarea>
            </div>

            <div id="cf7-wa-admin" class="mb-3 channel-config p-3 bg-white rounded border">
                <label class="form-label text-success small fw-bold"><i class="dashicons dashicons-whatsapp"></i> <?php esc_html_e('WhatsApp Template:', "rebuetext"); ?></label>
                <?php $saved_vars_admin = wp_json_encode($data['wa_admin_vars']); ?>
                <select class="form-select wa-template-selector mb-3" name="wpcf7si-settings[wa_admin_template]" data-container="cf7-wa-vars-admin" data-input-name="wpcf7si-settings[wa_admin_vars][]" data-saved-vars='<?php echo esc_attr($saved_vars_admin); ?>'>
                    <option value="">-- <?php esc_html_e('Select an Approved Template', 'rebuetext'); ?> --</option>
                    <?php foreach ($wa_templates as $tpl): ?>
                        <option value="<?php echo esc_attr($tpl['name'] . '|' . $tpl['language']); ?>" <?php selected($data['wa_admin_template'], $tpl['name'] . '|' . $tpl['language']); ?>>
                            <?php echo esc_html($tpl['name'] . ' (' . $tpl['language'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="cf7-wa-vars-admin" class="wa-vars-container row g-2"></div>
            </div>
        </div>

        <div class="p-3 border rounded bg-light">
            <h4 class="fw-bold text-dark mb-3"><?php esc_html_e("Visitor Notifications", "rebuetext"); ?></h4>

            <table class="form-table mb-3">
                <tr>
                    <th><label class="fw-bold"><?php esc_html_e("To (Visitor Phone Field):", "rebuetext"); ?></label></th>
                    <td>
                        <input type="text" name="wpcf7si-settings[visitorNumber]" class="regular-text" value="<?php echo esc_attr($data['visitorNumber']); ?>">
                        <p class="description"><?php esc_html_e("Must be a CF7 tag like [your-phone].", "rebuetext"); ?></p>
                    </td>
                </tr>
            </table>

            <div class="mb-3 pb-2 border-bottom">
                <label class="form-check form-check-inline">
                    <input class="form-check-input channel-toggle" type="checkbox" name="wpcf7si-settings[visitor_channels][]" value="sms" data-target="cf7-sms-visitor" <?php echo in_array('sms', $data['visitor_channels']) ? 'checked' : ''; ?>>
                    <?php esc_html_e('Send SMS', 'rebuetext'); ?>
                </label>
                <label class="form-check form-check-inline">
                    <input class="form-check-input channel-toggle" type="checkbox" name="wpcf7si-settings[visitor_channels][]" value="whatsapp" data-target="cf7-wa-visitor" <?php echo in_array('whatsapp', $data['visitor_channels']) ? 'checked' : ''; ?>>
                    <?php esc_html_e('Send WhatsApp', 'rebuetext'); ?>
                </label>
            </div>

            <div id="cf7-sms-visitor" class="mb-3 channel-config">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label text-muted small fw-bold mb-0"><i class="dashicons dashicons-email-alt"></i> <?php esc_html_e('Visitor SMS Message Body:', "rebuetext"); ?></label>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Insert Tag">
                            <span class="dashicons dashicons-tag" style="line-height: 1.5; font-size: 14px; width: 14px; height: 14px;"></span> Insert Tag
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow sms-tag-dropdown" style="max-height: 250px; overflow-y: auto;">
                        </ul>
                    </div>
                </div>
                <textarea name="wpcf7si-settings[visitorMessage]" rows="4" class="form-control"><?php echo esc_textarea($data['visitorMessage']); ?></textarea>
            </div>

            <div id="cf7-wa-visitor" class="mb-3 channel-config p-3 bg-white rounded border">
                <label class="form-label text-success small fw-bold"><i class="dashicons dashicons-whatsapp"></i> <?php esc_html_e('Visitor WhatsApp Template:', "rebuetext"); ?></label>
                <?php $saved_vars_visitor = wp_json_encode($data['wa_visitor_vars']); ?>
                <select class="form-select wa-template-selector mb-3" name="wpcf7si-settings[wa_visitor_template]" data-container="cf7-wa-vars-visitor" data-input-name="wpcf7si-settings[wa_visitor_vars][]" data-saved-vars='<?php echo esc_attr($saved_vars_visitor); ?>'>
                    <option value="">-- <?php esc_html_e('Select an Approved Template', 'rebuetext'); ?> --</option>
                    <?php foreach ($wa_templates as $tpl): ?>
                        <option value="<?php echo esc_attr($tpl['name'] . '|' . $tpl['language']); ?>" <?php selected($data['wa_visitor_template'], $tpl['name'] . '|' . $tpl['language']); ?>>
                            <?php echo esc_html($tpl['name'] . ' (' . $tpl['language'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="cf7-wa-vars-visitor" class="wa-vars-container row g-2"></div>
            </div>
        </div>

    </div>
<?php
}

function rebuetext_form_integrations_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'rebuetext'));
    }

    $cf7_forms = get_posts([
        'post_type'   => 'wpcf7_contact_form',
        'numberposts' => -1
    ]);
?>
    <div class="wrap">
        <h1><?php esc_html_e('CF7 Form Integrations', 'rebuetext'); ?></h1>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e('Form Title', 'rebuetext'); ?></th>
                    <th><?php esc_html_e('Action', 'rebuetext'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cf7_forms as $form): ?>
                    <tr>
                        <td><?php echo esc_html($form->post_title); ?></td>
                        <td>
                            <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=wpcf7&post=' . intval($form->ID) . '&action=edit&rebuetext_sms_tab=1')); ?>">
                                <?php esc_html_e('Edit SMS Settings', 'rebuetext'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function rebuetext_sms_balance_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'rebuetext'));
    }

    $api_token = get_option('rebuetext_api_token');

    if (!$api_token) {
    ?>
        <div class="wrap">
            <h1><?php esc_html_e('SMS Balance', 'rebuetext'); ?></h1>
            <div class="notice notice-error">
                <p><?php
                    printf(
                        wp_kses_post(__('Please set your API token in the <a href="%s">Rebuetext settings</a> page to check your balance.', 'rebuetext')),
                        esc_url(admin_url('admin.php?page=rebuetext-settings'))
                    );
                    ?></p>
            </div>
        </div>
    <?php
        return;
    }

    $endpoint = rebuetext_get_api_base_url() . '/account/balance';
    $response = wp_remote_get($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_token,
            'Accept' => 'application/json',
        ],
        'timeout' => 30,
    ]);

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('SMS Balance', 'rebuetext'); ?></h1>

        <?php
        if (is_wp_error($response)) {
        ?>
            <div class="notice notice-error">
                <p><?php printf(esc_html__('Unable to fetch balance: %s', 'rebuetext'), esc_html($response->get_error_message())); ?></p>
            </div>
            <?php
        } else {
            $http_status = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);

            if ($http_status === 200 && isset($body['status']) && $body['status'] && isset($body['data']['balance_kes'])) {
                $units = esc_html($body['data']['balance_kes']);
                $date = isset($body['data']['date']) ? esc_html($body['data']['date']) : esc_html__('N/A', 'rebuetext');
            ?>
                <div class="card w-100">
                    <h2><?php esc_html_e('Account Balance', 'rebuetext'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Available Units', 'rebuetext'); ?></th>
                            <td>
                                <strong style="font-size: 1.2em;"><?php esc_html_e('KES', 'rebuetext'); ?> <?php echo esc_html($units); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Last Updated', 'rebuetext'); ?></th>
                            <td>
                                <em><?php echo esc_html($date); ?></em>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card w-100">
                    <h2><?php esc_html_e('Need More Units?', 'rebuetext'); ?></h2>
                    <p><?php esc_html_e('You can purchase more units directly from the Rebuetext website.', 'rebuetext'); ?></p>
                    <p><a href="https://rebuetext.com/pricing" class="button button-primary" target="_blank"><?php esc_html_e('Buy More Units', 'rebuetext'); ?></a></p>
                </div>

            <?php
            } else {
                $error_message = esc_html__('Unexpected response from API.', 'rebuetext');
                if (isset($body['message'])) {
                    $error_message = esc_html($body['message']);
                }
            ?>
                <div class="notice notice-error">
                    <p><?php echo esc_html($error_message); ?></p>
                    <?php if ($http_status !== 200) : ?>
                        <p><?php printf(esc_html__('HTTP Status: %s', 'rebuetext'), esc_html($http_status)); ?></p>
                    <?php endif; ?>
                </div>
        <?php
            }
        }
        ?>
    </div>
<?php
}

function rebuetext_gf_form_settings_page()
{
    // Authorization Check
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'rebuetext'));
    }

    // Data Validation
    $form_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

    if (! class_exists('GFAPI')) {
        wp_die(esc_html__('Gravity Forms is not active.', 'rebuetext'));
    }

    $form = \GFAPI::get_form($form_id);
    if (! $form) {
        echo '<div class="error notice"><p>' . esc_html__('Form not found.', 'rebuetext') . '</p></div>';
        return;
    }

    $settings = get_option("rebuetext_gf_form_settings_{$form_id}", [
        'enabled'         => false,
        'phone_field_id'  => '',
        'visitor_message' => '',
        'admin_message'   => '',
    ]);

    if (isset($_POST['rebuetext_sms_save'])) {
        // Verify the Nonce using strict sanitization
        $nonce = isset($_POST['rebuetext_gf_settings_nonce']) ? sanitize_text_field(wp_unslash($_POST['rebuetext_gf_settings_nonce'])) : '';
        if (! wp_verify_nonce($nonce, 'save_rebuetext_gf_settings')) {
            wp_die(esc_html__('Security check failed. Please refresh the page and try again.', 'rebuetext'));
        }

        $settings['enabled']         = isset($_POST['enabled']);
        $settings['phone_field_id']  = isset($_POST['phone_field_id']) ? sanitize_text_field($_POST['phone_field_id']) : '';
        $settings['visitor_message'] = isset($_POST['visitor_message']) ? sanitize_textarea_field($_POST['visitor_message']) : '';
        $settings['admin_message']   = isset($_POST['admin_message']) ? sanitize_textarea_field($_POST['admin_message']) : '';

        update_option("rebuetext_gf_form_settings_{$form_id}", $settings);

        if (isset($_POST['admin_phone_number'])) {
            update_option('rebuetext_admin_phone', sanitize_text_field($_POST['admin_phone_number']));
        }

        echo '<div class="updated notice"><p>' . esc_html__('Settings saved.', 'rebuetext') . '</p></div>';
    }

?>
    <form method="post">
        <?php wp_nonce_field('save_rebuetext_gf_settings', 'rebuetext_gf_settings_nonce'); ?>

        <h3><?php printf(esc_html__('RebueText SMS Settings for Form: %s', 'rebuetext'), esc_html($form['title'])); ?></h3>

        <p>
            <label>
                <input type="checkbox" name="enabled" <?php checked($settings['enabled']); ?>>
                <?php esc_html_e('Enable SMS for this form', 'rebuetext'); ?>
            </label>
        </p>

        <p>
            <label><?php esc_html_e('Admin Phone Number:', 'rebuetext'); ?>
                <input type="text" name="admin_phone_number" value="<?php echo esc_attr(get_option('rebuetext_admin_phone', '')); ?>">
            </label>
        </p>

        <p>
            <label><?php esc_html_e('Visitor Phone Field:', 'rebuetext'); ?></label><br>
            <select name="phone_field_id">
                <option value="">-- <?php esc_html_e('Select a phone field', 'rebuetext'); ?> --</option>
                <?php foreach ($form['fields'] as $field): ?>
                    <?php
                    $field_id = $field->id;
                    $field_label = $field->label;
                    ?>
                    <option value="<?php echo esc_attr($field_id); ?>" <?php selected($settings['phone_field_id'], $field_id); ?>>
                        <?php echo esc_html(sprintf('%1$s (ID: %2$s)', $field_label, $field_id)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label><?php esc_html_e('Visitor SMS Message:', 'rebuetext'); ?></label><br>
            <textarea name="visitor_message" rows="4" cols="50"><?php echo esc_textarea($settings['visitor_message']); ?></textarea>
        </p>
        <p><strong><?php esc_html_e('Available Merge Tags:', 'rebuetext'); ?></strong></p>
        <ul>
            <?php foreach ($form['fields'] as $field): ?>
                <li>
                    <?php
                    if ($field->type === 'name' && isset($field->inputs) && is_array($field->inputs)) {
                        echo esc_html($field->label) . ' — ';
                        foreach ($field->inputs as $input) {
                            echo esc_html($input['label']) . ': ';
                            echo '<code>' . esc_html('{' . $field->label . ':' . $input['id'] . '}') . '</code><br>';
                        }
                    } else {
                        echo esc_html($field->label) . ' — ';
                        echo '<code>' . esc_html('{' . $field->label . ':' . $field->id . '}') . '</code>';
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <p>
            <label><?php esc_html_e('Admin SMS Message:', 'rebuetext'); ?></label><br>
            <textarea name="admin_message" rows="4" cols="50"><?php echo esc_textarea($settings['admin_message']); ?></textarea>
        </p>

        <?php submit_button(esc_html__('Save SMS Settings', 'rebuetext'), 'primary', 'rebuetext_sms_save'); ?>
    </form>
    <?php
}

function rebuetext_gravityforms_redirect_to_gf()
{
    // Ensure the user has permission and the class exists
    if (class_exists('GFForms') && current_user_can('manage_options')) {

        // 1. Use admin_url() to get the destination
        $redirect_url = admin_url('admin.php?page=gf_edit_forms');

        // 2. Use wp_safe_redirect() with esc_url_raw()
        wp_safe_redirect(esc_url_raw($redirect_url));

        // 3. Always call exit() immediately after
        exit;
    } else {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to view this page.', 'rebuetext'));
        }
    ?>
        <div class="wrap">
            <h1><?php esc_html_e('Gravity Forms Integration', 'rebuetext'); ?></h1>
            <p><?php esc_html_e('Gravity Forms is not active or you do not have permission to view the forms.', 'rebuetext'); ?></p>
            <?php if (class_exists('GFForms')) : ?>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gf_edit_forms')); ?>" class="button">
                        <?php esc_html_e('Go to Gravity Forms', 'rebuetext'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
<?php
    }
}
