<?php
/**
 * Kinboni Atelier Checkout Showcase - Elementor Widget
 *
 * Implements the luxury editorial WooCommerce Checkout page matching checkout.html
 * featuring White-Glove Dispatch, Live Coupon Application, Full Billing & Delivery Form,
 * Bespoke Gift Wrapping & Wax-Sealed Inscription, Live Order Summary with Dynamic Shipping
 * calculation, bKash/Nagad & COD payment switchers, and WooCommerce Order Received Thank-You view.
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

class Kinboni_Widget_Checkout extends Widget_Base {

    public function get_name() {
        return 'kinboni_checkout';
    }

    public function get_title() {
        return esc_html__('Atelier Checkout Showcase', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-checkout';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('checkout', 'woocommerce', 'order', 'billing', 'shipping', 'payment', 'cod', 'kinboni');
    }

    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: HEADER & BREADCRUMB
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
            'breadcrumb_checkout_text',
            array(
                'label'       => esc_html__('Breadcrumb Checkout Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Checkout',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'pre_title',
            array(
                'label'       => esc_html__('Subtitle / Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'White-Glove Dispatch',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'title',
            array(
                'label'       => esc_html__('Page Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Atelier Checkout',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'show_quick_cod_top_btn',
            array(
                'label'        => esc_html__('Show Quick COD Header Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'quick_cod_top_btn_text',
            array(
                'label'       => esc_html__('Quick COD Header Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Prefer 3-Field Quick COD? Open Express Modal',
                'condition'   => array('show_quick_cod_top_btn' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: PRIVILEGE VOUCHER BANNER
        // ==========================================
        $this->start_controls_section(
            'section_voucher_content',
            array(
                'label' => esc_html__('Privilege Voucher Banner', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_voucher_banner',
            array(
                'label'        => esc_html__('Show Voucher Banner', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'voucher_banner_text',
            array(
                'label'       => esc_html__('Voucher Prompt Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Have a privilege voucher? Enter code <strong>LUXE20</strong> for 20% off.',
                'condition'   => array('show_voucher_banner' => 'yes'),
            )
        );

        $this->add_control(
            'voucher_placeholder',
            array(
                'label'       => esc_html__('Voucher Input Placeholder', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Coupon code (LUXE20)',
                'condition'   => array('show_voucher_banner' => 'yes'),
            )
        );

        $this->add_control(
            'voucher_btn_text',
            array(
                'label'       => esc_html__('Voucher Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Apply',
                'condition'   => array('show_voucher_banner' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: BILLING & GIFT WRAPPING
        // ==========================================
        $this->start_controls_section(
            'section_billing_content',
            array(
                'label' => esc_html__('Billing & Delivery Form', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'billing_title',
            array(
                'label'       => esc_html__('Billing Section Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Billing & Delivery Details',
            )
        );

        $this->add_control(
            'gift_wrap_title',
            array(
                'label'       => esc_html__('Gift Wrap Option Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Include Complimentary Silk Ribbon & Wax-Sealed Gift Box',
            )
        );

        $this->add_control(
            'notes_placeholder',
            array(
                'label'       => esc_html__('Calligrapher Notes Placeholder', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => 'Optional: Enter a personal message for our calligrapher to inscribe on your wax-sealed gift card...',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: ORDER SUMMARY & PAYMENT
        // ==========================================
        $this->start_controls_section(
            'section_summary_content',
            array(
                'label' => esc_html__('Order Summary & Payment', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'summary_title',
            array(
                'label'       => esc_html__('Order Summary Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Your Order Summary',
            )
        );

        $this->add_control(
            'place_order_btn_text',
            array(
                'label'       => esc_html__('Submit Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Place Bespoke Order',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: THANK YOU VIEW
        // ==========================================
        $this->start_controls_section(
            'section_thankyou_content',
            array(
                'label' => esc_html__('Thank You (Order Received) View', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'ty_title',
            array(
                'label'       => esc_html__('Thank You Heading', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Thank you. Your order has been received.',
            )
        );

        $this->add_control(
            'ty_desc',
            array(
                'label'       => esc_html__('Thank You Description', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => 'An official atelier invoice has been logged to your Patron Account. You can print your official receipt or track dispatch status below.',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: AMBIENT GLOW & GEOMETRY
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
                'label' => esc_html__('Typography & Accents', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'checkout_title_typography',
                'label'    => esc_html__('Page Title Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} #page-view-checkout h1',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'billing_heading_typography',
                'label'    => esc_html__('Section Headings Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} #page-view-checkout h3',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $show_header             = !empty($settings['show_header']) && $settings['show_header'] === 'yes';
        $breadcrumb_home         = !empty($settings['breadcrumb_home_text']) ? esc_html($settings['breadcrumb_home_text']) : 'Home';
        $breadcrumb_cart         = !empty($settings['breadcrumb_cart_text']) ? esc_html($settings['breadcrumb_cart_text']) : 'Shopping Bag';
        $breadcrumb_checkout     = !empty($settings['breadcrumb_checkout_text']) ? esc_html($settings['breadcrumb_checkout_text']) : 'Checkout';
        $pre_title               = !empty($settings['pre_title']) ? esc_html($settings['pre_title']) : 'White-Glove Dispatch';
        $title                   = !empty($settings['title']) ? esc_html($settings['title']) : 'Atelier Checkout';
        $show_quick_cod_top      = !empty($settings['show_quick_cod_top_btn']) && $settings['show_quick_cod_top_btn'] === 'yes';
        $quick_cod_top_text      = !empty($settings['quick_cod_top_btn_text']) ? esc_html($settings['quick_cod_top_btn_text']) : 'Prefer 3-Field Quick COD? Open Express Modal';

        $show_voucher            = !empty($settings['show_voucher_banner']) && $settings['show_voucher_banner'] === 'yes';
        $vip_code                = class_exists('Kinboni_Content') ? Kinboni_Content::get('vip_coupon_code', 'LUXE20') : 'LUXE20';
        $default_voucher_text    = sprintf('Have a privilege voucher? Enter code <strong>%s</strong> for 20%% off.', $vip_code);
        $default_voucher_ph      = sprintf('Coupon code (%s)', $vip_code);
        $voucher_text            = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'voucher_banner_text', 'voucher_banner_text', $default_voucher_text, $this) : (!empty($settings['voucher_banner_text']) ? $settings['voucher_banner_text'] : $default_voucher_text);
        $voucher_placeholder     = class_exists('Kinboni_Content') ? esc_attr(Kinboni_Content::resolve($settings, 'voucher_placeholder', 'voucher_placeholder', $default_voucher_ph, $this)) : (!empty($settings['voucher_placeholder']) ? esc_attr($settings['voucher_placeholder']) : $default_voucher_ph);
        $voucher_btn_text        = !empty($settings['voucher_btn_text']) ? esc_html($settings['voucher_btn_text']) : 'Apply';

        $billing_title           = !empty($settings['billing_title']) ? esc_html($settings['billing_title']) : 'Billing & Delivery Details';
        $gift_wrap_title         = !empty($settings['gift_wrap_title']) ? esc_html($settings['gift_wrap_title']) : 'Include Complimentary Silk Ribbon & Wax-Sealed Gift Box';
        $notes_placeholder       = !empty($settings['notes_placeholder']) ? esc_attr($settings['notes_placeholder']) : 'Optional: Enter a personal message for our calligrapher to inscribe on your wax-sealed gift card...';

        $summary_title           = !empty($settings['summary_title']) ? esc_html($settings['summary_title']) : 'Your Order Summary';
        $place_order_btn_text    = !empty($settings['place_order_btn_text']) ? esc_html($settings['place_order_btn_text']) : 'Place Bespoke Order';

        $ty_title                = !empty($settings['ty_title']) ? esc_html($settings['ty_title']) : 'Thank you. Your order has been received.';
        $ty_desc                 = !empty($settings['ty_desc']) ? esc_html($settings['ty_desc']) : 'An official atelier invoice has been logged to your Patron Account. You can print your official receipt or track dispatch status below.';

        $show_ambient            = !empty($settings['show_ambient_glow']) && $settings['show_ambient_glow'] === 'yes';
        $show_arches             = !empty($settings['show_arch_shapes']) && $settings['show_arch_shapes'] === 'yes';

        $home_url                = home_url('/');
        $cart_url                = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
        $shop_url                = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
        $myaccount_url           = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

        // Retrieve WooCommerce Cart data
        $cart = (function_exists('WC') && WC()->cart) ? WC()->cart : null;
        $is_empty = (!$cart || $cart->is_empty());
        $subtotal_raw = $cart ? (float)$cart->get_subtotal() : 0;
        $discount_raw = $cart ? (float)$cart->get_discount_total() : 0;
        $threshold = (float) (class_exists('Kinboni_Content') ? Kinboni_Content::get('free_shipping_threshold', 2500) : get_option('kinboni_free_shipping_threshold', 2500));
        $is_free_shipping = ($threshold > 0 && $subtotal_raw >= $threshold);
        $initial_shipping_fee = $is_free_shipping ? 0 : (float) (class_exists('Kinboni_Content') ? Kinboni_Content::get('delivery_fee_inside', 70) : get_option('kinboni_delivery_fee_inside', 70));
        $initial_total_raw = max(0, $subtotal_raw - $discount_raw + $initial_shipping_fee);

        $cart_items = array();
        if ($cart && !$is_empty) {
            foreach ($cart->get_cart() as $key => $item) {
                $p = $item['data'];
                if ($p && $p->exists() && $item['quantity'] > 0) {
                    $pid = $item['product_id'];
                    $img_id = $p->get_image_id();
                    $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : wc_placeholder_img_src();
                    $price_raw = (float)$p->get_price();
                    $line_total = $price_raw * $item['quantity'];

                    $cart_items[] = array(
                        'id'         => $pid,
                        'name'       => $p->get_name(),
                        'quantity'   => $item['quantity'],
                        'price'      => $price_raw,
                        'line_total' => $line_total,
                        'image'      => $img_url,
                        'monogram'   => !empty($item['kinboni_monogram']) ? $item['kinboni_monogram'] : '',
                    );
                }
            }
        }
        ?>
        <main id="page-view-checkout" class="wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]">
            
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
                    <a href="<?php echo esc_url($cart_url); ?>" onclick="if(window.RouterController){RouterController.navigate('cart'); return false;}" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors"><?php echo $breadcrumb_cart; ?></a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    <span class="text-stone-900 dark:text-white font-semibold"><?php echo $breadcrumb_checkout; ?></span>
                  </nav>
                  <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400"><?php echo $pre_title; ?></span>
                  <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white mt-1">
                    <?php echo $title; ?>
                  </h1>
                </div>
                <?php if ($show_quick_cod_top) : ?>
                <button type="button" onclick="if(window.QuickOrderController && window.QuickOrderController.open){QuickOrderController.open();}else if(window.KinboniCore){KinboniCore.openQuickOrder();}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gold-400/15 border border-gold-500/40 text-gold-700 dark:text-gold-300 text-xs font-bold uppercase tracking-wider hover:bg-gold-400 hover:text-stone-950 transition-all cursor-pointer">
                  <i data-lucide="zap" class="w-4 h-4"></i>
                  <span><?php echo $quick_cod_top_text; ?></span>
                </button>
                <?php endif; ?>
              </div>
              <?php endif; ?>

              <div id="wc-checkout-form-container">
                <?php if ($show_voucher) : ?>
                <div class="mb-8 p-4 rounded-2xl bg-white/95 dark:bg-stone-900/90 backdrop-blur-md border border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                  <span class="flex items-center gap-2 text-stone-700 dark:text-stone-300">
                    <i data-lucide="tag" class="w-4 h-4 text-gold-500"></i>
                    <span><?php echo wp_kses_post($voucher_text); ?></span>
                  </span>
                  <div class="flex items-center gap-2">
                    <input type="text" id="wc-checkout-coupon-input" placeholder="<?php echo $voucher_placeholder; ?>" class="px-3 py-1.5 rounded-lg border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs uppercase" />
                    <button type="button" onclick="if(window.CheckoutPageController){CheckoutPageController.applyCoupon();}" class="px-4 py-1.5 rounded-lg bg-stone-900 text-white dark:bg-white dark:text-stone-900 font-bold uppercase text-[11px] hover:bg-gold-500 hover:text-stone-950 transition-colors cursor-pointer"><?php echo $voucher_btn_text; ?></button>
                  </div>
                </div>
                <?php endif; ?>

                <form id="wc-full-checkout-form" class="checkout woocommerce-checkout grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" novalidate>
                  <div style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;" aria-hidden="true"><input type="text" name="kinboni_hp" value="" tabindex="-1" autocomplete="off"></div>
                  
                  <div class="lg:col-span-7 bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="font-serif text-2xl font-medium text-stone-900 dark:text-white pb-3 border-b border-stone-200 dark:border-stone-800">
                      <?php echo $billing_title; ?>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label for="wc-billing-first-name" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('First Name', 'kinboni-core'); ?> <span class="text-rose-500">*</span></label>
                        <input type="text" id="wc-billing-first-name" name="first_name" required placeholder="e.g. Tahmid" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="wc-billing-last-name" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Last Name', 'kinboni-core'); ?> <span class="text-rose-500">*</span></label>
                        <input type="text" id="wc-billing-last-name" name="last_name" required placeholder="e.g. Rahman" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label for="wc-billing-phone" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Mobile Phone (11 Digits)', 'kinboni-core'); ?> <span class="text-rose-500">*</span></label>
                        <input type="tel" id="wc-billing-phone" name="phone" required placeholder="017XXXXXXXX" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="wc-billing-email" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Email Address (Optional)', 'kinboni-core'); ?></label>
                        <input type="email" id="wc-billing-email" name="email" placeholder="patron@domain.com" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label for="wc-billing-city" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('City / District', 'kinboni-core'); ?> <span class="text-rose-500">*</span></label>
                        <select id="wc-billing-city" name="city" onchange="if(window.CheckoutPageController){CheckoutPageController.onCityChange(this.value);}" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500">
                          <?php
                          $chk_fee_in  = (float) get_option('kinboni_delivery_fee_inside', 70);
                          $chk_fee_out = (float) get_option('kinboni_delivery_fee_outside', 130);
                          $chk_time_in = get_option('kinboni_delivery_time_inside', '1 - 2 Business Days');
                          $chk_time_out= get_option('kinboni_delivery_time_outside', '2 - 4 Business Days');
                          ?>
                          <option value="inside"><?php echo esc_html(sprintf(__('Dhaka City (Inside Dhaka — ৳%1$s • %2$s)', 'kinboni-core'), $chk_fee_in, $chk_time_in)); ?></option>
                          <option value="outside"><?php echo esc_html(sprintf(__('Chattogram (Outside Dhaka — ৳%1$s • %2$s)', 'kinboni-core'), $chk_fee_out, $chk_time_out)); ?></option>
                          <option value="outside"><?php echo esc_html(sprintf(__('Sylhet (Outside Dhaka — ৳%1$s • %2$s)', 'kinboni-core'), $chk_fee_out, $chk_time_out)); ?></option>
                          <option value="outside"><?php echo esc_html(sprintf(__('Rajshahi (Outside Dhaka — ৳%1$s • %2$s)', 'kinboni-core'), $chk_fee_out, $chk_time_out)); ?></option>
                          <option value="outside"><?php echo esc_html(sprintf(__('Khulna (Outside Dhaka — ৳%1$s • %2$s)', 'kinboni-core'), $chk_fee_out, $chk_time_out)); ?></option>
                          <option value="outside"><?php echo esc_html(sprintf(__('Other District in Bangladesh (৳%1$s • %2$s)', 'kinboni-core'), $chk_fee_out, $chk_time_out)); ?></option>
                        </select>
                      </div>
                      <div>
                        <label for="wc-billing-postcode" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Area / Thana', 'kinboni-core'); ?></label>
                        <input type="text" id="wc-billing-postcode" name="postcode" placeholder="e.g. Gulshan-2 / Banani / Dhanmondi" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                    </div>

                    <div>
                      <label for="wc-billing-address" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Street Address / House & Road', 'kinboni-core'); ?> <span class="text-rose-500">*</span></label>
                      <input type="text" id="wc-billing-address" name="address" required placeholder="House number, street name, apartment suite" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                    </div>

                    <div class="p-4 rounded-2xl bg-gold-50/50 dark:bg-stone-950 border border-gold-500/30 space-y-2">
                      <label class="flex items-center justify-between cursor-pointer text-xs font-bold text-stone-900 dark:text-white">
                        <span class="flex items-center gap-2">
                          <input type="checkbox" id="wc-gift-wrap-checkbox" name="gift_wrap" checked class="rounded text-gold-500 focus:ring-gold-500 w-4 h-4" />
                          <span><?php echo $gift_wrap_title; ?></span>
                        </span>
                        <span class="text-xs uppercase tracking-wider text-gold-700 dark:text-gold-400"><?php esc_html_e('Complimentary', 'kinboni-core'); ?></span>
                      </label>
                      <textarea id="wc-order-notes" name="order_notes" rows="2" placeholder="<?php echo $notes_placeholder; ?>" class="w-full px-3.5 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500"></textarea>
                    </div>

                    <p id="wc-checkout-error" class="hidden text-xs font-semibold text-rose-500"></p>
                  </div>

                  <div class="lg:col-span-5 bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="font-serif text-2xl font-medium text-stone-900 dark:text-white pb-3 border-b border-stone-200 dark:border-stone-800">
                      <?php echo $summary_title; ?>
                    </h3>

                    <div id="wc-checkout-order-items" class="space-y-3 max-h-64 overflow-y-auto pr-1 divide-y divide-stone-200/60 dark:divide-stone-800">
                      <?php if (!empty($cart_items)) : ?>
                        <?php foreach ($cart_items as $ci) : ?>
                          <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3 min-w-0">
                              <img loading="lazy" src="<?php echo esc_url($ci['image']); ?>" alt="<?php echo esc_attr($ci['name']); ?>" class="w-12 h-14 object-cover rounded-lg flex-shrink-0" />
                              <div class="min-w-0">
                                <h4 class="font-serif text-sm font-medium text-stone-900 dark:text-white truncate"><?php echo esc_html($ci['name']); ?></h4>
                                <span class="text-stone-500">Qty: <?php echo esc_html($ci['quantity']); ?></span>
                                <?php if (!empty($ci['monogram'])) : ?>
                                  <span class="block text-[11px] text-gold-700 dark:text-gold-400 font-semibold">Monogram: "<?php echo esc_html($ci['monogram']); ?>"</span>
                                <?php endif; ?>
                              </div>
                            </div>
                            <span class="font-bold text-stone-900 dark:text-white flex-shrink-0"><?php echo wp_kses_post(wc_price($ci['line_total'])); ?></span>
                          </div>
                        <?php endforeach; ?>
                      <?php else : ?>
                        <div class="py-6 text-center text-xs text-stone-500">
                          <?php esc_html_e('Your bag is currently empty.', 'kinboni-core'); ?> <a href="<?php echo esc_url($shop_url); ?>" onclick="if(window.RouterController){RouterController.navigate('shop'); return false;}" class="text-gold-600 font-bold underline"><?php esc_html_e('Browse Creations', 'kinboni-core'); ?></a>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="pt-4 border-t border-stone-200 dark:border-stone-800 space-y-2.5 text-xs sm:text-sm">
                      <div class="flex justify-between text-stone-600 dark:text-stone-400">
                        <span><?php esc_html_e('Subtotal', 'kinboni-core'); ?></span>
                        <span id="wc-checkout-subtotal" class="font-bold text-stone-900 dark:text-white"><?php echo wp_kses_post(wc_price($subtotal_raw)); ?></span>
                      </div>
                      <div id="wc-checkout-discount-row" class="<?php echo ($discount_raw > 0) ? 'flex' : 'hidden'; ?> justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                        <span><?php esc_html_e('Privilege Voucher', 'kinboni-core'); ?></span>
                        <span id="wc-checkout-discount">-<?php echo wp_kses_post(wc_price($discount_raw)); ?></span>
                      </div>
                      <div class="flex justify-between text-stone-600 dark:text-stone-400">
                        <span><?php esc_html_e('White-Glove Courier', 'kinboni-core'); ?></span>
                        <span id="wc-checkout-shipping" class="font-bold text-stone-900 dark:text-white"><?php echo $is_free_shipping ? esc_html__('Free (Complimentary)', 'kinboni-core') : wp_kses_post(wc_price($initial_shipping_fee)); ?></span>
                      </div>
                      <div class="flex justify-between items-baseline pt-3 border-t border-stone-200 dark:border-stone-800">
                        <span class="text-base font-bold text-stone-900 dark:text-white"><?php esc_html_e('Total Payable', 'kinboni-core'); ?></span>
                        <span id="wc-checkout-total" class="font-serif text-2xl font-bold text-gold-700 dark:text-gold-400"><?php echo wp_kses_post(wc_price($initial_total_raw)); ?></span>
                      </div>
                    </div>

                    <div id="payment" class="woocommerce-checkout-payment pt-4 border-t border-stone-200 dark:border-stone-800 space-y-3">
                      <span class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200"><?php esc_html_e('Select Payment Method', 'kinboni-core'); ?></span>

                      <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-gold-500 bg-gold-50/40 dark:bg-gold-950/20 cursor-pointer">
                        <input type="radio" name="wc_payment_method" value="Cash on Delivery (COD)" checked onchange="if(window.CheckoutPageController){CheckoutPageController.togglePaymentFields(this.value);}" class="mt-0.5 text-gold-500 focus:ring-gold-500" />
                        <div class="text-xs">
                          <span class="font-bold text-stone-900 dark:text-white block"><?php esc_html_e('Cash on Delivery (Pay Upon Inspection)', 'kinboni-core'); ?></span>
                          <span class="text-stone-600 dark:text-stone-300 text-[11px]"><?php esc_html_e('Pay in cash to our courier after verifying your timepiece or leather creation.', 'kinboni-core'); ?></span>
                        </div>
                      </label>

                      <?php 
                      $bkash_merchant = trim((string) get_option('kinboni_bkash_merchant_number', ''));
                      if (!empty($bkash_merchant)) : 
                      ?>
                      <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-stone-200 dark:border-stone-800 cursor-pointer hover:border-gold-400 transition-colors">
                        <input type="radio" name="wc_payment_method" value="bKash / Nagad Express" onchange="if(window.CheckoutPageController){CheckoutPageController.togglePaymentFields(this.value);}" class="mt-0.5 text-gold-500 focus:ring-gold-500" />
                        <div class="text-xs flex-grow">
                          <span class="font-bold text-stone-900 dark:text-white block"><?php esc_html_e('bKash / Nagad Merchant Payment', 'kinboni-core'); ?></span>
                          <span class="text-stone-600 dark:text-stone-300 text-[11px]"><?php esc_html_e('Instant mobile wallet settlement with priority dispatch.', 'kinboni-core'); ?></span>
                        </div>
                      </label>

                      <div id="wc-bkash-fields" class="hidden p-3.5 rounded-xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 space-y-2 text-xs">
                        <p class="text-[11px] text-stone-600 dark:text-stone-400"><?php esc_html_e('Merchant Number:', 'kinboni-core'); ?> <strong class="text-gold-700 dark:text-gold-400 font-mono"><?php echo esc_html($bkash_merchant); ?></strong></p>
                        <input type="text" id="wc-bkash-trx" placeholder="<?php esc_attr_e('Enter Transaction ID (TrxID)', 'kinboni-core'); ?>" class="w-full px-3 py-2 rounded-lg border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs" />
                      </div>
                      <?php endif; ?>
                    </div>

                    <button 
                      type="submit" 
                      id="wc-place-order-btn"
                      class="w-full py-4 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs sm:text-sm font-bold uppercase tracking-widest rounded-xl shadow-lg hover:shadow-gold-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                      <i data-lucide="shield-check" class="w-5 h-5"></i>
                      <span><?php echo $place_order_btn_text; ?></span>
                    </button>
                  </div>

                </form>
              </div>

              <div id="wc-checkout-thankyou-container" class="hidden bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-8 sm:p-12 shadow-sm max-w-3xl mx-auto text-center space-y-6">
                <div class="w-16 h-16 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                  <i data-lucide="check-circle-2" class="w-9 h-9"></i>
                </div>
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-widest">
                  <?php esc_html_e('WooCommerce Order Received', 'kinboni-core'); ?>
                </span>
                <h2 class="font-serif text-3xl sm:text-4xl font-medium text-stone-900 dark:text-white">
                  <?php echo $ty_title; ?>
                </h2>
                <p class="text-sm text-stone-600 dark:text-stone-300 max-w-lg mx-auto font-normal">
                  <?php echo $ty_desc; ?>
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-left text-xs">
                  <div>
                    <span class="text-stone-400 block uppercase text-xs"><?php esc_html_e('Order Number:', 'kinboni-core'); ?></span>
                    <strong id="wc-ty-order-id" class="font-mono text-stone-900 dark:text-white text-sm">#KB-849201</strong>
                  </div>
                  <div>
                    <span class="text-stone-400 block uppercase text-xs"><?php esc_html_e('Date:', 'kinboni-core'); ?></span>
                    <strong id="wc-ty-date" class="text-stone-900 dark:text-white"><?php echo date_i18n('M d, Y'); ?></strong>
                  </div>
                  <div>
                    <span class="text-stone-400 block uppercase text-xs"><?php esc_html_e('Total Payable:', 'kinboni-core'); ?></span>
                    <strong id="wc-ty-total" class="text-gold-700 dark:text-gold-400 font-serif text-base"><?php echo wp_kses_post(wc_price($initial_total_raw)); ?></strong>
                  </div>
                  <div>
                    <span class="text-stone-400 block uppercase text-xs"><?php esc_html_e('Payment Method:', 'kinboni-core'); ?></span>
                    <strong id="wc-ty-method" class="text-stone-900 dark:text-white"><?php esc_html_e('Cash on Delivery', 'kinboni-core'); ?></strong>
                  </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-3 pt-4">
                  <button type="button" onclick="window.print()" class="px-6 py-3.5 rounded-xl bg-stone-900 text-white dark:bg-stone-800 text-xs font-bold uppercase tracking-widest flex items-center gap-2 hover:bg-gold-500 hover:text-stone-950 transition-all cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span><?php esc_html_e('Print Official Invoice', 'kinboni-core'); ?></span>
                  </button>
                  <a href="<?php echo esc_url($myaccount_url); ?>" onclick="if(window.AccountPageController){AccountPageController.openOrdersView(); return false;}" class="btn-primary rounded-xl inline-block px-6 py-3.5 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest transition-all">
                    <?php esc_html_e('View in My Account Orders', 'kinboni-core'); ?>
                  </a>
                  <a href="<?php echo esc_url($shop_url); ?>" onclick="if(window.RouterController){RouterController.navigate('shop'); return false;}" class="btn-outline rounded-xl inline-block px-6 py-3.5 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:border-gold-500 text-xs font-bold uppercase tracking-widest transition-all">
                    <?php esc_html_e('Continue Shopping', 'kinboni-core'); ?>
                  </a>
                </div>
              </div>

            </div>
        </main>
        <?php
    }
}

