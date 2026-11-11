<?php
/**
 * Kinboni Fast 3-Field Quick Order (Cash on Delivery) Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Quick_Order {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints for Quick Order
        add_action('wp_ajax_kinboni_quick_order_submit', array($this, 'handle_order_submission'));
        add_action('wp_ajax_nopriv_kinboni_quick_order_submit', array($this, 'handle_order_submission'));
    }

    private static function normalize_product_price($product) {
        if (!$product || !method_exists($product, 'get_price')) {
            return 0.0;
        }
        $raw_price = (float) $product->get_price('edit');
        $display_text = html_entity_decode(wp_strip_all_tags(wc_price($raw_price)), ENT_QUOTES, 'UTF-8');
        $display_text = preg_replace('/[^0-9.,-]/u', '', $display_text);
        $display_price = (float) str_replace(',', '', $display_text);
        if ($raw_price > 0 && $display_price > 0 && abs($raw_price - ($display_price * 100)) < 0.0001) {
            $product->set_price($display_price);
            return $display_price;
        }
        return $raw_price;
    }

    /**
     * Handle Fast 3-Field COD Order Submission
     */
    public function handle_order_submission() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        // Honeypot check
        if (!empty($_POST['kinboni_hp'])) {
            wp_send_json_error(array('message' => esc_html__('Spam detected.', 'kinboni-core')));
        }

        // Per-IP rate limit (max 5 orders / 10 minutes)
        $ip = sanitize_text_field(Kinboni_Core::get_client_ip());
        $rate_key = 'kb_rate_order_' . md5($ip);
        $order_count = (int) get_transient($rate_key);
        if ($order_count >= 5) {
            wp_send_json_error(array('message' => esc_html__('অতিরিক্ত অর্ডার চেষ্টার কারণে সাময়িকভাবে ব্লক করা হয়েছে। অনুগ্রহ করে ১০ মিনিট পর চেষ্টা করুন।', 'kinboni-core')));
        }

        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce is required for checkout.', 'kinboni-core')));
        }

        $customer_name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $customer_phone   = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $customer_address = isset($_POST['address']) ? sanitize_textarea_field(wp_unslash($_POST['address'])) : '';
        $delivery_zone    = isset($_POST['zone']) ? sanitize_text_field(wp_unslash($_POST['zone'])) : 'inside';
        $product_id       = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $quantity         = isset($_POST['quantity']) ? min(max(1, absint(wp_unslash($_POST['quantity']))), 20) : 1;
        $promo_code       = isset($_POST['promo_code']) ? sanitize_text_field(wp_unslash($_POST['promo_code'])) : '';

        // 1. Validation
        if (empty($customer_name)) {
            wp_send_json_error(array('message' => esc_html__('অনুগ্রহ করে আপনার নাম লিখুন।', 'kinboni-core')));
        }

        // Validate Bangladeshi 11-digit Phone (013-019)
        $clean_phone = preg_replace('/[^0-9]/', '', $customer_phone);
        if (strlen($clean_phone) === 13 && substr($clean_phone, 0, 2) === '88') {
            $clean_phone = substr($clean_phone, 2);
        }
        if (!preg_match('/^01[3-9]\d{8}$/', $clean_phone)) {
            wp_send_json_error(array('message' => esc_html__('অনুগ্রহ করে একটি সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 01711000000)।', 'kinboni-core')));
        }

        $addr_len = function_exists('mb_strlen') ? mb_strlen($customer_address, 'UTF-8') : strlen($customer_address);
        if (empty($customer_address) || $addr_len < 5) {
            wp_send_json_error(array('message' => esc_html__('অনুগ্রহ করে আপনার সম্পূর্ণ ডেলিভারি ঠিকানা দিন।', 'kinboni-core')));
        }

        // Enforce COD availability per delivery zone
        if ($delivery_zone === 'outside' && get_option('kinboni_cod_outside_enabled', 'yes') !== 'yes') {
            wp_send_json_error(array('message' => esc_html__('ঢাকার বাইরে ক্যাশ অন ডেলিভারি বর্তমানে বন্ধ আছে। অনুগ্রহ করে মোবাইল পেমেন্ট ব্যবহার করুন।', 'kinboni-core')));
        }
        if ($delivery_zone !== 'outside' && get_option('kinboni_cod_inside_enabled', 'yes') !== 'yes') {
            wp_send_json_error(array('message' => esc_html__('ঢাকার ভেতরে ক্যাশ অন ডেলিভারি বর্তমানে বন্ধ আছে। অনুগ্রহ করে মোবাইল পেমেন্ট ব্যবহার করুন।', 'kinboni-core')));
        }

        // Validate cart items server-side if checking out from cart
        $order_subtotal = 0.0;
        if ($product_id > 0) {
            $single_p = wc_get_product($product_id);
            if (!$single_p) {
                wp_send_json_error(array('message' => esc_html__('প্রোডাক্টটি খুঁজে পাওয়া যায়নি।', 'kinboni-core')));
            }
            if (!$single_p->is_purchasable() || !$single_p->is_in_stock() || $single_p->is_type('variable')) {
                wp_send_json_error(array('message' => esc_html__('এই পণ্যটি বর্তমানে কেনার জন্য উপলব্ধ নয়।', 'kinboni-core')));
            }
            if (!$single_p->has_enough_stock($quantity)) {
                wp_send_json_error(array('message' => sprintf(esc_html__('"%s" পণ্যের পর্যাপ্ত স্টক নেই।', 'kinboni-core'), $single_p->get_name())));
            }
            $max_purchase = $single_p->get_max_purchase_quantity();
            if ($max_purchase > 0 && $quantity > $max_purchase) {
                wp_send_json_error(array('message' => sprintf(esc_html__('"%s" পণ্যের সর্বোচ্চ ক্রয় সীমা অতিক্রান্ত।', 'kinboni-core'), $single_p->get_name())));
            }
            $order_subtotal = (float) $single_p->get_price() * $quantity;
        } else {
            if (!WC()->cart || WC()->cart->is_empty()) {
                wp_send_json_error(array('message' => esc_html__('আপনার ব্যাগ বর্তমানে খালি।', 'kinboni-core')));
            }
            foreach (WC()->cart->get_cart() as $cart_item) {
                $p   = isset($cart_item['data']) ? $cart_item['data'] : null;
                $qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;
                if (!$p || !is_a($p, 'WC_Product') || !$p->exists()) {
                    wp_send_json_error(array('message' => esc_html__('আপনার ব্যাগের একটি পণ্য আর উপলব্ধ নেই।', 'kinboni-core')));
                }
                if (!$p->is_purchasable() || !$p->is_in_stock()) {
                    wp_send_json_error(array('message' => sprintf(esc_html__('"%s" পণ্যটি বর্তমানে কেনার জন্য উপলব্ধ নয়।', 'kinboni-core'), $p->get_name())));
                }
                if (!$p->has_enough_stock($qty)) {
                    wp_send_json_error(array('message' => sprintf(esc_html__('"%s" পণ্যের পর্যাপ্ত স্টক নেই।', 'kinboni-core'), $p->get_name())));
                }
                $max_p = $p->get_max_purchase_quantity();
                if ($max_p > 0 && $qty > $max_p) {
                    wp_send_json_error(array('message' => sprintf(esc_html__('"%s" পণ্যের সর্বোচ্চ ক্রয় সীমা অতিক্রান্ত।', 'kinboni-core'), $p->get_name())));
                }
            }
            // Use validated product prices rather than a potentially stale
            // WooCommerce subtotal after a cart totals recalculation.
            $order_subtotal = 0.0;
            foreach (WC()->cart->get_cart() as $cart_item) {
                $cart_product = isset($cart_item['data']) ? $cart_item['data'] : null;
                $cart_qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;
                if ($cart_product && $cart_qty > 0) {
                    $order_subtotal += self::normalize_product_price($cart_product) * $cart_qty;
                }
            }
        }

        // Duplicate-order protection with product quantities and simultaneous request locking
        if ($product_id > 0) {
            $order_sig = 'prod_' . $product_id . '_qty_' . $quantity;
        } else {
            $cart_sig_items = array();
            foreach (WC()->cart->get_cart() as $cart_item) {
                $cart_sig_items[] = array(
                    'p' => isset($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0,
                    'v' => isset($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : 0,
                    'q' => isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1,
                );
            }
            $order_sig = 'cart_' . md5(wp_json_encode($cart_sig_items));
        }

        $lock_key = 'kb_order_lock_' . md5($ip . '_' . $clean_phone . '_' . $order_sig);
        $lock_val = get_transient($lock_key);

        // If another request is currently processing this exact order, wait briefly for completion
        if ($lock_val === 'processing') {
            for ($i = 0; $i < 4; $i++) {
                usleep(500000);
                $lock_val = get_transient($lock_key);
                if (is_numeric($lock_val) && (int) $lock_val > 0) {
                    break;
                }
            }
        }

        // Return existing order with identical success response structure if already created
        if (!empty($lock_val) && is_numeric($lock_val) && (int) $lock_val > 0) {
            $existing_order = wc_get_order((int) $lock_val);
            if ($existing_order) {
                $wa_phone_raw = get_option('kinboni_whatsapp_phone', '8801700000000');
                $wa_phone     = preg_replace('/\D/', '', (string) $wa_phone_raw);
                $whatsapp_url = '';
                if (!empty($wa_phone) && $wa_phone !== '8801700000000') {
                    $whatsapp_msg = rawurlencode(sprintf(
                        "Hello Kinboni Atelier! I have placed an order.\nOrder ID: #%s\nName: %s\nPhone: %s\nTotal: ৳%s\nAddress: %s",
                        $existing_order->get_id(),
                        $customer_name,
                        $clean_phone,
                        $existing_order->get_total(),
                        $customer_address
                    ));
                    $whatsapp_url = "https://wa.me/{$wa_phone}?text={$whatsapp_msg}";
                }

                wp_send_json_success(array(
                    'order_id'     => $existing_order->get_id(),
                    'order_number' => '#' . $existing_order->get_id(),
                    'order_total'  => wc_price($existing_order->get_total()),
                    'name'         => $customer_name,
                    'phone'        => $clean_phone,
                    'address'      => $customer_address,
                    'whatsapp_url' => $whatsapp_url,
                    'message'      => esc_html__('আপনার অর্ডারটি ইতিমধ্যে গ্রহণ করা হয়েছে!', 'kinboni-core'),
                ));
            }
        }

        // Lock against simultaneous requests during order creation
        set_transient($lock_key, 'processing', 15);

        // 2. Determine Unified Dynamic Shipping Cost
        $shipping_info  = Kinboni_Core::calculate_shipping($order_subtotal, $delivery_zone);
        $shipping_cost  = $shipping_info['cost'];
        $shipping_title = $shipping_info['title'];

        try {
            // 3. Create WooCommerce Order
            $order = wc_create_order();

            if ($product_id > 0) {
                // Single Product Fast Buy
                $product = wc_get_product($product_id);
                self::normalize_product_price($product);
                $order->add_product($product, $quantity);
            } else {
                // Cart Checkout
                foreach (WC()->cart->get_cart() as $cart_item) {
                    self::normalize_product_price($cart_item['data']);
                    $order->add_product($cart_item['data'], $cart_item['quantity']);
                }
            }

            // Apply valid promo code if provided
            if (!empty($promo_code)) {
                $coupon_id = wc_get_coupon_id_by_code($promo_code);
                if ($coupon_id) {
                    $coupon = new \WC_Coupon($promo_code);
                    if ($coupon->is_valid()) {
                        $order->apply_coupon($promo_code);
                    }
                }
            }

            // Set Customer Address
            $address = array(
                'first_name' => $customer_name,
                'phone'      => $clean_phone,
                'address_1'  => $customer_address,
                'city'       => ($delivery_zone === 'outside') ? 'Outside Dhaka' : 'Dhaka',
                'country'    => 'BD',
            );
            $order->set_address($address, 'billing');
            $order->set_address($address, 'shipping');

            // Add Shipping Line
            $shipping_item = new WC_Order_Item_Shipping();
            $shipping_item->set_method_title($shipping_title);
            $shipping_item->set_total($shipping_cost);
            $order->add_item($shipping_item);

            // Set Payment Method: Cash on Delivery
            $order->set_payment_method('cod');
            $order->set_payment_method_title(esc_html__('Cash on Delivery (ক্যাশ অন ডেলিভারি)', 'kinboni-core'));

            // Calculate Totals and Set Status
            $order->calculate_totals();
            $order->update_status('processing', esc_html__('Quick 3-Field Cash on Delivery Order', 'kinboni-core'));

            // Set rate limit transient for IP and duplicate order lock (60s)
            set_transient($rate_key, $order_count + 1, 10 * MINUTE_IN_SECONDS);
            set_transient($lock_key, $order->get_id(), 60);

            $order_id    = $order->get_id();
            $order_total = $order->get_total();

            // Trigger order created action & standard WooCommerce checkout hooks
            do_action('kinboni_order_created', $order);
            do_action('woocommerce_checkout_order_processed', $order_id, $address, $order);

            // Clear Cart if cart was used
            if ($product_id === 0 && WC()->cart) {
                WC()->cart->empty_cart();
            }

            // WhatsApp direct link (digits only from option, empty if placeholder or not configured)
            $wa_phone_raw = get_option('kinboni_whatsapp_phone', '8801700000000');
            $wa_phone     = preg_replace('/\D/', '', (string) $wa_phone_raw);
            $whatsapp_url = '';
            if (!empty($wa_phone) && $wa_phone !== '8801700000000') {
                $whatsapp_msg = rawurlencode(sprintf(
                    "Hello Kinboni Atelier! I have placed an order.\nOrder ID: #%s\nName: %s\nPhone: %s\nTotal: ৳%s\nAddress: %s",
                    $order_id,
                    $customer_name,
                    $clean_phone,
                    $order_total,
                    $customer_address
                ));
                $whatsapp_url = "https://wa.me/{$wa_phone}?text={$whatsapp_msg}";
            }

            wp_send_json_success(array(
                'order_id'        => $order_id,
                'order_number'    => '#' . $order_id,
                'order_total'     => wc_price($order_total),
                'order_total_raw' => (float) $order_total,
                'name'         => $customer_name,
                'phone'        => $clean_phone,
                'address'      => $customer_address,
                'whatsapp_url' => $whatsapp_url,
                'message'      => esc_html__('আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে!', 'kinboni-core'),
            ));

        } catch (\Exception $e) {
            delete_transient($lock_key);
            Kinboni_Activity_Logger::error('quick_order', $e->getMessage(), array('trace' => $e->getTraceAsString()));
            wp_send_json_error(array('message' => esc_html__('অর্ডার সম্পন্ন করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।', 'kinboni-core')));
        }
    }
}

