<?php
/**
 * Kinboni Pro Luxury Printable Order Invoice
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var WC_Order $order
 */

if (!defined('ABSPATH')) {
    exit;
}

$order_id       = $order->get_id();
$order_number   = $order->get_order_number();
$date_created   = $order->get_date_created();
$date_formatted = $date_created ? date_i18n('F j, Y', $date_created->getTimestamp()) : '';
$customer_name  = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Guest Connoisseur';
$customer_phone = $order->get_billing_phone();
$customer_email = $order->get_billing_email();
$address        = $order->get_formatted_billing_address() ?: $order->get_billing_address_1();
$payment_method = $order->get_payment_method_title() ?: 'Cash on Delivery';
$c              = get_woocommerce_currency_symbol();
$courier_name   = get_option('kinboni_primary_courier', 'Steadfast Courier');
$brand_name     = class_exists('Kinboni_Content') ? Kinboni_Content::get('brand_name', 'KINBONI') : 'KINBONI';
$atelier_addr   = class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_address', 'Banani, Road 11, Dhaka, Bangladesh') : 'Banani, Road 11, Dhaka, Bangladesh';
$atelier_email  = class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_email', 'concierge@kinboni.com') : 'concierge@kinboni.com';
$helpline       = get_option('kinboni_whatsapp_phone', '8801700000000');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #KB-<?php echo esc_attr($order_number); ?> — KINBONI Atelier</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1A1817;
            background: #F9F8F6;
            padding: 40px 20px;
        }
        .invoice-card {
            max-width: 800px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 48px;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border: 1px solid #E8E6DF;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 32px;
            border-bottom: 2px solid #D4AF37;
            margin-bottom: 32px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-badge {
            width: 44px;
            height: 44px;
            background: #D4AF37;
            color: #121214;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Georgia, serif;
            font-size: 24px;
            font-weight: 700;
        }
        .brand-text h1 {
            font-family: Georgia, serif;
            font-size: 24px;
            letter-spacing: 0.15em;
            color: #121214;
        }
        .brand-text p {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: #B89628;
            font-weight: 700;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta h2 {
            font-size: 20px;
            color: #121214;
            font-family: Georgia, serif;
            margin-bottom: 6px;
        }
        .meta-line {
            font-size: 12px;
            color: #78716C;
            margin-bottom: 2px;
        }
        .grid-addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 36px;
        }
        .addr-box h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #B89628;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .addr-box p {
            font-size: 13px;
            line-height: 1.6;
            color: #27272A;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }
        .invoice-table th {
            background: #F9F8F6;
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #78716C;
            border-bottom: 1px solid #E8E6DF;
        }
        .invoice-table td {
            padding: 16px;
            border-bottom: 1px solid #E8E6DF;
            font-size: 13px;
            vertical-align: middle;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .totals-table {
            width: 320px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: #78716C;
        }
        .totals-row.grand-total {
            border-top: 2px solid #121214;
            padding-top: 14px;
            font-size: 18px;
            font-weight: 700;
            color: #121214;
        }
        .invoice-footer {
            border-top: 1px solid #E8E6DF;
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #78716C;
        }
        .print-actions {
            max-width: 800px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .btn-print {
            background: #121214;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #D4AF37; color: #121214; }
        @media print {
            body { background: #FFFFFF; padding: 0; }
            .invoice-card { box-shadow: none; border: none; padding: 0; max-width: 100%; }
            .print-actions { display: none; }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <button type="button" class="btn-print" onclick="window.print();">🖨️ Print Invoice</button>
    </div>

    <div class="invoice-card">
        <!-- Header -->
        <div class="invoice-header">
            <div class="brand-logo">
                <div class="brand-badge">K</div>
                <div class="brand-text">
                    <h1>KINBONI</h1>
                    <p>Haute Horlogerie & Atelier</p>
                </div>
            </div>
            <div class="invoice-meta">
                <h2>INVOICE</h2>
                <div class="meta-line font-bold">#KB-<?php echo esc_html($order_number); ?></div>
                <div class="meta-line">Date: <?php echo esc_html($date_formatted); ?></div>
                <div class="meta-line">Payment: <strong><?php echo esc_html($payment_method); ?></strong></div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="grid-addresses">
            <div class="addr-box">
                <h3>Billed & Delivered To</h3>
                <p>
                    <strong><?php echo esc_html($customer_name); ?></strong><br>
                    <?php echo nl2br(esc_html($address)); ?><br>
                    Phone: <?php echo esc_html($customer_phone); ?><br>
                    <?php if (!empty($customer_email)) : ?>
                        Email: <?php echo esc_html($customer_email); ?><br>
                    <?php endif; ?>
                </p>
            </div>
            <div class="addr-box">
                <h3>Origin & Concierge Atelier</h3>
                <p>
                    <strong><?php echo esc_html($brand_name . ' Atelier Bangladesh'); ?></strong><br>
                    <?php echo esc_html($atelier_addr); ?><br>
                    Hotline: +<?php echo esc_html($helpline); ?> &bull; Courier: <?php echo esc_html($courier_name); ?><br>
                    <?php echo esc_html($atelier_email); ?>
                </p>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="text-center" width="80">Qty</th>
                    <th class="text-right" width="120">Unit Price</th>
                    <th class="text-right" width="130">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order->get_items() as $item) : 
                    $qty = $item->get_quantity();
                    $line_total = $item->get_total();
                    $unit_price = $qty > 0 ? ($line_total / $qty) : $line_total;
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($item->get_name()); ?></strong>
                            <?php if ($meta = wc_display_item_meta($item, array('echo' => false))) : ?>
                                <div style="font-size: 11px; color: #78716C; margin-top: 3px;"><?php echo wp_kses_post($meta); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center font-bold"><?php echo esc_html($qty); ?></td>
                        <td class="text-right"><?php echo esc_html($c . number_format($unit_price, 2)); ?></td>
                        <td class="text-right font-bold"><?php echo esc_html($c . number_format($line_total, 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Breakdown -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <span><?php echo esc_html($c . number_format($order->get_subtotal(), 2)); ?></span>
                </div>
                <?php if ($order->get_discount_total() > 0) : ?>
                    <div class="totals-row" style="color: #059669; font-weight: 600;">
                        <span>Privilege Voucher Discount:</span>
                        <span>-<?php echo esc_html($c . number_format($order->get_discount_total(), 2)); ?></span>
                    </div>
                <?php endif; ?>
                <div class="totals-row">
                    <span>Delivery Charge:</span>
                    <span><?php echo esc_html($c . number_format($order->get_shipping_total(), 2)); ?></span>
                </div>
                <div class="totals-row grand-total">
                    <span>Net Amount to Pay:</span>
                    <span><?php echo esc_html($c . number_format($order->get_total(), 2)); ?></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p>Thank you for acquiring bespoke creations from the KINBONI Atelier.</p>
            <p>Authorized Dispatch &bull; Confidential Invoice</p>
        </div>
    </div>

</body>
</html>

