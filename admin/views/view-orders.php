<?php
/**
 * Kinboni Pro Admin View: Orders Management
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $orders_data
 * @var array $wc_statuses
 */

if (!defined('ABSPATH')) {
    exit;
}

$orders       = $orders_data['orders'] ?? array();
$total        = $orders_data['total'] ?? 0;
$max_pages    = $orders_data['max_pages'] ?? 1;
$current_page = $orders_data['current_page'] ?? 1;

$current_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
$current_pay    = isset($_GET['pay_method']) ? sanitize_key(wp_unslash($_GET['pay_method'])) : '';
$current_flag   = isset($_GET['flagged']) ? sanitize_key(wp_unslash($_GET['flagged'])) : '';
$search_term    = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$start_date     = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : '';
$end_date       = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : '';

$export_url = wp_nonce_url(add_query_arg(array(
    'page'       => 'kinboni-orders',
    'action'     => 'export_csv',
    'status'     => $current_status,
    'start_date' => $start_date,
    'end_date'   => $end_date,
), admin_url('admin.php')), 'kinboni_orders_export', '_kb_nonce');
?>

<div class="kinboni-admin-wrap" id="kinboni-orders-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Orders</span></h1>
                <p class="kinboni-subtitle">HPOS Order Processing &bull; <?php echo esc_html($total); ?> Total Acquisition(s)</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <!-- Export CSV Button -->
            <a href="<?php echo esc_url($export_url); ?>" class="btn-action-gold" title="Download Excel-compatible CSV">
                <span class="dashicons dashicons-download"></span> Export CSV
            </a>
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh Orders">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Sub-Navigation Status Tabs -->
    <div class="kinboni-tabs-bar">
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders')); ?>" class="tab-pill <?php echo (empty($current_status) && empty($current_pay) && empty($current_flag)) ? 'is-active' : ''; ?>">
            All Orders
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=processing')); ?>" class="tab-pill <?php echo $current_status === 'processing' ? 'is-active' : ''; ?>">
            Processing
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=pending')); ?>" class="tab-pill <?php echo $current_status === 'pending' ? 'is-active' : ''; ?>">
            Pending Payment
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=on-hold')); ?>" class="tab-pill <?php echo $current_status === 'on-hold' ? 'is-active' : ''; ?>">
            On Hold
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=completed')); ?>" class="tab-pill <?php echo $current_status === 'completed' ? 'is-active' : ''; ?>">
            Completed
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&pay_method=cod')); ?>" class="tab-pill <?php echo $current_pay === 'cod' ? 'is-active' : ''; ?>">
            Cash on Delivery (COD)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&flagged=yes')); ?>" class="tab-pill tab-flagged <?php echo $current_flag === 'yes' ? 'is-active' : ''; ?>">
            ⚠️ Suspicious / Flagged
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="kinboni-toolbar-panel">
        <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="kinboni-filter-form">
            <input type="hidden" name="page" value="kinboni-orders" />
            <?php if (!empty($current_status)) : ?>
                <input type="hidden" name="status" value="<?php echo esc_attr($current_status); ?>" />
            <?php endif; ?>
            <?php if (!empty($current_pay)) : ?>
                <input type="hidden" name="pay_method" value="<?php echo esc_attr($current_pay); ?>" />
            <?php endif; ?>

            <!-- Search Field -->
            <div class="filter-group flex-search">
                <span class="dashicons dashicons-search icon-search"></span>
                <input 
                    type="text" 
                    name="s" 
                    value="<?php echo esc_attr($search_term); ?>" 
                    placeholder="Search by order #, phone number, customer name..." 
                    class="input-search"
                />
            </div>

            <!-- Date Range Fields -->
            <div class="filter-group">
                <label>From:</label>
                <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>" class="input-date" />
            </div>
            <div class="filter-group">
                <label>To:</label>
                <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>" class="input-date" />
            </div>

            <button type="submit" class="btn-filter-apply">Filter</button>
            <?php if (!empty($search_term) || !empty($start_date) || !empty($end_date) || !empty($current_status) || !empty($current_pay) || !empty($current_flag)) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders')); ?>" class="btn-filter-reset">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Bulk Action Controls -->
        <div class="bulk-actions-wrapper">
            <select id="bulk-order-action-select" class="select-bulk">
                <option value="">Bulk Actions</option>
                <option value="status_processing">Mark Processing</option>
                <option value="status_completed">Mark Completed</option>
                <option value="status_on-hold">Mark On-Hold</option>
                <option value="status_cancelled">Mark Cancelled</option>
            </select>
            <button type="button" id="btn-apply-bulk-action" class="btn-bulk-apply">Apply</button>
        </div>
    </div>

    <!-- Orders Table Panel -->
    <div class="kinboni-panel" style="margin-top: 16px;">
        <div class="panel-body p-0">
            <?php if (!empty($orders)) : ?>
                <table class="kinboni-table kinboni-orders-table">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="check-all-orders" /></th>
                            <th width="110">Order</th>
                            <th>Customer & Contact</th>
                            <th>Destination</th>
                            <th>Creations</th>
                            <th>Total</th>
                            <th width="140">Status</th>
                            <th>Risk Warning</th>
                            <th width="160" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o) : ?>
                            <tr id="order-row-<?php echo esc_attr($o['id']); ?>" class="<?php echo $o['is_suspicious'] ? 'row-suspicious' : ''; ?>">
                                <td>
                                    <input type="checkbox" class="order-cb" value="<?php echo esc_attr($o['id']); ?>" />
                                </td>
                                <td>
                                    <a href="#" class="btn-view-order-details font-bold" data-order-id="<?php echo esc_attr($o['id']); ?>">
                                        #<?php echo esc_html($o['order_number']); ?>
                                    </a>
                                    <div class="text-subtle text-xs"><?php echo esc_html($o['date']); ?></div>
                                </td>
                                <td>
                                    <div class="font-bold text-dark"><?php echo esc_html($o['customer_name']); ?></div>
                                    <div class="phone-line">
                                        <span class="text-subtle"><?php echo esc_html($o['customer_phone']); ?></span>
                                        <?php if (!empty($o['clean_phone'])) : ?>
                                            <a href="https://wa.me/88<?php echo esc_attr($o['clean_phone']); ?>" target="_blank" class="whatsapp-quick-link" title="Open WhatsApp Chat (No auto-message)">
                                                <span class="dashicons dashicons-format-chat"></span> WhatsApp
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="address-snippet" title="<?php echo esc_attr($o['address']); ?>">
                                        <?php echo esc_html($o['city']); ?>
                                        <span class="text-subtle text-xs block"><?php echo esc_html(wp_trim_words($o['address'], 6)); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="items-summary" title="<?php echo esc_attr($o['items_text']); ?>">
                                        <strong><?php echo esc_html($o['items_count']); ?> item(s):</strong>
                                        <span class="text-subtle text-xs"><?php echo esc_html(wp_trim_words($o['items_text'], 5)); ?></span>
                                    </span>
                                </td>
                                <td>
                                    <div class="font-bold text-dark"><?php echo wp_kses_post($o['total_formatted']); ?></div>
                                    <?php if ($o['is_cod']) : ?>
                                        <span class="badge-cod">COD</span>
                                    <?php else : ?>
                                        <span class="badge-online"><?php echo esc_html($o['payment_method_title']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- In-line Quick Status Selector -->
                                    <select class="select-inline-status" data-order-id="<?php echo esc_attr($o['id']); ?>">
                                        <?php foreach ($wc_statuses as $st_key => $st_label) : 
                                            $clean_st = str_replace('wc-', '', $st_key);
                                        ?>
                                            <option value="<?php echo esc_attr($clean_st); ?>" <?php selected($o['status'], $clean_st); ?>>
                                                <?php echo esc_html($st_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <?php if ($o['phone_repeat_count'] >= 2) : ?>
                                        <span class="badge-risk-warning" title="<?php echo esc_attr($o['phone_repeat_count'] . ' orders found with this exact phone number in database'); ?>">
                                            ⚠️ <?php echo esc_html($o['phone_repeat_count']); ?>× Repeat Phone
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($o['manual_flag']) : ?>
                                        <span class="badge-risk-manual" title="Manually flagged by administrator for verification">
                                            🚩 Flagged
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!$o['is_suspicious']) : ?>
                                        <span class="badge-risk-clean">✓ Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <div class="order-action-buttons">
                                        <!-- Open Details Slide-Over -->
                                        <button type="button" class="btn-view-order-details btn-icon-action" data-order-id="<?php echo esc_attr($o['id']); ?>" title="View Order Details">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>

                                        <!-- Print Invoice -->
                                        <?php
                                        $invoice_url = wp_nonce_url(admin_url('admin.php?page=kinboni-orders&action=print_invoice&order_id=' . $o['id']), 'kinboni_print_order_' . $o['id'], '_kb_nonce');
                                        $slip_url = wp_nonce_url(admin_url('admin.php?page=kinboni-orders&action=print_slip&order_id=' . $o['id']), 'kinboni_print_order_' . $o['id'], '_kb_nonce');
                                        ?>
                                        <a href="<?php echo esc_url($invoice_url); ?>" target="_blank" class="btn-icon-action" title="Print Invoice">
                                            <span class="dashicons dashicons-media-document"></span>
                                        </a>

                                        <!-- Print Delivery Packing Slip -->
                                        <a href="<?php echo esc_url($slip_url); ?>" target="_blank" class="btn-icon-action" title="Print Courier Delivery Slip">
                                            <span class="dashicons dashicons-tag"></span>
                                        </a>

                                        <!-- Manual Flag Toggle -->
                                        <button type="button" class="btn-toggle-flag btn-icon-action <?php echo $o['manual_flag'] ? 'is-flagged' : ''; ?>" data-order-id="<?php echo esc_attr($o['id']); ?>" title="<?php echo $o['manual_flag'] ? 'Unflag order' : 'Flag order as suspicious for review'; ?>">
                                            <span class="dashicons dashicons-flag"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="kinboni-empty-state">
                    <span class="dashicons dashicons-cart"></span>
                    <h3>No Orders Found</h3>
                    <p>There are no orders matching your selected criteria.</p>
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

    <!-- ORDER DETAILS SLIDE-OVER MODAL -->
    <div id="kinboni-order-modal" class="kinboni-modal-overlay hidden">
        <div class="kinboni-modal-backdrop"></div>
        <div class="kinboni-slideover-panel">
            <div class="slideover-header">
                <div>
                    <h3 id="modal-order-title" class="slideover-title">Order #...</h3>
                    <span id="modal-order-status-badge" class="badge-status">Processing</span>
                </div>
                <button type="button" id="btn-close-order-modal" class="btn-close-modal">✕</button>
            </div>

            <div class="slideover-body">
                <!-- Suspicious Warning Banner (shown if repeated phone) -->
                <div id="modal-suspicious-alert" class="alert-box-warning hidden">
                    <span class="dashicons dashicons-warning"></span>
                    <div>
                        <strong id="modal-suspicious-heading">Potential Suspicious Order</strong>
                        <p id="modal-suspicious-desc">Customer has multiple orders in the database with the same mobile number.</p>
                        <div id="modal-repeat-orders-list" class="repeat-orders-tags"></div>
                    </div>
                </div>

                <!-- Section: Customer & Delivery Info -->
                <div class="modal-info-card">
                    <div class="grid-2col">
                        <div>
                            <span class="info-label">Customer Name</span>
                            <h4 id="modal-cust-name" class="info-val">—</h4>
                            <div id="modal-cust-phone" class="info-sub">—</div>
                            <div id="modal-cust-email" class="info-sub">—</div>
                            <div class="mt-2">
                                <a id="modal-whatsapp-btn" href="#" target="_blank" class="whatsapp-quick-link inline-flex">
                                    <span class="dashicons dashicons-format-chat"></span> WhatsApp Chat
                                </a>
                            </div>
                        </div>
                        <div>
                            <span class="info-label">Delivery Address</span>
                            <div id="modal-delivery-addr" class="info-address">—</div>
                            <div class="mt-2 text-xs text-subtle">
                                Method: <strong id="modal-shipping-method">—</strong>
                            </div>
                            <div id="modal-steadfast-box" style="margin-top:10px; display:none; background:#ECFDF5; border:1px solid #10B981; padding:8px 12px; border-radius:6px; font-size:12px; color:#065F46;">
                                <strong>Steadfast Courier:</strong> Consignment #<span id="modal-steadfast-consignment"></span> | Tracking: <strong id="modal-steadfast-tracking"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Line Items -->
                <div class="modal-items-section">
                    <h4 class="section-title">Order Items</h4>
                    <div id="modal-line-items" class="modal-items-list">
                        <!-- Populated dynamically via JS -->
                    </div>

                    <!-- Totals Breakdown -->
                    <div class="modal-totals-box">
                        <div class="total-row"><span>Subtotal:</span><strong id="modal-subtotal">৳0</strong></div>
                        <div class="total-row"><span>Discount:</span><strong id="modal-discount">-৳0</strong></div>
                        <div class="total-row"><span>Shipping:</span><strong id="modal-shipping">৳0</strong></div>
                        <div class="total-row grand-total"><span>Grand Total:</span><strong id="modal-total">৳0</strong></div>
                        <div class="total-row text-xs text-subtle"><span>Payment Method:</span><span id="modal-pay-title">COD</span></div>
                    </div>
                </div>

                <!-- Section: Order Notes & Private Admin Notes -->
                <div class="modal-notes-section">
                    <h4 class="section-title">Order Notes & Private Annotations</h4>
                    <div id="modal-notes-list" class="modal-notes-timeline">
                        <!-- Populated dynamically via JS -->
                    </div>

                    <!-- Add Note Form -->
                    <div class="add-note-box">
                        <textarea id="input-new-order-note" placeholder="Write a private administrative note for this order..." rows="2"></textarea>
                        <button type="button" id="btn-submit-order-note" class="btn-action-gold mt-2">Add Private Note</button>
                    </div>
                </div>

                <!-- Actions Footer -->
                <div class="slideover-footer" style="display:flex; flex-wrap:wrap; gap:8px;">
                    <button type="button" id="btn-book-steadfast" class="btn-action-gold" style="background:#059669; border-color:#059669; color:#fff;">
                        <span class="dashicons dashicons-external"></span> Book Steadfast Courier
                    </button>
                    <a id="modal-invoice-link" href="#" target="_blank" class="btn-outline-gold">Print Invoice</a>
                    <a id="modal-slip-link" href="#" target="_blank" class="btn-outline-gold">Courier Slip</a>
                    <button type="button" id="modal-toggle-flag-btn" class="btn-flag-toggle">Flag as Suspicious</button>
                </div>
            </div>
        </div>
    </div>

</div>

