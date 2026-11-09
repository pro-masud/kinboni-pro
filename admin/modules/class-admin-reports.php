<?php
/**
 * Kinboni Admin Module: Reports & Financial P&L Engine
 *
 * Implements:
 * - Sales Reports (Daily, Weekly, Monthly, Yearly & Custom Date Breakdown)
 * - Financial Cost of Goods Sold (COGS) & Gross Profit Margin Calculation
 * - Automated Weekly/Monthly Email Reports via WP-Cron
 * - Printable Luxury Executive Report / PDF Export
 * - CSV & Excel-Compatible Financial Export
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Reports {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_save_report_settings', array($this, 'ajax_save_report_settings'));
        add_action('wp_ajax_kinboni_send_instant_report', array($this, 'ajax_send_instant_report'));

        // WP-Cron Scheduled Reporting (Weekly & Monthly)
        add_action('kinboni_cron_weekly_report', array($this, 'dispatch_scheduled_report'));
        add_action('kinboni_cron_monthly_report', array($this, 'dispatch_scheduled_monthly_report'));
        if (!wp_next_scheduled('kinboni_cron_weekly_report')) {
            wp_schedule_event(time() + 86400, 'weekly', 'kinboni_cron_weekly_report');
        }

        // Export & Printable Triggers
        add_action('admin_init', array($this, 'handle_exports'));
    }

    /**
     * Render the Reports Admin Page
     */
    public function render() {
        $period     = isset($_GET['period']) ? sanitize_key(wp_unslash($_GET['period'])) : 'monthly';
        $start_date = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : '';
        $end_date   = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : '';

        $report_data = $this->generate_financial_report($period, $start_date, $end_date);
        $settings    = $this->get_report_settings();

        $view_file = KINBONI_CORE_PATH . 'admin/views/view-reports.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Reports view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Retrieve Automated Reporting Settings
     */
    public function get_report_settings() {
        return array(
            'automated_weekly'  => get_option('kinboni_automated_weekly_report', 'yes'),
            'automated_monthly' => get_option('kinboni_automated_monthly_report', 'yes'),
            'recipients'        => get_option('kinboni_report_recipients', get_option('admin_email')),
            'default_cogs_pct'  => (float) get_option('kinboni_default_cogs_pct', 60),
        );
    }

    /**
     * AJAX: Save Report Settings
     */
    public function ajax_save_report_settings() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('reports') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $weekly    = isset($_POST['automated_weekly']) && wp_unslash($_POST['automated_weekly']) === 'yes' ? 'yes' : 'no';
        $monthly   = isset($_POST['automated_monthly']) && wp_unslash($_POST['automated_monthly']) === 'yes' ? 'yes' : 'no';
        $recip     = isset($_POST['recipients']) ? sanitize_text_field(wp_unslash($_POST['recipients'])) : get_option('admin_email');
        $cogs_pct  = isset($_POST['default_cogs_pct']) ? max(1, min(99, (float) $_POST['default_cogs_pct'])) : 60;

        update_option('kinboni_automated_weekly_report', $weekly);
        update_option('kinboni_automated_monthly_report', $monthly);
        update_option('kinboni_report_recipients', $recip);
        update_option('kinboni_default_cogs_pct', $cogs_pct);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('report_settings_updated', 'Updated automated financial reporting preferences.');
        }

        wp_send_json_success(array('message' => esc_html__('Reporting preferences saved successfully.', 'kinboni-core')));
    }

    /**
     * Parse Date Bounds for Reporting
     */
    public function parse_report_dates($period, $custom_start = '', $custom_end = '') {
        $now = current_time('timestamp');

        if (!empty($custom_start) && !empty($custom_end)) {
            return array(
                'start' => date('Y-m-d 00:00:00', strtotime($custom_start)),
                'end'   => date('Y-m-d 23:59:59', strtotime($custom_end)),
                'label' => date('M j, Y', strtotime($custom_start)) . ' – ' . date('M j, Y', strtotime($custom_end)),
                'type'  => 'custom',
            );
        }

        switch ($period) {
            case 'daily':
                $start = date('Y-m-d 00:00:00', $now);
                $end   = date('Y-m-d 23:59:59', $now);
                $label = 'Today (' . date('M j, Y', $now) . ')';
                break;

            case 'weekly':
                $start = date('Y-m-d 00:00:00', strtotime('-6 days', $now));
                $end   = date('Y-m-d 23:59:59', $now);
                $label = 'Last 7 Days';
                break;

            case 'yearly':
                $start = date('Y-01-01 00:00:00', $now);
                $end   = date('Y-12-31 23:59:59', $now);
                $label = 'This Year (' . date('Y', $now) . ')';
                break;

            case 'monthly':
            default:
                $start = date('Y-m-01 00:00:00', $now);
                $end   = date('Y-m-t 23:59:59', $now);
                $label = 'This Month (' . date('F Y', $now) . ')';
                break;
        }

        return array('start' => $start, 'end' => $end, 'label' => $label, 'type' => $period);
    }

    /**
     * Generate Comprehensive Financial P&L & Sales Report (HPOS Compatible)
     */
    public function generate_financial_report($period = 'monthly', $custom_start = '', $custom_end = '') {
        $bounds = $this->parse_report_dates($period, $custom_start, $custom_end);
        $start_ts = strtotime($bounds['start']);
        $end_ts   = strtotime($bounds['end']);

        $default_cogs_pct = ((float) get_option('kinboni_default_cogs_pct', 60)) / 100;

        // Fetch HPOS orders within window (only paid/confirmed orders: completed & processing)
        $orders = wc_get_orders(array(
            'limit'        => -1,
            'date_created' => $bounds['start'] . '...' . $bounds['end'],
            'status'       => array('wc-completed', 'wc-processing'),
            'orderby'      => 'date',
            'order'        => 'DESC',
        ));

        $total_orders     = count($orders);
        $total_revenue    = 0;
        $total_shipping   = 0;
        $total_discount   = 0;
        $total_cogs       = 0;

        $breakdown_map = array();
        $product_profit_map = array();

        foreach ($orders as $order) {
            $ord_refunded = (float) $order->get_total_refunded();
            $ord_total    = max(0, (float) $order->get_total() - $ord_refunded);
            $ord_shipping = (float) $order->get_shipping_total();
            $ord_discount = (float) $order->get_discount_total();
            $ord_date     = $order->get_date_created();
            $date_key     = $ord_date ? wp_date(($period === 'yearly') ? 'Y-m' : 'Y-m-d', $ord_date->getTimestamp()) : current_time(($period === 'yearly') ? 'Y-m' : 'Y-m-d');

            $total_revenue  += $ord_total;
            $total_shipping += $ord_shipping;
            $total_discount += $ord_discount;

            $order_cogs = 0;

            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $qty     = (int) $item->get_quantity();
                $line_tot= (float) $item->get_total();

                $unit_cost = 0;
                if ($product) {
                    $saved_cost = get_post_meta($product->get_id(), '_kinboni_cost_price', true);
                    if ($saved_cost !== '' && is_numeric($saved_cost)) {
                        $unit_cost = (float) $saved_cost;
                    } else {
                        // Fallback estimation using default COGS percentage
                        $unit_cost = (float) $product->get_price() * $default_cogs_pct;
                    }

                    // Product profit tracking
                    $pid = $product->get_id();
                    if (!isset($product_profit_map[$pid])) {
                        $product_profit_map[$pid] = array(
                            'name'     => $product->get_name(),
                            'sku'      => $product->get_sku() ?: '—',
                            'qty_sold' => 0,
                            'revenue'  => 0,
                            'cogs'     => 0,
                            'profit'   => 0,
                        );
                    }
                    $product_profit_map[$pid]['qty_sold'] += $qty;
                    $product_profit_map[$pid]['revenue']  += $line_tot;
                    $product_profit_map[$pid]['cogs']     += ($unit_cost * $qty);
                    $product_profit_map[$pid]['profit']   += ($line_tot - ($unit_cost * $qty));
                }

                $order_cogs += ($unit_cost * $qty);
            }

            $total_cogs += $order_cogs;

            // Grouping for table breakdown
            if (!isset($breakdown_map[$date_key])) {
                $breakdown_map[$date_key] = array(
                    'date_key'    => $date_key,
                    'date_label'  => ($period === 'yearly') ? date('F Y', strtotime($date_key . '-01')) : date('M j, Y (D)', strtotime($date_key)),
                    'orders'      => 0,
                    'gross_sales' => 0,
                    'shipping'    => 0,
                    'discount'    => 0,
                    'cogs'        => 0,
                    'profit'      => 0,
                );
            }

            $breakdown_map[$date_key]['orders']      += 1;
            $breakdown_map[$date_key]['gross_sales'] += $ord_total;
            $breakdown_map[$date_key]['shipping']    += $ord_shipping;
            $breakdown_map[$date_key]['discount']    += $ord_discount;
            $breakdown_map[$date_key]['cogs']        += $order_cogs;
            $breakdown_map[$date_key]['profit']      += ($ord_total - $ord_shipping - $order_cogs);
        }

        $gross_profit = ($total_revenue - $total_shipping) - $total_cogs;
        $gross_margin = ($total_revenue > 0) ? round(($gross_profit / $total_revenue) * 100, 2) : 0;
        $aov          = ($total_orders > 0) ? round($total_revenue / $total_orders, 2) : 0;

        // Sort Top Profitable Products
        usort($product_profit_map, function ($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        return array(
            'bounds'          => $bounds,
            'summary'         => array(
                'total_orders'   => $total_orders,
                'total_revenue'  => $total_revenue,
                'total_shipping' => $total_shipping,
                'total_discount' => $total_discount,
                'total_cogs'     => $total_cogs,
                'gross_profit'   => $gross_profit,
                'gross_margin'   => $gross_margin,
                'aov'            => $aov,
            ),
            'breakdown'       => array_values($breakdown_map),
            'top_products'    => array_slice($product_profit_map, 0, 8),
        );
    }

    /**
     * AJAX: Send Instant Executive Report via Email
     */
    public function ajax_send_instant_report() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('reports') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $period = isset($_POST['period']) ? sanitize_key(wp_unslash($_POST['period'])) : 'weekly';
        $report = $this->generate_financial_report($period);

        $recipients_str = get_option('kinboni_report_recipients', get_option('admin_email'));
        $recipients = array_filter(array_map('trim', explode(',', $recipients_str)));
        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }

        $subject = sprintf('📊 [Executive P&L Report] KINBONI Pro %s Summary', $report['bounds']['label']);
        $html_body = $this->build_report_email_html($report);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = class_exists('Kinboni_Core')
            ? Kinboni_Core::send_mail($recipients, $subject, $html_body, $headers, 'financial_report')
            : wp_mail($recipients, $subject, $html_body, $headers);

        if ($sent) {
            wp_send_json_success(array(
                'message' => sprintf(esc_html__('Executive report successfully dispatched to: %s', 'kinboni-core'), implode(', ', $recipients))
            ));
        } else {
            wp_send_json_error(array('message' => esc_html__('Failed to send report. Please check server SMTP.', 'kinboni-core')));
        }
    }

    /**
     * WP-Cron Triggered Scheduled Report
     */
    public function dispatch_scheduled_report() {
        if (get_option('kinboni_automated_weekly_report', 'yes') !== 'yes') {
            return;
        }

        $report = $this->generate_financial_report('weekly');
        $recipients_str = get_option('kinboni_report_recipients', get_option('admin_email'));
        $recipients = array_filter(array_map('trim', explode(',', $recipients_str)));
        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }

        $subject = sprintf('📈 [Weekly Automated P&L] KINBONI Pro Executive Summary (%s)', $report['bounds']['label']);
        $html_body = $this->build_report_email_html($report);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (class_exists('Kinboni_Core')) {
            Kinboni_Core::send_mail($recipients, $subject, $html_body, $headers, 'scheduled_financial_report');
        } else {
            wp_mail($recipients, $subject, $html_body, $headers);
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('cron_report_dispatched', 'Automated weekly financial P&L report dispatched.');
        }
    }

    /**
     * WP-Cron Triggered Monthly Scheduled Report
     */
    public function dispatch_scheduled_monthly_report() {
        if (get_option('kinboni_automated_monthly_report', 'no') !== 'yes') {
            return;
        }

        $report = $this->generate_financial_report('monthly');
        $recipients_str = get_option('kinboni_report_recipients', get_option('admin_email'));
        $recipients = array_filter(array_map('trim', explode(',', $recipients_str)));
        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }

        $subject = sprintf('📊 [Monthly Automated P&L] KINBONI Pro Executive Summary (%s)', $report['bounds']['label']);
        $html_body = $this->build_report_email_html($report);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (class_exists('Kinboni_Core')) {
            Kinboni_Core::send_mail($recipients, $subject, $html_body, $headers, 'scheduled_monthly_financial_report');
        } else {
            wp_mail($recipients, $subject, $html_body, $headers);
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('cron_monthly_report_dispatched', 'Automated monthly financial P&L report dispatched.');
        }
    }

    /**
     * Build HTML for Executive Email Report
     */
    private function build_report_email_html($report) {
        $s = $report['summary'];
        $c = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="font-family:-apple-system,sans-serif; background:#F8F7F4; padding:30px 10px; margin:0;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <table width="600" style="background:#FFF; border-radius:8px; border:1px solid #D4AF37; overflow:hidden;">
                            <tr>
                                <td style="background:#121214; padding:28px 24px; text-align:center;">
                                    <div style="font-size:22px; font-weight:800; color:#D4AF37; letter-spacing:2px;">KINBONI PRO</div>
                                    <div style="font-size:11px; color:#9CA3AF; margin-top:4px;">Executive Sales & Profit Report &bull; <?php echo esc_html($report['bounds']['label']); ?></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:28px 24px;">
                                    <table width="100%" cellpadding="10" cellspacing="0" style="background:#FAF9F6; border:1px solid #E8E6DF; border-radius:6px; margin-bottom:20px;">
                                        <tr>
                                            <td><strong>Total Revenue:</strong></td>
                                            <td align="right" style="font-size:16px; font-weight:bold; color:#121214;"><?php echo esc_html($c . number_format($s['total_revenue'], 2)); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Cost of Goods Sold (COGS):</strong></td>
                                            <td align="right" style="color:#DC2626;"><?php echo esc_html($c . number_format($s['total_cogs'], 2)); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Gross Profit:</strong></td>
                                            <td align="right" style="font-size:16px; font-weight:bold; color:#059669;"><?php echo esc_html($c . number_format($s['gross_profit'], 2)); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Gross Profit Margin:</strong></td>
                                            <td align="right" style="font-weight:bold; color:#B89628;"><?php echo esc_html($s['gross_margin']); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Orders Processed:</strong></td>
                                            <td align="right"><?php echo esc_html($s['total_orders']); ?></td>
                                        </tr>
                                    </table>

                                    <div style="text-align:center; margin-top:20px;">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-reports')); ?>" style="background:#121214; color:#D4AF37; border:1px solid #D4AF37; padding:10px 24px; text-decoration:none; font-weight:bold; border-radius:4px; font-size:13px;">
                                            View Complete Reports in Dashboard &rarr;
                                        </a>
                                    </div>
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
     * Handle CSV Export & Printable PDF
     */
    public function handle_exports() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'kinboni-reports') return;
        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('reports') : !current_user_can('manage_woocommerce')) return;

        $period     = isset($_GET['period']) ? sanitize_key(wp_unslash($_GET['period'])) : 'monthly';
        $start_date = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : '';
        $end_date   = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : '';

        // Printable HTML / PDF view
        if (isset($_GET['action']) && $_GET['action'] === 'print_report') {
            check_admin_referer('kinboni_reports_export', '_kb_nonce');
            $report = $this->generate_financial_report($period, $start_date, $end_date);

            $print_file = KINBONI_CORE_PATH . 'admin/views/view-report-printable.php';
            if (file_exists($print_file)) {
                include $print_file;
            }
            exit;
        }

        // CSV Export
        if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
            check_admin_referer('kinboni_reports_export', '_kb_nonce');
            $report = $this->generate_financial_report($period, $start_date, $end_date);

            $filename = 'kinboni-financial-report-' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');

            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header Section
            fputcsv($out, array('KINBONI PRO EXECUTIVE FINANCIAL & SALES REPORT'));
            fputcsv($out, array('Period', $report['bounds']['label']));
            fputcsv($out, array('Total Revenue', $report['summary']['total_revenue']));
            fputcsv($out, array('Total Orders', $report['summary']['total_orders']));
            fputcsv($out, array('Cost of Goods Sold (COGS)', $report['summary']['total_cogs']));
            fputcsv($out, array('Gross Profit', $report['summary']['gross_profit']));
            fputcsv($out, array('Profit Margin (%)', $report['summary']['gross_margin']));
            fputcsv($out, array('Average Order Value (AOV)', $report['summary']['aov']));
            fputcsv($out, array());

            // Breakdown
            fputcsv($out, array('--- PERIOD SALES BREAKDOWN ---'));
            fputcsv($out, array('Date', 'Orders', 'Gross Revenue', 'Shipping Fee', 'Discount', 'COGS', 'Gross Profit'));
            foreach ($report['breakdown'] as $b) {
                fputcsv($out, array(
                    $b['date_label'],
                    $b['orders'],
                    $b['gross_sales'],
                    $b['shipping'],
                    $b['discount'],
                    $b['cogs'],
                    $b['profit']
                ));
            }
            fputcsv($out, array());

            // Product Profitability
            fputcsv($out, array('--- PRODUCT PROFITABILITY ---'));
            fputcsv($out, array('Product Name', 'SKU', 'Units Sold', 'Revenue', 'Cost', 'Net Profit'));
            foreach ($report['top_products'] as $p) {
                fputcsv($out, array($p['name'], $p['sku'], $p['qty_sold'], $p['revenue'], $p['cogs'], $p['profit']));
            }

            fclose($out);
            exit;
        }
    }
}

