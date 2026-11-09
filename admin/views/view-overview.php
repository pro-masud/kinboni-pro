<?php
/**
 * Kinboni Pro Admin View: Overview Dashboard
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $stats
 */

if (!defined('ABSPATH')) {
    exit;
}

$c = $stats['currency'];
$orders = $stats['order_counts'];
$sales = $stats['sales_metrics'];
$inv = $stats['inventory'];
$tasks = $stats['today_tasks'];
$recent = $stats['recent_orders'];
$bestsellers = $stats['bestsellers'];
$categories = $stats['top_categories'];
$visitors = $stats['visitor_stats'];
$current_range = $stats['range'];
?>

<div class="kinboni-admin-wrap" id="kinboni-overview-app">
    
    <!-- Top Header Bar -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Overview</span></h1>
                <p class="kinboni-subtitle">Bespoke eCommerce Command Center &bull; Real-time Operations</p>
            </div>
        </div>
        
        <div class="kinboni-header-right">
            <!-- Range Selector Tabs -->
            <div class="kinboni-range-pills" id="overview-range-selector">
                <button type="button" class="kinboni-pill <?php echo $current_range === 'today' ? 'is-active' : ''; ?>" data-range="today">Today</button>
                <button type="button" class="kinboni-pill <?php echo $current_range === '7d' ? 'is-active' : ''; ?>" data-range="7d">Last 7 Days</button>
                <button type="button" class="kinboni-pill <?php echo $current_range === '30d' ? 'is-active' : ''; ?>" data-range="30d">Last 30 Days</button>
                <button type="button" class="kinboni-pill <?php echo $current_range === '12m' ? 'is-active' : ''; ?>" data-range="12m">Last 12 Months</button>
            </div>

            <!-- Quick Refresh -->
            <button type="button" class="kinboni-btn-refresh" id="btn-refresh-overview" title="Refresh Live Data">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Row 1: Clickable Order Status Cards -->
    <section class="kinboni-status-grid">
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=pending')); ?>" class="kinboni-status-card status-pending" title="View Pending Orders">
            <div class="status-icon"><span class="dashicons dashicons-clock"></span></div>
            <div class="status-info">
                <span class="status-label">Pending</span>
                <span class="status-val" id="stat-pending"><?php echo esc_html($orders['pending'] ?? 0); ?></span>
            </div>
        </a>

        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=processing')); ?>" class="kinboni-status-card status-processing" title="View Processing Orders">
            <div class="status-icon"><span class="dashicons dashicons-marker"></span></div>
            <div class="status-info">
                <span class="status-label">Processing</span>
                <span class="status-val" id="stat-processing"><?php echo esc_html($orders['processing'] ?? 0); ?></span>
            </div>
        </a>

        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=on-hold')); ?>" class="kinboni-status-card status-on-hold" title="View On-Hold Orders">
            <div class="status-icon"><span class="dashicons dashicons-controls-pause"></span></div>
            <div class="status-info">
                <span class="status-label">On Hold</span>
                <span class="status-val" id="stat-on-hold"><?php echo esc_html($orders['on-hold'] ?? 0); ?></span>
            </div>
        </a>

        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=completed')); ?>" class="kinboni-status-card status-completed" title="View Completed Orders">
            <div class="status-icon"><span class="dashicons dashicons-yes-alt"></span></div>
            <div class="status-info">
                <span class="status-label">Completed</span>
                <span class="status-val" id="stat-completed"><?php echo esc_html($orders['completed'] ?? 0); ?></span>
            </div>
        </a>

        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=cancelled')); ?>" class="kinboni-status-card status-cancelled" title="View Cancelled Orders">
            <div class="status-icon"><span class="dashicons dashicons-dismiss"></span></div>
            <div class="status-info">
                <span class="status-label">Cancelled</span>
                <span class="status-val" id="stat-cancelled"><?php echo esc_html($orders['cancelled'] ?? 0); ?></span>
            </div>
        </a>

        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=refunded')); ?>" class="kinboni-status-card status-refunded" title="View Refunded Orders">
            <div class="status-icon"><span class="dashicons dashicons-undo"></span></div>
            <div class="status-info">
                <span class="status-label">Refunded</span>
                <span class="status-val" id="stat-refunded"><?php echo esc_html($orders['refunded'] ?? 0); ?></span>
            </div>
        </a>
    </section>

    <!-- Row 2: Sales & Revenue Key Metric Cards with Previous Period Comparison -->
    <section class="kinboni-metrics-grid">
        <!-- Total Revenue -->
        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Total Revenue</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-money-alt"></span></span>
            </div>
            <div class="metric-body">
                <h2 class="metric-number" id="metric-revenue"><?php echo esc_html($c . number_format($sales['total_revenue'], 2)); ?></h2>
                <div class="metric-comparison">
                    <?php $rev_up = $sales['rev_change_pct'] >= 0; ?>
                    <span class="badge-trend <?php echo $rev_up ? 'trend-up' : 'trend-down'; ?>">
                        <?php echo $rev_up ? '↑ +' : '↓ '; ?><?php echo esc_html($sales['rev_change_pct']); ?>%
                    </span>
                    <span class="comparison-note">vs prior period (<?php echo esc_html($c . number_format($sales['total_revenue_prev'], 2)); ?>)</span>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Total Orders</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-cart"></span></span>
            </div>
            <div class="metric-body">
                <h2 class="metric-number" id="metric-orders"><?php echo esc_html(number_format($sales['total_orders'])); ?></h2>
                <div class="metric-comparison">
                    <?php $ord_up = $sales['orders_change_pct'] >= 0; ?>
                    <span class="badge-trend <?php echo $ord_up ? 'trend-up' : 'trend-down'; ?>">
                        <?php echo $ord_up ? '↑ +' : '↓ '; ?><?php echo esc_html($sales['orders_change_pct']); ?>%
                    </span>
                    <span class="comparison-note">vs prior period (<?php echo esc_html($sales['total_orders_prev']); ?> orders)</span>
                </div>
            </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Average Order Value</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-chart-line"></span></span>
            </div>
            <div class="metric-body">
                <h2 class="metric-number" id="metric-aov"><?php echo esc_html($c . number_format($sales['aov'], 2)); ?></h2>
                <div class="metric-comparison">
                    <?php $aov_up = $sales['aov_change_pct'] >= 0; ?>
                    <span class="badge-trend <?php echo $aov_up ? 'trend-up' : 'trend-down'; ?>">
                        <?php echo $aov_up ? '↑ +' : '↓ '; ?><?php echo esc_html($sales['aov_change_pct']); ?>%
                    </span>
                    <span class="comparison-note">vs prior (<?php echo esc_html($c . number_format($sales['aov_prev'], 2)); ?>)</span>
                </div>
            </div>
        </div>

        <!-- Real Unique Visitors -->
        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Unique Visitors</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-groups"></span></span>
            </div>
            <div class="metric-body">
                <h2 class="metric-number" id="metric-visitors"><?php echo esc_html(number_format($visitors['unique_visitors'])); ?></h2>
                <div class="metric-comparison">
                    <span class="comparison-note">Total Page Views: <strong id="metric-views"><?php echo esc_html(number_format($visitors['total_views'])); ?></strong></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Row 3: Sales Chart + Today's Operational Tasks -->
    <section class="kinboni-chart-row">
        <!-- Sales & Visitor Analytics Chart -->
        <div class="kinboni-panel chart-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Sales & Revenue Progression</h3>
                    <p class="panel-desc">Dynamic trend based on confirmed orders across Bangladesh</p>
                </div>
                <div class="chart-toggle-tabs">
                    <button type="button" class="chart-tab is-active" data-view="revenue">Revenue (<?php echo esc_html($c); ?>)</button>
                    <button type="button" class="chart-tab" data-view="orders">Orders Count</button>
                    <button type="button" class="chart-tab" data-view="visitors">Visitors</button>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-canvas-wrapper" style="position: relative; height: 320px; width: 100%;">
                    <canvas id="kinboniSalesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Today's Tasks & Urgent Action Box -->
        <div class="kinboni-panel tasks-panel">
            <div class="panel-header">
                <h3 class="panel-title">Today's Tasks</h3>
                <span class="badge-gold">Live Priorities</span>
            </div>
            <div class="panel-body">
                <ul class="kinboni-task-list">
                    <li class="task-item <?php echo $tasks['pending_orders'] > 0 ? 'is-urgent' : ''; ?>">
                        <div class="task-icon"><span class="dashicons dashicons-warning"></span></div>
                        <div class="task-text">
                            <h4>Pending Orders</h4>
                            <p><?php echo $tasks['pending_orders'] > 0 ? esc_html($tasks['pending_orders']) . ' order(s) awaiting verification & dispatch' : 'All orders up to date'; ?></p>
                        </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders&status=pending')); ?>" class="task-btn">
                            Review (<?php echo esc_html($tasks['pending_orders']); ?>)
                        </a>
                    </li>

                    <li class="task-item <?php echo $tasks['out_of_stock_count'] > 0 ? 'is-urgent' : ''; ?>">
                        <div class="task-icon"><span class="dashicons dashicons-archive"></span></div>
                        <div class="task-text">
                            <h4>Out-of-Stock Items</h4>
                            <p><?php echo $tasks['out_of_stock_count'] > 0 ? esc_html($tasks['out_of_stock_count']) . ' creation(s) unavailable for purchase' : 'Stock levels healthy'; ?></p>
                        </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products&stock_status=outofstock')); ?>" class="task-btn">
                            Replenish (<?php echo esc_html($tasks['out_of_stock_count']); ?>)
                        </a>
                    </li>

                    <li class="task-item">
                        <div class="task-icon"><span class="dashicons dashicons-email-alt"></span></div>
                        <div class="task-text">
                            <h4>New VIP Subscribers</h4>
                            <p><?php echo esc_html($tasks['new_subscribers_today']); ?> new connoisseur(s) joined today</p>
                        </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-marketing&tab=subscribers')); ?>" class="task-btn">
                            View
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Row 4: Inventory Alerts (Low Stock & Out of Stock) -->
    <section class="kinboni-split-row">
        <!-- Low Stock Alert Box -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Low Stock Alert (≤ <?php echo esc_html($inv['low_stock_threshold']); ?> units)</h3>
                    <p class="panel-desc"><?php echo esc_html($inv['low_stock_count']); ?> item(s) nearing depletion</p>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products&stock_status=low')); ?>" class="panel-link">Manage Stock →</a>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($inv['low_stock_list'])) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock Left</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inv['low_stock_list'] as $item) : ?>
                                <tr>
                                    <td class="font-bold text-dark"><?php echo esc_html($item['name']); ?></td>
                                    <td><code><?php echo esc_html($item['sku']); ?></code></td>
                                    <td><?php echo wp_kses_post($item['price']); ?></td>
                                    <td><span class="badge-stock-low"><?php echo esc_html($item['stock']); ?> left</span></td>
                                    <td><a href="<?php echo esc_url($item['edit_url']); ?>" class="btn-table-action" target="_blank">Edit</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-yes"></span>
                        <p>No products are currently in low-stock status.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Out of Stock Alert Box -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Out-of-Stock Alert</h3>
                    <p class="panel-desc"><?php echo esc_html($inv['out_of_stock_count']); ?> item(s) sold out</p>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products&stock_status=outofstock')); ?>" class="panel-link">View All →</a>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($inv['out_of_stock_list'])) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inv['out_of_stock_list'] as $item) : ?>
                                <tr>
                                    <td class="font-bold text-dark"><?php echo esc_html($item['name']); ?></td>
                                    <td><code><?php echo esc_html($item['sku']); ?></code></td>
                                    <td><?php echo wp_kses_post($item['price']); ?></td>
                                    <td><span class="badge-stock-out">Depleted</span></td>
                                    <td><a href="<?php echo esc_url($item['edit_url']); ?>" class="btn-table-action" target="_blank">Restock</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-yes"></span>
                        <p>All catalog creations are currently in stock.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Row 5: Recent 10 Orders + Bestsellers -->
    <section class="kinboni-split-row">
        <!-- Recent 10 Orders Table -->
        <div class="kinboni-panel lg-flex-7">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Recent Orders</h3>
                    <p class="panel-desc">Latest patron acquisitions requiring processing</p>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-orders')); ?>" class="panel-link">All Orders (<?php echo esc_html($sales['total_orders']); ?>) →</a>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($recent)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Patron</th>
                                <th>Destination</th>
                                <th>Total</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $o) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url($o['edit_url']); ?>" class="order-link font-bold">
                                            #<?php echo esc_html($o['order_number']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($o['customer']); ?></strong>
                                        <div class="text-subtle"><?php echo esc_html($o['phone']); ?></div>
                                    </td>
                                    <td><?php echo esc_html($o['city']); ?></td>
                                    <td class="font-bold"><?php echo wp_kses_post($o['total']); ?></td>
                                    <td><span class="badge-cod"><?php echo esc_html($o['payment']); ?></span></td>
                                    <td><span class="badge-status status-<?php echo esc_attr($o['status']); ?>"><?php echo esc_html($o['status_name']); ?></span></td>
                                    <td class="text-subtle text-xs"><?php echo esc_html($o['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-cart"></span>
                        <p>No orders placed yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Best-selling Products & Categories -->
        <div class="kinboni-panel lg-flex-5">
            <div class="panel-header">
                <h3 class="panel-title">Bestsellers & Popular Curations</h3>
                <span class="badge-gold">Top Performers</span>
            </div>
            <div class="panel-body">
                <div class="bestseller-list">
                    <?php if (!empty($bestsellers)) : ?>
                        <?php foreach ($bestsellers as $p) : ?>
                            <div class="bestseller-item">
                                <img src="<?php echo esc_url($p['image']); ?>" alt="<?php echo esc_attr($p['name']); ?>" class="bestseller-img" />
                                <div class="bestseller-meta">
                                    <a href="<?php echo esc_url($p['url']); ?>" target="_blank" class="bestseller-name"><?php echo esc_html($p['name']); ?></a>
                                    <div class="bestseller-sub">
                                        <span><?php echo wp_kses_post($p['price']); ?></span> &bull; 
                                        <strong><?php echo esc_html($p['total_sales']); ?> sold</strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-subtle text-center py-4">No product sales recorded yet.</p>
                    <?php endif; ?>
                </div>

                <div class="top-categories-box">
                    <h4 class="subhead-gold">Leading Categories</h4>
                    <div class="category-pills">
                        <?php foreach ($categories as $cat) : ?>
                            <span class="category-pill">
                                <?php echo esc_html($cat['name']); ?> 
                                <span class="pill-count"><?php echo esc_html($cat['count']); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- Pass initial data to JavaScript for chart rendering -->
<script type="text/javascript">
    window.kinboniOverviewInit = {
        labels: <?php echo json_encode($sales['chart_labels']); ?>,
        revenues: <?php echo json_encode($sales['chart_revenues']); ?>,
        orders: <?php echo json_encode($sales['chart_orders']); ?>,
        visitorLabels: <?php echo json_encode($visitors['chart_labels']); ?>,
        visitorViews: <?php echo json_encode($visitors['chart_views']); ?>,
        currency: <?php echo json_encode($c); ?>
    };
</script>

