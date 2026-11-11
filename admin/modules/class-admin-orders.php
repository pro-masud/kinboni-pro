<?php
/**
 * Kinboni Admin Module: Orders Management
 *
 * Full HPOS-compatible order management with status changing,
 * date/status filtering, suspicious order detection, printable invoices,
 * delivery packing slips, bulk actions, and CSV/Excel export.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Orders {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_update_order_status', array($this, 'ajax_update_order_status'));
        add_action('wp_ajax_kinboni_add_order_note', array($this, 'ajax_add_order_note'));
        add_action('wp_ajax_kinboni_toggle_order_flag', array($this, 'ajax_toggle_order_flag'));
        add_action('wp_ajax_kinboni_bulk_order_action', array($this, 'ajax_bulk_order_action'));
        add_action('wp_ajax_kinboni_get_order_details', array($this, 'ajax_get_order_details'));
        add_action('wp_ajax_kinboni_book_steadfast_order', array($this, 'ajax_book_steadfast_order'));

        // Printable Views & Export Triggers (via admin_init)
        add_action('admin_init', array($this, 'handle_export_and_prints'));
    }

    /**
     * Handle Export and Printable Documents via GET
     */
    public function handle_export_and_prints() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'kinboni-orders') {
            return;
        }

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('orders') : !current_user_can('manage_woocommerce')) {
            return;
        }

        $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

        if ($action === 'export_csv') {
            check_admin_referer('kinboni_orders_export', '_kb_nonce');
            $this->export_orders_csv();
            exit;
        }

        if ($action === 'print_invoice') {
            $order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
            check_admin_referer('kinboni_print_order_' . $order_id, '_kb_nonce');
            $this->render_printable_document($order_id, 'invoice');
            exit;
        }

        if ($action === 'print_slip') {
            $order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
            check_admin_referer('kinboni_print_order_' . $order_id, '_kb_nonce');
            $this->render_printable_document($order_id, 'slip');
            exit;
        }
    }

    /**
     * Render the Orders Management Admin Page
     */
    public function render() {
        $filter_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
        $filter_search = isset($_GET['s']) ? sanitize_text_field(trim(wp_unslash($_GET['s']))) : '';
        $filter_pay    = isset($_GET['pay_method']) ? sanitize_key(wp_unslash($_GET['pay_method'])) : '';
        $filter_flag   = isset($_GET['flagged']) ? sanitize_key(wp_unslash($_GET['flagged'])) : '';
        $filter_start  = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : '';
        $filter_end    = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : '';
        $paged         = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $per_page      = 20;

        $orders_data = $this->query_orders(array(
            'status'     => $filter_status,
            'search'     => $filter_search,
            'pay_method' => $filter_pay,
            'flagged'    => $filter_flag,
            'start_date' => $filter_start,
            'end_date'   => $filter_end,
            'paged'      => $paged,
            'per_page'   => $per_page,
        ));

        // Available WC statuses
        $wc_statuses = wc_get_order_statuses();

        // Include View
        $view_file = KINBONI_CORE_PATH . 'admin/views/view-orders.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Orders view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * HPOS-Compatible Order Querying with Suspicious Detection
     */
    public function query_orders($args = array()) {
        $paged    = $args['paged'] ?? 1;
        $per_page = $args['per_page'] ?? 20;

        $query_args = array(
            'type'     => 'shop_order',
            'limit'    => $per_page,
            'page'     => $paged,
            'paginate' => true,
            'orderby'  => 'date',
            'order'    => 'DESC',
        );

        // Status Filter
        if (!empty($args['status']) && $args['status'] !== 'all') {
            $query_args['status'] = $args['status'];
        }

        // Date Range
        if (!empty($args['start_date']) && !empty($args['end_date'])) {
            $query_args['date_created'] = $args['start_date'] . ' 00:00:00...' . $args['end_date'] . ' 23:59:59';
        } elseif (!empty($args['start_date'])) {
            $query_args['date_created'] = '>=' . $args['start_date'] . ' 00:00:00';
        }

        // Search Filter (Customer name, Phone, or Order ID)
        if (!empty($args['search'])) {
            $s = $args['search'];
            if (is_numeric($s)) {
                $query_args['field_query'] = array(
                    'relation' => 'OR',
                    array('field' => 'id', 'value' => absint($s)),
                    array('field' => 'billing_phone', 'value' => $s, 'compare' => 'LIKE'),
                );
            } else {
                $query_args['search'] = $s;
            }
        }

        // Payment Method Filter (e.g. COD)
        if (!empty($args['pay_method']) && $args['pay_method'] === 'cod') {
            $query_args['payment_method'] = 'cod';
        }

        $is_flagged_filter = (!empty($args['flagged']) && $args['flagged'] === 'yes');
        if ($is_flagged_filter) {
            $query_args['limit']    = 250;
            $query_args['paginate'] = false;
            unset($query_args['page']);
            $wc_orders    = wc_get_orders($query_args);
            $total_orders = count($wc_orders);
            $max_pages    = 1;
        } else {
            $results      = wc_get_orders($query_args);
            $wc_orders    = $results->orders ?? array();
            $total_orders = $results->total ?? 0;
            $max_pages    = $results->max_num_pages ?? 1;
        }

        // Cache repeat phone counts per request to avoid N+1 duplicate queries
        $phone_counts_cache = array();
        $formatted_orders   = array();

        foreach ($wc_orders as $order) {
            if (!$order || !is_a($order, 'WC_Order') || is_a($order, 'WC_Order_Refund') || !method_exists($order, 'get_billing_phone')) {
                continue;
            }

            $order_id = $order->get_id();
            $phone = (string) $order->get_billing_phone();
            $clean_phone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($clean_phone) === 13 && substr($clean_phone, 0, 2) === '88') {
                $clean_phone = substr($clean_phone, 2);
            }

            // Suspicious order analysis (memoized per phone number)
            $phone_repeat_count = 0;
            if (!empty($clean_phone)) {
                if (!isset($phone_counts_cache[$clean_phone])) {
                    $matching_res = wc_get_orders(array(
                        'type'          => 'shop_order',
                        'billing_phone' => $clean_phone,
                        'return'        => 'ids',
                        'limit'         => 10,
                    ));
                    $phone_counts_cache[$clean_phone] = is_array($matching_res) ? count($matching_res) : 0;
                }
                $phone_repeat_count = $phone_counts_cache[$clean_phone];
            }

            $manual_flag = $order->get_meta('_kinboni_suspicious_flag', true) === 'yes';
            $is_suspicious = ($manual_flag || $phone_repeat_count >= 3);

            // Filter by flagged if requested
            if ($is_flagged_filter && !$is_suspicious) {
                continue;
            }

            // Line items summary
            $items_summary = array();
            foreach ($order->get_items() as $item) {
                $items_summary[] = $item->get_name() . ' × ' . $item->get_quantity();
            }

            $date_obj = $order->get_date_created();
            $date_raw_str = $date_obj ? wp_date('Y-m-d H:i:s', $date_obj->getTimestamp()) : current_time('mysql');
            $date_fmt_str = $date_obj ? wp_date('M j, Y h:i A', $date_obj->getTimestamp()) : date_i18n('M j, Y h:i A');

            $formatted_orders[] = array(
                'id'                   => $order_id,
                'order_number'         => $order->get_order_number(),
                'date'                 => $date_fmt_str,
                'date_raw'             => $date_raw_str,
                'customer_name'        => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Guest Connoisseur',
                'customer_email'       => $order->get_billing_email() ?: '—',
                'customer_phone'       => $phone ?: '—',
                'clean_phone'          => $clean_phone,
                'address'              => $order->get_formatted_billing_address() ?: $order->get_billing_address_1(),
                'city'                 => $order->get_billing_city() ?: 'Dhaka',
                'items_count'          => $order->get_item_count(),
                'items_text'           => implode(', ', $items_summary),
                'total'                => $order->get_total(),
                'total_formatted'      => wc_price($order->get_total()),
                'status'               => $order->get_status(),
                'status_name'          => wc_get_order_status_name($order->get_status()),
                'payment_method'       => $order->get_payment_method(),
                'payment_method_title' => $order->get_payment_method_title() ?: 'Cash on Delivery',
                'is_cod'               => (stripos($order->get_payment_method_title(), 'Cash') !== false || $order->get_payment_method() === 'cod'),
                'phone_repeat_count'   => $phone_repeat_count,
                'manual_flag'          => $manual_flag,
                'is_suspicious'        => $is_suspicious,
                'customer_note'        => $order->get_customer_note(),
            );
        }

        if ($is_flagged_filter) {
            $total_orders     = count($formatted_orders);
            $max_pages        = max(1, (int) ceil($total_orders / $per_page));
            $formatted_orders = array_slice($formatted_orders, ($paged - 1) * $per_page, $per_page);
        }

        return array(
            'orders'       => $formatted_orders,
            'total'        => $total_orders,
            'max_pages'    => $max_pages,
            'current_page' => $paged,
        );
    }

    /**
     * AJAX: Update Order Status
     */
    public function ajax_update_order_status() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('orders') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_id   = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
        $new_status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';

        // Strip wc- prefix if passed
        if (substr($new_status, 0, 3) === 'wc-') {
            $new_status = substr($new_status, 3);
        }

        // Validate against WooCommerce registered order statuses
        $valid_statuses = function_exists('wc_get_order_statuses') ? array_keys(wc_get_order_statuses()) : array();
        if (!empty($valid_statuses) && !in_array('wc-' . $new_status, $valid_statuses, true)) {
            wp_send_json_error(array('message' => esc_html__('Invalid order status specified.', 'kinboni-core')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => esc_html__('Order not found.', 'kinboni-core')));
        }

        $old_status = $order->get_status();
        $order->update_status($new_status, sprintf(
            esc_html__('Status updated via KINBONI Pro Admin by %s', 'kinboni-core'),
            wp_get_current_user()->display_name
        ));

        // Log Activity
        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log(
                'order_status_update',
                sprintf('Order #%d status changed from %s to %s', $order_id, $old_status, $new_status)
            );
        }

        wp_send_json_success(array(
            'order_id'    => $order_id,
            'old_status'  => $old_status,
            'new_status'  => $new_status,
            'status_name' => wc_get_order_status_name($new_status),
            'message'     => sprintf(esc_html__('Order #%d updated to %s.', 'kinboni-core'), $order_id, wc_get_order_status_name($new_status)),
        ));
    }

    /**
     * AJAX: Add Private Admin Note
     */
    public function ajax_add_order_note() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('orders') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
        $note     = isset($_POST['note']) ? sanitize_textarea_field(wp_unslash($_POST['note'])) : '';

        if (empty($note)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a note.', 'kinboni-core')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => esc_html__('Order not found.', 'kinboni-core')));
        }

        // Add private note (is_customer_note = 0)
        $comment_id = $order->add_order_note($note, 0, true);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log(
                'order_note_added',
                sprintf('Private note added to Order #%d: "%s"', $order_id, substr($note, 0, 50))
            );
        }

        wp_send_json_success(array(
            'note_id' => $comment_id,
            'note'    => esc_html($note),
            'date'    => date_i18n('M j, Y h:i A', current_time('timestamp')),
            'author'  => wp_get_current_user()->display_name,
            'message' => esc_html__('Private note added successfully.', 'kinboni-core'),
        ));
    }

    /**
     * AJAX: Toggle Suspicious Order Manual Flag
     */
    public function ajax_toggle_order_flag() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('orders') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
        $order = wc_get_order($order_id);
        if (!$order || !is_a($order, 'WC_Order') || is_a($order, 'WC_Order_Refund')) {
            wp_send_json_error(array('message' => esc_html__('Order not found.', 'kinboni-core')));
        }

        $current_flag = $order->get_meta('_kinboni_suspicious_flag', true);
        $new_flag = ($current_flag === 'yes') ? 'no' : 'yes';
        $order->update_meta_data('_kinboni_suspicious_flag', $new_flag);
        $order->save();

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log(
                'order_flag_toggled',
                sprintf('Order #%d manually flagged as %s by administrator.', $order_id, ($new_flag === 'yes' ? 'Suspicious' : 'Normal'))
            );
        }

        wp_send_json_success(array(
            'order_id'      => $order_id,
            'is_flagged'    => ($new_flag === 'yes'),
            'message'       => ($new_flag === 'yes') ? esc_html__('Order flagged for administrator review.', 'kinboni-core') : esc_html__('Order warning flag removed.', 'kinboni-core'),
        ));
    }

    /**
     * AJAX: Bulk Order Action (e.g. Bulk Status Update)
     */
    public function ajax_bulk_order_action() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('orders') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_ids   = isset($_POST['order_ids']) ? array_map('absint', (array) $_POST['order_ids']) : array();
        $bulk_action = isset($_POST['bulk_action']) ? sanitize_key(wp_unslash($_POST['bulk_action'])) : '';

        if (empty($order_ids)) {
            wp_send_json_error(array('message' => esc_html__('No orders selected.', 'kinboni-core')));
        }

        if (substr($bulk_action, 0, 7) === 'status_') {
            $new_status = substr($bulk_action, 7);
            if (substr($new_status, 0, 3) === 'wc-') {
                $new_status = substr($new_status, 3);
            }
            $valid_statuses = function_exists('wc_get_order_statuses') ? array_keys(wc_get_order_statuses()) : array();
            if (!empty($valid_statuses) && !in_array('wc-' . $new_status, $valid_statuses, true)) {
                wp_send_json_error(array('message' => esc_html__('Invalid bulk order status specified.', 'kinboni-core')));
            }
            $updated_count = 0;

            foreach ($order_ids as $id) {
                $order = wc_get_order($id);
                if ($order) {
                    $order->update_status($new_status, sprintf(
                        esc_html__('Bulk status update via KINBONI Pro Admin by %s', 'kinboni-core'),
                        wp_get_current_user()->display_name
                    ));
                    $updated_count++;
                }
            }

            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::log(
                    'bulk_order_status_update',
                    sprintf('Bulk updated %d orders to %s', $updated_count, $new_status)
                );
            }

            wp_send_json_success(array(
                'updated_count' => $updated_count,
                'message'       => sprintf(esc_html__('%d orders updated to %s.', 'kinboni-core'), $updated_count, wc_get_order_status_name($new_status)),
            ));
        }

        wp_send_json_error(array('message' => esc_html__('Invalid bulk action.', 'kinboni-core')));
    }

    /**
     * AJAX: Get Full Order Details for Modal
     */
    public function ajax_get_order_details() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('orders') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
        $order = wc_get_order($order_id);
        if (!$order || !is_a($order, 'WC_Order') || is_a($order, 'WC_Order_Refund')) {
            wp_send_json_error(array('message' => esc_html__('Order not found.', 'kinboni-core')));
        }

        // Line Items
        $items = array();
        foreach ($order->get_items() as $item_id => $item) {
            $p = $item->get_product();
            $items[] = array(
                'id'       => $item_id,
                'name'     => $item->get_name(),
                'qty'      => $item->get_quantity(),
                'total'    => wc_price($item->get_total()),
                'image'    => $p ? (wp_get_attachment_image_url($p->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src()) : wc_placeholder_img_src(),
                'sku'      => $p ? $p->get_sku() : '—',
                'meta'     => wc_display_item_meta($item, array('echo' => false)),
            );
        }

        // Order Notes
        $notes = array();
        $wc_notes = wc_get_order_notes(array('order_id' => $order_id));
        foreach ($wc_notes as $n) {
            $n_ts = is_object($n->date_created) && method_exists($n->date_created, 'getTimestamp')
                ? $n->date_created->getTimestamp()
                : strtotime((string) $n->date_created);
            $notes[] = array(
                'id'          => $n->id,
                'content'     => $n->content,
                'date'        => $n_ts ? wp_date('M j, Y h:i A', $n_ts) : '',
                'author'      => $n->added_by,
                'is_customer' => (bool) $n->customer_note,
            );
        }

        $phone = method_exists($order, 'get_billing_phone') ? (string) $order->get_billing_phone() : '';
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean_phone) === 13 && substr($clean_phone, 0, 2) === '88') {
            $clean_phone = substr($clean_phone, 2);
        }

        // Repeat phone checks
        $repeat_orders = array();
        if (!empty($clean_phone)) {
            $matching_ids = wc_get_orders(array(
                'type'          => 'shop_order',
                'billing_phone' => $clean_phone,
                'return'        => 'ids',
                'limit'         => -1,
            ));
            foreach ($matching_ids as $mid) {
                if ($mid !== $order_id) {
                    $m_order = wc_get_order($mid);
                    if ($m_order && is_a($m_order, 'WC_Order') && !is_a($m_order, 'WC_Order_Refund')) {
                        $m_dt = $m_order->get_date_created();
                        $repeat_orders[] = array(
                            'id'     => $mid,
                            'number' => $m_order->get_order_number(),
                            'date'   => $m_dt ? wp_date('M j, Y', $m_dt->getTimestamp()) : '',
                            'total'  => wc_price($m_order->get_total()),
                            'status' => wc_get_order_status_name($m_order->get_status()),
                        );
                    }
                }
            }
        }

        $manual_flag = $order->get_meta('_kinboni_suspicious_flag', true) === 'yes';

        $ord_dt = $order->get_date_created();
        wp_send_json_success(array(
            'order_id'        => $order_id,
            'order_number'    => $order->get_order_number(),
            'date'            => $ord_dt ? wp_date('M j, Y h:i A', $ord_dt->getTimestamp()) : '',
            'status'          => $order->get_status(),
            'status_name'     => wc_get_order_status_name($order->get_status()),
            'customer_name'   => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Guest Connoisseur',
            'customer_phone'  => $phone,
            'customer_email'  => $order->get_billing_email(),
            'whatsapp_url'    => !empty($clean_phone) ? 'https://wa.me/88' . $clean_phone : '#',
            'billing_address' => $order->get_formatted_billing_address(),
            'shipping_method' => $order->get_shipping_method() ?: 'Standard Delivery',
            'shipping_total'  => wc_price($order->get_shipping_total()),
            'payment_title'   => $order->get_payment_method_title() ?: 'Cash on Delivery',
            'subtotal'        => wc_price($order->get_subtotal()),
            'discount_total'  => wc_price($order->get_discount_total()),
            'total'           => wc_price($order->get_total()),
            'customer_note'   => $order->get_customer_note(),
            'items'           => $items,
            'notes'           => $notes,
            'manual_flag'     => $manual_flag,
            'repeat_orders'   => $repeat_orders,
            'steadfast_tracking'    => $order->get_meta('_kinboni_steadfast_tracking_code', true),
            'steadfast_consignment' => $order->get_meta('_kinboni_steadfast_consignment_id', true),
            'print_invoice'   => wp_nonce_url(admin_url('admin.php?page=kinboni-orders&action=print_invoice&order_id=' . $order_id), 'kinboni_print_order_' . $order_id, '_kb_nonce'),
            'print_slip'      => wp_nonce_url(admin_url('admin.php?page=kinboni-orders&action=print_slip&order_id=' . $order_id), 'kinboni_print_order_' . $order_id, '_kb_nonce'),
        ));
    }

    /**
     * AJAX: Book Order with Steadfast Courier API
     */
    public function ajax_book_steadfast_order() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('orders') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
        $order = wc_get_order($order_id);
        if (!$order || !is_a($order, 'WC_Order') || is_a($order, 'WC_Order_Refund')) {
            wp_send_json_error(array('message' => esc_html__('Order not found.', 'kinboni-core')));
        }

        $api_key = get_option('kinboni_courier_api_key', '');
        $secret_key = get_option('kinboni_courier_secret_key', '');

        if (empty($api_key) || empty($secret_key)) {
            wp_send_json_error(array('message' => esc_html__('Steadfast API Key or Secret Key is not configured. Please add them in KINBONI Pro -> Delivery.', 'kinboni-core')));
        }

        // Prepare Steadfast payload
        $invoice = (string) $order->get_id();
        $recipient_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Customer';
        $recipient_phone = $order->get_billing_phone();
        $recipient_address = trim($order->get_billing_address_1() . ' ' . $order->get_billing_address_2() . ', ' . $order->get_billing_city());
        $cod_amount = ($order->get_payment_method() === 'cod') ? (float) $order->get_total() : 0;

        $items_desc = array();
        foreach ($order->get_items() as $item) {
            $items_desc[] = $item->get_name() . ' x' . $item->get_quantity();
        }
        $note = implode(', ', $items_desc);

        $payload = array(
            'invoice'           => $invoice,
            'recipient_name'    => $recipient_name,
            'recipient_phone'   => $recipient_phone,
            'recipient_address' => $recipient_address,
            'cod_amount'        => $cod_amount,
            'note'              => substr($note, 0, 200),
        );

        $response = wp_remote_post('https://portal.steadfast.com.bd/api/v1/create_order', array(
            'headers' => array(
                'Api-Key'      => $api_key,
                'Secret-Key'   => $secret_key,
                'Content-Type' => 'application/json',
            ),
            'body'    => wp_json_encode($payload),
            'timeout' => 20,
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Steadfast Connection Error: ' . $response->get_error_message()));
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && isset($body['status']) && $body['status'] == 200) {
            $consignment = $body['consignment'] ?? array();
            $consignment_id = $consignment['consignment_id'] ?? '';
            $tracking_code = $consignment['tracking_code'] ?? '';

            $order->update_meta_data('_kinboni_steadfast_consignment_id', $consignment_id);
            $order->update_meta_data('_kinboni_steadfast_tracking_code', $tracking_code);
            $order->add_order_note(sprintf('Booked with Steadfast Courier. Consignment ID: %s, Tracking Code: %s', $consignment_id, $tracking_code));
            $order->save();

            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::log('steadfast_booked', sprintf('Booked order #%d with Steadfast Courier (Tracking: %s)', $order_id, $tracking_code));
            }

            wp_send_json_success(array(
                'message'        => sprintf(esc_html__('Order #%d successfully booked with Steadfast! Tracking Code: %s', 'kinboni-core'), $order_id, $tracking_code),
                'tracking_code'  => $tracking_code,
                'consignment_id' => $consignment_id,
            ));
        } else {
            $err_msg = isset($body['message']) ? $body['message'] : ('API returned status code ' . $code);
            wp_send_json_error(array('message' => 'Steadfast booking failed: ' . $err_msg));
        }
    }

    /**
     * Export Orders to CSV / Excel-Compatible file
     */
    public function export_orders_csv() {
        $filter_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
        $filter_start  = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : '';
        $filter_end    = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : '';

        $query_args = array(
            'type'    => 'shop_order',
            'limit'   => -1,
            'orderby' => 'date',
            'order'   => 'DESC',
        );

        if (!empty($filter_status) && $filter_status !== 'all') {
            $query_args['status'] = $filter_status;
        }

        if (!empty($filter_start) && !empty($filter_end)) {
            $query_args['date_created'] = $filter_start . ' 00:00:00...' . $filter_end . ' 23:59:59';
        }

        $orders = wc_get_orders($query_args);

        // Send headers for CSV download
        $filename = 'kinboni-orders-' . date('Y-m-d-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // Excel UTF-8 BOM
        fputs($out, "\xEF\xBB\xBF");

        // Headers
        fputcsv($out, array(
            'Order ID',
            'Order Number',
            'Date Created',
            'Status',
            'Customer Name',
            'Phone',
            'Email',
            'Address',
            'City',
            'Items',
            'Items Count',
            'Subtotal',
            'Shipping',
            'Discount',
            'Total',
            'Payment Method',
            'Customer Notes',
        ));

        foreach ($orders as $o) {
            if (!$o || !is_a($o, 'WC_Order') || is_a($o, 'WC_Order_Refund') || !method_exists($o, 'get_billing_phone')) {
                continue;
            }

            $items_str = array();
            foreach ($o->get_items() as $item) {
                $items_str[] = $item->get_name() . ' (qty: ' . $item->get_quantity() . ')';
            }

            $csv_esc = array('Kinboni_Core', 'sanitize_csv_cell');
            $o_dt = $o->get_date_created();
            fputcsv($out, array(
                $o->get_id(),
                call_user_func($csv_esc, $o->get_order_number()),
                $o_dt ? wp_date('Y-m-d H:i:s', $o_dt->getTimestamp()) : current_time('mysql'),
                $o->get_status(),
                call_user_func($csv_esc, trim($o->get_billing_first_name() . ' ' . $o->get_billing_last_name())),
                call_user_func($csv_esc, $o->get_billing_phone()),
                call_user_func($csv_esc, $o->get_billing_email()),
                call_user_func($csv_esc, $o->get_billing_address_1()),
                call_user_func($csv_esc, $o->get_billing_city()),
                call_user_func($csv_esc, implode(' | ', $items_str)),
                $o->get_item_count(),
                $o->get_subtotal(),
                $o->get_shipping_total(),
                $o->get_discount_total(),
                $o->get_total(),
                call_user_func($csv_esc, $o->get_payment_method_title()),
                call_user_func($csv_esc, $o->get_customer_note()),
            ));
        }

        fclose($out);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('orders_exported_csv', sprintf('Exported %d orders to CSV file %s', count($orders), $filename));
        }

        exit;
    }

    /**
     * Render Printable Invoice or Packing Slip
     */
    public function render_printable_document($order_id, $type = 'invoice') {
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die(esc_html__('Order not found.', 'kinboni-core'));
        }

        $template_name = ($type === 'slip') ? 'view-packing-slip.php' : 'view-invoice.php';
        $template_path = KINBONI_CORE_PATH . 'admin/views/' . $template_name;

        if (file_exists($template_path)) {
            include $template_path;
        } else {
            wp_die(esc_html__('Print template not found.', 'kinboni-core'));
        }
    }
}

