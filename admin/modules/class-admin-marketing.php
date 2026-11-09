<?php
/**
 * Kinboni Admin Module: Marketing & Growth Engine
 *
 * Implements:
 * - Subscriber Management (Growth stats, List, Send HTML Marketing Campaigns, Unsubscribe)
 * - Coupon Manager (Native WC_Coupon CRUD, Redemptions, Total Discount Value)
 * - Abandoned Cart Recovery (Real DB tracking, Recovery links, Email deduplication)
 * - Marketing Settings (Free Shipping Threshold, Meta Pixel, GA4, Scheduled Promotional Popup)
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Marketing {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_save_marketing_settings', array($this, 'ajax_save_marketing_settings'));
        add_action('wp_ajax_kinboni_send_marketing_campaign', array($this, 'ajax_send_marketing_campaign'));
        add_action('wp_ajax_kinboni_add_subscriber', array($this, 'ajax_add_subscriber'));
        add_action('wp_ajax_kinboni_toggle_subscriber_status', array($this, 'ajax_toggle_subscriber_status'));
        add_action('wp_ajax_kinboni_delete_subscriber', array($this, 'ajax_delete_subscriber'));
        add_action('wp_ajax_kinboni_create_coupon', array($this, 'ajax_create_coupon'));
        add_action('wp_ajax_kinboni_delete_coupon', array($this, 'ajax_delete_coupon'));
        add_action('wp_ajax_kinboni_send_abandoned_reminder', array($this, 'ajax_send_abandoned_reminder'));
        add_action('wp_ajax_kinboni_get_subscriber_growth_chart', array($this, 'ajax_get_subscriber_growth_chart'));
        add_action('wp_ajax_kinboni_capture_guest_cart', array($this, 'ajax_capture_guest_cart'));
        add_action('wp_ajax_nopriv_kinboni_capture_guest_cart', array($this, 'ajax_capture_guest_cart'));

        // Handle Front-end Cart Tracking & Recovery
        add_action('woocommerce_cart_updated', array($this, 'capture_active_cart'));
        add_action('woocommerce_checkout_order_processed', array($this, 'mark_cart_recovered'), 10, 3);
        add_action('kinboni_order_created', array($this, 'on_kinboni_order_created'));
        add_action('kinboni_cron_abandoned_cart', array($this, 'cron_send_abandoned_cart_reminders'));
        add_action('template_redirect', array($this, 'handle_cart_recovery_link'));
        add_action('template_redirect', array($this, 'handle_front_unsubscribe_link'));

        // Front-end Tracking & Promotional Banner Hooks
        add_action('wp_head', array($this, 'render_tracking_scripts'), 1);
        add_action('wp_footer', array($this, 'render_promotional_popup'), 20);

        // CSV Exports
        add_action('admin_init', array($this, 'handle_exports'));
    }

    /**
     * Render the Marketing Admin Submenu
     */
    public function render() {
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'subscribers';
        $allowed_tabs = array('subscribers', 'coupons', 'abandoned', 'settings');
        if (!in_array($active_tab, $allowed_tabs, true)) {
            $active_tab = 'subscribers';
        }

        // Data for each tab
        $subscribers_data = ($active_tab === 'subscribers') ? $this->get_subscribers_data() : array();
        $coupons_data     = ($active_tab === 'coupons') ? $this->get_coupons_data() : array();
        $abandoned_data   = ($active_tab === 'abandoned') ? $this->get_abandoned_carts_data() : array();
        $settings_data    = ($active_tab === 'settings') ? $this->get_marketing_settings() : array();

        $view_file = KINBONI_CORE_PATH . 'admin/views/view-marketing.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Marketing view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    // =========================================================================
    // 1. SUBSCRIBERS LOGIC
    // =========================================================================

    public function get_subscribers_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_subscribers';

        $search = isset($_GET['s']) ? sanitize_text_field(trim(wp_unslash($_GET['s']))) : '';
        $status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
        $paged  = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;

        $where = array('1=1');
        $params = array();

        if (!empty($search)) {
            $where[] = '(email LIKE %s OR name LIKE %s)';
            $like = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($status)) {
            $where[] = 'status = %s';
            $params[] = $status;
        }

        $where_sql = implode(' AND ', $where);

        $total_query = "SELECT COUNT(id) FROM {$table} WHERE {$where_sql}";
        $total = !empty($params) ? $wpdb->get_var($wpdb->prepare($total_query, $params)) : $wpdb->get_var($total_query);

        $data_query = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY subscribed_at DESC LIMIT %d OFFSET %d";
        $data_params = array_merge($params, array($per_page, $offset));
        $subscribers = $wpdb->get_results($wpdb->prepare($data_query, $data_params), ARRAY_A);

        // Overall Metrics
        $total_active = $wpdb->get_var("SELECT COUNT(id) FROM {$table} WHERE status = 'subscribed'");
        $total_unsub  = $wpdb->get_var("SELECT COUNT(id) FROM {$table} WHERE status = 'unsubscribed'");
        $this_month   = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$table} WHERE subscribed_at >= %s", date('Y-m-01 00:00:00', current_time('timestamp'))));

        return array(
            'subscribers'   => $subscribers,
            'total'         => (int) $total,
            'max_pages'     => ceil($total / $per_page) ?: 1,
            'current_page'  => $paged,
            'growth_chart'  => $this->get_subscriber_growth_chart_data('30d'),
            'metrics'       => array(
                'total_all'    => (int) ($total_active + $total_unsub),
                'active'       => (int) $total_active,
                'unsubscribed' => (int) $total_unsub,
                'this_month'   => (int) $this_month,
            )
        );
    }

    /**
     * Compute Subscriber Growth Timeseries for Charting
     */
    public function get_subscriber_growth_chart_data($range = '30d') {
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_subscribers';

        $labels = array();
        $counts = array();

        if ($range === 'today') {
            for ($h = 0; $h <= 23; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $counts[] = 0;
            }
            $today_start = current_time('Y-m-d 00:00:00');
            $today_end   = current_time('Y-m-d 23:59:59');
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT HOUR(subscribed_at) as hr, COUNT(id) as cnt FROM {$table}
                 WHERE subscribed_at BETWEEN %s AND %s
                 GROUP BY HOUR(subscribed_at)",
                $today_start, $today_end
            ), ARRAY_A);
            if (!empty($rows)) {
                foreach ($rows as $r) {
                    $hr = (int) $r['hr'];
                    if (isset($counts[$hr])) {
                        $counts[$hr] = (int) $r['cnt'];
                    }
                }
            }
        } elseif ($range === '7d' || $range === '30d') {
            $days = ($range === '7d') ? 7 : 30;
            $date_map = array();
            for ($d = $days; $d >= 0; $d--) {
                $ts = strtotime("-{$d} days");
                $d_str = date('Y-m-d', $ts);
                $labels[] = date('M j', $ts);
                $date_map[$d_str] = count($labels) - 1;
                $counts[] = 0;
            }

            $start_sql = date('Y-m-d 00:00:00', strtotime("-{$days} days"));
            $end_sql   = current_time('mysql');
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT DATE(subscribed_at) as dt, COUNT(id) as cnt FROM {$table}
                 WHERE subscribed_at BETWEEN %s AND %s
                 GROUP BY DATE(subscribed_at)",
                $start_sql, $end_sql
            ), ARRAY_A);
            if (!empty($rows)) {
                foreach ($rows as $r) {
                    if (isset($date_map[$r['dt']])) {
                        $counts[$date_map[$r['dt']]] = (int) $r['cnt'];
                    }
                }
            }
        } elseif ($range === '12m') {
            $date_map = array();
            for ($m = 11; $m >= 0; $m--) {
                $month_ts = strtotime("-{$m} months");
                $m_key = date('Y-m', $month_ts);
                $labels[] = date('M Y', $month_ts);
                $date_map[$m_key] = count($labels) - 1;
                $counts[] = 0;
            }
            $start_sql = date('Y-m-01 00:00:00', strtotime('-11 months'));
            $end_sql   = current_time('mysql');
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT DATE_FORMAT(subscribed_at, '%%Y-%%m') as ym, COUNT(id) as cnt FROM {$table}
                 WHERE subscribed_at BETWEEN %s AND %s
                 GROUP BY ym",
                $start_sql, $end_sql
            ), ARRAY_A);
            if (!empty($rows)) {
                foreach ($rows as $r) {
                    if (isset($date_map[$r['ym']])) {
                        $counts[$date_map[$r['ym']]] = (int) $r['cnt'];
                    }
                }
            }
        }

        return array(
            'labels' => $labels,
            'counts' => $counts,
            'range'  => $range,
        );
    }

    /**
     * AJAX: Get Subscriber Growth Chart
     */
    public function ajax_get_subscriber_growth_chart() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');
        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }
        $range = isset($_POST['range']) ? sanitize_key(wp_unslash($_POST['range'])) : '30d';
        $chart_data = $this->get_subscriber_growth_chart_data($range);
        wp_send_json_success($chart_data);
    }

    /**
     * AJAX: Send Marketing Campaign Email to Subscribers
     */
    public function ajax_send_marketing_campaign() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $subject     = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
        $headline    = isset($_POST['headline']) ? sanitize_text_field(wp_unslash($_POST['headline'])) : '';
        $content     = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';
        $btn_text    = isset($_POST['btn_text']) ? sanitize_text_field(wp_unslash($_POST['btn_text'])) : '';
        $btn_url     = isset($_POST['btn_url']) ? esc_url_raw(wp_unslash($_POST['btn_url'])) : home_url('/');
        $audience    = isset($_POST['audience']) ? sanitize_key(wp_unslash($_POST['audience'])) : 'all';

        if (empty($subject) || empty($content)) {
            wp_send_json_error(array('message' => esc_html__('Subject and message content are required.', 'kinboni-core')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_subscribers';

        if ($audience === 'all') {
            $recipients = $wpdb->get_results("SELECT email, name FROM {$table} WHERE status = 'subscribed'", ARRAY_A);
        } else {
            // Last 30 days new subscribers
            $recipients = $wpdb->get_results($wpdb->prepare(
                "SELECT email, name FROM {$table} WHERE status = 'subscribed' AND subscribed_at >= %s",
                date('Y-m-d H:i:s', strtotime('-30 days'))
            ), ARRAY_A);
        }

        if (empty($recipients)) {
            wp_send_json_error(array('message' => esc_html__('No active subscribers found for this audience.', 'kinboni-core')));
        }

        $site_name = get_bloginfo('name');
        $headers   = array('Content-Type: text/html; charset=UTF-8');
        $sent_count = 0;

        foreach ($recipients as $sub) {
            $email = $sub['email'];
            $name  = $sub['name'] ?: 'Valued Connoisseur';
            $token = wp_hash($email . 'kinboni_salt');
            $unsub_url = add_query_arg(array('kinboni_unsub' => urlencode($email), 'token' => $token), home_url('/'));

            $html_body = $this->build_luxury_email_template(array(
                'subject'   => $subject,
                'headline'  => !empty($headline) ? $headline : $subject,
                'recipient' => $name,
                'content'   => $content,
                'btn_text'  => $btn_text,
                'btn_url'   => $btn_url,
                'unsub_url' => $unsub_url,
            ));

            if (wp_mail($email, $subject, $html_body, $headers)) {
                $sent_count++;
            }
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log(
                'marketing_campaign_sent',
                sprintf('Dispatched campaign "%s" to %d subscribers.', $subject, $sent_count)
            );
        }

        wp_send_json_success(array(
            'message' => sprintf(esc_html__('Campaign successfully dispatched to %d subscriber(s).', 'kinboni-core'), $sent_count)
        ));
    }

    /**
     * AJAX: Add Single Subscriber
     */
    public function ajax_add_subscriber() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $name  = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a valid email address.', 'kinboni-core')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_subscribers';

        $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$table} WHERE email = %s", $email));
        if ($existing) {
            wp_send_json_error(array('message' => esc_html__('This email is already in the subscribers database.', 'kinboni-core')));
        }

        $wpdb->insert(
            $table,
            array(
                'email'         => $email,
                'name'          => $name,
                'status'        => 'subscribed',
                'subscribed_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s')
        );

        wp_send_json_success(array('message' => esc_html__('Subscriber added successfully.', 'kinboni-core')));
    }

    /**
     * AJAX: Toggle Subscriber Status (Subscribed / Unsubscribed)
     */
    public function ajax_toggle_subscriber_status() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $sub_id = isset($_POST['subscriber_id']) ? absint(wp_unslash($_POST['subscriber_id'])) : 0;
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_subscribers';

        $sub = $wpdb->get_row($wpdb->prepare("SELECT id, status FROM {$table} WHERE id = %d", $sub_id));
        if (!$sub) {
            wp_send_json_error(array('message' => esc_html__('Subscriber record not found.', 'kinboni-core')));
        }

        $new_status = ($sub->status === 'subscribed') ? 'unsubscribed' : 'subscribed';
        $unsub_at   = ($new_status === 'unsubscribed') ? current_time('mysql') : null;

        $wpdb->update(
            $table,
            array('status' => $new_status, 'unsubscribed_at' => $unsub_at),
            array('id' => $sub_id),
            array('%s', $unsub_at ? '%s' : null),
            array('%d')
        );

        wp_send_json_success(array(
            'new_status' => $new_status,
            'message'    => sprintf(esc_html__('Status changed to %s.', 'kinboni-core'), $new_status)
        ));
    }

    /**
     * AJAX: Delete Subscriber
     */
    public function ajax_delete_subscriber() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $sub_id = isset($_POST['subscriber_id']) ? absint(wp_unslash($_POST['subscriber_id'])) : 0;
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_subscribers';

        $wpdb->delete($table, array('id' => $sub_id), array('%d'));

        wp_send_json_success(array('message' => esc_html__('Subscriber removed permanently.', 'kinboni-core')));
    }

    /**
     * Build Responsive Luxury HTML Email
     */
    private function build_luxury_email_template($args) {
        $site_name = get_bloginfo('name');
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($args['subject']); ?></title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #F8F7F4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8F7F4; padding: 30px 10px;">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                            <!-- Luxury Header -->
                            <tr>
                                <td style="background-color: #121214; padding: 32px 30px; text-align: center;">
                                    <div style="font-size: 24px; font-weight: 800; color: #D4AF37; letter-spacing: 3px; text-transform: uppercase;">KINBONI</div>
                                    <div style="font-size: 11px; color: #9CA3AF; letter-spacing: 1px; margin-top: 4px; text-transform: uppercase;">High-Echelon eCommerce</div>
                                </td>
                            </tr>
                            <!-- Body Content -->
                            <tr>
                                <td style="padding: 36px 32px 28px;">
                                    <h2 style="margin: 0 0 16px; font-size: 20px; font-weight: 700; color: #121214;"><?php echo esc_html($args['headline']); ?></h2>
                                    <p style="margin: 0 0 16px; font-size: 14px; color: #4B5563; line-height: 1.6;">
                                        Dear <?php echo esc_html($args['recipient']); ?>,
                                    </p>
                                    <div style="font-size: 15px; color: #374151; line-height: 1.7; margin-bottom: 24px;">
                                        <?php echo wp_kses_post(nl2br($args['content'])); ?>
                                    </div>
                                    <?php if (!empty($args['btn_text']) && !empty($args['btn_url'])) : ?>
                                        <div style="text-align: center; margin: 30px 0;">
                                            <a href="<?php echo esc_url($args['btn_url']); ?>" style="display: inline-block; background-color: #121214; color: #D4AF37; font-weight: 700; font-size: 14px; padding: 14px 32px; text-decoration: none; border-radius: 4px; border: 1px solid #D4AF37; letter-spacing: 0.5px;">
                                                <?php echo esc_html($args['btn_text']); ?> &rarr;
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <!-- Luxury Footer -->
                            <tr>
                                <td style="background-color: #F9FAFB; padding: 24px 30px; border-top: 1px solid #E5E7EB; text-align: center;">
                                    <p style="margin: 0 0 8px; font-size: 12px; color: #6B7280;">
                                        You are receiving this communication as an esteemed subscriber of <?php echo esc_html($site_name); ?>.
                                    </p>
                                    <p style="margin: 0; font-size: 11px; color: #9CA3AF;">
                                        <a href="<?php echo esc_url($args['unsub_url']); ?>" style="color: #9CA3AF; text-decoration: underline;">Click here to unsubscribe</a> from promotional bulletins.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle Public Unsubscribe Request via URL
     */
    public function handle_front_unsubscribe_link() {
        if (!isset($_GET['kinboni_unsub']) || !isset($_GET['token'])) {
            return;
        }

        $email = sanitize_email(urldecode(wp_unslash($_GET['kinboni_unsub'])));
        $token = sanitize_text_field(wp_unslash($_GET['token']));

        if (empty($email) || !is_email($email)) {
            return;
        }

        $expected_token = wp_hash($email . 'kinboni_salt');
        if (!hash_equals($expected_token, $token)) {
            wp_die('Invalid security token for unsubscribe link.', 'Security Error', array('response' => 403));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_subscribers';
        $wpdb->update(
            $table,
            array('status' => 'unsubscribed', 'unsubscribed_at' => current_time('mysql')),
            array('email' => $email),
            array('%s', '%s'),
            array('%s')
        );

        wp_die(
            '<div style="max-width:500px;margin:80px auto;text-align:center;font-family:sans-serif;padding:30px;border:1px solid #ddd;border-radius:8px;">' .
            '<h2 style="color:#121214;">Unsubscribed Successfully</h2>' .
            '<p style="color:#666;">Your email <strong>' . esc_html($email) . '</strong> has been removed from promotional broadcasts.</p>' .
            '<a href="' . esc_url(home_url('/')) . '" style="display:inline-block;margin-top:15px;background:#121214;color:#D4AF37;padding:10px 20px;text-decoration:none;border-radius:4px;">Return to Store</a>' .
            '</div>',
            'Unsubscribed — KINBONI Pro'
        );
    }

    // =========================================================================
    // 2. COUPON MANAGER LOGIC
    // =========================================================================

    public function get_coupons_data() {
        $args = array(
            'posts_per_page' => 50,
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $coupon_posts = get_posts($args);
        $coupons = array();

        global $wpdb;
        $total_redemptions = 0;
        $total_discount_distributed = 0;

        foreach ($coupon_posts as $post) {
            $coupon = new WC_Coupon($post->ID);
            $code   = $coupon->get_code();
            $usage  = $coupon->get_usage_count();
            $limit  = $coupon->get_usage_limit();

            // Calculate total discount given by this coupon across all orders
            $discount_sum = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(oim.meta_value) 
                 FROM {$wpdb->prefix}woocommerce_order_itemmeta oim
                 JOIN {$wpdb->prefix}woocommerce_order_items oi ON oim.order_item_id = oi.order_item_id
                 WHERE oi.order_item_type = 'coupon' 
                 AND oi.order_item_name = %s 
                 AND oim.meta_key = 'discount_amount'",
                $code
            ));

            $discount_amount_total = (float) $discount_sum;
            $total_redemptions += $usage;
            $total_discount_distributed += $discount_amount_total;

            $expiry = $coupon->get_date_expires();
            $is_expired = false;
            if ($expiry) {
                $is_expired = $expiry->getTimestamp() < time();
            }

            $coupons[] = array(
                'id'             => $coupon->get_id(),
                'code'           => $code,
                'description'    => $coupon->get_description(),
                'discount_type'  => $coupon->get_discount_type(),
                'amount'         => $coupon->get_amount(),
                'usage_count'    => $usage,
                'usage_limit'    => $limit ?: '∞',
                'total_discount' => $discount_amount_total,
                'min_spend'      => $coupon->get_minimum_amount(),
                'max_spend'      => $coupon->get_maximum_amount(),
                'free_shipping'  => $coupon->get_free_shipping(),
                'individual_use' => $coupon->get_individual_use(),
                'expiry_date'    => $expiry ? $expiry->date('M j, Y') : 'Never',
                'is_expired'     => $is_expired,
            );
        }

        return array(
            'coupons' => $coupons,
            'metrics' => array(
                'total_coupons'     => count($coupons),
                'total_redemptions' => $total_redemptions,
                'total_discount'    => $total_discount_distributed,
            )
        );
    }

    /**
     * AJAX: Create New Coupon
     */
    public function ajax_create_coupon() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $code          = isset($_POST['code']) ? sanitize_text_field(strtoupper(trim(wp_unslash($_POST['code'])))) : '';
        $discount_type = isset($_POST['discount_type']) ? sanitize_key(wp_unslash($_POST['discount_type'])) : 'percent';
        $amount        = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
        $description   = isset($_POST['description']) ? sanitize_text_field(wp_unslash($_POST['description'])) : '';
        $min_spend     = isset($_POST['min_spend']) ? (float) $_POST['min_spend'] : 0;
        $max_spend     = isset($_POST['max_spend']) ? (float) $_POST['max_spend'] : 0;
        $usage_limit   = isset($_POST['usage_limit']) ? absint(wp_unslash($_POST['usage_limit'])) : 0;
        $user_limit    = isset($_POST['user_limit']) ? absint(wp_unslash($_POST['user_limit'])) : 1;
        $free_shipping = isset($_POST['free_shipping']) && wp_unslash($_POST['free_shipping']) === 'yes';
        $indiv_use     = isset($_POST['individual_use']) && wp_unslash($_POST['individual_use']) === 'yes';
        $expiry_date   = isset($_POST['expiry_date']) ? sanitize_text_field(wp_unslash($_POST['expiry_date'])) : '';

        if (empty($code) || $amount <= 0) {
            wp_send_json_error(array('message' => esc_html__('Please enter a coupon code and valid discount amount.', 'kinboni-core')));
        }

        $existing_id = wc_get_coupon_id_by_code($code);
        if ($existing_id) {
            wp_send_json_error(array('message' => esc_html__('A coupon with this code already exists.', 'kinboni-core')));
        }

        try {
            $coupon = new WC_Coupon();
            $coupon->set_code($code);
            $coupon->set_discount_type($discount_type);
            $coupon->set_amount($amount);
            $coupon->set_description($description);
            $coupon->set_individual_use($indiv_use);
            $coupon->set_free_shipping($free_shipping);

            if ($min_spend > 0) {
                $coupon->set_minimum_amount($min_spend);
            }
            if ($max_spend > 0) {
                $coupon->set_maximum_amount($max_spend);
            }
            if ($usage_limit > 0) {
                $coupon->set_usage_limit($usage_limit);
            }
            if ($user_limit > 0) {
                $coupon->set_usage_limit_per_user($user_limit);
            }
            if (!empty($expiry_date)) {
                $coupon->set_date_expires($expiry_date);
            }

            $coupon->save();

            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::log('coupon_created', sprintf('Created promotional coupon: %s (Type: %s, Amount: %s)', $code, $discount_type, $amount));
            }

            wp_send_json_success(array('message' => sprintf(esc_html__('Coupon %s created successfully.', 'kinboni-core'), $code)));
        } catch (Exception $e) {
            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::error('coupon_creation_failed', $e->getMessage(), array('exception' => get_class($e)));
            }
            wp_send_json_error(array('message' => esc_html__('Failed to create coupon. Please check your data and try again.', 'kinboni-core')));
        }
    }

    /**
     * AJAX: Delete Coupon
     */
    public function ajax_delete_coupon() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $coupon_id = isset($_POST['coupon_id']) ? absint(wp_unslash($_POST['coupon_id'])) : 0;
        if (!$coupon_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid coupon ID.', 'kinboni-core')));
        }

        $coupon = new WC_Coupon($coupon_id);
        if ($coupon->get_id()) {
            $code = $coupon->get_code();
            $coupon->delete(true);

            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::log('coupon_deleted', sprintf('Deleted coupon: %s', $code));
            }

            wp_send_json_success(array('message' => sprintf(esc_html__('Coupon %s deleted.', 'kinboni-core'), $code)));
        }

        wp_send_json_error(array('message' => esc_html__('Coupon not found.', 'kinboni-core')));
    }

    // =========================================================================
    // 3. ABANDONED CART ENGINE & RECOVERY
    // =========================================================================

    public function get_abandoned_carts_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_abandoned_carts';

        $threshold_mins = (int) get_option('kinboni_abandoned_threshold_min', 30);
        $threshold_time = date('Y-m-d H:i:s', current_time('timestamp') - ($threshold_mins * 60));

        $paged    = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $per_page = 20;
        $offset   = ($paged - 1) * $per_page;

        // Query carts that are either abandoned or were updated before threshold and haven't recovered
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$table} WHERE (status = 'abandoned' OR (status = 'active' AND updated_at <= %s))",
            $threshold_time
        ));

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE (status = 'abandoned' OR (status = 'active' AND updated_at <= %s))
             ORDER BY updated_at DESC LIMIT %d OFFSET %d",
            $threshold_time,
            $per_page,
            $offset
        ), ARRAY_A);

        $carts = array();
        foreach ($rows as $row) {
            $cart_items = json_decode($row['cart_data'], true) ?: array();
            $row['items'] = $cart_items;
            $row['time_ago'] = human_time_diff(strtotime($row['updated_at']), current_time('timestamp')) . ' ago';
            
            // Clean phone for safe WhatsApp link
            $clean_phone = preg_replace('/[^0-9]/', '', $row['customer_phone']);
            if (strlen($clean_phone) === 13 && substr($clean_phone, 0, 2) === '88') {
                $clean_phone = substr($clean_phone, 2);
            }
            $row['clean_phone'] = $clean_phone;
            $row['whatsapp_url'] = !empty($clean_phone) ? ('https://wa.me/88' . $clean_phone) : '#';

            $token = wp_hash($row['id'] . 'kinboni_recover_salt');
            $row['recovery_url'] = add_query_arg(array('kinboni_recover_cart' => $row['id'], 'token' => $token), home_url('/'));

            $carts[] = $row;
        }

        // Metrics
        $total_abandoned = (int) $total;
        $total_recovered = (int) $wpdb->get_var("SELECT COUNT(id) FROM {$table} WHERE status = 'recovered'");
        $potential_val   = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(cart_total) FROM {$table} WHERE (status = 'abandoned' OR (status = 'active' AND updated_at <= %s))",
            $threshold_time
        ));
        $recovered_val   = (float) $wpdb->get_var("SELECT SUM(cart_total) FROM {$table} WHERE status = 'recovered'");

        return array(
            'carts'        => $carts,
            'total'        => $total_abandoned,
            'max_pages'    => ceil($total_abandoned / $per_page) ?: 1,
            'current_page' => $paged,
            'metrics'      => array(
                'abandoned_count' => $total_abandoned,
                'recovered_count' => $total_recovered,
                'potential_val'   => $potential_val,
                'recovered_val'   => $recovered_val,
                'recovery_rate'   => ($total_abandoned + $total_recovered > 0) ? round(($total_recovered / ($total_abandoned + $total_recovered)) * 100, 1) : 0,
            )
        );
    }

    /**
     * Capture Active Cart on Front-end
     */
    public function capture_active_cart() {
        if (!function_exists('WC') || !WC()->cart) {
            return;
        }

        if (WC()->cart->is_empty()) {
            return;
        }

        if (is_admin() && !wp_doing_ajax()) {
            return;
        }

        $cart = WC()->cart;
        $items = array();

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if (!$product) continue;

            $items[] = array(
                'product_id'   => $cart_item['product_id'],
                'variation_id' => $cart_item['variation_id'],
                'name'         => $product->get_name(),
                'quantity'     => $cart_item['quantity'],
                'price'        => $product->get_price(),
                'line_total'   => $cart_item['line_total'],
                'thumbnail'    => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            );
        }

        if (empty($items)) {
            return;
        }

        $session_id = WC()->session ? WC()->session->get_customer_id() : session_id();
        if (empty($session_id)) {
            $session_id = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
        }

        $user_id = get_current_user_id();
        $email = '';
        $phone = '';
        $name  = '';

        if ($user_id) {
            $user  = get_userdata($user_id);
            $email = $user->user_email;
            $name  = $user->display_name;
            $phone = get_user_meta($user_id, 'billing_phone', true);
        }

        // Check if customer email / phone exists in WC Customer session
        if (WC()->customer) {
            if (empty($email)) $email = WC()->customer->get_billing_email();
            if (empty($phone)) $phone = WC()->customer->get_billing_phone();
            if (empty($name))  $name  = trim(WC()->customer->get_billing_first_name() . ' ' . WC()->customer->get_billing_last_name());
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_abandoned_carts';

        // Check if session or email already has an active entry
        $existing = null;
        if (!empty($session_id)) {
            $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$table} WHERE session_id = %s AND status != 'recovered'", $session_id));
        }
        if (!$existing && !empty($email)) {
            $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$table} WHERE customer_email = %s AND status != 'recovered'", $email));
        }

        $data_payload = array(
            'session_id'     => $session_id,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'customer_name'  => $name,
            'cart_data'      => wp_json_encode($items),
            'cart_total'     => $cart->get_total('edit'),
            'status'         => 'active',
            'updated_at'     => current_time('mysql'),
        );

        if ($existing) {
            $wpdb->update($table, $data_payload, array('id' => $existing->id));
        } else {
            $data_payload['reminder_count'] = 0;
            $wpdb->insert($table, $data_payload);
        }
    }

    /**
     * AJAX: Capture Guest Contact Details from Checkout / Quick Order Forms
     */
    public function ajax_capture_guest_cart() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
            wp_send_json_success();
        }

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $name  = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';

        if (empty($email) && empty($phone)) {
            wp_send_json_success();
        }

        if (WC()->customer) {
            if (!empty($email) && is_email($email)) {
                WC()->customer->set_billing_email($email);
            }
            if (!empty($phone)) {
                WC()->customer->set_billing_phone($phone);
            }
            if (!empty($name)) {
                WC()->customer->set_billing_first_name($name);
            }
        }

        $this->capture_active_cart();
        wp_send_json_success();
    }

    /**
     * Mark Abandoned Cart as Recovered upon Order Completion
     */
    public function mark_cart_recovered($order_id, $posted_data = null, $order = null) {
        if (!$order && $order_id) {
            $order = wc_get_order($order_id);
        }
        if ($order) {
            $this->on_kinboni_order_created($order);
        }
    }

    /**
     * Handler for kinboni_order_created action: recovers abandoned cart by email, phone, or session
     *
     * @param WC_Order $order
     */
    public function on_kinboni_order_created($order) {
        if (!$order || !is_a($order, 'WC_Order')) {
            return;
        }

        $email       = sanitize_email($order->get_billing_email());
        $phone       = $order->get_billing_phone();
        $clean_phone = preg_replace('/[^0-9]/', '', (string)$phone);
        $session_id  = (function_exists('WC') && WC()->session) ? WC()->session->get_customer_id() : '';

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_abandoned_carts';

        // 1. Recover by email
        if (!empty($email)) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$table} SET status = 'recovered', updated_at = %s WHERE customer_email = %s AND status != 'recovered'",
                current_time('mysql'),
                $email
            ));
        }

        // 2. Recover by clean phone
        if (!empty($clean_phone) && strlen($clean_phone) >= 7) {
            $phone_suffix = substr($clean_phone, -7);
            $wpdb->query($wpdb->prepare(
                "UPDATE {$table} SET status = 'recovered', updated_at = %s WHERE (customer_phone = %s OR customer_phone LIKE %s) AND status != 'recovered'",
                current_time('mysql'),
                $phone,
                '%' . $wpdb->esc_like($phone_suffix)
            ));
        }

        // 3. Recover by session ID
        if (!empty($session_id)) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$table} SET status = 'recovered', updated_at = %s WHERE session_id = %s AND status != 'recovered'",
                current_time('mysql'),
                $session_id
            ));
        }
    }

    /**
     * Handle Customer Clicking Recovery Link
     */
    public function handle_cart_recovery_link() {
        if (!isset($_GET['kinboni_recover_cart']) || !isset($_GET['token'])) {
            return;
        }

        $cart_id = absint(wp_unslash($_GET['kinboni_recover_cart']));
        $token   = sanitize_text_field(wp_unslash($_GET['token']));

        if (!$cart_id) return;

        $expected_token = wp_hash($cart_id . 'kinboni_recover_salt');
        if (!hash_equals($expected_token, $token)) {
            wp_die('Invalid or expired cart recovery token.', 'Security Check', array('response' => 403));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_abandoned_carts';
        $cart_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $cart_id), ARRAY_A);

        if (!$cart_row) {
            wp_die('Cart session expired or already placed.', 'Cart Recovery');
        }

        $items = json_decode($cart_row['cart_data'], true);
        if (!empty($items) && function_exists('WC') && WC()->cart) {
            WC()->cart->empty_cart();

            foreach ($items as $item) {
                $pid = absint($item['product_id']);
                $vid = isset($item['variation_id']) ? absint($item['variation_id']) : 0;
                $qty = max(1, absint($item['quantity']));
                WC()->cart->add_to_cart($pid, $qty, $vid);
            }

            wc_add_notice(esc_html__('Welcome back! Your curated cart has been safely restored.', 'kinboni-core'), 'success');
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }
    }

    /**
     * AJAX: Send Abandoned Cart Reminder Email (with Deduplication Guard)
     */
    public function ajax_send_abandoned_reminder() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $cart_id = isset($_POST['cart_id']) ? absint(wp_unslash($_POST['cart_id'])) : 0;
        if (!$cart_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid cart identifier.', 'kinboni-core')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_abandoned_carts';
        $cart = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $cart_id), ARRAY_A);

        if (!$cart) {
            wp_send_json_error(array('message' => esc_html__('Cart record not found.', 'kinboni-core')));
        }

        if (empty($cart['customer_email']) || !is_email($cart['customer_email'])) {
            wp_send_json_error(array('message' => esc_html__('No valid email address recorded for this cart.', 'kinboni-core')));
        }

        $sent = $this->send_abandoned_email_for_cart($cart);

        if ($sent) {
            $new_count = ((int) $cart['reminder_count']) + 1;
            wp_send_json_success(array(
                'message'        => sprintf(esc_html__('Recovery reminder successfully sent to %s.', 'kinboni-core'), $cart['customer_email']),
                'reminder_count' => $new_count,
            ));
        } else {
            wp_send_json_error(array('message' => esc_html__('Mail server failed to dispatch email. Please check server SMTP configuration.', 'kinboni-core')));
        }
    }

    /**
     * Send Abandoned Cart Reminder Email for a cart row
     *
     * @param array $cart
     * @return bool
     */
    public function send_abandoned_email_for_cart($cart) {
        if (empty($cart['customer_email']) || !is_email($cart['customer_email'])) {
            return false;
        }

        $cart_id      = (int) $cart['id'];
        $items        = json_decode($cart['cart_data'], true) ?: array();
        $token        = wp_hash($cart_id . 'kinboni_recover_salt');
        $recovery_url = add_query_arg(array('kinboni_recover_cart' => $cart_id, 'token' => $token), home_url('/'));

        $subject   = get_option('kinboni_abandoned_email_subject', 'Did you leave something behind? Your KINBONI order is waiting');
        $incentive = get_option('kinboni_abandoned_email_coupon', '');

        // Generate items HTML
        $items_html = '';
        foreach ($items as $item) {
            $item_name  = isset($item['name']) ? $item['name'] : '';
            $item_qty   = isset($item['quantity']) ? $item['quantity'] : 1;
            $line_total = isset($item['line_total']) ? (float)$item['line_total'] : 0.0;

            $items_html .= sprintf(
                '<div style="padding: 10px 0; border-bottom: 1px solid #EEE; display: flex; justify-content: space-between; align-items: center;">
                    <div><strong>%s</strong> &times; %d</div>
                    <div>৳%s</div>
                 </div>',
                esc_html($item_name),
                esc_html($item_qty),
                number_format($line_total, 2)
            );
        }

        $cart_total = isset($cart['cart_total']) ? (float) $cart['cart_total'] : 0.0;
        $body_content = sprintf(
            '<p>We noticed you left premium items in your shopping bag. Stock is limited and items are reserved for a brief period.</p>
             <div style="margin: 20px 0; padding: 15px; background: #FAF9F6; border-radius: 6px; border: 1px solid #E8E6DF;">
                %s
                <div style="margin-top: 10px; font-weight: bold; text-align: right; color: #121214;">Total: ৳%s</div>
             </div>',
            $items_html,
            number_format($cart_total, 2)
        );

        if (!empty($incentive)) {
            $body_content .= sprintf(
                '<p style="background: #FCF9EE; border: 1px dashed #D4AF37; padding: 12px; border-radius: 4px; text-align: center; color: #B89628; font-weight: bold;">
                    Exclusive Courtesy: Use code <span style="font-size: 16px; color: #121214;">%s</span> at checkout for an instant discount!
                 </p>',
                esc_html($incentive)
            );
        }

        $unsub_token = wp_hash($cart['customer_email'] . 'kinboni_salt');
        $unsub_url   = add_query_arg(array('kinboni_unsub' => urlencode($cart['customer_email']), 'token' => $unsub_token), home_url('/'));

        $html_email = $this->build_luxury_email_template(array(
            'subject'   => $subject,
            'headline'  => 'Your Curated Selection is Waiting',
            'recipient' => !empty($cart['customer_name']) ? $cart['customer_name'] : 'Valued Connoisseur',
            'content'   => $body_content,
            'btn_text'  => 'Complete Your Order Now',
            'btn_url'   => $recovery_url,
            'unsub_url' => $unsub_url,
        ));

        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (class_exists('Kinboni_Core')) {
            $sent = Kinboni_Core::send_mail($cart['customer_email'], $subject, $html_email, $headers, 'abandoned_cart');
        } else {
            $sent = wp_mail($cart['customer_email'], $subject, $html_email, $headers);
        }

        if ($sent) {
            global $wpdb;
            $table = $wpdb->prefix . 'kinboni_abandoned_carts';
            $wpdb->update(
                $table,
                array(
                    'reminder_count' => ((int) $cart['reminder_count']) + 1,
                    'status'         => 'abandoned',
                    'updated_at'     => current_time('mysql'),
                ),
                array('id' => $cart_id),
                array('%d', '%s', '%s'),
                array('%d')
            );

            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::log('abandoned_cart_reminder_sent', sprintf('Dispatched cart recovery email to %s (Cart #%d, Total: ৳%s)', $cart['customer_email'], $cart_id, $cart_total));
            }
            return true;
        }

        return false;
    }

    /**
     * Hourly Cron: Send automated abandoned cart reminders
     */
    public function cron_send_abandoned_cart_reminders() {
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_abandoned_carts';

        $threshold_min = (int) get_option('kinboni_abandoned_threshold_min', 30);
        $time_min_ago  = date('Y-m-d H:i:s', current_time('timestamp') - ($threshold_min * 60));
        $time_24h_ago  = date('Y-m-d H:i:s', current_time('timestamp') - 86400);

        // Find eligible abandoned carts (up to 20 per run):
        // 1. reminder_count = 0 and last activity older than threshold
        // 2. reminder_count = 1 and last reminder sent > 24 hours ago
        $carts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE status != 'recovered'
               AND customer_email != ''
               AND (
                   (reminder_count = 0 AND updated_at <= %s)
                   OR
                   (reminder_count = 1 AND updated_at <= %s)
               )
             ORDER BY updated_at ASC
             LIMIT 20",
            $time_min_ago,
            $time_24h_ago
        ), ARRAY_A);

        if (!empty($carts)) {
            foreach ($carts as $cart) {
                $this->send_abandoned_email_for_cart($cart);
            }
        }
    }

    // =========================================================================
    // 4. MARKETING SETTINGS & TRACKING SCRIPTS
    // =========================================================================

    public function get_marketing_settings() {
        return array(
            'free_shipping_threshold'     => get_option('kinboni_free_shipping_threshold', 2500),
            'fb_pixel_enabled'            => get_option('kinboni_fb_pixel_enabled', 'no'),
            'fb_pixel_id'                 => get_option('kinboni_fb_pixel_id', ''),
            'ga4_enabled'                 => get_option('kinboni_ga4_enabled', 'no'),
            'ga4_measurement_id'          => get_option('kinboni_ga4_measurement_id', ''),
            'popup_enabled'               => get_option('kinboni_popup_banner_enabled', 'no'),
            'popup_title'                 => get_option('kinboni_popup_title', 'Exclusive Connoisseur Privilege'),
            'popup_subtitle'              => get_option('kinboni_popup_subtitle', 'Join The Kinboni Circle and receive ৳500 voucher'),
            'popup_image_url'             => get_option('kinboni_popup_image_url', ''),
            'popup_btn_text'              => get_option('kinboni_popup_btn_text', 'Claim My Voucher'),
            'popup_btn_url'               => get_option('kinboni_popup_btn_url', '#'),
            'popup_start_date'            => get_option('kinboni_popup_start_date', ''),
            'popup_end_date'              => get_option('kinboni_popup_end_date', ''),
            'popup_delay_sec'             => get_option('kinboni_popup_delay_sec', 5),
            'abandoned_threshold_min'     => get_option('kinboni_abandoned_threshold_min', 30),
            'abandoned_email_subject'     => get_option('kinboni_abandoned_email_subject', 'Did you leave something behind? Your KINBONI order is waiting'),
            'abandoned_email_coupon'      => get_option('kinboni_abandoned_email_coupon', 'RECOVER5'),
        );
    }

    /**
     * AJAX: Save Marketing Settings
     */
    public function ajax_save_marketing_settings() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        // Explicitly handle checkboxes (serializeArray omits unchecked checkboxes)
        $fb_enabled    = (isset($_POST['fb_pixel_enabled']) && wp_unslash($_POST['fb_pixel_enabled']) === 'yes') ? 'yes' : 'no';
        $ga4_enabled   = (isset($_POST['ga4_enabled']) && wp_unslash($_POST['ga4_enabled']) === 'yes') ? 'yes' : 'no';
        $popup_enabled = ((isset($_POST['popup_enabled']) && wp_unslash($_POST['popup_enabled']) === 'yes') || (isset($_POST['popup_banner_enabled']) && wp_unslash($_POST['popup_banner_enabled']) === 'yes')) ? 'yes' : 'no';

        update_option('kinboni_fb_pixel_enabled', $fb_enabled);
        update_option('kinboni_ga4_enabled', $ga4_enabled);
        update_option('kinboni_popup_banner_enabled', $popup_enabled);
        update_option('kinboni_popup_enabled', $popup_enabled);

        $fields = array(
            'free_shipping_threshold' => 'floatval',
            'fb_pixel_id'             => 'sanitize_text_field',
            'ga4_measurement_id'      => 'sanitize_text_field',
            'popup_title'             => 'sanitize_text_field',
            'popup_subtitle'          => 'sanitize_text_field',
            'popup_image_url'         => 'esc_url_raw',
            'popup_btn_text'          => 'sanitize_text_field',
            'popup_btn_url'           => 'esc_url_raw',
            'popup_start_date'        => 'sanitize_text_field',
            'popup_end_date'          => 'sanitize_text_field',
            'popup_delay_sec'         => 'absint',
            'abandoned_threshold_min' => 'absint',
            'abandoned_email_subject' => 'sanitize_text_field',
            'abandoned_email_coupon'  => 'sanitize_text_field',
        );

        foreach ($fields as $key => $sanitizer) {
            $opt_name = 'kinboni_' . $key;
            if (isset($_POST[$key])) {
                $val = call_user_func($sanitizer, wp_unslash($_POST[$key]));
                update_option($opt_name, $val);
            }
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('marketing_settings_updated', 'Updated Marketing & Analytics settings.');
        }

        wp_send_json_success(array('message' => esc_html__('Marketing preferences saved successfully.', 'kinboni-core')));
    }

    /**
     * Render Tracking Scripts in Front-End wp_head
     */
    public function render_tracking_scripts() {
        if (is_admin()) return;

        // 1. Meta / Facebook Pixel
        $fb_enabled = get_option('kinboni_fb_pixel_enabled', 'no');
        $fb_pixel_id = sanitize_text_field(get_option('kinboni_fb_pixel_id', ''));

        if ($fb_enabled === 'yes' && !empty($fb_pixel_id)) {
            ?>
            <!-- Meta Pixel Code by KINBONI Pro -->
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo esc_js($fb_pixel_id); ?>');
            fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=<?php echo esc_attr($fb_pixel_id); ?>&ev=PageView&noscript=1"
            /></noscript>
            <!-- End Meta Pixel Code -->
            <?php
        }

        // 2. Google Analytics 4 (GA4)
        $ga4_enabled = get_option('kinboni_ga4_enabled', 'no');
        $ga4_id = sanitize_text_field(get_option('kinboni_ga4_measurement_id', ''));

        if ($ga4_enabled === 'yes' && !empty($ga4_id)) {
            ?>
            <!-- Google tag (gtag.js) by KINBONI Pro -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga4_id); ?>"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '<?php echo esc_js($ga4_id); ?>');
            </script>
            <?php
        }
    }

    /**
     * Render Scheduled Promotional Popup Banner in wp_footer
     */
    public function render_promotional_popup() {
        if (is_admin()) return;

        $popup_enabled = get_option('kinboni_popup_banner_enabled', 'no');
        if ($popup_enabled !== 'yes') return;

        $start_date = get_option('kinboni_popup_start_date', '');
        $end_date   = get_option('kinboni_popup_end_date', '');
        $today      = current_time('Y-m-d');

        if (!empty($start_date) && $today < $start_date) return;
        if (!empty($end_date) && $today > $end_date) return;

        $title     = get_option('kinboni_popup_title', 'Exclusive Privilege');
        $subtitle  = get_option('kinboni_popup_subtitle', 'Join The Kinboni Circle');
        $image_url = get_option('kinboni_popup_image_url', '');
        $btn_text  = get_option('kinboni_popup_btn_text', 'Explore Collection');
        $btn_url   = get_option('kinboni_popup_btn_url', '#');
        $delay_sec = (int) get_option('kinboni_popup_delay_sec', 5);

        ?>
        <!-- KINBONI Scheduled Luxury Promotional Banner -->
        <div id="kinboni-promo-banner" style="display:none; position:fixed; z-index:999999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
            <div style="background:#FFF; max-width:520px; width:90%; border-radius:10px; overflow:hidden; position:relative; box-shadow:0 20px 40px rgba(0,0,0,0.3); border:1px solid #D4AF37;">
                <button type="button" id="btn-close-kinboni-banner" style="position:absolute; top:12px; right:12px; background:rgba(0,0,0,0.5); color:#FFF; border:none; border-radius:50%; width:32px; height:32px; font-size:16px; cursor:pointer; z-index:10; line-height:30px; text-align:center;">&times;</button>
                <?php if (!empty($image_url)) : ?>
                    <div style="width:100%; height:200px; background-image:url('<?php echo esc_url($image_url); ?>'); background-size:cover; background-position:center;"></div>
                <?php endif; ?>
                <div style="padding:28px 24px; text-align:center;">
                    <div style="color:#D4AF37; font-size:11px; font-weight:800; letter-spacing:2px; text-transform:uppercase; margin-bottom:8px;">Limited Edition Promotion</div>
                    <h3 style="margin:0 0 10px; font-size:22px; color:#121214; font-weight:700;"><?php echo esc_html($title); ?></h3>
                    <p style="margin:0 0 20px; color:#6B7280; font-size:14px; line-height:1.5;"><?php echo esc_html($subtitle); ?></p>
                    <a href="<?php echo esc_url($btn_url); ?>" style="display:inline-block; background:#121214; color:#D4AF37; border:1px solid #D4AF37; padding:12px 28px; font-weight:700; font-size:14px; border-radius:4px; text-decoration:none; letter-spacing:0.5px;">
                        <?php echo esc_html($btn_text); ?> &rarr;
                    </a>
                </div>
            </div>
        </div>
        <script>
        (function() {
            if (sessionStorage.getItem('kinboni_promo_dismissed')) return;
            setTimeout(function() {
                var el = document.getElementById('kinboni-promo-banner');
                if (el) el.style.display = 'flex';
            }, <?php echo ($delay_sec * 1000); ?>);
            document.addEventListener('click', function(e) {
                if (e.target && (e.target.id === 'btn-close-kinboni-banner' || e.target.id === 'kinboni-promo-banner')) {
                    var el = document.getElementById('kinboni-promo-banner');
                    if (el) el.style.display = 'none';
                    sessionStorage.setItem('kinboni_promo_dismissed', '1');
                }
            });
        })();
        </script>
        <?php
    }

    /**
     * CSV Exports Handler
     */
    public function handle_exports() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'kinboni-marketing') return;
        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('marketing') : !current_user_can('manage_woocommerce')) return;

        if (isset($_GET['action']) && $_GET['action'] === 'export_subscribers') {
            check_admin_referer('kinboni_marketing_export', '_kb_nonce');

            global $wpdb;
            $table = $wpdb->prefix . 'kinboni_subscribers';
            $subscribers = $wpdb->get_results("SELECT email, name, status, subscribed_at, unsubscribed_at FROM {$table} ORDER BY subscribed_at DESC", ARRAY_A);

            $filename = 'kinboni-subscribers-' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');

            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, array('Email', 'Name', 'Status', 'Subscribed At', 'Unsubscribed At'));

            $csv_esc = array('Kinboni_Core', 'sanitize_csv_cell');
            foreach ($subscribers as $s) {
                fputcsv($out, array(
                    call_user_func($csv_esc, $s['email']),
                    call_user_func($csv_esc, $s['name']),
                    call_user_func($csv_esc, $s['status']),
                    $s['subscribed_at'],
                    $s['unsubscribed_at'] ?: 'N/A',
                ));
            }

            fclose($out);
            exit;
        }
    }
}

