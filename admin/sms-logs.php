<?php

if (!defined('ABSPATH')) exit;

function rebuetext_sms_logs_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rebuetext_sms_logs';

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY sent_at DESC", ARRAY_A);
?>
    <div class="wrap">
        <h2>SMS Logs</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Response</th>
                    <th>Sent At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log['id']); ?></td>
                        <td><?php echo esc_html($log['sender']); ?></td>
                        <td><?php echo esc_html($log['phone']); ?></td>
                        <td><?php echo esc_html($log['message']); ?></td>
                        <td>
                            <?php
                            // Add badge class based on status
                            $badge_class = ($log['status'] == 'sent') ? 'bg-success' : 'bg-danger';
                            ?>
                            <span class="badge <?php echo esc_attr($badge_class); ?>">
                                <?php echo esc_html(ucwords($log['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <button class="button view-response" data-response="<?php echo esc_attr($log['response']); ?>">
                                View Details
                            </button>
                        </td>
                        <td><?php echo esc_html($log['sent_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="response-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:5px; box-shadow:0px 0px 10px rgba(0,0,0,0.2);">
        <h3>Response Details</h3>
        <p id="modal-response-content"></p>
        <button id="close-modal" class="button button-secondary">Close</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".view-response").forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("modal-response-content").textContent = this.dataset.response;
                    document.getElementById("response-modal").style.display = "block";
                });
            });

            document.getElementById("close-modal").addEventListener("click", function() {
                document.getElementById("response-modal").style.display = "none";
            });
        });
    </script>
<?php
}
