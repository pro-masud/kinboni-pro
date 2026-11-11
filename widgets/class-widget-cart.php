<?php
/**
 * Kinboni Shopping Bag & Cart Showcase - Elementor Widget
 *
 * Implements the luxury editorial Shopping Bag & Cart Archive page matching cart.html
 * featuring real-time Free Shipping Progress, Cart Items Table with Quantity & Monogram controls,
 * Privilege Coupon Code application, Dynamic Cross-Sell Recommendations, Shipping Zone switchers,
 * Cart Totals breakdown, and 3-Field Quick COD Modal integration.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;

class Kinboni_Widget_Cart extends Widget_Base {

    public function get_name() {
        return 'kinboni_cart';
    }

    public function get_title() {
        return esc_html__('Shopping Bag & Cart Showcase', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-cart';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('cart', 'shopping bag', 'bag', 'woocommerce', 'checkout', 'quick order', 'kinboni');
    }

    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: SECTION HEADER
        // ==========================================
        $this->start_controls_section(
            'section_header_content',
            array(
                'label' => esc_html__('Header & Breadcrumb', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_header',
            array(
                'label'        => esc_html__('Show Header', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'breadcrumb_home_text',
            array(
                'label'       => esc_html__('Breadcrumb Home Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Home',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'breadcrumb_cart_text',
            array(
                'label'       => esc_html__('Breadcrumb Cart Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Shopping Bag',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'pre_title',
            array(
                'label'       => esc_html__('Subtitle / Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Your Selection',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'title',
            array(
                'label'       => esc_html__('Page Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Shopping Bag',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'continue_shopping_text',
            array(
                'label'       => esc_html__('Continue Shopping Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Continue Shopping',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'continue_shopping_url',
            array(
                'label'       => esc_html__('Continue Shopping Custom URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('Leave empty for default shop page', 'kinboni-core'),
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: FREE SHIPPING PROGRESS BANNER
        // ==========================================
        $this->start_controls_section(
            'section_shipping_banner_content',
            array(
                'label' => esc_html__('Free Shipping Progress Banner', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_shipping_banner',
            array(
                'label'        => esc_html__('Show Free Shipping Banner', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'shipping_threshold',
            array(
                'label'       => esc_html__('Free Shipping Threshold (৳)', 'kinboni-core'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 2500,
                'min'         => 0,
                'step'        => 50,
                'condition'   => array('show_shipping_banner' => 'yes'),
            )
        );

        $this->add_control(
            'shipping_banner_text',
            array(
                'label'       => esc_html__('Progress Message', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Add <strong>{diff}</strong> more to unlock Complimentary White-Glove Express Shipping',
                'description' => esc_html__('Use {diff} for remaining amount and {threshold} for target threshold.', 'kinboni-core'),
                'condition'   => array('show_shipping_banner' => 'yes'),
            )
        );

        $this->add_control(
            'shipping_unlocked_text',
            array(
                'label'       => esc_html__('Unlocked Message', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '🎉 Complimentary White-Glove Express Shipping Unlocked!',
                'condition'   => array('show_shipping_banner' => 'yes'),
            )
        );

        $this->add_control(
            'shipping_threshold_badge',
            array(
                'label'       => esc_html__('Threshold Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Threshold: {threshold}',
                'condition'   => array('show_shipping_banner' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CART EMPTY STATE
        // ==========================================
        $this->start_controls_section(
            'section_empty_cart_content',
            array(
                'label' => esc_html__('Empty Cart State', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'empty_title',
            array(
                'label'       => esc_html__('Empty Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Your Shopping Bag is Currently Empty',
            )
        );

        $this->add_control(
            'empty_description',
            array(
                'label'       => esc_html__('Empty Description', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => 'Explore our horological archive, Tuscan vegetable-tanned leather goods, and artisanal extraits to curate your bag.',
            )
        );

        $this->add_control(
            'empty_btn_text',
            array(
                'label'       => esc_html__('Empty Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Return to Shop Archive',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: TABLE & COUPONS
        // ==========================================
        $this->start_controls_section(
            'section_table_content',
            array(
                'label' => esc_html__('Table & Coupons', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'coupon_placeholder',
            array(
                'label'       => esc_html__('Coupon Input Placeholder', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Privilege code (try LUXE20)',
            )
        );

        $this->add_control(
            'coupon_btn_text',
            array(
                'label'       => esc_html__('Coupon Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Apply Coupon',
            )
        );

        $this->add_control(
            'clear_btn_text',
            array(
                'label'       => esc_html__('Clear Bag Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Clear Bag',
            )
        );

        $this->add_control(
            'update_btn_text',
            array(
                'label'       => esc_html__('Update Cart Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Update Cart',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CROSS-SELL RECOMMENDATIONS
        // ==========================================
        $this->start_controls_section(
            'section_cross_sells_content',
            array(
                'label' => esc_html__('Cross-Sell Recommendations', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_cross_sells',
            array(
                'label'        => esc_html__('Show Cross-Sells', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'cross_sells_pre_title',
            array(
                'label'       => esc_html__('Cross-Sell Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Atelier Cross-Sell',
                'condition'   => array('show_cross_sells' => 'yes'),
            )
        );

        $this->add_control(
            'cross_sells_title',
            array(
                'label'       => esc_html__('Cross-Sell Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Frequently Acquired Together',
                'condition'   => array('show_cross_sells' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CART TOTALS & DISPATCH
        // ==========================================
        $this->start_controls_section(
            'section_totals_content',
            array(
                'label' => esc_html__('Cart Totals & Dispatch', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'totals_title',
            array(
                'label'       => esc_html__('Totals Card Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Cart Totals',
            )
        );

        $this->add_control(
            'subtotal_label',
            array(
                'label'       => esc_html__('Subtotal Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Subtotal',
            )
        );

        $this->add_control(
            'discount_label',
            array(
                'label'       => esc_html__('Privilege Discount Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Privilege Discount',
            )
        );

        $this->add_control(
            'shipping_title',
            array(
                'label'       => esc_html__('Shipping Section Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Shipping & Dispatch',
            )
        );

        $this->add_control(
            'fee_inside_label',
            array(
                'label'       => esc_html__('Inside Dhaka Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Inside Dhaka (1-2 Days)',
            )
        );

        $this->add_control(
            'fee_inside_amount',
            array(
                'label'       => esc_html__('Inside Dhaka Fee (৳)', 'kinboni-core'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 60,
                'min'         => 0,
            )
        );

        $this->add_control(
            'fee_outside_label',
            array(
                'label'       => esc_html__('Outside Dhaka Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Outside Dhaka (2-4 Days)',
            )
        );

        $this->add_control(
            'fee_outside_amount',
            array(
                'label'       => esc_html__('Outside Dhaka Fee (৳)', 'kinboni-core'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 120,
                'min'         => 0,
            )
        );

        $this->add_control(
            'total_label',
            array(
                'label'       => esc_html__('Total Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Estimated Total',
            )
        );

        $this->add_control(
            'checkout_btn_text',
            array(
                'label'       => esc_html__('Checkout Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Proceed to Checkout Page',
            )
        );

        $this->add_control(
            'show_quick_cod_btn',
            array(
                'label'        => esc_html__('Show Quick COD Modal Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'quick_cod_btn_text',
            array(
                'label'       => esc_html__('Quick COD Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '3-Field Quick COD Modal',
                'condition'   => array('show_quick_cod_btn' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: AMBIENT GLOW & ARCHES
        // ==========================================
        $this->start_controls_section(
            'section_style_ambient',
            array(
                'label' => esc_html__('Ambient Lighting & Geometry', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'show_ambient_glow',
            array(
                'label'        => esc_html__('Show Ambient Glow & Patterns', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_arch_shapes',
            array(
                'label'        => esc_html__('Show Architectural Arches & Circles', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: TYPOGRAPHY
        // ==========================================
        $this->start_controls_section(
            'section_style_typography',
            array(
                'label' => esc_html__('Typography & Gold Accents', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'page_title_typography',
                'label'    => esc_html__('Page Title Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} #page-view-cart h1',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'totals_title_typography',
                'label'    => esc_html__('Totals Title Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} .cart_totals h2',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $show_header           = !empty($settings['show_header']) && $settings['show_header'] === 'yes';
        $breadcrumb_home       = !empty($settings['breadcrumb_home_text']) ? esc_html($settings['breadcrumb_home_text']) : 'Home';
        $breadcrumb_cart       = !empty($settings['breadcrumb_cart_text']) ? esc_html($settings['breadcrumb_cart_text']) : 'Shopping Bag';
        $pre_title             = !empty($settings['pre_title']) ? esc_html($settings['pre_title']) : 'Your Selection';
        $title                 = !empty($settings['title']) ? esc_html($settings['title']) : 'Shopping Bag';
        $continue_text         = !empty($settings['continue_shopping_text']) ? esc_html($settings['continue_shopping_text']) : 'Continue Shopping';
        $continue_url          = !empty($settings['continue_shopping_url']['url']) ? esc_url($settings['continue_shopping_url']['url']) : (function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('shop')) : 'shop.html');

        $show_shipping_banner  = !empty($settings['show_shipping_banner']) && $settings['show_shipping_banner'] === 'yes';
        $threshold             = class_exists('Kinboni_Content') ? (float) Kinboni_Content::resolve($settings, 'shipping_threshold', 'free_shipping_threshold', 2500, $this) : (!empty($settings['shipping_threshold']) ? absint($settings['shipping_threshold']) : 2500);
        $banner_progress_tmpl  = !empty($settings['shipping_banner_text']) ? $settings['shipping_banner_text'] : 'Add <strong>{diff}</strong> more to unlock Complimentary White-Glove Express Shipping';
        $banner_unlocked_tmpl  = !empty($settings['shipping_unlocked_text']) ? esc_html($settings['shipping_unlocked_text']) : '🎉 Complimentary White-Glove Express Shipping Unlocked!';
        $threshold_badge_tmpl  = !empty($settings['shipping_threshold_badge']) ? $settings['shipping_threshold_badge'] : 'Threshold: {threshold}';

        $empty_title           = !empty($settings['empty_title']) ? esc_html($settings['empty_title']) : 'Your Shopping Bag is Currently Empty';
        $empty_desc            = !empty($settings['empty_description']) ? esc_html($settings['empty_description']) : 'Explore our horological archive, Tuscan vegetable-tanned leather goods, and artisanal extraits to curate your bag.';
        $empty_btn_text        = !empty($settings['empty_btn_text']) ? esc_html($settings['empty_btn_text']) : 'Return to Shop Archive';

        $vip_coupon_code       = class_exists('Kinboni_Content') ? Kinboni_Content::get('vip_coupon_code', 'LUXE20') : 'LUXE20';
        $default_coupon_ph     = sprintf('Privilege code (try %s)', $vip_coupon_code);
        $coupon_placeholder    = class_exists('Kinboni_Content') ? esc_attr(Kinboni_Content::resolve($settings, 'coupon_placeholder', 'coupon_placeholder', $default_coupon_ph, $this)) : (!empty($settings['coupon_placeholder']) ? esc_attr($settings['coupon_placeholder']) : $default_coupon_ph);
        $coupon_btn_text       = !empty($settings['coupon_btn_text']) ? esc_html($settings['coupon_btn_text']) : 'Apply Coupon';
        $clear_btn_text        = !empty($settings['clear_btn_text']) ? esc_html($settings['clear_btn_text']) : 'Clear Bag';
        $update_btn_text       = !empty($settings['update_btn_text']) ? esc_html($settings['update_btn_text']) : 'Update Cart';

        $show_cross_sells      = (!empty($settings['show_cross_sells']) && $settings['show_cross_sells'] === 'yes') && (get_option('kinboni_cart_recommendations', 'yes') === 'yes');
        $cross_sells_pre       = !empty($settings['cross_sells_pre_title']) ? esc_html($settings['cross_sells_pre_title']) : 'Atelier Cross-Sell';
        $cross_sells_title     = !empty($settings['cross_sells_title']) ? esc_html($settings['cross_sells_title']) : 'Frequently Acquired Together';

        $totals_title          = !empty($settings['totals_title']) ? esc_html($settings['totals_title']) : 'Cart Totals';
        $subtotal_label        = !empty($settings['subtotal_label']) ? esc_html($settings['subtotal_label']) : 'Subtotal';
        $discount_label        = !empty($settings['discount_label']) ? esc_html($settings['discount_label']) : 'Privilege Discount';
        $shipping_title        = !empty($settings['shipping_title']) ? esc_html($settings['shipping_title']) : 'Shipping & Dispatch';
        $fee_inside_label      = !empty($settings['fee_inside_label']) ? esc_html($settings['fee_inside_label']) : 'Inside Dhaka (1-2 Days)';
        $fee_inside            = class_exists('Kinboni_Content') ? (float) Kinboni_Content::resolve($settings, 'fee_inside_amount', 'delivery_fee_inside', 70, $this) : (isset($settings['fee_inside_amount']) ? (float)$settings['fee_inside_amount'] : 70);
        $fee_outside_label     = !empty($settings['fee_outside_label']) ? esc_html($settings['fee_outside_label']) : 'Outside Dhaka (2-4 Days)';
        $fee_outside           = class_exists('Kinboni_Content') ? (float) Kinboni_Content::resolve($settings, 'fee_outside_amount', 'delivery_fee_outside', 130, $this) : (isset($settings['fee_outside_amount']) ? (float)$settings['fee_outside_amount'] : 130);
        $total_label           = !empty($settings['total_label']) ? esc_html($settings['total_label']) : 'Estimated Total';
        $checkout_btn_text     = !empty($settings['checkout_btn_text']) ? esc_html($settings['checkout_btn_text']) : 'Proceed to Checkout Page';
        $show_quick_cod        = !empty($settings['show_quick_cod_btn']) && $settings['show_quick_cod_btn'] === 'yes';
        $quick_cod_btn_text    = !empty($settings['quick_cod_btn_text']) ? esc_html($settings['quick_cod_btn_text']) : '3-Field Quick COD Modal';

        $show_ambient          = !empty($settings['show_ambient_glow']) && $settings['show_ambient_glow'] === 'yes';
        $show_arches           = !empty($settings['show_arch_shapes']) && $settings['show_arch_shapes'] === 'yes';

        $home_url              = home_url('/');
        $checkout_url          = (function_exists('wc_get_checkout_url') && wc_get_checkout_url() && wc_get_checkout_url() !== home_url('/')) ? wc_get_checkout_url() : home_url('/checkout/');
        $currency_symbol       = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';

        // Retrieve Live WooCommerce Cart
        $cart = (function_exists('WC') && WC()->cart) ? WC()->cart : null;
        $is_empty = (!$cart || $cart->is_empty());
        $subtotal_raw = $cart ? (float)$cart->get_subtotal() : 0;
        $discount_raw = $cart ? (float)$cart->get_discount_total() : 0;
        $is_free_shipping = ($subtotal_raw >= $threshold);
        $diff_raw = max(0, $threshold - $subtotal_raw);
        $progress_pct = min(100, round(($subtotal_raw / $threshold) * 100));

        $shipping_fee = $is_free_shipping ? 0 : $fee_inside;
        $final_total_raw = max(0, $subtotal_raw - $discount_raw + $shipping_fee);

        $applied_coupons = $cart ? $cart->get_applied_coupons() : array();
        $in_cart_product_ids = array();
        $cart_items_data = array();

        if ($cart && !$is_empty) {
            foreach ($cart->get_cart() as $key => $item) {
                $p = $item['data'];
                if ($p && $p->exists() && $item['quantity'] > 0) {
                    $pid = $item['product_id'];
                    $in_cart_product_ids[] = $pid;
                    $img_id = $p->get_image_id();
                    $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'medium') : wc_placeholder_img_src();
                    $cats = get_the_terms($pid, 'product_cat');
                    $cat_label = (!empty($cats) && !is_wp_error($cats)) ? esc_html($cats[0]->name) : 'Atelier Piece';

                    $cart_items_data[] = array(
                        'key'             => $key,
                        'id'              => $pid,
                        'name'            => $p->get_name(),
                        'category'        => $cat_label,
                        'image'           => $img_url,
                        'price'           => (float)$p->get_price(),
                        'price_formatted' => wc_price($p->get_price()),
                        'quantity'        => $item['quantity'],
                        'subtotal'        => (float)$p->get_price() * $item['quantity'],
                        'subtotal_formatted' => wc_price((float)$p->get_price() * $item['quantity']),
                        'monogram'        => !empty($item['kinboni_monogram']) ? $item['kinboni_monogram'] : '',
                        'url'             => get_permalink($pid),
                    );
                }
            }
        }

        // Cross-sells retrieval: WooCommerce Cross-Sells or fallback to published items
        $cross_sells = array();
        $cross_sell_ids = $cart ? $cart->get_cross_sells() : array();
        if (empty($cross_sell_ids)) {
            $cross_prods = wc_get_products(array(
                'status'  => 'publish',
                'limit'   => 4,
                'orderby' => 'popularity',
                'order'   => 'DESC',
                'exclude' => $in_cart_product_ids,
            ));
            foreach ($cross_prods as $cp) {
                $cross_sell_ids[] = $cp->get_id();
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
                    'price'            => (float)$cs_prod->get_price(),
                    'price_formatted'  => wc_price($cs_prod->get_price()),
                    'url'              => get_permalink($cs_id),
                );
            }
        }

        // Format shipping banner text
        $formatted_diff_str = wc_price($diff_raw);
        $formatted_thresh_str = wc_price($threshold);
        $shipping_text_rendered = $is_free_shipping 
            ? $banner_unlocked_tmpl 
            : str_replace(array('{diff}', '{threshold}'), array($formatted_diff_str, $formatted_thresh_str), $banner_progress_tmpl);
        $threshold_badge_rendered = str_replace('{threshold}', $formatted_thresh_str, $threshold_badge_tmpl);
        ?>
        <main id="page-view-cart" class="wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]">
            
            <?php if ($show_ambient) : ?>
            <div class="absolute inset-0 bg-gradient-to-b from-gold-200/35 via-transparent to-transparent dark:from-gold-900/15 pointer-events-none"></div>
            <div class="absolute inset-0 opacity-35 dark:opacity-20 bg-[radial-gradient(#C5A880_1px,transparent_1px)] dark:bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:26px_26px] pointer-events-none"></div>
            <div class="absolute -top-24 right-1/4 w-96 h-96 bg-gold-300/25 dark:bg-gold-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 -left-24 w-80 h-80 bg-amber-300/20 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <?php endif; ?>

            <?php if ($show_arches) : ?>
            <div class="block absolute top-14 right-8 w-72 h-72 rounded-full border-2 border-gold-600/25 dark:border-gold-500/15 bg-gold-200/10 dark:bg-gold-500/[0.02] pointer-events-none"></div>
            <div class="block absolute top-24 right-16 w-52 h-52 rounded-full border border-dashed border-gold-700/30 dark:border-gold-400/20 pointer-events-none"></div>
            <div class="block absolute bottom-14 left-8 w-80 h-80 rounded-t-[999px] border-2 border-gold-600/20 dark:border-gold-500/12 bg-gold-100/15 dark:bg-transparent pointer-events-none"></div>
            <div class="block absolute top-1/3 left-1/2 -translate-x-1/2 w-[520px] h-[520px] rounded-full border border-gold-600/15 dark:border-gold-500/10 pointer-events-none"></div>
            <?php endif; ?>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

              <?php if ($show_header) : ?>
              <div class="mb-8 pb-6 border-b border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                  <nav class="woocommerce-breadcrumb flex items-center gap-2 text-xs text-stone-600 dark:text-stone-300 mb-3" aria-label="Breadcrumb">
                    <a href="<?php echo esc_url($home_url); ?>" onclick="if(window.RouterController){RouterController.navigate('home'); return false;}" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors"><?php echo $breadcrumb_home; ?></a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    <span class="text-stone-900 dark:text-white font-semibold"><?php echo $breadcrumb_cart; ?></span>
                  </nav>
                  <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400"><?php echo $pre_title; ?></span>
                  <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white mt-1">
                    <?php echo $title; ?>
                  </h1>
                </div>
                <a href="<?php echo esc_url($continue_url); ?>" onclick="if(window.RouterController){RouterController.navigate('shop'); return false;}" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                  <i data-lucide="arrow-left" class="w-4 h-4"></i>
                  <span><?php echo $continue_text; ?></span>
                </a>
              </div>
              <?php endif; ?>

              <?php if ($show_shipping_banner) : ?>
              <div class="mb-8 p-5 rounded-2xl bg-white/95 dark:bg-stone-900/90 backdrop-blur-md border border-stone-200/80 dark:border-stone-800 shadow-sm">
                <div class="flex items-center justify-between text-xs sm:text-sm mb-2">
                  <span id="cart-page-shipping-text" class="font-medium text-stone-700 dark:text-stone-300">
                    <?php echo wp_kses_post($shipping_text_rendered); ?>
                  </span>
                  <span class="text-[11px] uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400">
                    <?php echo wp_kses_post($threshold_badge_rendered); ?>
                  </span>
                </div>
                <div class="w-full h-2 bg-stone-200 dark:bg-stone-800 rounded-full overflow-hidden">
                  <div id="cart-page-shipping-bar" class="h-full bg-gold-500 rounded-full transition-all duration-500" style="width: <?php echo esc_attr($progress_pct); ?>%"></div>
                </div>
              </div>
              <?php endif; ?>

              <div id="cart-page-empty" class="<?php echo $is_empty ? '' : 'hidden'; ?> py-16 px-6 text-center bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm">
                <div class="w-20 h-20 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 mx-auto mb-5">
                  <i data-lucide="shopping-bag" class="w-10 h-10"></i>
                </div>
                <h2 class="font-serif text-2xl sm:text-3xl font-medium text-stone-900 dark:text-white mb-2"><?php echo $empty_title; ?></h2>
                <p class="text-sm text-stone-600 dark:text-stone-300 max-w-md mx-auto mb-8 font-normal">
                  <?php echo $empty_desc; ?>
                </p>
                <a href="<?php echo esc_url($continue_url); ?>" onclick="if(window.RouterController){RouterController.navigate('shop'); return false;}" class="btn-primary rounded-xl inline-block px-8 py-3.5 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-wider transition-all">
                  <?php echo $empty_btn_text; ?>
                </a>
              </div>

              <div id="cart-page-content" class="<?php echo $is_empty ? 'hidden' : ''; ?> grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8 space-y-6">
                  <div class="bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                      <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents w-full text-left border-collapse">
                        <thead>
                          <tr class="border-b border-stone-200 dark:border-stone-800 text-[11px] font-bold uppercase tracking-wider text-stone-600 dark:text-stone-300 bg-stone-50/60 dark:bg-stone-950/60">
                            <th class="py-4 px-5"><?php esc_html_e('Product', 'kinboni-core'); ?></th>
                            <th class="py-4 px-4"><?php esc_html_e('Price', 'kinboni-core'); ?></th>
                            <th class="py-4 px-4"><?php esc_html_e('Quantity', 'kinboni-core'); ?></th>
                            <th class="py-4 px-5 text-right"><?php esc_html_e('Subtotal', 'kinboni-core'); ?></th>
                            <th class="py-4 px-4 text-center"><span class="sr-only"><?php esc_html_e('Remove', 'kinboni-core'); ?></span></th>
                          </tr>
                        </thead>
                        <tbody id="cart-page-table-body" class="divide-y divide-stone-200/70 dark:divide-stone-800 text-sm">
                          <?php if (!empty($cart_items_data)) : ?>
                            <?php foreach ($cart_items_data as $item) : ?>
                              <tr class="woocommerce-cart-form__cart-item cart_item hover:bg-stone-50/50 dark:hover:bg-stone-950/40 transition-colors" data-cart-item-key="<?php echo esc_attr($item['key']); ?>">
                                <td class="py-4 px-5">
                                  <div class="flex items-center gap-4">
                                    <a href="<?php echo esc_url($item['url']); ?>">
                                      <img loading="lazy" src="<?php echo esc_url($item['image']); ?>" 
                                        alt="<?php echo esc_attr($item['name']); ?>" 
                                        class="w-16 h-20 object-cover rounded-xl bg-stone-100 dark:bg-stone-800 cursor-pointer flex-shrink-0" 
                                        onerror="this.src='<?php echo esc_url(wc_placeholder_img_src('thumbnail')); ?>';this.onerror=null;"
                                      />
                                    </a>
                                    <div>
                                      <span class="text-xs font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400 block"><?php echo esc_html($item['category']); ?></span>
                                      <a href="<?php echo esc_url($item['url']); ?>" class="font-serif text-base font-medium text-stone-900 dark:text-white hover:text-gold-600 transition-colors">
                                        <?php echo esc_html($item['name']); ?>
                                      </a>
                                      <?php if (!empty($item['monogram'])) : ?>
                                        <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-gold-700 dark:text-gold-400">
                                          <i data-lucide="sparkles" class="w-3 h-3"></i>
                                          <span>Foil Monogram: "<?php echo esc_html($item['monogram']); ?>"</span>
                                        </div>
                                      <?php endif; ?>
                                    </div>
                                  </div>
                                </td>
                                <td class="py-4 px-4 font-semibold text-stone-700 dark:text-stone-300">
                                  <?php echo wp_kses_post($item['price_formatted']); ?>
                                </td>
                                <td class="py-4 px-4">
                                  <div class="inline-flex items-center border border-stone-200 dark:border-stone-700 rounded-xl bg-stone-50 dark:bg-stone-950">
                                    <button type="button" class="btn-cart-page-qty-minus w-8 h-8 flex items-center justify-center text-stone-600 dark:text-stone-400 hover:text-gold-500 cursor-pointer" data-key="<?php echo esc_attr($item['key']); ?>" data-id="<?php echo esc_attr($item['id']); ?>" data-qty="<?php echo esc_attr($item['quantity'] - 1); ?>" aria-label="Decrease">
                                      <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <span class="w-8 text-center text-xs font-bold text-stone-900 dark:text-white"><?php echo esc_html($item['quantity']); ?></span>
                                    <button type="button" class="btn-cart-page-qty-plus w-8 h-8 flex items-center justify-center text-stone-600 dark:text-stone-400 hover:text-gold-500 cursor-pointer" data-key="<?php echo esc_attr($item['key']); ?>" data-id="<?php echo esc_attr($item['id']); ?>" data-qty="<?php echo esc_attr($item['quantity'] + 1); ?>" aria-label="Increase">
                                      <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    </button>
                                  </div>
                                </td>
                                <td class="py-4 px-5 text-right font-serif text-base font-bold text-stone-900 dark:text-white">
                                  <?php echo wp_kses_post($item['subtotal_formatted']); ?>
                                </td>
                                <td class="py-4 px-4 text-center">
                                  <button type="button" class="btn-cart-page-remove p-2 text-stone-400 hover:text-rose-500 transition-colors cursor-pointer" data-key="<?php echo esc_attr($item['key']); ?>" data-id="<?php echo esc_attr($item['id']); ?>" aria-label="Remove item">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>

                    <div class="p-5 bg-stone-50/50 dark:bg-stone-950/50 border-t border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                      <form id="cart-page-coupon-form" class="coupon flex items-center gap-2">
                        <input 
                          type="text" 
                          id="cart-page-coupon-input" 
                          placeholder="<?php echo $coupon_placeholder; ?>" 
                          aria-label="Coupon code"
                          class="px-4 py-2.5 text-xs uppercase rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500"
                        />
                        <button type="submit" class="px-5 py-2.5 bg-stone-900 text-white dark:bg-white dark:text-stone-900 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-gold-500 hover:text-stone-950 transition-colors cursor-pointer">
                          <?php echo $coupon_btn_text; ?>
                        </button>
                      </form>

                      <div class="flex items-center gap-3 justify-end">
                        <button 
                          type="button" 
                          id="cart-page-clear-btn"
                          onclick="if(window.CartController && window.CartController.clear){CartController.clear();}else if(window.KinboniCore){KinboniCore.clearCart();}"
                          class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-xs font-semibold text-stone-600 dark:text-stone-400 hover:border-rose-500 hover:text-rose-500 transition-colors cursor-pointer"
                        >
                          <?php echo $clear_btn_text; ?>
                        </button>
                        <button 
                          type="button" 
                          id="cart-page-update-btn"
                          onclick="if(window.CartPageController){CartPageController.render();}else if(window.KinboniCore){KinboniCore.refreshCart();}"
                          class="px-4 py-2.5 rounded-xl bg-stone-200/80 dark:bg-stone-800 text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 hover:bg-gold-400 hover:text-stone-950 transition-colors cursor-pointer"
                        >
                          <?php echo $update_btn_text; ?>
                        </button>
                      </div>
                    </div>
                  </div>

                  <?php if ($show_cross_sells) : ?>
                  <div class="p-6 rounded-3xl bg-white/95 dark:bg-stone-900/90 backdrop-blur-md border border-stone-200/80 dark:border-stone-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                      <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-gold-700 dark:text-gold-400"><?php echo $cross_sells_pre; ?></span>
                        <h3 class="font-serif text-xl font-medium text-stone-900 dark:text-white"><?php echo $cross_sells_title; ?></h3>
                      </div>
                    </div>
                    <div id="cart-cross-sell-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <?php if (!empty($cross_sells)) : ?>
                        <?php foreach ($cross_sells as $cs) : ?>
                          <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200/80 dark:border-stone-800">
                            <div class="flex items-center gap-3 min-w-0">
                              <img loading="lazy" src="<?php echo esc_url($cs['image']); ?>" alt="<?php echo esc_attr($cs['name']); ?>" class="w-14 h-16 object-cover rounded-xl flex-shrink-0" onerror="this.src='<?php echo esc_url(wc_placeholder_img_src('thumbnail')); ?>';this.onerror=null;" />
                              <div class="min-w-0">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400 block"><?php echo esc_html($cs['category']); ?></span>
                                <h4 class="font-serif text-xs font-semibold text-stone-900 dark:text-white truncate"><?php echo esc_html($cs['name']); ?></h4>
                                <span class="text-xs font-bold text-stone-900 dark:text-white"><?php echo wp_kses_post($cs['price_formatted']); ?></span>
                              </div>
                            </div>
                            <button type="button" class="btn-cart-cross-sell-add px-3 py-2 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-wider flex-shrink-0 cursor-pointer" data-id="<?php echo esc_attr($cs['id']); ?>" onclick="if(window.CartController && window.CartController.add){CartController.add(<?php echo esc_attr($cs['id']); ?>, 1);}else if(window.KinboniCore){KinboniCore.addToCart(<?php echo esc_attr($cs['id']); ?>, 1);}">
                              + Add
                            </button>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="lg:col-span-4 cart-collaterals">
                  <div class="cart_totals bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-7 shadow-sm space-y-5">
                    <h2 class="font-serif text-2xl font-medium text-stone-900 dark:text-white pb-3 border-b border-stone-200 dark:border-stone-800">
                      <?php echo $totals_title; ?>
                    </h2>

                    <div id="cart-page-applied-promo">
                      <?php if (!empty($applied_coupons)) : ?>
                        <?php foreach ($applied_coupons as $cpn) : ?>
                          <div class="flex items-center justify-between p-3 rounded-xl bg-gold-50 dark:bg-gold-950/40 border border-gold-300 dark:border-gold-800 text-xs mb-2">
                            <span class="font-semibold text-gold-700 dark:text-gold-300">Active Code: <strong><?php echo esc_html($cpn); ?></strong></span>
                            <button type="button" class="btn-cart-remove-coupon text-stone-400 hover:text-rose-500 cursor-pointer" data-coupon="<?php echo esc_attr($cpn); ?>" onclick="if(window.CartController && window.CartController.removePromo){CartController.removePromo('<?php echo esc_attr($cpn); ?>');}">
                              <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>

                    <div class="space-y-3 text-xs sm:text-sm">
                      <div class="flex justify-between text-stone-600 dark:text-stone-400">
                        <span><?php echo $subtotal_label; ?></span>
                        <span id="cart-page-subtotal" class="font-bold text-stone-900 dark:text-white"><?php echo wp_kses_post(wc_price($subtotal_raw)); ?></span>
                      </div>

                      <div id="cart-page-discount-row" class="<?php echo ($discount_raw > 0) ? 'flex' : 'hidden'; ?> justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                        <span><?php echo $discount_label; ?></span>
                        <span id="cart-page-discount">-<?php echo wp_kses_post(wc_price($discount_raw)); ?></span>
                      </div>

                      <div class="pt-3 border-t border-stone-200 dark:border-stone-800 space-y-2">
                        <span class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200"><?php echo $shipping_title; ?></span>
                        <label class="flex items-center justify-between p-2.5 rounded-xl border border-stone-200 dark:border-stone-800 cursor-pointer text-xs">
                          <span class="flex items-center gap-2">
                            <input type="radio" name="cart-page-zone" value="inside" checked onchange="if(window.CartPageController){CartPageController.setZone('inside');}" class="text-gold-500 focus:ring-gold-500" />
                            <span><?php echo $fee_inside_label; ?></span>
                          </span>
                          <span id="cart-page-fee-inside" class="font-bold"><?php echo $is_free_shipping ? esc_html__('Free', 'kinboni-core') : wp_kses_post(wc_price($fee_inside)); ?></span>
                        </label>
                        <label class="flex items-center justify-between p-2.5 rounded-xl border border-stone-200 dark:border-stone-800 cursor-pointer text-xs">
                          <span class="flex items-center gap-2">
                            <input type="radio" name="cart-page-zone" value="outside" onchange="if(window.CartPageController){CartPageController.setZone('outside');}" class="text-gold-500 focus:ring-gold-500" />
                            <span><?php echo $fee_outside_label; ?></span>
                          </span>
                          <span id="cart-page-fee-outside" class="font-bold"><?php echo $is_free_shipping ? esc_html__('Free', 'kinboni-core') : wp_kses_post(wc_price($fee_outside)); ?></span>
                        </label>
                      </div>

                      <div class="pt-4 border-t border-stone-200 dark:border-stone-800 flex justify-between items-baseline">
                        <span class="text-base font-bold text-stone-900 dark:text-white"><?php echo $total_label; ?></span>
                        <span id="cart-page-total" class="font-serif text-2xl font-bold text-gold-700 dark:text-gold-400"><?php echo wp_kses_post(wc_price($final_total_raw)); ?></span>
                      </div>
                    </div>

                    <div class="space-y-2.5 pt-2">
                      <a 
                        href="<?php echo esc_url($checkout_url); ?>"
                        class="wc-proceed-to-checkout w-full py-4 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg hover:shadow-gold-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer"
                      >
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span><?php echo $checkout_btn_text; ?></span>
                      </a>

                      <?php if ($show_quick_cod) : ?>
                      <button 
                        type="button" 
                        onclick="if(window.QuickOrderController && window.QuickOrderController.open){QuickOrderController.open();}else if(window.KinboniCore){KinboniCore.openQuickOrder();}"
                        class="w-full py-3 bg-stone-900 text-white dark:bg-stone-800 dark:text-stone-100 hover:bg-stone-800 text-xs font-bold uppercase tracking-wider rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer"
                      >
                        <i data-lucide="zap" class="w-4 h-4 text-gold-400"></i>
                        <span><?php echo $quick_cod_btn_text; ?></span>
                      </button>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <script type="application/json" id="kinboni-cart-initial-data">
              <?php
              $initial_payload = array(
                  'is_empty'           => $is_empty,
                  'items'              => $cart_items_data,
                  'subtotal'           => $subtotal_raw,
                  'subtotal_formatted' => wc_price($subtotal_raw),
                  'discount'           => $discount_raw,
                  'discount_formatted' => wc_price($discount_raw),
                  'coupons'            => $applied_coupons,
                  'total'              => $final_total_raw,
                  'total_formatted'    => wc_price($final_total_raw),
                  'threshold'          => $threshold,
                  'diff'               => $diff_raw,
                  'is_free_shipping'   => $is_free_shipping,
                  'fee_inside'         => $fee_inside,
                  'fee_outside'        => $fee_outside,
                  'cross_sells'        => $cross_sells,
                  'currency_symbol'    => $currency_symbol,
              );
              echo wp_json_encode($initial_payload);
              ?>
            </script>
        </main>
        <?php
    }
}

