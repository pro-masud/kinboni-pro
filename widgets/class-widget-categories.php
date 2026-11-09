<?php
/**
 * Kinboni Categories Grid - Elementor Widget
 *
 * Implements the luxury editorial "Shop by Category" section featuring
 * ambient architectural glows, customizable header, and a 6-item responsive grid.
 * Allows store admins to manually curate categories with custom images/counts
 * or automatically pull dynamic WooCommerce product categories.
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
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Utils;

class Kinboni_Widget_Categories extends Widget_Base {

    public function get_name() {
        return 'kinboni_categories_grid';
    }

    public function get_title() {
        return esc_html__('Shop by Category Grid', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('category', 'categories', 'grid', 'shop', 'disciplines', 'collections', 'woocommerce', 'kinboni');
    }

    /**
     * Curated luxury fallback images
     */
    private function get_curated_fallback_images() {
        return array(
            'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80',
        );
    }

    /**
     * Fetch all WooCommerce product categories for select options (with none option)
     */
    private function get_all_wc_categories() {
        $options = array('' => esc_html__('— None / Custom URL —', 'kinboni-core'));
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
     * Fetch WooCommerce product categories for Select2 multiselect (clean, without empty string)
     */
    private function get_wc_categories_multiselect() {
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
                'default'     => esc_html__('Curated Disciplines', 'kinboni-core'),
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
                'default'     => esc_html__('Shop by Category', 'kinboni-core'),
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
                'label'        => esc_html__('Show Gold Accent Divider', 'kinboni-core'),
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
                'default'     => esc_html__('Each collection represents years of artisanal devotion, ethically sourced raw elements, and immaculate finishing.', 'kinboni-core'),
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

        $this->add_responsive_control(
            'header_align',
            array(
                'label'     => esc_html__('Header Alignment', 'kinboni-core'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => esc_html__('Left', 'kinboni-core'),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => esc_html__('Center', 'kinboni-core'),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'  => array(
                        'title' => esc_html__('Right', 'kinboni-core'),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default'   => 'center',
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CATEGORIES ITEMS & SOURCE
        // ==========================================
        $this->start_controls_section(
            'section_categories_content',
            array(
                'label' => esc_html__('Categories Items', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'source_type',
            array(
                'label'   => esc_html__('Categories Source', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'wc_auto' => esc_html__('Dynamic WooCommerce Categories (Recommended)', 'kinboni-core'),
                    'manual'  => esc_html__('Custom Repeater (Manual Curated)', 'kinboni-core'),
                ),
                'default' => 'wc_auto',
            )
        );

        // --- Dynamic WooCommerce Categories Controls ---
        $this->add_control(
            'wc_cat_limit',
            array(
                'label'     => esc_html__('Number of Categories to Show', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 30,
                'step'      => 1,
                'default'   => 6,
                'condition' => array('source_type' => 'wc_auto'),
            )
        );

        $this->add_control(
            'wc_cat_select',
            array(
                'label'       => esc_html__('Specific Categories (Optional)', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $this->get_wc_categories_multiselect(),
                'label_block' => true,
                'description' => esc_html__('Leave empty to pull all top-level categories automatically.', 'kinboni-core'),
                'condition'   => array('source_type' => 'wc_auto'),
            )
        );

        $this->add_control(
            'wc_parent_only',
            array(
                'label'        => esc_html__('Top-Level Categories Only', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__('Show only parent categories (hide subcategories) unless specifically chosen above.', 'kinboni-core'),
                'condition'    => array('source_type' => 'wc_auto'),
            )
        );

        $this->add_control(
            'wc_exclude_uncategorized',
            array(
                'label'        => esc_html__('Exclude "Uncategorized"', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__('Hide the default WooCommerce Uncategorized term.', 'kinboni-core'),
                'condition'    => array('source_type' => 'wc_auto'),
            )
        );

        $this->add_control(
            'show_cat_count',
            array(
                'label'        => esc_html__('Show Creation / Product Count', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'wc_orderby',
            array(
                'label'     => esc_html__('Order By', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'name'       => esc_html__('Name', 'kinboni-core'),
                    'count'      => esc_html__('Product Count', 'kinboni-core'),
                    'term_id'    => esc_html__('Category ID', 'kinboni-core'),
                    'menu_order' => esc_html__('Menu Order', 'kinboni-core'),
                ),
                'default'   => 'name',
                'condition' => array('source_type' => 'wc_auto'),
            )
        );

        $this->add_control(
            'wc_order',
            array(
                'label'     => esc_html__('Order', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'ASC'  => esc_html__('Ascending (ASC)', 'kinboni-core'),
                    'DESC' => esc_html__('Descending (DESC)', 'kinboni-core'),
                ),
                'default'   => 'ASC',
                'condition' => array('source_type' => 'wc_auto'),
            )
        );

        $this->add_control(
            'wc_hide_empty',
            array(
                'label'        => esc_html__('Hide Empty Categories', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => array('source_type' => 'wc_auto'),
            )
        );

        $this->add_control(
            'wc_count_suffix',
            array(
                'label'       => esc_html__('Count Suffix', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Creations', 'kinboni-core'),
                'description' => esc_html__('Appended after the number (e.g. "18 Creations")', 'kinboni-core'),
                'condition'   => array('source_type' => 'wc_auto'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'wc_fallback_image',
            array(
                'label'       => esc_html__('Fallback Category Image', 'kinboni-core'),
                'type'        => Controls_Manager::MEDIA,
                'default'     => array(
                    'url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=600&q=80',
                ),
                'description' => esc_html__('Used when a WooCommerce category does not have a thumbnail and no products with images exist.', 'kinboni-core'),
                'condition'   => array('source_type' => 'wc_auto'),
            )
        );

        // --- Manual Repeater Controls ---
        $repeater = new Repeater();

        $repeater->add_control(
            'cat_title',
            array(
                'label'       => esc_html__('Category Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Category Discipline', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'cat_count',
            array(
                'label'       => esc_html__('Caption / Item Count', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('18 Creations', 'kinboni-core'),
                'placeholder' => esc_html__('e.g. 18 Creations, 12 Extraits', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'cat_image',
            array(
                'label'   => esc_html__('Category Image', 'kinboni-core'),
                'type'    => Controls_Manager::MEDIA,
                'default' => array(
                    'url' => Utils::get_placeholder_image_src(),
                ),
            )
        );

        $repeater->add_control(
            'cat_slug',
            array(
                'label'       => esc_html__('Category Slug / Filter Key', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'category',
                'description' => esc_html__('Used for data-category attribute and JS filter matching (e.g. timepieces, leather, fragrance).', 'kinboni-core'),
            )
        );

        $repeater->add_control(
            'wc_sync_term',
            array(
                'label'       => esc_html__('Sync with WooCommerce Category (Auto Link)', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT,
                'options'     => $this->get_all_wc_categories(),
                'default'     => '',
                'description' => esc_html__('If selected, the card will automatically link to this category archive page.', 'kinboni-core'),
            )
        );

        $repeater->add_control(
            'custom_link',
            array(
                'label'       => esc_html__('Custom Link (If not synced)', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-domain.com/shop', 'kinboni-core'),
                'default'     => array(
                    'url'         => '#',
                    'is_external' => false,
                    'nofollow'    => false,
                ),
                'condition'   => array('wc_sync_term' => ''),
            )
        );

        $this->add_control(
            'categories_list',
            array(
                'label'       => esc_html__('Category Cards', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ cat_title }}}',
                'default'     => array(
                    array(
                        'cat_title'    => esc_html__('Timepieces', 'kinboni-core'),
                        'cat_count'    => esc_html__('18 Creations', 'kinboni-core'),
                        'cat_image'    => array('url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=600&q=80'),
                        'cat_slug'     => 'timepieces',
                        'wc_sync_term' => '',
                        'custom_link'  => array('url' => '#'),
                    ),
                    array(
                        'cat_title'    => esc_html__('Leather Goods', 'kinboni-core'),
                        'cat_count'    => esc_html__('24 Creations', 'kinboni-core'),
                        'cat_image'    => array('url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80'),
                        'cat_slug'     => 'leather',
                        'wc_sync_term' => '',
                        'custom_link'  => array('url' => '#'),
                    ),
                    array(
                        'cat_title'    => esc_html__('Fine Fragrance', 'kinboni-core'),
                        'cat_count'    => esc_html__('12 Extraits', 'kinboni-core'),
                        'cat_image'    => array('url' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80'),
                        'cat_slug'     => 'fragrance',
                        'wc_sync_term' => '',
                        'custom_link'  => array('url' => '#'),
                    ),
                    array(
                        'cat_title'    => esc_html__('Fine Jewelry', 'kinboni-core'),
                        'cat_count'    => esc_html__('15 Pieces', 'kinboni-core'),
                        'cat_image'    => array('url' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80'),
                        'cat_slug'     => 'jewelry',
                        'wc_sync_term' => '',
                        'custom_link'  => array('url' => '#'),
                    ),
                    array(
                        'cat_title'    => esc_html__('Eyewear', 'kinboni-core'),
                        'cat_count'    => esc_html__('10 Models', 'kinboni-core'),
                        'cat_image'    => array('url' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=600&q=80'),
                        'cat_slug'     => 'eyewear',
                        'wc_sync_term' => '',
                        'custom_link'  => array('url' => '#'),
                    ),
                    array(
                        'cat_title'    => esc_html__('Silk & Cashmere', 'kinboni-core'),
                        'cat_count'    => esc_html__('14 Weaves', 'kinboni-core'),
                        'cat_image'    => array('url' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=600&q=80'),
                        'cat_slug'     => 'silk',
                        'wc_sync_term' => '',
                        'custom_link'  => array('url' => '#'),
                    ),
                ),
                'condition'   => array('source_type' => 'manual'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: GRID & CARD SETTINGS
        // ==========================================
        $this->start_controls_section(
            'section_grid_settings',
            array(
                'label' => esc_html__('Grid & Card Settings', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'cols_desktop',
            array(
                'label'   => esc_html__('Desktop Columns', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'lg:grid-cols-6' => esc_html__('6 Columns (Default Luxury)', 'kinboni-core'),
                    'lg:grid-cols-4' => esc_html__('4 Columns', 'kinboni-core'),
                    'lg:grid-cols-3' => esc_html__('3 Columns', 'kinboni-core'),
                    'lg:grid-cols-2' => esc_html__('2 Columns', 'kinboni-core'),
                ),
                'default' => 'lg:grid-cols-6',
            )
        );

        $this->add_control(
            'cols_tablet',
            array(
                'label'   => esc_html__('Tablet Columns', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'md:grid-cols-4' => esc_html__('4 Columns', 'kinboni-core'),
                    'md:grid-cols-3' => esc_html__('3 Columns (Default)', 'kinboni-core'),
                    'md:grid-cols-2' => esc_html__('2 Columns', 'kinboni-core'),
                ),
                'default' => 'md:grid-cols-3',
            )
        );

        $this->add_control(
            'cols_mobile',
            array(
                'label'   => esc_html__('Mobile Columns', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'grid-cols-2' => esc_html__('2 Columns (Default)', 'kinboni-core'),
                    'grid-cols-1' => esc_html__('1 Column', 'kinboni-core'),
                ),
                'default' => 'grid-cols-2',
            )
        );

        $this->add_control(
            'card_aspect_ratio',
            array(
                'label'   => esc_html__('Card Aspect Ratio', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'aspect-[3/4]' => esc_html__('Portrait 3:4 (Default Prototype)', 'kinboni-core'),
                    'aspect-square' => esc_html__('Square 1:1', 'kinboni-core'),
                    'aspect-[4/5]' => esc_html__('Portrait 4:5', 'kinboni-core'),
                    'aspect-[2/3]' => esc_html__('Tall 2:3', 'kinboni-core'),
                ),
                'default' => 'aspect-[3/4]',
            )
        );

        $this->add_control(
            'card_border_radius',
            array(
                'label'   => esc_html__('Card Border Radius', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'rounded-2xl' => esc_html__('Curved 2XL (Default)', 'kinboni-core'),
                    'rounded-xl'  => esc_html__('Rounded XL', 'kinboni-core'),
                    'rounded-3xl' => esc_html__('Rounded 3XL', 'kinboni-core'),
                    'rounded-lg'  => esc_html__('Subtle LG', 'kinboni-core'),
                    'rounded-none'=> esc_html__('Sharp Square', 'kinboni-core'),
                ),
                'default' => 'rounded-2xl',
            )
        );

        $this->add_control(
            'enable_image_zoom',
            array(
                'label'        => esc_html__('Enable Hover Image Zoom', 'kinboni-core'),
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
                'label'   => esc_html__('Card Title Tag', 'kinboni-core'),
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

        $this->add_control(
            'card_count_tag',
            array(
                'label'   => esc_html__('Card Count Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'p'    => 'p',
                    'span' => 'span',
                    'div'  => 'div',
                ),
                'default' => 'p',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: DECORATIONS & ARCHITECTURE
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
                'default'     => 'categories',
                'placeholder' => esc_html__('categories', 'kinboni-core'),
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
        // STYLE TAB: SECTION & BACKGROUND
        // ==========================================
        $this->start_controls_section(
            'section_style_general',
            array(
                'label' => esc_html__('Section Background & Padding', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'section_padding',
            array(
                'label'      => esc_html__('Padding', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-categories-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Custom Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-categories-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: HEADER TYPOGRAPHY & COLORS
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
            'heading_badge_style',
            array(
                'label'     => esc_html__('Subtitle / Badge', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'badge_color',
            array(
                'label'     => esc_html__('Subtitle Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-cat-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-cat-badge',
            )
        );

        $this->add_control(
            'heading_title_style',
            array(
                'label'     => esc_html__('Main Title', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-cat-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-cat-title',
            )
        );

        $this->add_control(
            'heading_divider_style',
            array(
                'label'     => esc_html__('Gold Line Divider', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'divider_color',
            array(
                'label'     => esc_html__('Divider Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-cat-divider' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'divider_width',
            array(
                'label'      => esc_html__('Divider Width', 'kinboni-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'rem', '%'),
                'range'      => array(
                    'px' => array('min' => 10, 'max' => 200),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-cat-divider' => 'width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'heading_desc_style',
            array(
                'label'     => esc_html__('Description', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-cat-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .kinboni-cat-desc',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: CATEGORY CARDS
        // ==========================================
        $this->start_controls_section(
            'section_style_cards',
            array(
                'label' => esc_html__('Category Cards', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_item_title_heading',
            array(
                'label' => esc_html__('Card Title', 'kinboni-core'),
                'type'  => Controls_Manager::HEADING,
            )
        );

        $this->add_control(
            'card_title_color',
            array(
                'label'     => esc_html__('Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-card-heading' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_title_hover_color',
            array(
                'label'     => esc_html__('Title Hover Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .category-card-trigger:hover .kinboni-card-heading' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'card_title_typography',
                'selector' => '{{WRAPPER}} .kinboni-card-heading',
            )
        );

        $this->add_control(
            'card_item_count_heading',
            array(
                'label'     => esc_html__('Card Caption / Count', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'card_count_color',
            array(
                'label'     => esc_html__('Caption Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-card-count' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'card_count_typography',
                'selector' => '{{WRAPPER}} .kinboni-card-count',
            )
        );

        $this->add_control(
            'card_border_heading',
            array(
                'label'     => esc_html__('Card Border & Shadow', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .category-card-trigger',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Gather Category Items according to settings
     */
    private function get_render_categories($settings) {
        $items = array();

        $source_type = !empty($settings['source_type']) ? $settings['source_type'] : 'wc_auto';

        // Check if manual repeater was untouched (default dummy items)
        $is_untouched_dummy = false;
        if ('manual' === $source_type && !empty($settings['categories_list']) && is_array($settings['categories_list'])) {
            if (count($settings['categories_list']) === 6 && isset($settings['categories_list'][0]['cat_title']) && $settings['categories_list'][0]['cat_title'] === 'Timepieces' && empty($settings['categories_list'][0]['wc_sync_term']) && (empty($settings['categories_list'][0]['custom_link']['url']) || $settings['categories_list'][0]['custom_link']['url'] === '#')) {
                $is_untouched_dummy = true;
            }
        }

        // Pull dynamic WooCommerce categories if source is wc_auto OR untouched dummy
        if (('wc_auto' === $source_type || $is_untouched_dummy) && class_exists('WooCommerce')) {
            $limit      = !empty($settings['wc_cat_limit']) ? intval($settings['wc_cat_limit']) : 6;
            $orderby    = !empty($settings['wc_orderby']) ? sanitize_text_field($settings['wc_orderby']) : 'name';
            $order      = !empty($settings['wc_order']) ? sanitize_text_field($settings['wc_order']) : 'ASC';
            $hide_empty = (!empty($settings['wc_hide_empty']) && 'yes' === $settings['wc_hide_empty']);
            $suffix     = !empty($settings['wc_count_suffix']) ? $settings['wc_count_suffix'] : esc_html__('Creations', 'kinboni-core');
            $show_count = (!isset($settings['show_cat_count']) || 'yes' === $settings['show_cat_count']);

            $args = array(
                'taxonomy'   => 'product_cat',
                'number'     => $limit,
                'orderby'    => $orderby,
                'order'      => $order,
                'hide_empty' => $hide_empty,
            );

            // Handle specific categories selection
            $selected_slugs = array();
            if (!empty($settings['wc_cat_select']) && is_array($settings['wc_cat_select'])) {
                $selected_slugs = array_values(array_filter($settings['wc_cat_select']));
            }

            if (!empty($selected_slugs)) {
                $args['slug'] = $selected_slugs;
            } else {
                // Top-level categories only
                if (!isset($settings['wc_parent_only']) || 'yes' === $settings['wc_parent_only']) {
                    $args['parent'] = 0;
                }

                // Exclude default "Uncategorized" category
                if (!isset($settings['wc_exclude_uncategorized']) || 'yes' === $settings['wc_exclude_uncategorized']) {
                    $default_cat_id = (int) get_option('default_product_cat');
                    $exclude_ids = array();
                    if ($default_cat_id) {
                        $exclude_ids[] = $default_cat_id;
                    }
                    $uncat_term = get_term_by('slug', 'uncategorized', 'product_cat');
                    if ($uncat_term && !is_wp_error($uncat_term)) {
                        $exclude_ids[] = $uncat_term->term_id;
                    }
                    if (!empty($exclude_ids)) {
                        $args['exclude'] = array_unique($exclude_ids);
                    }
                }
            }

            $terms = get_terms($args);

            // Fallback: If parent=0 returned nothing, try without parent=0
            if ((empty($terms) || is_wp_error($terms)) && empty($selected_slugs)) {
                unset($args['parent']);
                $terms = get_terms($args);
            }

            if (!empty($terms) && !is_wp_error($terms)) {
                $curated_fallbacks = $this->get_curated_fallback_images();
                $idx = 0;

                foreach ($terms as $term) {
                    // Check WooCommerce category thumbnail
                    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    $image_url    = $thumbnail_id ? (wp_get_attachment_image_url($thumbnail_id, 'kinboni-product-card') ?: wp_get_attachment_image_url($thumbnail_id, 'large')) : '';

                    // If no category thumbnail, automatically pull image from latest product in this category
                    if (empty($image_url) && function_exists('wc_get_products')) {
                        $cat_products = wc_get_products(array(
                            'category' => array($term->slug),
                            'limit'    => 1,
                            'status'   => 'publish',
                            'orderby'  => 'date',
                            'order'    => 'DESC',
                        ));
                        if (!empty($cat_products)) {
                            $image_url = class_exists('Kinboni_Core') ? Kinboni_Core::get_product_card_image($cat_products[0], 'kinboni-product-card') : (wp_get_attachment_image_url($cat_products[0]->get_image_id(), 'kinboni-product-card') ?: '');
                        }
                    }

                    // Fallback to widget custom fallback image or curated luxury image
                    if (empty($image_url)) {
                        if (!empty($settings['wc_fallback_image']['url'])) {
                            $image_url = $settings['wc_fallback_image']['url'];
                        } else {
                            $image_url = $curated_fallbacks[$idx % count($curated_fallbacks)];
                        }
                    }

                    $term_link = get_term_link($term, 'product_cat');
                    if (is_wp_error($term_link)) {
                        $term_link = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
                    }

                    $count_label = $show_count ? sprintf('%d %s', $term->count, $suffix) : '';

                    $items[] = array(
                        'title'       => $term->name,
                        'count'       => $count_label,
                        'image_url'   => $image_url,
                        'slug'        => $term->slug,
                        'link_url'    => $term_link,
                        'is_external' => false,
                        'nofollow'    => false,
                    );

                    $idx++;
                }
            }
        }

        // If items are still empty (e.g. manual mode or WooCommerce has no categories yet)
        if (empty($items)) {
            if (!empty($settings['categories_list']) && is_array($settings['categories_list'])) {
                foreach ($settings['categories_list'] as $item) {
                    $title     = !empty($item['cat_title']) ? $item['cat_title'] : '';
                    $count     = !empty($item['cat_count']) ? $item['cat_count'] : '';
                    $slug      = !empty($item['cat_slug']) ? sanitize_title($item['cat_slug']) : sanitize_title($title);
                    $image_url = !empty($item['cat_image']['url']) ? $item['cat_image']['url'] : 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=600&q=80';

                    $link_url    = '#';
                    $is_external = false;
                    $nofollow    = false;

                    // If synced to a WooCommerce term
                    if (!empty($item['wc_sync_term']) && taxonomy_exists('product_cat')) {
                        $term = get_term_by('slug', $item['wc_sync_term'], 'product_cat');
                        if ($term && !is_wp_error($term)) {
                            $term_link = get_term_link($term, 'product_cat');
                            if (!is_wp_error($term_link)) {
                                $link_url = $term_link;
                            }
                            if (empty($slug)) {
                                $slug = $term->slug;
                            }
                        }
                    } elseif (!empty($item['custom_link']['url'])) {
                        $link_url    = $item['custom_link']['url'];
                        $is_external = !empty($item['custom_link']['is_external']);
                        $nofollow    = !empty($item['custom_link']['nofollow']);
                    }

                    $items[] = array(
                        'title'       => $title,
                        'count'       => $count,
                        'image_url'   => $image_url,
                        'slug'        => $slug,
                        'link_url'    => $link_url,
                        'is_external' => $is_external,
                        'nofollow'    => $nofollow,
                    );
                }
            }
        }

        return $items;
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'categories';
        $categories = $this->get_render_categories($settings);
        if (empty($categories)) {
            return;
        }

        $badge_tag = in_array($settings['badge_tag'], array('span', 'p', 'div', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4', 'div'), true) ? $settings['title_tag'] : 'h2';
        $desc_tag  = in_array($settings['desc_tag'], array('p', 'div', 'span'), true) ? $settings['desc_tag'] : 'p';

        $badge_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'categories_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $title_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'categories_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');

        $card_title_tag = in_array($settings['card_title_tag'], array('h2', 'h3', 'h4', 'h5', 'div'), true) ? $settings['card_title_tag'] : 'h3';
        $card_count_tag = in_array($settings['card_count_tag'], array('p', 'span', 'div'), true) ? $settings['card_count_tag'] : 'p';

        $cols_desktop = !empty($settings['cols_desktop']) ? $settings['cols_desktop'] : 'lg:grid-cols-6';
        $cols_tablet  = !empty($settings['cols_tablet']) ? $settings['cols_tablet'] : 'md:grid-cols-3';
        $cols_mobile  = !empty($settings['cols_mobile']) ? $settings['cols_mobile'] : 'grid-cols-2';

        $aspect_ratio  = !empty($settings['card_aspect_ratio']) ? $settings['card_aspect_ratio'] : 'aspect-[3/4]';
        $border_radius = !empty($settings['card_border_radius']) ? $settings['card_border_radius'] : 'rounded-2xl';
        $enable_zoom   = ('yes' === $settings['enable_image_zoom']);

        $header_align = !empty($settings['header_align']) ? $settings['header_align'] : 'center';
        $align_class  = 'text-center';
        $margin_auto  = 'mx-auto';
        if ('left' === $header_align) {
            $align_class = 'text-left';
            $margin_auto = 'mr-auto';
        } elseif ('right' === $header_align) {
            $align_class = 'text-right';
            $margin_auto = 'ml-auto';
        }
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-categories-section relative overflow-hidden py-16 sm:py-24 bg-stone-100/60 dark:bg-stone-900/40 border-t border-b border-stone-200/70 dark:border-stone-800/80 scroll-mt-24">
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
    <div class="<?php echo esc_attr($align_class); ?> max-w-2xl <?php echo esc_attr($margin_auto); ?> mb-12 sm:mb-16">
      <?php if (!empty($badge_text)) : ?>
      <<?php echo $badge_tag; ?> class="kinboni-cat-badge text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400 inline-block">
        <?php echo esc_html($badge_text); ?>
      </<?php echo $badge_tag; ?>>
      <?php endif; ?>

      <?php if (!empty($title_text)) : ?>
      <<?php echo $title_tag; ?> class="kinboni-cat-title font-serif text-3xl sm:text-4xl font-medium text-stone-900 dark:text-white mt-1">
        <?php echo esc_html($title_text); ?>
      </<?php echo $title_tag; ?>>
      <?php endif; ?>

      <?php if ('yes' === $settings['show_divider']) : ?>
      <div class="kinboni-cat-divider w-12 h-0.5 bg-gold-500 <?php echo esc_attr($margin_auto); ?> mt-3 mb-4"></div>
      <?php endif; ?>

      <?php if (!empty($settings['description_text'])) : ?>
      <<?php echo $desc_tag; ?> class="kinboni-cat-desc text-sm text-stone-600 dark:text-stone-400 font-normal">
        <?php echo esc_html($settings['description_text']); ?>
      </<?php echo $desc_tag; ?>>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($categories)) : ?>
    <div class="grid <?php echo esc_attr($cols_mobile); ?> <?php echo esc_attr($cols_tablet); ?> <?php echo esc_attr($cols_desktop); ?> gap-4 sm:gap-6">
      <?php foreach ($categories as $cat) :
        $img_zoom_class = $enable_zoom ? 'group-hover:scale-110 transition-transform duration-700 ease-out' : '';
        $target_attr    = !empty($cat['is_external']) ? ' target="_blank"' : '';
        $rel_attr       = !empty($cat['nofollow']) ? ' rel="nofollow noopener noreferrer"' : (!empty($cat['is_external']) ? ' rel="noopener noreferrer"' : '');
      ?>
      <a href="<?php echo esc_url($cat['link_url']); ?>" class="category-card-trigger group relative <?php echo esc_attr($border_radius); ?> overflow-hidden <?php echo esc_attr($aspect_ratio); ?> cursor-pointer shadow-sm hover:shadow-xl transition-all duration-500 border border-stone-200/80 dark:border-stone-800 block" data-category="<?php echo esc_attr($cat['slug']); ?>"<?php echo $target_attr . $rel_attr; ?>>
        <img loading="lazy" src="<?php echo esc_url($cat['image_url']); ?>" 
          alt="<?php echo esc_attr($cat['title']); ?>" 
          class="w-full h-full object-cover object-center <?php echo esc_attr($img_zoom_class); ?>" 
          loading="lazy"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/90 via-stone-950/30 to-transparent"></div>
        <div class="absolute inset-x-3 bottom-4 text-center">
          <<?php echo $card_title_tag; ?> class="kinboni-card-heading font-serif text-lg sm:text-xl font-medium text-white group-hover:text-gold-400 transition-colors">
            <?php echo esc_html($cat['title']); ?>
          </<?php echo $card_title_tag; ?>>
          <?php if (!empty($cat['count'])) : ?>
          <<?php echo $card_count_tag; ?> class="kinboni-card-count text-sm uppercase tracking-wider text-stone-300 mt-0.5">
            <?php echo esc_html($cat['count']); ?>
          </<?php echo $card_count_tag; ?>>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php elseif (\Elementor\Plugin::$instance->editor->is_edit_mode()) : ?>
    <div class="p-8 text-center text-xs text-amber-800 dark:text-amber-300 bg-amber-500/10 border border-amber-500/30 rounded-2xl max-w-lg mx-auto">
      <?php esc_html_e('No product categories found in WooCommerce. Please add categories under Products > Categories in WP Admin, or switch source to Custom Repeater.', 'kinboni-core'); ?>
    </div>
    <?php endif; ?>
  </div>
</section>
        <?php
    }
}

