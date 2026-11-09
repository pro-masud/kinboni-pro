<?php
/**
 * Kinboni Pro Admin View: Delivery & Logistics Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $settings
 * @var array $divisions
 */

if (!defined('ABSPATH')) {
    exit;
}

$c = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';
?>

<div class="kinboni-admin-wrap" id="kinboni-delivery-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Delivery & Logistics</span></h1>
                <p class="kinboni-subtitle">Inside/Outside Dhaka Pricing &bull; Free Shipping Threshold &bull; Bangladesh Geo-Hierarchy</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh Delivery Engine">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <form id="form-delivery-settings">
        <!-- Delivery Pricing Cards Strip -->
        <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">

            <!-- Inside Dhaka Card -->
            <div class="kinboni-panel" style="margin: 0; padding: 20px; border-top: 3px solid #059669;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span class="font-bold text-dark text-sm">Inside Dhaka Delivery</span>
                    <span class="badge-status status-completed">Capital Metro</span>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Delivery Fee (৳) *</label>
                    <input type="number" step="1" name="fee_inside" class="form-control" value="<?php echo esc_attr($settings['fee_inside']); ?>" required style="font-size:18px; font-weight:bold; color:#059669;" />
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Estimated Lead Time</label>
                    <input type="text" name="time_inside" class="form-control" value="<?php echo esc_attr($settings['time_inside']); ?>" placeholder="1 - 2 Business Days" />
                </div>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="cod_inside_enabled" value="yes" <?php checked($settings['cod_inside_enabled'], 'yes'); ?> />
                    <span class="text-xs font-bold text-dark">Cash on Delivery (COD) Allowed</span>
                </label>
            </div>

            <!-- Outside Dhaka Card -->
            <div class="kinboni-panel" style="margin: 0; padding: 20px; border-top: 3px solid #D4AF37;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span class="font-bold text-dark text-sm">Outside Dhaka Delivery</span>
                    <span class="badge-status" style="background:#FCF9EE; color:#B89628; border:1px solid #D4AF37;">Nationwide</span>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Delivery Fee (৳) *</label>
                    <input type="number" step="1" name="fee_outside" class="form-control" value="<?php echo esc_attr($settings['fee_outside']); ?>" required style="font-size:18px; font-weight:bold; color:#B89628;" />
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Estimated Lead Time</label>
                    <input type="text" name="time_outside" class="form-control" value="<?php echo esc_attr($settings['time_outside']); ?>" placeholder="2 - 4 Business Days" />
                </div>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="cod_outside_enabled" value="yes" <?php checked($settings['cod_outside_enabled'], 'yes'); ?> />
                    <span class="text-xs font-bold text-dark">Cash on Delivery (COD) Allowed</span>
                </label>
            </div>

            <!-- Free Delivery Threshold Card -->
            <div class="kinboni-panel" style="margin: 0; padding: 20px; border-top: 3px solid #121214;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span class="font-bold text-dark text-sm">Free Delivery Threshold</span>
                    <span class="badge-status status-completed">Incentive</span>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Threshold Amount (৳) *</label>
                    <input type="number" step="1" name="free_threshold" class="form-control" value="<?php echo esc_attr($settings['free_threshold']); ?>" required style="font-size:18px; font-weight:bold; color:#121214;" />
                </div>
                <p class="text-xs text-subtle" style="margin-bottom:14px;">Cart values reaching or exceeding this amount unlock complimentary white-glove shipping.</p>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="allow_inspection" value="yes" <?php checked($settings['allow_inspection'], 'yes'); ?> />
                    <span class="text-xs font-bold text-dark">Open-box Inspection Allowed (Pay Upon Check)</span>
                </label>
            </div>

        </section>

        <!-- Logistics Partners & Address Cascading Grid -->
        <div class="grid-2col" style="gap: 24px; margin-bottom: 24px; align-items: start;">

            <!-- Logistics & Courier Partners -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Courier Partner Configuration</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-4">
                        <label class="form-label">Primary Courier Logistics Partner</label>
                        <select name="courier_partner" class="form-control">
                            <option value="Steadfast Courier" <?php selected($settings['courier_partner'], 'Steadfast Courier'); ?>>Steadfast Courier (Fastest Nationwide Coverage)</option>
                            <option value="Pathao Courier" <?php selected($settings['courier_partner'], 'Pathao Courier'); ?>>Pathao Courier (On-Demand Same Day)</option>
                            <option value="RedX Logistics" <?php selected($settings['courier_partner'], 'RedX Logistics'); ?>>RedX Logistics (Doorstep COD)</option>
                            <option value="Paperfly" <?php selected($settings['courier_partner'], 'Paperfly'); ?>>Paperfly (Union Level Coverage)</option>
                            <option value="Sundarban Courier" <?php selected($settings['courier_partner'], 'Sundarban Courier'); ?>>Sundarban Courier Service</option>
                        </select>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Courier API Key (Optional)</label>
                            <input type="password" name="courier_api_key" class="form-control" value="<?php echo esc_attr($settings['courier_api_key']); ?>" placeholder="Enter API Key" />
                        </div>
                        <div>
                            <label class="form-label">Secret Key / Salt</label>
                            <input type="password" name="courier_secret_key" class="form-control" value="<?php echo esc_attr($settings['courier_secret_key']); ?>" placeholder="Enter Secret Key" />
                        </div>
                    </div>

                    <div style="background:#FAF9F6; border:1px solid #E8E6DF; border-radius:6px; padding:12px; font-size:12px; color:#6B7280;">
                        <span class="dashicons dashicons-yes" style="color:#059669;"></span>
                        Selected courier partner is automatically printed on delivery slips and packing invoices with COD collection amounts.
                    </div>
                </div>
            </div>

            <!-- Address Management & Geo-Selector Hierarchy -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Bangladesh Address Management & Dropdowns</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="enable_geo_selector" value="yes" <?php checked($settings['enable_geo_selector'], 'yes'); ?> />
                            <span class="font-bold text-dark">Enable Dependent Bangladesh Address Dropdowns on Checkout</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Replaces freeform city input with cascading Division &rarr; District &rarr; Thana/Upazila selector.</p>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Test Address Hierarchy: Division</label>
                        <select id="select-geo-division" class="form-control">
                            <option value="">Select Division...</option>
                            <?php foreach ($divisions as $div_key => $div_name) : ?>
                                <option value="<?php echo esc_attr($div_key); ?>"><?php echo esc_html($div_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid-2col mb-3">
                        <div>
                            <label class="form-label">District</label>
                            <select id="select-geo-district" class="form-control" disabled>
                                <option value="">Select Division First</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Thana / Upazila</label>
                            <select id="select-geo-thana" class="form-control" disabled>
                                <option value="">Select District First</option>
                            </select>
                        </div>
                    </div>

                    <div id="geo-preview-box" style="display:none; padding:10px 14px; background:#ECFDF5; border:1px solid #A7F3D0; border-radius:6px; font-size:12px; color:#065F46;">
                        <strong>Formatted Delivery Address Preview:</strong>
                        <div id="geo-preview-text" class="mt-1 font-bold"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Live Interactive Delivery Simulator / Calculator -->
        <div class="kinboni-panel" style="margin-bottom: 24px;">
            <div class="panel-header">
                <h3 class="panel-title">Live Delivery Rate Calculator & Simulator</h3>
            </div>
            <div class="panel-body">
                <div class="grid-2col" style="gap:24px; align-items:center;">
                    <div>
                        <p class="text-xs text-subtle mb-3">Simulate and verify how order totals, delivery fees, and free shipping conditions behave for clients.</p>
                        <div class="grid-2col mb-3">
                            <div>
                                <label class="form-label">Order Subtotal (৳)</label>
                                <input type="number" id="sim-subtotal" class="form-control" value="1800" step="100" />
                            </div>
                            <div>
                                <label class="form-label">Destination</label>
                                <select id="sim-zone" class="form-control">
                                    <option value="inside">Inside Dhaka City</option>
                                    <option value="outside">Outside Dhaka City</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" id="btn-calc-rate" class="btn-action-gold" style="padding:8px 18px;">
                            Calculate Delivery
                        </button>
                    </div>

                    <div id="sim-result-card" style="padding: 16px 20px; background: #FAF9F6; border: 1px solid #E8E6DF; border-radius: 8px;">
                        <span class="info-label">Simulation Result</span>
                        <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:13px;">
                            <span>Shipping Charge:</span>
                            <strong id="sim-charge-val" style="color:#059669;">৳70.00</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:13px;">
                            <span>Final Total Payable:</span>
                            <strong id="sim-total-val" style="font-size:16px; color:#121214;">৳1,870.00</strong>
                        </div>
                        <div id="sim-gap-text" style="margin-top:10px; font-size:12px; color:#B89628; background:#FCF9EE; padding:6px 10px; border-radius:4px; border:1px dashed #D4AF37;">
                            Add ৳700.00 more for Free Delivery
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button Footer -->
        <div style="display:flex; justify-content:flex-end;">
            <button type="submit" id="btn-save-delivery" class="btn-action-gold" style="padding: 12px 30px; font-size: 14px;">
                <span class="dashicons dashicons-saved"></span> Save Delivery Configuration
            </button>
        </div>
    </form>

</div>

