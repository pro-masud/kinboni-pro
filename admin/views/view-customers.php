<?php
/**
 * Kinboni Pro Admin View: Patrons & Customers Management
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $customers_data
 * @var array $metrics
 */

if (!defined('ABSPATH')) {
    exit;
}

$customers    = $customers_data['customers'] ?? array();
$total        = $customers_data['total'] ?? 0;
$max_pages    = $customers_data['max_pages'] ?? 1;
$current_page = $customers_data['current_page'] ?? 1;

$current_tag  = isset($_GET['tag']) ? sanitize_key(wp_unslash($_GET['tag'])) : '';
$current_type = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : '';
$search_term  = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

$c = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';

$export_url = wp_nonce_url(add_query_arg(array(
    'page'   => 'kinboni-customers',
    'action' => 'export_csv',
), admin_url('admin.php')), 'kinboni_customers_export', '_kb_nonce');
?>

<div class="kinboni-admin-wrap" id="kinboni-customers-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Patrons & Customers</span></h1>
                <p class="kinboni-subtitle">Clientele Intelligence &bull; <?php echo esc_html($total); ?> Registered & Guest Connoisseurs</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <!-- Export CSV -->
            <a href="<?php echo esc_url($export_url); ?>" class="btn-action-gold" title="Export Customer List to CSV">
                <span class="dashicons dashicons-download"></span> Export Customers CSV
            </a>
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh Clientele">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Metric Strip -->
    <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">Total Patrons</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-groups"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0;"><?php echo esc_html($metrics['total_customers']); ?></h3>
        </div>

        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">VIP Connoisseurs</span>
                <span class="metric-icon-gold" style="color:#D4AF37;background:#FCF9EE;"><span class="dashicons dashicons-awards"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#B89628;"><?php echo esc_html($metrics['vip_count']); ?></h3>
        </div>

        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">Returning Patrons</span>
                <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-update"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#059669;"><?php echo esc_html($metrics['returning_count']); ?></h3>
        </div>

        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">Lifetime Revenue</span>
                <span class="metric-icon-gold" style="color:#121214;background:#E8E6DF;"><span class="dashicons dashicons-money-alt"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0;"><?php echo esc_html($c . number_format($metrics['total_revenue'], 2)); ?></h3>
        </div>
    </section>

    <!-- Sub-Navigation Filter Tabs -->
    <div class="kinboni-tabs-bar">
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-customers')); ?>" class="tab-pill <?php echo (empty($current_tag) && empty($current_type)) ? 'is-active' : ''; ?>">
            All Patrons (<?php echo esc_html($metrics['total_customers']); ?>)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-customers&type=vip')); ?>" class="tab-pill <?php echo $current_type === 'vip' ? 'is-active' : ''; ?>">
            ⭐ VIP Connoisseurs (<?php echo esc_html($metrics['vip_count']); ?>)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-customers&type=returning')); ?>" class="tab-pill <?php echo $current_type === 'returning' ? 'is-active' : ''; ?>">
            Returning Patrons (<?php echo esc_html($metrics['returning_count']); ?>)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-customers&type=new')); ?>" class="tab-pill <?php echo $current_type === 'new' ? 'is-active' : ''; ?>">
            New Patrons (<?php echo esc_html($metrics['new_count']); ?>)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-customers&tag=problematic')); ?>" class="tab-pill tab-flagged <?php echo $current_tag === 'problematic' ? 'is-active' : ''; ?>">
            ⚠️ Problematic
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="kinboni-toolbar-panel">
        <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="kinboni-filter-form">
            <input type="hidden" name="page" value="kinboni-customers" />
            <?php if (!empty($current_type)) : ?>
                <input type="hidden" name="type" value="<?php echo esc_attr($current_type); ?>" />
            <?php endif; ?>
            <?php if (!empty($current_tag)) : ?>
                <input type="hidden" name="tag" value="<?php echo esc_attr($current_tag); ?>" />
            <?php endif; ?>

            <div class="filter-group flex-search">
                <span class="dashicons dashicons-search icon-search"></span>
                <input 
                    type="text" 
                    name="s" 
                    value="<?php echo esc_attr($search_term); ?>" 
                    placeholder="Search patron by name, phone number, email or city..." 
                    class="input-search"
                />
            </div>

            <button type="submit" class="btn-filter-apply">Search</button>
            <?php if (!empty($search_term) || !empty($current_tag) || !empty($current_type)) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-customers')); ?>" class="btn-filter-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="kinboni-panel" style="margin-top: 16px;">
        <div class="panel-body p-0">
            <?php if (!empty($customers)) : ?>
                <table class="kinboni-table kinboni-customers-table">
                    <thead>
                        <tr>
                            <th>Patron Name</th>
                            <th>Contact & City</th>
                            <th width="100">Orders</th>
                            <th width="140">Total Spend</th>
                            <th width="120">AOV</th>
                            <th width="120">Last Order</th>
                            <th width="140">Customer Tag</th>
                            <th width="150" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $cust) : ?>
                            <tr id="customer-row-<?php echo esc_attr($cust['key']); ?>">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="kinboni-logo-badge" style="width: 34px; height: 34px; font-size: 16px;">
                                            <?php echo esc_html(strtoupper(substr($cust['name'], 0, 1))); ?>
                                        </div>
                                        <div>
                                            <a href="#" class="btn-view-customer-history font-bold text-dark order-link" data-customer-key="<?php echo esc_attr($cust['key']); ?>">
                                                <?php echo esc_html($cust['name']); ?>
                                            </a>
                                            <div class="text-subtle text-xs">
                                                <?php echo ($cust['insight'] === 'VIP') ? '⭐ VIP Connoisseur' : ($cust['insight'] === 'Returning' ? 'Returning Buyer' : 'New Client'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="phone-line">
                                        <span class="font-bold text-dark"><?php echo esc_html($cust['phone'] ?: '—'); ?></span>
                                        <?php if (!empty($cust['clean_phone'])) : ?>
                                            <a href="<?php echo esc_url($cust['whatsapp_url']); ?>" target="_blank" class="whatsapp-quick-link" title="Open direct WhatsApp chat (No automated message)">
                                                <span class="dashicons dashicons-format-chat"></span> WhatsApp
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-subtle text-xs mt-1">
                                        <?php echo esc_html($cust['city']); ?><?php if (!empty($cust['email'])) : ?> &bull; <?php echo esc_html($cust['email']); ?><?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-status status-completed">
                                        <?php echo esc_html($cust['orders_count']); ?> order(s)
                                    </span>
                                </td>
                                <td>
                                    <div class="font-bold text-dark" style="font-size: 14px;">
                                        <?php echo esc_html($c . number_format($cust['total_spent'], 2)); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-subtle text-xs font-bold"><?php echo esc_html($c . number_format($cust['aov'], 2)); ?></span>
                                </td>
                                <td>
                                    <span class="text-subtle text-xs"><?php echo esc_html($cust['last_date_formatted']); ?></span>
                                </td>
                                <td>
                                    <!-- In-line Customer Tag Dropdown (VIP / Problematic / New) -->
                                    <select class="select-inline-customer-tag" data-customer-key="<?php echo esc_attr($cust['key']); ?>">
                                        <option value="New" <?php selected($cust['tag'], 'New'); ?>>New</option>
                                        <option value="VIP" <?php selected($cust['tag'], 'VIP'); ?>>⭐ VIP</option>
                                        <option value="Problematic" <?php selected($cust['tag'], 'Problematic'); ?>>⚠️ Problematic</option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <div class="order-action-buttons">
                                        <!-- View Complete History -->
                                        <button type="button" class="btn-view-customer-history btn-icon-action" data-customer-key="<?php echo esc_attr($cust['key']); ?>" title="View Complete Order History">
                                            <span class="dashicons dashicons-list-view"></span>
                                        </button>

                                        <!-- WhatsApp Chat Link -->
                                        <?php if (!empty($cust['clean_phone'])) : ?>
                                            <a href="<?php echo esc_url($cust['whatsapp_url']); ?>" target="_blank" class="btn-icon-action" style="color: #15803D;" title="Open WhatsApp Chat in new tab">
                                                <span class="dashicons dashicons-format-chat"></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="kinboni-empty-state">
                    <span class="dashicons dashicons-groups"></span>
                    <h3>No Patrons Found</h3>
                    <p>There are no customers matching your search criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($max_pages > 1) : ?>
        <div class="kinboni-pagination">
            <?php for ($p = 1; $p <= $max_pages; $p++) : ?>
                <a href="<?php echo esc_url(add_query_arg('paged', $p)); ?>" class="page-link <?php echo $current_page == $p ? 'is-active' : ''; ?>">
                    <?php echo esc_html($p); ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <!-- CUSTOMER ORDER HISTORY SLIDE-OVER MODAL -->
    <div id="kinboni-customer-modal" class="kinboni-modal-overlay hidden">
        <div class="kinboni-modal-backdrop"></div>
        <div class="kinboni-slideover-panel" style="max-width: 580px;">
            <div class="slideover-header">
                <div>
                    <h3 id="modal-cust-name-title" class="slideover-title">Patron Dossier</h3>
                    <span id="modal-cust-tag-badge" class="badge-status status-completed">New</span>
                </div>
                <button type="button" id="btn-close-cust-modal" class="btn-close-modal">✕</button>
            </div>

            <div class="slideover-body">
                <!-- Patron Profile Summary Box -->
                <div class="modal-info-card">
                    <div class="grid-2col">
                        <div>
                            <span class="info-label">Mobile Contact</span>
                            <div id="modal-dossier-phone" class="font-bold text-dark text-sm mt-1">—</div>
                            <div id="modal-dossier-email" class="text-xs text-subtle">—</div>
                            <div class="mt-2">
                                <a id="modal-dossier-wa-btn" href="#" target="_blank" class="whatsapp-quick-link inline-flex">
                                    <span class="dashicons dashicons-format-chat"></span> Open WhatsApp Chat
                                </a>
                            </div>
                        </div>
                        <div>
                            <span class="info-label">Clientele Metrics</span>
                            <div class="text-xs mt-1">Total Acquisitions: <strong id="modal-dossier-orders-count">0</strong></div>
                            <div class="text-xs mt-1">Lifetime Value: <strong id="modal-dossier-total-spent">৳0</strong></div>
                            <div class="text-xs mt-1">Average Order Value: <strong id="modal-dossier-aov">৳0</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Complete Order History List -->
                <h4 class="section-title">Complete Order History</h4>
                <div id="modal-dossier-orders-list" class="modal-items-list">
                    <!-- Populated via JS -->
                </div>
            </div>
        </div>
    </div>

</div>

