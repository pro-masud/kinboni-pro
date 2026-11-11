<?php
/**
 * Kinboni Slide-over Mini-Cart & Free Shipping Progress Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Ajax_Cart {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX: Add to Cart
        add_action('wp_ajax_kinboni_add_to_cart', array($this, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_kinboni_add_to_cart', array($this, 'ajax_add_to_cart'));

        // AJAX: Update item quantity in drawer
        add_action('wp_ajax_kinboni_update_cart_qty', array($this, 'update_cart_qty'));
        add_action('wp_ajax_nopriv_kinboni_update_cart_qty', array($this, 'update_cart_qty'));

        // AJAX: Remove item from drawer
        add_action('wp_ajax_kinboni_remove_cart_item', array($this, 'remove_cart_item'));
        add_action('wp_ajax_nopriv_kinboni_remove_cart_item', array($this, 'remove_cart_item'));

        // AJAX: Apply Coupon
        add_action('wp_ajax_kinboni_apply_coupon', array($this, 'apply_coupon'));
        add_action('wp_ajax_nopriv_kinboni_apply_coupon', array($this, 'apply_coupon'));

        // AJAX: Remove Coupon
        add_action('wp_ajax_kinboni_remove_coupon', array($this, 'remove_coupon'));
        add_action('wp_ajax_nopriv_kinboni_remove_coupon', array($this, 'remove_coupon'));

        // AJAX: Clear Cart
        add_action('wp_ajax_kinboni_clear_cart', array($this, 'ajax_clear_cart'));
        add_action('wp_ajax_nopriv_kinboni_clear_cart', array($this, 'ajax_clear_cart'));

        // AJAX: Get Cart JSON Data
        add_action('wp_ajax_kinboni_get_cart_data', array($this, 'ajax_get_cart_data'));
        add_action('wp_ajax_nopriv_kinboni_get_cart_data', array($this, 'ajax_get_cart_data'));

        // Filter: Custom WooCommerce fragments
        add_filter('woocommerce_add_to_cart_fragments', array($this, 'cart_fragments'));

        // Preserve & display Bespoke Monogram in native WooCommerce cart and checkout
        add_filter('woocommerce_get_item_data', array($this, 'display_monogram_item_data'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'save_monogram_to_order_item'), 10, 4);
    }

    /**
     * Display Bespoke Foil Monogram in standard WooCommerce Cart / Checkout item meta
     */
    public function display_monogram_item_data($item_data, $cart_item) {
        if (!empty($cart_item['kinboni_monogram'])) {
            $item_data[] = array(
                'key'   => esc_html__('Bespoke Foil Monogram', 'kinboni-core'),
                'value' => sanitize_text_field($cart_item['kinboni_monogram']),
            );
        }
        return $item_data;
    }

    /**
     * Save Bespoke Foil Monogram to WC_Order_Item_Product during standard WooCommerce checkout
     */
    public function save_monogram_to_order_item($item, $cart_item_key, $values, $order) {
        if (!empty($values['kinboni_monogram'])) {
            $item->add_meta_data(esc_html__('Bespoke Foil Monogram', 'kinboni-core'), sanitize_text_field($values['kinboni_monogram']), true);
        }
    }

    /**
     * Return custom fragments for slide-over drawer
     */
    public function cart_fragments($fragments) {
        if (!function_exists('WC') || !WC()->cart) {
            return $fragments;
        }

        $count = WC()->cart->get_cart_contents_count();
        $subtotal_raw = (float) WC()->cart->get_subtotal();
        $threshold = (float) get_option('kinboni_free_shipping_threshold', 2500);
        $progress = ($threshold > 0) ? min(100, (int) round(($subtotal_raw / $threshold) * 100)) : 100;
        $diff = max(0, $threshold - $subtotal_raw);

        $fragments['.cart-count-badge'] = '<span class="cart-count-badge absolute -top-1 -right-1 sm:-top-1.5 sm:-right-1.5 ' . ($count > 0 ? 'scale-100' : 'scale-0') . ' transform transition-transform duration-300 flex items-center justify-center min-w-[18px] h-[18px] sm:min-w-[20px] sm:h-[20px] px-1 text-xs font-bold text-stone-950 bg-gold-400 dark:bg-white rounded-full shadow-md">' . $count . '</span>';
        
        ob_start();
        $this->render_drawer_items();
        $fragments['#cart-drawer-items-wrap'] = ob_get_clean();

        $fragments['#cart-subtotal'] = '<span id="cart-subtotal" class="font-semibold text-stone-900 dark:text-white">' . (WC()->cart ? WC()->cart->get_cart_subtotal() : '৳0') . '</span>';
        $fragments['#cart-total'] = '<span id="cart-total" class="font-serif font-bold text-lg text-gold-700 dark:text-gold-400">' . (WC()->cart ? WC()->cart->get_total() : '৳0') . '</span>';

        if ($threshold > 0 && $subtotal_raw >= $threshold) {
            $shipping_text = '<span class="text-emerald-600 dark:text-emerald-400 font-bold">🎉 ' . esc_html__('Complimentary Express Shipping Unlocked!', 'kinboni-core') . '</span>';
        } elseif ($threshold > 0) {
            $shipping_text = sprintf(esc_html__('Add %s more for Complimentary Express Shipping', 'kinboni-core'), '<strong>' . wc_price($diff) . '</strong>');
        } else {
            $shipping_text = '<span class="text-emerald-600 dark:text-emerald-400 font-bold">🎉 ' . esc_html__('Complimentary Express Shipping Unlocked!', 'kinboni-core') . '</span>';
        }
        $fragments['#free-shipping-text'] = '<p id="free-shipping-text" class="text-xs text-stone-600 dark:text-stone-300 mb-1.5 font-medium">' . $shipping_text . '</p>';
        $fragments['#free-shipping-progress'] = '<div id="free-shipping-progress" class="h-full bg-gold-500 rounded-full transition-all duration-500" style="width: ' . $progress . '%"></div>';
        $is_free_frag = ($threshold <= 0 || $subtotal_raw >= $threshold);
        $fragments['#cart-shipping-status'] = '<span id="cart-shipping-status" class="font-semibold ' . ($is_free_frag ? 'text-emerald-600 dark:text-emerald-400' : 'text-stone-700 dark:text-stone-300') . '">' . ($is_free_frag ? esc_html__('COMPLIMENTARY', 'kinboni-core') : esc_html__('Calculated at Checkout', 'kinboni-core')) . '</span>';

        return $fragments;
    }

    /**
     * Render Drawer Line Items
     */
    public function render_drawer_items() {
        if (!function_exists('WC') || !WC()->cart) {
            return;
        }
        ?>
        <div id="cart-drawer-items-wrap" class="space-y-4">
          <?php if (WC()->cart && !WC()->cart->is_empty()) : ?>
            <?php
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product   = $cart_item['data'];
                $product_id = $cart_item['product_id'];

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                    $thumbnail = $_product->get_image('thumbnail', array('class' => 'w-full h-full object-cover'));
                    ?>
                    <div class="flex items-center gap-4 p-3 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                      <div class="w-16 h-20 rounded-xl overflow-hidden bg-stone-100 dark:bg-stone-900 flex-shrink-0">
                        <?php echo $thumbnail; ?>
                      </div>
                      <div class="flex-grow space-y-1 min-w-0">
                        <h4 class="font-serif text-sm font-medium text-stone-900 dark:text-white truncate">
                          <?php echo esc_html($_product->get_name()); ?>
                        </h4>
                        <div class="text-xs font-semibold text-gold-700 dark:text-gold-400 font-serif">
                          <?php echo WC()->cart->get_product_price($_product); ?>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                          <button type="button" class="btn-drawer-qty-minus w-6 h-6 rounded-md bg-stone-200 dark:bg-stone-700 flex items-center justify-center text-xs font-bold" data-key="<?php echo esc_attr($cart_item_key); ?>" data-qty="<?php echo esc_attr($cart_item['quantity'] - 1); ?>">-</button>
                          <span class="text-xs font-bold w-6 text-center"><?php echo esc_html($cart_item['quantity']); ?></span>
                          <button type="button" class="btn-drawer-qty-plus w-6 h-6 rounded-md bg-stone-200 dark:bg-stone-700 flex items-center justify-center text-xs font-bold" data-key="<?php echo esc_attr($cart_item_key); ?>" data-qty="<?php echo esc_attr($cart_item['quantity'] + 1); ?>">+</button>
                        </div>
                      </div>
                      <button type="button" class="btn-drawer-remove-item text-stone-400 hover:text-rose-500 p-1" data-key="<?php echo esc_attr($cart_item_key); ?>">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </div>
                    <?php
                }
            }
            ?>
          <?php else : ?>
            <div class="text-center py-12 space-y-3">
              <i data-lucide="shopping-bag" class="w-10 h-10 text-stone-400 mx-auto"></i>
              <p class="text-xs text-stone-500"><?php esc_html_e('Your shopping bag is currently empty.', 'kinboni-core'); ?></p>
            </div>
          <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Update quantity via AJAX
     */
    public function update_cart_qty() {
        if (!check_ajax_referer('kinboni_core_nonce', 'nonce', false) && !check_ajax_referer('woocommerce-cart', 'security', false)) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh and try again.', 'kinboni-core')), 403);
        }

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce cart is unavailable.', 'kinboni-core')));
        }

        $key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
        $qty = isset($_POST['quantity']) ? max(0, intval(wp_unslash($_POST['quantity']))) : 1;

        if ($key && WC()->cart) {
            $cart = WC()->cart->get_cart();
            if (!isset($cart[$key])) {
                wp_send_json_error(array('message' => esc_html__('Cart item not found.', 'kinboni-core')), 404);
            }

            $product = isset($cart[$key]['data']) ? $cart[$key]['data'] : null;
            if ($qty > 0 && $product instanceof \WC_Product) {
                $max_purchase = $product->get_max_purchase_quantity();
                if ($max_purchase > 0 && $qty > $max_purchase) {
                    wp_send_json_error(array('message' => esc_html__('The requested quantity exceeds the purchase limit.', 'kinboni-core')), 400);
                }
                if (!$product->has_enough_stock($qty)) {
                    wp_send_json_error(array('message' => esc_html__('The requested quantity is not available in stock.', 'kinboni-core')), 400);
                }
            }

            if ($qty <= 0) {
                WC()->cart->remove_cart_item($key);
            } else {
                WC()->cart->set_quantity($key, $qty, true);
            }
            WC()->cart->calculate_totals();

            ob_start();
            $this->render_drawer_items();
            $items_html = ob_get_clean();

            $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
            $fragments['#cart-drawer-items-wrap'] = $items_html;

            wp_send_json_success(array(
                'fragments'  => $fragments,
                'cart_hash'  => WC()->cart->get_cart_hash(),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_total(),
            ));
        }

        wp_send_json_error(array('message' => esc_html__('Invalid quantity update.', 'kinboni-core')));
    }

    /**
     * Remove item via AJAX
     */
    public function remove_cart_item() {
        if (!check_ajax_referer('kinboni_core_nonce', 'nonce', false) && !check_ajax_referer('woocommerce-cart', 'security', false)) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh and try again.', 'kinboni-core')), 403);
        }

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce cart is unavailable.', 'kinboni-core')));
        }

        $key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';

        if ($key && WC()->cart) {
            WC()->cart->remove_cart_item($key);
            WC()->cart->calculate_totals();

            ob_start();
            $this->render_drawer_items();
            $items_html = ob_get_clean();

            $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
            $fragments['#cart-drawer-items-wrap'] = $items_html;

            wp_send_json_success(array(
                'message'    => esc_html__('Item removed from bag.', 'kinboni-core'),
                'fragments'  => $fragments,
                'cart_hash'  => WC()->cart->get_cart_hash(),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_total(),
            ));
        }

        wp_send_json_error(array('message' => esc_html__('Could not remove item.', 'kinboni-core')));
    }

    /**
     * Apply coupon via AJAX
     */
    public function apply_coupon() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce cart is unavailable.', 'kinboni-core')));
        }

        $coupon = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';

        if ($coupon && WC()->cart) {
            // The storefront advertises the configured VIP code (LUXE20 by
            // default). Ensure that code exists before WooCommerce validates
            // it, so a fresh install cannot show an unusable voucher banner.
            $vip_code = sanitize_text_field(get_option('kinboni_vip_coupon_code', 'LUXE20'));
            if ($vip_code && strcasecmp($coupon, $vip_code) === 0 && class_exists('Kinboni_Core') && method_exists('Kinboni_Core', 'ensure_vip_coupon')) {
                Kinboni_Core::ensure_vip_coupon();
            }

            $result = WC()->cart->apply_coupon($coupon);
            if ($result) {
                WC()->cart->calculate_totals();
                wp_send_json_success(array('message' => esc_html__('Coupon applied successfully!', 'kinboni-core')));
            } else {
                wp_send_json_error(array('message' => esc_html__('Invalid or expired coupon.', 'kinboni-core')));
            }
        }
        wp_send_json_error(array('message' => esc_html__('Please enter a coupon code.', 'kinboni-core')));
    }

    /**
     * Remove coupon via AJAX
     */
    public function remove_coupon() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce cart is unavailable.', 'kinboni-core')));
        }

        $coupon = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';

        if ($coupon && WC()->cart) {
            $result = WC()->cart->remove_coupon($coupon);
            if ($result) {
                WC()->cart->calculate_totals();
                wp_send_json_success(array('message' => esc_html__('Privilege code removed.', 'kinboni-core')));
            } else {
                wp_send_json_error(array('message' => esc_html__('Could not remove coupon.', 'kinboni-core')));
            }
        }
        wp_send_json_error(array('message' => esc_html__('Invalid request.', 'kinboni-core')));
    }

    /**
     * Clear entire cart via AJAX
     */
    public function ajax_clear_cart() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce cart is unavailable.', 'kinboni-core')));
        }

        if (WC()->cart) {
            WC()->cart->empty_cart();
            wp_send_json_success(array(
                'message' => esc_html__('Shopping bag cleared.', 'kinboni-core'),
                'cart'    => $this->get_cart_payload(),
            ));
        }
        wp_send_json_error(array('message' => esc_html__('Could not clear shopping bag.', 'kinboni-core')));
    }

    /**
     * Get Complete Cart JSON Data
     */
    public function ajax_get_cart_data() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce cart is unavailable.', 'kinboni-core')));
        }

        $data = $this->get_cart_payload();
        wp_send_json_success($data);
    }

    /**
     * Build Cart Payload Array
     */
    public function get_cart_payload() {
        if (!function_exists('WC') || !WC()->cart) {
            return array('is_empty' => true, 'items' => array(), 'count' => 0);
        }

        // Normalize any catalog/cart price that is exactly 100x the customer
        // facing formatted value before WooCommerce recalculates totals.
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;
            if (!$product || !method_exists($product, 'get_price')) {
                continue;
            }
            $raw_price = (float) $product->get_price('edit');
            $display_text = html_entity_decode(wp_strip_all_tags(wc_price($raw_price)), ENT_QUOTES, 'UTF-8');
            $display_text = preg_replace('/[^0-9.,-]/u', '', $display_text);
            $display_price = (float) str_replace(',', '', $display_text);
            if ($raw_price > 0 && $display_price > 0 && abs($raw_price - ($display_price * 100)) < 0.0001) {
                $product->set_price($display_price);
            }
        }

        // A previous totals pass can leave WooCommerce's cached subtotal in
        // minor units while product prices are stored/displayed in major
        // units (for example 1600 vs 16). Recalculate before reading any
        // totals so every AJAX consumer receives a consistent cart snapshot.
        WC()->cart->calculate_totals();

        $items = array();
        $subtotal_raw = (float) WC()->cart->get_subtotal();
        $discount_raw = (float) WC()->cart->get_discount_total();
        $total_raw    = (float) WC()->cart->get_total('edit');
        $threshold    = (float) get_option('kinboni_free_shipping_threshold', 2500);
        $progress     = ($threshold > 0) ? min(100, (int) round(($subtotal_raw / $threshold) * 100)) : 100;
        $in_cart_ids  = array();

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product   = $cart_item['data'];
            $product_id = $cart_item['product_id'];

            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                $in_cart_ids[] = $product_id;
                $img_id = $_product->get_image_id();
                $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'medium') : wc_placeholder_img_src();
                
                $cats = get_the_terms($product_id, 'product_cat');
                $cat_label = (!empty($cats) && !is_wp_error($cats)) ? esc_html($cats[0]->name) : 'Atelier Creation';

                $price_raw = (float) $_product->get_price();
                // Normalize through WooCommerce's formatted price because a
                // currency formatter may expose raw values in minor units
                // after totals recalculation (e.g. 1600 displayed as 16).
                $price_display_text = html_entity_decode(wp_strip_all_tags(wc_price($price_raw)), ENT_QUOTES, 'UTF-8');
                $price_display_text = preg_replace('/[^0-9.,-]/u', '', $price_display_text);
                $price_display = (float) str_replace(',', '', $price_display_text);
                $price_raw = $price_display > 0 ? $price_display : $price_raw;
                $line_total_raw = $price_raw * $cart_item['quantity'];

                $monogram = !empty($cart_item['kinboni_monogram']) ? $cart_item['kinboni_monogram'] : '';

                $items[] = array(
                    'key'              => $cart_item_key,
                    'id'               => $product_id,
                    'name'             => $_product->get_name(),
                    'category'         => $cat_label,
                    'image'            => $img_url,
                    'price'            => $price_raw,
                    'price_formatted'  => wc_price($price_raw),
                    'quantity'         => $cart_item['quantity'],
                    'subtotal'         => $line_total_raw,
                    'subtotal_formatted' => wc_price($line_total_raw),
                    'monogram'         => $monogram,
                    'url'              => get_permalink($product_id),
                );
            }
        }

        // Some WooCommerce extensions can leave get_subtotal() in minor units
        // after a totals recalculation while product prices and wc_price() use
        // major units. Use the rendered line subtotals as the authoritative
        // value so AJAX cart/checkout totals cannot jump from ৳16 to ৳1,600.
        if (!empty($items)) {
            $line_subtotal = array_sum(array_map(static function ($item) {
                return isset($item['subtotal']) ? (float) $item['subtotal'] : 0.0;
            }, $items));
            if ($line_subtotal > 0 && abs($subtotal_raw - $line_subtotal) > 0.0001) {
                $subtotal_raw = $line_subtotal;
            }
        }

        $applied_coupons = array();
        foreach (WC()->cart->get_applied_coupons() as $code) {
            $coupon = new \WC_Coupon($code);
            $applied_coupons[] = array(
                'code'   => $code,
                'amount' => $coupon->get_amount(),
                'type'   => $coupon->get_discount_type(),
            );
        }

        // Cross-sells: get WooCommerce cross-sells or fallback to other products not in cart (if enabled)
        $cross_sells = array();
        $cross_sell_ids = (get_option('kinboni_cart_recommendations', 'yes') === 'yes') ? WC()->cart->get_cross_sells() : array();
        if (empty($cross_sell_ids) && get_option('kinboni_cart_recommendations', 'yes') === 'yes') {
            $args = array(
                'status'  => 'publish',
                'limit'   => 4,
                'orderby' => 'popularity',
                'order'   => 'DESC',
                'exclude' => $in_cart_ids,
            );
            $prods = wc_get_products($args);
            foreach ($prods as $p) {
                $cross_sell_ids[] = $p->get_id();
            }
        }

        foreach (array_slice($cross_sell_ids, 0, 2) as $cs_id) {
            $cs_prod = wc_get_product($cs_id);
            if ($cs_prod) {
                $cs_img_id = $cs_prod->get_image_id();
                $cs_img = $cs_img_id ? wp_get_attachment_image_url($cs_img_id, 'medium') : wc_placeholder_img_src();
                $cs_cats = get_the_terms($cs_id, 'product_cat');
                $cs_cat_label = (!empty($cs_cats) && !is_wp_error($cs_cats)) ? esc_html($cs_cats[0]->name) : 'Atelier';

                $cross_sells[] = array(
                    'id'               => $cs_id,
                    'name'             => $cs_prod->get_name(),
                    'category'         => $cs_cat_label,
                    'image'            => $cs_img,
                    'price'            => (float) $cs_prod->get_price(),
                    'price_formatted'  => wc_price($cs_prod->get_price()),
                    'url'              => get_permalink($cs_id),
                );
            }
        }

        $is_free = ($threshold <= 0 || $subtotal_raw >= $threshold);
        $progress = ($threshold > 0) ? min(100, (int) round(($subtotal_raw / $threshold) * 100)) : 100;

        return array(
            'is_empty'           => empty($items),
            'count'              => WC()->cart->get_cart_contents_count(),
            'items'              => $items,
            'subtotal'           => $subtotal_raw,
            'subtotal_formatted' => wc_price($subtotal_raw),
            'discount'           => $discount_raw,
            'discount_formatted' => wc_price($discount_raw),
            'coupons'            => $applied_coupons,
            'total'              => $total_raw,
            'total_formatted'    => wc_price($total_raw),
            'threshold'          => $threshold,
            'diff'               => max(0, $threshold - $subtotal_raw),
            'diff_formatted'     => wc_price(max(0, $threshold - $subtotal_raw)),
            'is_free_shipping'   => $is_free,
            'progress'           => $progress,
            'cross_sells'        => $cross_sells,
            'currency_symbol'    => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳',
        );
    }

    /**
     * Add to Cart via AJAX
     */
    public function ajax_add_to_cart() {
        if (!check_ajax_referer('kinboni_core_nonce', 'nonce', false) && !check_ajax_referer('woocommerce-cart', 'security', false)) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh and try again.', 'kinboni-core')), 403);
        }

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce cart is unavailable.', 'kinboni-core')));
        }

        $product_id   = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;
        $quantity     = isset($_POST['quantity']) ? max(1, intval(wp_unslash($_POST['quantity']))) : 1;
        $variation    = isset($_POST['variation']) && is_array($_POST['variation']) ? array_map('sanitize_text_field', wp_unslash($_POST['variation'])) : array();
        $cart_item_data = array();

        if (!empty($_POST['monogram'])) {
            $cart_item_data['kinboni_monogram'] = sanitize_text_field(wp_unslash($_POST['monogram']));
        }

        if ($product_id && WC()->cart) {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
            if (function_exists('wc_clear_notices')) {
                wc_clear_notices();
            }
            if ($cart_item_key) {
                WC()->cart->calculate_totals();

                ob_start();
                $this->render_drawer_items();
                $items_html = ob_get_clean();

                $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
                $fragments['#cart-drawer-items-wrap'] = $items_html;

                wp_send_json_success(array(
                    'message'       => esc_html__('Creation added to your shopping bag!', 'kinboni-core'),
                    'cart_item_key' => $cart_item_key,
                    'fragments'     => $fragments,
                    'cart_hash'     => WC()->cart->get_cart_hash(),
                    'cart_count'    => WC()->cart->get_cart_contents_count(),
                    'cart_total'    => WC()->cart->get_total(),
                ));
            } else {
                wp_send_json_error(array('message' => esc_html__('Could not add creation to shopping bag.', 'kinboni-core')));
            }
        }
        wp_send_json_error(array('message' => esc_html__('Invalid product.', 'kinboni-core')));
    }
}

