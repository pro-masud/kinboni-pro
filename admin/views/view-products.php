<?php
/**
 * Kinboni Pro Admin View: Products & Inventory Management
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var array $products_data
 * @var array $categories
 * @var array $counts
 */

if (!defined('ABSPATH')) {
    exit;
}

$products     = $products_data['products'] ?? array();
$total        = $products_data['total'] ?? 0;
$max_pages    = $products_data['max_pages'] ?? 1;
$current_page = $products_data['current_page'] ?? 1;

$current_cat   = isset($_GET['cat']) ? sanitize_text_field(wp_unslash($_GET['cat'])) : '';
$current_stock = isset($_GET['stock_status']) ? sanitize_key(wp_unslash($_GET['stock_status'])) : '';
$search_term   = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

$c = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';

$export_url = wp_nonce_url(add_query_arg(array(
    'page'   => 'kinboni-products',
    'action' => 'export_csv',
), admin_url('admin.php')), 'kinboni_products_export', '_kb_nonce');
?>

<div class="kinboni-admin-wrap" id="kinboni-products-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>Products & Inventory</span></h1>
                <p class="kinboni-subtitle">Direct Atelier Publishing & Inventory Engine &bull; <?php echo esc_html($total); ?> Catalog Item(s)</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <!-- Upload New Creation Button -->
            <button type="button" class="btn-action-gold" id="btn-open-create-product">
                <span class="dashicons dashicons-plus-alt2"></span> Upload New Product
            </button>

            <!-- CSV Import & Export -->
            <button type="button" class="btn-outline-gold" id="btn-open-import-modal">
                <span class="dashicons dashicons-upload"></span> Import CSV
            </button>
            <a href="<?php echo esc_url($export_url); ?>" class="btn-outline-gold" title="Export Product Catalog to CSV">
                <span class="dashicons dashicons-download"></span> Export CSV
            </a>
            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh Products">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Metric Strip -->
    <section class="kinboni-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">Total Catalog</span>
                <span class="metric-icon-gold"><span class="dashicons dashicons-archive"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0;"><?php echo esc_html($counts['total']); ?></h3>
        </div>

        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">In Stock</span>
                <span class="metric-icon-gold" style="color:#059669;background:#ECFDF5;"><span class="dashicons dashicons-yes-alt"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#059669;"><?php echo esc_html($counts['instock']); ?></h3>
        </div>

        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">Low Stock Alert</span>
                <span class="metric-icon-gold" style="color:#D97706;background:#FEF3C7;"><span class="dashicons dashicons-warning"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#D97706;"><?php echo esc_html($counts['lowstock']); ?></h3>
        </div>

        <div class="kinboni-metric-card" style="padding: 16px 20px;">
            <div class="metric-head" style="margin-bottom: 6px;">
                <span class="metric-title">Out of Stock</span>
                <span class="metric-icon-gold" style="color:#DC2626;background:#FEF2F2;"><span class="dashicons dashicons-dismiss"></span></span>
            </div>
            <h3 class="metric-number" style="font-size: 22px; margin: 0; color:#DC2626;"><?php echo esc_html($counts['outofstock']); ?></h3>
        </div>
    </section>

    <!-- Sub-Navigation Stock Status Tabs -->
    <div class="kinboni-tabs-bar">
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products')); ?>" class="tab-pill <?php echo empty($current_stock) ? 'is-active' : ''; ?>">
            All Creations (<?php echo esc_html($counts['total']); ?>)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products&stock_status=instock')); ?>" class="tab-pill <?php echo $current_stock === 'instock' ? 'is-active' : ''; ?>">
            In Stock (<?php echo esc_html($counts['instock']); ?>)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products&stock_status=low')); ?>" class="tab-pill <?php echo $current_stock === 'low' ? 'is-active' : ''; ?>">
            ⚠️ Low Stock Alert (<?php echo esc_html($counts['lowstock']); ?>)
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products&stock_status=outofstock')); ?>" class="tab-pill <?php echo $current_stock === 'outofstock' ? 'is-active' : ''; ?>">
            Out of Stock (<?php echo esc_html($counts['outofstock']); ?>)
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="kinboni-toolbar-panel">
        <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="kinboni-filter-form">
            <input type="hidden" name="page" value="kinboni-products" />
            <?php if (!empty($current_stock)) : ?>
                <input type="hidden" name="stock_status" value="<?php echo esc_attr($current_stock); ?>" />
            <?php endif; ?>

            <!-- Search Field -->
            <div class="filter-group flex-search">
                <span class="dashicons dashicons-search icon-search"></span>
                <input 
                    type="text" 
                    name="s" 
                    value="<?php echo esc_attr($search_term); ?>" 
                    placeholder="Search creation by title or SKU..." 
                    class="input-search"
                />
            </div>

            <!-- Category Filter Dropdown -->
            <div class="filter-group">
                <select name="cat" class="select-bulk">
                    <option value="">All Categories</option>
                    <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($current_cat, $cat->slug); ?>>
                                <?php echo esc_html($cat->name); ?> (<?php echo esc_html($cat->count); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn-filter-apply">Filter</button>
            <?php if (!empty($search_term) || !empty($current_cat) || !empty($current_stock)) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=kinboni-products')); ?>" class="btn-filter-reset">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Bulk Action Controls -->
        <div class="bulk-actions-wrapper">
            <select id="bulk-product-action-select" class="select-bulk">
                <option value="">Bulk Operations</option>
                <option value="status_publish">Set Status: Published</option>
                <option value="status_draft">Set Status: Draft</option>
                <option value="price_adjust_pct">Adjust Regular Price (+/- %)</option>
                <option value="stock_set">Set Stock Quantity</option>
                <option value="status_trash">Move to Trash</option>
            </select>
            <input type="text" id="bulk-product-val" placeholder="Value (+10, 50)" class="input-search" style="width: 110px; display: none;" />
            <button type="button" id="btn-apply-bulk-product-action" class="btn-bulk-apply">Apply</button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="kinboni-panel" style="margin-top: 16px;">
        <div class="panel-body p-0">
            <?php if (!empty($products)) : ?>
                <table class="kinboni-table kinboni-products-table">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="check-all-products" /></th>
                            <th width="50">Image</th>
                            <th>Creation / Title</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th width="150">Price (<?php echo esc_html($c); ?>)</th>
                            <th width="130">Cost / Margin</th>
                            <th width="140">Stock Controls</th>
                            <th width="100">Performance</th>
                            <th width="120" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="kinboni-products-sortable">
                        <?php foreach ($products as $p) : ?>
                            <tr id="product-row-<?php echo esc_attr($p['id']); ?>" data-product-id="<?php echo esc_attr($p['id']); ?>">
                                <td>
                                    <input type="checkbox" class="product-cb" value="<?php echo esc_attr($p['id']); ?>" />
                                </td>
                                <td>
                                    <img src="<?php echo esc_url($p['image']); ?>" alt="<?php echo esc_attr($p['name']); ?>" class="bestseller-img" style="width: 40px; height: 40px;" />
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($p['view_url']); ?>" target="_blank" class="font-bold text-dark order-link">
                                        <?php echo esc_html($p['name']); ?>
                                    </a>
                                    <?php if ($p['status'] === 'draft') : ?>
                                        <span class="badge-status status-cancelled" style="margin-left: 4px;">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?php echo esc_html($p['sku']); ?></code>
                                </td>
                                <td>
                                    <span class="text-subtle text-xs"><?php echo esc_html($p['categories']); ?></span>
                                </td>
                                <td>
                                    <!-- In-line Quick Price Editor -->
                                    <div class="inline-price-box">
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            class="input-inline-price" 
                                            data-product-id="<?php echo esc_attr($p['id']); ?>" 
                                            value="<?php echo esc_attr($p['regular_price']); ?>" 
                                            title="Click and edit regular price, press Enter to save"
                                        />
                                        <?php if (!empty($p['sale_price'])) : ?>
                                            <div class="text-xs font-bold" style="color: #059669;">Sale: <?php echo esc_html($c . $p['sale_price']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($p['cost_price'] > 0) : ?>
                                        <div class="text-xs">Cost: <strong><?php echo esc_html($c . $p['cost_price']); ?></strong></div>
                                        <div class="text-xs font-bold" style="color: #059669;">+<?php echo esc_html($c . $p['profit']); ?> (<?php echo esc_html($p['margin_pct']); ?>%)</div>
                                    <?php else : ?>
                                        <span class="text-subtle text-xs">No cost set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- In-line Quick Stock Editor -->
                                    <div class="inline-stock-box">
                                        <input 
                                            type="number" 
                                            class="input-inline-stock" 
                                            data-product-id="<?php echo esc_attr($p['id']); ?>" 
                                            value="<?php echo is_numeric($p['stock_qty']) ? esc_attr($p['stock_qty']) : 0; ?>" 
                                            min="0"
                                            title="Edit stock quantity directly"
                                        />
                                        <span class="badge-stock-<?php echo ($p['stock_status'] === 'outofstock') ? 'out' : ($p['is_low'] ? 'low' : 'ok'); ?>">
                                            <?php echo ($p['stock_status'] === 'outofstock') ? 'Out of Stock' : ($p['is_low'] ? 'Low Stock' : 'In Stock'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs" title="Views recorded by Kinboni Tracker">
                                        👁️ <?php echo esc_html($p['views_count']); ?> views
                                    </div>
                                    <div class="text-xs font-bold" title="Total completed orders">
                                        🛒 <?php echo esc_html($p['total_sales']); ?> sold
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="order-action-buttons">
                                        <!-- Edit Modal Button -->
                                        <button type="button" class="btn-edit-product btn-icon-action" data-product-id="<?php echo esc_attr($p['id']); ?>" title="Edit Creation">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>

                                        <!-- Duplicate Button -->
                                        <button type="button" class="btn-duplicate-product btn-icon-action" data-product-id="<?php echo esc_attr($p['id']); ?>" title="One-Click Duplicate / Clone">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </button>

                                        <!-- View on Storefront -->
                                        <a href="<?php echo esc_url($p['view_url']); ?>" target="_blank" class="btn-icon-action" title="View Creation Live">
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
                    <span class="dashicons dashicons-archive"></span>
                    <h3>No Creations Found</h3>
                    <p>Click "Upload New Product" to publish your first creation to the catalog.</p>
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

    <!-- DIRECT PRODUCT UPLOAD & EDIT SLIDE-OVER MODAL -->
    <div id="kinboni-product-modal" class="kinboni-modal-overlay hidden">
        <div class="kinboni-modal-backdrop"></div>
        <div class="kinboni-slideover-panel" style="max-width: 680px;">
            <div class="slideover-header">
                <div>
                    <h3 id="modal-product-title" class="slideover-title">Upload New Creation</h3>
                    <p class="panel-desc">Direct Publishing Engine &bull; Zero WooCommerce Complication</p>
                </div>
                <button type="button" id="btn-close-product-modal" class="btn-close-modal">✕</button>
            </div>

            <form id="kinboni-product-form" class="slideover-body">
                <input type="hidden" id="prod-form-id" name="product_id" value="0" />

                <!-- 1. General Info -->
                <div class="form-section">
                    <label class="form-label font-bold">Creation Title *</label>
                    <input type="text" id="prod-form-title" name="title" placeholder="e.g. Chronos Royale Automatic Timepiece" class="form-control" required />
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label font-bold">SKU (Inventory Identifier)</label>
                        <input type="text" id="prod-form-sku" name="sku" placeholder="KB-CHRONOS-01" class="form-control" />
                    </div>
                    <div>
                        <label class="form-label font-bold">Publication Status</label>
                        <select id="prod-form-status" name="post_status" class="form-control">
                            <option value="publish">Published (Live on Store)</option>
                            <option value="draft">Draft (Private)</option>
                        </select>
                    </div>
                </div>

                <!-- Product Type & Variations Selector -->
                <div class="form-section">
                    <label class="form-label font-bold">Creation Classification</label>
                    <select id="prod-form-type" name="product_type" class="form-control" style="font-weight:bold;">
                        <option value="simple">Simple Creation (Standard Item)</option>
                        <option value="variable">Variable Creation (Multiple Sizes / Attributes)</option>
                    </select>
                    <input type="hidden" id="prod-form-is-variable" name="is_variable" value="0" />
                    <input type="hidden" id="prod-form-variations-data" name="variations_data" value="" />
                </div>

                <!-- Dynamic Variations Builder Box (Shown when Variable is selected) -->
                <div id="prod-form-variations-box" style="display:none; background:#FAF9F6; border:1px solid #D4AF37; border-radius:6px; padding:14px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <label class="form-label font-bold text-dark" style="margin:0;">Atelier Variations Matrix (Sizes / Options)</label>
                        <span class="text-xs text-subtle">Set individual price & stock for each size</span>
                    </div>
                    <div style="display:flex; gap:10px; margin-bottom:12px;">
                        <input type="text" id="input-new-var-tag" placeholder="Type size or option (e.g. S, M, L, XL) and click Add" class="form-control" />
                        <button type="button" id="btn-add-var-row" class="btn-action-gold" style="white-space:nowrap; padding:6px 14px;">+ Add Variation</button>
                    </div>
                    <div id="variations-table-wrap">
                        <table class="kinboni-table" style="background:#FFF; font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Option</th>
                                    <th>Specific Price (৳)</th>
                                    <th>Stock</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody id="variations-rows-body">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Pricing & Cost -->
                <div class="form-grid-3">
                    <div>
                        <label class="form-label font-bold">Regular Price (<?php echo esc_html($c); ?>) *</label>
                        <input type="number" step="0.01" id="prod-form-reg-price" name="regular_price" placeholder="18500" class="form-control" required />
                    </div>
                    <div>
                        <label class="form-label font-bold">Privilege Sale Price (<?php echo esc_html($c); ?>)</label>
                        <input type="number" step="0.01" id="prod-form-sale-price" name="sale_price" placeholder="16200" class="form-control" />
                    </div>
                    <div>
                        <label class="form-label font-bold" title="Purchase/production cost for Profit & Loss Reports">Cost Price (<?php echo esc_html($c); ?>)</label>
                        <input type="number" step="0.01" id="prod-form-cost-price" name="cost_price" placeholder="8500" class="form-control" />
                    </div>
                </div>

                <!-- Sale Scheduling -->
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Sale Start Date</label>
                        <input type="date" id="prod-form-sale-from" name="sale_date_from" class="form-control" />
                    </div>
                    <div>
                        <label class="form-label">Sale End Date</label>
                        <input type="date" id="prod-form-sale-to" name="sale_date_to" class="form-control" />
                    </div>
                </div>

                <!-- 3. Inventory Stock -->
                <div class="form-grid-2">
                    <div>
                        <label class="form-label font-bold">Stock Quantity</label>
                        <input type="number" id="prod-form-stock-qty" name="stock_qty" placeholder="15" class="form-control" min="0" />
                    </div>
                    <div>
                        <label class="form-label font-bold">Stock Status</label>
                        <select id="prod-form-stock-st" name="stock_status" class="form-control">
                            <option value="instock">In Stock</option>
                            <option value="outofstock">Out of Stock</option>
                        </select>
                    </div>
                </div>

                <!-- 4. Media & Categories -->
                <div class="form-grid-2">
                    <!-- Featured Image Uploader -->
                    <div>
                        <label class="form-label font-bold">Featured Artwork / Photo</label>
                        <div class="image-upload-wrapper">
                            <input type="hidden" id="prod-form-img-id" name="image_id" value="0" />
                            <img id="prod-form-img-preview" src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Preview" class="img-preview-box" />
                            <div class="mt-2">
                                <button type="button" id="btn-select-prod-img" class="btn-outline-gold" style="padding: 4px 10px; font-size: 11px;">
                                    Select / Upload Media
                                </button>
                                <button type="button" id="btn-remove-prod-img" class="text-subtle text-xs" style="margin-left: 6px; display: none;">Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Selector -->
                    <div>
                        <label class="form-label font-bold">Collections / Categories</label>
                        <div class="categories-checklist-box">
                            <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                                <?php foreach ($categories as $cat) : ?>
                                    <label class="cat-check-item">
                                        <input type="checkbox" name="categories[]" value="<?php echo esc_attr($cat->term_id); ?>" class="prod-cat-cb" />
                                        <span><?php echo esc_html($cat->name); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 5. Editorial Descriptions -->
                <div class="form-section">
                    <label class="form-label font-bold">Short Editorial Excerpt</label>
                    <textarea id="prod-form-short-desc" name="short_description" rows="2" class="form-control" placeholder="Brief luxury summary for product card..."></textarea>
                </div>

                <div class="form-section">
                    <label class="form-label font-bold">Atelier Specification & Description</label>
                    <textarea id="prod-form-desc" name="description" rows="4" class="form-control" placeholder="Detailed craftsmanship, materials, dimensions, and heritage note..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="slideover-footer" style="margin-top: 16px;">
                    <button type="submit" id="btn-save-product-submit" class="btn-action-gold" style="width: 100%; justify-content: center; padding: 12px;">
                        Save & Publish Creation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CSV IMPORT MODAL -->
    <div id="kinboni-import-modal" class="kinboni-modal-overlay hidden">
        <div class="kinboni-modal-backdrop"></div>
        <div class="kinboni-slideover-panel" style="max-width: 480px; height: auto; max-height: 90vh; margin: auto; border-radius: 16px; inset: 0;">
            <div class="slideover-header">
                <h3 class="slideover-title">Import Catalog CSV</h3>
                <button type="button" id="btn-close-import-modal" class="btn-close-modal">✕</button>
            </div>
            <form method="POST" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin.php?page=kinboni-products')); ?>" class="slideover-body">
                <?php wp_nonce_field('kinboni_import_products_action', 'kinboni_import_products_nonce'); ?>
                <p class="text-subtle text-xs" style="margin-bottom: 16px;">
                    Upload a CSV file containing your product catalog. Columns: <code>Title, SKU, Regular Price, Sale Price, Cost Price, Stock</code>.
                </p>
                <div style="border: 2px dashed var(--kb-border); border-radius: 10px; padding: 24px; text-align: center; margin-bottom: 16px;">
                    <input type="file" name="csv_file" accept=".csv" required />
                </div>
                <button type="submit" class="btn-action-gold" style="width: 100%; justify-content: center;">
                    Upload & Import Products
                </button>
            </form>
        </div>
    </div>

</div>

