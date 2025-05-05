<?php
if (!defined('ABSPATH')) exit;

function rebuetext_settings_page()
{
    $statuses = wc_get_order_statuses();
    $enabled_statuses = get_option('rebuetext_enabled_statuses', []);
    $customer_templates = get_option('rebuetext_customer_templates', []);
    $admin_templates = get_option('rebuetext_admin_templates', []);
    $admin_phone = get_option('rebuetext_admin_phone', '');
?>
    <div class="wrap">
        <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

        <h1 class="wp-heading-inline">Rebuetext SMS Settings</h1>
        <hr class="wp-header-end">

        <div id="rebuetext-settings-container" class="container-fluid px-0">
            <form id="rebuetext-settings-form">
                <?php wp_nonce_field('rebuetext_nonce', 'rebuetext_ajax_nonce'); ?>

                <div class="card mb-4 w-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">API Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="rebuetext_api_token" class="form-label">API Token</label>
                                <input type="text" class="form-control" name="rebuetext_api_token"
                                    value="<?php echo esc_attr(get_option('rebuetext_api_token')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rebuetext_sender_id" class="form-label">Sender ID</label>
                                <input type="text" class="form-control" name="rebuetext_sender_id"
                                    value="<?php echo esc_attr(get_option('rebuetext_sender_id')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rebuetext_admin_phone" class="form-label">Admin Phone Number</label>
                                <input type="text" class="form-control" name="rebuetext_admin_phone"
                                    value="<?php echo esc_attr($admin_phone); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 w-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Enable SMS for Statuses</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggle-all-statuses">
                                Select All
                            </button>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($statuses as $key => $status) { ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check form-switch rebuetext-switch-group" data-bs-toggle="tooltip" title="Enable SMS for <?php echo esc_attr($status); ?>">
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
                        <h5 class="mb-0">SMS Templates</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6>Available Merge Tags</h6>
                            <div class="merge-tags mb-3">
                                <?php
                                $merge_tags = [
                                    'billing_first_name',
                                    'billing_last_name',
                                    'billing_company',
                                    'billing_address',
                                    'billing_country',
                                    'billing_city',
                                    'billing_state',
                                    'billing_email',
                                    'billing_phone',
                                    'payment_method',
                                    'payment_method_title',
                                    'date_created',
                                    'date_modified',
                                    'date_completed',
                                    'date_paid',
                                    'order_id',
                                    'order_number',
                                    'order_total',
                                    'order_discount',
                                    'order_currency',
                                    'status',
                                    // 'old_status'
                                ];
                                foreach ($merge_tags as $tag) {
                                    echo '<span class="merge-tag badge bg-primary-2 me-2 mb-2" data-tag="{' . $tag . '}">{' . $tag . '}</span>';
                                }
                                ?>
                            </div>
                            <small class="text-muted">Click on any tag to copy it to clipboard</small>
                        </div>

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
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                <label class="form-label">Customer SMS Template</label>
                                                <textarea class="form-control" rows="3"
                                                    name="rebuetext_customer_templates[<?php echo esc_attr($key); ?>]"><?php
                                                                                                                        echo isset($customer_templates[$key]) ? esc_textarea($customer_templates[$key]) : '';
                                                                                                                        ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Admin SMS Template</label>
                                                <textarea class="form-control" rows="3"
                                                    name="rebuetext_admin_templates[<?php echo esc_attr($key); ?>]"><?php
                                                                                                                    echo isset($admin_templates[$key]) ? esc_textarea($admin_templates[$key]) : '';
                                                                                                                    ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="status-message mb-3"></div>
                <button type="submit" id="save-settings-btn" class="btn btn-primary-2">Save Settings</button>
            </form>
            <!-- </div> -->
        </div>
    <?php
}

function rebuetext_cf7_sms_panel_callback($form)
{
    $form_id = $form->id();
    $data = get_option('wpcf7_rebuetext_sms_' . $form_id, [
        'phone'          => '',
        'message'        => '',
        'visitorNumber'  => '',
        'visitorMessage' => ''
    ]);
    ?>
        <div id="cf7si-sms-sortables" class="meta-box-sortables ui-sortable">
            <h4><?php _e("Admin SMS Notifications", "rebuetext_cf7_sms"); ?></h4>
            <fieldset>
                <legend><?php _e("You can use these CF7 tags:", "rebuetext_cf7_sms"); ?><br />
                    <?php $form->suggest_mail_tags(); ?>
                </legend>
                <table class="form-table">
                    <tr>
                        <th><label><?php _e("To (admin phone):", "rebuetext_cf7_sms"); ?></label></th>
                        <td>
                            <input type="text" name="wpcf7si-settings[phone]" class="wide" size="70" value="<?php echo esc_attr($data['phone']); ?>">
                            <br /><small><?php _e("Use CF7 mail-tags like [your-phone] or raw numbers (comma-separated)", "rebuetext_cf7_sms"); ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e("Message body:", "rebuetext_cf7_sms"); ?></label></th>
                        <td>
                            <textarea name="wpcf7si-settings[message]" cols="100" rows="4" class="large-text code"><?php echo esc_textarea($data['message']); ?></textarea>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <hr />
            <h3><?php _e("Visitor SMS Notifications", "rebuetext_cf7_sms"); ?></h3>
            <fieldset>
                <legend><?php _e("Use CF7 mail-tags like [your-phone] for visitor number", "rebuetext_cf7_sms"); ?></legend>
                <table class="form-table">
                    <tr>
                        <th><label><?php _e("Visitor Mobile:", "rebuetext_cf7_sms"); ?></label></th>
                        <td>
                            <input type="text" name="wpcf7si-settings[visitorNumber]" class="wide" size="70" value="<?php echo esc_attr($data['visitorNumber']); ?>">
                            <br /><small><?php _e("Use CF7 tags or comma-separated values", "rebuetext_cf7_sms"); ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e("Message body:", "rebuetext_cf7_sms"); ?></label></th>
                        <td>
                            <textarea name="wpcf7si-settings[visitorMessage]" cols="100" rows="4" class="large-text code"><?php echo esc_textarea($data['visitorMessage']); ?></textarea>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
    <?php
}

function rebuetext_form_integrations_page()
{
    $cf7_forms = get_posts([
        'post_type'   => 'wpcf7_contact_form',
        'numberposts' => -1
    ]);
    ?>
        <div class="wrap">
            <h1>CF7 Form Integrations</h1>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Form Title</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cf7_forms as $form): ?>
                        <tr>
                            <td><?php echo esc_html($form->post_title); ?></td>
                            <td>
                                <a class="button button-primary" href="<?php echo admin_url('admin.php?page=wpcf7&post=' . $form->ID . '&action=edit&rebuetext_sms_tab=1'); ?>">
                                    Edit SMS Settings
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
        $api_token = get_option('rebuetext_api_token');

        if (!$api_token) {
        ?>
            <div class="wrap">
                <h1>SMS Balance</h1>
                <div class="notice notice-error">
                    <p>Please set your API token in the <a href="<?php echo admin_url('admin.php?page=rebuetext-settings'); ?>">Rebuetext settings</a> page to check your balance.</p>
                </div>
            </div>
        <?php
            return;
        }

        $response = wp_remote_get('https://rebuetext.com/api/v1/account/balance', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_token,
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ]);

        ?>
        <div class="wrap">
            <h1>SMS Balance</h1>

            <?php
            if (is_wp_error($response)) {
            ?>
                <div class="notice notice-error">
                    <p>Unable to fetch balance: <?php echo esc_html($response->get_error_message()); ?></p>
                </div>
                <?php
            } else {
                $http_status = wp_remote_retrieve_response_code($response);
                $body = json_decode(wp_remote_retrieve_body($response), true);

                if ($http_status === 200 && isset($body['status']) && $body['status'] && isset($body['data']['account_units'])) {
                    $units = esc_html($body['data']['account_units']);
                    $date = isset($body['data']['date']) ? esc_html($body['data']['date']) : 'N/A'; // Handle missing date

                    // Use WordPress dashboard components for a modern look
                ?>
                    <div class="card w-100">
                        <h2>Account Balance</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Available Units</th>
                                <td>
                                    <strong style="font-size: 1.2em;"><?php echo $units; ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Last Updated</th>
                                <td>
                                    <em><?php echo $date; ?></em>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="card w-100">
                        <h2>Need More Units?</h2>
                        <p>You can purchase more units directly from the Rebuetext website.</p>
                        <p><a href="https://rebuetext.com/pricing" class="button button-primary" target="_blank">Buy More Units</a></p>
                    </div>

                <?php
                } else {
                    $error_message = 'Unexpected response from API.';
                    if (isset($body['message'])) {
                        $error_message = esc_html($body['message']);
                    }
                ?>
                    <div class="notice notice-error">
                        <p><?php echo $error_message; ?></p>
                        <?php if ($http_status !== 200) : ?>
                            <p>HTTP Status: <?php echo $http_status; ?></p>
                        <?php endif; ?>
                    </div>
            <?php
                }
            }
            ?>
        </div>
    <?php
    }

    add_action('gform_form_settings_page_rebuetext_sms', 'rebuetext_gf_form_settings_page');
    function rebuetext_gf_form_settings_page()
    {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.'));
        }

        // Get form ID from URL
        $form_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        // echo '<div class="error notice"><p>' . $form_id . '.</p></div>';

        // FIX: Get full form object using ID
        $form = \GFAPI::get_form($form_id);
        if (!$form) {
            echo '<div class="error notice"><p>Form not found.</p></div>';
            return;
        }

        $settings = get_option("rebuetext_gf_form_settings_{$form_id}", [
            'enabled'         => false,
            'phone_field_id'  => '',
            'visitor_message' => '',
            'admin_message'   => '',
        ]);

        if (isset($_POST['rebuetext_sms_save'])) {
            $settings['enabled']         = isset($_POST['enabled']);
            $settings['phone_field_id']  = sanitize_text_field($_POST['phone_field_id']);
            $settings['visitor_message'] = sanitize_textarea_field($_POST['visitor_message']);
            $settings['admin_message']   = sanitize_textarea_field($_POST['admin_message']);
            update_option("rebuetext_gf_form_settings_{$form_id}", $settings);
            update_option('rebuetext_admin_phone', sanitize_text_field($_POST['admin_phone_number']));

            echo '<div class="updated notice"><p>Settings saved.</p></div>';
        }

    ?>
        <form method="post">
            <h3>RebueText SMS Settings for Form: <?php echo esc_html($form['title']); ?></h3>

            <p>
                <label>
                    <input type="checkbox" name="enabled" <?php checked($settings['enabled']); ?>>
                    Enable SMS for this form
                </label>
            </p>

            <p>
                <label>Admin Phone Number:
                    <input type="text" name="admin_phone_number" value="<?php echo esc_attr(get_option('rebuetext_admin_phone', '')); ?>">
                </label>
            </p>

            <p>
                <label>Visitor Phone Field:</label><br>
                <select name="phone_field_id">
                    <option value="">-- Select a phone field --</option>
                    <?php foreach ($form['fields'] as $field): ?>
                        <?php
                        $field_id = $field->id;
                        $field_label = $field->label;
                        $selected = selected($settings['phone_field_id'], $field_id, false);
                        ?>
                        <option value="<?php echo esc_attr($field_id); ?>" <?php echo $selected; ?>>
                            <?php echo esc_html("{$field_label} (ID: {$field_id})"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label>Visitor SMS Message:</label><br>
                <textarea name="visitor_message" rows="4" cols="50"><?php echo esc_textarea($settings['visitor_message']); ?></textarea>
            </p>
            <p><strong>Available Merge Tags:</strong></p>
            <ul>
                <?php foreach ($form['fields'] as $field): ?>
                    <li>
                        <?php
                        // Check if the field is a 'name' type, and handle its sub-fields
                        if ($field->type === 'name') {
                            echo esc_html($field->label) . ' — ';
                            // Loop through the sub-fields (inputs) of the name field
                            foreach ($field->inputs as $input) {
                                // Display the label of each sub-field
                                echo esc_html($input['label']) . ': ';
                                // Generate the merge tag for each sub-field
                                echo '<code>{' . esc_html($field->label . ':' . $input['id']) . '}</code><br>';
                            }
                        } else {
                            // For non-name fields, just output the merge tag for the field itself
                            echo esc_html($field->label) . ' — ';
                            echo '<code>{' . esc_html($field->label . ':' . $field->id) . '}</code>';
                        }
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>


            <p>
                <label>Admin SMS Message:</label><br>
                <textarea name="admin_message" rows="4" cols="50"><?php echo esc_textarea($settings['admin_message']); ?></textarea>
            </p>

            <?php submit_button('Save SMS Settings', 'primary', 'rebuetext_sms_save'); ?>
        </form>
    <?php
    }
