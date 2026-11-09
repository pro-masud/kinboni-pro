<?php
/**
 * Kinboni Pro Admin View: Storefront Experience & Brand Controls
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $settings
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="kinboni-admin-wrap" id="kinboni-storefront-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Storefront Experience</span></h1>
                <p class="kinboni-subtitle">Live Social Proof &bull; WhatsApp Concierge &bull; Theme Accent &bull; Shopping Nuances</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh Settings">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <form id="form-storefront-settings">
        <div class="grid-2col" style="gap: 24px; align-items: start;">

            <!-- SECTION 1: LIVE SALES SOCIAL PROOF -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Live Sales Social Proof</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="live_sales_enabled" value="yes" <?php checked($settings['live_sales_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Enable 15s Live Sales Notification Popups</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Displays recent orders in real-time at the bottom-left of the storefront to foster FOMO.</p>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Display Interval (Seconds) *</label>
                            <input type="number" min="5" max="120" name="live_sales_interval" class="form-control" value="<?php echo esc_attr($settings['live_sales_interval']); ?>" required />
                        </div>
                        <div>
                            <label class="form-label">Sound Notification</label>
                            <select name="live_sales_sound" class="form-control">
                                <option value="yes" <?php selected($settings['live_sales_sound'], 'yes'); ?>>Enabled (Delicate Chime)</option>
                                <option value="no" <?php selected($settings['live_sales_sound'], 'no'); ?>>Muted (Silent Popups)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="live_sales_real_names" value="yes" <?php checked($settings['live_sales_real_names'] ?? 'no', 'yes'); ?> />
                            <span class="font-bold text-dark text-sm">Show Customer First Name (e.g., "Tahmid from Dhaka")</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">When unchecked, displays anonymized privacy-safe text ("Someone from Dhaka").</p>
                    </div>

                    <div style="background:#FAF9F6; border:1px solid #E8E6DF; border-radius:6px; padding:12px; font-size:12px; color:#6B7280;">
                        <span class="dashicons dashicons-yes" style="color:#059669;"></span>
                        <strong>Dynamic Order Sync:</strong> Real completed and processing orders are automatically fetched first, with fallback to curated atelier items.
                    </div>
                </div>
            </div>

            <!-- SECTION 2: FLOATING WHATSAPP BUTTON -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Floating WhatsApp Concierge</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="whatsapp_enabled" value="yes" <?php checked($settings['whatsapp_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Enable Floating WhatsApp Concierge Button</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Opens direct WhatsApp chat with zero automation or third-party gateways.</p>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">WhatsApp Number *</label>
                            <input type="text" name="whatsapp_phone" class="form-control" value="<?php echo esc_attr($settings['whatsapp_phone']); ?>" placeholder="8801700000000" required />
                            <p class="text-xs text-subtle mt-1">Include country code without '+' (e.g., 8801711000000).</p>
                        </div>
                        <div>
                            <label class="form-label">Button Position</label>
                            <select name="whatsapp_position" class="form-control">
                                <option value="bottom-right" <?php selected($settings['whatsapp_position'], 'bottom-right'); ?>>Bottom Right Corner</option>
                                <option value="bottom-left" <?php selected($settings['whatsapp_position'], 'bottom-left'); ?>>Bottom Left Corner</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Button Color</label>
                            <input type="color" name="whatsapp_color" class="form-control" value="<?php echo esc_attr($settings['whatsapp_color']); ?>" style="height:38px; padding:2px;" />
                        </div>
                        <div>
                            <label class="form-label">Button Label Text</label>
                            <input type="text" name="whatsapp_label" class="form-control" value="<?php echo esc_attr($settings['whatsapp_label']); ?>" placeholder="Instant Concierge" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: THEME CUSTOMIZATION -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Theme Aesthetics & Typography</h3>
                </div>
                <div class="panel-body">
                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Luxury Accent Color</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="color" name="theme_accent_color" class="form-control" value="<?php echo esc_attr($settings['theme_accent_color']); ?>" style="width:48px; height:38px; padding:2px;" />
                                <input type="text" value="<?php echo esc_attr($settings['theme_accent_color']); ?>" class="form-control" readonly style="font-weight:bold;" />
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Default Color Mode</label>
                            <select name="theme_mode_default" class="form-control">
                                <option value="system" <?php selected($settings['theme_mode_default'], 'system'); ?>>System Preference (Auto)</option>
                                <option value="dark" <?php selected($settings['theme_mode_default'], 'dark'); ?>>Luxury Dark Mode</option>
                                <option value="light" <?php selected($settings['theme_mode_default'], 'light'); ?>>Clean Light Mode</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label class="form-label">Display Font Family</label>
                        <input type="text" name="theme_heading_font" class="form-control" value="<?php echo esc_attr($settings['theme_heading_font']); ?>" placeholder="Cinzel, Georgia, serif" />
                        <p class="text-xs text-subtle mt-1">Applies luxury serif typography across titles while maintaining existing responsive styling.</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: SHOPPING EXPERIENCE & MICRO-CONVERSIONS -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Shopping Experience & Micro-Conversions</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-3">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="cart_recommendations" value="yes" <?php checked($settings['cart_recommendations'], 'yes'); ?> />
                            <span class="font-bold text-dark text-sm">Cart Drawer Curated Upsell Recommendations</span>
                        </label>
                    </div>

                    <div class="form-group mb-3">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="recently_viewed_enabled" value="yes" <?php checked($settings['recently_viewed_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark text-sm">Enable "Recently Viewed Products" Grid</span>
                        </label>
                    </div>

                    <div class="form-group mb-3">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="low_stock_text_enabled" value="yes" <?php checked($settings['low_stock_text_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark text-sm">Display "Only X left in the atelier!" Urgency Badge</span>
                        </label>
                    </div>

                    <div class="form-group mb-3">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="quickview_sizechart" value="yes" <?php checked($settings['quickview_sizechart'], 'yes'); ?> />
                            <span class="font-bold text-dark text-sm">Enable Quick View Size & Dimensions Chart</span>
                        </label>
                    </div>

                    <div class="form-group mb-2">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="quickview_delivery_info" value="yes" <?php checked($settings['quickview_delivery_info'], 'yes'); ?> />
                            <span class="font-bold text-dark text-sm">Enable Quick View Express Delivery Guarantee Badge</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: PAYMENT GATEWAYS & MOBILE WALLETS -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title"><?php esc_html_e('Payment Gateways & Mobile Wallets', 'kinboni-core'); ?></h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-3">
                        <label class="form-label"><?php esc_html_e('bKash / Nagad Merchant Number', 'kinboni-core'); ?></label>
                        <input type="text" name="bkash_merchant_number" class="form-control" value="<?php echo esc_attr(isset($settings['bkash_merchant_number']) ? $settings['bkash_merchant_number'] : ''); ?>" placeholder="01711000000" />
                        <p class="text-xs text-subtle mt-1"><?php esc_html_e('Enter your bKash / Nagad Merchant or Personal number. If left empty, the bKash/Nagad payment option will be hidden on checkout.', 'kinboni-core'); ?></p>
                    </div>
                </div>
            </div>

            <!-- SECTION 6: CENTRAL ATELIER CONTENT & BRANDING -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title"><?php esc_html_e('Central Atelier Content & Branding', 'kinboni-core'); ?></h3>
                </div>
                <div class="panel-body">
                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label"><?php esc_html_e('Brand Name', 'kinboni-core'); ?></label>
                            <input type="text" name="brand_name" class="form-control" value="<?php echo esc_attr(isset($settings['brand_name']) ? $settings['brand_name'] : 'KINBONI'); ?>" placeholder="KINBONI" />
                        </div>
                        <div>
                            <label class="form-label"><?php esc_html_e('Brand Tagline', 'kinboni-core'); ?></label>
                            <input type="text" name="brand_tagline" class="form-control" value="<?php echo esc_attr(isset($settings['brand_tagline']) ? $settings['brand_tagline'] : ''); ?>" placeholder="Artisanal Luxury & Master Craftsmanship" />
                        </div>
                    </div>
                    <div class="grid-2col mb-3">
                        <div>
                            <label class="form-label"><?php esc_html_e('Concierge Support Email', 'kinboni-core'); ?></label>
                            <input type="email" name="concierge_email" class="form-control" value="<?php echo esc_attr(isset($settings['concierge_email']) ? $settings['concierge_email'] : ''); ?>" placeholder="concierge@kinboni.com" />
                        </div>
                        <div>
                            <label class="form-label"><?php esc_html_e('Atelier Physical Address', 'kinboni-core'); ?></label>
                            <input type="text" name="concierge_address" class="form-control" value="<?php echo esc_attr(isset($settings['concierge_address']) ? $settings['concierge_address'] : ''); ?>" placeholder="Banani, Road 11, Dhaka, Bangladesh" />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Save Button Footer -->
        <div style="margin-top:24px; display:flex; justify-content:flex-end;">
            <button type="submit" id="btn-save-storefront" class="btn-action-gold" style="padding:12px 30px; font-size:14px;">
                <span class="dashicons dashicons-saved"></span> Save Storefront Configuration
            </button>
        </div>
    </form>

</div>

