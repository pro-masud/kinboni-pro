<?php
/**
 * Kinboni Best Sellers Filterable Showcase - Elementor Widget
 *
 * Implements the luxury editorial "The Best Sellers" section featuring
 * ambient gold glows, architectural geometry, responsive category filter tabs,
 * and a 4-column dynamic grid displaying WooCommerce products with instant
 * client-side category filtering, Wishlist, Quick View, Ajax Add to Bag, and Fast COD Order triggers.
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

class Kinboni_Widget_Bestsellers extends Widget_Base {

    public function get_name() {
        return 'kinboni_bestsellers';
    }

    public function get_title() {
        return esc_html__('Best Sellers Filterable Showcase', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-filter';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('bestsellers', 'filter', 'tabs', 'products', 'woocommerce', 'atelier', 'kinboni', 'shop');
    }

    /**
     * Fetch all WooCommerce product categories
     */
    private function get_all_wc_categories() {
        $options = array();
        if (taxonomy_exists('product_cat')) {
            $terms = get_terms(array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
            ));
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $options[$term->slug] = sprintf('%s (%d)', $term->name, $term->count);
                }
            }
        }
        return $options;
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: SECTION HEADER
        // ==========================================
        $this->start_controls_section(
            'section_header_content',
            array(
                'label' => esc_html__('Section Header', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_header',
            array(
                'label'        => esc_html__('Show Header', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Show', 'kinboni-core'),
                'label_off'    => esc_html__('Hide', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'badge_text',
            array(
                'label'       => esc_html__('Subtitle / Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Patron Favorites', 'kinboni-core'),
                'placeholder' => esc_html__('Enter subtitle badge', 'kinboni-core'),
                'condition'   => array('show_header' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'badge_tag',
            array(
                'label'     => esc_html__('Subtitle HTML Tag', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'span' => 'span',
                    'p'    => 'p',
                    'div'  => 'div',
                    'h5'   => 'h5',
                    'h6'   => 'h6',
                ),
                'default'   => 'span',
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'title_text',
            array(
                'label'       => esc_html__('Main Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('The Best Sellers', 'kinboni-core'),
                'placeholder' => esc_html__('Enter main title', 'kinboni-core'),
                'condition'   => array('show_header' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'title_tag',
            array(
                'label'     => esc_html__('Title HTML Tag', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'h1'  => 'h1',
                    'h2'  => 'h2',
                    'h3'  => 'h3',
                    'h4'  => 'h4',
                    'div' => 'div',
                ),
                'default'   => 'h2',
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'show_divider',
            array(
                'label'        => esc_html__('Show Gold Accent Line', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'description_text',
            array(
                'label'       => esc_html__('Description Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Filter our most demanded atelier creations by category, recent arrivals, or archive privileges.', 'kinboni-core'),
                'placeholder' => esc_html__('Enter section description', 'kinboni-core'),
                'condition'   => array('show_header' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'desc_tag',
            array(
                'label'     => esc_html__('Description HTML Tag', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'p'    => 'p',
                    'div'  => 'div',
                    'span' => 'span',
                ),
                'default'   => 'p',
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: FILTER TABS
        // ==========================================
        $this->start_controls_section(
            'section_filter_tabs',
            array(
                'label' => esc_html__('Filter Tabs', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_filter_tabs',
            array(
                'label'        => esc_html__('Show Filter Tabs', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'tabs_source',
            array(
                'label'     => esc_html__('Filter Tabs Source', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'wc_categories' => esc_html__('Dynamic WooCommerce Categories (Recommended)', 'kinboni-core'),
                    'from_products' => esc_html__('Categories of Loaded Products (Auto-Sync)', 'kinboni-core'),
                    'preset'        => esc_html__('Dynamic WooCommerce (Fallback to Preset)', 'kinboni-core'),
                    'manual_static' => esc_html__('Static Prototype Preset (8 Fixed Tabs)', 'kinboni-core'),
                ),
                'default'   => 'wc_categories',
                'condition' => array('show_filter_tabs' => 'yes'),
            )
        );

        $this->add_control(
            'tabs_select_categories',
            array(
                'label'       => esc_html__('Specific Categories (Optional)', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $this->get_all_wc_categories(),
                'label_block' => true,
                'description' => esc_html__('Select specific categories to show as tabs. Leave empty to automatically display all categories.', 'kinboni-core'),
                'condition'   => array(
                    'show_filter_tabs' => 'yes',
                    'tabs_source'      => array('wc_categories', 'preset'),
                ),
            )
        );

        $this->add_control(
            'tabs_parent_only',
            array(
                'label'        => esc_html__('Top-Level Categories Only', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__('Hide subcategories from the tabs bar unless specifically chosen above.', 'kinboni-core'),
                'condition'    => array(
                    'show_filter_tabs' => 'yes',
                    'tabs_source'      => array('wc_categories', 'preset'),
                ),
            )
        );

        $this->add_control(
            'tabs_exclude_uncat',
            array(
                'label'        => esc_html__('Exclude "Uncategorized"', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_filter_tabs' => 'yes'),
            )
        );

        $this->add_control(
            'tabs_show_all',
            array(
                'label'        => esc_html__('Show "All Creations" Tab', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_filter_tabs' => 'yes'),
            )
        );

        $this->add_control(
            'tabs_all_label',
            array(
                'label'       => esc_html__('"All Creations" Tab Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('All Creations', 'kinboni-core'),
                'condition'   => array(
                    'show_filter_tabs' => 'yes',
                    'tabs_show_all'    => 'yes',
                ),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'tabs_show_sale',
            array(
                'label'        => esc_html__('Show "Private Sale" Tab', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_filter_tabs' => 'yes'),
            )
        );

        $this->add_control(
            'tabs_sale_label',
            array(
                'label'       => esc_html__('"Private Sale" Tab Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Private Sale', 'kinboni-core'),
                'condition'   => array(
                    'show_filter_tabs' => 'yes',
                    'tabs_show_sale'   => 'yes',
                ),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'default_filter',
            array(
                'label'       => esc_html__('Default Active Filter Slug', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'all',
                'placeholder' => 'all',
                'condition'   => array('show_filter_tabs' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: PRODUCTS QUERY
        // ==========================================
        $this->start_controls_section(
            'section_query_content',
            array(
                'label' => esc_html__('Products Query', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'source_mode',
            array(
                'label'   => esc_html__('Data Source', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'wc_query'      => esc_html__('WooCommerce Products (Dynamic)', 'kinboni-core'),
                    'manual_preset' => esc_html__('Atelier Prototype Preset (12 Best Sellers)', 'kinboni-core'),
                ),
                'default' => 'wc_query',
            )
        );

        $this->add_control(
            'query_orderby',
            array(
                'label'     => esc_html__('Order By', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'bestsellers' => esc_html__('Best Sellers (Sales Velocity / Popularity)', 'kinboni-core'),
                    'date'        => esc_html__('Latest Creations (Newest First)', 'kinboni-core'),
                    'featured'    => esc_html__('Featured Creations First', 'kinboni-core'),
                    'title'       => esc_html__('Product Title (A-Z)', 'kinboni-core'),
                    'price'       => esc_html__('Price', 'kinboni-core'),
                ),
                'default'   => 'bestsellers',
                'condition' => array('source_mode' => 'wc_query'),
            )
        );

        $this->add_control(
            'query_order',
            array(
                'label'     => esc_html__('Order Direction', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'DESC' => esc_html__('Descending (DESC)', 'kinboni-core'),
                    'ASC'  => esc_html__('Ascending (ASC)', 'kinboni-core'),
                ),
                'default'   => 'DESC',
                'condition' => array('source_mode' => 'wc_query'),
            )
        );

        $this->add_control(
            'products_limit',
            array(
                'label'     => esc_html__('Total Products Limit', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 4,
                'max'       => 48,
                'step'      => 1,
                'default'   => 24,
                'condition' => array('source_mode' => 'wc_query'),
            )
        );

        $this->add_control(
            'query_categories',
            array(
                'label'       => esc_html__('Filter Products by Specific Categories', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $this->get_all_wc_categories(),
                'label_block' => true,
                'description' => esc_html__('Leave empty to pull creations across all categories.', 'kinboni-core'),
                'condition'   => array('source_mode' => 'wc_query'),
            )
        );

        $this->add_control(
            'hide_out_of_stock',
            array(
                'label'        => esc_html__('Hide Out of Stock Products', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => array('source_mode' => 'wc_query'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CARD ACTIONS & BADGES
        // ==========================================
        $this->start_controls_section(
            'section_card_actions',
            array(
                'label' => esc_html__('Card Buttons & Badges', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'btn_cart_text',
            array(
                'label'       => esc_html__('Add to Bag Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Add to Bag', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'btn_cod_text',
            array(
                'label'       => esc_html__('Order Now (COD) Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Order Now', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'show_wishlist_btn',
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
            'show_quickview_btn',
            array(
                'label'        => esc_html__('Show Quick View Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_badges',
            array(
                'label'        => esc_html__('Show Badges & Discount %', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_ratings',
            array(
                'label'        => esc_html__('Show Star Ratings', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'card_title_tag',
            array(
                'label'   => esc_html__('Product Title Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h2'  => 'h2',
                    'h3'  => 'h3',
                    'h4'  => 'h4',
                    'h5'  => 'h5',
                    'div' => 'div',
                ),
                'default' => 'h3',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: ATMOSPHERE & DECORATIONS
        // ==========================================
        $this->start_controls_section(
            'section_decorations_content',
            array(
                'label' => esc_html__('Atmosphere & Glows', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'section_html_id',
            array(
                'label'       => esc_html__('Section HTML ID', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'bestsellers',
                'placeholder' => 'bestsellers',
            )
        );

        $this->add_control(
            'grid_html_id',
            array(
                'label'       => esc_html__('Grid Container ID', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'bestsellers-grid',
                'placeholder' => 'bestsellers-grid',
            )
        );

        $this->add_control(
            'show_ambient_glows',
            array(
                'label'        => esc_html__('Show Ambient Gold Glows', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_radial_dots',
            array(
                'label'        => esc_html__('Show Radial Dot Pattern', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_architectural_shapes',
            array(
                'label'        => esc_html__('Show Geometric Architectural Shapes', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: GENERAL & SECTION
        // ==========================================
        $this->start_controls_section(
            'section_style_general',
            array(
                'label' => esc_html__('Section & Background', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'section_padding',
            array(
                'label'      => esc_html__('Section Padding', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-bestsellers-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-bestsellers-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: HEADER
        // ==========================================
        $this->start_controls_section(
            'section_style_header',
            array(
                'label'     => esc_html__('Section Header', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'badge_color',
            array(
                'label'     => esc_html__('Subtitle / Badge Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-bs-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-bs-badge',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Main Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-bs-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-bs-title',
            )
        );

        $this->add_control(
            'divider_color',
            array(
                'label'     => esc_html__('Gold Line Divider Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-bs-divider' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-bs-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .kinboni-bs-desc',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: FILTER TABS
        // ==========================================
        $this->start_controls_section(
            'section_style_filter_tabs',
            array(
                'label'     => esc_html__('Filter Tabs', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_filter_tabs' => 'yes'),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'tabs_typography',
                'selector' => '{{WRAPPER}} .bestseller-filter-btn',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: PRODUCT CARDS
        // ==========================================
        $this->start_controls_section(
            'section_style_cards',
            array(
                'label' => esc_html__('Product Cards', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_title_color',
            array(
                'label'     => esc_html__('Product Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-product-card-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_title_hover_color',
            array(
                'label'     => esc_html__('Product Title Hover Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-bestseller-card:hover .kinboni-product-card-title' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'card_title_typography',
                'selector' => '{{WRAPPER}} .kinboni-product-card-title',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .kinboni-bestseller-card',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Preset filter tabs matching the prototype
     */
    private function get_preset_filter_tabs() {
        return array(
            array('slug' => 'all',        'label' => esc_html__('All Creations', 'kinboni-core')),
            array('slug' => 'timepieces', 'label' => esc_html__('Timepieces', 'kinboni-core')),
            array('slug' => 'leather',    'label' => esc_html__('Leather Goods', 'kinboni-core')),
            array('slug' => 'fragrance',  'label' => esc_html__('Fragrance', 'kinboni-core')),
            array('slug' => 'jewelry',    'label' => esc_html__('Fine Jewelry', 'kinboni-core')),
            array('slug' => 'eyewear',    'label' => esc_html__('Eyewear', 'kinboni-core')),
            array('slug' => 'silk',       'label' => esc_html__('Silk & Cashmere', 'kinboni-core')),
            array('slug' => 'sale',       'label' => esc_html__('Private Sale', 'kinboni-core')),
        );
    }

    /**
     * Preset data matching the prototype's 12 best seller products
     */
    private function get_prototype_preset_products() {
        return array(
            array(
                'id'            => 1,
                'name'          => 'Chronos Royale Automatic',
                'category'      => 'timepieces',
                'categoryLabel' => 'Timepieces',
                'price'         => 18500,
                'originalPrice' => 22000,
                'rating'        => 4.9,
                'reviewsCount'  => 68,
                'badge'         => 'BESTSELLER',
                'is_sale'       => true,
                'image'         => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 2,
                'name'          => 'Atelier Saddle Leather Briefcase',
                'category'      => 'leather',
                'categoryLabel' => 'Leather Goods',
                'price'         => 14200,
                'originalPrice' => 16800,
                'rating'        => 5.0,
                'reviewsCount'  => 42,
                'badge'         => 'NEW',
                'is_sale'       => true,
                'image'         => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 3,
                'name'          => 'Nocturne Oud Extrait De Parfum',
                'category'      => 'fragrance',
                'categoryLabel' => 'Fine Fragrance',
                'price'         => 8900,
                'originalPrice' => 0,
                'rating'        => 4.8,
                'reviewsCount'  => 94,
                'badge'         => 'SIGNATURE',
                'is_sale'       => false,
                'image'         => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 4,
                'name'          => 'Aethelgard 18K Minimalist Cuff',
                'category'      => 'jewelry',
                'categoryLabel' => 'Fine Jewelry',
                'price'         => 6400,
                'originalPrice' => 7500,
                'rating'        => 4.9,
                'reviewsCount'  => 37,
                'badge'         => 'LIMITED',
                'is_sale'       => true,
                'image'         => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 5,
                'name'          => 'Monaco Polarized Acetate Shades',
                'category'      => 'eyewear',
                'categoryLabel' => 'Eyewear',
                'price'         => 5200,
                'originalPrice' => 0,
                'rating'        => 4.7,
                'reviewsCount'  => 53,
                'badge'         => '',
                'is_sale'       => false,
                'image'         => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 6,
                'name'          => 'Kyoto Mulberry Silk Foulard',
                'category'      => 'silk',
                'categoryLabel' => 'Silk & Cashmere',
                'price'         => 4800,
                'originalPrice' => 5600,
                'rating'        => 4.9,
                'reviewsCount'  => 29,
                'badge'         => 'ARTISANAL',
                'is_sale'       => true,
                'image'         => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 7,
                'name'          => 'Heritage Bi-Fold Card Wallet',
                'category'      => 'leather',
                'categoryLabel' => 'Leather Goods',
                'price'         => 3200,
                'originalPrice' => 3900,
                'rating'        => 4.8,
                'reviewsCount'  => 88,
                'badge'         => 'BESTSELLER',
                'is_sale'       => true,
                'image'         => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 8,
                'name'          => 'Sylvan Vetiver Botanical Parfum',
                'category'      => 'fragrance',
                'categoryLabel' => 'Fine Fragrance',
                'price'         => 9500,
                'originalPrice' => 0,
                'rating'        => 4.9,
                'reviewsCount'  => 46,
                'badge'         => 'NEW',
                'is_sale'       => false,
                'image'         => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 9,
                'name'          => 'Vanguard Skeleton Dial Watch',
                'category'      => 'timepieces',
                'categoryLabel' => 'Timepieces',
                'price'         => 24500,
                'originalPrice' => 28000,
                'rating'        => 5.0,
                'reviewsCount'  => 23,
                'badge'         => 'EXCLUSIVE',
                'is_sale'       => true,
                'image'         => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 10,
                'name'          => 'Cypress Minimalist Leather Tote',
                'category'      => 'leather',
                'categoryLabel' => 'Leather Goods',
                'price'         => 11800,
                'originalPrice' => 0,
                'rating'        => 4.8,
                'reviewsCount'  => 31,
                'badge'         => 'ATELIER',
                'is_sale'       => false,
                'image'         => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 11,
                'name'          => 'Celeste Pearl Pendant Necklace',
                'category'      => 'jewelry',
                'categoryLabel' => 'Fine Jewelry',
                'price'         => 7800,
                'originalPrice' => 9200,
                'rating'        => 4.9,
                'reviewsCount'  => 19,
                'badge'         => 'RARE',
                'is_sale'       => true,
                'image'         => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 12,
                'name'          => 'Soleil Aviator Titanium Shades',
                'category'      => 'eyewear',
                'categoryLabel' => 'Eyewear',
                'price'         => 6800,
                'originalPrice' => 0,
                'rating'        => 4.7,
                'reviewsCount'  => 38,
                'badge'         => '',
                'is_sale'       => false,
                'image'         => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
        );
    }

    /**
     * Query WooCommerce products
     */
    private function get_query_products($settings) {
        if (!class_exists('WooCommerce')) {
            return array();
        }

        $limit   = !empty($settings['products_limit']) ? intval($settings['products_limit']) : 24;
        $orderby = !empty($settings['query_orderby']) ? sanitize_text_field($settings['query_orderby']) : 'bestsellers';
        $order   = !empty($settings['query_order']) ? sanitize_text_field($settings['query_order']) : 'DESC';

        $args = array(
            'status' => 'publish',
            'limit'  => $limit,
            'order'  => $order,
        );

        if (!empty($settings['hide_out_of_stock']) && 'yes' === $settings['hide_out_of_stock']) {
            $args['stock_status'] = 'instock';
        }

        if (!empty($settings['query_categories']) && is_array($settings['query_categories'])) {
            $cat_slugs = array_values(array_filter($settings['query_categories']));
            if (!empty($cat_slugs)) {
                $args['category'] = $cat_slugs;
            }
        }

        if ('bestsellers' === $orderby) {
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $products = wc_get_products($args);

            // If fewer products returned than limit (e.g. store has few sales recorded yet),
            // fill up with latest products from the store so all categories have products in the showcase
            if (count($products) < $limit) {
                $needed = $limit - count($products);
                $existing_ids = array_map(function($p) { return $p->get_id(); }, $products);

                $fill_args = array(
                    'status'  => 'publish',
                    'limit'   => $needed,
                    'orderby' => 'date',
                    'order'   => 'DESC',
                );
                if (!empty($existing_ids)) {
                    $fill_args['exclude'] = $existing_ids;
                }
                if (!empty($args['category'])) {
                    $fill_args['category'] = $args['category'];
                }
                if (!empty($settings['hide_out_of_stock']) && 'yes' === $settings['hide_out_of_stock']) {
                    $fill_args['stock_status'] = 'instock';
                }
                $fill_products = wc_get_products($fill_args);
                if (!empty($fill_products)) {
                    $products = array_merge($products, $fill_products);
                }
            }
        } elseif ('featured' === $orderby) {
            $args['featured'] = true;
            $products = wc_get_products($args);
            if (empty($products)) {
                unset($args['featured']);
                $products = wc_get_products($args);
            }
        } elseif ('date' === $orderby) {
            $args['orderby'] = 'date';
            $products = wc_get_products($args);
        } elseif ('price' === $orderby) {
            $args['orderby'] = 'price';
            $products = wc_get_products($args);
        } else {
            $args['orderby'] = 'title';
            $products = wc_get_products($args);
        }

        return $products;
    }

    /**
     * Render widget output on frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'bestsellers';
        $grid_id    = !empty($settings['grid_html_id']) ? sanitize_html_class($settings['grid_html_id']) : 'bestsellers-grid';

        $badge_tag = in_array($settings['badge_tag'], array('span', 'p', 'div', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4', 'div'), true) ? $settings['title_tag'] : 'h2';
        $desc_tag  = in_array($settings['desc_tag'], array('p', 'div', 'span'), true) ? $settings['desc_tag'] : 'p';
        $card_title_tag = in_array($settings['card_title_tag'], array('h2', 'h3', 'h4', 'h5', 'div'), true) ? $settings['card_title_tag'] : 'h3';

        $badge_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'bestsellers_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $title_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'bestsellers_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');

        $btn_cart_text = !empty($settings['btn_cart_text']) ? $settings['btn_cart_text'] : esc_html__('Add to Bag', 'kinboni-core');
        $btn_cod_text  = !empty($settings['btn_cod_text']) ? $settings['btn_cod_text'] : esc_html__('Order Now', 'kinboni-core');

        $show_wishlist  = ('yes' === $settings['show_wishlist_btn']);
        $show_quickview = ('yes' === $settings['show_quickview_btn']);
        $show_badges    = ('yes' === $settings['show_badges']);
        $show_ratings   = ('yes' === $settings['show_ratings']);

        $default_filter = !empty($settings['default_filter']) ? sanitize_title($settings['default_filter']) : 'all';

        $products_data = array();
        $loaded_categories_map = array(); // slug => name

        if ('wc_query' === $settings['source_mode']) {
            $wc_products = $this->get_query_products($settings);
            if (!empty($wc_products)) {
                foreach ($wc_products as $product) {
                    $pid          = $product->get_id();
                    $pname        = $product->get_name();
                    $plink        = $product->get_permalink();
                    $price        = (float) $product->get_price();
                    $reg_price    = (float) $product->get_regular_price();
                    $is_sale      = $product->is_on_sale();
                    $discount_pct = ($is_sale && $reg_price > $price) ? round((($reg_price - $price) / $reg_price) * 100) : 0;

                    $main_img  = class_exists('Kinboni_Core') ? Kinboni_Core::get_product_card_image($product, 'kinboni-product-card') : (wp_get_attachment_image_url($product->get_image_id(), 'kinboni-product-card') ?: wc_placeholder_img_src('kinboni-product-card'));
                    $hover_img = class_exists('Kinboni_Core') ? Kinboni_Core::get_product_hover_image($product, $main_img, 'kinboni-product-card') : $main_img;

                    $terms = get_the_terms($pid, 'product_cat');
                    $cat_slugs  = array();
                    $cat_labels = array();
                    if ($terms && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            if ($term->slug !== 'uncategorized') {
                                $cat_slugs[]  = $term->slug;
                                $cat_labels[] = $term->name;
                                $loaded_categories_map[$term->slug] = $term->name;
                            }
                        }
                    }
                    if (empty($cat_slugs)) {
                        $cat_slugs[]  = 'all';
                        $cat_labels[] = esc_html__('Atelier Collection', 'kinboni-core');
                    }
                    $primary_slug  = $cat_slugs[0];
                    $primary_label = $cat_labels[0];

                    $badge = '';
                    if ($product->is_featured()) {
                        $badge = 'BESTSELLER';
                    } elseif ($is_sale) {
                        $badge = 'SALE';
                    }

                    $products_data[] = array(
                        'id'              => $pid,
                        'name'            => $pname,
                        'category'        => implode(' ', $cat_slugs),
                        'category_slugs'  => $cat_slugs,
                        'categoryLabel'   => $primary_label,
                        'price'           => $price,
                        'originalPrice'   => ($is_sale && $reg_price > $price) ? $reg_price : 0,
                        'rating'          => (float) $product->get_average_rating(),
                        'reviewsCount'    => (int) $product->get_review_count(),
                        'badge'           => $badge,
                        'discount_pct'    => $discount_pct,
                        'is_sale'         => $is_sale,
                        'image'           => $main_img,
                        'hoverImage'      => $hover_img,
                        'link'            => $plink,
                        'add_to_cart_url' => $product->add_to_cart_url(),
                        'is_purchasable'  => $product->is_purchasable() && $product->is_in_stock(),
                    );
                }
            }
        }

        // If no products were returned from WooCommerce query, use the luxury prototype preset
        if (empty($products_data)) {
            $preset_items = $this->get_prototype_preset_products();
            foreach ($preset_items as $item) {
                $orig = (float) $item['originalPrice'];
                $cur  = (float) $item['price'];
                $pct  = ($orig > $cur) ? round((($orig - $cur) / $orig) * 100) : 0;

                $products_data[] = array(
                    'id'              => $item['id'],
                    'name'            => $item['name'],
                    'category'        => $item['category'],
                    'category_slugs'  => array($item['category']),
                    'categoryLabel'   => $item['categoryLabel'],
                    'price'           => $cur,
                    'originalPrice'   => $orig,
                    'rating'          => (float) $item['rating'],
                    'reviewsCount'    => (int) $item['reviewsCount'],
                    'badge'           => $item['badge'],
                    'discount_pct'    => $pct,
                    'is_sale'         => !empty($item['is_sale']),
                    'image'           => $item['image'],
                    'hoverImage'      => $item['hoverImage'],
                    'link'            => $item['link'],
                    'add_to_cart_url' => '#',
                    'is_purchasable'  => true,
                );
            }
        }

        // Filter tabs setup
        $filter_tabs = array();
        if ('yes' === $settings['show_filter_tabs']) {
            $tabs_source = !empty($settings['tabs_source']) ? $settings['tabs_source'] : 'wc_categories';

            // Show "All Creations" Tab
            $show_all = (!isset($settings['tabs_show_all']) || 'yes' === $settings['tabs_show_all']);
            if ($show_all) {
                $all_label = !empty($settings['tabs_all_label']) ? $settings['tabs_all_label'] : esc_html__('All Creations', 'kinboni-core');
                $filter_tabs[] = array('slug' => 'all', 'label' => $all_label);
            }

            if ('from_products' === $tabs_source && !empty($loaded_categories_map)) {
                // Auto-sync tabs from loaded products
                foreach ($loaded_categories_map as $c_slug => $c_name) {
                    $filter_tabs[] = array('slug' => $c_slug, 'label' => $c_name);
                }
            } elseif ('manual_static' === $tabs_source) {
                // Fixed 8 prototype tabs
                $raw_preset = $this->get_preset_filter_tabs();
                foreach ($raw_preset as $pt) {
                    if ($pt['slug'] === 'all') continue;
                    if ($pt['slug'] === 'sale') continue;
                    $filter_tabs[] = $pt;
                }
            } else {
                // Dynamic WooCommerce Categories (wc_categories or preset auto-fallback)
                $cat_args = array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                );

                // Specific categories chosen
                if (!empty($settings['tabs_select_categories']) && is_array($settings['tabs_select_categories'])) {
                    $selected_slugs = array_values(array_filter($settings['tabs_select_categories']));
                    if (!empty($selected_slugs)) {
                        $cat_args['slug'] = $selected_slugs;
                    }
                } else {
                    // Parent only
                    if (!isset($settings['tabs_parent_only']) || 'yes' === $settings['tabs_parent_only']) {
                        $cat_args['parent'] = 0;
                    }

                    // Exclude uncategorized
                    if (!isset($settings['tabs_exclude_uncat']) || 'yes' === $settings['tabs_exclude_uncat']) {
                        $default_cat_id = (int) get_option('default_product_cat');
                        $exclude_ids = array();
                        if ($default_cat_id) {
                            $exclude_ids[] = $default_cat_id;
                        }
                        $uncat = get_term_by('slug', 'uncategorized', 'product_cat');
                        if ($uncat && !is_wp_error($uncat)) {
                            $exclude_ids[] = $uncat->term_id;
                        }
                        if (!empty($exclude_ids)) {
                            $cat_args['exclude'] = array_unique($exclude_ids);
                        }
                    }
                }

                $terms = taxonomy_exists('product_cat') ? get_terms($cat_args) : array();

                // If parent=0 returned nothing, try without parent=0
                if ((empty($terms) || is_wp_error($terms)) && empty($selected_slugs) && taxonomy_exists('product_cat')) {
                    unset($cat_args['parent']);
                    $terms = get_terms($cat_args);
                }

                if (!empty($terms) && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $filter_tabs[] = array('slug' => $term->slug, 'label' => $term->name);
                    }
                } elseif (!empty($loaded_categories_map)) {
                    foreach ($loaded_categories_map as $c_slug => $c_name) {
                        $filter_tabs[] = array('slug' => $c_slug, 'label' => $c_name);
                    }
                } else {
                    // Fallback to preset tabs if no WC categories exist yet
                    $raw_preset = $this->get_preset_filter_tabs();
                    foreach ($raw_preset as $pt) {
                        if ($pt['slug'] === 'all') continue;
                        if ($pt['slug'] === 'sale') continue;
                        $filter_tabs[] = $pt;
                    }
                }
            }

            // Show "Private Sale" Tab
            $show_sale = (!isset($settings['tabs_show_sale']) || 'yes' === $settings['tabs_show_sale']);
            if ($show_sale) {
                $sale_label = !empty($settings['tabs_sale_label']) ? $settings['tabs_sale_label'] : esc_html__('Private Sale', 'kinboni-core');
                $filter_tabs[] = array('slug' => 'sale', 'label' => $sale_label);
            }
        }
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-bestsellers-section relative overflow-hidden py-16 sm:py-24 scroll-mt-24">
  <?php if ('yes' === $settings['show_ambient_glows']) : ?>
  <div class="absolute inset-0 bg-gradient-to-b from-gold-200/35 via-transparent to-transparent dark:from-gold-900/15 pointer-events-none"></div>
  <div class="absolute -top-24 -left-24 w-96 h-96 bg-gold-300/25 dark:bg-gold-500/15 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 right-10 w-80 h-80 bg-amber-300/20 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
  <?php endif; ?>

  <?php if ('yes' === $settings['show_radial_dots']) : ?>
  <div class="absolute inset-0 opacity-35 dark:opacity-20 bg-[radial-gradient(#C5A880_1px,transparent_1px)] dark:bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:28px_28px] pointer-events-none"></div>
  <?php endif; ?>

  <?php if ('yes' === $settings['show_architectural_shapes']) : ?>
  <div class="block absolute top-8 right-8 w-80 h-80 rounded-full border-2 border-gold-600/25 dark:border-gold-500/15 bg-gold-200/10 dark:bg-gold-500/[0.02] pointer-events-none"></div>
  <div class="block absolute top-20 right-20 w-56 h-56 rounded-full border border-dashed border-gold-700/30 dark:border-gold-400/20 pointer-events-none"></div>
  <div class="block absolute -bottom-16 left-12 w-72 h-72 rounded-t-[999px] border border-gold-600/20 dark:border-gold-500/12 bg-gold-100/15 dark:bg-transparent pointer-events-none"></div>
  <div class="block absolute top-1/3 left-10 w-24 h-24 rotate-45 border border-gold-600/20 dark:border-gold-500/15 pointer-events-none"></div>
  <?php endif; ?>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <?php if ('yes' === $settings['show_header']) : ?>
    <div class="text-center max-w-2xl mx-auto mb-10">
      <?php if (!empty($badge_text)) : ?>
      <<?php echo $badge_tag; ?> class="kinboni-bs-badge text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400 inline-block">
        <?php echo esc_html($badge_text); ?>
      </<?php echo $badge_tag; ?>>
      <?php endif; ?>

      <?php if (!empty($title_text)) : ?>
      <<?php echo $title_tag; ?> class="kinboni-bs-title font-serif text-3xl sm:text-4xl font-medium text-stone-900 dark:text-white mt-1">
        <?php echo esc_html($title_text); ?>
      </<?php echo $title_tag; ?>>
      <?php endif; ?>

      <?php if ('yes' === $settings['show_divider']) : ?>
      <div class="kinboni-bs-divider w-12 h-0.5 bg-gold-500 mx-auto mt-3 mb-4"></div>
      <?php endif; ?>

      <?php if (!empty($settings['description_text'])) : ?>
      <<?php echo $desc_tag; ?> class="kinboni-bs-desc text-sm text-stone-600 dark:text-stone-400 font-normal">
        <?php echo esc_html($settings['description_text']); ?>
      </<?php echo $desc_tag; ?>>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($filter_tabs)) : ?>
    <div class="flex items-center justify-center flex-wrap gap-2 mb-12">
      <?php foreach ($filter_tabs as $tab) :
        $is_active = ($tab['slug'] === $default_filter);
        $active_class = $is_active
          ? 'bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 border-transparent shadow-sm'
          : 'bg-transparent text-stone-700 dark:text-stone-300 border-stone-300 dark:border-stone-700 hover:border-gold-500';
      ?>
      <button
        type="button"
        class="bestseller-filter-btn px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-300 border <?php echo esc_attr($active_class); ?>"
        data-filter="<?php echo esc_attr($tab['slug']); ?>"
      >
        <?php echo esc_html($tab['label']); ?>
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div id="<?php echo esc_attr($grid_id); ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
      <?php foreach ($products_data as $product) :
        $pid          = $product['id'];
        $pname        = $product['name'];
        $plink        = $product['link'];
        $pimage       = $product['image'];
        $phover       = $product['hoverImage'];
        $cat_slug     = $product['category'];
        $cat_label    = $product['categoryLabel'];
        $price        = $product['price'];
        $orig_price   = $product['originalPrice'];
        $badge        = $product['badge'];
        $discount_pct = $product['discount_pct'];
        $rating       = $product['rating'];
        $reviews      = $product['reviewsCount'];
        $cart_url     = !empty($product['add_to_cart_url']) ? $product['add_to_cart_url'] : $plink;
        $is_purchasable = !empty($product['is_purchasable']);
        $is_sale_str  = !empty($product['is_sale']) ? 'true' : 'false';

        // Check if initial visibility should be shown based on default active filter
        $cat_slugs_list = !empty($product['category_slugs']) ? $product['category_slugs'] : array($cat_slug);
        $is_visible = ('all' === $default_filter || in_array($default_filter, $cat_slugs_list, true) || ('sale' === $default_filter && !empty($product['is_sale'])));
        $style_attr = $is_visible ? '' : 'style="display:none;"';
      ?>
      <div 
        class="kinboni-bestseller-card group relative flex flex-col bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-luxury dark:hover:shadow-luxury-dark hover:border-gold-500/40 dark:hover:border-gold-500/40 transition-all duration-500 cursor-pointer" 
        data-product-id="<?php echo esc_attr($pid); ?>" 
        data-category="<?php echo esc_attr($product['category']); ?>" 
        data-categories="<?php echo esc_attr(json_encode($cat_slugs_list)); ?>" 
        data-is-sale="<?php echo esc_attr($is_sale_str); ?>"
        <?php echo $style_attr; ?>
      >
        <div class="relative w-full aspect-[4/5] bg-stone-100 dark:bg-stone-950 overflow-hidden cursor-pointer" onclick="if (!event.target.closest('button, a')) window.location.href='<?php echo esc_url($plink); ?>'">
          <img 
            src="<?php echo esc_url($pimage); ?>" 
            alt="<?php echo esc_attr($pname); ?>" 
            loading="lazy" 
            class="w-full h-full object-cover object-center transition-all duration-700 ease-out group-hover:scale-105"
          />

          <?php if (!empty($phover) && $phover !== $pimage) : ?>
          <img 
            src="<?php echo esc_url($phover); ?>" 
            alt="<?php echo esc_attr($pname); ?> alternate angle" 
            loading="lazy" 
            class="absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-700 ease-out group-hover:opacity-100 group-hover:scale-105 pointer-events-none"
          />
          <?php endif; ?>

          <?php if ($show_badges && (!empty($badge) || $discount_pct > 0)) : ?>
          <div class="absolute top-3 left-3 right-14 flex flex-row flex-wrap items-center gap-1.5 z-10 pointer-events-none">
            <?php if (!empty($badge)) : ?>
            <span class="w-fit flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] rounded-full bg-stone-950/90 text-stone-100 dark:bg-white/95 dark:text-stone-950 shadow-md backdrop-blur-sm border border-white/10 dark:border-stone-900/10">
              <?php echo esc_html($badge); ?>
            </span>
            <?php endif; ?>

            <?php if ($discount_pct > 0) : ?>
            <span class="w-fit flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[11px] font-bold tracking-wider rounded-full bg-rose-600 text-white shadow-md">
              -<?php echo esc_html($discount_pct); ?>%
            </span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="absolute top-3 right-3 z-10 flex flex-col gap-2">
            <?php if ($show_wishlist) : ?>
            <button 
              type="button" 
              class="btn-toggle-wishlist w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:text-rose-500 hover:border-rose-400 dark:hover:border-rose-400 hover:scale-110 active:scale-90 transition-all shadow-md group/heart cursor-pointer" 
              data-product-id="<?php echo esc_attr($pid); ?>" 
              aria-label="<?php echo esc_attr(sprintf(__('Save %s to Wishlist', 'kinboni-core'), $pname)); ?>"
            >
              <svg class="w-4 h-4 transition-transform group-hover/heart:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </button>
            <?php endif; ?>

            <?php if ($show_quickview) : ?>
            <button 
              type="button" 
              class="btn-open-quickview w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:bg-gold-400 hover:text-stone-950 hover:border-gold-400 dark:hover:bg-gold-400 dark:hover:text-stone-950 hover:scale-110 active:scale-90 transition-all shadow-md cursor-pointer"
              data-product-id="<?php echo esc_attr($pid); ?>"
              aria-label="<?php echo esc_attr(sprintf(__('Quick View %s', 'kinboni-core'), $pname)); ?>"
              title="<?php esc_attr_e('Quick View', 'kinboni-core'); ?>"
            >
              <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
            <?php endif; ?>
          </div>
        </div>

        <div class="p-4 sm:p-5 flex flex-col flex-grow">
          <div class="flex items-center justify-between gap-2 mb-2 min-w-0">
            <span class="text-xs uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400 truncate">
              <?php echo esc_html($cat_label); ?>
            </span>
            <?php if ($show_ratings && $rating > 0) : ?>
            <div class="flex items-center gap-1 text-[11px] font-semibold text-stone-700 dark:text-stone-300 flex-shrink-0" title="<?php echo esc_attr(sprintf(__('%s out of 5 stars from %d verified reviews', 'kinboni-core'), number_format($rating, 1), $reviews)); ?>">
              <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              <span><?php echo esc_html(number_format($rating, 1)); ?></span>
              <span class="text-stone-400 dark:text-stone-500 font-normal">(<?php echo esc_html($reviews); ?>)</span>
            </div>
            <?php endif; ?>
          </div>

          <<?php echo $card_title_tag; ?> class="kinboni-product-card-title font-serif text-xl sm:text-2xl font-bold text-stone-900 dark:text-white leading-snug truncate mb-2 group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors" title="<?php echo esc_attr($pname); ?>">
            <a href="<?php echo esc_url($plink); ?>">
              <?php echo esc_html($pname); ?>
            </a>
          </<?php echo $card_title_tag; ?>>

          <div class="flex items-baseline gap-2 mb-3">
            <span class="font-serif text-xl sm:text-2xl font-bold text-stone-950 dark:text-white">
              ৳<?php echo esc_html(number_format($price)); ?>
            </span>
            <?php if ($orig_price > $price) : ?>
            <span class="text-xs text-stone-400 dark:text-stone-500 line-through font-normal">
              ৳<?php echo esc_html(number_format($orig_price)); ?>
            </span>
            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 ml-auto">
              Save ৳<?php echo esc_html(number_format($orig_price - $price)); ?>
            </span>
            <?php endif; ?>
          </div>

          <div class="flex items-center gap-2 mt-auto pt-3 border-t border-stone-100 dark:border-stone-800/80">
            <?php if ($is_purchasable && '#' !== $cart_url) : ?>
            <a 
              href="<?php echo esc_url($cart_url); ?>" 
              data-quantity="1"
              data-product_id="<?php echo esc_attr($pid); ?>"
              class="ajax_add_to_cart add_to_cart_button flex-1 py-2.5 px-3 rounded-xl bg-stone-200/80 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-100 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer" 
              title="<?php esc_attr_e('Add to shopping bag', 'kinboni-core'); ?>"
            >
              <i data-lucide="shopping-bag" class="w-3.5 h-3.5 flex-shrink-0 text-stone-600 dark:text-stone-300"></i>
              <span class="whitespace-nowrap"><?php echo esc_html($btn_cart_text); ?></span>
            </a>
            <?php else : ?>
            <a 
              href="<?php echo esc_url($plink); ?>" 
              class="flex-1 py-2.5 px-3 rounded-xl bg-stone-200/80 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-100 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer" 
              title="<?php esc_attr_e('View Product', 'kinboni-core'); ?>"
            >
              <i data-lucide="shopping-bag" class="w-3.5 h-3.5 flex-shrink-0 text-stone-600 dark:text-stone-300"></i>
              <span class="whitespace-nowrap"><?php echo esc_html($btn_cart_text); ?></span>
            </a>
            <?php endif; ?>

            <button 
              type="button" 
              class="btn-trigger-quick-order flex-1 py-2.5 px-3 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm hover:shadow-gold-500/25 active:scale-95 cursor-pointer" 
              title="<?php esc_attr_e('Direct Order with Cash on Delivery', 'kinboni-core'); ?>"
              data-product-id="<?php echo esc_attr($pid); ?>"
              data-product-name="<?php echo esc_attr($pname); ?>"
              data-product-price="<?php echo esc_attr($price); ?>"
              data-product-image="<?php echo esc_url($pimage); ?>"
            >
              <i data-lucide="zap" class="w-3.5 h-3.5 fill-stone-950 flex-shrink-0"></i>
              <span class="whitespace-nowrap"><?php echo esc_html($btn_cod_text); ?></span>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
        <?php
    }
}

