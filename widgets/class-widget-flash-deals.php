<?php
/**
 * Kinboni Flash Deals & Vault Archive - Elementor Widget
 *
 * Implements the luxury editorial "Flash Deals & Private Archive Privilege" page section
 * featuring ambient geometric arch shapes, dramatic luxury countdown hero banner,
 * interactive promo voucher triggers (copy & auto-apply), live ticking countdown timer,
 * and a responsive grid querying real WooCommerce on-sale products with Quick COD & Bag actions.
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

class Kinboni_Widget_Flash_Deals extends Widget_Base {

    public function get_name() {
        return 'kinboni_flash_deals';
    }

    public function get_title() {
        return esc_html__('Private Archive Flash Deals', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-countdown';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('flash', 'deals', 'sale', 'archive', 'countdown', 'vault', 'privilege', 'discount', 'woocommerce', 'kinboni');
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
        // CONTENT TAB: COUNTDOWN HERO BANNER
        // ==========================================
        $this->start_controls_section(
            'section_banner_content',
            array(
                'label' => esc_html__('Hero Banner Copy', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'badge_text',
            array(
                'label'       => esc_html__('Badge Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Limited Private Vault Release', 'kinboni-core'),
                'placeholder' => esc_html__('Limited Private Vault Release', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'pulse_dot',
            array(
                'label'        => esc_html__('Show Pulsing Dot in Badge', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'banner_title',
            array(
                'label'       => esc_html__('Main Headline', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('The Autumn Private Archive Privilege — Up to 20% Off', 'kinboni-core'),
                'placeholder' => esc_html__('Enter headline', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'banner_title_tag',
            array(
                'label'   => esc_html__('Headline Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                ),
                'default' => 'h1',
            )
        );

        $this->add_control(
            'banner_desc',
            array(
                'label'       => esc_html__('Description Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Acquire numbered horological editions, vegetable-tanned Tuscan briefcases, and South Sea pearl creations with immediate price reductions plus extra <strong class="text-gold-400">LUXE20</strong> voucher privileges.', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'show_promo_buttons',
            array(
                'label'        => esc_html__('Show Promo Action Buttons', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'promo_code',
            array(
                'label'       => esc_html__('Promo Voucher Code', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'LUXE20',
                'placeholder' => 'LUXE20',
                'condition'   => array('show_promo_buttons' => 'yes'),
            )
        );

        $this->add_control(
            'btn_copy_text',
            array(
                'label'       => esc_html__('Copy Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Copy Code: LUXE20', 'kinboni-core'),
                'condition'   => array('show_promo_buttons' => 'yes'),
            )
        );

        $this->add_control(
            'btn_apply_text',
            array(
                'label'       => esc_html__('Auto-Apply Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Auto-Apply 20% Voucher to Bag', 'kinboni-core'),
                'condition'   => array('show_promo_buttons' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: LIVE COUNTDOWN TIMER
        // ==========================================
        $this->start_controls_section(
            'section_timer_settings',
            array(
                'label' => esc_html__('Live Countdown Timer', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'timer_title',
            array(
                'label'   => esc_html__('Timer Header Label', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Archive Privilege Closes In:', 'kinboni-core'),
            )
        );

        $this->add_control(
            'countdown_type',
            array(
                'label'   => esc_html__('Timer Mode', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'evergreen'   => esc_html__('Evergreen (Rolling Timer)', 'kinboni-core'),
                    'target_date' => esc_html__('Fixed Target Date', 'kinboni-core'),
                ),
                'default' => 'evergreen',
            )
        );

        $this->add_control(
            'target_date',
            array(
                'label'       => esc_html__('End Date & Time', 'kinboni-core'),
                'type'        => Controls_Manager::DATE_TIME,
                'default'     => date('Y-m-d H:i', strtotime('+3 days')),
                'description' => esc_html__('Select the exact closing date and time.', 'kinboni-core'),
                'condition'   => array('countdown_type' => 'target_date'),
            )
        );

        $this->add_control(
            'evergreen_days',
            array(
                'label'     => esc_html__('Days', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 2,
                'min'       => 0,
                'max'       => 365,
                'condition' => array('countdown_type' => 'evergreen'),
            )
        );

        $this->add_control(
            'evergreen_hours',
            array(
                'label'     => esc_html__('Hours', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 14,
                'min'       => 0,
                'max'       => 23,
                'condition' => array('countdown_type' => 'evergreen'),
            )
        );

        $this->add_control(
            'evergreen_mins',
            array(
                'label'     => esc_html__('Minutes', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 38,
                'min'       => 0,
                'max'       => 59,
                'condition' => array('countdown_type' => 'evergreen'),
            )
        );

        $this->add_control(
            'evergreen_secs',
            array(
                'label'     => esc_html__('Seconds', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 45,
                'min'       => 0,
                'max'       => 59,
                'condition' => array('countdown_type' => 'evergreen'),
            )
        );

        $this->add_control(
            'label_days',
            array(
                'label'   => esc_html__('Days Label', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Days', 'kinboni-core'),
            )
        );

        $this->add_control(
            'label_hours',
            array(
                'label'   => esc_html__('Hours Label', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Hours', 'kinboni-core'),
            )
        );

        $this->add_control(
            'label_mins',
            array(
                'label'   => esc_html__('Mins Label', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Mins', 'kinboni-core'),
            )
        );

        $this->add_control(
            'label_secs',
            array(
                'label'   => esc_html__('Secs Label', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Secs', 'kinboni-core'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: WOOCOMMERCE PRODUCTS QUERY
        // ==========================================
        $this->start_controls_section(
            'section_products_query',
            array(
                'label' => esc_html__('Products Query & Data', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'query_type',
            array(
                'label'   => esc_html__('Product Query Source', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'on_sale'       => esc_html__('On Sale / Discounted Products', 'kinboni-core'),
                    'manual_select' => esc_html__('Manually Select Products', 'kinboni-core'),
                    'by_category'   => esc_html__('Filter by Categories', 'kinboni-core'),
                    'featured'      => esc_html__('Featured Products', 'kinboni-core'),
                    'latest'        => esc_html__('Recent Products', 'kinboni-core'),
                ),
                'default' => 'on_sale',
            )
        );

        $this->add_control(
            'selected_products',
            array(
                'label'       => esc_html__('Choose Products', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_all_woocommerce_products(),
                'multiple'    => true,
                'label_block' => true,
                'condition'   => array('query_type' => 'manual_select'),
            )
        );

        $this->add_control(
            'selected_categories',
            array(
                'label'       => esc_html__('Choose Categories', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_all_wc_categories(),
                'multiple'    => true,
                'label_block' => true,
                'condition'   => array('query_type' => 'by_category'),
            )
        );

        $this->add_control(
            'products_limit',
            array(
                'label'   => esc_html__('Number of Products', 'kinboni-core'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 8,
                'min'     => 1,
                'max'     => 40,
            )
        );

        $this->add_control(
            'orderby',
            array(
                'label'     => esc_html__('Order By', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'date',
                'options'   => array(
                    'date'       => esc_html__('Date', 'kinboni-core'),
                    'title'      => esc_html__('Title', 'kinboni-core'),
                    'price'      => esc_html__('Price', 'kinboni-core'),
                    'popularity' => esc_html__('Popularity / Sales', 'kinboni-core'),
                    'rand'       => esc_html__('Random', 'kinboni-core'),
                ),
                'condition' => array('query_type!' => 'manual_select'),
            )
        );

        $this->add_control(
            'order',
            array(
                'label'     => esc_html__('Sort Order', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'DESC',
                'options'   => array(
                    'DESC' => esc_html__('Descending', 'kinboni-core'),
                    'ASC'  => esc_html__('Ascending', 'kinboni-core'),
                ),
                'condition' => array('query_type!' => 'manual_select'),
            )
        );

        $this->add_control(
            'hide_out_of_stock',
            array(
                'label'        => esc_html__('Hide Out of Stock', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: PRODUCTS GRID HEADER
        // ==========================================
        $this->start_controls_section(
            'section_grid_header',
            array(
                'label' => esc_html__('Products Grid Header', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'grid_badge',
            array(
                'label'       => esc_html__('Grid Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Numbered Editions', 'kinboni-core'),
                'placeholder' => esc_html__('Numbered Editions', 'kinboni-core'),
            )
        );

        $this->add_control(
            'grid_title',
            array(
                'label'       => esc_html__('Grid Headline', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Creations on Archive Privilege', 'kinboni-core'),
                'placeholder' => esc_html__('Creations on Archive Privilege', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $this->add_control(
            'grid_title_tag',
            array(
                'label'   => esc_html__('Headline Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                    'div' => 'div',
                ),
                'default' => 'h2',
            )
        );

        $this->add_control(
            'grid_sub_notice',
            array(
                'label'       => esc_html__('Right-side Notice', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Complimentary Express Delivery Included', 'kinboni-core'),
                'placeholder' => esc_html__('Complimentary Express Delivery Included', 'kinboni-core'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: AMBIENT DECOR & SETTINGS
        // ==========================================
        $this->start_controls_section(
            'section_decor_settings',
            array(
                'label' => esc_html__('Ambient Decor & ID', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_ambient_shapes',
            array(
                'label'        => esc_html__('Show Ambient Glow & Geometric Shapes', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'section_html_id',
            array(
                'label'       => esc_html__('Section HTML ID', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'page-view-flash-deals',
                'placeholder' => 'page-view-flash-deals',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: SECTION & HERO BANNER
        // ==========================================
        $this->start_controls_section(
            'section_style_banner',
            array(
                'label' => esc_html__('Hero Banner Style', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'banner_bg_color',
            array(
                'label'     => esc_html__('Banner Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-countdown-hero' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'banner_border_color',
            array(
                'label'     => esc_html__('Banner Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-countdown-hero' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'banner_padding',
            array(
                'label'      => esc_html__('Banner Padding', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-countdown-hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'banner_box_shadow',
                'selector' => '{{WRAPPER}} .kinboni-countdown-hero',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: COUNTDOWN TIMER BOXES
        // ==========================================
        $this->start_controls_section(
            'section_style_timer',
            array(
                'label' => esc_html__('Timer Boxes Style', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'timer_box_bg',
            array(
                'label'     => esc_html__('Box Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-timer-box' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'timer_box_border',
            array(
                'label'     => esc_html__('Box Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-timer-box' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'timer_number_color',
            array(
                'label'     => esc_html__('Digits Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-timer-num' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'timer_label_color',
            array(
                'label'     => esc_html__('Labels Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-timer-label' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Query WooCommerce products based on widget settings
     */
    private function get_query_products($settings) {
        if (!class_exists('WooCommerce')) {
            return array();
        }

        $limit      = !empty($settings['products_limit']) ? intval($settings['products_limit']) : 8;
        $query_type = !empty($settings['query_type']) ? $settings['query_type'] : 'on_sale';

        $args = array(
            'status' => 'publish',
            'limit'  => $limit,
        );

        if (!empty($settings['hide_out_of_stock']) && 'yes' === $settings['hide_out_of_stock']) {
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

            if ('on_sale' === $query_type) {
                $product_ids_on_sale = wc_get_product_ids_on_sale();
                $args['include']     = !empty($product_ids_on_sale) ? $product_ids_on_sale : array(0);
            } elseif ('featured' === $query_type) {
                $args['featured'] = true;
            } elseif ('by_category' === $query_type && !empty($settings['selected_categories'])) {
                $args['category'] = (array) $settings['selected_categories'];
            }
        }

        return wc_get_products($args);
    }

    /**
     * Luxury Prototype Fallback Items
     */
    private function get_prototype_preset_products() {
        return array(
            array(
                'id'            => 1,
                'name'          => 'Chronos Royale Tourbillon 42mm',
                'categoryLabel' => 'Horology',
                'price'         => 18500,
                'originalPrice' => 22000,
                'rating'        => 4.9,
                'reviewsCount'  => 42,
                'badge'         => 'ARCHIVE',
                'image'         => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 2,
                'name'          => 'Atelier Saddle Leather Briefcase',
                'categoryLabel' => 'Leathercraft',
                'price'         => 14200,
                'originalPrice' => 16500,
                'rating'        => 4.8,
                'reviewsCount'  => 36,
                'badge'         => 'PRIVILEGE',
                'image'         => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 3,
                'name'          => 'Aurelia 18K Solid Gold Signet Ring',
                'categoryLabel' => 'Haute Joaillerie',
                'price'         => 24800,
                'originalPrice' => 29000,
                'rating'        => 5.0,
                'reviewsCount'  => 19,
                'badge'         => 'VAULT',
                'image'         => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
            array(
                'id'            => 4,
                'name'          => 'Kyoto Mulberry Silk Foulard Scarf',
                'categoryLabel' => 'Silk & Cashmere',
                'price'         => 4800,
                'originalPrice' => 5600,
                'rating'        => 4.9,
                'reviewsCount'  => 29,
                'badge'         => 'LIMITED',
                'image'         => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=800&q=80',
                'hoverImage'    => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=80',
                'link'          => '#',
            ),
        );
    }

    /**
     * Render Widget Output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'page-view-flash-deals';

        $show_decor = (!empty($settings['show_ambient_shapes']) && $settings['show_ambient_shapes'] === 'yes');

        // Banner elements
        $badge_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'flash_deals_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $pulse_dot  = (!empty($settings['pulse_dot']) && $settings['pulse_dot'] === 'yes');
        $banner_title = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'banner_title', 'flash_deals_title', '', $this) : (!empty($settings['banner_title']) ? $settings['banner_title'] : '');
        $banner_title_tag = in_array($settings['banner_title_tag'], array('h1', 'h2', 'h3', 'h4'), true) ? $settings['banner_title_tag'] : 'h1';
        $banner_desc = !empty($settings['banner_desc']) ? $settings['banner_desc'] : '';

        $show_promo = (!empty($settings['show_promo_buttons']) && $settings['show_promo_buttons'] === 'yes');
        $promo_code = class_exists('Kinboni_Content') ? sanitize_text_field(Kinboni_Content::resolve($settings, 'promo_code', 'vip_coupon_code', 'LUXE20', $this)) : (!empty($settings['promo_code']) ? sanitize_text_field($settings['promo_code']) : 'LUXE20');
        $btn_copy   = !empty($settings['btn_copy_text']) ? $settings['btn_copy_text'] : sprintf(esc_html__('Copy Code: %s', 'kinboni-core'), $promo_code);
        $btn_apply  = !empty($settings['btn_apply_text']) ? $settings['btn_apply_text'] : esc_html__('Auto-Apply 20% Voucher to Bag', 'kinboni-core');

        // Timer settings
        $timer_title = !empty($settings['timer_title']) ? $settings['timer_title'] : '';
        $countdown_type = !empty($settings['countdown_type']) ? $settings['countdown_type'] : 'evergreen';
        $target_date = !empty($settings['target_date']) ? $settings['target_date'] : date('Y-m-d H:i', strtotime('+3 days'));

        $ev_days  = isset($settings['evergreen_days']) ? intval($settings['evergreen_days']) : 2;
        $ev_hours = isset($settings['evergreen_hours']) ? intval($settings['evergreen_hours']) : 14;
        $ev_mins  = isset($settings['evergreen_mins']) ? intval($settings['evergreen_mins']) : 38;
        $ev_secs  = isset($settings['evergreen_secs']) ? intval($settings['evergreen_secs']) : 45;
        $evergreen_seconds = ($ev_days * 86400) + ($ev_hours * 3600) + ($ev_mins * 60) + $ev_secs;

        $label_days  = !empty($settings['label_days']) ? $settings['label_days'] : esc_html__('Days', 'kinboni-core');
        $label_hours = !empty($settings['label_hours']) ? $settings['label_hours'] : esc_html__('Hours', 'kinboni-core');
        $label_mins  = !empty($settings['label_mins']) ? $settings['label_mins'] : esc_html__('Mins', 'kinboni-core');
        $label_secs  = !empty($settings['label_secs']) ? $settings['label_secs'] : esc_html__('Secs', 'kinboni-core');

        // Grid header
        $grid_badge = !empty($settings['grid_badge']) ? $settings['grid_badge'] : '';
        $grid_title = !empty($settings['grid_title']) ? $settings['grid_title'] : '';
        $grid_title_tag = in_array($settings['grid_title_tag'], array('h2', 'h3', 'h4', 'div'), true) ? $settings['grid_title_tag'] : 'h2';
        $grid_notice = !empty($settings['grid_sub_notice']) ? $settings['grid_sub_notice'] : '';

        // Query Products
        $products_data = array();
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

                $badge = 'PRIVILEGE';
                if ($product->is_featured()) {
                    $badge = 'SIGNATURE';
                }

                $products_data[] = array(
                    'id'              => $pid,
                    'name'            => $pname,
                    'categoryLabel'   => $cat_label,
                    'price'           => $price,
                    'originalPrice'   => ($is_sale && $reg_price > $price) ? $reg_price : 0,
                    'rating'          => (float) $product->get_average_rating(),
                    'reviewsCount'    => (int) $product->get_review_count(),
                    'badge'           => $badge,
                    'discount_pct'    => $discount_pct,
                    'image'           => $main_img,
                    'hoverImage'      => $hover_img,
                    'link'            => $plink,
                    'add_to_cart_url' => $product->add_to_cart_url(),
                    'is_purchasable'  => $product->is_purchasable() && $product->is_in_stock(),
                );
            }
        }

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
<section 
  id="<?php echo esc_attr($section_id); ?>" 
  class="kinboni-flash-deals-section wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]"
  data-countdown-type="<?php echo esc_attr($countdown_type); ?>"
  data-target-date="<?php echo esc_attr($target_date); ?>"
  data-evergreen-seconds="<?php echo esc_attr($evergreen_seconds); ?>"
>
  <?php if ($show_decor) : ?>
  <div class="absolute inset-0 bg-gradient-to-b from-gold-200/35 via-transparent to-transparent dark:from-gold-900/15 pointer-events-none"></div>
  <div class="absolute inset-0 opacity-35 dark:opacity-20 bg-[radial-gradient(#C5A880_1px,transparent_1px)] dark:bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:26px_26px] pointer-events-none"></div>
  <div class="absolute -top-24 right-1/4 w-96 h-96 bg-gold-300/25 dark:bg-gold-500/15 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute top-1/2 -left-24 w-80 h-80 bg-amber-300/20 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
  <div class="block absolute top-14 right-8 w-72 h-72 rounded-full border-2 border-gold-600/25 dark:border-gold-500/15 bg-gold-200/10 dark:bg-gold-500/[0.02] pointer-events-none"></div>
  <div class="block absolute top-24 right-16 w-52 h-52 rounded-full border border-dashed border-gold-700/30 dark:border-gold-400/20 pointer-events-none"></div>
  <div class="block absolute bottom-14 left-8 w-80 h-80 rounded-t-[999px] border-2 border-gold-600/20 dark:border-gold-500/12 bg-gold-100/15 dark:bg-transparent pointer-events-none"></div>
  <div class="block absolute top-1/3 left-1/2 -translate-x-1/2 w-[520px] h-[520px] rounded-full border border-gold-600/15 dark:border-gold-500/10 pointer-events-none"></div>
  <?php endif; ?>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

    <div class="kinboni-countdown-hero relative rounded-3xl overflow-hidden bg-stone-950 text-white border border-gold-500/40 p-8 sm:p-12 shadow-2xl">
      <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:18px_18px] pointer-events-none"></div>
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-gold-500/15 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-7 space-y-4">
          <?php if (!empty($badge_text)) : ?>
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-600/20 border border-rose-500/40 text-rose-300 text-xs font-bold uppercase tracking-widest">
            <?php if ($pulse_dot) : ?><span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span><?php endif; ?>
            <span><?php echo esc_html($badge_text); ?></span>
          </span>
          <?php endif; ?>

          <?php if (!empty($banner_title)) : ?>
          <<?php echo $banner_title_tag; ?> class="font-serif text-3xl sm:text-5xl font-medium text-white leading-tight">
            <?php echo esc_html($banner_title); ?>
          </<?php echo $banner_title_tag; ?>>
          <?php endif; ?>

          <?php if (!empty($banner_desc)) : ?>
          <div class="text-stone-300 text-sm sm:text-base font-normal max-w-2xl leading-relaxed">
            <?php echo wp_kses_post(wpautop($banner_desc)); ?>
          </div>
          <?php endif; ?>

          <?php if ($show_promo) : ?>
          <div class="pt-2 flex flex-wrap items-center gap-3">
            <button 
              type="button" 
              class="btn-copy-promo-code px-5 py-3 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest flex items-center gap-2 transition-all active:scale-95 shadow-md"
              data-code="<?php echo esc_attr($promo_code); ?>"
            >
              <i data-lucide="copy" class="w-4 h-4"></i>
              <span><?php echo esc_html($btn_copy); ?></span>
            </button>
            <button 
              type="button" 
              class="btn-apply-promo-voucher px-5 py-3 rounded-xl border border-gold-500/50 text-gold-300 hover:bg-gold-500/10 text-xs font-bold uppercase tracking-widest transition-all active:scale-95"
              data-code="<?php echo esc_attr($promo_code); ?>"
            >
              <?php echo esc_html($btn_apply); ?>
            </button>
          </div>
          <?php endif; ?>
        </div>

        <div class="lg:col-span-5 flex flex-col items-center lg:items-end">
          <?php if (!empty($timer_title)) : ?>
          <span class="text-xs uppercase tracking-[0.25em] text-gold-400 font-bold mb-3"><?php echo esc_html($timer_title); ?></span>
          <?php endif; ?>

          <div class="grid grid-cols-4 gap-2.5 sm:gap-3 text-center">
            <div class="kinboni-timer-box p-3.5 sm:p-4 rounded-2xl bg-stone-900/90 border border-gold-500/30 min-w-[68px]">
              <span class="flash-cd-days kinboni-timer-num font-serif text-2xl sm:text-3xl font-bold text-gold-400 block"><?php echo esc_html(sprintf('%02d', $ev_days)); ?></span>
              <span class="kinboni-timer-label text-[11px] uppercase tracking-widest text-stone-400"><?php echo esc_html($label_days); ?></span>
            </div>
            <div class="kinboni-timer-box p-3.5 sm:p-4 rounded-2xl bg-stone-900/90 border border-gold-500/30 min-w-[68px]">
              <span class="flash-cd-hours kinboni-timer-num font-serif text-2xl sm:text-3xl font-bold text-gold-400 block"><?php echo esc_html(sprintf('%02d', $ev_hours)); ?></span>
              <span class="kinboni-timer-label text-[11px] uppercase tracking-widest text-stone-400"><?php echo esc_html($label_hours); ?></span>
            </div>
            <div class="kinboni-timer-box p-3.5 sm:p-4 rounded-2xl bg-stone-900/90 border border-gold-500/30 min-w-[68px]">
              <span class="flash-cd-mins kinboni-timer-num font-serif text-2xl sm:text-3xl font-bold text-gold-400 block"><?php echo esc_html(sprintf('%02d', $ev_mins)); ?></span>
              <span class="kinboni-timer-label text-[11px] uppercase tracking-widest text-stone-400"><?php echo esc_html($label_mins); ?></span>
            </div>
            <div class="kinboni-timer-box p-3.5 sm:p-4 rounded-2xl bg-stone-900/90 border border-gold-500/30 min-w-[68px]">
              <span class="flash-cd-secs kinboni-timer-num font-serif text-2xl sm:text-3xl font-bold text-white block"><?php echo esc_html(sprintf('%02d', $ev_secs)); ?></span>
              <span class="kinboni-timer-label text-[11px] uppercase tracking-widest text-stone-400"><?php echo esc_html($label_secs); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 mb-6">
        <div>
          <?php if (!empty($grid_badge)) : ?>
          <span class="text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400"><?php echo esc_html($grid_badge); ?></span>
          <?php endif; ?>
          <?php if (!empty($grid_title)) : ?>
          <<?php echo $grid_title_tag; ?> class="font-serif text-2xl sm:text-3xl font-medium text-stone-900 dark:text-white mt-1">
            <?php echo esc_html($grid_title); ?>
          </<?php echo $grid_title_tag; ?>>
          <?php endif; ?>
        </div>
        <?php if (!empty($grid_notice)) : ?>
        <span class="text-xs text-stone-500"><?php echo esc_html($grid_notice); ?></span>
        <?php endif; ?>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
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

            <?php if (!empty($badge) || $discount_pct > 0) : ?>
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

              <button 
                type="button" 
                class="btn-open-quickview w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:bg-gold-400 hover:text-stone-950 hover:border-gold-400 dark:hover:bg-gold-400 dark:hover:text-stone-950 hover:scale-110 active:scale-90 transition-all shadow-md cursor-pointer"
                data-product-id="<?php echo esc_attr($pid); ?>"
                aria-label="<?php echo esc_attr(sprintf(__('Quick View %s', 'kinboni-core'), $pname)); ?>"
                title="<?php esc_attr_e('Quick View', 'kinboni-core'); ?>"
              >
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </div>
          </div>

          <div class="p-4 sm:p-5 flex flex-col flex-grow">
            <div class="flex items-center justify-between gap-2 mb-2 min-w-0">
              <span class="text-xs uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400 truncate">
                <?php echo esc_html($cat_label); ?>
              </span>
              <?php if ($rating > 0) : ?>
              <div class="flex items-center gap-1 text-[11px] font-semibold text-stone-700 dark:text-stone-300 flex-shrink-0" title="<?php echo esc_attr(sprintf(__('%s out of 5 stars from %d verified reviews', 'kinboni-core'), number_format($rating, 1), $reviews)); ?>">
                <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span><?php echo esc_html(number_format($rating, 1)); ?></span>
                <span class="text-stone-400 dark:text-stone-500 font-normal">(<?php echo esc_html($reviews); ?>)</span>
              </div>
              <?php endif; ?>
            </div>

            <h3 class="kinboni-product-card-title font-serif text-xl sm:text-2xl font-bold text-stone-900 dark:text-white leading-snug truncate mb-2 group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors" title="<?php echo esc_attr($pname); ?>">
              <a href="<?php echo esc_url($plink); ?>">
                <?php echo esc_html($pname); ?>
              </a>
            </h3>

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
                <span class="whitespace-nowrap"><?php esc_html_e('Add to Bag', 'kinboni-core'); ?></span>
              </a>
              <?php else : ?>
              <a 
                href="<?php echo esc_url($plink); ?>" 
                class="flex-1 py-2.5 px-3 rounded-xl bg-stone-200/80 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-100 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer" 
                title="<?php esc_attr_e('View Product', 'kinboni-core'); ?>"
              >
                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 flex-shrink-0 text-stone-600 dark:text-stone-300"></i>
                <span class="whitespace-nowrap"><?php esc_html_e('Add to Bag', 'kinboni-core'); ?></span>
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
                <span class="whitespace-nowrap"><?php esc_html_e('Order Now', 'kinboni-core'); ?></span>
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</section>
        <?php
    }
}

