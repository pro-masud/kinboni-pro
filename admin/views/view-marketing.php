<?php
/**
 * Kinboni Pro Admin View: Marketing & Growth Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var string $active_tab
 * @var array $subscribers_data
 * @var array $coupons_data
 * @var array $abandoned_data
 * @var array $settings_data
 */

if (!defined('ABSPATH')) {
    exit;
}

$c = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';
$base_url = admin_url('admin.php?page=kinboni-marketing');
?>

<div class="kinboni-admin-wrap" id="kinboni-marketing-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Marketing & Growth</span></h1>
                <p class="kinboni-subtitle">Campaigns, Coupon Engine, Cart Recovery & Tracking Integrations</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <?php if ($active_tab === 'subscribers') : ?>
                <button type="button" id="btn-open-campaign-modal" class="btn-action-gold">
                    <span class="dashicons dashicons-email-alt"></span> Send Campaign
                </button>
                <button type="button" id="btn-open-add-sub-modal" class="btn-action-outline">
                    <span class="dashicons dashicons-plus"></span> Add Subscriber
                </button>
                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('page' => 'kinboni-marketing', 'action' => 'export_subscribers'), admin_url('admin.php')), 'kinboni_marketing_export', '_kb_nonce')); ?>" class="btn-action-outline" title="Export CSV">
                    <span class="dashicons dashicons-download"></span> Export CSV
                </a>
            <?php elseif ($active_tab === 'coupons') : ?>
                <button type="button" id="btn-open-coupon-modal" class="btn-action-gold">
                    <span class="dashicons dashicons-tag"></span> Create Coupon
                </button>
            <?php endif; ?>
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Navigation Tabs Bar -->
    <div class="kinboni-tabs-bar" style="margin-bottom: 24px;">
        <a href="<?php echo esc_url(add_query_arg('tab', 'subscribers', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'subscribers' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-groups"></span> Subscribers
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'coupons', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'coupons' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-tag"></span> Coupon Manager
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'abandoned', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'abandoned' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-cart"></span> Abandoned Carts
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'settings', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'settings' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-admin-settings"></span> Marketing Settings
        </a>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 1: SUBSCRIBERS                                                  -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'subscribers') : 
        $subs_list    = $subscribers_data['subscribers'] ?? array();
        $metrics      = $subscribers_data['metrics'] ?? array();
        $growth_chart = $subscribers_data['growth_chart'] ?? array();
        $total_subs   = $subscribers_data['total'] ?? 0;
        $max_pages    = $subscribers_data['max_pages'] ?? 1;
        $curr_page    = $subscribers_data['current_page'] ?? 1;
        $search_term  = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $filter_stat  = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
    ?>
        <script>
            window.kinboniSubscribersInit = <?php echo wp_json_encode($growth_chart); ?>;
        </script>

        <!-- Metrics Strip -->
        <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Total Audience</span>
                    <span class="metric-icon-gold"><span class="dashicons dashicons-email"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0;"><?php echo esc_html($metrics['total_all'] ?? 0); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Active Subscribers</span>
                    <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-yes-alt"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#059669;"><?php echo esc_html($metrics['active'] ?? 0); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Unsubscribed</span>
                    <span class="metric-icon-gold" style="color:#DC2626;background:#FEF2F2;"><span class="dashicons dashicons-dismiss"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#DC2626;"><?php echo esc_html($metrics['unsubscribed'] ?? 0); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">New This Month</span>
                    <span class="metric-icon-gold" style="color:#D4AF37;background:#FCF9EE;"><span class="dashicons dashicons-chart-line"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#B89628;"><?php echo esc_html($metrics['this_month'] ?? 0); ?></h3>
            </div>
        </section>

        <!-- Subscriber Growth Graph Card -->
        <div class="kinboni-panel" style="margin-bottom: 20px;">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid #E8E6DF;">
                <div class="panel-title-wrap" style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-chart-area" style="color: #D4AF37; font-size: 20px;"></span>
                    <h3 class="panel-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #121214;">Audience Acquisition & Growth Trend</h3>
                </div>
                <div class="chart-controls-group">
                    <div class="pills-time-filter" id="sub-chart-range-pills" style="display: flex; gap: 6px;">
                        <button type="button" class="btn-time-pill" data-range="today">Today</button>
                        <button type="button" class="btn-time-pill" data-range="7d">Last 7 Days</button>
                        <button type="button" class="btn-time-pill is-active" data-range="30d">Last 30 Days</button>
                        <button type="button" class="btn-time-pill" data-range="12m">Last 12 Months</button>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding: 16px 20px;">
                <div class="chart-canvas-wrapper" style="position: relative; height: 240px; width: 100%;">
                    <canvas id="kinboniSubscribersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Search & Filter Toolbar -->
        <div class="kinboni-toolbar-panel">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="kinboni-filter-form">
                <input type="hidden" name="page" value="kinboni-marketing" />
                <input type="hidden" name="tab" value="subscribers" />

                <div class="filter-group flex-search">
                    <span class="dashicons dashicons-search icon-search"></span>
                    <input type="text" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="Search subscriber by email or name..." class="input-search" />
                </div>

                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="subscribed" <?php selected($filter_stat, 'subscribed'); ?>>Subscribed</option>
                        <option value="unsubscribed" <?php selected($filter_stat, 'unsubscribed'); ?>>Unsubscribed</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter-apply">Filter</button>
                <?php if (!empty($search_term) || !empty($filter_stat)) : ?>
                    <a href="<?php echo esc_url(add_query_arg('tab', 'subscribers', $base_url)); ?>" class="btn-filter-reset">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Subscribers Table -->
        <div class="kinboni-panel" style="margin-top: 16px;">
            <div class="panel-body p-0">
                <?php if (!empty($subs_list)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Subscriber Email</th>
                                <th>Full Name</th>
                                <th width="140">Status</th>
                                <th width="180">Subscribed At</th>
                                <th width="150" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subs_list as $s) : ?>
                                <tr id="sub-row-<?php echo esc_attr($s['id']); ?>">
                                    <td>
                                        <div class="font-bold text-dark"><?php echo esc_html($s['email']); ?></div>
                                    </td>
                                    <td>
                                        <span><?php echo esc_html($s['name'] ?: '—'); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo ($s['status'] === 'subscribed') ? 'status-completed' : 'status-cancelled'; ?>">
                                            <?php echo esc_html(ucfirst($s['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-subtle text-xs"><?php echo esc_html(date_i18n('M j, Y H:i', strtotime($s['subscribed_at']))); ?></span>
                                    </td>
                                    <td class="text-right">
                                        <div class="order-action-buttons">
                                            <!-- Toggle Status -->
                                            <button type="button" class="btn-toggle-sub btn-icon-action" data-sub-id="<?php echo esc_attr($s['id']); ?>" title="Toggle Subscribed / Unsubscribed">
                                                <span class="dashicons dashicons-update"></span>
                                            </button>
                                            <!-- Delete -->
                                            <button type="button" class="btn-delete-sub btn-icon-action" data-sub-id="<?php echo esc_attr($s['id']); ?>" style="color: #DC2626;" title="Delete Permanently">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-email-alt"></span>
                        <h3>No Subscribers Yet</h3>
                        <p>Subscribers collected through the footer newsletter modal will automatically appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($max_pages > 1) : ?>
            <div class="kinboni-pagination">
                <?php for ($p = 1; $p <= $max_pages; $p++) : ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $p)); ?>" class="page-link <?php echo $curr_page == $p ? 'is-active' : ''; ?>">
                        <?php echo esc_html($p); ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <!-- MODAL: SEND MARKETING CAMPAIGN -->
        <div id="modal-marketing-campaign" class="kinboni-modal-overlay hidden">
            <div class="kinboni-modal-backdrop"></div>
            <div class="kinboni-slideover-panel" style="max-width: 600px;">
                <div class="slideover-header">
                    <div>
                        <h3 class="slideover-title">Broadcast Email Campaign</h3>
                        <span class="badge-status status-completed">The Kinboni Circle</span>
                    </div>
                    <button type="button" class="btn-close-modal" id="btn-close-campaign-modal">✕</button>
                </div>
                <form id="form-send-campaign" class="slideover-body">
                    <div class="form-group mb-4">
                        <label class="form-label">Audience Segment</label>
                        <select name="audience" class="form-control">
                            <option value="all">All Active Subscribers (<?php echo esc_html($metrics['active'] ?? 0); ?>)</option>
                            <option value="recent">Recent Subscribers (Last 30 Days: <?php echo esc_html($metrics['this_month'] ?? 0); ?>)</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Email Subject Line *</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. An Exclusive Invitation: Autumn Luxury Drop" required />
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Banner Headline</label>
                        <input type="text" name="headline" class="form-control" placeholder="e.g. Discover Curated Sophistication" />
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Email Message Content *</label>
                        <textarea name="content" rows="6" class="form-control" placeholder="Write your luxury announcement, promotion details, or coupon instructions..." required></textarea>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Call to Action Button Text</label>
                            <input type="text" name="btn_text" class="form-control" placeholder="e.g. Shop The Drop" value="Shop Now" />
                        </div>
                        <div>
                            <label class="form-label">Button Target URL</label>
                            <input type="url" name="btn_url" class="form-control" value="<?php echo esc_url(home_url('/shop/')); ?>" />
                        </div>
                    </div>

                    <div style="background:#FCF9EE; border:1px solid #E8E6DF; border-radius:6px; padding:12px; margin-bottom:20px; font-size:12px; color:#6B7280;">
                        <span class="dashicons dashicons-info" style="color:#D4AF37;"></span>
                        Emails are dispatched using responsive branded luxury styling with an automatic one-click unsubscribe mechanism.
                    </div>

                    <button type="submit" id="btn-submit-campaign" class="btn-action-gold w-full" style="padding: 12px; font-size: 14px;">
                        <span class="dashicons dashicons-email-alt"></span> Dispatch Broadcast Campaign
                    </button>
                </form>
            </div>
        </div>

        <!-- MODAL: ADD SUBSCRIBER -->
        <div id="modal-add-sub" class="kinboni-modal-overlay hidden">
            <div class="kinboni-modal-backdrop"></div>
            <div class="kinboni-slideover-panel" style="max-width: 440px;">
                <div class="slideover-header">
                    <div>
                        <h3 class="slideover-title">Add New Subscriber</h3>
                    </div>
                    <button type="button" class="btn-close-modal" id="btn-close-add-sub">✕</button>
                </div>
                <form id="form-add-subscriber" class="slideover-body">
                    <div class="form-group mb-4">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required placeholder="patron@domain.com" />
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Patron Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Masud Rana" />
                    </div>
                    <button type="submit" class="btn-action-gold w-full" style="padding:10px;">
                        Save Subscriber
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 2: COUPON MANAGER                                               -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'coupons') : 
        $coupons = $coupons_data['coupons'] ?? array();
        $metrics = $coupons_data['metrics'] ?? array();
    ?>
        <!-- Metrics Strip -->
        <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 20px;">
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Active Coupons</span>
                    <span class="metric-icon-gold"><span class="dashicons dashicons-tag"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0;"><?php echo esc_html($metrics['total_coupons'] ?? 0); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Total Redemptions</span>
                    <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-yes-alt"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#059669;"><?php echo esc_html($metrics['total_redemptions'] ?? 0); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Total Discount Distributed</span>
                    <span class="metric-icon-gold" style="color:#D4AF37;background:#FCF9EE;"><span class="dashicons dashicons-money-alt"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#B89628;"><?php echo esc_html($c . number_format($metrics['total_discount'] ?? 0, 2)); ?></h3>
            </div>
        </section>

        <!-- Coupons Table -->
        <div class="kinboni-panel">
            <div class="panel-body p-0">
                <?php if (!empty($coupons)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Coupon Code</th>
                                <th>Type & Amount</th>
                                <th>Usage / Limit</th>
                                <th>Total Discount Given</th>
                                <th>Expiry</th>
                                <th>Restrictions</th>
                                <th width="100" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $cpn) : ?>
                                <tr id="coupon-row-<?php echo esc_attr($cpn['id']); ?>">
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-dark" style="letter-spacing:1px; background:#F8F7F4; border:1px dashed #D4AF37; padding:4px 8px; border-radius:4px;">
                                                <?php echo esc_html($cpn['code']); ?>
                                            </span>
                                            <button type="button" class="btn-copy-code" data-code="<?php echo esc_attr($cpn['code']); ?>" title="Copy Code" style="background:none;border:none;cursor:pointer;color:#9CA3AF;">
                                                <span class="dashicons dashicons-admin-page"></span>
                                            </button>
                                        </div>
                                        <?php if (!empty($cpn['description'])) : ?>
                                            <div class="text-subtle text-xs mt-1"><?php echo esc_html($cpn['description']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="font-bold text-dark">
                                            <?php 
                                            if ($cpn['discount_type'] === 'percent') {
                                                echo esc_html($cpn['amount'] . '% OFF');
                                            } else {
                                                echo esc_html($c . number_format($cpn['amount'], 2) . ' OFF');
                                            }
                                            ?>
                                        </div>
                                        <div class="text-subtle text-xs"><?php echo esc_html(ucwords(str_replace('_', ' ', $cpn['discount_type']))); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-status status-completed">
                                            <?php echo esc_html($cpn['usage_count']); ?> / <?php echo esc_html($cpn['usage_limit']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-bold text-dark"><?php echo esc_html($c . number_format($cpn['total_discount'], 2)); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($cpn['is_expired']) : ?>
                                            <span class="badge-status status-cancelled">Expired</span>
                                        <?php else : ?>
                                            <span class="text-subtle text-xs"><?php echo esc_html($cpn['expiry_date']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-subtle text-xs">
                                            <?php if ($cpn['min_spend'] > 0) echo 'Min: ' . $c . $cpn['min_spend'] . '<br>'; ?>
                                            <?php if ($cpn['free_shipping']) echo '🚚 Free Shipping<br>'; ?>
                                            <?php if ($cpn['individual_use']) echo '🔒 Individual Use<br>'; ?>
                                            <?php if (!$cpn['min_spend'] && !$cpn['free_shipping'] && !$cpn['individual_use']) echo 'None'; ?>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn-delete-coupon btn-icon-action" data-coupon-id="<?php echo esc_attr($cpn['id']); ?>" style="color:#DC2626;" title="Delete Coupon">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-tag"></span>
                        <h3>No Coupons Found</h3>
                        <p>Create attractive promotional codes to incentivize connoisseurs and boost order values.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- MODAL: CREATE COUPON -->
        <div id="modal-create-coupon" class="kinboni-modal-overlay hidden">
            <div class="kinboni-modal-backdrop"></div>
            <div class="kinboni-slideover-panel" style="max-width: 520px;">
                <div class="slideover-header">
                    <div>
                        <h3 class="slideover-title">Create Promotional Coupon</h3>
                    </div>
                    <button type="button" class="btn-close-modal" id="btn-close-coupon-modal">✕</button>
                </div>
                <form id="form-create-coupon" class="slideover-body">
                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Coupon Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. LUXE25" required style="text-transform:uppercase;" />
                        </div>
                        <div>
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-control">
                                <option value="percent">Percentage Discount (%)</option>
                                <option value="fixed_cart">Fixed Cart Discount (৳)</option>
                                <option value="fixed_product">Fixed Product Discount (৳)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Discount Amount *</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 20 or 250" required />
                        </div>
                        <div>
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Description / Note</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. 20% off for Circle members" />
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Minimum Spend (৳)</label>
                            <input type="number" step="0.01" name="min_spend" class="form-control" placeholder="0" />
                        </div>
                        <div>
                            <label class="form-label">Maximum Spend (৳)</label>
                            <input type="number" step="0.01" name="max_spend" class="form-control" placeholder="0" />
                        </div>
                    </div>

                    <div class="grid-2col mb-4">
                        <div>
                            <label class="form-label">Total Usage Limit</label>
                            <input type="number" name="usage_limit" class="form-control" placeholder="0 = Unlimited" />
                        </div>
                        <div>
                            <label class="form-label">Usage Limit Per User</label>
                            <input type="number" name="user_limit" class="form-control" value="1" />
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="free_shipping" value="yes" />
                            <span class="text-sm font-bold">Grant Free Shipping</span>
                        </label>
                    </div>

                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="individual_use" value="yes" />
                            <span class="text-sm font-bold">Individual Use Only (Cannot be combined)</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-action-gold w-full" style="padding:12px;">
                        Save & Activate Coupon
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 3: ABANDONED CARTS                                              -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'abandoned') : 
        $carts_list   = $abandoned_data['carts'] ?? array();
        $metrics      = $abandoned_data['metrics'] ?? array();
        $total_carts  = $abandoned_data['total'] ?? 0;
        $max_pages    = $abandoned_data['max_pages'] ?? 1;
        $curr_page    = $abandoned_data['current_page'] ?? 1;
    ?>
        <!-- Metrics Strip -->
        <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Abandoned Bags</span>
                    <span class="metric-icon-gold"><span class="dashicons dashicons-cart"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#DC2626;"><?php echo esc_html($metrics['abandoned_count'] ?? 0); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Recovered Orders</span>
                    <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-yes-alt"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#059669;"><?php echo esc_html($metrics['recovered_count'] ?? 0); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Potential Value</span>
                    <span class="metric-icon-gold" style="color:#D4AF37;background:#FCF9EE;"><span class="dashicons dashicons-money-alt"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#B89628;"><?php echo esc_html($c . number_format($metrics['potential_val'] ?? 0, 2)); ?></h3>
            </div>
            <div class="kinboni-metric-card" style="padding: 16px 20px;">
                <div class="metric-head" style="margin-bottom: 6px;">
                    <span class="metric-title">Recovery Rate</span>
                    <span class="metric-icon-gold" style="color:#121214;background:#E8E6DF;"><span class="dashicons dashicons-chart-pie"></span></span>
                </div>
                <h3 class="metric-number" style="font-size: 22px; margin: 0;"><?php echo esc_html($metrics['recovery_rate'] ?? 0); ?>%</h3>
            </div>
        </section>

        <!-- Abandoned Carts Table -->
        <div class="kinboni-panel">
            <div class="panel-body p-0">
                <?php if (!empty($carts_list)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Patron Contact</th>
                                <th>Curated Selection</th>
                                <th>Cart Total</th>
                                <th>Abandoned</th>
                                <th>Reminder</th>
                                <th width="180" class="text-right">Recovery Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carts_list as $cart) : ?>
                                <tr id="cart-row-<?php echo esc_attr($cart['id']); ?>">
                                    <td>
                                        <div class="font-bold text-dark"><?php echo esc_html($cart['customer_name'] ?: 'Guest Patron'); ?></div>
                                        <div class="text-subtle text-xs"><?php echo esc_html($cart['customer_email'] ?: 'No email recorded'); ?></div>
                                        <?php if (!empty($cart['customer_phone'])) : ?>
                                            <div class="phone-line mt-1">
                                                <span class="font-bold text-xs"><?php echo esc_html($cart['customer_phone']); ?></span>
                                                <a href="<?php echo esc_url($cart['whatsapp_url']); ?>" target="_blank" class="whatsapp-quick-link" title="Open direct WhatsApp chat">
                                                    <span class="dashicons dashicons-format-chat"></span> WhatsApp
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="flex flex-col gap-1">
                                            <?php foreach (array_slice($cart['items'], 0, 3) as $it) : ?>
                                                <div class="text-xs">
                                                    <strong><?php echo esc_html($it['quantity']); ?>x</strong> <?php echo esc_html($it['name']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($cart['items']) > 3) : ?>
                                                <div class="text-subtle text-xs">+ <?php echo (count($cart['items']) - 3); ?> more items</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-bold text-dark" style="font-size:14px;"><?php echo esc_html($c . number_format($cart['cart_total'], 2)); ?></div>
                                    </td>
                                    <td>
                                        <span class="text-subtle text-xs"><?php echo esc_html($cart['time_ago']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo ($cart['reminder_count'] > 0) ? 'status-completed' : 'status-processing'; ?>">
                                            Sent <?php echo esc_html($cart['reminder_count']); ?>x
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="order-action-buttons">
                                            <?php if (!empty($cart['customer_email'])) : ?>
                                                <button type="button" class="btn-send-cart-reminder btn-action-gold" data-cart-id="<?php echo esc_attr($cart['id']); ?>" style="padding:4px 8px;font-size:11px;" title="Dispatch Recovery Email with deduplication">
                                                    <span class="dashicons dashicons-email-alt"></span> Remind
                                                </button>
                                            <?php endif; ?>
                                            <a href="<?php echo esc_url($cart['recovery_url']); ?>" target="_blank" class="btn-icon-action" title="Test / Preview Cart Restore Link">
                                                <span class="dashicons dashicons-external"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-cart"></span>
                        <h3>No Abandoned Carts</h3>
                        <p>Customer cart sessions that are not converted within the configured detection time will be listed here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($max_pages > 1) : ?>
            <div class="kinboni-pagination">
                <?php for ($p = 1; $p <= $max_pages; $p++) : ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $p)); ?>" class="page-link <?php echo $curr_page == $p ? 'is-active' : ''; ?>">
                        <?php echo esc_html($p); ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 4: MARKETING SETTINGS & TRACKING                                -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'settings') : ?>
        <form id="form-marketing-settings">
            <div class="grid-2col" style="gap: 24px; align-items: start;">

                <!-- Free Delivery & Cart Rules -->
                <div class="kinboni-panel">
                    <div class="panel-header">
                        <h3 class="panel-title">Delivery Incentives & Cart Rules</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group mb-4">
                            <label class="form-label">Minimum Spend for Free Delivery (৳) *</label>
                            <input type="number" step="1" name="free_shipping_threshold" class="form-control" value="<?php echo esc_attr($settings_data['free_shipping_threshold']); ?>" required />
                            <p class="text-xs text-subtle mt-1">Orders exceeding this amount automatically receive complimentary delivery inside & outside Dhaka.</p>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Cart Abandonment Detection Delay (Minutes) *</label>
                            <input type="number" step="5" min="5" name="abandoned_threshold_min" class="form-control" value="<?php echo esc_attr($settings_data['abandoned_threshold_min']); ?>" required />
                            <p class="text-xs text-subtle mt-1">Number of minutes of inactivity before an uncompleted cart is marked abandoned.</p>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Abandoned Cart Reminder Subject Line</label>
                            <input type="text" name="abandoned_email_subject" class="form-control" value="<?php echo esc_attr($settings_data['abandoned_email_subject']); ?>" />
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Abandoned Cart Courtesy Discount Code (Optional)</label>
                            <input type="text" name="abandoned_email_coupon" class="form-control" value="<?php echo esc_attr($settings_data['abandoned_email_coupon']); ?>" placeholder="e.g. RECOVER10" style="text-transform:uppercase;" />
                        </div>
                    </div>
                </div>

                <!-- Meta & Google Analytics Tracking -->
                <div class="kinboni-panel">
                    <div class="panel-header">
                        <h3 class="panel-title">Pixel & Analytics Tracking</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group mb-4">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="checkbox" name="fb_pixel_enabled" value="yes" <?php checked($settings_data['fb_pixel_enabled'], 'yes'); ?> />
                                <span class="font-bold text-dark">Enable Meta / Facebook Pixel</span>
                            </label>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label">Facebook Pixel ID</label>
                            <input type="text" name="fb_pixel_id" class="form-control" value="<?php echo esc_attr($settings_data['fb_pixel_id']); ?>" placeholder="e.g. 123456789012345" />
                            <p class="text-xs text-subtle mt-1">Injects standard PageView and eCommerce conversion tracking events.</p>
                        </div>

                        <div class="form-group mb-4" style="border-top:1px solid var(--kb-border-light); padding-top:16px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="checkbox" name="ga4_enabled" value="yes" <?php checked($settings_data['ga4_enabled'], 'yes'); ?> />
                                <span class="font-bold text-dark">Enable Google Analytics 4 (GA4)</span>
                            </label>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label">GA4 Measurement ID</label>
                            <input type="text" name="ga4_measurement_id" class="form-control" value="<?php echo esc_attr($settings_data['ga4_measurement_id']); ?>" placeholder="e.g. G-XXXXXXXXXX" />
                            <p class="text-xs text-subtle mt-1">Injects gtag.js with anonymized IP headers for privacy compliance.</p>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Promotional Popup Banner -->
                <div class="kinboni-panel" style="grid-column: span 2;">
                    <div class="panel-header">
                        <h3 class="panel-title">Scheduled Promotional Popup Banner</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group mb-4">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="checkbox" name="popup_enabled" value="yes" <?php checked($settings_data['popup_enabled'], 'yes'); ?> />
                                <span class="font-bold text-dark" style="color:var(--kb-gold-600);">Enable Front-end Promotional Modal Popup</span>
                            </label>
                        </div>

                        <div class="grid-2col mb-4">
                            <div>
                                <label class="form-label">Popup Title</label>
                                <input type="text" name="popup_title" class="form-control" value="<?php echo esc_attr($settings_data['popup_title']); ?>" />
                            </div>
                            <div>
                                <label class="form-label">Popup Subtitle / Text</label>
                                <input type="text" name="popup_subtitle" class="form-control" value="<?php echo esc_attr($settings_data['popup_subtitle']); ?>" />
                            </div>
                        </div>

                        <div class="grid-2col mb-4">
                            <div>
                                <label class="form-label">Banner Image URL</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="url" id="input-popup-image" name="popup_image_url" class="form-control" value="<?php echo esc_attr($settings_data['popup_image_url']); ?>" placeholder="https://..." />
                                    <button type="button" id="btn-upload-popup-img" class="btn-action-outline">Media</button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Display Delay (Seconds)</label>
                                <input type="number" min="1" max="60" name="popup_delay_sec" class="form-control" value="<?php echo esc_attr($settings_data['popup_delay_sec']); ?>" />
                            </div>
                        </div>

                        <div class="grid-2col mb-4">
                            <div>
                                <label class="form-label">Button Label</label>
                                <input type="text" name="popup_btn_text" class="form-control" value="<?php echo esc_attr($settings_data['popup_btn_text']); ?>" />
                            </div>
                            <div>
                                <label class="form-label">Button Target URL</label>
                                <input type="url" name="popup_btn_url" class="form-control" value="<?php echo esc_attr($settings_data['popup_btn_url']); ?>" />
                            </div>
                        </div>

                        <div class="grid-2col mb-4">
                            <div>
                                <label class="form-label">Schedule Start Date (Optional)</label>
                                <input type="date" name="popup_start_date" class="form-control" value="<?php echo esc_attr($settings_data['popup_start_date']); ?>" />
                            </div>
                            <div>
                                <label class="form-label">Schedule End Date (Optional)</label>
                                <input type="date" name="popup_end_date" class="form-control" value="<?php echo esc_attr($settings_data['popup_end_date']); ?>" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Save Settings Button -->
            <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                <button type="submit" id="btn-save-marketing-settings" class="btn-action-gold" style="padding:12px 28px; font-size:14px;">
                    <span class="dashicons dashicons-saved"></span> Save Marketing Settings
                </button>
            </div>
        </form>
    <?php endif; ?>

</div>

