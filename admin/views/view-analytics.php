<?php
/**
 * Kinboni Pro Admin View: Live Analytics & Traffic Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $analytics_data
 */

if (!defined('ABSPATH')) {
    exit;
}

$bounds       = $analytics_data['bounds'] ?? array();
$metrics      = $analytics_data['metrics'] ?? array();
$chart_data   = $analytics_data['chart_data'] ?? array();
$sources      = $analytics_data['traffic_sources'] ?? array();
$devices      = $analytics_data['device_stats'] ?? array();
$device_pct   = $analytics_data['device_pct'] ?? array();
$top_pages    = $analytics_data['top_pages'] ?? array();
$top_products = $analytics_data['top_products'] ?? array();
$top_searches = $analytics_data['top_searches'] ?? array();
$zero_searches= $analytics_data['zero_searches'] ?? array();
$wishlist     = $analytics_data['wishlist_items'] ?? array();

$current_range = $bounds['type'] ?? '30d';
$export_url = wp_nonce_url(add_query_arg(array(
    'page'   => 'kinboni-analytics',
    'action' => 'export_analytics',
    'range'  => $current_range,
), admin_url('admin.php')), 'kinboni_analytics_export', '_kb_nonce');
?>

<div class="kinboni-admin-wrap" id="kinboni-analytics-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Analytics & Traffic</span></h1>
                <p class="kinboni-subtitle">Period: <strong><?php echo esc_html($bounds['label'] ?? 'Last 30 Days'); ?></strong> &bull; 100% Real Database Analytics</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <!-- Preset Date Range Pills -->
            <div class="kinboni-pills-group" id="analytics-range-selector">
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-analytics&range=today')); ?>" class="kinboni-pill <?php echo $current_range === 'today' ? 'is-active' : ''; ?>" data-range="today">Today</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-analytics&range=7d')); ?>" class="kinboni-pill <?php echo $current_range === '7d' ? 'is-active' : ''; ?>" data-range="7d">7D</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-analytics&range=30d')); ?>" class="kinboni-pill <?php echo $current_range === '30d' ? 'is-active' : ''; ?>" data-range="30d">30D</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-analytics&range=12m')); ?>" class="kinboni-pill <?php echo $current_range === '12m' ? 'is-active' : ''; ?>" data-range="12m">12M</a>
            </div>

            <!-- Export CSV -->
            <a href="<?php echo esc_url($export_url); ?>" class="btn-action-gold" title="Export Analytics to CSV">
                <span class="dashicons dashicons-download"></span> Export CSV
            </a>

            <!-- Refresh Button -->
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh Analytics">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Custom Date Filter Toolbar -->
    <div class="kinboni-toolbar-panel" style="margin-bottom: 20px;">
        <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="kinboni-filter-form">
            <input type="hidden" name="page" value="kinboni-analytics" />
            <span class="text-xs font-bold text-dark" style="margin-right: 8px;">Custom Date Range:</span>

            <div class="filter-group">
                <label class="text-xs text-subtle" style="margin-right: 4px;">From:</label>
                <input type="date" name="start_date" value="<?php echo esc_attr($_GET['start_date'] ?? ''); ?>" class="form-control" style="padding: 4px 10px; font-size: 13px;" required />
            </div>

            <div class="filter-group">
                <label class="text-xs text-subtle" style="margin-right: 4px;">To:</label>
                <input type="date" name="end_date" value="<?php echo esc_attr($_GET['end_date'] ?? ''); ?>" class="form-control" style="padding: 4px 10px; font-size: 13px;" required />
            </div>

            <button type="submit" class="btn-filter-apply">Filter Dates</button>
            <?php if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-analytics')); ?>" class="btn-filter-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Core Metrics Strip -->
    <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Unique Visitors</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-admin-users"></span></span>
            </div>
            <h3 class="metric-number"><?php echo esc_html(number_format($metrics['unique_visitors'])); ?></h3>
            <span class="metric-subtle">Anonymized Unique Patrons</span>
        </div>

        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Total Page Views</span>
                <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-visibility"></span></span>
            </div>
            <h3 class="metric-number" style="color:#059669;"><?php echo esc_html(number_format($metrics['page_views'])); ?></h3>
            <span class="metric-subtle">Authentic Browsing Interactions</span>
        </div>

        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Orders Completed</span>
                <span class="metric-icon-gold" style="color:#D4AF37;background:#FCF9EE;"><span class="dashicons dashicons-cart"></span></span>
            </div>
            <h3 class="metric-number" style="color:#B89628;"><?php echo esc_html(number_format($metrics['orders_count'])); ?></h3>
            <span class="metric-subtle">Transactions In This Window</span>
        </div>

        <div class="kinboni-metric-card">
            <div class="metric-head">
                <span class="metric-title">Conversion Rate</span>
                <span class="metric-icon-gold" style="color:#121214;background:#E8E6DF;"><span class="dashicons dashicons-chart-pie"></span></span>
            </div>
            <h3 class="metric-number"><?php echo esc_html($metrics['conversion_rate']); ?>%</h3>
            <span class="metric-subtle">Visitors to Completed Orders</span>
        </div>
    </section>

    <!-- Interactive Canvas Traffic Chart -->
    <div class="kinboni-panel" style="margin-bottom: 24px;">
        <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 class="panel-title">Traffic & Engagement Dynamics</h3>
                <p class="text-xs text-subtle" style="margin-top:2px;">Real-time visitor trends across <?php echo esc_html($bounds['label']); ?></p>
            </div>
            <div class="chart-toggle-tabs" id="analytics-chart-tabs">
                <button type="button" class="chart-tab is-active" data-view="visitors">Unique Visitors</button>
                <button type="button" class="chart-tab" data-view="pageviews">Page Views</button>
                <button type="button" class="chart-tab" data-view="conversions">Conversion Rate (%)</button>
            </div>
        </div>
        <div class="panel-body">
            <div style="position: relative; width: 100%; height: 280px;">
                <canvas id="kinboni-analytics-canvas" width="1000" height="280"></canvas>
            </div>
        </div>
    </div>

    <!-- Traffic Sources & Device Distribution Grid -->
    <div class="grid-2col" style="gap: 24px; margin-bottom: 24px;">

        <!-- Traffic Acquisition Sources -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Acquisition Channels & Referrers</h3>
            </div>
            <div class="panel-body">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php 
                    $channel_colors = array(
                        'Direct'   => '#121214',
                        'Google'   => '#4285F4',
                        'Facebook' => '#1877F2',
                        'Other'    => '#D4AF37',
                    );
                    foreach ($sources as $name => $s) : ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span class="font-bold text-dark text-sm"><?php echo esc_html($name); ?></span>
                                <span class="text-subtle text-xs"><?php echo esc_html($s['count']); ?> views (<?php echo esc_html($s['pct']); ?>%)</span>
                            </div>
                            <div style="width: 100%; height: 8px; background: #F3F4F6; border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo esc_attr($s['pct']); ?>%; background: <?php echo esc_attr($channel_colors[$name] ?? '#D4AF37'); ?>; border-radius: 4px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Device Breakdown -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Device Breakdown</h3>
            </div>
            <div class="panel-body">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center; margin-top: 10px;">
                    <div style="padding: 16px; background: #FAF9F6; border-radius: 8px; border: 1px solid #E8E6DF;">
                        <span class="dashicons dashicons-desktop" style="font-size: 28px; width: 28px; height: 28px; color: #121214; margin-bottom: 8px;"></span>
                        <div class="font-bold text-dark" style="font-size: 20px;"><?php echo esc_html($device_pct['desktop'] ?? 0); ?>%</div>
                        <div class="text-xs text-subtle mt-1">Desktop (<?php echo esc_html($devices['desktop'] ?? 0); ?>)</div>
                    </div>

                    <div style="padding: 16px; background: #FAF9F6; border-radius: 8px; border: 1px solid #E8E6DF;">
                        <span class="dashicons dashicons-smartphone" style="font-size: 28px; width: 28px; height: 28px; color: #D4AF37; margin-bottom: 8px;"></span>
                        <div class="font-bold text-dark" style="font-size: 20px; color: #B89628;"><?php echo esc_html($device_pct['mobile'] ?? 0); ?>%</div>
                        <div class="text-xs text-subtle mt-1">Mobile (<?php echo esc_html($devices['mobile'] ?? 0); ?>)</div>
                    </div>

                    <div style="padding: 16px; background: #FAF9F6; border-radius: 8px; border: 1px solid #E8E6DF;">
                        <span class="dashicons dashicons-tablet" style="font-size: 28px; width: 28px; height: 28px; color: #6B7280; margin-bottom: 8px;"></span>
                        <div class="font-bold text-dark" style="font-size: 20px;"><?php echo esc_html($device_pct['tablet'] ?? 0); ?>%</div>
                        <div class="text-xs text-subtle mt-1">Tablet (<?php echo esc_html($devices['tablet'] ?? 0); ?>)</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Most Viewed Pages & Products Grid -->
    <div class="grid-2col" style="gap: 24px; margin-bottom: 24px;">

        <!-- Most Viewed Pages -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Most Viewed Pages</h3>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($top_pages)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Page URL</th>
                                <th>Type</th>
                                <th width="80" class="text-right">Views</th>
                                <th width="80" class="text-right">Visitors</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_pages as $tp) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url($tp['url']); ?>" target="_blank" class="font-bold text-dark text-xs order-link" title="<?php echo esc_attr($tp['url']); ?>">
                                            <?php echo esc_html(wp_strip_all_tags(substr($tp['url'], 0, 45))); ?><?php if (strlen($tp['url']) > 45) echo '...'; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-status status-completed" style="font-size: 10px;">
                                            <?php echo esc_html(strtoupper($tp['page_type'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-right font-bold text-dark"><?php echo esc_html($tp['views']); ?></td>
                                    <td class="text-right text-subtle text-xs"><?php echo esc_html($tp['visitors']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-admin-page"></span>
                        <h3>No Page Views Yet</h3>
                        <p>Visits to store pages will be tracked silently and summarized here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Most Viewed Products -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Most Viewed Products</h3>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($top_products)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th width="80" class="text-right">Views</th>
                                <th width="80" class="text-right">Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products as $prod) : ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <img src="<?php echo esc_url($prod['thumbnail']); ?>" class="prod-thumb" alt="" />
                                            <div>
                                                <a href="<?php echo esc_url(get_permalink($prod['id'])); ?>" target="_blank" class="font-bold text-dark text-xs order-link">
                                                    <?php echo esc_html($prod['name']); ?>
                                                </a>
                                                <div class="text-subtle text-xs"><?php echo wp_kses_post($prod['price']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right font-bold text-dark"><?php echo esc_html($prod['views']); ?></td>
                                    <td class="text-right text-subtle text-xs"><?php echo esc_html($prod['total_sold']); ?> units</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-tag"></span>
                        <h3>No Product Views Recorded</h3>
                        <p>Customer views on single product pages will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Search Query Analytics & Wishlist Analytics Grid -->
    <div class="grid-2col" style="gap: 24px;">

        <!-- Search Analytics -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Customer Search Queries</h3>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($top_searches)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Keyword</th>
                                <th width="100" class="text-right">Searches</th>
                                <th width="120" class="text-right">Avg Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_searches as $srch) : ?>
                                <tr>
                                    <td>
                                        <span class="font-bold text-dark text-sm" style="background:#FAF9F6; padding:2px 8px; border-radius:4px; border:1px solid #E8E6DF;">
                                            <?php echo esc_html($srch['search_term']); ?>
                                        </span>
                                    </td>
                                    <td class="text-right font-bold text-dark"><?php echo esc_html($srch['count']); ?></td>
                                    <td class="text-right">
                                        <span class="badge-status <?php echo ($srch['avg_results'] > 0) ? 'status-completed' : 'status-cancelled'; ?>">
                                            <?php echo esc_html(round($srch['avg_results'], 1)); ?> found
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-search"></span>
                        <h3>No Search Queries Logged</h3>
                        <p>Search queries submitted by visitors in the live search bar will be recorded here.</p>
                    </div>
                <?php endif; ?>

                <!-- Zero-Result Searches Warning Box -->
                <?php if (!empty($zero_searches)) : ?>
                    <div style="margin: 16px; padding: 14px; background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 6px;">
                        <h4 style="margin:0 0 6px; font-size:13px; color:#DC2626; font-weight:700;">
                            ⚠️ Searches Returning No Results
                        </h4>
                        <p style="margin:0 0 8px; font-size:12px; color:#7F1D1D;">Shoppers searched for these items but found no matching products in your atelier:</p>
                        <div style="display:flex; flex-wrap:wrap; gap:6px;">
                            <?php foreach ($zero_searches as $zs) : ?>
                                <span style="background:#FFF; color:#B91C1C; border:1px solid #F87171; font-size:11px; font-weight:bold; padding:2px 6px; border-radius:4px;">
                                    <?php echo esc_html($zs['search_term']); ?> (<?php echo esc_html($zs['count']); ?>x)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Wishlist Analytics -->
        <div class="kinboni-panel">
            <div class="panel-header">
                <h3 class="panel-title">Most Coveted Products (Wishlist)</h3>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($wishlist)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th width="100" class="text-right">Wishlisted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wishlist as $w) : ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <img src="<?php echo esc_url($w['thumbnail']); ?>" class="prod-thumb" alt="" />
                                            <div>
                                                <a href="<?php echo esc_url(get_permalink($w['id'])); ?>" target="_blank" class="font-bold text-dark text-xs order-link">
                                                    <?php echo esc_html($w['name']); ?>
                                                </a>
                                                <div class="text-subtle text-xs"><?php echo wp_kses_post($w['price']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo ($w['stock_status'] === 'instock') ? 'status-completed' : 'status-cancelled'; ?>">
                                            <?php echo ($w['stock_status'] === 'instock') ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </td>
                                    <td class="text-right font-bold text-dark" style="color:#B89628;">
                                        ⭐ <?php echo esc_html($w['wishlist_count']); ?>x
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-heart"></span>
                        <h3>No Wishlist Records Yet</h3>
                        <p>When clients save products to their curated wishlist drawer, rankings will automatically appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Initial Chart Data for JS Canvas -->
<script>
window.kinboniAnalyticsInit = <?php echo wp_json_encode($chart_data); ?>;
</script>

