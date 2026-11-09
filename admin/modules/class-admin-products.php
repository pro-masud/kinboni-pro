<?php
/**
 * Kinboni Admin Module: Products & Inventory Management
 *
 * Full WooCommerce product management with direct product upload/creator,
 * inventory controls, inline price/stock updates, one-click product duplication,
 * bulk operations, CSV import/export, and views/sales analytics.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Products {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_save_product', array($this, 'ajax_save_product'));
        add_action('wp_ajax_kinboni_quick_update_stock', array($this, 'ajax_quick_update_stock'));
        add_action('wp_ajax_kinboni_quick_update_price', array($this, 'ajax_quick_update_price'));
        add_action('wp_ajax_kinboni_duplicate_product', array($this, 'ajax_duplicate_product'));
        add_action('wp_ajax_kinboni_bulk_product_action', array($this, 'ajax_bulk_product_action'));
        add_action('wp_ajax_kinboni_get_product_data', array($this, 'ajax_get_product_data'));
        add_action('wp_ajax_kinboni_update_product_order', array($this, 'ajax_update_product_order'));

        // CSV Export & Import Handler (via admin_init)
        add_action('admin_init', array($this, 'handle_export_and_import'));
    }

    /**
     * Handle CSV Export & Import via GET/POST
     */
    public function handle_export_and_import() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'kinboni-products') {
            return;
        }

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            return;
        }

        $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

        // CSV Export
        if ($action === 'export_csv') {
            check_admin_referer('kinboni_products_export', '_kb_nonce');
            $this->export_products_csv();
            exit;
        }

        // CSV Import
        if (isset($_POST['kinboni_import_products_nonce']) && wp_verify_nonce($_POST['kinboni_import_products_nonce'], 'kinboni_import_products_action')) {
            $this->import_products_csv();
        }
    }

    /**
     * Render the Products Management Admin Page
     */
    public function render() {
        $filter_cat    = isset($_GET['cat']) ? sanitize_text_field(wp_unslash($_GET['cat'])) : '';
        $filter_stock  = isset($_GET['stock_status']) ? sanitize_key(wp_unslash($_GET['stock_status'])) : '';
        $filter_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
        $search_term   = isset($_GET['s']) ? sanitize_text_field(trim(wp_unslash($_GET['s']))) : '';
        $paged         = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $per_page      = 20;

        $products_data = $this->query_products(array(
            'category'     => $filter_cat,
            'stock_status' => $filter_stock,
            'status'       => $filter_status,
            'search'       => $search_term,
            'paged'        => $paged,
            'per_page'     => $per_page,
        ));

        // Get all categories for filter dropdown
        $categories = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        ));

        // Inventory counts for metric strip
        $counts = $this->get_inventory_counts();

        // Include View
        $view_file = KINBONI_CORE_PATH . 'admin/views/view-products.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Products view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Query Products with Views, Purchase Stats & Cost Margins
     */
    public function query_products($args = array()) {
        $paged    = $args['paged'] ?? 1;
        $per_page = $args['per_page'] ?? 20;

        $query_args = array(
            'limit'    => $per_page,
            'page'     => $paged,
            'paginate' => true,
            'orderby'  => 'menu_order title',
            'order'    => 'ASC',
        );

        if (!empty($args['status'])) {
            $query_args['status'] = $args['status'];
        }

        if (!empty($args['category'])) {
            $query_args['category'] = array($args['category']);
        }

        if (!empty($args['search'])) {
            $query_args['s'] = $args['search'];
        }

        $threshold = (int) get_option('kinboni_stock_threshold', 5);

        if (!empty($args['stock_status'])) {
            if ($args['stock_status'] === 'low') {
                $query_args['stock_status'] = 'instock';
                $query_args['stock_quantity'] = array(1, $threshold);
            } else {
                $query_args['stock_status'] = $args['stock_status'];
            }
        }

        $results = wc_get_products($query_args);
        $products = $results->products ?? array();
        $total = $results->total ?? 0;
        $max_pages = $results->max_num_pages ?? 1;

        $list = array();
        foreach ($products as $p) {
            $pid = $p->get_id();
            $terms = get_the_terms($pid, 'product_cat');
            $cat_names = array();
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    $cat_names[] = $t->name;
                }
            }

            $img = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail');
            $cost_price = (float) get_post_meta($pid, '_kinboni_cost_price', true);
            $views_count = (int) get_post_meta($pid, '_kinboni_views_count', true);
            $total_sales = (int) get_post_meta($pid, 'total_sales', true);

            $regular_price = $p->get_regular_price();
            $sale_price = $p->get_sale_price();
            $active_price = (float) $p->get_price();

            // Profit margin calculation
            $profit = ($cost_price > 0 && $active_price > 0) ? ($active_price - $cost_price) : 0;
            $margin_pct = ($active_price > 0 && $profit > 0) ? round(($profit / $active_price) * 100, 1) : 0;

            // Stock details
            $manage_stock = $p->get_manage_stock();
            $stock_qty = $p->get_stock_quantity();
            $stock_status = $p->get_stock_status(); // 'instock', 'outofstock', 'onbackorder'
            $is_low = ($manage_stock && $stock_qty !== null && $stock_qty > 0 && $stock_qty <= $threshold);

            $list[] = array(
                'id'            => $pid,
                'name'          => $p->get_name(),
                'sku'           => $p->get_sku() ?: '—',
                'type'          => $p->get_type(),
                'status'        => $p->get_status(),
                'image'         => $img,
                'categories'    => implode(', ', $cat_names) ?: 'Uncategorized',
                'regular_price' => $regular_price,
                'sale_price'    => $sale_price,
                'active_price'  => $active_price,
                'cost_price'    => $cost_price,
                'profit'        => $profit,
                'margin_pct'    => $margin_pct,
                'manage_stock'  => $manage_stock,
                'stock_qty'     => $stock_qty !== null ? $stock_qty : '∞',
                'stock_status'  => $stock_status,
                'is_low'        => $is_low,
                'views_count'   => $views_count,
                'total_sales'   => $total_sales,
                'view_url'      => get_permalink($pid),
                'menu_order'    => $p->get_menu_order(),
            );
        }

        return array(
            'products'     => $list,
            'total'        => $total,
            'max_pages'    => $max_pages,
            'current_page' => $paged,
        );
    }

    /**
     * Get Quick Metric Strip Counts
     */
    public function get_inventory_counts() {
        $threshold = (int) get_option('kinboni_stock_threshold', 5);

        $total_q      = wc_get_products(array('return' => 'ids', 'limit' => 1, 'paginate' => true));
        $instock_q    = wc_get_products(array('stock_status' => 'instock', 'return' => 'ids', 'limit' => 1, 'paginate' => true));
        $outofstock_q = wc_get_products(array('stock_status' => 'outofstock', 'return' => 'ids', 'limit' => 1, 'paginate' => true));
        $lowstock_q   = wc_get_products(array('stock_status' => 'instock', 'manage_stock' => true, 'stock_quantity' => array(1, $threshold), 'return' => 'ids', 'limit' => 1, 'paginate' => true));

        return array(
            'total'      => (int) ($total_q->total ?? 0),
            'instock'    => (int) ($instock_q->total ?? 0),
            'outofstock' => (int) ($outofstock_q->total ?? 0),
            'lowstock'   => (int) ($lowstock_q->total ?? 0),
        );
    }

    /**
     * AJAX: Direct Product Upload / Add New & Edit Product
     */
    public function ajax_save_product() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $title      = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $sku        = isset($_POST['sku']) ? sanitize_text_field(wp_unslash($_POST['sku'])) : '';
        $reg_price  = isset($_POST['regular_price']) ? wc_format_decimal($_POST['regular_price']) : '';
        $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? wc_format_decimal($_POST['sale_price']) : '';
        $cost_price = isset($_POST['cost_price']) && $_POST['cost_price'] !== '' ? wc_format_decimal($_POST['cost_price']) : '';
        $sale_from  = isset($_POST['sale_date_from']) ? sanitize_text_field(wp_unslash($_POST['sale_date_from'])) : '';
        $sale_to    = isset($_POST['sale_date_to']) ? sanitize_text_field(wp_unslash($_POST['sale_date_to'])) : '';
        $stock_qty  = isset($_POST['stock_qty']) && $_POST['stock_qty'] !== '' ? absint(wp_unslash($_POST['stock_qty'])) : null;
        $stock_st   = isset($_POST['stock_status']) ? sanitize_key(wp_unslash($_POST['stock_status'])) : 'instock';
        $post_st    = isset($_POST['post_status']) ? sanitize_key(wp_unslash($_POST['post_status'])) : 'publish';
        $cats       = isset($_POST['categories']) ? array_map('absint', (array) $_POST['categories']) : array();
        $image_id   = isset($_POST['image_id']) ? absint(wp_unslash($_POST['image_id'])) : 0;
        $short_desc = isset($_POST['short_description']) ? wp_kses_post(wp_unslash($_POST['short_description'])) : '';
        $full_desc  = isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : '';
        $is_variable= !empty($_POST['is_variable']) ? true : false;
        $variations = isset($_POST['variations_data']) ? sanitize_text_field(wp_unslash($_POST['variations_data'])) : '';

        if (empty($title)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a creation/product title.', 'kinboni-core')));
        }

        try {
            if ($product_id > 0) {
                // Editing existing product
                $product = wc_get_product($product_id);
                if (!$product) {
                    wp_send_json_error(array('message' => esc_html__('Product not found.', 'kinboni-core')));
                }
                $is_new = false;
            } else {
                // Creating new product directly inside KINBONI Pro
                $product = $is_variable ? new WC_Product_Variable() : new WC_Product_Simple();
                $is_new = true;
            }

            $product->set_name($title);
            $product->set_status($post_st);
            $product->set_short_description($short_desc);
            $product->set_description($full_desc);

            if (!empty($sku)) {
                $product->set_sku($sku);
            }

            // Pricing
            if ($reg_price !== '') {
                $product->set_regular_price($reg_price);
            }
            if ($sale_price !== '') {
                $product->set_sale_price($sale_price);
            } else {
                $product->set_sale_price('');
            }

            // Scheduled Sale Pricing (using WooCommerce timezone-aware helper)
            if (!empty($sale_from)) {
                $from_ts = function_exists('wc_string_to_timestamp') ? wc_string_to_timestamp($sale_from . ' 00:00:00') : strtotime($sale_from . ' 00:00:00');
                $product->set_date_on_sale_from($from_ts);
            } else {
                $product->set_date_on_sale_from(null);
            }
            if (!empty($sale_to)) {
                $to_ts = function_exists('wc_string_to_timestamp') ? wc_string_to_timestamp($sale_to . ' 23:59:59') : strtotime($sale_to . ' 23:59:59');
                $product->set_date_on_sale_to($to_ts);
            } else {
                $product->set_date_on_sale_to(null);
            }

            // Inventory
            if ($stock_qty !== null) {
                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock_qty);
                $product->set_stock_status($stock_qty > 0 ? 'instock' : 'outofstock');
            } else {
                $product->set_manage_stock(false);
                $product->set_stock_status($stock_st);
            }

            // Image (support clearing image when image_id is 0 on edit)
            if ($image_id > 0) {
                $product->set_image_id($image_id);
            } elseif (!$is_new && isset($_POST['image_id']) && absint($_POST['image_id']) === 0) {
                $product->set_image_id('');
            }

            // Categories (support clearing categories on edit)
            if (!empty($cats)) {
                $product->set_category_ids($cats);
            } elseif (!$is_new) {
                $product->set_category_ids(array());
            }

            $saved_id = $product->save();

            // Handle variable product variations if provided (JSON or pipe-delimited string)
            if ($is_variable && !empty($variations) && $product instanceof \WC_Product_Variable) {
                $parsed_vars = json_decode(wp_unslash($_POST['variations_data']), true);
                if (is_array($parsed_vars) && !empty($parsed_vars)) {
                    $attr_options = array();
                    foreach ($parsed_vars as $v_row) {
                        if (!empty($v_row['label'])) {
                            $attr_options[] = sanitize_text_field($v_row['label']);
                        }
                    }
                    if (!empty($attr_options)) {
                        $attribute = new \WC_Product_Attribute();
                        $attribute->set_name('Option');
                        $attribute->set_options($attr_options);
                        $attribute->set_position(0);
                        $attribute->set_visible(true);
                        $attribute->set_variation(true);
                        $product->set_attributes(array($attribute));
                        $product->save();

                        foreach ($parsed_vars as $v_row) {
                            $v_label = sanitize_text_field($v_row['label'] ?? '');
                            if (empty($v_label)) continue;
                            $variation = new \WC_Product_Variation();
                            $variation->set_parent_id($saved_id);
                            $variation->set_attributes(array('option' => $v_label));
                            $variation->set_regular_price( isset($v_row['price']) && $v_row['price'] !== '' ? wc_format_decimal($v_row['price']) : $reg_price );
                            if (isset($v_row['stock']) && $v_row['stock'] !== '') {
                                $variation->set_manage_stock(true);
                                $variation->set_stock_quantity(absint($v_row['stock']));
                            }
                            $variation->set_status('publish');
                            $variation->save();
                        }
                    }
                }
            }

            // Cost Price (Crucial for Profit & Loss calculation; allow clearing when empty)
            if ($cost_price !== '') {
                update_post_meta($saved_id, '_kinboni_cost_price', $cost_price);
            } elseif (!$is_new && isset($_POST['cost_price'])) {
                delete_post_meta($saved_id, '_kinboni_cost_price');
            }

            // Log activity
            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::log(
                    $is_new ? 'product_created' : 'product_updated',
                    sprintf('%s product "%s" (ID #%d) with price %s', ($is_new ? 'Created new' : 'Updated'), $title, $saved_id, $reg_price)
                );
            }

            wp_send_json_success(array(
                'product_id' => $saved_id,
                'name'       => $title,
                'is_new'     => $is_new,
                'message'    => $is_new ? sprintf(esc_html__('Creation "%s" uploaded and published successfully!', 'kinboni-core'), $title) : sprintf(esc_html__('Product "%s" updated successfully.', 'kinboni-core'), $title),
            ));
        } catch (\Exception $e) {
            if (class_exists('Kinboni_Activity_Logger')) {
                Kinboni_Activity_Logger::error('product_save_failed', $e->getMessage(), array('exception' => get_class($e)));
            }
            wp_send_json_error(array('message' => esc_html__('Failed to save product. Please check your data and try again.', 'kinboni-core')));
        }
    }

    /**
     * AJAX: Inline Instant Stock Update
     */
    public function ajax_quick_update_stock() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $stock_qty  = isset($_POST['stock_qty']) ? absint(wp_unslash($_POST['stock_qty'])) : 0;
        $stock_st   = isset($_POST['stock_status']) ? sanitize_key(wp_unslash($_POST['stock_status'])) : '';

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => esc_html__('Product not found.', 'kinboni-core')));
        }

        $product->set_manage_stock(true);
        $product->set_stock_quantity($stock_qty);
        if (!empty($stock_st)) {
            $product->set_stock_status($stock_st);
        } else {
            $product->set_stock_status($stock_qty > 0 ? 'instock' : 'outofstock');
        }
        $product->save();

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('product_stock_updated', sprintf('Quick-updated stock for Product #%d (%s) to %d units', $product_id, $product->get_name(), $stock_qty));
        }

        wp_send_json_success(array(
            'product_id'   => $product_id,
            'stock_qty'    => $stock_qty,
            'stock_status' => $product->get_stock_status(),
            'message'      => esc_html__('Stock updated.', 'kinboni-core'),
        ));
    }

    /**
     * AJAX: Inline Instant Price Update
     */
    public function ajax_quick_update_price() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $price      = isset($_POST['price']) ? wc_format_decimal($_POST['price']) : '';
        $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? wc_format_decimal($_POST['sale_price']) : '';

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => esc_html__('Product not found.', 'kinboni-core')));
        }

        if ($price !== '') {
            $product->set_regular_price($price);
        }
        if ($sale_price !== '') {
            $product->set_sale_price($sale_price);
        }
        $product->save();

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('product_price_updated', sprintf('Quick-updated price for Product #%d to ৳%s', $product_id, $price));
        }

        wp_send_json_success(array(
            'product_id' => $product_id,
            'price'      => $price,
            'message'    => esc_html__('Price updated.', 'kinboni-core'),
        ));
    }

    /**
     * AJAX: One-Click Duplicate Product
     */
    public function ajax_duplicate_product() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $source = wc_get_product($product_id);
        if (!$source) {
            wp_send_json_error(array('message' => esc_html__('Source product not found.', 'kinboni-core')));
        }

        if (class_exists('WC_Admin_Duplicate_Product')) {
            $duplicate_handler = new \WC_Admin_Duplicate_Product();
            $duplicate = $duplicate_handler->product_duplicate($source);
        } else {
            // Clone manually
            $duplicate = clone $source;
            $duplicate->set_id(0);
            $duplicate->set_name(sprintf(esc_html__('%s (Copy)', 'kinboni-core'), $source->get_name()));
            $duplicate->set_status('draft');
            $duplicate->set_total_sales(0);
            $duplicate->save();
        }

        $new_id = $duplicate->get_id();

        // Copy purchase cost meta
        $cost = get_post_meta($product_id, '_kinboni_cost_price', true);
        if ($cost !== '') {
            update_post_meta($new_id, '_kinboni_cost_price', $cost);
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('product_duplicated', sprintf('Duplicated Product #%d to new Draft #%d', $product_id, $new_id));
        }

        wp_send_json_success(array(
            'new_id'  => $new_id,
            'message' => sprintf(esc_html__('Product duplicated as Draft #%d successfully!', 'kinboni-core'), $new_id),
        ));
    }

    /**
     * AJAX: Bulk Product Operations (Price, Stock, Status)
     */
    public function ajax_bulk_product_action() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $product_ids = isset($_POST['product_ids']) ? array_map('absint', (array) $_POST['product_ids']) : array();
        $action      = isset($_POST['bulk_action']) ? sanitize_key(wp_unslash($_POST['bulk_action'])) : '';
        $value       = isset($_POST['bulk_val']) ? sanitize_text_field(wp_unslash($_POST['bulk_val'])) : '';

        if (empty($product_ids)) {
            wp_send_json_error(array('message' => esc_html__('No products selected.', 'kinboni-core')));
        }

        $count = 0;
        foreach ($product_ids as $pid) {
            $p = wc_get_product($pid);
            if (!$p) continue;

            if ($action === 'status_publish') {
                $p->set_status('publish');
                $p->save();
                $count++;
            } elseif ($action === 'status_draft') {
                $p->set_status('draft');
                $p->save();
                $count++;
            } elseif ($action === 'status_trash') {
                $p->delete();
                $count++;
            } elseif ($action === 'stock_set') {
                $val = absint($value);
                $p->set_manage_stock(true);
                $p->set_stock_quantity($val);
                $p->set_stock_status($val > 0 ? 'instock' : 'outofstock');
                $p->save();
                $count++;
            } elseif ($action === 'price_adjust_pct') {
                $pct = (float) $value;
                $reg = (float) $p->get_regular_price();
                if ($reg > 0) {
                    $new_price = round($reg * (1 + ($pct / 100)), 2);
                    $p->set_regular_price($new_price);
                    $p->save();
                    $count++;
                }
            }
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('bulk_product_action', sprintf('Executed %s on %d products', $action, $count));
        }

        wp_send_json_success(array(
            'count'   => $count,
            'message' => sprintf(esc_html__('Updated %d product(s) successfully.', 'kinboni-core'), $count),
        ));
    }

    /**
     * AJAX: Get Full Product Data for Edit Modal
     */
    public function ajax_get_product_data() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => esc_html__('Product not found.', 'kinboni-core')));
        }

        $sale_from = $product->get_date_on_sale_from() ? $product->get_date_on_sale_from()->date('Y-m-d') : '';
        $sale_to   = $product->get_date_on_sale_to() ? $product->get_date_on_sale_to()->date('Y-m-d') : '';

        wp_send_json_success(array(
            'id'                => $product_id,
            'name'              => $product->get_name(),
            'sku'               => $product->get_sku(),
            'regular_price'     => $product->get_regular_price(),
            'sale_price'        => $product->get_sale_price(),
            'cost_price'        => get_post_meta($product_id, '_kinboni_cost_price', true),
            'sale_date_from'    => $sale_from,
            'sale_date_to'      => $sale_to,
            'manage_stock'      => $product->get_manage_stock(),
            'stock_quantity'    => $product->get_stock_quantity(),
            'stock_status'      => $product->get_stock_status(),
            'status'            => $product->get_status(),
            'category_ids'      => $product->get_category_ids(),
            'image_id'          => $product->get_image_id(),
            'image_url'         => wp_get_attachment_image_url($product->get_image_id(), 'medium') ?: wc_placeholder_img_src(),
            'short_description' => $product->get_short_description(),
            'description'       => $product->get_description(),
        ));
    }

    /**
     * AJAX: Update Menu Order (Drag-and-Drop)
     */
    public function ajax_update_product_order() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('products') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $order_map = isset($_POST['order_map']) ? (array) $_POST['order_map'] : array();

        foreach ($order_map as $item) {
            $pid = absint($item['id'] ?? 0);
            $pos = absint($item['position'] ?? 0);
            if ($pid > 0 && get_post_type($pid) === 'product') {
                wp_update_post(array(
                    'ID'         => $pid,
                    'menu_order' => $pos,
                ));
            }
        }

        wp_send_json_success(array('message' => esc_html__('Catalog order updated.', 'kinboni-core')));
    }

    /**
     * Export Products to CSV
     */
    public function export_products_csv() {
        $products = wc_get_products(array('limit' => -1, 'status' => array('publish', 'draft', 'pending')));

        $filename = 'kinboni-products-' . date('Y-m-d-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($out, array(
            'Product ID',
            'Title',
            'SKU',
            'Categories',
            'Regular Price',
            'Sale Price',
            'Cost Price',
            'Stock Quantity',
            'Stock Status',
            'Status',
            'Views Count',
            'Total Units Sold',
        ));

        foreach ($products as $p) {
            $pid = $p->get_id();
            $terms = get_the_terms($pid, 'product_cat');
            $cats = array();
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $t) $cats[] = $t->name;
            }

            $csv_esc = array('Kinboni_Core', 'sanitize_csv_cell');
            fputcsv($out, array(
                $pid,
                call_user_func($csv_esc, $p->get_name()),
                call_user_func($csv_esc, $p->get_sku()),
                call_user_func($csv_esc, implode(', ', $cats)),
                $p->get_regular_price(),
                $p->get_sale_price(),
                get_post_meta($pid, '_kinboni_cost_price', true),
                $p->get_stock_quantity() !== null ? $p->get_stock_quantity() : '',
                $p->get_stock_status(),
                $p->get_status(),
                (int) get_post_meta($pid, '_kinboni_views_count', true),
                (int) get_post_meta($pid, 'total_sales', true),
            ));
        }

        fclose($out);
        exit;
    }

    /**
     * Import Products from CSV
     */
    public function import_products_csv() {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) return;

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $has_id_col = is_numeric($row[0] ?? '') && count($row) >= 8;
            $row_id     = $has_id_col ? absint($row[0]) : 0;
            $title      = sanitize_text_field($has_id_col ? ($row[1] ?? '') : ($row[0] ?? ''));
            if (empty($title)) continue;

            $sku        = sanitize_text_field($has_id_col ? ($row[2] ?? '') : ($row[1] ?? ''));
            $reg_price  = wc_format_decimal($has_id_col ? ($row[4] ?? 0) : ($row[2] ?? 0));
            $sale_price = wc_format_decimal($has_id_col ? ($row[5] ?? '') : ($row[3] ?? ''));
            $cost_price = wc_format_decimal($has_id_col ? ($row[6] ?? '') : ($row[4] ?? ''));
            $raw_stock  = $has_id_col ? ($row[7] ?? '') : ($row[5] ?? '');
            $stock_qty  = ($raw_stock !== '' && is_numeric($raw_stock)) ? absint($raw_stock) : null;

            try {
                $existing_product = null;
                if ($row_id > 0 && get_post_type($row_id) === 'product') {
                    $existing_product = wc_get_product($row_id);
                }
                if (!$existing_product && !empty($sku)) {
                    $sku_id = wc_get_product_id_by_sku($sku);
                    if ($sku_id > 0) {
                        $existing_product = wc_get_product($sku_id);
                    }
                }

                $product = $existing_product ? $existing_product : new WC_Product_Simple();
                $product->set_name($title);
                if (!$existing_product) {
                    $product->set_status('publish');
                }
                if (!empty($sku) && $product->get_sku() !== $sku) {
                    $dup_id = wc_get_product_id_by_sku($sku);
                    if (!$dup_id || $dup_id === $product->get_id()) {
                        $product->set_sku($sku);
                    }
                }
                $product->set_regular_price($reg_price);
                $product->set_sale_price($sale_price !== '' ? $sale_price : '');

                if ($stock_qty !== null) {
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($stock_qty);
                    $product->set_stock_status($stock_qty > 0 ? 'instock' : 'outofstock');
                }

                $new_id = $product->save();
                if ($cost_price !== '') {
                    update_post_meta($new_id, '_kinboni_cost_price', $cost_price);
                }
                $imported++;
            } catch (\Exception $e) {
                if (class_exists('Kinboni_Activity_Logger')) {
                    Kinboni_Activity_Logger::error('csv_import_row_failed', $e->getMessage());
                }
            }
        }

        fclose($handle);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('products_csv_imported', sprintf('Imported %d products from CSV upload', $imported));
        }

        wp_safe_redirect(add_query_arg(array('page' => 'kinboni-products', 'imported' => $imported), admin_url('admin.php')));
        exit;
    }
}

