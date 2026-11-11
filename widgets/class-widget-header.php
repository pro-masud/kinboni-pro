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
use Elementor\Repeater;
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
        // CONTENT TAB: SEARCH POPUP & MODAL
        // ==========================================
        $this->start_controls_section(
            'section_search_modal_controls',
            array(
                'label'     => esc_html__('Search Popup & Modal', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_CONTENT,
                'condition' => array('show_search' => 'yes'),
            )
        );

        $this->add_control(
            'search_action_type',
            array(
                'label'   => esc_html__('Click Action', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'modal',
                'options' => array(
                    'modal' => esc_html__('Open Live Ajax Popup Modal', 'kinboni-core'),
                    'url'   => esc_html__('Redirect to Page URL', 'kinboni-core'),
                ),
            )
        );

        $this->add_control(
            'search_custom_url',
            array(
                'label'       => esc_html__('Search Target URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com/shop', 'kinboni-core'),
                'condition'   => array('search_action_type' => 'url'),
            )
        );

        $this->add_control(
            'search_modal_placeholder',
            array(
                'label'       => esc_html__('Modal Input Placeholder', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Search by timepiece, leather, fragrance, gold, silk...', 'kinboni-core'),
                'condition'   => array('search_action_type' => 'modal'),
            )
        );

        $this->add_control(
            'search_results_heading',
            array(
                'label'       => esc_html__('Results Header Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Live Atelier Search', 'kinboni-core'),
                'condition'   => array('search_action_type' => 'modal'),
            )
        );

        $this->add_control(
            'search_show_suggestions',
            array(
                'label'        => esc_html__('Show Quick Suggestions', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('search_action_type' => 'modal'),
            )
        );

        $this->add_control(
            'search_suggestions_label',
            array(
                'label'       => esc_html__('Suggestions Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Suggestions:', 'kinboni-core'),
                'condition'   => array(
                    'search_action_type'      => 'modal',
                    'search_show_suggestions' => 'yes',
                ),
            )
        );

        $search_suggestions_repeater = new Repeater();

        $search_suggestions_repeater->add_control(
            'tag_label',
            array(
                'label'       => esc_html__('Display Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Timepieces', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $search_suggestions_repeater->add_control(
            'tag_query',
            array(
                'label'       => esc_html__('Search Query', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Timepiece', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $this->add_control(
            'search_suggestions_list',
            array(
                'label'       => esc_html__('Suggestion Pills', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $search_suggestions_repeater->get_controls(),
                'title_field' => '{{{ tag_label }}}',
                'default'     => array(
                    array('tag_label' => 'Timepieces', 'tag_query' => 'Timepiece'),
                    array('tag_label' => 'Leather Goods', 'tag_query' => 'Leather'),
                    array('tag_label' => 'Oud & Fragrance', 'tag_query' => 'Oud'),
                    array('tag_label' => 'Silk & Cashmere', 'tag_query' => 'Silk'),
                ),
                'condition'   => array(
                    'search_action_type'      => 'modal',
                    'search_show_suggestions' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: SHOPPING BAG DRAWER
        // ==========================================
        $this->start_controls_section(
            'section_cart_drawer_controls',
            array(
                'label'     => esc_html__('Shopping Bag Drawer', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_CONTENT,
                'condition' => array('show_cart' => 'yes'),
            )
        );

        $this->add_control(
            'cart_action_type',
            array(
                'label'   => esc_html__('Click Action', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'drawer',
                'options' => array(
                    'drawer' => esc_html__('Open Slide-out Bag Drawer', 'kinboni-core'),
                    'url'    => esc_html__('Redirect to Cart Page', 'kinboni-core'),
                ),
            )
        );

        $this->add_control(
            'cart_drawer_title',
            array(
                'label'       => esc_html__('Drawer Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Shopping Bag', 'kinboni-core'),
                'condition'   => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_show_shipping',
            array(
                'label'        => esc_html__('Show Free Shipping Status', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_free_shipping_threshold',
            array(
                'label'       => esc_html__('Free Shipping Threshold (৳)', 'kinboni-core'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 2500,
                'condition'   => array(
                    'cart_action_type'   => 'drawer',
                    'cart_show_shipping' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_shipping_label',
            array(
                'label'       => esc_html__('Shipping Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Express White-Glove Shipping', 'kinboni-core'),
                'condition'   => array(
                    'cart_action_type'   => 'drawer',
                    'cart_show_shipping' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_shipping_free_text',
            array(
                'label'       => esc_html__('Complimentary Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('COMPLIMENTARY', 'kinboni-core'),
                'condition'   => array(
                    'cart_action_type'   => 'drawer',
                    'cart_show_shipping' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_shipping_calc_text',
            array(
                'label'       => esc_html__('Under Threshold Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Calculated at Checkout', 'kinboni-core'),
                'condition'   => array(
                    'cart_action_type'   => 'drawer',
                    'cart_show_shipping' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_empty_title',
            array(
                'label'       => esc_html__('Empty Bag Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Your Bag is Empty', 'kinboni-core'),
                'separator'   => 'before',
                'condition'   => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_empty_desc',
            array(
                'label'       => esc_html__('Empty Bag Description', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Explore our curated horology and artisanal leather collections to begin your order.', 'kinboni-core'),
                'condition'   => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_empty_btn_text',
            array(
                'label'       => esc_html__('Empty CTA Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Explore Creations', 'kinboni-core'),
                'condition'   => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_empty_btn_url',
            array(
                'label'       => esc_html__('Empty CTA Button URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com/shop', 'kinboni-core'),
                'condition'   => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_show_quick_checkout',
            array(
                'label'        => esc_html__('Show Quick Checkout Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
                'condition'    => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_quick_checkout_text',
            array(
                'label'       => esc_html__('Quick Checkout Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Quick COD Checkout Modal', 'kinboni-core'),
                'condition'   => array(
                    'cart_action_type'         => 'drawer',
                    'cart_show_quick_checkout' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_show_cart_btn',
            array(
                'label'        => esc_html__('Show View Cart Page Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_view_cart_text',
            array(
                'label'       => esc_html__('View Cart Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('View Cart Page', 'kinboni-core'),
                'condition'   => array(
                    'cart_action_type'   => 'drawer',
                    'cart_show_cart_btn' => 'yes',
                ),
            )
        );

        $this->add_control(
            'cart_show_checkout_btn',
            array(
                'label'        => esc_html__('Show Checkout Page Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('cart_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'cart_checkout_page_text',
            array(
                'label'       => esc_html__('Checkout Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Checkout Page', 'kinboni-core'),
                'condition'   => array(
                    'cart_action_type'       => 'drawer',
                    'cart_show_checkout_btn' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: SAVED WISHLIST DRAWER
        // ==========================================
        $this->start_controls_section(
            'section_wishlist_drawer_controls',
            array(
                'label'     => esc_html__('Saved Wishlist Drawer', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_CONTENT,
                'condition' => array('show_wishlist' => 'yes'),
            )
        );

        $this->add_control(
            'wishlist_action_type',
            array(
                'label'   => esc_html__('Click Action', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'drawer',
                'options' => array(
                    'drawer' => esc_html__('Open Slide-out Wishlist Drawer', 'kinboni-core'),
                    'url'    => esc_html__('Redirect to Wishlist Page', 'kinboni-core'),
                ),
            )
        );

        $this->add_control(
            'wishlist_drawer_title',
            array(
                'label'       => esc_html__('Drawer Header Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Your Saved Wishlist', 'kinboni-core'),
                'condition'   => array('wishlist_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'wishlist_empty_title',
            array(
                'label'       => esc_html__('Empty Wishlist Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('No Saved Creations', 'kinboni-core'),
                'separator'   => 'before',
                'condition'   => array('wishlist_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'wishlist_empty_desc',
            array(
                'label'       => esc_html__('Empty Description', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Click the heart icon on any creation to bookmark it for later consideration.', 'kinboni-core'),
                'condition'   => array('wishlist_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'wishlist_empty_btn_text',
            array(
                'label'       => esc_html__('Empty CTA Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Browse Catalogue', 'kinboni-core'),
                'condition'   => array('wishlist_action_type' => 'drawer'),
            )
        );

        $this->add_control(
            'wishlist_empty_btn_url',
            array(
                'label'       => esc_html__('Empty CTA Button URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com/shop', 'kinboni-core'),
                'condition'   => array('wishlist_action_type' => 'drawer'),
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

        // ==========================================
        // STYLE TAB: POPUPS & DRAWERS STYLING
        // ==========================================
        $this->start_controls_section(
            'section_style_drawers',
            array(
                'label' => esc_html__('Popups & Drawers Styling', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'drawer_bg_color',
            array(
                'label'     => esc_html__('Drawer / Modal Card Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #cart-drawer, {{WRAPPER}} #wishlist-drawer, {{WRAPPER}} #search-modal > div:last-child' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'drawer_title_color',
            array(
                'label'     => esc_html__('Drawer Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #cart-drawer h3, {{WRAPPER}} #wishlist-drawer h3' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'drawer_accent_color',
            array(
                'label'     => esc_html__('Primary Button Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #cart-checkout-btn, {{WRAPPER}} #cart-empty-state a, {{WRAPPER}} .search-suggestion-tag:hover' => 'background-color: {{VALUE}} !important;',
                ),
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
        $show_dropdown   = !isset($settings['show_pages_dropdown']) || ('yes' === $settings['show_pages_dropdown']);
        $pages_badge     = !empty($settings['pages_badge_count']) ? $settings['pages_badge_count'] : '10';
        $deals_badge     = !empty($settings['flash_deals_badge']) ? $settings['flash_deals_badge'] : '-20%';

        $show_search     = !isset($settings['show_search']) || ('yes' === $settings['show_search']);
        $search_hint     = !empty($settings['search_placeholder']) ? $settings['search_placeholder'] : __('Search creations...', 'kinboni-core');
        $search_action   = !empty($settings['search_action_type']) ? $settings['search_action_type'] : 'modal';
        $search_page_url = !empty($settings['search_custom_url']['url']) ? $settings['search_custom_url']['url'] : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop'));

        $show_theme      = ('yes' === $settings['show_theme_toggle']);
        $show_account    = ('yes' === $settings['show_account']);
        $show_wishlist   = ('yes' === $settings['show_wishlist']);
        $wishlist_action = !empty($settings['wishlist_action_type']) ? $settings['wishlist_action_type'] : 'drawer';

        $show_cart       = ('yes' === $settings['show_cart']);
        $cart_action     = !empty($settings['cart_action_type']) ? $settings['cart_action_type'] : 'drawer';
        $cart_label      = !empty($settings['cart_button_label']) ? $settings['cart_button_label'] : __('Bag', 'kinboni-core');
        $show_mobile_btn = !isset($settings['show_mobile_menu']) || ('yes' === $settings['show_mobile_menu']);

        // Dynamic WooCommerce page links
        $shop_url     = (function_exists('wc_get_page_permalink') && wc_get_page_permalink('shop')) ? wc_get_page_permalink('shop') : home_url('/shop/');
        $cart_url     = (function_exists('wc_get_cart_url') && wc_get_cart_url() && wc_get_cart_url() !== home_url('/')) ? wc_get_cart_url() : home_url('/cart/');
        $checkout_url = (function_exists('wc_get_checkout_url') && wc_get_checkout_url() && wc_get_checkout_url() !== home_url('/')) ? wc_get_checkout_url() : home_url('/checkout/');
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
            <div class="kinboni-header-inner flex items-center justify-between h-20 gap-4 sm:gap-6">
              
              <!-- Mobile Menu Hamburger Button -->
              <?php if ($show_mobile_btn) : ?>
              <div class="kinboni-mobile-menu-trigger flex items-center lg:hidden">
                <button
                  id="mobile-menu-btn"
                  type="button"
                  class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                  aria-label="<?php esc_attr_e('Open mobile navigation menu', 'kinboni-core'); ?>"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
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
                class="kinboni-header-navigation hidden lg:flex items-center gap-3.5 2xl:gap-6 whitespace-nowrap"
                aria-label="<?php esc_attr_e('Main navigation', 'kinboni-core'); ?>"
              >
                <?php
                $use_wp_menu = ('wp_menu' === $nav_mode);
                $menu_to_render = !empty($settings['selected_wp_menu']) && '0' !== $settings['selected_wp_menu'] ? $settings['selected_wp_menu'] : '';
                if ($use_wp_menu && empty($menu_to_render)) {
                    $locations = get_nav_menu_locations();
                    if (!empty($locations['primary'])) {
                        $menu_to_render = $locations['primary'];
                    } else {
                        $all_menus = wp_get_nav_menus();
                        if (!empty($all_menus)) {
                            $menu_to_render = $all_menus[0]->term_id;
                        }
                    }
                }
                ?>
                <?php if ($use_wp_menu && !empty($menu_to_render)) : ?>
                  <?php
                  $menu_args = array(
                      'menu'        => $menu_to_render,
                      'container'   => false,
                      'menu_class'  => 'flex items-center gap-4 xl:gap-6',
                      'fallback_cb' => false,
                  );
                  if (class_exists('Kinboni_Nav_Walker')) {
                      $menu_args['walker'] = new Kinboni_Nav_Walker();
                  }
                  wp_nav_menu($menu_args);
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
              <div class="kinboni-header-actions flex items-center gap-1 sm:gap-2">
                
                <!-- Search Button -->
                <?php if ($show_search) : ?>
                  <?php if ('modal' === $search_action) : ?>
                  <button
                    type="button"
                    class="kinboni-search-desktop open-search-btn hidden md:flex items-center gap-2 pl-3.5 pr-4 py-2 whitespace-nowrap rounded-full border border-stone-300 dark:border-stone-700 text-xs text-stone-600 dark:text-stone-300 hover:border-gold-500 hover:text-gold-600 dark:hover:text-gold-400 transition-colors cursor-pointer"
                    aria-label="<?php esc_attr_e('Search creations', 'kinboni-core'); ?>"
                  >
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path></svg>
                    <span class="font-medium"><?php echo esc_html($search_hint); ?></span>
                  </button>
                  <button
                    type="button"
                    class="kinboni-search-mobile open-search-btn md:hidden w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                    aria-label="<?php esc_attr_e('Search creations', 'kinboni-core'); ?>"
                  >
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path></svg>
                  </button>
                  <?php else : ?>
                  <a
                    href="<?php echo esc_url($search_page_url); ?>"
                    class="kinboni-search-desktop hidden md:flex items-center gap-2 pl-3.5 pr-4 py-2 whitespace-nowrap rounded-full border border-stone-300 dark:border-stone-700 text-xs text-stone-600 dark:text-stone-300 hover:border-gold-500 hover:text-gold-600 dark:hover:text-gold-400 transition-colors cursor-pointer"
                    aria-label="<?php esc_attr_e('Search creations', 'kinboni-core'); ?>"
                  >
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path></svg>
                    <span class="font-medium"><?php echo esc_html($search_hint); ?></span>
                  </a>
                  <a
                    href="<?php echo esc_url($search_page_url); ?>"
                    class="kinboni-search-mobile md:hidden w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                    aria-label="<?php esc_attr_e('Search creations', 'kinboni-core'); ?>"
                  >
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path></svg>
                  </a>
                  <?php endif; ?>
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
                  <?php if ('drawer' === $wishlist_action) : ?>
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
                  <?php else : ?>
                  <a
                    href="<?php echo esc_url($wishlist_url); ?>"
                    class="relative w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer"
                    aria-label="<?php esc_attr_e('View Saved Wishlist', 'kinboni-core'); ?>"
                  >
                    <i data-lucide="heart" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    <span
                      class="wishlist-count-badge absolute top-0.5 right-0.5 transform scale-0 transition-transform duration-300 flex items-center justify-center min-w-[16px] h-[16px] sm:min-w-[18px] sm:h-[18px] px-1 text-[11px] sm:text-xs font-bold text-white bg-rose-600 rounded-full shadow-sm"
                    >0</span>
                  </a>
                  <?php endif; ?>
                <?php endif; ?>

                <!-- Shopping Bag Button with Live Item Counter -->
                <?php if ($show_cart) : ?>
                  <?php if ('drawer' === $cart_action) : ?>
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
                  <?php else : ?>
                  <a
                    href="<?php echo esc_url($cart_url); ?>"
                    class="relative w-8 h-8 sm:w-auto p-0 sm:pl-3.5 sm:pr-4 sm:py-2 rounded-full bg-stone-900 dark:bg-gold-400 text-white dark:text-stone-950 hover:bg-gold-600 dark:hover:bg-gold-300 transition-all shadow-sm active:scale-95 flex items-center justify-center sm:gap-2 flex-shrink-0 cursor-pointer"
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
                  </a>
                  <?php endif; ?>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </header>

        <?php
        // Mark as rendered so wp_footer does not output duplicate templates
        $GLOBALS['kinboni_header_drawers_rendered'] = true;

        // Render Live Search Modal
        if ($show_search && 'modal' === $search_action) {
            $this->render_search_modal($settings);
        }

        // Render Shopping Bag Drawer
        if ($show_cart && 'drawer' === $cart_action) {
            $this->render_cart_drawer($settings, $cart_count, $cart_url, $checkout_url);
        }

        // Render Saved Wishlist Drawer
        if ($show_wishlist && 'drawer' === $wishlist_action) {
            $this->render_wishlist_drawer($settings);
        }
    }

    /**
     * Render Live Ajax Search Modal
     */
    protected function render_search_modal($settings) {
        $search_placeholder = !empty($settings['search_modal_placeholder']) ? $settings['search_modal_placeholder'] : esc_html__('Search by timepiece, leather, fragrance, gold, silk...', 'kinboni-core');
        $results_heading    = !empty($settings['search_results_heading']) ? $settings['search_results_heading'] : esc_html__('Live Atelier Search', 'kinboni-core');
        $show_suggestions   = !isset($settings['search_show_suggestions']) || ('yes' === $settings['search_show_suggestions']);
        $suggestions_label  = !empty($settings['search_suggestions_label']) ? $settings['search_suggestions_label'] : esc_html__('Suggestions:', 'kinboni-core');
        $suggestions        = !empty($settings['search_suggestions_list']) ? $settings['search_suggestions_list'] : array(
            array('tag_label' => 'Timepieces', 'tag_query' => 'Timepiece'),
            array('tag_label' => 'Leather Goods', 'tag_query' => 'Leather'),
            array('tag_label' => 'Oud & Fragrance', 'tag_query' => 'Oud'),
            array('tag_label' => 'Silk & Cashmere', 'tag_query' => 'Silk'),
        );
        ?>
        <!-- Kinboni Search Modal Overlay -->
        <div id="search-modal" class="fixed inset-0 z-50 hidden flex flex-col justify-start p-4 sm:p-6 md:p-12" style="display: none;">
          <div id="search-backdrop" class="fixed inset-0 bg-stone-950/80 backdrop-blur-md cursor-pointer"></div>
          
          <div class="relative w-full max-w-3xl mx-auto bg-white dark:bg-stone-900 rounded-2xl shadow-2xl border border-stone-200 dark:border-stone-800 overflow-hidden z-10 animate-slide-down">
            
            <!-- Search Input Header -->
            <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center gap-3 m-0">
              <input type="hidden" name="post_type" value="product" />
              <svg class="w-5 h-5 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path></svg>
              <input 
                type="text" 
                name="s"
                id="search-input" 
                placeholder="<?php echo esc_attr($search_placeholder); ?>" 
                aria-label="<?php esc_attr_e('Search Kinboni Atelier creations', 'kinboni-core'); ?>"
                autocomplete="off"
                class="flex-grow bg-transparent text-stone-900 dark:text-stone-100 placeholder:text-stone-400 text-sm sm:text-base focus:outline-none"
              />
              <button type="button" id="close-search-btn" class="w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors cursor-pointer" aria-label="<?php esc_attr_e('Close search', 'kinboni-core'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </form>

            <!-- Quick Search Recommendations -->
            <?php if ($show_suggestions && !empty($suggestions)) : ?>
            <div class="px-5 py-3 bg-stone-50 dark:bg-stone-950 border-b border-stone-200 dark:border-stone-800 flex items-center gap-2 flex-wrap text-xs">
              <span class="text-stone-500 font-medium"><?php echo esc_html($suggestions_label); ?></span>
              <?php foreach ($suggestions as $tag) : ?>
                <?php
                $t_label = !empty($tag['tag_label']) ? $tag['tag_label'] : '';
                $t_query = !empty($tag['tag_query']) ? $tag['tag_query'] : $t_label;
                if (empty($t_label)) {
                    continue;
                }
                ?>
                <button type="button" class="search-suggestion-tag px-3 py-1 rounded-full bg-stone-200/80 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-gold-500 hover:text-stone-950 transition-colors font-medium cursor-pointer" data-query="<?php echo esc_attr($t_query); ?>"><?php echo esc_html($t_label); ?></button>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Search Results Area -->
            <div class="p-6 max-h-[60vh] overflow-y-auto">
              <div class="flex items-center justify-between mb-4">
                <span id="search-count" class="text-xs uppercase font-bold tracking-wider text-stone-500"><?php echo esc_html($results_heading); ?></span>
              </div>
              <div id="search-results-container">
                <!-- Dynamically filled by SearchController -->
              </div>
            </div>

          </div>
        </div>
        <?php
    }

    /**
     * Render Shopping Bag Slide-out Drawer
     */
    protected function render_cart_drawer($settings, $cart_count, $cart_url, $checkout_url) {
        $cart_subtotal_raw  = (function_exists('WC') && WC()->cart) ? (float) WC()->cart->get_subtotal() : 0;
        $cart_subtotal_html = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_subtotal() : '৳0';
        $cart_total_html    = (function_exists('WC') && WC()->cart) ? WC()->cart->get_total() : '৳0';

        $drawer_title       = !empty($settings['cart_drawer_title']) ? $settings['cart_drawer_title'] : esc_html__('Shopping Bag', 'kinboni-core');
        $show_shipping      = !isset($settings['cart_show_shipping']) || ('yes' === $settings['cart_show_shipping']);
        $shipping_label     = !empty($settings['cart_shipping_label']) ? $settings['cart_shipping_label'] : esc_html__('Express White-Glove Shipping', 'kinboni-core');
        $shipping_free_text = !empty($settings['cart_shipping_free_text']) ? $settings['cart_shipping_free_text'] : esc_html__('COMPLIMENTARY', 'kinboni-core');
        $shipping_calc_text = !empty($settings['cart_shipping_calc_text']) ? $settings['cart_shipping_calc_text'] : esc_html__('Calculated at Checkout', 'kinboni-core');
        $shipping_threshold = isset($settings['cart_free_shipping_threshold']) && '' !== $settings['cart_free_shipping_threshold'] 
            ? (float) $settings['cart_free_shipping_threshold'] 
            : (float) get_option('kinboni_free_shipping_threshold', 2500);

        $empty_title        = !empty($settings['cart_empty_title']) ? $settings['cart_empty_title'] : esc_html__('Your Bag is Empty', 'kinboni-core');
        $empty_desc         = !empty($settings['cart_empty_desc']) ? $settings['cart_empty_desc'] : esc_html__('Explore our curated horology and artisanal leather collections to begin your order.', 'kinboni-core');
        $empty_btn_text     = !empty($settings['cart_empty_btn_text']) ? $settings['cart_empty_btn_text'] : esc_html__('Explore Creations', 'kinboni-core');
        $empty_btn_url      = !empty($settings['cart_empty_btn_url']['url']) ? $settings['cart_empty_btn_url']['url'] : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop'));

        $show_quick_order   = !isset($settings['cart_show_quick_checkout']) || ('yes' === $settings['cart_show_quick_checkout']);
        $quick_order_text   = !empty($settings['cart_quick_checkout_text']) ? $settings['cart_quick_checkout_text'] : esc_html__('Quick COD Checkout Modal', 'kinboni-core');
        $show_cart_link     = !isset($settings['cart_show_cart_btn']) || ('yes' === $settings['cart_show_cart_btn']);
        $view_cart_text     = !empty($settings['cart_view_cart_text']) ? $settings['cart_view_cart_text'] : esc_html__('View Cart Page', 'kinboni-core');
        $show_checkout_link = !isset($settings['cart_show_checkout_btn']) || ('yes' === $settings['cart_show_checkout_btn']);
        $checkout_text      = !empty($settings['cart_checkout_page_text']) ? $settings['cart_checkout_page_text'] : esc_html__('Checkout Page', 'kinboni-core');
        ?>
        <!-- Kinboni Shopping Cart Slide-out Drawer -->
        <div id="cart-drawer-backdrop" class="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300"></div>
        <aside id="cart-drawer" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-stone-900 shadow-2xl transform translate-x-full transition-transform duration-300 ease-out flex flex-col border-l border-stone-200 dark:border-stone-800">
          
          <!-- Drawer Header -->
          <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i data-lucide="shopping-bag" class="w-5 h-5 text-gold-700 dark:text-gold-400"></i>
              <h3 class="font-serif text-lg font-medium text-stone-900 dark:text-white"><?php echo esc_html($drawer_title); ?></h3>
            </div>
            <button type="button" id="close-cart-btn" class="w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors cursor-pointer" aria-label="<?php esc_attr_e('Close cart', 'kinboni-core'); ?>">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
          </div>

          <!-- Scrollable Items Container -->
          <div id="cart-items-container" class="flex-grow overflow-y-auto p-5 space-y-4">
            <?php
            if (class_exists('Kinboni_Ajax_Cart')) {
                Kinboni_Ajax_Cart::instance()->render_drawer_items();
            }
            ?>
          </div>

          <!-- Empty State -->
          <div id="cart-empty-state" class="<?php echo ($cart_count > 0) ? 'hidden ' : ''; ?>flex-grow flex flex-col items-center justify-center p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 mb-4">
              <i data-lucide="shopping-bag" class="w-8 h-8"></i>
            </div>
            <h4 class="font-serif text-lg font-medium text-stone-900 dark:text-white mb-2"><?php echo esc_html($empty_title); ?></h4>
            <p class="text-sm text-stone-600 dark:text-stone-300 max-w-xs mb-6 font-normal">
              <?php echo esc_html($empty_desc); ?>
            </p>
            <a href="<?php echo esc_url($empty_btn_url); ?>" class="px-6 py-3 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest transition-all">
              <?php echo esc_html($empty_btn_text); ?>
            </a>
          </div>

          <!-- Drawer Footer (Subtotal & Checkout) -->
          <div id="cart-footer" class="<?php echo ($cart_count === 0) ? 'hidden ' : ''; ?>p-5 border-t border-stone-200 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-950/50 space-y-4">

            <!-- Price Breakdown -->
            <div class="space-y-1.5 text-xs">
              <div class="flex justify-between text-stone-600 dark:text-stone-400">
                <span><?php esc_html_e('Subtotal', 'kinboni-core'); ?></span>
                <span id="cart-subtotal" class="font-semibold text-stone-900 dark:text-white"><?php echo $cart_subtotal_html; ?></span>
              </div>
              <div id="cart-discount-row" class="hidden flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                <span><?php esc_html_e('Privilege Discount', 'kinboni-core'); ?></span>
                <span id="cart-discount-amount">-৳0</span>
              </div>
              <?php if ($show_shipping) : ?>
              <div class="flex justify-between text-stone-600 dark:text-stone-400">
                <span><?php echo esc_html($shipping_label); ?></span>
                <span id="cart-shipping-status" class="font-semibold <?php echo ($shipping_threshold <= 0 || $cart_subtotal_raw >= $shipping_threshold) ? 'text-emerald-600 dark:text-emerald-400' : 'text-stone-700 dark:text-stone-300'; ?>">
                  <?php echo ($shipping_threshold <= 0 || $cart_subtotal_raw >= $shipping_threshold) ? esc_html($shipping_free_text) : esc_html($shipping_calc_text); ?>
                </span>
              </div>
              <?php endif; ?>
              <div class="flex justify-between text-base font-bold text-stone-900 dark:text-white pt-2 border-t border-stone-200 dark:border-stone-800">
                <span><?php esc_html_e('Estimated Total', 'kinboni-core'); ?></span>
                <span id="cart-total" class="font-serif font-bold text-lg text-gold-700 dark:text-gold-400"><?php echo $cart_total_html; ?></span>
              </div>
            </div>

            <!-- Checkout CTA & Full Page Links -->
            <div class="space-y-2">
              <?php if ($show_quick_order) : ?>
              <button 
                id="cart-checkout-btn" 
                type="button" 
                class="w-full py-3.5 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-[0.15em] rounded-xl shadow-lg hover:shadow-gold-500/25 transition-all text-center flex items-center justify-center gap-2 active:scale-95 cursor-pointer"
              >
                <i data-lucide="zap" class="w-4 h-4 fill-stone-950"></i>
                <span><?php echo esc_html($quick_order_text); ?></span>
              </button>
              <?php endif; ?>
              <div class="grid grid-cols-2 gap-2">
                <?php if ($show_cart_link) : ?>
                <a 
                  href="<?php echo esc_url($cart_url); ?>"
                  class="w-full py-2.5 px-3 rounded-xl border border-stone-300 dark:border-stone-700 hover:border-gold-500 text-stone-800 dark:text-stone-200 text-[11px] font-bold uppercase tracking-wider transition-all flex items-center justify-center gap-1.5"
                >
                  <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                  <span><?php echo esc_html($view_cart_text); ?></span>
                </a>
                <?php endif; ?>
                <?php if ($show_checkout_link) : ?>
                <a 
                  href="<?php echo esc_url($checkout_url); ?>"
                  class="w-full py-2.5 px-3 rounded-xl bg-stone-900 text-white dark:bg-stone-100 dark:text-stone-950 hover:bg-gold-500 hover:text-stone-950 text-[11px] font-bold uppercase tracking-wider transition-all flex items-center justify-center gap-1.5"
                >
                  <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                  <span><?php echo esc_html($checkout_text); ?></span>
                </a>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </aside>
        <?php
    }

    /**
     * Render Saved Wishlist Slide-out Drawer
     */
    protected function render_wishlist_drawer($settings) {
        $drawer_title   = !empty($settings['wishlist_drawer_title']) ? $settings['wishlist_drawer_title'] : esc_html__('Your Saved Wishlist', 'kinboni-core');
        $empty_title    = !empty($settings['wishlist_empty_title']) ? $settings['wishlist_empty_title'] : esc_html__('No Saved Creations', 'kinboni-core');
        $empty_desc     = !empty($settings['wishlist_empty_desc']) ? $settings['wishlist_empty_desc'] : esc_html__('Click the heart icon on any creation to bookmark it for later consideration.', 'kinboni-core');
        $empty_btn_text = !empty($settings['wishlist_empty_btn_text']) ? $settings['wishlist_empty_btn_text'] : esc_html__('Browse Catalogue', 'kinboni-core');
        $empty_btn_url  = !empty($settings['wishlist_empty_btn_url']['url']) ? $settings['wishlist_empty_btn_url']['url'] : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop'));
        ?>
        <!-- Kinboni Wishlist Slide-out Drawer -->
        <div id="wishlist-drawer-backdrop" class="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300"></div>
        <aside id="wishlist-drawer" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-stone-900 shadow-2xl transform translate-x-full transition-transform duration-300 ease-out flex flex-col border-l border-stone-200 dark:border-stone-800">
          
          <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i data-lucide="heart" class="w-5 h-5 text-rose-500"></i>
              <h3 class="font-serif text-lg font-medium text-stone-900 dark:text-white"><?php echo esc_html($drawer_title); ?></h3>
            </div>
            <button type="button" id="close-wishlist-btn" class="w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors cursor-pointer" aria-label="<?php esc_attr_e('Close wishlist', 'kinboni-core'); ?>">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
          </div>

          <!-- Wishlist Items -->
          <div id="wishlist-items-container" class="flex-grow overflow-y-auto p-5 space-y-4">
            <!-- Injected by WishlistController.render() -->
          </div>

          <!-- Empty Wishlist State -->
          <div id="wishlist-empty-state" class="hidden flex-grow flex flex-col items-center justify-center p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 mb-4">
              <i data-lucide="heart" class="w-8 h-8"></i>
            </div>
            <h4 class="font-serif text-lg font-medium text-stone-900 dark:text-white mb-2"><?php echo esc_html($empty_title); ?></h4>
            <p class="text-sm text-stone-600 dark:text-stone-300 max-w-xs mb-6 font-normal">
              <?php echo esc_html($empty_desc); ?>
            </p>
            <a href="<?php echo esc_url($empty_btn_url); ?>" class="px-6 py-3 rounded-xl bg-stone-900 text-white dark:bg-white dark:text-stone-900 text-xs font-bold uppercase tracking-widest transition-all">
              <?php echo esc_html($empty_btn_text); ?>
            </a>
          </div>
        </aside>
        <?php
    }
}
