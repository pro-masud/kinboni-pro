<?php
/**
 * Kinboni Featured Creations - Elementor Widget
 *
 * Implements the luxury editorial "Featured Creations" section featuring
 * ambient gold glows, arched architectural silhouettes, customizable header,
 * and a 4-column responsive grid displaying WooCommerce products with
 * Wishlist, Quick View, Ajax Add to Bag, and Fast COD Checkout triggers.
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

class Kinboni_Widget_Featured_Products extends Widget_Base {

    public function get_name() {
        return 'kinboni_featured_products';
    }

    public function get_title() {
        return esc_html__('Featured Creations Grid', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-products';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('featured', 'products', 'creations', 'grid', 'woocommerce', 'atelier', 'kinboni', 'shop');
    }

    /**
     * Fetch all WooCommerce products for Select2 control
     */
    private function get_all_woocommerce_products() {
        $options = array();
        if (class_exists('WooCommerce')) {
            $products = wc_get_products(array(
                'limit'   => 150,
                'status'  => 'publish',
                'orderby' => 'title',
                'order'   => 'ASC',
            ));
            foreach ($products as $product) {
                $options[$product->get_id()] = sprintf('%s (৳%s)', $product->get_name(), $product->get_price());
            }
        }
        return $options;
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
                'default'     => esc_html__('Atelier Curations', 'kinboni-core'),
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
                'default'     => esc_html__('Featured Creations', 'kinboni-core'),
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
                'default'     => esc_html__('Hand-inspected, individually serial-numbered, and ready for complimentary white-glove dispatch.', 'kinboni-core'),
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
                    'manual_preset' => esc_html__('Atelier Prototype Preset (8 Creations)', 'kinboni-core'),
                ),
                'default' => 'wc_query',
            )
        );

        $this->add_control(
            'query_type',
            array(
                'label'     => esc_html__('Query Type', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'featured'      => esc_html__('Featured Products (Star Badge in WC)', 'kinboni-core'),
                    'recent'        => esc_html__('Latest / Newest Products', 'kinboni-core'),
                    'bestsellers'   => esc_html__('Top Best Sellers (Total Sales)', 'kinboni-core'),
                    'on_sale'       => esc_html__('On Sale Products', 'kinboni-core'),
                    'manual_select' => esc_html__('Manual Product Selection', 'kinboni-core'),
                    'by_category'   => esc_html__('Products by Category', 'kinboni-core'),
                ),
                'default'   => 'featured',
                'condition' => array('source_mode' => 'wc_query'),
            )
        );

        $this->add_control(
            'selected_products',
            array(
                'label'       => esc_html__('Select Products', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $this->get_all_woocommerce_products(),
                'label_block' => true,
                'condition'   => array(
                    'source_mode' => 'wc_query',
                    'query_type'  => 'manual_select',
                ),
            )
        );

        $this->add_control(
            'selected_categories',
            array(
                'label'       => esc_html__('Select Categories', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $this->get_all_wc_categories(),
                'label_block' => true,
                'condition'   => array(
                    'source_mode' => 'wc_query',
                    'query_type'  => 'by_category',
                ),
            )
        );

        $this->add_control(
            'products_limit',
            array(
                'label'     => esc_html__('Products Count', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 32,
                'step'      => 1,
                'default'   => 8,
                'condition' => array('source_mode' => 'wc_query'),
            )
        );

        $this->add_control(
            'orderby',
            array(
                'label'     => esc_html__('Order By', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'date'       => esc_html__('Date Created', 'kinboni-core'),
                    'title'      => esc_html__('Title', 'kinboni-core'),
                    'price'      => esc_html__('Price', 'kinboni-core'),
                    'popularity' => esc_html__('Popularity', 'kinboni-core'),
                    'rating'     => esc_html__('Rating', 'kinboni-core'),
                    'rand'       => esc_html__('Random', 'kinboni-core'),
                ),
                'default'   => 'date',
                'condition' => array(
                    'source_mode' => 'wc_query',
                    'query_type!' => array('manual_select', 'bestsellers'),
                ),
            )
        );

        $this->add_control(
            'order',
            array(
                'label'     => esc_html__('Order', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'DESC' => esc_html__('Descending (DESC)', 'kinboni-core'),
                    'ASC'  => esc_html__('Ascending (ASC)', 'kinboni-core'),
                ),
                'default'   => 'DESC',
                'condition' => array(
                    'source_mode' => 'wc_query',
                    'query_type!' => array('manual_select', 'bestsellers'),
                ),
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
                'default'     => 'featured',
                'placeholder' => 'featured',
            )
        );

        $this->add_control(
            'grid_html_id',
            array(
                'label'       => esc_html__('Grid Container ID', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'featured-products-grid',
                'placeholder' => 'featured-products-grid',
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
                'label'        => esc_html__('Show Arched Silhouette Shapes', 'kinboni-core'),
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
                    '{{WRAPPER}} .kinboni-featured-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-featured-section' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .kinboni-feat-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-feat-badge',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Main Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-feat-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-feat-title',
            )
        );

        $this->add_control(
            'divider_color',
            array(
                'label'     => esc_html__('Gold Line Divider Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-feat-divider' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-feat-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .kinboni-feat-desc',
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
                    '{{WRAPPER}} .kinboni-product-card:hover .kinboni-product-card-title' => 'color: {{VALUE}} !important;',
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
                'selector' => '{{WRAPPER}} .kinboni-product-card',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Preset data matching the prototype's 8 featured products
     */
    private function get_prototype_preset_products() {
        return array(
            array(
                'id'            => 1,
                'name'          => 'Chronos Royale Automatic',
                'categoryLabel' => 'Timepieces',
                'price'         => 18500,
                'originalPrice' => 22000,
                'rating'        => 4.9,
                'reviewsCount'  => 68,
                'badge'         => 'BESTSELLER',
                'image'         => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 2,
                'name'          => 'Atelier Saddle Leather Briefcase',
                'categoryLabel' => 'Leather Goods',
                'price'         => 14200,
                'originalPrice' => 16800,
                'rating'        => 5.0,
                'reviewsCount'  => 42,
                'badge'         => 'NEW',
                'image'         => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 3,
                'name'          => 'Nocturne Oud Extrait De Parfum',
                'categoryLabel' => 'Fine Fragrance',
                'price'         => 8900,
                'originalPrice' => 0,
                'rating'        => 4.8,
                'reviewsCount'  => 94,
                'badge'         => 'SIGNATURE',
                'image'         => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 4,
                'name'          => 'Aethelgard 18K Minimalist Cuff',
                'categoryLabel' => 'Fine Jewelry',
                'price'         => 6400,
                'originalPrice' => 7500,
                'rating'        => 4.9,
                'reviewsCount'  => 37,
                'badge'         => 'LIMITED',
                'image'         => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 5,
                'name'          => 'Monaco Polarized Acetate Shades',
                'categoryLabel' => 'Eyewear',
                'price'         => 5200,
                'originalPrice' => 0,
                'rating'        => 4.7,
                'reviewsCount'  => 53,
                'badge'         => '',
                'image'         => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 6,
                'name'          => 'Kyoto Mulberry Silk Foulard',
                'categoryLabel' => 'Silk & Cashmere',
                'price'         => 4800,
                'originalPrice' => 5600,
                'rating'        => 4.9,
                'reviewsCount'  => 29,
                'badge'         => 'ARTISANAL',
                'image'         => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 7,
                'name'          => 'Heritage Bi-Fold Card Wallet',
                'categoryLabel' => 'Leather Goods',
                'price'         => 3200,
                'originalPrice' => 3900,
                'rating'        => 4.8,
                'reviewsCount'  => 88,
                'badge'         => 'BESTSELLER',
                'image'         => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 8,
                'name'          => 'Sylvan Vetiver Botanical Parfum',
                'categoryLabel' => 'Fine Fragrance',
                'price'         => 9500,
                'originalPrice' => 0,
                'rating'        => 4.9,
                'reviewsCount'  => 46,
                'badge'         => 'NEW',
                'image'         => 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
        );
    }

    /**
     * Query WooCommerce products based on widget settings
     */
    private function get_query_products($settings) {
        if (!class_exists('WooCommerce')) {
            return array();
        }

        $limit      = !empty($settings['products_limit']) ? intval($settings['products_limit']) : 8;
        $query_type = !empty($settings['query_type']) ? $settings['query_type'] : 'featured';

        $args = array(
            'status' => 'publish',
            'limit'  => $limit,
        );

        if ('yes' === $settings['hide_out_of_stock']) {
            $args['stock_status'] = 'instock';
        }

        if ('manual_select' === $query_type && !empty($settings['selected_products']) && is_array($settings['selected_products'])) {
            $args['include'] = $settings['selected_products'];
            $args['orderby'] = 'post__in';
        } else {
            $orderby = !empty($settings['orderby']) ? $settings['orderby'] : 'date';
            $order   = !empty($settings['order']) ? $settings['order'] : 'DESC';

            $args['orderby'] = $orderby;
            $args['order']   = $order;

            if ('featured' === $query_type) {
                $args['featured'] = true;
                $products = wc_get_products($args);

                // If fewer products returned than limit (e.g. store has few or no featured products marked),
                // backfill with latest published store products so real products always display
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
                    if ('yes' === $settings['hide_out_of_stock']) {
                        $fill_args['stock_status'] = 'instock';
                    }
                    $fill_products = wc_get_products($fill_args);
                    if (!empty($fill_products)) {
                        $products = array_merge($products, $fill_products);
                    }
                }
                return $products;
            } elseif ('on_sale' === $query_type) {
                $product_ids_on_sale = wc_get_product_ids_on_sale();
                $args['include']     = !empty($product_ids_on_sale) ? $product_ids_on_sale : array(0);
                $products = wc_get_products($args);
                if (empty($products)) {
                    unset($args['include']);
                    $products = wc_get_products($args);
                }
                return $products;
            } elseif ('bestsellers' === $query_type) {
                $args['orderby']  = 'meta_value_num';
                $args['meta_key'] = 'total_sales';
                $args['order']    = 'DESC';
                $products = wc_get_products($args);
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
                    $fill_products = wc_get_products($fill_args);
                    if (!empty($fill_products)) {
                        $products = array_merge($products, $fill_products);
                    }
                }
                return $products;
            } elseif ('by_category' === $query_type && !empty($settings['selected_categories'])) {
                $args['category'] = (array) $settings['selected_categories'];
            }
        }

        return wc_get_products($args);
    }

    /**
     * Render widget output on frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'featured';
        $grid_id    = !empty($settings['grid_html_id']) ? sanitize_html_class($settings['grid_html_id']) : 'featured-products-grid';

        $badge_tag = in_array($settings['badge_tag'], array('span', 'p', 'div', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4', 'div'), true) ? $settings['title_tag'] : 'h2';
        $desc_tag  = in_array($settings['desc_tag'], array('p', 'div', 'span'), true) ? $settings['desc_tag'] : 'p';
        $card_title_tag = in_array($settings['card_title_tag'], array('h2', 'h3', 'h4', 'h5', 'div'), true) ? $settings['card_title_tag'] : 'h3';

        $badge_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'featured_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $title_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'featured_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');

        $btn_cart_text = !empty($settings['btn_cart_text']) ? $settings['btn_cart_text'] : esc_html__('Add to Bag', 'kinboni-core');
        $btn_cod_text  = !empty($settings['btn_cod_text']) ? $settings['btn_cod_text'] : esc_html__('Order Now', 'kinboni-core');

        $show_wishlist  = ('yes' === $settings['show_wishlist_btn']);
        $show_quickview = ('yes' === $settings['show_quickview_btn']);
        $show_badges    = ('yes' === $settings['show_badges']);
        $show_ratings   = ('yes' === $settings['show_ratings']);

        $products_data = array();

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
                    $cat_label = ($terms && !is_wp_error($terms)) ? $terms[0]->name : esc_html__('Atelier Collection', 'kinboni-core');

                    $badge = '';
                    if ($product->is_featured()) {
                        $badge = 'SIGNATURE';
                    } elseif ($is_sale) {
                        $badge = 'PRIVILEGE';
                    }

                    $products_data[] = array(
                        'id'            => $pid,
                        'name'          => $pname,
                        'categoryLabel' => $cat_label,
                        'price'         => $price,
                        'originalPrice' => ($is_sale && $reg_price > $price) ? $reg_price : 0,
                        'rating'        => (float) $product->get_average_rating(),
                        'reviewsCount'  => (int) $product->get_review_count(),
                        'badge'         => $badge,
                        'discount_pct'  => $discount_pct,
                        'image'         => $main_img,
                        'hoverImage'    => $hover_img,
                        'link'          => $plink,
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
                    'categoryLabel'   => $item['categoryLabel'],
                    'price'           => $cur,
                    'originalPrice'   => $orig,
                    'rating'          => (float) $item['rating'],
                    'reviewsCount'    => (int) $item['reviewsCount'],
                    'badge'           => $item['badge'],
                    'discount_pct'    => $pct,
                    'image'           => $item['image'],
                    'hoverImage'      => $item['hoverImage'],
                    'link'            => $item['link'],
                    'add_to_cart_url' => '#',
                    'is_purchasable'  => true,
                );
            }
        }
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-featured-section relative overflow-hidden py-16 sm:py-24 scroll-mt-24">
  <?php if ('yes' === $settings['show_radial_dots']) : ?>
  <div class="absolute inset-0 opacity-40 dark:opacity-20 bg-[radial-gradient(#C5A880_1px,transparent_1px)] dark:bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
  <?php endif; ?>

  <?php if ('yes' === $settings['show_ambient_glows']) : ?>
  <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-gold-300/25 dark:bg-gold-500/15 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-amber-200/25 dark:bg-gold-400/12 rounded-full blur-3xl pointer-events-none"></div>
  <?php endif; ?>

  <?php if ('yes' === $settings['show_architectural_shapes']) : ?>
  <div class="block absolute top-10 left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-t-[999px] border-2 border-gold-600/20 dark:border-gold-500/12 bg-gradient-to-b from-gold-200/15 to-transparent dark:from-gold-500/[0.03] pointer-events-none"></div>
  <div class="block absolute top-20 left-1/2 -translate-x-1/2 w-[480px] h-[480px] rounded-t-[999px] border border-dashed border-gold-700/25 dark:border-gold-400/15 pointer-events-none"></div>
  <div class="block absolute bottom-12 right-12 w-48 h-48 rounded-full border border-gold-600/25 dark:border-gold-500/15 pointer-events-none"></div>
  <?php endif; ?>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <?php if ('yes' === $settings['show_header']) : ?>
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
      <div>
        <?php if (!empty($badge_text)) : ?>
        <<?php echo $badge_tag; ?> class="kinboni-feat-badge text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400 inline-block">
          <?php echo esc_html($badge_text); ?>
        </<?php echo $badge_tag; ?>>
        <?php endif; ?>

        <?php if (!empty($title_text)) : ?>
        <<?php echo $title_tag; ?> class="kinboni-feat-title font-serif text-3xl sm:text-4xl font-medium text-stone-900 dark:text-white mt-1">
          <?php echo esc_html($title_text); ?>
        </<?php echo $title_tag; ?>>
        <?php endif; ?>

        <?php if ('yes' === $settings['show_divider']) : ?>
        <div class="kinboni-feat-divider w-12 h-0.5 bg-gold-500 mt-3"></div>
        <?php endif; ?>
      </div>

      <?php if (!empty($settings['description_text'])) : ?>
      <<?php echo $desc_tag; ?> class="kinboni-feat-desc mt-4 md:mt-0 text-sm text-stone-600 dark:text-stone-400 max-w-md font-normal">
        <?php echo esc_html($settings['description_text']); ?>
      </<?php echo $desc_tag; ?>>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div id="<?php echo esc_attr($grid_id); ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
      <?php foreach ($products_data as $product) :
        $pid          = $product['id'];
        $pname        = $product['name'];
        $plink        = $product['link'];
        $pimage       = $product['image'];
        $phover       = $product['hoverImage'];
        $cat_label    = $product['categoryLabel'];
        $price        = $product['price'];
        $orig_price   = $product['originalPrice'];
        $badge        = $product['badge'];
        $discount_pct = $product['discount_pct'];
        $rating       = $product['rating'];
        $reviews      = $product['reviewsCount'];
        $cart_url     = !empty($product['add_to_cart_url']) ? $product['add_to_cart_url'] : $plink;
        $is_purchasable = !empty($product['is_purchasable']);
      ?>
      <div class="kinboni-product-card group relative flex flex-col bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-luxury dark:hover:shadow-luxury-dark hover:border-gold-500/40 dark:hover:border-gold-500/40 transition-all duration-500 cursor-pointer" data-product-id="<?php echo esc_attr($pid); ?>">
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

