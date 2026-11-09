<?php
/**
 * Kinboni Core Main Class
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Kinboni_Core {

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.5.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Singleton instance
     *
     * @var Kinboni_Core
     */
    private static $_instance = null;

    /**
     * Instance getter
     *
     * @return Kinboni_Core
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Check PHP Version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'admin_notice_minimum_php_version'));
            return;
        }

        // Initialize Plugin
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Initialize after plugins loaded
     */
    public function init() {
        // Load Textdomain
        load_plugin_textdomain('kinboni-core', false, dirname(plugin_basename(KINBONI_CORE_FILE)) . '/languages');

        // Register Custom Product Gallery Image Size
        if (function_exists('add_image_size')) {
            add_image_size('kinboni-product-gallery', 800, 1000, true);
        }

        // Check if WooCommerce is installed and activated
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'admin_notice_missing_woocommerce'));
        }

        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', array($this, 'admin_notice_missing_elementor'));
        } else {
            // Load Elementor Manager
            require_once KINBONI_CORE_PATH . 'includes/class-elementor-manager.php';
            Kinboni_Elementor_Manager::instance();
        }

        // Check if WhatsApp phone is empty or default placeholder
        add_action('admin_notices', array($this, 'admin_notice_placeholder_whatsapp'));
        add_action('admin_notices', array($this, 'admin_notice_missing_bkash_merchant'));

        // Email Retry Hook
        add_action('kinboni_email_retry', array(__CLASS__, 'handle_email_retry'), 10, 5);

        // Load Core Modules
        $this->load_modules();

        // Enqueue Assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 20);

        // Render Modals & Drawers in Footer (Supports both kinboni_footer hook & standard wp_footer)
        add_action('kinboni_footer', array($this, 'render_global_templates'), 5);
        add_action('wp_footer', array($this, 'render_global_templates'), 5);

        // Automated WebP Image Generation & Optimization
        add_filter('image_editor_output_format', array($this, 'optimize_image_formats'));

        // AJAX Search Endpoints
        add_action('wp_ajax_kinboni_ajax_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_kinboni_ajax_search', array($this, 'ajax_search'));

        // AJAX Validate Promo Endpoint
        add_action('wp_ajax_kinboni_validate_promo', array($this, 'ajax_validate_promo'));
        add_action('wp_ajax_nopriv_kinboni_validate_promo', array($this, 'ajax_validate_promo'));

        // AJAX Nonce Refresh Endpoints
        add_action('wp_ajax_kinboni_refresh_nonce', array($this, 'ajax_refresh_nonce'));
        add_action('wp_ajax_nopriv_kinboni_refresh_nonce', array($this, 'ajax_refresh_nonce'));

        // AJAX Submit Review Endpoints
        add_action('wp_ajax_kinboni_submit_review', array($this, 'ajax_submit_review'));
        add_action('wp_ajax_nopriv_kinboni_submit_review', array($this, 'ajax_submit_review'));

        // AJAX Checkout Submit Endpoints
        add_action('wp_ajax_kinboni_checkout_submit', array($this, 'ajax_checkout_submit'));
        add_action('wp_ajax_nopriv_kinboni_checkout_submit', array($this, 'ajax_checkout_submit'));

        // AJAX Patron Account Endpoints
        add_action('wp_ajax_kinboni_login', array($this, 'ajax_login'));
        add_action('wp_ajax_nopriv_kinboni_login', array($this, 'ajax_login'));
        add_action('wp_ajax_kinboni_register', array($this, 'ajax_register'));
        add_action('wp_ajax_nopriv_kinboni_register', array($this, 'ajax_register'));
        add_action('wp_ajax_kinboni_update_profile', array($this, 'ajax_update_profile'));
        add_action('wp_ajax_nopriv_kinboni_update_profile', array($this, 'ajax_update_profile'));
    }

    /**
     * Lightweight AJAX Nonce Refresh Handler
     */
    public function ajax_refresh_nonce() {
        wp_send_json_success(array(
            'nonce' => wp_create_nonce('kinboni_core_nonce'),
        ));
    }

    /**
     * Load Functional Modules
     */
    private function load_modules() {
        $wc_active = class_exists('WooCommerce');

        // 1. Fast 3-Field Cash on Delivery Checkout Engine
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'includes/class-quick-order.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-quick-order.php';
            Kinboni_Quick_Order::instance();
        }

        // 2. Slide-Over Mini-Cart & Free Shipping Progress Engine
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'includes/class-ajax-cart.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-ajax-cart.php';
            Kinboni_Ajax_Cart::instance();
        }

        // 3. Quick View Modal Engine
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'includes/class-quick-view.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-quick-view.php';
            Kinboni_Quick_View::instance();
        }

        // 4. 15s Live Sales Notification Engine
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'includes/class-live-sales.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-live-sales.php';
            Kinboni_Live_Sales::instance();
        }

        // 5. Wishlist Engine
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'includes/class-wishlist.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-wishlist.php';
            Kinboni_Wishlist::instance();
        }

        // 6. Database Installer & Schema Upgrades
        if (file_exists(KINBONI_CORE_PATH . 'includes/class-kinboni-db.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-kinboni-db.php';
            Kinboni_DB::instance();
        }

        // 7. Activity Logger
        if (file_exists(KINBONI_CORE_PATH . 'includes/class-kinboni-activity-logger.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-kinboni-activity-logger.php';
        }

        // Central Widget Content Helper
        if (file_exists(KINBONI_CORE_PATH . 'includes/class-kinboni-content.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-kinboni-content.php';
        }

        // 8. Front-end Real Analytics & Search Tracker
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'includes/class-kinboni-tracker.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-kinboni-tracker.php';
            Kinboni_Tracker::instance();
        }

        // 9. Automated Bangladesh SMS Gateway Engine
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'includes/class-kinboni-sms.php')) {
            require_once KINBONI_CORE_PATH . 'includes/class-kinboni-sms.php';
            Kinboni_SMS::instance();
        }

        // 10. Dedicated KINBONI Pro Admin Management Engine
        if ($wc_active && file_exists(KINBONI_CORE_PATH . 'admin/class-kinboni-admin.php')) {
            require_once KINBONI_CORE_PATH . 'admin/class-kinboni-admin.php';
            Kinboni_Admin::instance();
        }
    }

    /**
     * Enqueue Plugin CSS & JS
     */
    public function enqueue_scripts() {
        // Plugin Custom CSS
        wp_enqueue_style(
            'kinboni-core-style',
            KINBONI_CORE_URL . 'assets/css/kinboni-core.css',
            array(),
            KINBONI_CORE_VERSION
        );

        // Plugin Core JS (Controllers for Quick Order, Cart Drawer, Quick View, Wishlist, Audio Chime)
        $js_deps = array('jquery');
        if (wp_script_is('lucide-icons', 'registered')) {
            $js_deps[] = 'lucide-icons';
        }

        wp_enqueue_script(
            'kinboni-core-js',
            KINBONI_CORE_URL . 'assets/js/kinboni-core.js',
            $js_deps,
            KINBONI_CORE_VERSION,
            true
        );

        // Localize dynamic data to JS
        wp_localize_script('kinboni-core-js', 'kinboniCoreData', array(
            'ajax_url'                => admin_url('admin-ajax.php'),
            'ajaxUrl'                 => admin_url('admin-ajax.php'),
            'nonce'                   => wp_create_nonce('kinboni_core_nonce'),
            'currency_symbol'         => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳',
            'currency_code'           => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'BDT',
            'free_shipping_threshold' => (float) get_option('kinboni_free_shipping_threshold', 2500),
            'fee_inside_dhaka'        => (float) get_option('kinboni_delivery_fee_inside', 70),
            'fee_outside_dhaka'       => (float) get_option('kinboni_delivery_fee_outside', 130),
            'delivery_time_inside'    => sanitize_text_field(get_option('kinboni_delivery_time_inside', '1 - 2 Business Days')),
            'delivery_time_outside'   => sanitize_text_field(get_option('kinboni_delivery_time_outside', '2 - 4 Business Days')),
            'cod_inside_enabled'      => get_option('kinboni_cod_inside_enabled', 'yes') === 'yes',
            'cod_outside_enabled'     => get_option('kinboni_cod_outside_enabled', 'yes') === 'yes',
            'allow_inspection'        => get_option('kinboni_allow_inspection', 'yes') === 'yes',
            'enable_geo_selector'     => get_option('kinboni_enable_geo_selector', 'yes') === 'yes',
            'live_sales_enabled'      => get_option('kinboni_live_sales_enabled', 'yes') === 'yes',
            'live_sales_interval'     => max(5, (int) get_option('kinboni_live_sales_interval', 15)),
            'live_sales_sound'        => get_option('kinboni_live_sales_sound', 'yes') === 'yes',
            'theme_mode_default'      => sanitize_key(get_option('kinboni_theme_mode_default', 'system')),
            'cart_recommendations'    => get_option('kinboni_cart_recommendations', 'yes') === 'yes',
            'recently_viewed_enabled' => get_option('kinboni_recently_viewed_enabled', 'yes') === 'yes',
            'quickview_sizechart'     => get_option('kinboni_quickview_sizechart', 'yes') === 'yes',
            'quickview_delivery_info' => get_option('kinboni_quickview_delivery_info', 'yes') === 'yes',
            'whatsapp_phone'          => sanitize_text_field(get_option('kinboni_whatsapp_phone', '8801700000000')),
            'is_user_logged_in'       => is_user_logged_in(),
            'shop_url'                => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'),
            'checkout_url'            => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/'),
            'cart_url'                => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/'),
            'myaccount_url'           => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'),
            'home_url'                => home_url('/'),
        ));
    }

    /**
     * Get local luxury placeholder SVG URL
     *
     * @return string
     */
    public static function placeholder_img_src() {
        return KINBONI_CORE_URL . 'assets/images/placeholder.svg';
    }

    /**
     * Render Global Drawers and Modals
     */
    public function render_global_templates() {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        $templates = array(
            'drawer-mobile-menu',
            'modal-quick-order',
            'drawer-cart',
            'modal-quick-view',
            'modal-search',
            'drawer-wishlist',
        );
        if (get_option('kinboni_live_sales_enabled', 'yes') === 'yes') {
            $templates[] = 'popup-live-sales';
        }

        foreach ($templates as $template) {
            $template_file = KINBONI_CORE_PATH . 'templates/' . $template . '.php';
            if (file_exists($template_file)) {
                include $template_file;
            }
        }
    }

    /**
     * Auto-convert uploaded image sub-sizes to WebP when supported
     */
    public function optimize_image_formats($formats) {
        if (function_exists('imagewebp') || (extension_loaded('imagick') && class_exists('Imagick') && in_array('WEBP', \Imagick::queryFormats()))) {
            $formats['image/jpeg'] = 'image/webp';
            $formats['image/png']  = 'image/webp';
        }
        return $formats;
    }

    /**
     * AJAX Live Product Search
     */
    public function ajax_search() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');
        $term = isset($_POST['term']) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
        $results = array();

        if (!empty($term) && class_exists('WooCommerce')) {
            $products = wc_get_products(array(
                's'      => $term,
                'limit'  => 8,
                'status' => 'publish',
            ));

            if (class_exists('Kinboni_Tracker')) {
                Kinboni_Tracker::instance()->log_search_query($term, count($products));
            }

            foreach ($products as $product) {
                $terms = get_the_terms($product->get_id(), 'product_cat');
                $cat = ($terms && !is_wp_error($terms)) ? $terms[0]->name : esc_html__('Atelier Collection', 'kinboni-core');
                $image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                if (!$image) {
                    $image = wc_placeholder_img_src('thumbnail');
                }

                $results[] = array(
                    'id'         => $product->get_id(),
                    'name'       => $product->get_name(),
                    'price_html' => $product->get_price_html(),
                    'image'      => $image,
                    'link'       => $product->get_permalink(),
                    'category'   => $cat,
                );
            }
        }

        wp_send_json_success(array('results' => $results));
    }

    /**
     * AJAX Submit Single Product Review
     */
    public function ajax_submit_review() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        // Honeypot check
        if (!empty($_POST['kinboni_hp'])) {
            wp_send_json_error(array('message' => esc_html__('Spam detected.', 'kinboni-core')));
        }

        // Per-IP rate limit (max 3 per hour)
        $ip = sanitize_text_field(self::get_client_ip());
        $rate_key = 'kb_rate_rev_' . md5($ip);
        $review_count = (int) get_transient($rate_key);
        if ($review_count >= 3) {
            wp_send_json_error(array('message' => esc_html__('Too many reviews submitted. Please try again later.', 'kinboni-core')));
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $author     = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
        $city       = isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '';
        $rating     = isset($_POST['rating']) ? absint(wp_unslash($_POST['rating'])) : 5;
        $comment    = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

        if (!$product_id || empty($author) || empty($comment)) {
            wp_send_json_error(array('message' => esc_html__('Please enter your name and review comments.', 'kinboni-core')));
        }

        // Verify product_id is a real 'product' post
        $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
        if (!$product || get_post_type($product_id) !== 'product') {
            wp_send_json_error(array('message' => esc_html__('Invalid product specified.', 'kinboni-core')));
        }

        $user_id = get_current_user_id();
        $email = is_user_logged_in() ? wp_get_current_user()->user_email : ('patron.' . time() . '@kinboni.local');

        // Set comment_approved to 0 unless logged-in user bought the product
        $approved = 0;
        if (is_user_logged_in() && function_exists('wc_customer_bought_product') && wc_customer_bought_product($email, $user_id, $product_id)) {
            $approved = 1;
        }

        $comment_id = wp_insert_comment(array(
            'comment_post_ID'      => $product_id,
            'comment_author'       => $author,
            'comment_author_email' => $email,
            'comment_content'      => $comment,
            'comment_type'         => 'review',
            'comment_parent'       => 0,
            'user_id'              => $user_id,
            'comment_approved'     => $approved,
        ));

        if (!$comment_id) {
            wp_send_json_error(array('message' => esc_html__('Unable to record your review. Please try again.', 'kinboni-core')));
        }

        set_transient($rate_key, $review_count + 1, HOUR_IN_SECONDS);

        $rating = max(1, min(5, $rating));
        update_comment_meta($comment_id, 'rating', $rating);
        if (!empty($city)) {
            update_comment_meta($comment_id, 'city', $city);
        }

        if (class_exists('WC_Comments')) {
            \WC_Comments::clear_transients($product_id);
        }

        $success_msg = ($approved === 1)
            ? esc_html__('Thank you! Your Patron review has been recorded.', 'kinboni-core')
            : esc_html__('Thank you! Your review has been submitted for moderation.', 'kinboni-core');

        $patron_badge = ($approved === 1) ? esc_html__('Verified Patron', 'kinboni-core') : esc_html__('Patron Impression', 'kinboni-core');

        wp_send_json_success(array(
            'message' => $success_msg,
            'review'  => array(
                'id'       => $comment_id,
                'author'   => $author,
                'city'     => !empty($city) ? $city : $patron_badge,
                'rating'   => $rating,
                'date'     => esc_html__('Just now', 'kinboni-core'),
                'comment'  => $comment,
                'approved' => $approved,
            ),
        ));
    }

    /**
     * Handle Full Atelier Checkout Submission via AJAX
     */
    public function ajax_checkout_submit() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        // Honeypot check
        if (!empty($_POST['kinboni_hp'])) {
            wp_send_json_error(array('message' => esc_html__('Spam detected.', 'kinboni-core')));
        }

        // Per-IP rate limit (max 5 orders / 10 min)
        $ip = sanitize_text_field(self::get_client_ip());
        $rate_key = 'kb_rate_order_' . md5($ip);
        $order_count = (int) get_transient($rate_key);
        if ($order_count >= 5) {
            wp_send_json_error(array('message' => esc_html__('Too many checkout attempts. Please try again in 10 minutes.', 'kinboni-core')));
        }

        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce is required for checkout.', 'kinboni-core')));
        }

        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            wp_send_json_error(array('message' => esc_html__('Your shopping bag is currently empty.', 'kinboni-core')));
        }

        $first_name     = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
        $last_name      = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
        $phone          = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $email          = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $city_val       = isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : 'inside';
        $postcode       = isset($_POST['postcode']) ? sanitize_text_field(wp_unslash($_POST['postcode'])) : '';
        $address_1      = isset($_POST['address']) ? sanitize_textarea_field(wp_unslash($_POST['address'])) : '';
        $notes          = isset($_POST['order_notes']) ? sanitize_textarea_field(wp_unslash($_POST['order_notes'])) : '';
        $gift_wrap      = !empty($_POST['gift_wrap']) ? true : false;
        $raw_pay_method = isset($_POST['payment_method']) ? sanitize_text_field(wp_unslash($_POST['payment_method'])) : 'cod';
        $trx_id         = isset($_POST['trx_id']) ? sanitize_text_field(wp_unslash($_POST['trx_id'])) : '';

        // Whitelist payment method to: cod, bkash_nagad, card
        $pay_method = '';
        if ($raw_pay_method === 'cod' || stripos($raw_pay_method, 'COD') !== false || stripos($raw_pay_method, 'Cash') !== false) {
            $pay_method = 'cod';
        } elseif ($raw_pay_method === 'bkash_nagad' || stripos($raw_pay_method, 'bKash') !== false || stripos($raw_pay_method, 'Nagad') !== false) {
            $pay_method = 'bkash_nagad';
        } elseif ($raw_pay_method === 'card' || stripos($raw_pay_method, 'Card') !== false || stripos($raw_pay_method, 'Visa') !== false) {
            $pay_method = 'card';
        }

        if (!in_array($pay_method, array('cod', 'bkash_nagad', 'card'), true)) {
            wp_send_json_error(array('message' => esc_html__('Invalid payment method selected.', 'kinboni-core')));
        }

        // Card option has no gateway - reject with clear error and do not mark paid
        if ($pay_method === 'card') {
            wp_send_json_error(array('message' => esc_html__('Credit / Debit Card payments are currently unavailable. Please choose Cash on Delivery or bKash / Nagad.', 'kinboni-core')));
        }

        // Check COD availability per zone
        if ($pay_method === 'cod') {
            if ($city_val === 'outside' && get_option('kinboni_cod_outside_enabled', 'yes') !== 'yes') {
                wp_send_json_error(array('message' => esc_html__('Cash on Delivery is currently unavailable for Outside Dhaka. Please select bKash / Nagad.', 'kinboni-core')));
            }
            if ($city_val !== 'outside' && get_option('kinboni_cod_inside_enabled', 'yes') !== 'yes') {
                wp_send_json_error(array('message' => esc_html__('Cash on Delivery is currently unavailable for Inside Dhaka. Please select bKash / Nagad.', 'kinboni-core')));
            }
        }

        // bKash/Nagad check merchant configuration & require non-empty TrxID (alphanumeric, 6-20 chars)
        if ($pay_method === 'bkash_nagad') {
            $merchant_number = trim((string) get_option('kinboni_bkash_merchant_number', ''));
            if (empty($merchant_number)) {
                wp_send_json_error(array('message' => esc_html__('bKash / Nagad payment is currently unavailable. Please choose Cash on Delivery.', 'kinboni-core')));
            }
            if (empty($trx_id) || !preg_match('/^[a-zA-Z0-9]{6,20}$/', $trx_id)) {
                wp_send_json_error(array('message' => esc_html__('Please enter a valid Transaction ID (6-20 alphanumeric characters).', 'kinboni-core')));
            }
        }

        // Validation
        if (empty($first_name)) {
            wp_send_json_error(array('message' => esc_html__('Please enter your first name.', 'kinboni-core')));
        }

        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean_phone) === 13 && substr($clean_phone, 0, 2) === '88') {
            $clean_phone = substr($clean_phone, 2);
        }
        if (!preg_match('/^01[3-9]\d{8}$/', $clean_phone)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a valid 11-digit Bangladeshi mobile number (01XXXXXXXXX).', 'kinboni-core')));
        }

        $addr_len = function_exists('mb_strlen') ? mb_strlen($address_1, 'UTF-8') : strlen($address_1);
        if (empty($address_1) || $addr_len < 5) {
            wp_send_json_error(array('message' => esc_html__('Please enter your complete street address.', 'kinboni-core')));
        }

        // Validate EVERY cart item server-side before creating order
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $p   = isset($cart_item['data']) ? $cart_item['data'] : null;
            $qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;

            if (!$p || !is_a($p, 'WC_Product') || !$p->exists()) {
                wp_send_json_error(array('message' => esc_html__('An item in your bag is no longer available.', 'kinboni-core')));
            }

            $p_name = $p->get_name();

            if (!$p->is_purchasable()) {
                wp_send_json_error(array('message' => sprintf(esc_html__('Product "%s" cannot be purchased at this time.', 'kinboni-core'), $p_name)));
            }

            if (!$p->is_in_stock()) {
                wp_send_json_error(array('message' => sprintf(esc_html__('Product "%s" is out of stock.', 'kinboni-core'), $p_name)));
            }

            if (!$p->has_enough_stock($qty)) {
                wp_send_json_error(array('message' => sprintf(esc_html__('Not enough stock available for "%s".', 'kinboni-core'), $p_name)));
            }

            $max_purchase = $p->get_max_purchase_quantity();
            if ($max_purchase > 0 && $qty > $max_purchase) {
                wp_send_json_error(array('message' => sprintf(esc_html__('Maximum purchase quantity exceeded for "%s".', 'kinboni-core'), $p_name)));
            }
        }

        // Duplicate-order protection (60s transient lock)
        $client_ip = self::get_client_ip();
        $cart_sig  = md5(wp_json_encode(array_keys($cart->get_cart())) . '_' . $cart->get_cart_contents_count());
        $lock_key  = 'kb_order_lock_' . md5($client_ip . '_' . $clean_phone . '_' . $cart_sig);
        $locked_id = (int) get_transient($lock_key);

        if ($locked_id > 0) {
            $existing_order = wc_get_order($locked_id);
            if ($existing_order) {
                wp_send_json_success(array(
                    'order_id'        => $locked_id,
                    'order_number'    => '#' . $existing_order->get_order_number(),
                    'date'            => $existing_order->get_date_created() ? date_i18n('M d, Y', strtotime($existing_order->get_date_created())) : current_time('M d, Y'),
                    'total'           => (float) $existing_order->get_total(),
                    'total_formatted' => wc_price($existing_order->get_total()),
                    'payment_method'  => $existing_order->get_payment_method_title(),
                    'message'         => esc_html__('Your order has already been received and confirmed!', 'kinboni-core'),
                ));
            }
        }

        try {
            $order = wc_create_order();

            // Add line items
            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                $pid = $cart_item['product_id'];
                $qty = $cart_item['quantity'];
                $item_id = $order->add_product($cart_item['data'], $qty);
                if (!empty($cart_item['kinboni_monogram'])) {
                    wc_add_order_item_meta($item_id, esc_html__('Bespoke Foil Monogram', 'kinboni-core'), sanitize_text_field($cart_item['kinboni_monogram']));
                }
            }

            // Add applied coupons
            foreach ($cart->get_applied_coupons() as $coupon_code) {
                $order->apply_coupon($coupon_code);
            }

            // Customer billing / shipping
            $city_label = ($city_val === 'outside') ? 'Outside Dhaka' : 'Dhaka';
            $address_data = array(
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'phone'      => $clean_phone,
                'email'      => $email,
                'address_1'  => $address_1,
                'city'       => $city_label,
                'postcode'   => $postcode,
                'country'    => 'BD',
            );
            $order->set_address($address_data, 'billing');
            $order->set_address($address_data, 'shipping');

            // Dynamic Unified Shipping Line
            $subtotal_raw  = (float) $cart->get_subtotal();
            $shipping_info = self::calculate_shipping($subtotal_raw, $city_val);

            $shipping_item = new \WC_Order_Item_Shipping();
            $shipping_item->set_method_title($shipping_info['title']);
            $shipping_item->set_total($shipping_info['cost']);
            $order->add_item($shipping_item);

            // Payment method mapping & order meta
            if ($pay_method === 'bkash_nagad') {
                $order->set_payment_method('bkash_nagad');
                $order->set_payment_method_title('bKash / Nagad Express' . ($trx_id ? ' (TrxID: ' . $trx_id . ')' : ''));
                $order->update_meta_data('_kinboni_trx_id', $trx_id);
                $order->update_meta_data('trx_id', $trx_id);
            } else {
                $order->set_payment_method('cod');
                $cod_title = (get_option('kinboni_allow_inspection', 'yes') === 'yes')
                    ? esc_html__('Cash on Delivery (Pay Upon Inspection)', 'kinboni-core')
                    : esc_html__('Cash on Delivery (COD)', 'kinboni-core');
                $order->set_payment_method_title($cod_title);
            }

            // Customer note
            $final_notes = array();
            if ($gift_wrap) {
                $final_notes[] = esc_html__('[Include Complimentary Silk Ribbon & Wax-Sealed Gift Box]', 'kinboni-core');
            }
            if (!empty($notes)) {
                $final_notes[] = esc_html__('Wax-Sealed Card Inscription: ', 'kinboni-core') . $notes;
            }
            if (!empty($trx_id)) {
                $final_notes[] = esc_html__('Mobile Wallet TrxID: ', 'kinboni-core') . $trx_id;
            }
            if (!empty($final_notes)) {
                $order->set_customer_note(implode("\n", $final_notes));
            }

            $order->calculate_totals();

            // Status handling
            if ($pay_method === 'bkash_nagad') {
                $order->update_status('on-hold', esc_html__('Awaiting manual TrxID verification', 'kinboni-core'));
                $order->add_order_note(esc_html__('Awaiting manual TrxID verification', 'kinboni-core'));
            } else {
                $order->update_status('processing', esc_html__('Atelier Bespoke Order Placed Online', 'kinboni-core'));
            }

            // Set rate limit transient for IP and duplicate order lock (60s)
            set_transient($rate_key, $order_count + 1, 10 * MINUTE_IN_SECONDS);
            set_transient($lock_key, $order->get_id(), 60);

            $order_id = $order->get_id();

            // Trigger order created action & standard WooCommerce checkout hooks
            do_action('kinboni_order_created', $order);
            do_action('woocommerce_checkout_order_processed', $order_id, $address_data, $order);
            do_action('woocommerce_thankyou', $order_id);

            // Empty cart
            $cart->empty_cart();

            $formatted_order_number = '#KB-' . $order_id;
            $order_total = $order->get_total();
            $ord_dt = $order->get_date_created();
            $date_str = $ord_dt ? wp_date('M d, Y', $ord_dt->getTimestamp()) : wp_date('M d, Y');

            wp_send_json_success(array(
                'order_id'        => $order_id,
                'order_number'    => $formatted_order_number,
                'date'            => $date_str,
                'total'           => (float)$order_total,
                'total_formatted' => wc_price($order_total),
                'payment_method'  => $order->get_payment_method_title(),
                'message'         => sprintf(esc_html__('Order %s confirmed successfully!', 'kinboni-core'), $formatted_order_number),
            ));
        } catch (\Exception $e) {
            Kinboni_Activity_Logger::error('checkout', $e->getMessage(), array('trace' => $e->getTraceAsString()));
            wp_send_json_error(array('message' => esc_html__('An error occurred while creating your order. Please try again.', 'kinboni-core')));
        }
    }

    /**
     * Handle AJAX Patron Sign In
     */
    public function ajax_login() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        $ip = sanitize_text_field(self::get_client_ip());
        $rate_key = 'kb_rate_login_' . md5($ip);
        $failed_attempts = (int) get_transient($rate_key);

        if ($failed_attempts >= 5) {
            wp_send_json_error(array('message' => esc_html__('Too many failed login attempts. Please try again in 15 minutes.', 'kinboni-core')));
        }

        $username = isset($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '';
        $password = isset($_POST['password']) ? trim(wp_unslash($_POST['password'])) : '';

        if (empty($username) || empty($password)) {
            wp_send_json_error(array('message' => esc_html__('Please enter your email/username and password.', 'kinboni-core')));
        }

        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon($credentials, false);
        if (is_wp_error($user)) {
            set_transient($rate_key, $failed_attempts + 1, 15 * MINUTE_IN_SECONDS);
            wp_send_json_error(array('message' => esc_html__('Invalid credentials.', 'kinboni-core')));
        }

        delete_transient($rate_key);

        wp_send_json_success(array(
            'message'      => sprintf(esc_html__('Welcome back, %s!', 'kinboni-core'), $user->display_name),
            'name'         => $user->display_name,
            'email'        => $user->user_email,
            'redirect_url' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'),
        ));
    }

    /**
     * Handle AJAX Patron Registration
     */
    public function ajax_register() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        // Only allow if WooCommerce 'woocommerce_enable_myaccount_registration' is 'yes' or WP registration is enabled
        $can_register = (get_option('woocommerce_enable_myaccount_registration') === 'yes' || get_option('users_can_register'));
        if (!$can_register) {
            wp_send_json_error(array('message' => esc_html__('Patron registration is currently closed.', 'kinboni-core')));
        }

        // Per-IP rate limit
        $ip = sanitize_text_field(self::get_client_ip());
        $rate_key = 'kb_rate_reg_' . md5($ip);
        $attempts = (int) get_transient($rate_key);
        if ($attempts >= 5) {
            wp_send_json_error(array('message' => esc_html__('Too many registration attempts. Please try again later.', 'kinboni-core')));
        }

        $name     = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email    = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $phone    = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $password = isset($_POST['password']) ? trim(wp_unslash($_POST['password'])) : '';

        if (empty($name)) {
            wp_send_json_error(array('message' => esc_html__('Please enter your full name.', 'kinboni-core')));
        }
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a valid email address.', 'kinboni-core')));
        }

        // Generic error for existing email (prevents user enumeration)
        if (email_exists($email)) {
            set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);
            wp_send_json_error(array('message' => esc_html__('Unable to complete registration. If you already have an account, please sign in.', 'kinboni-core')));
        }

        $password_generated = false;
        if (empty($password)) {
            $password = wp_generate_password(12, true);
            $password_generated = true;
        }

        $username = sanitize_user(current(explode('@', $email)));
        if (username_exists($username)) {
            $username = $username . '_' . wp_rand(100, 999);
        }

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }

        set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);

        wp_update_user(array(
            'ID'           => $user_id,
            'display_name' => $name,
            'first_name'   => $name,
        ));

        if (!empty($phone)) {
            update_user_meta($user_id, 'billing_phone', $phone);
        }

        if ($password_generated) {
            wp_new_user_notification($user_id, null, 'user');
        }

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        wp_send_json_success(array(
            'message'      => sprintf(esc_html__('Welcome to The Kinboni Circle, %s!', 'kinboni-core'), $name),
            'name'         => $name,
            'email'        => $email,
            'redirect_url' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'),
        ));
    }

    /**
     * Handle AJAX Profile Update
     */
    public function ajax_update_profile() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => esc_html__('You must be logged in to update your profile.', 'kinboni-core')));
        }

        $user_id = get_current_user_id();
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error(array('message' => esc_html__('User account not found.', 'kinboni-core')));
        }

        $name             = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email            = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $phone            = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $address          = isset($_POST['address']) ? sanitize_textarea_field(wp_unslash($_POST['address'])) : '';
        $password         = isset($_POST['password']) ? trim(wp_unslash($_POST['password'])) : '';
        $current_password = isset($_POST['current_password']) ? trim(wp_unslash($_POST['current_password'])) : '';

        $is_changing_email = (!empty($email) && $email !== $user->user_email);
        $is_changing_pass  = !empty($password);

        // Require current password when changing email or password
        if ($is_changing_email || $is_changing_pass) {
            if (empty($current_password) || !wp_check_password($current_password, $user->user_pass, $user_id)) {
                wp_send_json_error(array('message' => esc_html__('Current password is required and must be correct to change your email or password.', 'kinboni-core')));
            }
        }

        // Check email_exists for other users
        if ($is_changing_email) {
            if (!is_email($email)) {
                wp_send_json_error(array('message' => esc_html__('Please enter a valid email address.', 'kinboni-core')));
            }
            $existing_user_id = email_exists($email);
            if ($existing_user_id && (int) $existing_user_id !== (int) $user_id) {
                wp_send_json_error(array('message' => esc_html__('This email address is already in use by another account.', 'kinboni-core')));
            }
        }

        $update_data = array('ID' => $user_id);
        if (!empty($name)) {
            $update_data['display_name'] = $name;
            $update_data['first_name']   = $name;
        }
        if ($is_changing_email) {
            $update_data['user_email'] = $email;
        }
        if ($is_changing_pass) {
            $update_data['user_pass'] = $password;
        }

        $result = wp_update_user($update_data);
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        if (!empty($phone)) {
            update_user_meta($user_id, 'billing_phone', $phone);
        }
        if (!empty($address)) {
            update_user_meta($user_id, 'billing_address_1', $address);
        }

        wp_send_json_success(array(
            'message' => esc_html__('Patron profile details updated successfully.', 'kinboni-core'),
            'name'    => $name ?: $user->display_name,
            'email'   => $email ?: $user->user_email,
            'phone'   => $phone,
            'address' => $address,
        ));
    }

    /**
     * Admin Notice: Minimum PHP Version
     */
    public function admin_notice_minimum_php_version() {
        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'kinboni-core'),
            '<strong>' . esc_html__('Kinboni Core & Elementor Addons', 'kinboni-core') . '</strong>',
            '<strong>' . esc_html__('PHP', 'kinboni-core') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin Notice: Missing WooCommerce
     */
    public function admin_notice_missing_woocommerce() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: WooCommerce */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated for checkout and store modules to function.', 'kinboni-core'),
            '<strong>' . esc_html__('Kinboni Core & Elementor Addons', 'kinboni-core') . '</strong>',
            '<strong>' . esc_html__('WooCommerce', 'kinboni-core') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin Notice: Missing Elementor
     */
    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated for its custom widgets to display.', 'kinboni-core'),
            '<strong>' . esc_html__('Kinboni Core & Elementor Addons', 'kinboni-core') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'kinboni-core') . '</strong>'
        );
        printf('<div class="notice notice-info is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin Notice: Placeholder WhatsApp Phone
     */
    public function admin_notice_placeholder_whatsapp() {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        $phone = get_option('kinboni_whatsapp_phone', '8801700000000');
        $clean = preg_replace('/\D/', '', (string) $phone);
        if (empty($clean) || $clean === '8801700000000') {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('Kinboni: please set your real WhatsApp number in Storefront settings.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Admin Notice: Missing bKash / Nagad Merchant Number
     */
    public function admin_notice_missing_bkash_merchant() {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        $merchant = trim((string) get_option('kinboni_bkash_merchant_number', ''));
        if (empty($merchant)) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('Kinboni: Please set your real bKash / Nagad merchant number in Storefront settings to enable mobile wallet payments.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * AJAX endpoint to validate promotional coupon for quick-order / modal
     */
    public function ajax_validate_promo() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        $code = isset($_POST['promo_code']) ? sanitize_text_field(wp_unslash($_POST['promo_code'])) : '';
        $code = strtoupper(trim($code));

        if (empty($code)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a privilege voucher code.', 'kinboni-core')));
        }

        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce is required for privilege vouchers.', 'kinboni-core')));
        }

        // If code is VIP coupon, make sure it exists
        $vip_code = sanitize_text_field(get_option('kinboni_vip_coupon_code', 'LUXE20'));
        if ($code === strtoupper($vip_code)) {
            self::ensure_vip_coupon();
        }

        $coupon_id = wc_get_coupon_id_by_code($code);
        if (!$coupon_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid or expired privilege voucher.', 'kinboni-core')));
        }

        $coupon = new \WC_Coupon($code);
        if (!$coupon->is_valid()) {
            wp_send_json_error(array('message' => esc_html__('Invalid or expired privilege voucher.', 'kinboni-core')));
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $qty        = isset($_POST['quantity']) ? max(1, absint(wp_unslash($_POST['quantity']))) : 1;
        $price      = 0.0;

        if ($product_id > 0) {
            $p = wc_get_product($product_id);
            if ($p) {
                $price = (float) $p->get_price() * $qty;
            }
        } elseif (WC()->cart) {
            $price = (float) WC()->cart->get_subtotal();
        }

        $discount_type   = $coupon->get_discount_type();
        $discount_amount = (float) $coupon->get_amount();
        $calculated_disc = 0.0;

        if ($discount_type === 'percent') {
            $calculated_disc = round(($price * $discount_amount) / 100);
        } else {
            $calculated_disc = min($price, $discount_amount);
        }

        wp_send_json_success(array(
            'code'               => $code,
            'discount_type'      => $discount_type,
            'coupon_amount'      => $discount_amount,
            'discount'           => $calculated_disc,
            'discount_formatted' => wc_price($calculated_disc),
            'message'            => sprintf(esc_html__('Privilege voucher "%s" applied!', 'kinboni-core'), $code),
        ));
    }

    /**
     * Unified Shipping Calculation Helper
     *
     * @param float  $subtotal Order subtotal
     * @param string $zone     Delivery zone ('inside' or 'outside')
     * @return array Array with cost, is_free, and title
     */
    public static function calculate_shipping($subtotal, $zone = 'inside') {
        $threshold   = (float) get_option('kinboni_free_shipping_threshold', 2500);
        $fee_inside  = (float) get_option('kinboni_delivery_fee_inside', 70);
        $fee_outside = (float) get_option('kinboni_delivery_fee_outside', 130);

        $is_free = ($threshold > 0 && (float)$subtotal >= $threshold);
        $cost    = $is_free ? 0.0 : (($zone === 'outside') ? $fee_outside : $fee_inside);
        $title   = $is_free
            ? esc_html__('Complimentary White-Glove Express Shipping', 'kinboni-core')
            : (($zone === 'outside')
                ? sprintf(esc_html__('Standard Delivery (Outside Dhaka ৳%s)', 'kinboni-core'), $fee_outside)
                : sprintf(esc_html__('Standard Delivery (Inside Dhaka ৳%s)', 'kinboni-core'), $fee_inside));

        return array(
            'cost'    => (float) $cost,
            'is_free' => (bool) $is_free,
            'title'   => $title,
        );
    }

    /**
     * Ensure the VIP Privilege Coupon exists in WooCommerce
     *
     * @return string|false The coupon code if valid/created, or false on failure
     */
    public static function ensure_vip_coupon() {
        if (!class_exists('WooCommerce')) {
            return false;
        }

        $code = sanitize_text_field(get_option('kinboni_vip_coupon_code', 'LUXE20'));
        if (empty($code)) {
            $code = 'LUXE20';
        }

        $coupon_id = wc_get_coupon_id_by_code($code);

        if (!$coupon_id) {
            try {
                $coupon = new \WC_Coupon();
                $coupon->set_code($code);
                $coupon->set_description(esc_html__('Kinboni VIP Circle Private 20% Privilege Voucher', 'kinboni-core'));
                $coupon->set_discount_type('percent');
                $coupon->set_amount(20);
                $coupon->set_individual_use(true);
                $coupon->set_usage_limit_per_user(1);
                $coupon->set_status('publish');
                $coupon_id = $coupon->save();
            } catch (\Exception $e) {
                Kinboni_Activity_Logger::error('vip_coupon', 'Failed to create VIP coupon: ' . $e->getMessage(), array('code' => $code));
                return false;
            }
        }

        if ($coupon_id && get_post_status($coupon_id) === 'publish') {
            return $code;
        }

        return false;
    }

    /**
     * Robust email sender with return check, logging, and scheduled retry
     *
     * @param string|array $to Destination email(s)
     * @param string       $subject Email subject
     * @param string       $body Email content (HTML or text)
     * @param array|string $headers Headers
     * @param string       $context Context for logging
     * @return bool
     */
    public static function send_mail($to, $subject, $body, $headers = array(), $context = 'general') {
        $headers_arr = (array) $headers;
        if (empty($headers_arr)) {
            $headers_arr = array('Content-Type: text/html; charset=UTF-8');
        }

        $sent = wp_mail($to, $subject, $body, $headers_arr);

        if (!$sent) {
            Kinboni_Activity_Logger::error('email', 'Email delivery failed via wp_mail', array(
                'to'      => is_array($to) ? implode(', ', $to) : $to,
                'subject' => $subject,
                'context' => $context,
            ));

            $to_str     = is_array($to) ? implode(',', $to) : $to;
            $email_hash = md5($to_str . $subject);
            $retry_key  = 'kb_mail_retry_' . $email_hash;
            $retries    = (int) get_transient($retry_key);

            if ($retries < 2) {
                set_transient($retry_key, $retries + 1, 2 * HOUR_IN_SECONDS);
                wp_schedule_single_event(time() + 15 * MINUTE_IN_SECONDS, 'kinboni_email_retry', array(
                    'to'      => $to,
                    'subject' => $subject,
                    'body'    => $body,
                    'headers' => $headers_arr,
                    'context' => $context,
                ));
            }
            return false;
        }

        return true;
    }

    /**
     * Handle scheduled email retry event
     */
    public static function handle_email_retry($to, $subject, $body, $headers, $context) {
        self::send_mail($to, $subject, $body, $headers, $context);
    }

    /**
     * Get validated client IP address with Cloudflare and proxy support
     *
     * @return string
     */
    public static function get_client_ip() {
        $ip = '';
        $trust_proxies = (get_option('kinboni_trust_proxy_headers', 'no') === 'yes' || apply_filters('kinboni_trust_proxy_headers', false));

        if ($trust_proxies) {
            if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $cf_ip = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
                if (filter_var($cf_ip, FILTER_VALIDATE_IP)) {
                    $ip = $cf_ip;
                }
            }

            if (empty($ip) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwarded = array_reverse(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
                foreach ($forwarded as $f_ip) {
                    $f_ip = trim($f_ip);
                    if (filter_var($f_ip, FILTER_VALIDATE_IP)) {
                        $ip = $f_ip;
                        break;
                    }
                }
            }
        }

        if (empty($ip) && !empty($_SERVER['REMOTE_ADDR'])) {
            $remote_ip = trim($_SERVER['REMOTE_ADDR']);
            if (filter_var($remote_ip, FILTER_VALIDATE_IP)) {
                $ip = $remote_ip;
            }
        }

        if (empty($ip)) {
            $ip = '0.0.0.0';
        }

        return apply_filters('kinboni_client_ip', $ip);
    }

    /**
     * Sanitize cell string against CSV / Excel Formula Injection (=, +, -, @, Tab, CR)
     *
     * @param mixed $val
     * @return string
     */
    public static function sanitize_csv_cell($val) {
        if ($val === null || is_bool($val)) {
            return '';
        }
        if (is_int($val) || is_float($val)) {
            return (string) $val;
        }
        $str = (string) $val;
        if ($str !== '' && in_array($str[0], array('=', '+', '-', '@', "	", ""), true)) {
            // Allow negative numbers like -50.00
            if ($str[0] === '-' && is_numeric($str)) {
                return $str;
            }
            return "'" . $str;
        }
        return $str;
    }

    /**
     * Resolve product image URL with comprehensive fallback chain
     * Matches WooCommerce theme 'kinboni-product-card' (600x750) 4:5 ratio
     *
     * @param WC_Product|int $product Product object or Product ID
     * @param string $size Default 'kinboni-product-card'
     * @return string Image URL
     */
    public static function get_product_card_image($product, $size = 'kinboni-product-card') {
        if (is_numeric($product)) {
            $product = function_exists('wc_get_product') ? wc_get_product($product) : null;
        }

        if (!$product || !is_a($product, 'WC_Product')) {
            return function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src($size) : '';
        }

        $image_id = $product->get_image_id();

        // 1. If no main image, try first gallery image
        if (!$image_id) {
            $gallery = $product->get_gallery_image_ids();
            if (!empty($gallery)) {
                $image_id = $gallery[0];
            }
        }

        // 2. If still no image and it's variable, check first child variation with an image
        if (!$image_id && $product->is_type('variable')) {
            $children = $product->get_children();
            if (!empty($children)) {
                foreach ($children as $child_id) {
                    $child_img = get_post_thumbnail_id($child_id);
                    if ($child_img) {
                        $image_id = $child_img;
                        break;
                    }
                }
            }
        }

        // 3. Resolve attachment URL across available sizes
        if ($image_id) {
            // Requested size first (e.g. kinboni-product-card)
            $url = wp_get_attachment_image_url($image_id, $size);
            if ($url) {
                return $url;
            }

            // Fallback sizes in order of preference
            $fallback_sizes = array('woocommerce_single', 'medium_large', 'large', 'woocommerce_thumbnail', 'medium', 'full');
            foreach ($fallback_sizes as $fb_size) {
                $url = wp_get_attachment_image_url($image_id, $fb_size);
                if ($url) {
                    return $url;
                }
            }

            $url = wp_get_attachment_url($image_id);
            if ($url) {
                return $url;
            }
        }

        // 4. Default WooCommerce placeholder
        return function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src($size) : '';
    }

    /**
     * Resolve secondary / hover image URL for product cards
     *
     * @param WC_Product|int $product
     * @param string $main_image_url
     * @param string $size Default 'kinboni-product-card'
     * @return string Hover image URL or main image URL
     */
    public static function get_product_hover_image($product, $main_image_url = '', $size = 'kinboni-product-card') {
        if (is_numeric($product)) {
            $product = function_exists('wc_get_product') ? wc_get_product($product) : null;
        }

        if (!$product || !is_a($product, 'WC_Product')) {
            return $main_image_url;
        }

        $gallery = $product->get_gallery_image_ids();
        if (!empty($gallery)) {
            $hover_id = $gallery[0];
            $url = wp_get_attachment_image_url($hover_id, $size);
            if (!$url) {
                $fallback_sizes = array('woocommerce_single', 'medium_large', 'large', 'woocommerce_thumbnail', 'medium', 'full');
                foreach ($fallback_sizes as $fb_size) {
                    $url = wp_get_attachment_image_url($hover_id, $fb_size);
                    if ($url) break;
                }
            }
            if (!$url) {
                $url = wp_get_attachment_url($hover_id);
            }
            if ($url) {
                return $url;
            }
        }

        return $main_image_url;
    }
}

