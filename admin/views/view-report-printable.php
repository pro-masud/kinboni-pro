<?php
/**
 * Kinboni Pro Printable Executive Financial Report Template
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $report
 */

if (!defined('ABSPATH')) {
    exit;
}

$bounds       = $report['bounds'] ?? array();
$summary      = $report['summary'] ?? array();
$breakdown    = $report['breakdown'] ?? array();
$top_products = $report['top_products'] ?? array();
$c = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report — <?php echo esc_html($bounds['label']); ?> — KINBONI Pro</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #F8F7F4;
            color: #121214;
            margin: 0;
            padding: 30px;
        }
        .report-wrap {
            max-width: 860px;
            margin: 0 auto;
            background: #FFF;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #E5E7EB;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #121214;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #D4AF37;
            text-transform: uppercase;
        }
        .report-meta {
            text-align: right;
            font-size: 12px;
            color: #6B7280;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: #FAF9F6;
            border: 1px solid #E8E6DF;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }
        .kpi-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6B7280;
            margin-bottom: 6px;
        }
        .kpi-val {
            font-size: 18px;
            font-weight: bold;
            color: #121214;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin: 24px 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #121214;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 24px;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }
        th {
            background: #FAF9F6;
            font-weight: 700;
            color: #374151;
        }
        .text-right { text-align: right; }
        .no-print {
            text-align: right;
            margin-bottom: 20px;
        }
        .btn-print {
            background: #121214;
            color: #D4AF37;
            border: 1px solid #D4AF37;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
        @media print {
            body { background: #FFF; padding: 0; }
            .report-wrap { box-shadow: none; border: none; padding: 0; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="report-wrap">
        <div class="no-print">
            <button type="button" class="btn-print" onclick="window.print();">🖨️ Print / Save as PDF</button>
        </div>

        <div class="report-header">
            <div>
                <div class="brand-title">KINBONI PRO</div>
                <div style="font-size:12px; color:#6B7280; margin-top:2px;">Executive Sales & Financial Profitability Audit</div>
            </div>
            <div class="report-meta">
                <div><strong>Audit Period:</strong> <?php echo esc_html($bounds['label']); ?></div>
                <div>Generated on: <?php echo esc_html(current_time('M j, Y H:i')); ?></div>
            </div>
        </div>

        <!-- KPI Summary Strip -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Gross Revenue</div>
                <div class="kpi-val"><?php echo esc_html($c . number_format($summary['total_revenue'], 2)); ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Cost of Goods (COGS)</div>
                <div class="kpi-val" style="color:#DC2626;"><?php echo esc_html($c . number_format($summary['total_cogs'], 2)); ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Gross Profit</div>
                <div class="kpi-val" style="color:#059669;"><?php echo esc_html($c . number_format($summary['gross_profit'], 2)); ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Gross Margin %</div>
                <div class="kpi-val" style="color:#B89628;"><?php echo esc_html($summary['gross_margin']); ?>%</div>
            </div>
        </div>

        <!-- Period Timeline Breakdown -->
        <div class="section-title">Period Sales Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Date / Period</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Gross Sales</th>
                    <th class="text-right">Shipping</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">COGS</th>
                    <th class="text-right">Gross Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($breakdown)) : ?>
                    <?php foreach ($breakdown as $b) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($b['date_label']); ?></strong></td>
                            <td class="text-right"><?php echo esc_html($b['orders']); ?></td>
                            <td class="text-right"><?php echo esc_html($c . number_format($b['gross_sales'], 2)); ?></td>
                            <td class="text-right"><?php echo esc_html($c . number_format($b['shipping'], 2)); ?></td>
                            <td class="text-right">-<?php echo esc_html($c . number_format($b['discount'], 2)); ?></td>
                            <td class="text-right"><?php echo esc_html($c . number_format($b['cogs'], 2)); ?></td>
                            <td class="text-right" style="color:#059669; font-weight:bold;"><?php echo esc_html($c . number_format($b['profit'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px; color:#9CA3AF;">No order transactions recorded in this period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Product Profitability Table -->
        <div class="section-title">Product Profitability Audit</div>
        <table>
            <thead>
                <tr>
                    <th>Product Title</th>
                    <th>SKU</th>
                    <th class="text-right">Units Sold</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Total Cost</th>
                    <th class="text-right">Gross Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($top_products)) : ?>
                    <?php foreach ($top_products as $p) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($p['name']); ?></strong></td>
                            <td><?php echo esc_html($p['sku']); ?></td>
                            <td class="text-right"><?php echo esc_html($p['qty_sold']); ?>x</td>
                            <td class="text-right"><?php echo esc_html($c . number_format($p['revenue'], 2)); ?></td>
                            <td class="text-right"><?php echo esc_html($c . number_format($p['cogs'], 2)); ?></td>
                            <td class="text-right" style="color:#059669; font-weight:bold;"><?php echo esc_html($c . number_format($p['profit'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:20px; color:#9CA3AF;">No product sales recorded in this period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top:40px; padding-top:16px; border-top:1px solid #E5E7EB; display:flex; justify-content:space-between; font-size:11px; color:#9CA3AF;">
            <span>KINBONI Pro Automated Financial Intelligence Engine</span>
            <span>Confidential &bull; Internal Management Audit Only</span>
        </div>
    </div>

</body>
</html>

