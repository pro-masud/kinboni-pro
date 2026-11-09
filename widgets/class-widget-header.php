<?php
/**
 * Kinboni Haute Atelier Header & Navigation - Elementor Widget
 *
 * Implements the luxury unified header with:
 * - Brand Monogram badge & haute atelier typography
 * - Dynamic desktop navigation links
 * - Interactive 10-page luxury mega dropdown menu with icons and badges
 * - Quick Action Buttons: Live Ajax Search, Dark/Light Mode Switcher,
 *   Patron Account Concierge, Saved Wishlist with live counter badge,
 *   and Shopping Bag Drawer trigger with live WooCommerce item counter.
 * - Mobile drawer hamburger trigger
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

class Kinboni_Widget_Header extends Widget_Base {

    public function get_name() {
        return 'kinboni_header';
    }

    public function get_title() {
        return esc_html__('Haute Atelier Header & Navigation', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-header';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('header', 'nav', 'navigation', 'menu', 'logo', 'cart', 'wishlist', 'search', 'atelier', 'kinboni');
    }

    /**
     * Get available WordPress navigation menus
     */
    private function get_available_menus() {
        $menus = wp_get_nav_menus();
        $options = array('0' => esc_html__('— Select Menu —', 'kinboni-core'));
        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $options[$menu->slug] = $menu->name;
            }
        }
        return $options;
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: BRAND & LOGO
        // ==========================================
        $this->start_controls_section(
            'section_brand_content',
            array(
                'label' => esc_html__('Brand & Logo', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'logo_type',
            array(
                'label'   => esc_html__('Logo Type', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'monogram',
                'options' => array(
                    'monogram' => esc_html__('Monogram Badge + Wordmark', 'kinboni-core'),
                    'image'    => esc_html__('Custom Image Logo', 'kinboni-core'),
                ),
            )
        );

        $this->add_control(
            'monogram_letter',
            array(
                'label'       => esc_html__('Monogram Letter', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'K',
                'condition'   => array('logo_type' => 'monogram'),
            )
        );

        $this->add_control(
            'brand_title',
            array(
                'label'       => esc_html__('Brand Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'KINBONI',
                'condition'   => array('logo_type' => 'monogram'),
            )
        );

        $this->add_control(
            'brand_subtitle',
            array(
                'label'       => esc_html__('Brand Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'HAUTE ATELIER',
                'condition'   => array('logo_type' => 'monogram'),
            )
        );

        $this->add_control(
            'custom_logo_image',
            array(
                'label'     => esc_html__('Custom Logo Image', 'kinboni-core'),
                'type'      => Controls_Manager::MEDIA,
                'default'   => array('url' => ''),
                'condition' => array('logo_type' => 'image'),
            )
        );

        $this->add_control(
            'brand_link',
            array(
                'label'       => esc_html__('Logo Link URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com', 'kinboni-core'),
                'default'     => array('url' => home_url('/')),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: NAVIGATION MENU
        // ==========================================
        $this->start_controls_section(
            'section_nav_content',
            array(
                'label' => esc_html__('Navigation Menu', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'nav_mode',
            array(
                'label'   => esc_html__('Navigation Mode', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'unified',
                'options' => array(
                    'unified' => esc_html__('Unified Atelier Bar + Mega Dropdown', 'kinboni-core'),
                    'wp_menu' => esc_html__('WordPress Custom Menu', 'kinboni-core'),
                ),
            )
        );

        $this->add_control(
            'selected_wp_menu',
            array(
                'label'     => esc_html__('Select WordPress Menu', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => $this->get_available_menus(),
                'default'   => '0',
                'condition' => array('nav_mode' => 'wp_menu'),
            )
        );

        $this->add_control(
            'show_pages_dropdown',
            array(
                'label'        => esc_html__('Show Pages Mega Dropdown', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('nav_mode' => 'unified'),
            )
        );

        $this->add_control(
            'pages_badge_count',
            array(
                'label'       => esc_html__('Pages Badge Count', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '10',
                'condition'   => array(
                    'nav_mode'            => 'unified',
                    'show_pages_dropdown' => 'yes',
                ),
            )
        );

        $this->add_control(
            'flash_deals_badge',
            array(
                'label'       => esc_html__('Flash Deals Badge', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '-20%',
                'condition'   => array('nav_mode' => 'unified'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: ACTION BUTTONS & ICONS
        // ==========================================
        $this->start_controls_section(
            'section_action_icons',
            array(
                'label' => esc_html__('Action Buttons & Icons', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_search',
            array(
                'label'        => esc_html__('Show Search Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'search_placeholder',
            array(
                'label'       => esc_html__('Search Pill Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Search creations...', 'kinboni-core'),
                'condition'   => array('show_search' => 'yes'),
            )
        );

        $this->add_control(
            'show_theme_toggle',
            array(
                'label'        => esc_html__('Show Dark/Light Mode Toggle', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_account',
            array(
                'label'        => esc_html__('Show Patron Account Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'account_url',
            array(
                'label'       => esc_html__('Account Page URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com/my-account', 'kinboni-core'),
                'condition'   => array('show_account' => 'yes'),
            )
        );

        $this->add_control(
            'show_wishlist',
            array(
                'label'        => esc_html__('Show Wishlist Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_cart',
            array(
                'label'        => esc_html__('Show Shopping Bag Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'cart_button_label',
            array(
                'label'       => esc_html__('Bag Button Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Bag', 'kinboni-core'),
                'condition'   => array('show_cart' => 'yes'),
            )
        );

        $this->add_control(
            'show_mobile_menu',
            array(
                'label'        => esc_html__('Show Mobile Hamburger', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: HEADER BEHAVIOR
        // ==========================================
        $this->start_controls_section(
            'section_header_settings',
            array(
                'label' => esc_html__('Header Behavior', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'is_sticky',
            array(
                'label'        => esc_html__('Sticky on Scroll', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'backdrop_blur',
            array(
                'label'        => esc_html__('Enable Glassmorphism / Blur', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: HEADER STYLING
        // ==========================================
        $this->start_controls_section(
            'section_style_header',
            array(
                'label' => esc_html__('Header Styling', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'header_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #site-header' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'header_border_color',
            array(
                'label'     => esc_html__('Border Bottom Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #site-header' => 'border-bottom-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'nav_typography',
                'label'    => esc_html__('Nav Links Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} .wc-nav-link, {{WRAPPER}} nav a',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render the Header Widget Output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $is_sticky       = ('yes' === $settings['is_sticky']);
        $has_blur        = ('yes' === $settings['backdrop_blur']);
        $logo_type       = !empty($settings['logo_type']) ? $settings['logo_type'] : 'monogram';
        $brand_letter    = !empty($settings['monogram_letter']) ? $settings['monogram_letter'] : 'K';
        $brand_title     = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'brand_title', 'brand_name', 'KINBONI', $this) : (!empty($settings['brand_title']) ? $settings['brand_title'] : 'KINBONI');
        $brand_subtitle  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'brand_subtitle', 'brand_tagline', 'HAUTE ATELIER', $this) : (!empty($settings['brand_subtitle']) ? $settings['brand_subtitle'] : 'HAUTE ATELIER');
        $brand_url       = !empty($settings['brand_link']['url']) ? $settings['brand_link']['url'] : home_url('/');

        $nav_mode        = !empty($settings['nav_mode']) ? $settings['nav_mode'] : 'unified';
        $show_dropdown   = ('yes' === $settings['show_pages_dropdown']);
        $pages_badge     = !empty($settings['pages_badge_count']) ? $settings['pages_badge_count'] : '10';
        $deals_badge     = !empty($settings['flash_deals_badge']) ? $settings['flash_deals_badge'] : '-20%';

        $show_search     = ('yes' === $settings['show_search']);
        $search_hint     = !empty($settings['search_placeholder']) ? $settings['search_placeholder'] : __('Search creations...', 'kinboni-core');
        $show_theme      = ('yes' === $settings['show_theme_toggle']);
        $show_account    = ('yes' === $settings['show_account']);
        $show_wishlist   = ('yes' === $settings['show_wishlist']);
        $show_cart       = ('yes' === $settings['show_cart']);
        $cart_label      = !empty($settings['cart_button_label']) ? $settings['cart_button_label'] : __('Bag', 'kinboni-core');
        $show_mobile_btn = ('yes' === $settings['show_mobile_menu']);

        // Dynamic WooCommerce page links
        $shop_url     = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
        $cart_url     = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart');
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout');
        $account_url  = !empty($settings['account_url']['url']) 
            ? $settings['account_url']['url'] 
            : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account'));

        // Try getting a real single product link if available
        $sample_product_url = home_url('/product');
        if (class_exists('WooCommerce')) {
            $recent_prods = wc_get_products(array('limit' => 1, 'status' => 'publish'));
            if (!empty($recent_prods)) {
                $sample_product_url = get_permalink($recent_prods[0]->get_id());
            }
        }

        $wishlist_url    = home_url('/wishlist');
        $flash_deals_url = home_url('/flash-deals');
        $contact_url     = home_url('/contact');
        $faq_url         = home_url('/faq');

        // Dynamic Cart Count
        $cart_count = 0;
        if (function_exists('WC') && WC()->cart) {
            $cart_count = WC()->cart->get_cart_contents_count();
        }

        // Active path check
        $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));
        $is_home     = is_front_page() || is_home();
        $is_shop     = function_exists('is_shop') && is_shop();
        $is_product  = function_exists('is_product') && is_product();
        $is_cart     = function_exists('is_cart') && is_cart();
        $is_checkout = function_exists('is_checkout') && is_checkout();
        $is_account  = function_exists('is_account_page') && is_account_page();

        $header_classes = array('w-full', 'transition-all', 'duration-300', 'border-b', 'border-stone-200/70', 'dark:border-stone-800/70');
        if ($is_sticky) {
            $header_classes[] = 'sticky';
            $header_classes[] = 'top-0';
            $header_classes[] = 'z-40';
        }
        if ($has_blur) {
            $header_classes[] = 'bg-[#FAF8F5]/90';
            $header_classes[] = 'dark:bg-[#0E0E10]/90';
            $header_classes[] = 'backdrop-blur-md';
        } else {
            $header_classes[] = 'bg-[#FAF8F5]';
            $header_classes[] = 'dark:bg-[#0E0E10]';
        }
        ?>
        <header
          id="site-header"
          class="<?php echo esc_attr(implode(' ', $header_classes)); ?>"
        >
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4 sm:gap-6">
              
              <!-- Mobile Menu Hamburger Button -->
              <?php if ($show_mobile_btn) : ?>
              <div class="flex items-center xl:hidden">
                <button
                  id="mobile-menu-btn"
                  type="button"
                  class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                  aria-label="<?php esc_attr_e('Open mobile navigation menu', 'kinboni-core'); ?>"
                >
                  <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
              </div>
              <?php endif; ?>

              <!-- Brand Monogram & Wordmark Logo -->
              <a
                href="<?php echo esc_url($brand_url); ?>"
                class="group flex items-center gap-2 sm:gap-3 flex-shrink-0"
                aria-label="<?php echo esc_attr($brand_title); ?>"
              >
                <?php if ('image' === $logo_type && !empty($settings['custom_logo_image']['url'])) : ?>
                  <img src="<?php echo esc_url($settings['custom_logo_image']['url']); ?>" alt="<?php echo esc_attr($brand_title); ?>" class="h-10 sm:h-12 w-auto object-contain transition-transform group-hover:scale-105" />
                <?php else : ?>
                  <span
                    class="hidden sm:grid place-items-center w-10 h-10 rounded-full bg-stone-900 dark:bg-gold-400 text-gold-300 dark:text-stone-950 font-serif text-xl font-semibold shadow-sm transition-transform group-hover:scale-105"
                  ><?php echo esc_html($brand_letter); ?></span>
                  <span class="flex flex-col leading-none">
                    <span
                      class="font-serif text-xl sm:text-[26px] font-semibold tracking-[0.2em] sm:tracking-[0.22em] text-stone-900 dark:text-white group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors"
                    ><?php echo esc_html($brand_title); ?></span>
                    <span
                      class="text-xs tracking-[0.35em] sm:tracking-[0.4em] text-gold-700 dark:text-gold-400 font-semibold mt-0.5 sm:mt-1"
                    ><?php echo esc_html($brand_subtitle); ?></span>
                  </span>
                <?php endif; ?>
              </a>

              <!-- Desktop Navigation Links -->
              <nav
                class="hidden xl:flex items-center gap-3.5 2xl:gap-6 whitespace-nowrap"
                aria-label="<?php esc_attr_e('Main navigation', 'kinboni-core'); ?>"
              >
                <?php if ('wp_menu' === $nav_mode && !empty($settings['selected_wp_menu'])) : ?>
                  <?php
                  wp_nav_menu(array(
                      'menu'        => $settings['selected_wp_menu'],
                      'container'   => false,
                      'menu_class'  => 'flex items-center gap-4 xl:gap-6',
                      'fallback_cb' => false,
                  ));
                  ?>
                <?php else : ?>
                  <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    data-nav-view="home"
                    class="wc-nav-link whitespace-nowrap relative text-sm font-semibold tracking-wide <?php echo $is_home ? 'text-gold-700 dark:text-gold-400 after:w-full' : 'text-stone-700 dark:text-stone-300 after:w-0 hover:after:w-full'; ?> hover:text-gold-600 dark:hover:text-gold-400 transition-colors after:absolute after:left-0 after:-bottom-1.5 after:h-0.5 after:bg-gold-500 after:transition-all"
                  ><?php esc_html_e('Home', 'kinboni-core'); ?></a>

                  <a
                    href="<?php echo esc_url($shop_url); ?>"
                    data-nav-view="shop"
                    class="wc-nav-link whitespace-nowrap relative text-sm font-semibold tracking-wide <?php echo $is_shop ? 'text-gold-700 dark:text-gold-400 after:w-full' : 'text-stone-700 dark:text-stone-300 after:w-0 hover:after:w-full'; ?> hover:text-gold-600 dark:hover:text-gold-400 transition-colors after:absolute after:left-0 after:-bottom-1.5 after:h-0.5 after:bg-gold-500 after:transition-all"
                  ><?php esc_html_e('Shop', 'kinboni-core'); ?></a>

                  <a
                    href="<?php echo esc_url($sample_product_url); ?>"
                    data-nav-view="product"
                    class="wc-nav-link whitespace-nowrap relative text-sm font-semibold tracking-wide <?php echo $is_product ? 'text-gold-700 dark:text-gold-400 after:w-full' : 'text-stone-700 dark:text-stone-300 after:w-0 hover:after:w-full'; ?> hover:text-gold-600 dark:hover:text-gold-400 transition-colors after:absolute after:left-0 after:-bottom-1.5 after:h-0.5 after:bg-gold-500 after:transition-all"
                  ><?php esc_html_e('Product', 'kinboni-core'); ?></a>

                  <a
                    href="<?php echo esc_url($cart_url); ?>"
                    data-nav-view="cart"
                    class="wc-nav-link whitespace-nowrap relative text-sm font-semibold tracking-wide <?php echo $is_cart ? 'text-gold-700 dark:text-gold-400 after:w-full' : 'text-stone-700 dark:text-stone-300 after:w-0 hover:after:w-full'; ?> hover:text-gold-600 dark:hover:text-gold-400 transition-colors after:absolute after:left-0 after:-bottom-1.5 after:h-0.5 after:bg-gold-500 after:transition-all"
                  ><?php esc_html_e('Cart', 'kinboni-core'); ?></a>

                  <a
                    href="<?php echo esc_url($checkout_url); ?>"
                    data-nav-view="checkout"
                    class="wc-nav-link whitespace-nowrap relative text-sm font-semibold tracking-wide <?php echo $is_checkout ? 'text-gold-700 dark:text-gold-400 after:w-full' : 'text-stone-700 dark:text-stone-300 after:w-0 hover:after:w-full'; ?> hover:text-gold-600 dark:hover:text-gold-400 transition-colors after:absolute after:left-0 after:-bottom-1.5 after:h-0.5 after:bg-gold-500 after:transition-all"
                  ><?php esc_html_e('Checkout', 'kinboni-core'); ?></a>

                  <a
                    href="<?php echo esc_url($account_url); ?>"
                    data-nav-view="my-account"
                    class="wc-nav-link whitespace-nowrap relative text-sm font-semibold tracking-wide <?php echo $is_account ? 'text-gold-700 dark:text-gold-400 after:w-full' : 'text-stone-700 dark:text-stone-300 after:w-0 hover:after:w-full'; ?> hover:text-gold-600 dark:hover:text-gold-400 transition-colors after:absolute after:left-0 after:-bottom-1.5 after:h-0.5 after:bg-gold-500 after:transition-all"
                  ><?php esc_html_e('My Account', 'kinboni-core'); ?></a>

                  <a
                    href="<?php echo esc_url($flash_deals_url); ?>"
                    data-nav-view="flash-deals"
                    class="wc-nav-link whitespace-nowrap relative text-sm font-semibold tracking-wide text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 transition-colors after:absolute after:left-0 after:-bottom-1.5 after:h-0.5 after:w-0 after:bg-gold-500 after:transition-all hover:after:w-full flex items-center gap-1"
                  >
                    <span><?php esc_html_e('Flash Deals', 'kinboni-core'); ?></span>
                    <?php if (!empty($deals_badge)) : ?>
                    <span
                      class="px-1.5 py-0.5 rounded-full bg-rose-600 text-white text-[11px] font-bold uppercase tracking-wider leading-none"
                    ><?php echo esc_html($deals_badge); ?></span>
                    <?php endif; ?>
                  </a>

                  <!-- All Pages Mega Dropdown Menu -->
                  <?php if ($show_dropdown) : ?>
                  <div
                    id="pages-dropdown-group"
                    class="relative"
                    onmouseenter="window.NavMenuController ? NavMenuController.openDropdown() : null"
                    onmouseleave="window.NavMenuController ? NavMenuController.closeDropdown() : null"
                  >
                    <button
                      id="pages-dropdown-btn"
                      type="button"
                      onclick="window.NavMenuController ? NavMenuController.toggleDropdown(event) : null"
                      class="flex items-center gap-1.5 text-sm font-semibold tracking-wide text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 transition-colors py-2 whitespace-nowrap cursor-pointer"
                      aria-expanded="false"
                    >
                      <span><?php esc_html_e('Pages', 'kinboni-core'); ?></span>
                      <?php if (!empty($pages_badge)) : ?>
                      <span
                        class="px-1.5 py-0.5 rounded-full bg-gold-500/20 text-gold-700 dark:text-gold-300 text-[11px] font-bold leading-none"
                      ><?php echo esc_html($pages_badge); ?></span>
                      <?php endif; ?>
                      <i
                        id="pages-dropdown-chevron"
                        data-lucide="chevron-down"
                        class="w-3.5 h-3.5 transition-transform duration-200"
                      ></i>
                    </button>

                    <!-- Dropdown Card -->
                    <div
                      id="pages-dropdown-panel"
                      class="absolute right-0 xl:left-1/2 xl:-translate-x-1/2 top-full pt-2 w-[480px] opacity-0 invisible translate-y-2 pointer-events-none transition-all duration-200 z-50"
                    >
                      <div
                        class="p-5 rounded-3xl bg-white/95 dark:bg-stone-900/95 backdrop-blur-xl border border-stone-200/90 dark:border-stone-800 shadow-2xl grid grid-cols-2 gap-6 text-left"
                      >
                        <div>
                          <span
                            class="text-xs font-bold uppercase tracking-[0.2em] text-gold-700 dark:text-gold-400 block mb-2.5 pb-1.5 border-b border-stone-200/70 dark:border-stone-800"
                          ><?php esc_html_e('WooCommerce Core (1–5)', 'kinboni-core'); ?></span>
                          <ul class="space-y-1.5 text-sm">
                            <li>
                              <a
                                href="<?php echo esc_url(home_url('/')); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>1. <?php esc_html_e('Home Storefront', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="home"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($shop_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>2. <?php esc_html_e('Shop Catalogue', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="shopping-bag"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($sample_product_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>3. <?php esc_html_e('Product Single Page', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="watch"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($cart_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>4. <?php esc_html_e('Shopping Cart Page', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="shopping-cart"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($checkout_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>5. <?php esc_html_e('Checkout Page', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="credit-card"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                          </ul>
                        </div>
                        <div>
                          <span
                            class="text-xs font-bold uppercase tracking-[0.2em] text-gold-700 dark:text-gold-400 block mb-2.5 pb-1.5 border-b border-stone-200/70 dark:border-stone-800"
                          ><?php esc_html_e('Patron & Concierge (6–10)', 'kinboni-core'); ?></span>
                          <ul class="space-y-1.5 text-sm">
                            <li>
                              <a
                                href="<?php echo esc_url($account_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>6. <?php esc_html_e('My Account Portal', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="user-check"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($wishlist_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>7. <?php esc_html_e('Saved Wishlist', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="heart"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($flash_deals_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>8. <?php esc_html_e('Archive Flash Deals (-20%)', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="flame"
                                class="w-3.5 h-3.5 text-rose-500"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($contact_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>9. <?php esc_html_e('Contact & Boutique', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="map-pin"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                            <li>
                              <a
                                href="<?php echo esc_url($faq_url); ?>"
                                onclick="window.NavMenuController && NavMenuController.closeDropdown(true);"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-gold-500/15 hover:text-gold-600 dark:hover:text-gold-400 transition-colors"
                              ><span>10. <?php esc_html_e('FAQ & Care Policies', 'kinboni-core'); ?></span
                              ><i
                                data-lucide="help-circle"
                                class="w-3.5 h-3.5 opacity-60"
                              ></i
                              ></a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>
                <?php endif; ?>
              </nav>

              <!-- Right Action Icons (Search, Theme, Account, Wishlist, Bag) -->
              <div class="flex items-center gap-1 sm:gap-2">
                
                <!-- Search Button -->
                <?php if ($show_search) : ?>
                <button
                  type="button"
                  class="open-search-btn hidden xl:flex items-center gap-2 pl-3.5 pr-4 py-2 whitespace-nowrap rounded-full border border-stone-300 dark:border-stone-700 text-xs text-stone-600 dark:text-stone-300 hover:border-gold-500 hover:text-gold-600 dark:hover:text-gold-400 transition-colors cursor-pointer"
                  aria-label="<?php esc_attr_e('Search creations', 'kinboni-core'); ?>"
                >
                  <i data-lucide="search" class="w-3.5 h-3.5"></i>
                  <span class="font-medium"><?php echo esc_html($search_hint); ?></span>
                </button>
                <button
                  type="button"
                  class="open-search-btn xl:hidden w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                  aria-label="<?php esc_attr_e('Search creations', 'kinboni-core'); ?>"
                >
                  <i data-lucide="search" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </button>
                <?php endif; ?>

                <!-- Dark/Light Theme Toggle -->
                <?php if ($show_theme) : ?>
                <button
                  type="button"
                  class="theme-toggle-btn w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                  aria-label="<?php esc_attr_e('Toggle dark and light mode', 'kinboni-core'); ?>"
                >
                  <i
                    data-lucide="sun"
                    class="theme-sun-icon w-4 h-4 sm:w-5 sm:h-5 hidden"
                  ></i>
                  <i
                    data-lucide="moon"
                    class="theme-moon-icon w-4 h-4 sm:w-5 sm:h-5"
                  ></i>
                </button>
                <?php endif; ?>

                <!-- Patron Account Concierge -->
                <?php if ($show_account) : ?>
                <a
                  href="<?php echo esc_url($account_url); ?>"
                  class="open-account-btn hidden sm:flex w-10 h-10 rounded-full items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                  aria-label="<?php esc_attr_e('Patron Account Concierge', 'kinboni-core'); ?>"
                >
                  <i data-lucide="user" class="w-5 h-5"></i>
                </a>
                <?php endif; ?>

                <!-- Wishlist Button with Badge -->
                <?php if ($show_wishlist) : ?>
                <button
                  type="button"
                  class="open-wishlist-btn relative w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                  aria-label="<?php esc_attr_e('View Saved Wishlist', 'kinboni-core'); ?>"
                >
                  <i data-lucide="heart" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                  <span
                    class="wishlist-count-badge absolute top-0.5 right-0.5 transform scale-0 transition-transform duration-300 flex items-center justify-center min-w-[16px] h-[16px] sm:min-w-[18px] sm:h-[18px] px-1 text-[11px] sm:text-xs font-bold text-white bg-rose-600 rounded-full shadow-sm"
                  >0</span>
                </button>
                <?php endif; ?>

                <!-- Shopping Bag Button with Live Item Counter -->
                <?php if ($show_cart) : ?>
                <button
                  type="button"
                  class="open-cart-btn relative w-8 h-8 sm:w-auto p-0 sm:pl-3.5 sm:pr-4 sm:py-2 rounded-full bg-stone-900 dark:bg-gold-400 text-white dark:text-stone-950 hover:bg-gold-600 dark:hover:bg-gold-300 transition-all shadow-sm active:scale-95 flex items-center justify-center sm:gap-2 flex-shrink-0 cursor-pointer"
                  aria-label="<?php esc_attr_e('Open Shopping Bag', 'kinboni-core'); ?>"
                >
                  <i
                    data-lucide="shopping-bag"
                    class="cart-badge-icon w-4 h-4 transition-transform duration-200"
                  ></i>
                  <span class="hidden sm:inline text-xs font-bold tracking-wide"
                  ><?php echo esc_html($cart_label); ?></span>
                  <span
                    class="cart-count-badge absolute -top-1 -right-1 sm:-top-1.5 sm:-right-1.5 <?php echo $cart_count > 0 ? 'scale-100' : 'scale-0'; ?> transform transition-transform duration-300 flex items-center justify-center min-w-[18px] h-[18px] sm:min-w-[20px] sm:h-[20px] px-1 text-xs font-bold text-stone-950 bg-gold-400 dark:bg-white rounded-full shadow-md"
                  ><?php echo esc_html($cart_count); ?></span>
                </button>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </header>
        <?php
    }
}

