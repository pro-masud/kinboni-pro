<?php
/**
 * Kinboni Admin Module: Patrons & Customers Management
 *
 * Full customer aggregation from HPOS orders and registered users,
 * lifetime spend calculations, VIP / Problematic / New tagging,
 * one-click WhatsApp chat links, and complete order history.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Customers {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_update_customer_tag', array($this, 'ajax_update_customer_tag'));
        add_action('wp_ajax_kinboni_get_customer_history', array($this, 'ajax_get_customer_history'));

        // CSV Export Trigger
        add_action('admin_init', array($this, 'handle_export'));
    }

    /**
     * Handle Customer CSV Export
     */
    public function handle_export() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'kinboni-customers') {
            return;
        }

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('customers') : !current_user_can('manage_woocommerce')) {
            return;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
            check_admin_referer('kinboni_customers_export', '_kb_nonce');
            $this->export_customers_csv();
            exit;
        }
    }

    /**
     * Render the Customers Management Admin Page
     */
    public function render() {
        $filter_tag  = isset($_GET['tag']) ? sanitize_key(wp_unslash($_GET['tag'])) : '';
        $filter_type = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : '';
        $search_term = isset($_GET['s']) ? sanitize_text_field(trim(wp_unslash($_GET['s']))) : '';
        $paged       = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $per_page    = 20;

        $customers_data = $this->query_customers(array(
            'tag'      => $filter_tag,
            'type'     => $filter_type,
            'search'   => $search_term,
            'paged'    => $paged,
            'per_page' => $per_page,
        ));

        $metrics = $this->get_customer_metrics();

        // Include View
        $view_file = KINBONI_CORE_PATH . 'admin/views/view-customers.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Customers view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Aggregates and queries customers from HPOS orders & WP Users
     */
    public function query_customers($args = array()) {
        $all_customers = $this->aggregate_all_customers();

        // Search Filter
        if (!empty($args['search'])) {
            $s = strtolower($args['search']);
            $all_customers = array_filter($all_customers, function ($c) use ($s) {
                return (stripos($c['name'], $s) !== false ||
                        stripos($c['phone'], $s) !== false ||
                        stripos($c['email'], $s) !== false ||
                        stripos($c['city'], $s) !== false);
            });
        }

        // Tag Filter
        if (!empty($args['tag'])) {
            $tag = strtolower($args['tag']);
            $all_customers = array_filter($all_customers, function ($c) use ($tag) {
                return strtolower($c['tag']) === $tag;
            });
        }

        // Customer Type Filter (VIP, Returning, New)
        if (!empty($args['type'])) {
            $type = strtolower($args['type']);
            $all_customers = array_filter($all_customers, function ($c) use ($type) {
                return strtolower($c['insight']) === $type;
            });
        }

        // Sort by total spent DESC
        usort($all_customers, function ($a, $b) {
            return $b['total_spent'] <=> $a['total_spent'];
        });

        $total = count($all_customers);
        $per_page = $args['per_page'] ?? 20;
        $paged = $args['paged'] ?? 1;
        $max_pages = ceil($total / $per_page) ?: 1;

        $paged_customers = array_slice($all_customers, ($paged - 1) * $per_page, $per_page);

        return array(
            'customers'    => $paged_customers,
            'total'        => $total,
            'max_pages'    => $max_pages,
            'current_page' => $paged,
        );
    }

    /**
     * Compute Overall Customer Performance Metrics
     */
    public function get_customer_metrics() {
        $customers = $this->aggregate_all_customers();

        $total_customers = count($customers);
        $total_revenue   = 0;
        $vip_count       = 0;
        $returning_count = 0;
        $new_count       = 0;

        foreach ($customers as $c) {
            $total_revenue += $c['total_spent'];
            if ($c['tag'] === 'VIP' || $c['insight'] === 'VIP') {
                $vip_count++;
            }
            if ($c['orders_count'] >= 2) {
                $returning_count++;
            } else {
                $new_count++;
            }
        }

        return array(
            'total_customers' => $total_customers,
            'total_revenue'   => $total_revenue,
            'vip_count'       => $vip_count,
            'returning_count' => $returning_count,
            'new_count'       => $new_count,
        );
    }

    /**
     * Aggregate customer records by clean phone and email
     */
    public function aggregate_all_customers() {
        static $cached_customers = null;
        if ($cached_customers !== null) {
            return $cached_customers;
        }

        $customers_map = array();

        // 1. Gather recent WooCommerce orders (HPOS compatible, bounded for memory safety)
        $orders = wc_get_orders(array(
            'limit'   => 1000,
            'orderby' => 'date',
            'order'   => 'DESC',
        ));

        foreach ($orders as $order) {
            $phone = $order->get_billing_phone();
            $email = strtolower(trim($order->get_billing_email()));
            $name  = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Connoisseur';
            $city  = $order->get_billing_city() ?: 'Dhaka';
            $user_id = $order->get_user_id();
            $ord_status = $order->get_status();
            if (in_array($ord_status, array('cancelled', 'failed', 'refunded', 'trash'), true)) {
                continue;
            }
            $date_obj = $order->get_date_created();
            $order_date_str = $date_obj ? wp_date('Y-m-d H:i:s', $date_obj->getTimestamp()) : current_time('mysql');

            // Normalize clean phone (11 digits)
            $clean_phone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($clean_phone) === 13 && substr($clean_phone, 0, 2) === '88') {
                $clean_phone = substr($clean_phone, 2);
            }

            // Key on clean phone if available, otherwise email
            $cust_key = !empty($clean_phone) ? ('p_' . $clean_phone) : (!empty($email) ? ('e_' . $email) : ('u_' . $user_id));

            if (!isset($customers_map[$cust_key])) {
                $customers_map[$cust_key] = array(
                    'key'          => $cust_key,
                    'name'         => $name,
                    'phone'        => $phone,
                    'clean_phone'  => $clean_phone,
                    'email'        => $email,
                    'city'         => $city,
                    'user_id'      => $user_id,
                    'orders_count' => 0,
                    'total_spent'  => 0,
                    'orders'       => array(),
                    'last_order'   => $order_date_str,
                );
            }

            $net_order_total = max(0, (float) $order->get_total() - (float) $order->get_total_refunded());
            $customers_map[$cust_key]['orders_count'] += 1;
            $customers_map[$cust_key]['total_spent']  += $net_order_total;
            $customers_map[$cust_key]['orders'][] = array(
                'id'           => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'date'         => $date_obj ? wp_date('M j, Y', $date_obj->getTimestamp()) : date_i18n('M j, Y'),
                'status'       => $order->get_status(),
                'status_name'  => wc_get_order_status_name($order->get_status()),
                'total'        => wc_price($order->get_total()),
                'items_count'  => $order->get_item_count(),
            );
        }

        // 1b. Include registered WooCommerce customers who have not placed orders yet
        $reg_users = get_users(array(
            'role__in' => array('customer', 'subscriber'),
            'number'   => 250,
            'orderby'  => 'registered',
            'order'    => 'DESC',
        ));
        foreach ($reg_users as $u) {
            $u_phone = get_user_meta($u->ID, 'billing_phone', true);
            $u_clean = preg_replace('/[^0-9]/', '', (string) $u_phone);
            if (strlen($u_clean) === 13 && substr($u_clean, 0, 2) === '88') {
                $u_clean = substr($u_clean, 2);
            }
            $u_email = strtolower(trim($u->user_email));
            $p_key   = !empty($u_clean) ? ('p_' . $u_clean) : '';
            $e_key   = !empty($u_email) ? ('e_' . $u_email) : ('u_' . $u->ID);
            if (($p_key && isset($customers_map[$p_key])) || isset($customers_map[$e_key])) {
                continue;
            }
            $use_key = $p_key ? $p_key : $e_key;
            $customers_map[$use_key] = array(
                'key'          => $use_key,
                'name'         => $u->display_name ?: $u->user_login,
                'phone'        => $u_phone ?: '—',
                'clean_phone'  => $u_clean,
                'email'        => $u_email,
                'city'         => get_user_meta($u->ID, 'billing_city', true) ?: 'Dhaka',
                'user_id'      => $u->ID,
                'orders_count' => 0,
                'total_spent'  => 0,
                'orders'       => array(),
                'last_order'   => $u->user_registered ?: current_time('mysql'),
            );
        }

        // 2. Compute Tags, Insights and WhatsApp URLs
        $final_list = array();
        foreach ($customers_map as $key => $cust) {
            $tag_option_key = 'kinboni_cust_tag_' . $key;
            $saved_tag = get_option($tag_option_key, '');

            // Determine Insight
            $insight = 'New';
            if ($cust['orders_count'] >= 3 || $cust['total_spent'] >= 5000) {
                $insight = 'VIP';
            } elseif ($cust['orders_count'] >= 2) {
                $insight = 'Returning';
            }

            // If no manual tag is saved, inherit insight
            $tag = !empty($saved_tag) ? $saved_tag : ($insight === 'VIP' ? 'VIP' : 'New');

            $aov = $cust['orders_count'] > 0 ? ($cust['total_spent'] / $cust['orders_count']) : 0;

            $cust['tag']          = $tag;
            $cust['insight']      = $insight;
            $cust['aov']          = round($aov, 2);
            $cust['whatsapp_url'] = !empty($cust['clean_phone']) ? ('https://wa.me/88' . $cust['clean_phone']) : '#';
            $cust['last_date_formatted'] = date_i18n('M j, Y', strtotime($cust['last_order']));

            $final_list[] = $cust;
        }

        $cached_customers = $final_list;
        return $final_list;
    }

    /**
     * AJAX: Update Customer Tag (VIP, Problematic, New)
     */
    public function ajax_update_customer_tag() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('customers') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $cust_key = isset($_POST['customer_key']) ? preg_replace('/[^a-zA-Z0-9_@.\-]/', '', strtolower(trim(wp_unslash($_POST['customer_key'])))) : '';
        $new_tag  = isset($_POST['tag']) ? sanitize_text_field(wp_unslash($_POST['tag'])) : 'New';

        $allowed_tags = array('VIP', 'Problematic', 'New');
        if (!in_array($new_tag, $allowed_tags, true)) {
            $new_tag = 'New';
        }

        if (empty($cust_key)) {
            wp_send_json_error(array('message' => esc_html__('Invalid customer identifier.', 'kinboni-core')));
        }

        update_option('kinboni_cust_tag_' . $cust_key, $new_tag);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log(
                'customer_tag_updated',
                sprintf('Customer (%s) assigned tag: %s', $cust_key, $new_tag)
            );
        }

        wp_send_json_success(array(
            'customer_key' => $cust_key,
            'tag'          => $new_tag,
            'message'      => sprintf(esc_html__('Customer tag updated to %s.', 'kinboni-core'), $new_tag),
        ));
    }

    /**
     * AJAX: Get Full Customer Order History for Slide-Over Modal
     */
    public function ajax_get_customer_history() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('customers') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $cust_key = isset($_POST['customer_key']) ? preg_replace('/[^a-zA-Z0-9_@.\-]/', '', strtolower(trim(wp_unslash($_POST['customer_key'])))) : '';
        $customers = $this->aggregate_all_customers();

        $found = null;
        foreach ($customers as $c) {
            if ($c['key'] === $cust_key) {
                $found = $c;
                break;
            }
        }

        if (!$found) {
            wp_send_json_error(array('message' => esc_html__('Customer not found.', 'kinboni-core')));
        }

        $found['total_spent_formatted'] = wc_price($found['total_spent']);
        $found['aov_formatted'] = wc_price($found['aov']);

        wp_send_json_success($found);
    }

    /**
     * Export Customers to CSV
     */
    public function export_customers_csv() {
        $customers = $this->aggregate_all_customers();

        $filename = 'kinboni-customers-' . date('Y-m-d-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($out, array(
            'Customer Name',
            'Phone',
            'Email',
            'City',
            'Total Orders',
            'Total Spent',
            'Average Order Value',
            'Customer Classification',
            'Customer Tag',
            'Last Order Date',
        ));

        $csv_esc = array('Kinboni_Core', 'sanitize_csv_cell');
        foreach ($customers as $c) {
            fputcsv($out, array(
                call_user_func($csv_esc, $c['name']),
                call_user_func($csv_esc, $c['phone']),
                call_user_func($csv_esc, $c['email']),
                call_user_func($csv_esc, $c['city']),
                $c['orders_count'],
                $c['total_spent'],
                $c['aov'],
                $c['insight'],
                $c['tag'],
                $c['last_order'],
            ));
        }

        fclose($out);
        exit;
    }
}

