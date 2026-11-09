<?php
/**
 * Kinboni Admin Module: Alerts & Notifications Engine
 *
 * Implements:
 * - New Order Admin Email Dispatch with luxury responsive HTML formatting
 * - Audio Chime Poller & Web Audio API Sound Trigger
 * - Low-Stock & Out-of-Stock Automatic Email Alerts with Deduplication Guard
 * - Configurable Recipient Lists, Preferences & Live Audio/Email Testing
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Notifications {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_save_notification_settings', array($this, 'ajax_save_notification_settings'));
        add_action('wp_ajax_kinboni_send_test_notification_email', array($this, 'ajax_send_test_notification_email'));

        // WooCommerce Event Hooks
        add_action('woocommerce_order_status_processing', array($this, 'handle_new_order_notification'), 20, 2);
        add_action('woocommerce_order_status_on-hold', array($this, 'handle_new_order_notification'), 20, 2);
        add_action('woocommerce_product_set_stock', array($this, 'handle_stock_level_notification'));
        add_action('woocommerce_variation_set_stock', array($this, 'handle_stock_level_notification'));
    }

    /**
     * Render the Notifications Admin Page
     */
    public function render() {
        $settings = $this->get_notification_settings();

        $view_file = KINBONI_CORE_PATH . 'admin/views/view-notifications.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Notifications view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Retrieve Configured Notification Settings
     */
    public function get_notification_settings() {
        return array(
            'order_email_enabled'   => get_option('kinboni_order_email_enabled', 'yes'),
            'stock_email_enabled'   => get_option('kinboni_stock_email_enabled', 'yes'),
            'low_stock_enabled'     => get_option('kinboni_low_stock_enabled', 'yes'),
            'stock_threshold'       => (int) get_option('kinboni_stock_threshold', 5),
            'order_sound_enabled'   => get_option('kinboni_order_sound_enabled', 'yes'),
            'sound_tone'            => get_option('kinboni_sound_tone', 'gold_bell'),
            'admin_badge_enabled'   => get_option('kinboni_admin_badge_enabled', 'yes'),
            'recipients'            => get_option('kinboni_notification_recipients', get_option('admin_email')),
            // SMS Gateway
            'sms_enabled'           => get_option('kinboni_sms_enabled', 'no'),
            'sms_provider'          => get_option('kinboni_sms_provider', 'greenweb'),
            'sms_api_key'           => get_option('kinboni_sms_api_key', ''),
            'sms_sender_id'         => get_option('kinboni_sms_sender_id', ''),
            'sms_order_template'    => get_option('kinboni_sms_order_template', 'Dear {customer_name}, your order #{order_id} of ৳{order_total} is confirmed. - {site_name}'),
            'sms_custom_url'        => get_option('kinboni_sms_custom_url', ''),
        );
    }

    /**
     * AJAX: Save Notification Preferences
     */
    public function ajax_save_notification_settings() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('notifications') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_email = isset($_POST['order_email_enabled']) && wp_unslash($_POST['order_email_enabled']) === 'yes' ? 'yes' : 'no';
        $stock_email = isset($_POST['stock_email_enabled']) && wp_unslash($_POST['stock_email_enabled']) === 'yes' ? 'yes' : 'no';
        $low_stock   = isset($_POST['low_stock_enabled']) && wp_unslash($_POST['low_stock_enabled']) === 'yes' ? 'yes' : 'no';
        $threshold   = isset($_POST['stock_threshold']) ? max(1, absint(wp_unslash($_POST['stock_threshold']))) : 5;
        $order_sound = isset($_POST['order_sound_enabled']) && wp_unslash($_POST['order_sound_enabled']) === 'yes' ? 'yes' : 'no';
        $sound_tone  = isset($_POST['sound_tone']) ? sanitize_key(wp_unslash($_POST['sound_tone'])) : 'gold_bell';
        $badge       = isset($_POST['admin_badge_enabled']) && wp_unslash($_POST['admin_badge_enabled']) === 'yes' ? 'yes' : 'no';
        $recipients  = isset($_POST['recipients']) ? sanitize_text_field(wp_unslash($_POST['recipients'])) : get_option('admin_email');

        // SMS Settings
        $sms_enabled  = isset($_POST['sms_enabled']) && wp_unslash($_POST['sms_enabled']) === 'yes' ? 'yes' : 'no';
        $sms_provider = isset($_POST['sms_provider']) ? sanitize_key(wp_unslash($_POST['sms_provider'])) : 'greenweb';
        $sms_api_key  = isset($_POST['sms_api_key']) ? sanitize_text_field(wp_unslash($_POST['sms_api_key'])) : '';
        $sms_sender_id= isset($_POST['sms_sender_id']) ? sanitize_text_field(wp_unslash($_POST['sms_sender_id'])) : '';
        $sms_template = isset($_POST['sms_order_template']) ? sanitize_textarea_field(wp_unslash($_POST['sms_order_template'])) : '';
        $sms_custom   = isset($_POST['sms_custom_url']) ? esc_url_raw(wp_unslash($_POST['sms_custom_url'])) : '';

        update_option('kinboni_order_email_enabled', $order_email);
        update_option('kinboni_stock_email_enabled', $stock_email);
        update_option('kinboni_low_stock_enabled', $low_stock);
        update_option('kinboni_stock_threshold', $threshold);
        update_option('kinboni_order_sound_enabled', $order_sound);
        update_option('kinboni_sound_tone', $sound_tone);
        update_option('kinboni_admin_badge_enabled', $badge);
        update_option('kinboni_notification_recipients', $recipients);

        update_option('kinboni_sms_enabled', $sms_enabled);
        update_option('kinboni_sms_provider', $sms_provider);
        update_option('kinboni_sms_api_key', $sms_api_key);
        update_option('kinboni_sms_sender_id', $sms_sender_id);
        update_option('kinboni_sms_order_template', $sms_template);
        update_option('kinboni_sms_custom_url', $sms_custom);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('notification_settings_updated', 'Updated Alert & Notification settings.');
        }

        wp_send_json_success(array('message' => esc_html__('Notification preferences saved successfully.', 'kinboni-core')));
    }

    /**
     * AJAX: Send Test Notification Email
     */
    public function ajax_send_test_notification_email() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('notifications') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $recipients_str = get_option('kinboni_notification_recipients', get_option('admin_email'));
        $recipients = array_filter(array_map('trim', explode(',', $recipients_str)));

        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }

        $subject = '🔔 [Test Alert] KINBONI Pro Notification Engine Test';
        $body = '
            <div style="font-family:sans-serif; max-width:550px; margin:20px auto; background:#FFF; border:1px solid #D4AF37; border-radius:8px; overflow:hidden;">
                <div style="background:#121214; padding:24px; text-align:center;">
                    <div style="font-size:20px; font-weight:800; color:#D4AF37; letter-spacing:2px;">KINBONI PRO</div>
                    <div style="font-size:11px; color:#9CA3AF; margin-top:4px;">Notification Delivery Verification</div>
                </div>
                <div style="padding:28px 24px;">
                    <h3 style="margin:0 0 12px; color:#121214;">Alert System Operational</h3>
                    <p style="color:#4B5563; font-size:14px; line-height:1.6; margin:0 0 16px;">
                        This test communication confirms that your administrative notification routing is fully functioning.
                        When new orders are placed or stock thresholds are reached, alerts will be instantly routed to this address.
                    </p>
                    <div style="background:#FAF9F6; padding:12px; border-radius:6px; font-size:12px; color:#78716C; border:1px solid #E8E6DF;">
                        Dispatched at: <strong>' . current_time('M j, Y H:i:s') . '</strong> &bull; Server: ' . esc_html($_SERVER['SERVER_NAME'] ?? 'localhost') . '
                    </div>
                </div>
            </div>
        ';

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = class_exists('Kinboni_Core')
            ? Kinboni_Core::send_mail($recipients, $subject, $body, $headers, 'test_notification')
            : wp_mail($recipients, $subject, $body, $headers);

        if ($sent) {
            wp_send_json_success(array('message' => sprintf(esc_html__('Test notification successfully sent to: %s', 'kinboni-core'), implode(', ', $recipients))));
        } else {
            wp_send_json_error(array('message' => esc_html__('Failed to send test email. Please verify WordPress SMTP configuration.', 'kinboni-core')));
        }
    }

    /**
     * Dispatch New Order Admin Email (with Deduplication Guard)
     */
    public function handle_new_order_notification($order_id, $order = null) {
        if (get_option('kinboni_order_email_enabled', 'yes') !== 'yes') {
            return;
        }

        if (!$order) {
            $order = wc_get_order($order_id);
        }
        if (!$order) {
            return;
        }

        // Send only if order item count > 0 and total > 0
        if (count($order->get_items()) === 0 || (float) $order->get_total() <= 0) {
            return;
        }

        // Deduplication Check
        if ($order->get_meta('_kinboni_order_notified') === 'yes') {
            return;
        }

        $recipients_str = get_option('kinboni_notification_recipients', get_option('admin_email'));
        $recipients = array_filter(array_map('trim', explode(',', $recipients_str)));
        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }

        $order_num = $order->get_order_number();
        $total     = wc_price($order->get_total());
        $phone     = $order->get_billing_phone() ?: '—';
        $customer  = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Connoisseur';
        $address   = trim($order->get_billing_address_1() . ', ' . $order->get_billing_city());

        $items_html = '';
        foreach ($order->get_items() as $item) {
            $items_html .= sprintf(
                '<div style="padding:8px 0; border-bottom:1px solid #EEE; display:flex; justify-content:space-between; font-size:13px;">
                    <div><strong>%s</strong> &times; %d</div>
                    <div>%s</div>
                 </div>',
                esc_html($item->get_name()),
                $item->get_quantity(),
                wc_price($item->get_total())
            );
        }

        $subject = sprintf('🛍️ [New Order #%s] Total %s from %s', $order_num, strip_tags($total), $customer);
        $admin_url = admin_url('admin.php?page=kinboni-orders');

        $body = '
            <div style="font-family:sans-serif; max-width:600px; margin:20px auto; background:#FFF; border:1px solid #D4AF37; border-radius:8px; overflow:hidden;">
                <div style="background:#121214; padding:24px; text-align:center;">
                    <div style="font-size:22px; font-weight:800; color:#D4AF37; letter-spacing:2px;">KINBONI PRO</div>
                    <div style="font-size:11px; color:#9CA3AF; margin-top:4px;">New Order Notification</div>
                </div>
                <div style="padding:28px 24px;">
                    <h3 style="margin:0 0 16px; color:#121214;">Order #' . esc_html($order_num) . ' Placed</h3>
                    <div style="background:#FAF9F6; border:1px solid #E8E6DF; border-radius:6px; padding:16px; margin-bottom:20px;">
                        <div style="font-size:13px; margin-bottom:6px;"><strong>Customer:</strong> ' . esc_html($customer) . '</div>
                        <div style="font-size:13px; margin-bottom:6px;"><strong>Contact Phone:</strong> ' . esc_html($phone) . '</div>
                        <div style="font-size:13px; margin-bottom:6px;"><strong>Address:</strong> ' . esc_html($address) . '</div>
                        <div style="font-size:13px;"><strong>Payment Method:</strong> ' . esc_html($order->get_payment_method_title() ?: 'Cash on Delivery') . '</div>
                    </div>

                    <h4 style="margin:0 0 10px; font-size:14px; color:#121214;">Order Items:</h4>
                    <div style="margin-bottom:20px;">
                        ' . $items_html . '
                        <div style="padding:10px 0; text-align:right; font-size:15px; font-weight:bold; color:#121214;">
                            Total: ' . $total . '
                        </div>
                    </div>

                    <div style="text-align:center; margin-top:24px;">
                        <a href="' . esc_url($admin_url) . '" style="display:inline-block; background:#121214; color:#D4AF37; border:1px solid #D4AF37; padding:12px 28px; text-decoration:none; font-weight:bold; border-radius:4px; font-size:13px;">
                            Manage Order in Dashboard &rarr;
                        </a>
                    </div>
                </div>
            </div>
        ';

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = class_exists('Kinboni_Core')
            ? Kinboni_Core::send_mail($recipients, $subject, $body, $headers, 'order_notification')
            : wp_mail($recipients, $subject, $body, $headers);

        if ($sent) {
            // Deduplication Flag
            $order->update_meta_data('_kinboni_order_notified', 'yes');
            $order->save();
        }
    }

    /**
     * Handle Stock Notifications (Low Stock & Out of Stock with Deduplication)
     */
    public function handle_stock_level_notification($product) {
        if (!$product) return;

        $stock_qty = $product->get_stock_quantity();
        if ($stock_qty === null) return;

        $threshold       = (int) get_option('kinboni_stock_threshold', 5);
        $stock_email_on  = get_option('kinboni_stock_email_enabled', 'yes') === 'yes';
        $low_stock_on    = get_option('kinboni_low_stock_enabled', 'yes') === 'yes';

        $product_id = $product->get_id();
        $recipients_str = get_option('kinboni_notification_recipients', get_option('admin_email'));
        $recipients = array_filter(array_map('trim', explode(',', $recipients_str)));
        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }

        // 1. OUT OF STOCK
        if ($stock_qty <= 0 && $stock_email_on) {
            if (get_post_meta($product_id, '_kinboni_out_stock_notified', true) !== 'yes') {
                $subject = sprintf('🚨 [OUT OF STOCK] %s has 0 units remaining', $product->get_name());
                $body = sprintf(
                    '<div style="font-family:sans-serif; padding:20px; border:1px solid #DC2626; border-radius:8px; max-width:500px;">
                        <h3 style="color:#DC2626; margin:0 0 10px;">Product Out of Stock</h3>
                        <p><strong>%s</strong> (SKU: %s) has reached 0 inventory.</p>
                        <p><a href="%s" style="background:#121214; color:#D4AF37; padding:8px 16px; text-decoration:none; border-radius:4px;">Restock Product Now</a></p>
                    </div>',
                    esc_html($product->get_name()),
                    esc_html($product->get_sku() ?: 'N/A'),
                    esc_url(admin_url('admin.php?page=kinboni-products'))
                );
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $sent = class_exists('Kinboni_Core')
                    ? Kinboni_Core::send_mail($recipients, $subject, $body, $headers, 'out_of_stock')
                    : wp_mail($recipients, $subject, $body, $headers);
                if ($sent) {
                    update_post_meta($product_id, '_kinboni_out_stock_notified', 'yes');
                }
            }
        } elseif ($stock_qty > 0) {
            // Reset out of stock flag upon replenishment
            delete_post_meta($product_id, '_kinboni_out_stock_notified');
        }

        // 2. LOW STOCK WARNING
        if ($stock_qty > 0 && $stock_qty <= $threshold && $low_stock_on) {
            if (get_post_meta($product_id, '_kinboni_low_stock_notified', true) !== 'yes') {
                $subject = sprintf('⚠️ [LOW STOCK ALERT] %s only %d units remaining', $product->get_name(), $stock_qty);
                $body = sprintf(
                    '<div style="font-family:sans-serif; padding:20px; border:1px solid #D4AF37; border-radius:8px; max-width:500px;">
                        <h3 style="color:#B89628; margin:0 0 10px;">Low Inventory Warning</h3>
                        <p><strong>%s</strong> has dropped to <strong>%d units</strong> (threshold: %d).</p>
                        <p><a href="%s" style="background:#121214; color:#D4AF37; padding:8px 16px; text-decoration:none; border-radius:4px;">Manage Inventory</a></p>
                    </div>',
                    esc_html($product->get_name()),
                    $stock_qty,
                    $threshold,
                    esc_url(admin_url('admin.php?page=kinboni-products'))
                );
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $sent = class_exists('Kinboni_Core')
                    ? Kinboni_Core::send_mail($recipients, $subject, $body, $headers, 'low_stock')
                    : wp_mail($recipients, $subject, $body, $headers);
                if ($sent) {
                    update_post_meta($product_id, '_kinboni_low_stock_notified', 'yes');
                }
            }
        } elseif ($stock_qty > $threshold) {
            // Reset low stock flag upon restocking
            delete_post_meta($product_id, '_kinboni_low_stock_notified');
        }
    }
}

