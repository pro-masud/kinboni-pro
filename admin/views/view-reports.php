<?php
/**
 * Kinboni Pro Admin View: Reports & Financial P&L Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $report_data
 * @var array $settings
 */

if (!defined('ABSPATH')) {
    exit;
}

$bounds       = $report_data['bounds'] ?? array();
$summary      = $report_data['summary'] ?? array();
$breakdown    = $report_data['breakdown'] ?? array();
$top_products = $report_data['top_products'] ?? array();
$current_period = $bounds['type'] ?? 'monthly';

$c = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';

$export_csv_url = wp_nonce_url(add_query_arg(array(
    'page'   => 'kinboni-reports',
    'action' => 'export_csv',
    'period' => $current_period,
    'start_date' => $_GET['start_date'] ?? '',
    'end_date'   => $_GET['end_date'] ?? '',
), admin_url('admin.php')), 'kinboni_reports_export', '_kb_nonce');

$print_report_url = wp_nonce_url(add_query_arg(array(
    'page'   => 'kinboni-reports',
    'action' => 'print_report',
    'period' => $current_period,
    'start_date' => $_GET['start_date'] ?? '',
    'end_date'   => $_GET['end_date'] ?? '',
), admin_url('admin.php')), 'kinboni_reports_export', '_kb_nonce');
?>

<div class="kinboni-admin-wrap" id="kinboni-reports-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Reports & Financial P&L</span></h1>
                <p class="kinboni-subtitle">Period: <strong><?php echo esc_html($bounds['label']); ?></strong> &bull; Executive Profitability & COGS Audit</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <!-- Period Switcher Pills -->
            <div class="kinboni-pills-group" id="reports-period-selector">
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-reports&period=daily')); ?>" class="kinboni-pill <?php echo $current_period === 'daily' ? 'is-active' : ''; ?>">Today</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-reports&period=weekly')); ?>" class="kinboni-pill <?php echo $current_period === 'weekly' ? 'is-active' : ''; ?>">7 Days</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-reports&period=monthly')); ?>" class="kinboni-pill <?php echo $current_period === 'monthly' ? 'is-active' : ''; ?>">This Month</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-reports&period=yearly')); ?>" class="kinboni-pill <?php echo $current_period === 'yearly' ? 'is-active' : ''; ?>">This Year</a>
            </div>

            <!-- Print / PDF Report -->
            <a href="<?php echo esc_url($print_report_url); ?>" target="_blank" class="btn-action-gold" title="Open Clean Printable PDF View">
                <span class="dashicons dashicons-printer"></span> Print / PDF
            </a>

            <!-- Export CSV -->
            <a href="<?php echo esc_url($export_csv_url); ?>" class="btn-action-outline" title="Export Financial Data to CSV / Excel">
                <span class="dashicons dashicons-download"></span> Export CSV
            </a>

            <!-- Dispatch Instant Email -->
            <button type="button" id="btn-send-instant-report" class="btn-action-outline" data-period="<?php echo esc_attr($current_period); ?>" title="Send Email Executive Report Now">
                <span class="dashicons dashicons-email-alt"></span> Email Report
            </button>
        </div>
    </header>

    <!-- Custom Date Filter Toolbar -->
    <div class="kinboni-toolbar-panel" style="margin-bottom: 20px;">
        <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="kinboni-filter-form">
            <input type="hidden" name="page" value="kinboni-reports" />
            <span class="text-xs font-bold text-dark" style="margin-right: 8px;">Custom Financial Window:</span>

            <div class="filter-group">
                <label class="text-xs text-subtle" style="margin-right: 4px;">Start:</label>
                <input type="date" name="start_date" value="<?php echo esc_attr($_GET['start_date'] ?? ''); ?>" class="form-control" style="padding: 4px 10px; font-size: 13px;" required />
            </div>

            <div class="filter-group">
                <label class="text-xs text-subtle" style="margin-right: 4px;">End:</label>
                <input type="date" name="end_date" value="<?php echo esc_attr($_GET['end_date'] ?? ''); ?>" class="form-control" style="padding: 4px 10px; font-size: 13px;" required />
            </div>

            <button type="submit" class="btn-filter-apply">Audit Period</button>
            <?php if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-reports')); ?>" class="btn-filter-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Core Financial P&L KPI Strip -->
    <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Gross Revenue</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-money-alt"></span></span>
            </div>
            <h3 class="metric-number"><?php echo esc_html($c . number_format($summary['total_revenue'], 2)); ?></h3>
            <span class="metric-subtle"><?php echo esc_html($summary['total_orders']); ?> Orders Processed</span>
        </div>

        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Cost of Goods (COGS)</span>
                <span class="metric-icon-gold" style="color:#DC2626;background:#FEF2F2;"><span class="dashicons dashicons-cart"></span></span>
            </div>
            <h3 class="metric-number" style="color:#DC2626;"><?php echo esc_html($c . number_format($summary['total_cogs'], 2)); ?></h3>
            <span class="metric-subtle">Product Acquisition Costs</span>
        </div>

        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Gross Profit</span>
                <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-chart-area"></span></span>
            </div>
            <h3 class="metric-number" style="color:#059669;"><?php echo esc_html($c . number_format($summary['gross_profit'], 2)); ?></h3>
            <span class="metric-subtle">Revenue Minus Inventory Cost</span>
        </div>

        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Profit Margin</span>
                <span class="metric-icon-gold" style="color:#D4AF37;background:#FCF9EE;"><span class="dashicons dashicons-awards"></span></span>
            </div>
            <h3 class="metric-number" style="color:#B89628;"><?php echo esc_html($summary['gross_margin']); ?>%</h3>
            <span class="metric-subtle">AOV: <?php echo esc_html($c . number_format($summary['aov'], 2)); ?></span>
        </div>
    </section>

    <!-- Period Sales Breakdown Table -->
    <div class="kinboni-panel" style="margin-bottom: 24px;">
        <div class="panel-header">
            <h3 class="panel-title">Sales & Profit Timeline Breakdown</h3>
        </div>
        <div class="panel-body p-0">
            <?php if (!empty($breakdown)) : ?>
                <table class="kinboni-table">
                    <thead>
                        <tr>
                            <th>Timeline</th>
                            <th width="90" class="text-right">Orders</th>
                            <th width="130" class="text-right">Gross Sales</th>
                            <th width="110" class="text-right">Shipping</th>
                            <th width="110" class="text-right">Discounts</th>
                            <th width="130" class="text-right">COGS</th>
                            <th width="140" class="text-right">Gross Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($breakdown as $row) : ?>
                            <tr>
                                <td>
                                    <div class="font-bold text-dark text-sm"><?php echo esc_html($row['date_label']); ?></div>
                                </td>
                                <td class="text-right">
                                    <span class="badge-status status-completed"><?php echo esc_html($row['orders']); ?></span>
                                </td>
                                <td class="text-right font-bold text-dark"><?php echo esc_html($c . number_format($row['gross_sales'], 2)); ?></td>
                                <td class="text-right text-subtle text-xs"><?php echo esc_html($c . number_format($row['shipping'], 2)); ?></td>
                                <td class="text-right text-subtle text-xs" style="color:#DC2626;">-<?php echo esc_html($c . number_format($row['discount'], 2)); ?></td>
                                <td class="text-right text-subtle text-xs"><?php echo esc_html($c . number_format($row['cogs'], 2)); ?></td>
                                <td class="text-right font-bold" style="color:#059669;"><?php echo esc_html($c . number_format($row['profit'], 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="kinboni-empty-state">
                    <span class="dashicons dashicons-chart-pie"></span>
                    <h3>No Transactions in this Window</h3>
                    <p>There are no completed or processing orders recorded for <?php echo esc_html($bounds['label']); ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Profitability & Automated Reporting Grid -->
    <div class="grid-2col" style="gap: 24px; align-items: start;">

        <!-- Product Profitability Table -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Product Profitability Ranking</h3>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($top_products)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Product Item</th>
                                <th width="80" class="text-right">Sold</th>
                                <th width="120" class="text-right">Revenue</th>
                                <th width="120" class="text-right">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products as $tp) : ?>
                                <tr>
                                    <td>
                                        <div class="font-bold text-dark text-xs"><?php echo esc_html($tp['name']); ?></div>
                                        <div class="text-subtle text-xs">SKU: <?php echo esc_html($tp['sku']); ?></div>
                                    </td>
                                    <td class="text-right font-bold text-dark"><?php echo esc_html($tp['qty_sold']); ?>x</td>
                                    <td class="text-right text-dark text-xs"><?php echo esc_html($c . number_format($tp['revenue'], 2)); ?></td>
                                    <td class="text-right font-bold" style="color:#059669;"><?php echo esc_html($c . number_format($tp['profit'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-tag"></span>
                        <h3>No Sales Recorded</h3>
                        <p>Product profit calculations will appear as transactions complete.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Automated Email Report Settings -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Automated Executive Email Reports</h3>
            </div>
            <form id="form-report-settings" class="panel-body">
                <div class="form-group mb-4">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="automated_weekly" value="yes" <?php checked($settings['automated_weekly'], 'yes'); ?> />
                        <span class="font-bold text-dark">Automated Weekly Executive P&L Email via WP-Cron</span>
                    </label>
                    <p class="text-xs text-subtle mt-1">Dispatches weekly summary of revenue, orders, and profit directly to executive inboxes.</p>
                </div>

                <div class="form-group mb-4">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="automated_monthly" value="yes" <?php checked($settings['automated_monthly'], 'yes'); ?> />
                        <span class="font-bold text-dark">Automated Monthly Fiscal Summary Email</span>
                    </label>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Default Cost of Goods Estimate (% of Retail Price)</label>
                    <input type="number" min="1" max="99" name="default_cogs_pct" class="form-control" value="<?php echo esc_attr($settings['default_cogs_pct']); ?>" />
                    <p class="text-xs text-subtle mt-1">Used as fallback calculation when an explicit Cost Price (`_kinboni_cost_price`) is not entered for a product.</p>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Report Recipient Email(s) *</label>
                    <input type="text" name="recipients" class="form-control" value="<?php echo esc_attr($settings['recipients']); ?>" required />
                </div>

                <button type="submit" id="btn-save-report-settings" class="btn-action-gold w-full" style="padding:10px;">
                    <span class="dashicons dashicons-saved"></span> Save Reporting Preferences
                </button>
            </form>
        </div>

    </div>

</div>

