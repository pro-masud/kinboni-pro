<?php
/**
 * Kinboni Pro Courier Delivery Packing Slip
 *
 * Optimized for parcel dispatch across Bangladesh (Inside/Outside Dhaka).
 * Features prominent COD collection amount, courier routing, and checklist.
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
$date_formatted = $date_created ? date_i18n('M j, Y', $date_created->getTimestamp()) : '';
$customer_name  = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Customer';
$customer_phone = $order->get_billing_phone();
$city           = $order->get_billing_city() ?: 'Dhaka';
$address        = $order->get_formatted_billing_address() ?: $order->get_billing_address_1();
$total_amount   = $order->get_total();
$is_cod         = (stripos($order->get_payment_method_title(), 'Cash') !== false || $order->get_payment_method() === 'cod');
$is_outside     = (stripos($city, 'outside') !== false || stripos($order->get_shipping_method(), 'outside') !== false);
$courier_name   = get_option('kinboni_primary_courier', 'Steadfast Courier');
$brand_name     = class_exists('Kinboni_Content') ? Kinboni_Content::get('brand_name', 'KINBONI') : 'KINBONI';
$atelier_addr   = class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_address', 'Banani, Road 11, Dhaka, Bangladesh') : 'Banani, Road 11, Dhaka, Bangladesh';
$helpline       = get_option('kinboni_whatsapp_phone', '8801700000000');
$allow_inspect  = get_option('kinboni_allow_inspection', 'yes') === 'yes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Courier Slip #KB-<?php echo esc_attr($order_number); ?> — KINBONI</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #F4F4F5;
            color: #18181B;
            padding: 30px 15px;
        }
        .slip-box {
            max-width: 650px;
            margin: 0 auto;
            background: #FFFFFF;
            border: 2px dashed #D4AF37;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .slip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #18181B;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .slip-brand h1 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: #18181B;
        }
        .slip-brand span {
            font-size: 11px;
            font-weight: 700;
            color: #D4AF37;
            text-transform: uppercase;
        }
        .zone-badge {
            background: #18181B;
            color: #FFFFFF;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .zone-badge.outside {
            background: #D97706;
        }
        .cod-highlight-box {
            background: #FEF3C7;
            border: 2px solid #F59E0B;
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .cod-highlight-box.prepaid {
            background: #ECFDF5;
            border-color: #10B981;
        }
        .cod-label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #92400E;
        }
        .cod-highlight-box.prepaid .cod-label { color: #065F46; }
        .cod-amount {
            font-size: 28px;
            font-weight: 900;
            color: #B45309;
        }
        .cod-highlight-box.prepaid .cod-amount { color: #047857; }
        .grid-parties {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .party-box {
            background: #F9F8F6;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #E4E4E7;
        }
        .party-box h3 {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #71717A;
            margin-bottom: 8px;
        }
        .customer-phone-bold {
            font-size: 18px;
            font-weight: 800;
            color: #18181B;
            margin: 6px 0;
            letter-spacing: 0.05em;
        }
        .items-checklist {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 12px;
        }
        .items-checklist th {
            background: #F4F4F5;
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #E4E4E7;
            font-weight: 700;
        }
        .items-checklist td {
            padding: 10px 12px;
            border-bottom: 1px solid #E4E4E7;
        }
        .slip-footer {
            border-top: 1px solid #E4E4E7;
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #71717A;
        }
        .btn-print {
            background: #18181B;
            color: #FFFFFF;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
        }
        @media print {
            body { background: #FFFFFF; padding: 0; }
            .slip-box { box-shadow: none; border: 2px solid #000; padding: 20px; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div style="max-width: 650px; margin: 0 auto 16px; text-align: right;" class="no-print">
        <button type="button" class="btn-print" onclick="window.print();">🖨️ Print Courier Slip</button>
    </div>

    <div class="slip-box">
        <div class="slip-header">
            <div class="slip-brand">
                <h1><?php echo esc_html($brand_name); ?></h1>
                <span><?php echo esc_html($courier_name); ?> Dispatch Slip &bull; #KB-<?php echo esc_html($order_number); ?></span>
            </div>
            <div>
                <span class="zone-badge <?php echo $is_outside ? 'outside' : ''; ?>">
                    <?php echo $is_outside ? 'Outside Dhaka' : 'Inside Dhaka'; ?>
                </span>
            </div>
        </div>

        <!-- COD Collection Card -->
        <div class="cod-highlight-box <?php echo !$is_cod ? 'prepaid' : ''; ?>">
            <div>
                <div class="cod-label"><?php echo $is_cod ? 'Cash to Collect (COD)' : 'Prepaid Order (No Collection)'; ?></div>
                <div style="font-size: 11px; color: #78716C; margin-top: 2px;"><?php echo $allow_inspect ? esc_html__('Deliver package upon inspection', 'kinboni-core') : esc_html__('Standard Courier Dispatch', 'kinboni-core'); ?></div>
            </div>
            <div class="cod-amount">
                <?php echo $is_cod ? esc_html('৳' . number_format($total_amount, 2)) : '৳0.00 (PAID)'; ?>
            </div>
        </div>

        <!-- Customer & Merchant Details -->
        <div class="grid-parties">
            <div class="party-box">
                <h3>Deliver To (Recipient)</h3>
                <div style="font-size: 15px; font-weight: 800;"><?php echo esc_html($customer_name); ?></div>
                <div class="customer-phone-bold">📞 <?php echo esc_html($customer_phone); ?></div>
                <div style="font-size: 13px; line-height: 1.5; color: #27272A; margin-top: 6px;">
                    <?php echo nl2br(esc_html($address)); ?>
                </div>
            </div>

            <div class="party-box">
                <h3>Shipped By (Merchant)</h3>
                <div style="font-weight: 800; font-size: 13px;"><?php echo esc_html($brand_name . ' Atelier'); ?></div>
                <div style="font-size: 12px; margin-top: 4px;"><?php echo esc_html($atelier_addr); ?></div>
                <div style="font-size: 12px; font-weight: 700; margin-top: 4px;">Helpline: +<?php echo esc_html($helpline); ?></div>
                <div style="font-size: 11px; color: #71717A; margin-top: 6px;">Courier: <strong><?php echo esc_html($courier_name); ?></strong></div>
                <div style="font-size: 11px; color: #71717A; margin-top: 4px;">Order Date: <?php echo esc_html($date_formatted); ?></div>
            </div>
        </div>

        <!-- Line Items Package Checklist -->
        <table class="items-checklist">
            <thead>
                <tr>
                    <th width="40">Check</th>
                    <th>Package Item Description</th>
                    <th width="80" style="text-align: right;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order->get_items() as $item) : ?>
                    <tr>
                        <td style="text-align: center;"><input type="checkbox" /></td>
                        <td>
                            <strong><?php echo esc_html($item->get_name()); ?></strong>
                        </td>
                        <td style="text-align: right; font-weight: 800;"><?php echo esc_html($item->get_quantity()); ?> pcs</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="slip-footer">
            <div>
                <strong>⚠️ Fragile Luxury Shipment:</strong> Handle with exceptional care.
            </div>
            <div>
                Courier Dispatch Slip &bull; Kinboni.com
            </div>
        </div>
    </div>

</body>
</html>

