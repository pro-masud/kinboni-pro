<?php
/**
 * Kinboni Admin Module: Live Analytics & Intelligence Engine
 *
 * Implements:
 * - Real Traffic Analytics (Unique Visitors, Total Page Views, Graph with Today/7D/30D/12M & Custom Range)
 * - Device Breakdown (Desktop vs Mobile vs Tablet)
 * - Traffic Referrer Acquisition (Direct, Google, Facebook, Others)
 * - Visitor-to-Order Conversion Rate & Trends
 * - Search Query Analytics (Top Keywords & Zero-Result Searches)
 * - Wishlist Analytics (Most Coveted Products)
 * - Dynamic Date Range Filtering & CSV Export
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Analytics {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Dynamic Data Fetcher
        add_action('wp_ajax_kinboni_get_analytics_data', array($this, 'ajax_get_analytics_data'));

        // CSV Export Trigger
        add_action('admin_init', array($this, 'handle_export'));
    }

    /**
     * Render the Analytics Admin Page
     */
    public function render() {
        $range      = isset($_GET['range']) ? sanitize_key(wp_unslash($_GET['range'])) : '30d';
        $start_date = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : '';
        $end_date   = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : '';

        $analytics_data = $this->query_analytics_report($range, $start_date, $end_date);

        $view_file = KINBONI_CORE_PATH . 'admin/views/view-analytics.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Analytics view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Parse Date Ranges into Start and End Datetime
     */
    public function parse_date_boundaries($range, $custom_start = '', $custom_end = '') {
        $now = current_time('timestamp');

        if (!empty($custom_start) && !empty($custom_end)) {
            $start_dt = wp_date('Y-m-d 00:00:00', strtotime($custom_start));
            $end_dt   = wp_date('Y-m-d 23:59:59', strtotime($custom_end));
            $range_label = wp_date('M j, Y', strtotime($custom_start)) . ' – ' . wp_date('M j, Y', strtotime($custom_end));
            return array('start' => $start_dt, 'end' => $end_dt, 'label' => $range_label, 'type' => 'custom');
        }

        switch ($range) {
            case 'today':
                $start_dt = wp_date('Y-m-d 00:00:00', $now);
                $end_dt   = wp_date('Y-m-d 23:59:59', $now);
                $label    = 'Today (' . wp_date('M j, Y', $now) . ')';
                break;

            case '7d':
                $start_dt = wp_date('Y-m-d 00:00:00', strtotime('-7 days', $now));
                $end_dt   = wp_date('Y-m-d 23:59:59', $now);
                $label    = 'Last 7 Days';
                break;

            case '12m':
                $start_dt = wp_date('Y-m-01 00:00:00', strtotime('-11 months', $now));
                $end_dt   = wp_date('Y-m-d 23:59:59', $now);
                $label    = 'Last 12 Months';
                break;

            case '30d':
            default:
                $start_dt = wp_date('Y-m-d 00:00:00', strtotime('-30 days', $now));
                $end_dt   = wp_date('Y-m-d 23:59:59', $now);
                $label    = 'Last 30 Days';
                break;
        }

        return array('start' => $start_dt, 'end' => $end_dt, 'label' => $label, 'type' => $range);
    }

    /**
     * Query Complete Analytics Report from Real DB Tables
     */
    public function query_analytics_report($range = '30d', $custom_start = '', $custom_end = '') {
        global $wpdb;
        $t_analytics = $wpdb->prefix . 'kinboni_analytics';
        $t_searches  = $wpdb->prefix . 'kinboni_searches';

        $bounds = $this->parse_date_boundaries($range, $custom_start, $custom_end);
        $start  = $bounds['start'];
        $end    = $bounds['end'];

        // 1. Core KPIs
        $unique_visitors = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT visitor_id) FROM {$t_analytics} WHERE created_at BETWEEN %s AND %s",
            $start, $end
        ));

        $page_views = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$t_analytics} WHERE created_at BETWEEN %s AND %s",
            $start, $end
        ));

        // HPOS Compatible Orders in this period
        $orders = wc_get_orders(array(
            'type'         => 'shop_order',
            'limit'        => -1,
            'date_created' => $start . '...' . $end,
            'return'       => 'ids',
        ));
        $orders_count = count($orders);

        // Conversion Rate
        $conversion_rate = ($unique_visitors > 0) ? round(($orders_count / $unique_visitors) * 100, 2) : 0;

        // 2. Time-series Chart Data (Daily / Monthly)
        $chart_data = $this->build_chart_timeseries($range, $start, $end);

        // 3. Traffic Sources Breakdown
        $sources_raw = $wpdb->get_results($wpdb->prepare(
            "SELECT referer, COUNT(id) as cnt FROM {$t_analytics} 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY referer ORDER BY cnt DESC",
            $start, $end
        ), ARRAY_A);

        $source_counts = array(
            'Direct'    => 0,
            'Google'    => 0,
            'Facebook'  => 0,
            'Other'     => 0,
        );
        $top_referrers = array();

        foreach ($sources_raw as $row) {
            $ref = strtolower(trim($row['referer']));
            $cnt = (int) $row['cnt'];

            if (empty($ref)) {
                $source_counts['Direct'] += $cnt;
            } elseif (strpos($ref, 'google') !== false) {
                $source_counts['Google'] += $cnt;
            } elseif (strpos($ref, 'facebook') !== false || strpos($ref, 'instagram') !== false || strpos($ref, 'fb.me') !== false) {
                $source_counts['Facebook'] += $cnt;
            } else {
                $source_counts['Other'] += $cnt;
                $top_referrers[] = array('host' => $row['referer'], 'count' => $cnt);
            }
        }

        $total_traffic = max(1, $page_views);
        $traffic_sources = array(
            'Direct'   => array('count' => $source_counts['Direct'], 'pct' => round(($source_counts['Direct'] / $total_traffic) * 100, 1)),
            'Google'   => array('count' => $source_counts['Google'], 'pct' => round(($source_counts['Google'] / $total_traffic) * 100, 1)),
            'Facebook' => array('count' => $source_counts['Facebook'], 'pct' => round(($source_counts['Facebook'] / $total_traffic) * 100, 1)),
            'Other'    => array('count' => $source_counts['Other'], 'pct' => round(($source_counts['Other'] / $total_traffic) * 100, 1)),
        );

        // 4. Device Breakdown
        $devices_raw = $wpdb->get_results($wpdb->prepare(
            "SELECT device, COUNT(id) as cnt FROM {$t_analytics} 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY device ORDER BY cnt DESC",
            $start, $end
        ), ARRAY_A);

        $device_stats = array('desktop' => 0, 'mobile' => 0, 'tablet' => 0);
        foreach ($devices_raw as $d) {
            $dev = strtolower($d['device']);
            if (isset($device_stats[$dev])) {
                $device_stats[$dev] = (int) $d['cnt'];
            }
        }
        $device_pct = array(
            'desktop' => round(($device_stats['desktop'] / $total_traffic) * 100, 1),
            'mobile'  => round(($device_stats['mobile'] / $total_traffic) * 100, 1),
            'tablet'  => round(($device_stats['tablet'] / $total_traffic) * 100, 1),
        );

        // 5. Most Viewed Pages
        $top_pages = $wpdb->get_results($wpdb->prepare(
            "SELECT url, page_type, COUNT(id) as views, COUNT(DISTINCT visitor_id) as visitors 
             FROM {$t_analytics} 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY url, page_type 
             ORDER BY views DESC LIMIT 10",
            $start, $end
        ), ARRAY_A);

        // 6. Most Viewed Products
        $top_products = array();
        $prod_views = $wpdb->get_results($wpdb->prepare(
            "SELECT page_id, COUNT(id) as views, COUNT(DISTINCT visitor_id) as visitors 
             FROM {$t_analytics} 
             WHERE page_type = 'product' AND page_id > 0 AND created_at BETWEEN %s AND %s 
             GROUP BY page_id ORDER BY views DESC LIMIT 8",
            $start, $end
        ), ARRAY_A);

        foreach ($prod_views as $pv) {
            $product = wc_get_product($pv['page_id']);
            if ($product) {
                $top_products[] = array(
                    'id'            => $product->get_id(),
                    'name'          => $product->get_name(),
                    'sku'           => $product->get_sku() ?: '—',
                    'price'         => $product->get_price_html(),
                    'views'         => (int) $pv['views'],
                    'visitors'      => (int) $pv['visitors'],
                    'total_sold'    => (int) $product->get_total_sales(),
                    'thumbnail'     => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src(),
                );
            }
        }

        // If no product views logged in analytics table yet, fallback to top products with _kinboni_views_count postmeta
        if (empty($top_products)) {
            $viewed_posts = get_posts(array(
                'post_type'      => 'product',
                'posts_per_page' => 8,
                'meta_key'       => '_kinboni_views_count',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            ));
            foreach ($viewed_posts as $post) {
                $product = wc_get_product($post->ID);
                if ($product) {
                    $top_products[] = array(
                        'id'            => $product->get_id(),
                        'name'          => $product->get_name(),
                        'sku'           => $product->get_sku() ?: '—',
                        'price'         => $product->get_price_html(),
                        'views'         => (int) get_post_meta($product->get_id(), '_kinboni_views_count', true),
                        'visitors'      => (int) get_post_meta($product->get_id(), '_kinboni_views_count', true),
                        'total_sold'    => (int) $product->get_total_sales(),
                        'thumbnail'     => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src(),
                    );
                }
            }
        }

        // 7. Search Analytics (Frequent Keywords & Zero-Result Searches)
        $top_searches = $wpdb->get_results($wpdb->prepare(
            "SELECT search_term, COUNT(id) as count, AVG(results_count) as avg_results 
             FROM {$t_searches} 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY search_term ORDER BY count DESC LIMIT 10",
            $start, $end
        ), ARRAY_A);

        $zero_searches = $wpdb->get_results($wpdb->prepare(
            "SELECT search_term, COUNT(id) as count 
             FROM {$t_searches} 
             WHERE results_count = 0 AND created_at BETWEEN %s AND %s 
             GROUP BY search_term ORDER BY count DESC LIMIT 10",
            $start, $end
        ), ARRAY_A);

        // 8. Wishlist Analytics (Most Frequently Wishlisted Products)
        $wishlisted_posts = get_posts(array(
            'post_type'      => 'product',
            'posts_per_page' => 10,
            'meta_key'       => '_kinboni_wishlist_count',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ));

        $wishlist_items = array();
        foreach ($wishlisted_posts as $p) {
            $product = wc_get_product($p->ID);
            $w_count = (int) get_post_meta($p->ID, '_kinboni_wishlist_count', true);
            if ($product && $w_count > 0) {
                $wishlist_items[] = array(
                    'id'             => $product->get_id(),
                    'name'           => $product->get_name(),
                    'sku'            => $product->get_sku() ?: '—',
                    'price'          => $product->get_price_html(),
                    'stock_status'   => $product->get_stock_status(),
                    'wishlist_count' => $w_count,
                    'total_sold'     => (int) $product->get_total_sales(),
                    'thumbnail'      => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src(),
                );
            }
        }

        return array(
            'bounds'          => $bounds,
            'metrics'         => array(
                'unique_visitors' => $unique_visitors,
                'page_views'      => $page_views,
                'orders_count'    => $orders_count,
                'conversion_rate' => $conversion_rate,
            ),
            'chart_data'      => $chart_data,
            'traffic_sources' => $traffic_sources,
            'device_stats'    => $device_stats,
            'device_pct'      => $device_pct,
            'top_pages'       => $top_pages,
            'top_products'    => $top_products,
            'top_searches'    => $top_searches,
            'zero_searches'   => $zero_searches,
            'wishlist_items'  => $wishlist_items,
        );
    }

    /**
     * Build Daily or Monthly Time Series for Canvas Graph
     */
    private function build_chart_timeseries($range, $start, $end) {
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_analytics';

        $labels      = array();
        $visitors    = array();
        $pageviews   = array();
        $orders_pts  = array();
        $conversions = array();

        // Pre-aggregate HPOS WooCommerce orders in date range
        $orders_in_range = wc_get_orders(array(
            'type'         => 'shop_order',
            'limit'        => -1,
            'date_created' => $start . '...' . $end,
            'status'       => array('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending'),
        ));

        $order_buckets = array();
        foreach ($orders_in_range as $ord) {
            $created = $ord->get_date_created();
            if (!$created) continue;

            if ($range === 'today') {
                $bucket_key = (int) $created->date('H');
            } elseif ($range === '12m') {
                $bucket_key = $created->date('Y-m');
            } else {
                $bucket_key = $created->date('Y-m-d');
            }

            if (!isset($order_buckets[$bucket_key])) {
                $order_buckets[$bucket_key] = 0;
            }
            $order_buckets[$bucket_key]++;
        }

        if ($range === 'today') {
            // Group by hour
            for ($h = 0; $h < 24; $h++) {
                $hour_str = sprintf('%02d:00', $h);
                $labels[] = $hour_str;
                $h_start = wp_date('Y-m-d H:00:00', strtotime($start . " +{$h} hours"));
                $h_end   = wp_date('Y-m-d H:59:59', strtotime($start . " +{$h} hours"));

                $vis = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM {$table} WHERE created_at BETWEEN %s AND %s",
                    $h_start, $h_end
                ));
                $pvs = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(id) FROM {$table} WHERE created_at BETWEEN %s AND %s",
                    $h_start, $h_end
                ));

                $ord_cnt = $order_buckets[$h] ?? 0;
                $conv    = ($vis > 0) ? round(($ord_cnt / $vis) * 100, 1) : 0;

                $visitors[]    = $vis;
                $pageviews[]   = $pvs;
                $orders_pts[]  = $ord_cnt;
                $conversions[] = $conv;
            }
        } elseif ($range === '12m') {
            // Group by month
            $start_time = strtotime($start);
            for ($i = 0; $i < 12; $i++) {
                $m_time = strtotime("+{$i} months", $start_time);
                $m_label = wp_date('M Y', $m_time);
                $labels[] = $m_label;

                $m_start = wp_date('Y-m-01 00:00:00', $m_time);
                $m_end   = wp_date('Y-m-t 23:59:59', $m_time);

                $vis = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM {$table} WHERE created_at BETWEEN %s AND %s",
                    $m_start, $m_end
                ));
                $pvs = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(id) FROM {$table} WHERE created_at BETWEEN %s AND %s",
                    $m_start, $m_end
                ));

                $m_key   = wp_date('Y-m', $m_time);
                $ord_cnt = $order_buckets[$m_key] ?? 0;
                $conv    = ($vis > 0) ? round(($ord_cnt / $vis) * 100, 1) : 0;

                $visitors[]    = $vis;
                $pageviews[]   = $pvs;
                $orders_pts[]  = $ord_cnt;
                $conversions[] = $conv;
            }
        } else {
            // Daily group (7D, 30D or custom range)
            $start_ts = strtotime($start);
            $end_ts   = strtotime($end);
            $days_count = max(1, min(60, ceil(($end_ts - $start_ts) / 86400)));

            for ($d = 0; $d <= $days_count; $d++) {
                $day_time = strtotime("+{$d} days", $start_ts);
                if ($day_time > $end_ts) break;

                $labels[] = wp_date('M j', $day_time);
                $day_start = wp_date('Y-m-d 00:00:00', $day_time);
                $day_end   = wp_date('Y-m-d 23:59:59', $day_time);

                $vis = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM {$table} WHERE created_at BETWEEN %s AND %s",
                    $day_start, $day_end
                ));
                $pvs = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(id) FROM {$table} WHERE created_at BETWEEN %s AND %s",
                    $day_start, $day_end
                ));

                $d_key   = wp_date('Y-m-d', $day_time);
                $ord_cnt = $order_buckets[$d_key] ?? 0;
                $conv    = ($vis > 0) ? round(($ord_cnt / $vis) * 100, 1) : 0;

                $visitors[]    = $vis;
                $pageviews[]   = $pvs;
                $orders_pts[]  = $ord_cnt;
                $conversions[] = $conv;
            }
        }

        return array(
            'labels'      => $labels,
            'visitors'    => $visitors,
            'pageviews'   => $pageviews,
            'orders'      => $orders_pts,
            'conversions' => $conversions,
        );
    }

    /**
     * AJAX Endpoint: Fetch Analytics Data for Chart & KPIs
     */
    public function ajax_get_analytics_data() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('analytics') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $range       = isset($_POST['range']) ? sanitize_key(wp_unslash($_POST['range'])) : '30d';
        $start_date  = isset($_POST['start_date']) ? sanitize_text_field(wp_unslash($_POST['start_date'])) : '';
        $end_date    = isset($_POST['end_date']) ? sanitize_text_field(wp_unslash($_POST['end_date'])) : '';

        $data = $this->query_analytics_report($range, $start_date, $end_date);
        wp_send_json_success($data);
    }

    /**
     * CSV Export of Analytics
     */
    public function handle_export() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'kinboni-analytics') return;
        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('analytics') : !current_user_can('manage_woocommerce')) return;

        if (isset($_GET['action']) && $_GET['action'] === 'export_analytics') {
            check_admin_referer('kinboni_analytics_export', '_kb_nonce');

            $range      = isset($_GET['range']) ? sanitize_key(wp_unslash($_GET['range'])) : '30d';
            $start_date = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : '';
            $end_date   = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : '';

            $report = $this->query_analytics_report($range, $start_date, $end_date);

            $filename = 'kinboni-analytics-' . wp_date('Y-m-d') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');

            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header summary
            fputcsv($out, array('Period', $report['bounds']['label']));
            fputcsv($out, array('Unique Visitors', $report['metrics']['unique_visitors']));
            fputcsv($out, array('Total Page Views', $report['metrics']['page_views']));
            fputcsv($out, array('Conversion Rate (%)', $report['metrics']['conversion_rate']));
            fputcsv($out, array());

            // Top Pages
            fputcsv($out, array('--- TOP VIEWED PAGES ---'));
            fputcsv($out, array('Page URL', 'Page Type', 'Views', 'Unique Visitors'));
            foreach ($report['top_pages'] as $p) {
                fputcsv($out, array($p['url'], $p['page_type'], $p['views'], $p['visitors']));
            }
            fputcsv($out, array());

            // Top Searches
            fputcsv($out, array('--- FREQUENT SEARCH QUERIES ---'));
            fputcsv($out, array('Search Keyword', 'Search Count', 'Avg Results Found'));
            foreach ($report['top_searches'] as $s) {
                fputcsv($out, array($s['search_term'], $s['count'], round($s['avg_results'], 1)));
            }

            fclose($out);
            exit;
        }
    }
}

