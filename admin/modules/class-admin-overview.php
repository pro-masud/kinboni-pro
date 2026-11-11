<?php
/**
 * Kinboni Admin Module: Overview Dashboard
 *
 * Gathers authentic statistics from WooCommerce orders, products,
 * inventory alerts, and Kinboni analytics.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Overview {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('wp_ajax_kinboni_get_overview_stats', array($this, 'ajax_get_overview_stats'));
    }

    /**
     * Render the Overview Dashboard Page
     */
    public function render() {
        $range = isset($_GET['range']) ? sanitize_key(wp_unslash($_GET['range'])) : '30d';
        $stats = $this->get_dashboard_data($range);

        // Include the View
        $view_file = KINBONI_CORE_PATH . 'admin/views/view-overview.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Overview view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * AJAX Endpoint for Range Filtering without page reload
     */
    public function ajax_get_overview_stats() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $range = isset($_POST['range']) ? sanitize_key(wp_unslash($_POST['range'])) : '30d';
        $data = $this->get_dashboard_data($range);

        wp_send_json_success($data);
    }

    /**
     * Get Complete Dynamic Dashboard Data
     *
     * @param string $range 'today', '7d', '30d', '12m'
     * @return array
     */
    public function get_dashboard_data($range = '30d') {
        return array(
            'order_counts'   => $this->get_order_status_counts(),
            'sales_metrics'  => $this->get_sales_metrics($range),
            'inventory'      => $this->get_inventory_alerts(),
            'today_tasks'    => $this->get_today_tasks(),
            'recent_orders'  => $this->get_recent_orders(10),
            'bestsellers'    => $this->get_bestselling_products(5),
            'top_categories' => $this->get_top_categories(5),
            'visitor_stats'  => $this->get_visitor_analytics($range),
            'currency'       => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳',
            'range'          => $range,
        );
    }

    /**
     * Order Counts by Status
     */
    public function get_order_status_counts() {
        $statuses = array('pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded');
        $counts = array();

        if (function_exists('wc_orders_count')) {
            foreach ($statuses as $st) {
                $counts[$st] = wc_orders_count($st);
            }
        } else {
            foreach ($statuses as $st) {
                $orders = wc_get_orders(array('status' => $st, 'return' => 'ids', 'limit' => -1));
                $counts[$st] = count($orders);
            }
        }

        return $counts;
    }

    /**
     * Sales Analytics for Selected Range with Prior Period Comparison
     *
     * @param string $range
     * @return array
     */
    public function get_sales_metrics($range = '30d') {
        global $wpdb;

        $now = current_time('timestamp');
        $labels = array();
        $revenue_points = array();
        $orders_points = array();

        switch ($range) {
            case 'today':
                $start_curr = strtotime('today midnight', $now);
                $end_curr   = $now;
                $start_prev = strtotime('yesterday midnight', $now);
                $end_prev   = strtotime('yesterday 23:59:59', $now);

                // Hourly buckets (00:00 to 23:00)
                for ($h = 0; $h <= (int)wp_date('H', $now); $h++) {
                    $labels[] = sprintf('%02d:00', $h);
                    $revenue_points[$h] = 0;
                    $orders_points[$h] = 0;
                }
                break;

            case '7d':
                $start_curr = strtotime('-6 days midnight', $now);
                $end_curr   = $now;
                $start_prev = strtotime('-13 days midnight', $now);
                $end_prev   = strtotime('-7 days 23:59:59', $now);

                for ($d = 6; $d >= 0; $d--) {
                    $day_ts = strtotime("-{$d} days", $now);
                    $key = wp_date('Y-m-d', $day_ts);
                    $labels[] = wp_date('D (M j)', $day_ts);
                    $revenue_points[$key] = 0;
                    $orders_points[$key] = 0;
                }
                break;

            case '12m':
                $start_curr = strtotime('-11 months first day of this month 00:00:00', $now);
                $end_curr   = $now;
                $start_prev = strtotime('-23 months first day of this month 00:00:00', $now);
                $end_prev   = strtotime('-12 months last day of this month 23:59:59', $now);

                for ($m = 11; $m >= 0; $m--) {
                    $month_ts = strtotime("-{$m} months", $now);
                    $key = wp_date('Y-m', $month_ts);
                    $labels[] = wp_date('M Y', $month_ts);
                    $revenue_points[$key] = 0;
                    $orders_points[$key] = 0;
                }
                break;

            case '30d':
            default:
                $start_curr = strtotime('-29 days midnight', $now);
                $end_curr   = $now;
                $start_prev = strtotime('-59 days midnight', $now);
                $end_prev   = strtotime('-30 days 23:59:59', $now);

                for ($d = 29; $d >= 0; $d--) {
                    $day_ts = strtotime("-{$d} days", $now);
                    $key = wp_date('Y-m-d', $day_ts);
                    $labels[] = wp_date('M j', $day_ts);
                    $revenue_points[$key] = 0;
                    $orders_points[$key] = 0;
                }
                break;
        }

        // Fetch Current Period Orders
        $orders_curr = wc_get_orders(array(
            'type'         => 'shop_order',
            'date_created' => wp_date('Y-m-d H:i:s', $start_curr) . '...' . wp_date('Y-m-d H:i:s', $end_curr),
            'status'       => array('completed', 'processing'),
            'limit'        => -1,
        ));

        $total_revenue_curr = 0;
        $total_orders_curr = count($orders_curr);

        foreach ($orders_curr as $order) {
            $total = (float) $order->get_total();
            $total_revenue_curr += $total;
            $ord_dt = $order->get_date_created();
            $order_ts = $ord_dt ? ($ord_dt->getTimestamp() + (int) (get_option('gmt_offset') * HOUR_IN_SECONDS)) : current_time('timestamp');

            if ($range === 'today') {
                $bucket = (int) wp_date('H', $order_ts);
                if (isset($revenue_points[$bucket])) {
                    $revenue_points[$bucket] += $total;
                    $orders_points[$bucket] += 1;
                }
            } elseif ($range === '12m') {
                $bucket = wp_date('Y-m', $order_ts);
                if (isset($revenue_points[$bucket])) {
                    $revenue_points[$bucket] += $total;
                    $orders_points[$bucket] += 1;
                }
            } else {
                $bucket = wp_date('Y-m-d', $order_ts);
                if (isset($revenue_points[$bucket])) {
                    $revenue_points[$bucket] += $total;
                    $orders_points[$bucket] += 1;
                }
            }
        }

        // Fetch Previous Equivalent Period Orders
        $orders_prev = wc_get_orders(array(
            'type'         => 'shop_order',
            'date_created' => wp_date('Y-m-d H:i:s', $start_prev) . '...' . wp_date('Y-m-d H:i:s', $end_prev),
            'status'       => array('completed', 'processing'),
            'limit'        => -1,
        ));

        $total_revenue_prev = 0;
        $total_orders_prev = count($orders_prev);
        foreach ($orders_prev as $order) {
            $total_revenue_prev += (float) $order->get_total();
        }

        // Calculate Average Order Value (AOV)
        $aov_curr = $total_orders_curr > 0 ? ($total_revenue_curr / $total_orders_curr) : 0;
        $aov_prev = $total_orders_prev > 0 ? ($total_revenue_prev / $total_orders_prev) : 0;

        // Calculate Percentage Changes
        $rev_change_pct = $total_revenue_prev > 0 ? round((($total_revenue_curr - $total_revenue_prev) / $total_revenue_prev) * 100, 1) : ($total_revenue_curr > 0 ? 100 : 0);
        $orders_change_pct = $total_orders_prev > 0 ? round((($total_orders_curr - $total_orders_prev) / $total_orders_prev) * 100, 1) : ($total_orders_curr > 0 ? 100 : 0);
        $aov_change_pct = $aov_prev > 0 ? round((($aov_curr - $aov_prev) / $aov_prev) * 100, 1) : ($aov_curr > 0 ? 100 : 0);

        return array(
            'total_revenue'       => $total_revenue_curr,
            'total_revenue_prev'  => $total_revenue_prev,
            'rev_change_pct'      => $rev_change_pct,
            'total_orders'        => $total_orders_curr,
            'total_orders_prev'   => $total_orders_prev,
            'orders_change_pct'   => $orders_change_pct,
            'aov'                 => round($aov_curr, 2),
            'aov_prev'            => round($aov_prev, 2),
            'aov_change_pct'      => $aov_change_pct,
            'chart_labels'        => $labels,
            'chart_revenues'      => array_values($revenue_points),
            'chart_orders'        => array_values($orders_points),
        );
    }

    /**
     * Inventory Low-Stock & Out-of-Stock Alerts
     */
    public function get_inventory_alerts() {
        $threshold = (int) get_option('kinboni_stock_threshold', 5);

        // Low stock products
        $low_stock_products = wc_get_products(array(
            'status'         => 'publish',
            'limit'          => 10,
            'stock_status'   => 'instock',
            'manage_stock'   => true,
            'stock_quantity' => array(1, $threshold),
        ));

        // Out of stock products
        $out_of_stock_products = wc_get_products(array(
            'status'       => 'publish',
            'limit'        => 10,
            'stock_status' => 'outofstock',
        ));

        $format_product = function($p) {
            return array(
                'id'       => $p->get_id(),
                'name'     => $p->get_name(),
                'sku'      => $p->get_sku() ?: 'N/A',
                'price'    => wc_price($p->get_price()),
                'stock'    => $p->get_stock_quantity() ?? 0,
                'edit_url' => admin_url('post.php?post=' . $p->get_id() . '&action=edit'),
            );
        };

        return array(
            'low_stock_threshold'   => $threshold,
            'low_stock_count'       => count($low_stock_products),
            'low_stock_list'        => array_map($format_product, $low_stock_products),
            'out_of_stock_count'    => count($out_of_stock_products),
            'out_of_stock_list'     => array_map($format_product, $out_of_stock_products),
        );
    }

    /**
     * Today's Operational Tasks
     */
    public function get_today_tasks() {
        global $wpdb;

        // Pending orders
        $pending_count = function_exists('wc_orders_count') ? wc_orders_count('pending') : count(wc_get_orders(array('status' => 'pending', 'return' => 'ids')));

        // Out of stock products
        $out_stock_count = count(wc_get_products(array('status' => 'publish', 'stock_status' => 'outofstock', 'return' => 'ids', 'limit' => -1)));

        // New subscribers today
        $table_subscribers = $wpdb->prefix . 'kinboni_subscribers';
        $today_start = wp_date('Y-m-d 00:00:00', current_time('timestamp'));
        $subscribers_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$table_subscribers} WHERE subscribed_at >= %s",
            $today_start
        ));

        return array(
            'pending_orders'       => $pending_count,
            'out_of_stock_count'   => $out_stock_count,
            'new_subscribers_today'=> $subscribers_count,
        );
    }

    /**
     * Recent Orders
     */
    public function get_recent_orders($limit = 10) {
        $orders = wc_get_orders(array(
            'type'    => 'shop_order',
            'limit'   => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
        ));

        $list = array();
        foreach ($orders as $order) {
            if (!$order || !is_a($order, 'WC_Order') || is_a($order, 'WC_Order_Refund')) {
                continue;
            }
            $o_dt = $order->get_date_created();
            $phone = method_exists($order, 'get_billing_phone') ? $order->get_billing_phone() : '—';
            $first_name = method_exists($order, 'get_billing_first_name') ? $order->get_billing_first_name() : '';
            $last_name = method_exists($order, 'get_billing_last_name') ? $order->get_billing_last_name() : '';
            $city = method_exists($order, 'get_billing_city') ? $order->get_billing_city() : 'Dhaka';
            $list[] = array(
                'id'           => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'customer'     => trim($first_name . ' ' . $last_name) ?: 'Guest Connoisseur',
                'phone'        => $phone ?: '—',
                'city'         => $city ?: 'Dhaka',
                'status'       => $order->get_status(),
                'status_name'  => wc_get_order_status_name($order->get_status()),
                'items_count'  => $order->get_item_count(),
                'total'        => wc_price($order->get_total()),
                'payment'      => $order->get_payment_method_title() ?: 'COD',
                'date'         => $o_dt ? wp_date('M j, Y h:i A', $o_dt->getTimestamp()) : '',
                'edit_url'     => admin_url('admin.php?page=kinboni-orders&order_id=' . $order->get_id()),
            );
        }

        return $list;
    }

    /**
     * Bestselling Products
     */
    public function get_bestselling_products($limit = 5) {
        $products = wc_get_products(array(
            'status'  => 'publish',
            'limit'   => $limit,
            'orderby' => 'total_sales',
            'order'   => 'DESC',
        ));

        $list = array();
        foreach ($products as $p) {
            $img = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail');
            $list[] = array(
                'id'          => $p->get_id(),
                'name'        => $p->get_name(),
                'image'       => $img,
                'price'       => wc_price($p->get_price()),
                'total_sales' => (int) get_post_meta($p->get_id(), 'total_sales', true),
                'stock'       => $p->is_in_stock() ? ($p->get_stock_quantity() !== null ? $p->get_stock_quantity() . ' in stock' : 'In Stock') : 'Out of Stock',
                'url'         => get_permalink($p->get_id()),
            );
        }

        return $list;
    }

    /**
     * Top Selling Product Categories
     */
    public function get_top_categories($limit = 5) {
        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'orderby'    => 'count',
            'order'      => 'DESC',
            'hide_empty' => true,
            'number'     => $limit,
        ));

        $list = array();
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $t) {
                $list[] = array(
                    'name'  => $t->name,
                    'count' => $t->count,
                    'link'  => get_term_link($t),
                );
            }
        }
        return $list;
    }

    /**
     * Visitor & Traffic Analytics from Real Kinboni Tracker
     */
    public function get_visitor_analytics($range = '30d') {
        global $wpdb;
        $table_analytics = $wpdb->prefix . 'kinboni_analytics';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_analytics}'") !== $table_analytics) {
            return array('total_views' => 0, 'unique_visitors' => 0, 'chart_labels' => array(), 'chart_views' => array());
        }

        $now = current_time('timestamp');
        switch ($range) {
            case 'today':
                $start = wp_date('Y-m-d 00:00:00', $now);
                break;
            case '7d':
                $start = wp_date('Y-m-d 00:00:00', strtotime('-6 days', $now));
                break;
            case '12m':
                $start = wp_date('Y-m-01 00:00:00', strtotime('-11 months', $now));
                break;
            case '30d':
            default:
                $start = wp_date('Y-m-d 00:00:00', strtotime('-29 days', $now));
                break;
        }

        $total_views = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$table_analytics} WHERE created_at >= %s",
            $start
        ));

        $unique_visitors = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT visitor_id) FROM {$table_analytics} WHERE created_at >= %s",
            $start
        ));

        // Group by day for the chart
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as visit_date, COUNT(id) as view_count 
             FROM {$table_analytics} 
             WHERE created_at >= %s 
             GROUP BY DATE(created_at) 
             ORDER BY visit_date ASC",
            $start
        ));

        $chart_labels = array();
        $chart_views  = array();
        foreach ($rows as $row) {
            $chart_labels[] = wp_date('M j', strtotime($row->visit_date));
            $chart_views[]  = (int) $row->view_count;
        }

        return array(
            'total_views'     => $total_views,
            'unique_visitors' => $unique_visitors,
            'chart_labels'    => $chart_labels,
            'chart_views'     => $chart_views,
        );
    }
}

