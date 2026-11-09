<?php
/**
 * Kinboni My Account & Patron Concierge Portal - Elementor Widget
 *
 * Implements the luxury editorial WooCommerce My Account page matching my-account.html
 * featuring Patron VIP Badge, Left Editorial Navigation, Luxury Welcome Banner with Click-to-Copy
 * Privilege Promo Code, Dynamic Stats Grid, Orders History Table with Status Tracking,
 * Digital Authenticity Certificates & Care Manuals, Saved Addresses, Edit Profile Form
 * with Password updating, and Dual Split Sign In & Registration screen.
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

class Kinboni_Widget_My_Account extends Widget_Base {

    public function get_name() {
        return 'kinboni_my_account';
    }

    public function get_title() {
        return esc_html__('My Account & Patron Portal', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-user-circle-o';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('account', 'my account', 'patron', 'orders', 'profile', 'downloads', 'addresses', 'login', 'register', 'kinboni');
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
            'breadcrumb_account_text',
            array(
                'label'       => esc_html__('Breadcrumb Account Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'My Account',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'pre_title',
            array(
                'label'       => esc_html__('Subtitle / Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Patron Concierge Portal',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'title',
            array(
                'label'       => esc_html__('Page Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'My Account',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'vip_badge_text',
            array(
                'label'       => esc_html__('VIP Patron Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Verified VIP Patron',
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: DASHBOARD & WELCOME BANNER
        // ==========================================
        $this->start_controls_section(
            'section_dashboard_content',
            array(
                'label' => esc_html__('Dashboard & Welcome Banner', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'circle_badge_text',
            array(
                'label'       => esc_html__('Circle Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Kinboni Private Circle',
            )
        );

        $this->add_control(
            'welcome_prefix',
            array(
                'label'       => esc_html__('Welcome Prefix', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Welcome back,',
            )
        );

        $this->add_control(
            'dashboard_desc',
            array(
                'label'       => esc_html__('Dashboard Description', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.',
            )
        );

        $this->add_control(
            'promo_label',
            array(
                'label'       => esc_html__('Promo Code Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Member Privilege Code',
            )
        );

        $this->add_control(
            'promo_code',
            array(
                'label'       => esc_html__('Privilege Promo Code', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'LUXE20',
            )
        );

        $this->add_control(
            'promo_badge_text',
            array(
                'label'       => esc_html__('Promo Button Display Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'LUXE20 (20% OFF)',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: STATS GRID
        // ==========================================
        $this->start_controls_section(
            'section_stats_content',
            array(
                'label' => esc_html__('Stats Cards', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'stat_orders_label',
            array(
                'label'       => esc_html__('Orders Stat Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Total Atelier Orders',
            )
        );

        $this->add_control(
            'stat_wishlist_label',
            array(
                'label'       => esc_html__('Wishlist Stat Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Saved Wishlist Items',
            )
        );

        $this->add_control(
            'stat_warranty_label',
            array(
                'label'       => esc_html__('Warranty Stat Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Warranty Protection',
            )
        );

        $this->add_control(
            'stat_warranty_val',
            array(
                'label'       => esc_html__('Warranty Status Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Lifetime Active',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CERTIFICATES & CARE MANUALS
        // ==========================================
        $this->start_controls_section(
            'section_downloads_content',
            array(
                'label' => esc_html__('Certificates & Care Manuals', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'downloads_title',
            array(
                'label'       => esc_html__('Downloads Section Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Digital Authenticity Certificates & Care Manuals',
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
                'label' => esc_html__('Typography & Accents', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'account_title_typography',
                'label'    => esc_html__('Page Title Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} #page-view-my-account h1',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'panel_heading_typography',
                'label'    => esc_html__('Panel Headings Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} .wc-acc-panel h2',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $show_header        = !empty($settings['show_header']) && $settings['show_header'] === 'yes';
        $breadcrumb_home    = !empty($settings['breadcrumb_home_text']) ? esc_html($settings['breadcrumb_home_text']) : 'Home';
        $breadcrumb_acc     = !empty($settings['breadcrumb_account_text']) ? esc_html($settings['breadcrumb_account_text']) : 'My Account';
        $pre_title          = !empty($settings['pre_title']) ? esc_html($settings['pre_title']) : 'Patron Concierge Portal';
        $title              = !empty($settings['title']) ? esc_html($settings['title']) : 'My Account';
        $vip_badge          = !empty($settings['vip_badge_text']) ? esc_html($settings['vip_badge_text']) : 'Verified VIP Patron';

        $circle_badge       = !empty($settings['circle_badge_text']) ? esc_html($settings['circle_badge_text']) : 'Kinboni Private Circle';
        $welcome_prefix     = !empty($settings['welcome_prefix']) ? esc_html($settings['welcome_prefix']) : 'Welcome back,';
        $dashboard_desc     = !empty($settings['dashboard_desc']) ? esc_html($settings['dashboard_desc']) : 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.';
        $promo_label        = !empty($settings['promo_label']) ? esc_html($settings['promo_label']) : 'Member Privilege Code';
        $promo_code         = !empty($settings['promo_code']) ? esc_attr($settings['promo_code']) : 'LUXE20';
        $promo_badge        = !empty($settings['promo_badge_text']) ? esc_html($settings['promo_badge_text']) : 'LUXE20 (20% OFF)';

        $stat_orders_lbl    = !empty($settings['stat_orders_label']) ? esc_html($settings['stat_orders_label']) : 'Total Atelier Orders';
        $stat_wish_lbl      = !empty($settings['stat_wishlist_label']) ? esc_html($settings['stat_wishlist_label']) : 'Saved Wishlist Items';
        $stat_warranty_lbl  = !empty($settings['stat_warranty_label']) ? esc_html($settings['stat_warranty_label']) : 'Warranty Protection';
        $stat_warranty_val  = !empty($settings['stat_warranty_val']) ? esc_html($settings['stat_warranty_val']) : 'Lifetime Active';

        $downloads_title    = !empty($settings['downloads_title']) ? esc_html($settings['downloads_title']) : 'Digital Authenticity Certificates & Care Manuals';

        $show_ambient       = !empty($settings['show_ambient_glow']) && $settings['show_ambient_glow'] === 'yes';
        $show_arches        = !empty($settings['show_arch_shapes']) && $settings['show_arch_shapes'] === 'yes';

        $home_url           = home_url('/');
        $wishlist_url       = home_url('/wishlist/');
        $is_logged_in       = is_user_logged_in();

        // Current User Profile Data
        $current_user = wp_get_current_user();
        $user_id      = $current_user ? $current_user->ID : 0;
        $display_name = ($user_id > 0) ? $current_user->display_name : 'Tahmid Rahman';
        $default_email   = class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_email', 'concierge@kinboni.com') : 'concierge@kinboni.com';
        $default_phone   = class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_phone', '01711000000') : '01711000000';
        $default_address = class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_address', 'Banani, Road 11, Dhaka, Bangladesh') : 'Banani, Road 11, Dhaka, Bangladesh';

        $user_email   = ($user_id > 0) ? $current_user->user_email : $default_email;
        $user_phone   = ($user_id > 0) ? get_user_meta($user_id, 'billing_phone', true) : $default_phone;
        if (empty($user_phone)) $user_phone = $default_phone;
        $user_address = ($user_id > 0) ? get_user_meta($user_id, 'billing_address_1', true) : $default_address;
        if (empty($user_address)) $user_address = $default_address;

        // Retrieve real WooCommerce Orders if logged in
        $orders_data = array();
        if ($user_id > 0 && function_exists('wc_get_orders')) {
            $customer_orders = wc_get_orders(array(
                'customer_id' => $user_id,
                'limit'       => 15,
                'orderby'     => 'date',
                'order'       => 'DESC',
            ));
            foreach ($customer_orders as $ord) {
                $orders_data[] = array(
                    'id'         => '#KB-' . $ord->get_id(),
                    'date'       => date_i18n('M d, Y', strtotime($ord->get_date_created())),
                    'status'     => wc_get_order_status_name($ord->get_status()),
                    'total'      => (float)$ord->get_total(),
                    'total_fmt'  => wc_price($ord->get_total()),
                    'itemsCount' => $ord->get_item_count(),
                    'method'     => $ord->get_payment_method_title(),
                );
            }
        }

        // Demo fallback orders if no orders found
        if (empty($orders_data)) {
            $orders_data = array(
                array(
                    'id'         => '#KB-849201',
                    'date'       => 'Oct 24, 2026',
                    'status'     => 'In Transit',
                    'total'      => 18500,
                    'total_fmt'  => wc_price(18500),
                    'itemsCount' => 1,
                    'method'     => 'Cash on Delivery (COD)',
                ),
                array(
                    'id'         => '#KB-793104',
                    'date'       => 'Oct 09, 2026',
                    'status'     => 'Completed',
                    'total'      => 14200,
                    'total_fmt'  => wc_price(14200),
                    'itemsCount' => 1,
                    'method'     => 'bKash Express',
                ),
            );
        }

        $order_count = count($orders_data);
        ?>
        <main id="page-view-my-account" class="wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]">
            
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
                    <span class="text-stone-900 dark:text-white font-semibold"><?php echo $breadcrumb_acc; ?></span>
                  </nav>
                  <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400"><?php echo $pre_title; ?></span>
                  <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white mt-1">
                    <?php echo $title; ?>
                  </h1>
                </div>
                <div class="flex items-center gap-3">
                  <span id="wc-account-status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full <?php echo $is_logged_in ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'; ?> text-xs font-bold">
                    <span class="w-2 h-2 rounded-full <?php echo $is_logged_in ? 'bg-emerald-500' : 'bg-emerald-500'; ?>"></span> <?php echo $vip_badge; ?>
                  </span>
                </div>
              </div>
              <?php endif; ?>

              <div id="wc-account-dashboard-wrap" class="<?php echo $is_logged_in ? '' : ''; ?> grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <nav class="woocommerce-MyAccount-navigation lg:col-span-3 bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-2xl border border-stone-200/80 dark:border-stone-800 p-4 shadow-sm space-y-1">
                  <button type="button" onclick="if(window.AccountPageController){AccountPageController.switchTab('dashboard');}" data-acc-tab="dashboard" class="wc-acc-nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 cursor-pointer">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span><?php esc_html_e('Dashboard', 'kinboni-core'); ?></span>
                  </button>
                  <button type="button" onclick="if(window.AccountPageController){AccountPageController.switchTab('orders');}" data-acc-tab="orders" class="wc-acc-nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 cursor-pointer">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span><?php esc_html_e('Orders', 'kinboni-core'); ?></span>
                  </button>
                  <button type="button" onclick="if(window.AccountPageController){AccountPageController.switchTab('downloads');}" data-acc-tab="downloads" class="wc-acc-nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 cursor-pointer">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    <span><?php esc_html_e('Certificates & Care', 'kinboni-core'); ?></span>
                  </button>
                  <button type="button" onclick="if(window.AccountPageController){AccountPageController.switchTab('addresses');}" data-acc-tab="addresses" class="wc-acc-nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 cursor-pointer">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span><?php esc_html_e('Addresses', 'kinboni-core'); ?></span>
                  </button>
                  <button type="button" onclick="if(window.AccountPageController){AccountPageController.switchTab('details');}" data-acc-tab="details" class="wc-acc-nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 cursor-pointer">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span><?php esc_html_e('Account Details', 'kinboni-core'); ?></span>
                  </button>
                  <button type="button" onclick="if(window.RouterController){RouterController.navigate('wishlist');}else if(window.KinboniCore){KinboniCore.openWishlist();}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 cursor-pointer">
                    <i data-lucide="heart" class="w-4 h-4 text-rose-500"></i>
                    <span><?php esc_html_e('Saved Wishlist', 'kinboni-core'); ?></span>
                  </button>
                  <div class="pt-2 mt-2 border-t border-stone-200 dark:border-stone-800">
                    <button type="button" onclick="if(window.AccountPageController){AccountPageController.toggleAuthView(true);}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all cursor-pointer">
                      <i data-lucide="log-out" class="w-4 h-4"></i>
                      <span><?php esc_html_e('Sign Out / Switch Account', 'kinboni-core'); ?></span>
                    </button>
                  </div>
                </nav>

                <div class="woocommerce-MyAccount-content lg:col-span-9 bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-8 shadow-sm">
                  
                  <div id="wc-acc-panel-dashboard" class="wc-acc-panel space-y-6">
                    <div class="p-6 rounded-2xl bg-stone-950 text-stone-100 border border-gold-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                      <div>
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-gold-400"><?php echo $circle_badge; ?></span>
                        <h2 class="font-serif text-2xl sm:text-3xl text-white mt-1">
                          <?php echo $welcome_prefix; ?> <span id="wc-acc-greeting-name" class="text-gold-400"><?php echo esc_html($display_name); ?></span>
                        </h2>
                        <p class="text-sm text-stone-400 mt-1 font-normal">
                          <?php echo $dashboard_desc; ?>
                        </p>
                      </div>
                      <div class="text-left sm:text-right flex-shrink-0">
                        <span class="text-xs uppercase tracking-widest text-stone-400 block"><?php echo $promo_label; ?></span>
                        <button type="button" onclick="if(window.copyPromoCode){copyPromoCode('<?php echo $promo_code; ?>');}" class="mt-1 px-3.5 py-1.5 rounded-lg bg-gold-400 hover:bg-gold-500 text-stone-950 font-mono text-xs font-bold tracking-wider transition-all cursor-pointer"><?php echo $promo_badge; ?></button>
                      </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800">
                        <span class="text-xs text-stone-500"><?php echo $stat_orders_lbl; ?></span>
                        <p id="wc-acc-stat-orders" class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1"><?php echo esc_html($order_count); ?></p>
                      </div>
                      <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800">
                        <span class="text-xs text-stone-500"><?php echo $stat_wish_lbl; ?></span>
                        <p id="wc-acc-stat-wishlist" class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1">0</p>
                      </div>
                      <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800">
                        <span class="text-xs text-stone-500"><?php echo $stat_warranty_lbl; ?></span>
                        <p class="font-serif text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-2"><?php echo $stat_warranty_val; ?></p>
                      </div>
                    </div>
                  </div>

                  <div id="wc-acc-panel-orders" class="wc-acc-panel hidden space-y-6">
                    <div class="flex items-center justify-between border-b border-stone-200 dark:border-stone-800 pb-4">
                      <h2 class="font-serif text-2xl font-medium text-stone-900 dark:text-white"><?php esc_html_e('Order History', 'kinboni-core'); ?></h2>
                      <span class="text-xs font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400"><?php esc_html_e('Verified Patron Dispatches', 'kinboni-core'); ?></span>
                    </div>
                    <div class="overflow-x-auto">
                      <table class="w-full text-left border-collapse text-sm">
                        <thead>
                          <tr class="border-b border-stone-200 dark:border-stone-800 text-[11px] uppercase tracking-wider text-stone-500">
                            <th class="py-3 px-4"><?php esc_html_e('Order', 'kinboni-core'); ?></th>
                            <th class="py-3 px-4"><?php esc_html_e('Date', 'kinboni-core'); ?></th>
                            <th class="py-3 px-4"><?php esc_html_e('Status', 'kinboni-core'); ?></th>
                            <th class="py-3 px-4"><?php esc_html_e('Total', 'kinboni-core'); ?></th>
                            <th class="py-3 px-4 text-right"><?php esc_html_e('Actions', 'kinboni-core'); ?></th>
                          </tr>
                        </thead>
                        <tbody id="wc-acc-orders-tbody" class="divide-y divide-stone-200/70 dark:divide-stone-800">
                          <?php foreach ($orders_data as $od) : ?>
                            <tr class="hover:bg-stone-50/60 dark:hover:bg-stone-950/40">
                              <td class="py-3.5 px-4 font-mono font-bold text-stone-900 dark:text-white"><?php echo esc_html($od['id']); ?></td>
                              <td class="py-3.5 px-4 text-stone-500"><?php echo esc_html($od['date']); ?></td>
                              <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-600 dark:text-emerald-400"><?php echo esc_html($od['status']); ?></span>
                              </td>
                              <td class="py-3.5 px-4 font-serif font-bold text-stone-900 dark:text-white"><?php echo wp_kses_post($od['total_fmt']); ?> <span class="text-[11px] font-sans font-normal text-stone-400">(<?php echo esc_html($od['itemsCount']); ?> item)</span></td>
                              <td class="py-3.5 px-4 text-right">
                                <button type="button" onclick="if(window.KinboniCore){KinboniCore.showToast('Order <?php echo esc_js($od['id']); ?> Status: <?php echo esc_js($od['status']); ?> — White-Glove Dispatch', 'info');}" class="px-3 py-1.5 rounded-lg bg-stone-900 text-white dark:bg-stone-800 text-[11px] font-bold uppercase tracking-wider hover:bg-gold-500 hover:text-stone-950 transition-colors cursor-pointer">
                                  <?php esc_html_e('View Status', 'kinboni-core'); ?>
                                </button>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div id="wc-acc-panel-downloads" class="wc-acc-panel hidden space-y-6">
                    <h2 class="font-serif text-2xl font-medium text-stone-900 dark:text-white border-b border-stone-200 dark:border-stone-800 pb-4">
                      <?php echo $downloads_title; ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 flex items-center justify-between gap-4">
                        <div>
                          <span class="text-xs font-bold uppercase tracking-widest text-gold-700 dark:text-gold-400"><?php esc_html_e('Horology Manual', 'kinboni-core'); ?></span>
                          <h4 class="font-serif text-lg font-medium text-stone-900 dark:text-white"><?php esc_html_e('Automatic Calibre Care & Winding Guide', 'kinboni-core'); ?></h4>
                          <p class="text-sm text-stone-500 mt-0.5"><?php esc_html_e('PDF Edition • Certified Master Guild', 'kinboni-core'); ?></p>
                        </div>
                        <button type="button" onclick="if(window.KinboniCore){KinboniCore.showToast('Horology Care Guide PDF dispatched to your device.', 'success');}" class="px-4 py-2 rounded-xl bg-stone-900 text-white dark:bg-white dark:text-stone-900 text-xs font-bold uppercase tracking-wider flex-shrink-0 hover:bg-gold-500 hover:text-stone-950 transition-colors cursor-pointer">
                          <?php esc_html_e('Download', 'kinboni-core'); ?>
                        </button>
                      </div>
                      <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 flex items-center justify-between gap-4">
                        <div>
                          <span class="text-xs font-bold uppercase tracking-widest text-gold-700 dark:text-gold-400"><?php esc_html_e('Tuscan Leather', 'kinboni-core'); ?></span>
                          <h4 class="font-serif text-lg font-medium text-stone-900 dark:text-white"><?php esc_html_e('Vegetable-Tanned Bridle Leather Conditioning', 'kinboni-core'); ?></h4>
                          <p class="text-sm text-stone-500 mt-0.5"><?php esc_html_e('PDF Edition • Lifetime Patina Guide', 'kinboni-core'); ?></p>
                        </div>
                        <button type="button" onclick="if(window.KinboniCore){KinboniCore.showToast('Leather Conditioning Guide PDF downloaded.', 'success');}" class="px-4 py-2 rounded-xl bg-stone-900 text-white dark:bg-white dark:text-stone-900 text-xs font-bold uppercase tracking-wider flex-shrink-0 hover:bg-gold-500 hover:text-stone-950 transition-colors cursor-pointer">
                          <?php esc_html_e('Download', 'kinboni-core'); ?>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div id="wc-acc-panel-addresses" class="wc-acc-panel hidden space-y-6">
                    <h2 class="font-serif text-2xl font-medium text-stone-900 dark:text-white border-b border-stone-200 dark:border-stone-800 pb-4">
                      <?php esc_html_e('Saved Dispatch Addresses', 'kinboni-core'); ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                          <h4 class="font-bold uppercase tracking-wider text-stone-900 dark:text-white"><?php esc_html_e('Primary Residence (Billing & Shipping)', 'kinboni-core'); ?></h4>
                          <span class="px-2 py-0.5 rounded bg-gold-400/20 text-gold-700 dark:text-gold-300 font-bold text-xs"><?php esc_html_e('Default', 'kinboni-core'); ?></span>
                        </div>
                        <p id="wc-acc-address-display" class="text-stone-600 dark:text-stone-400 leading-relaxed">
                          <?php echo esc_html($display_name); ?><br/>
                          <?php echo esc_html($user_address); ?><br/>
                          Dhaka 1212, Bangladesh<br/>
                          Phone: <?php echo esc_html($user_phone); ?>
                        </p>
                      </div>
                    </div>
                  </div>

                  <div id="wc-acc-panel-details" class="wc-acc-panel hidden space-y-6">
                    <h2 class="font-serif text-2xl font-medium text-stone-900 dark:text-white border-b border-stone-200 dark:border-stone-800 pb-4">
                      <?php esc_html_e('Edit Patron Profile', 'kinboni-core'); ?>
                    </h2>
                    <form id="wc-acc-details-form" class="space-y-4 max-w-xl" novalidate>
                      <div>
                        <label for="wc-acc-input-name" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Display Name *', 'kinboni-core'); ?></label>
                        <input type="text" id="wc-acc-input-name" value="<?php echo esc_attr($display_name); ?>" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="wc-acc-input-email" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Email Address *', 'kinboni-core'); ?></label>
                        <input type="email" id="wc-acc-input-email" value="<?php echo esc_attr($user_email); ?>" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="wc-acc-input-phone" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Mobile Phone *', 'kinboni-core'); ?></label>
                        <input type="tel" id="wc-acc-input-phone" value="<?php echo esc_attr($user_phone); ?>" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="wc-acc-input-address" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Primary Delivery Address *', 'kinboni-core'); ?></label>
                        <input type="text" id="wc-acc-input-address" value="<?php echo esc_attr($user_address); ?>" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="wc-acc-input-current-password" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Current Password (required to change email or password)', 'kinboni-core'); ?></label>
                        <input type="password" id="wc-acc-input-current-password" autocomplete="current-password" placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="wc-acc-input-password" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('New Password (leave blank to leave unchanged)', 'kinboni-core'); ?></label>
                        <input type="password" id="wc-acc-input-password" placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <button type="submit" class="px-6 py-3 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-md transition-all cursor-pointer">
                        <?php esc_html_e('Save Profile Changes', 'kinboni-core'); ?>
                      </button>
                    </form>
                  </div>

                </div>
              </div>

              <div id="wc-account-auth-wrap" class="hidden grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
                <div class="bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-8 shadow-sm">
                  <h2 class="font-serif text-2xl font-medium text-stone-900 dark:text-white mb-2"><?php esc_html_e('Patron Sign In', 'kinboni-core'); ?></h2>
                  <p class="text-sm text-stone-500 mb-6"><?php esc_html_e('Access your private archive orders and saved wishlist.', 'kinboni-core'); ?></p>
                  <form id="wc-login-form" class="space-y-4" novalidate>
                    <div>
                      <label for="wc-login-email" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Email or Mobile Number *', 'kinboni-core'); ?></label>
                      <input type="text" id="wc-login-email" required placeholder="patron@kinboni.com" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                    </div>
                    <div>
                      <label for="wc-login-pass" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Password *', 'kinboni-core'); ?></label>
                      <input type="password" id="wc-login-pass" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-gold-500 hover:text-stone-950 transition-all cursor-pointer">
                      <?php esc_html_e('Sign In to Atelier', 'kinboni-core'); ?>
                    </button>
                  </form>
                </div>

                <div class="bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-8 shadow-sm">
                  <h2 class="font-serif text-2xl font-medium text-stone-900 dark:text-white mb-2"><?php esc_html_e('Register Patron Membership', 'kinboni-core'); ?></h2>
                  <p class="text-sm text-stone-500 mb-6"><?php esc_html_e('Receive complimentary white-glove priority dispatch and 20% privilege voucher.', 'kinboni-core'); ?></p>
                  <form id="wc-register-form" class="space-y-4" novalidate>
                    <div>
                      <label for="wc-reg-name" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Full Name *', 'kinboni-core'); ?></label>
                      <input type="text" id="wc-reg-name" required placeholder="<?php esc_attr_e('Your full name', 'kinboni-core'); ?>" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                    </div>
                    <div>
                      <label for="wc-reg-email" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Email Address *', 'kinboni-core'); ?></label>
                      <input type="email" id="wc-reg-email" required placeholder="your@email.com" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                    </div>
                    <div>
                      <label for="wc-reg-phone" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Mobile Number (11 Digits) *', 'kinboni-core'); ?></label>
                      <input type="tel" id="wc-reg-phone" required placeholder="017XXXXXXXX" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                    </div>
                    <div>
                      <label for="wc-reg-password" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Password *', 'kinboni-core'); ?></label>
                      <input type="password" id="wc-reg-password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs sm:text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-md transition-all cursor-pointer">
                      <?php esc_html_e('Create Patron Account', 'kinboni-core'); ?>
                    </button>
                  </form>
                </div>
              </div>

            </div>

            <script type="application/json" id="kinboni-account-initial-data">
              <?php
              $account_payload = array(
                  'is_logged_in' => $is_logged_in,
                  'profile'      => array(
                      'name'    => $display_name,
                      'email'   => $user_email,
                      'phone'   => $user_phone,
                      'address' => $user_address,
                  ),
                  'orders'       => $orders_data,
              );
              echo wp_json_encode($account_payload);
              ?>
            </script>
        </main>
        <?php
    }
}

