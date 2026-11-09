<?php
/**
 * Kinboni Pro Admin View: Alerts & Notifications Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $settings
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="kinboni-admin-wrap" id="kinboni-notifications-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Alerts & Notifications</span></h1>
                <p class="kinboni-subtitle">Order Notifications &bull; Real-time Audio Chime &bull; Out-of-Stock Alerts</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <!-- Test Audio Sound Button -->
            <button type="button" id="btn-test-chime" class="btn-action-gold" title="Preview Audio Bell Chime">
                <span class="dashicons dashicons-controls-volumeon"></span> Test Audio Chime
            </button>

            <!-- Test Email Button -->
            <button type="button" id="btn-test-email" class="btn-action-outline" title="Send a verification test email to configured recipients">
                <span class="dashicons dashicons-email-alt"></span> Send Test Email
            </button>

            <!-- Refresh Button -->
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh Notifications">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Status Cards Strip -->
    <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
        <div class="kinboni-metric-card" style="padding:16px 20px;">
            <div class="metric-head" style="margin-bottom:6px;">
                <span class="metric-title">Order Alerts</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-cart"></span></span>
            </div>
            <h3 class="metric-number" style="font-size:20px; margin:0; color:<?php echo ($settings['order_email_enabled'] === 'yes') ? '#059669' : '#DC2626'; ?>;">
                <?php echo ($settings['order_email_enabled'] === 'yes') ? 'Active' : 'Muted'; ?>
            </h3>
        </div>

        <div class="kinboni-metric-card" style="padding:16px 20px;">
            <div class="metric-head" style="margin-bottom:6px;">
                <span class="metric-title">Stock Monitor</span>
                <span class="metric-icon-gold" style="color:#D4AF37;background:#FCF9EE;"><span class="dashicons dashicons-warning"></span></span>
            </div>
            <h3 class="metric-number" style="font-size:20px; margin:0; color:#B89628;">
                <?php echo ($settings['stock_email_enabled'] === 'yes') ? 'Monitoring' : 'Disabled'; ?>
            </h3>
        </div>

        <div class="kinboni-metric-card" style="padding:16px 20px;">
            <div class="metric-head" style="margin-bottom:6px;">
                <span class="metric-title">Audio Chime</span>
                <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-controls-volumeon"></span></span>
            </div>
            <h3 class="metric-number" style="font-size:20px; margin:0; color:#059669;">
                <?php echo ($settings['order_sound_enabled'] === 'yes') ? 'Web Audio Ready' : 'Muted'; ?>
            </h3>
        </div>

        <div class="kinboni-metric-card" style="padding:16px 20px;">
            <div class="metric-head" style="margin-bottom:6px;">
                <span class="metric-title">Top Recipient</span>
                <span class="metric-icon-gold" style="color:#121214;background:#E8E6DF;"><span class="dashicons dashicons-email"></span></span>
            </div>
            <h3 class="metric-number" style="font-size:14px; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <?php echo esc_html(explode(',', $settings['recipients'])[0] ?? 'Admin'); ?>
            </h3>
        </div>
    </section>

    <!-- Main Configuration Form -->
    <form id="form-notification-settings">
        <div class="grid-2col" style="gap: 24px; align-items: start;">

            <!-- Order Notifications & Audio Sound -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Order Alerts & Sound Engine</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="order_email_enabled" value="yes" <?php checked($settings['order_email_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Send Admin Email Notification When a New Order is Placed</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Dispatches a luxury branded order invoice with item breakdown, customer phone, and address.</p>
                    </div>

                    <div class="form-group mb-4" style="border-top:1px solid var(--kb-border-light); padding-top:16px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="order_sound_enabled" value="yes" <?php checked($settings['order_sound_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Play Audio Sound When a New Order Arrives</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Plays a non-intrusive sound while the WordPress Admin Dashboard is active in any browser tab.</p>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Audio Chime Acoustic Profile</label>
                        <select name="sound_tone" class="form-control">
                            <option value="gold_bell" <?php selected($settings['sound_tone'], 'gold_bell'); ?>>Luxury Gold Bell (Two-Tone Sine 880Hz &rarr; 1320Hz)</option>
                            <option value="crisp_chime" <?php selected($settings['sound_tone'], 'crisp_chime'); ?>>Crisp Atelier Chime (High Octave 1760Hz)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" style="border-top:1px solid var(--kb-border-light); padding-top:16px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="admin_badge_enabled" value="yes" <?php checked($settings['admin_badge_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Display Red / Gold Order Badge in WordPress Admin Bar</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Shows live count of pending orders on the top admin toolbar with instant link to orders table.</p>
                    </div>
                </div>
            </div>

            <!-- Stock & Inventory Alerts -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Stock & Inventory Alerts</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="stock_email_enabled" value="yes" <?php checked($settings['stock_email_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Send Email When a Product Goes Out of Stock (0 units)</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Prevents lost sales by alerting the team immediately upon stock depletion.</p>
                    </div>

                    <div class="form-group mb-4" style="border-top:1px solid var(--kb-border-light); padding-top:16px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="low_stock_enabled" value="yes" <?php checked($settings['low_stock_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Enable Low-Stock Proactive Warning Emails</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Alerts you before inventory runs out completely.</p>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Low-Stock Alert Threshold (Units) *</label>
                        <input type="number" min="1" max="100" name="stock_threshold" class="form-control" value="<?php echo esc_attr($settings['stock_threshold']); ?>" required />
                        <p class="text-xs text-subtle mt-1">Triggers a warning when product stock reaches or falls below this number.</p>
                    </div>

                    <div style="background:#FAF9F6; border:1px solid #E8E6DF; border-radius:6px; padding:12px; font-size:12px; color:#6B7280;">
                        <span class="dashicons dashicons-shield" style="color:#D4AF37;"></span>
                        <strong>Deduplication Guard Active:</strong> Administrators will never receive duplicate alerts for the same stock or order event.
                    </div>
                </div>
            </div>

            <!-- Notification Recipients Configuration -->
            <div class="kinboni-panel" style="grid-column: span 2;">
                <div class="panel-header">
                    <h3 class="panel-title">Notification Recipients & Routing</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-2">
                        <label class="form-label">Recipient Email Addresses (Comma Separated) *</label>
                        <input type="text" name="recipients" class="form-control" value="<?php echo esc_attr($settings['recipients']); ?>" placeholder="admin@domain.com, orders@domain.com" required style="font-size:14px; font-weight:bold;" />
                        <p class="text-xs text-subtle mt-1">Multiple email addresses can be separated by a comma (e.g., masud@kinboni.com, support@kinboni.com).</p>
                    </div>
                </div>
            </div>

            <!-- Bangladesh SMS Gateway Integration -->
            <div class="kinboni-panel" style="grid-column: span 2;">
                <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="panel-title">Bangladesh SMS Gateway & OTP Confirmation</h3>
                    <span class="badge-status <?php echo ($settings['sms_enabled'] === 'yes') ? 'status-completed' : 'status-processing'; ?>">
                        <?php echo ($settings['sms_enabled'] === 'yes') ? 'SMS Engine Active' : 'SMS Inactive'; ?>
                    </span>
                </div>
                <div class="panel-body">
                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="sms_enabled" value="yes" <?php checked($settings['sms_enabled'], 'yes'); ?> />
                            <span class="font-bold text-dark">Send Instant SMS Confirmation to Customer on New Order (Prevents COD Returns)</span>
                        </label>
                        <p class="text-xs text-subtle mt-1">Dispatches an authentic SMS via your gateway to the customer's 11-digit mobile number upon order placement.</p>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">SMS Gateway Provider</label>
                            <select name="sms_provider" id="sms-provider-select" class="form-control">
                                <option value="greenweb" <?php selected($settings['sms_provider'], 'greenweb'); ?>>Greenweb BD (api.greenweb.com.bd)</option>
                                <option value="bulksmsbd" <?php selected($settings['sms_provider'], 'bulksmsbd'); ?>>BulkSMSBD (bulksmsbd.net)</option>
                                <option value="custom" <?php selected($settings['sms_provider'], 'custom'); ?>>Custom Webhook / Other Provider</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">API Token / Key</label>
                            <input type="password" name="sms_api_key" class="form-control" value="<?php echo esc_attr($settings['sms_api_key']); ?>" placeholder="Enter SMS Gateway API Key" />
                        </div>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Sender ID / Masking (Optional)</label>
                            <input type="text" name="sms_sender_id" class="form-control" value="<?php echo esc_attr($settings['sms_sender_id']); ?>" placeholder="e.g. KINBONI or 88096..." />
                        </div>
                        <div>
                            <label class="form-label">Custom Webhook URL (Only if Custom Provider selected)</label>
                            <input type="url" name="sms_custom_url" class="form-control" value="<?php echo esc_attr($settings['sms_custom_url']); ?>" placeholder="https://..." />
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Order Confirmation SMS Template</label>
                        <textarea name="sms_order_template" class="form-control" rows="3" style="font-family:monospace; font-size:12px;"><?php echo esc_textarea($settings['sms_order_template']); ?></textarea>
                        <p class="text-xs text-subtle mt-1">Available Tags: <code>{customer_name}</code>, <code>{order_id}</code>, <code>{order_total}</code>, <code>{site_name}</code>, <code>{site_url}</code></p>
                    </div>

                    <!-- Live Test SMS Dispatcher -->
                    <div style="background:#FAF9F6; border:1px solid #E8E6DF; border-radius:6px; padding:14px;">
                        <h4 class="font-bold text-dark text-xs mb-2">Test SMS Dispatcher</h4>
                        <div style="display:flex; gap:10px; max-width:500px;">
                            <input type="text" id="test-sms-phone" placeholder="01711000000" class="form-control" style="font-size:13px;" />
                            <button type="button" id="btn-send-test-sms" class="btn-action-gold" style="white-space:nowrap; padding:6px 16px;">
                                <span class="dashicons dashicons-smartphone"></span> Send Test SMS
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Save Button Footer -->
        <div style="margin-top:24px; display:flex; justify-content:flex-end;">
            <button type="submit" id="btn-save-notifications" class="btn-action-gold" style="padding:12px 30px; font-size:14px;">
                <span class="dashicons dashicons-saved"></span> Save Notification Preferences
            </button>
        </div>
    </form>

</div>

