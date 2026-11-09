<?php
/**
 * Kinboni Shop Catalogue & Archive Showcase - Elementor Widget
 *
 * Implements the full luxury Shop Catalogue page experience matching shop.html 100%:
 * 1. Ambient Page Glows & Geometric Arch Shapes
 * 2. Breadcrumb & Archive Header (Subtitle, Serif Title, Narrative Description)
 * 3. Left Sidebar Filter Widgets:
 *    - Live Search Catalogue with clear button
 *    - Dynamic WooCommerce Product Categories list with count badges
 *    - Interactive Price Range slider with live currency display
 *    - Archive Privileges checkboxes (On Private Sale Only, Patron Best Sellers)
 *    - Reset All Filters button
 * 4. Main Catalogue Area:
 *    - Toolbar (Result count, Sort by dropdown, Grid / List view mode switcher)
 *    - Quick Category Filter Pills bar (horizontal scrolling pills with counts)
 *    - Removable Active Filter Chips
 *    - Dynamic Products Grid / List Loop with Luxury Cards (Add to Bag, Fast COD, Quick View, Wishlist)
 *    - Dynamic Pagination with per-page switcher (12/Page, 6/Page) and page navigation
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

class Kinboni_Widget_Shop extends Widget_Base {

    public function get_name() {
        return 'kinboni_shop';
    }

    public function get_title() {
        return esc_html__('Shop Catalogue & Archive Showcase', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-archive-posts';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('shop', 'catalogue', 'archive', 'products', 'filter', 'woocommerce', 'atelier', 'kinboni');
    }

    /**
     * Fetch all WooCommerce product categories
     */
    private function get_all_wc_categories() {
        $options = array('all' => esc_html__('All Categories (Default)', 'kinboni-core'));
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
        // CONTENT TAB: ARCHIVE HEADER
        // ==========================================
        $this->start_controls_section(
            'section_archive_header',
            array(
                'label' => esc_html__('Archive Header & Breadcrumb', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_breadcrumb',
            array(
                'label'        => esc_html__('Show Breadcrumb', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'home_label',
            array(
                'label'     => esc_html__('Home Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Home', 'kinboni-core'),
                'condition' => array('show_breadcrumb' => 'yes'),
            )
        );

        $this->add_control(
            'breadcrumb_current',
            array(
                'label'     => esc_html__('Current Breadcrumb Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Shop Archive', 'kinboni-core'),
                'condition' => array('show_breadcrumb' => 'yes'),
            )
        );

        $this->add_control(
            'archive_subtitle',
            array(
                'label'   => esc_html__('Archive Subtitle', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('The Complete Archive', 'kinboni-core'),
            )
        );

        $this->add_control(
            'archive_title',
            array(
                'label'   => esc_html__('Archive Title', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('All Atelier Creations', 'kinboni-core'),
            )
        );

        $this->add_control(
            'archive_description',
            array(
                'label'   => esc_html__('Archive Description', 'kinboni-core'),
                'type'    => Controls_Manager::TEXTAREA,
                'default' => esc_html__('Every creation is handcrafted in limited batches, individually serial-numbered, and accompanied by a signed certificate of authenticity.', 'kinboni-core'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: SIDEBAR FILTERS
        // ==========================================
        $this->start_controls_section(
            'section_sidebar_filters',
            array(
                'label' => esc_html__('Sidebar Filter Widgets', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_sidebar',
            array(
                'label'        => esc_html__('Show Filter Sidebar', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_search_filter',
            array(
                'label'        => esc_html__('Show Search Catalogue Widget', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_sidebar' => 'yes'),
            )
        );

        $this->add_control(
            'show_categories_filter',
            array(
                'label'        => esc_html__('Show Product Categories Widget', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_sidebar' => 'yes'),
            )
        );

        $this->add_control(
            'show_price_filter',
            array(
                'label'        => esc_html__('Show Price Filter Range Slider', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_sidebar' => 'yes'),
            )
        );

        $this->add_control(
            'show_privileges_filter',
            array(
                'label'        => esc_html__('Show Archive Privileges (Sale/Bestseller)', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_sidebar' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: TOOLBAR & CATALOGUE
        // ==========================================
        $this->start_controls_section(
            'section_catalogue_settings',
            array(
                'label' => esc_html__('Toolbar & Catalogue Settings', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_quick_pills',
            array(
                'label'        => esc_html__('Show Top Category Pills Bar', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_view_mode_toggle',
            array(
                'label'        => esc_html__('Show Grid / List View Toggle', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'default_view_mode',
            array(
                'label'   => esc_html__('Default View Mode', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => array(
                    'grid' => esc_html__('Grid View (3 Columns)', 'kinboni-core'),
                    'list' => esc_html__('List View (Horizontal Cards)', 'kinboni-core'),
                ),
            )
        );

        $this->add_control(
            'per_page',
            array(
                'label'   => esc_html__('Initial Products Per Page', 'kinboni-core'),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 3,
                'max'     => 36,
                'step'    => 3,
                'default' => 12,
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CARDS & BUTTONS
        // ==========================================
        $this->start_controls_section(
            'section_card_buttons',
            array(
                'label' => esc_html__('Product Card Buttons', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_badges',
            array(
                'label'        => esc_html__('Show Badges (Sale / Featured)', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
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
            'show_quickview',
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
            'btn_cart_text',
            array(
                'label'   => esc_html__('Add to Bag Button Text', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Add to Bag', 'kinboni-core'),
            )
        );

        $this->add_control(
            'btn_cod_text',
            array(
                'label'   => esc_html__('Order COD Button Text', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Order Now', 'kinboni-core'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: AMBIENT GLOW & ARCHITECTURAL SHAPES
        // ==========================================
        $this->start_controls_section(
            'section_style_ambient',
            array(
                'label' => esc_html__('Ambient Glow & Architectural Shapes', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'show_ambient_glow',
            array(
                'label'        => esc_html__('Show Ambient Glow', 'kinboni-core'),
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
                'label'        => esc_html__('Show Geometric Arch Shapes', 'kinboni-core'),
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
                'label' => esc_html__('Typography', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'archive_title_typography',
                'label'    => esc_html__('Archive Title Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} #shop-archive-title',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Fetch products and categories from WooCommerce
     */
    private function get_shop_data() {
        $products_data = array();
        $categories_data = array();
        $max_price_found = 30000;
        $min_price_found = 1000;

        if (!class_exists('WooCommerce')) {
            return array(
                'products'   => $products_data,
                'categories' => $categories_data,
                'max_price'  => $max_price_found,
                'min_price'  => $min_price_found,
            );
        }

        // Query all published products
        $wc_products = wc_get_products(array(
            'limit'   => 150,
            'status'  => 'publish',
            'orderby' => 'menu_order',
            'order'   => 'ASC',
        ));

        $cat_counts = array();

        foreach ($wc_products as $p) {
            $pid       = $p->get_id();
            $price     = (float) $p->get_price();
            $reg_price = (float) $p->get_regular_price();
            $is_sale   = $p->is_on_sale();
            $is_feat   = $p->is_featured();

            if ($price > $max_price_found) {
                $max_price_found = ceil($price / 1000) * 1000;
            }
            if ($price > 0 && $price < $min_price_found) {
                $min_price_found = floor($price / 1000) * 1000;
            }

            // Categories
            $terms = get_the_terms($pid, 'product_cat');
            $cat_slug = 'uncategorized';
            $cat_label = esc_html__('Atelier Collection', 'kinboni-core');

            if ($terms && !is_wp_error($terms)) {
                $first_term = $terms[0];
                $cat_slug = $first_term->slug;
                $cat_label = $first_term->name;

                foreach ($terms as $t) {
                    if (!isset($cat_counts[$t->slug])) {
                        $cat_counts[$t->slug] = array(
                            'id'    => $t->slug,
                            'label' => $t->name,
                            'count' => 0,
                        );
                    }
                    $cat_counts[$t->slug]['count']++;
                }
            }

            // Images
            $main_img  = class_exists('Kinboni_Core') ? Kinboni_Core::get_product_card_image($p, 'kinboni-product-card') : (wp_get_attachment_image_url($p->get_image_id(), 'kinboni-product-card') ?: wc_placeholder_img_src('kinboni-product-card'));
            $hover_img = class_exists('Kinboni_Core') ? Kinboni_Core::get_product_hover_image($p, $main_img, 'kinboni-product-card') : $main_img;

            // Specs
            $specs = array();
            $attrs = $p->get_attributes();
            if (!empty($attrs)) {
                foreach ($attrs as $attr) {
                    if ($attr->is_taxonomy()) {
                        $term_vals = wc_get_product_terms($pid, $attr->get_name(), array('fields' => 'names'));
                        if (!empty($term_vals)) {
                            $specs[] = $attr->get_taxonomy_object()->attribute_label . ': ' . implode(', ', $term_vals);
                        }
                    } else {
                        $opt_vals = $attr->get_options();
                        if (!empty($opt_vals)) {
                            $specs[] = $attr->get_name() . ': ' . implode(', ', $opt_vals);
                        }
                    }
                }
            }
            if (empty($specs)) {
                $specs = array(
                    esc_html__('Handcrafted Limited Batch', 'kinboni-core'),
                    esc_html__('Individually Serial-Numbered', 'kinboni-core'),
                    esc_html__('Certificate of Authenticity', 'kinboni-core')
                );
            }

            $badge = $is_feat ? 'SIGNATURE' : ($is_sale ? 'PRIVILEGE' : '');

            $short_desc = $p->get_short_description();
            if (empty($short_desc)) {
                $short_desc = wp_trim_words($p->get_description(), 22);
            } else {
                $short_desc = wp_strip_all_tags($short_desc);
            }

            $products_data[] = array(
                'id'            => $pid,
                'name'          => $p->get_name(),
                'price'         => $price,
                'originalPrice' => ($is_sale && $reg_price > $price) ? $reg_price : 0,
                'category'      => $cat_slug,
                'categoryLabel' => $cat_label,
                'image'         => $main_img,
                'hoverImage'    => $hover_img,
                'rating'        => (float) $p->get_average_rating(),
                'reviewsCount'  => (int) $p->get_review_count(),
                'description'   => $short_desc,
                'specs'         => $specs,
                'badge'         => $badge,
                'onSale'        => $is_sale,
                'bestSeller'    => ($p->get_total_sales() > 0 || $is_feat),
                'link'          => $p->get_permalink(),
                'add_to_cart_url'=> $p->add_to_cart_url() ?: $p->get_permalink(),
            );
        }

        // Build Categories list with "All Creations" first
        $categories_data[] = array(
            'id'    => 'all',
            'label' => esc_html__('All Creations', 'kinboni-core'),
            'count' => count($products_data),
        );

        foreach ($cat_counts as $slug => $c_data) {
            $categories_data[] = $c_data;
        }

        return array(
            'products'   => $products_data,
            'categories' => $categories_data,
            'max_price'  => $max_price_found,
            'min_price'  => $min_price_found,
        );
    }

    /**
     * Render the Widget Output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $shop_data = $this->get_shop_data();

        $products   = $shop_data['products'];
        $categories = $shop_data['categories'];
        $max_price  = $shop_data['max_price'];
        $min_price  = $shop_data['min_price'];

        $show_glow     = ('yes' === $settings['show_ambient_glow']);
        $show_shapes   = ('yes' === $settings['show_arch_shapes']);
        $show_crumbs   = ('yes' === $settings['show_breadcrumb']);
        $show_sidebar  = ('yes' === $settings['show_sidebar']);
        $show_search   = ('yes' === $settings['show_search_filter']);
        $show_cats     = ('yes' === $settings['show_categories_filter']);
        $show_price    = ('yes' === $settings['show_price_filter']);
        $show_priv     = ('yes' === $settings['show_privileges_filter']);
        $show_pills    = ('yes' === $settings['show_quick_pills']);
        $show_toggle   = ('yes' === $settings['show_view_mode_toggle']);
        $show_badges   = ('yes' === $settings['show_badges']);
        $show_wishlist = ('yes' === $settings['show_wishlist']);
        $show_quickview= ('yes' === $settings['show_quickview']);
        $btn_cart_text = !empty($settings['btn_cart_text']) ? $settings['btn_cart_text'] : __('Add to Bag', 'kinboni-core');
        $btn_cod_text  = !empty($settings['btn_cod_text']) ? $settings['btn_cod_text'] : __('Order Now', 'kinboni-core');
        $per_page      = !empty($settings['per_page']) ? absint($settings['per_page']) : 12;

        $archive_subtitle = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'archive_subtitle', 'shop_badge', '', $this) : (!empty($settings['archive_subtitle']) ? $settings['archive_subtitle'] : '');
        $archive_title    = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'archive_title', 'shop_title', __('All Atelier Creations', 'kinboni-core'), $this) : (!empty($settings['archive_title']) ? $settings['archive_title'] : __('All Atelier Creations', 'kinboni-core'));
        $default_view  = !empty($settings['default_view_mode']) ? $settings['default_view_mode'] : 'grid';

        $total_products = count($products);
        ?>
        <main id="page-view-shop" class="wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]" data-per-page="<?php echo esc_attr($per_page); ?>" data-default-view="<?php echo esc_attr($default_view); ?>">

          <?php if ($show_glow) : ?>
          <div class="absolute inset-0 bg-gradient-to-b from-gold-200/35 via-transparent to-transparent dark:from-gold-900/15 pointer-events-none"></div>
          <div class="absolute inset-0 opacity-35 dark:opacity-20 bg-[radial-gradient(#C5A880_1px,transparent_1px)] dark:bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:26px_26px] pointer-events-none"></div>
          <div class="absolute -top-24 right-1/4 w-96 h-96 bg-gold-300/25 dark:bg-gold-500/15 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute top-1/2 -left-24 w-80 h-80 bg-amber-300/20 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
          <?php endif; ?>

          <?php if ($show_shapes) : ?>
          <div class="block absolute top-14 right-8 w-72 h-72 rounded-full border-2 border-gold-600/25 dark:border-gold-500/15 bg-gold-200/10 dark:bg-gold-500/[0.02] pointer-events-none"></div>
          <div class="block absolute top-24 right-16 w-52 h-52 rounded-full border border-dashed border-gold-700/30 dark:border-gold-400/20 pointer-events-none"></div>
          <div class="block absolute bottom-14 left-8 w-80 h-80 rounded-t-[999px] border-2 border-gold-600/20 dark:border-gold-500/12 bg-gold-100/15 dark:bg-transparent pointer-events-none"></div>
          <div class="block absolute top-1/3 left-1/2 -translate-x-1/2 w-[520px] h-[520px] rounded-full border border-gold-600/15 dark:border-gold-500/10 pointer-events-none"></div>
          <?php endif; ?>

          <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8 pb-6 border-b border-stone-200 dark:border-stone-800 flex flex-col md:flex-row md:items-end justify-between gap-4">
              <div>
                <?php if ($show_crumbs) : ?>
                <nav class="woocommerce-breadcrumb flex items-center gap-2 text-xs text-stone-600 dark:text-stone-300 mb-3" aria-label="Breadcrumb">
                  <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                    <?php echo esc_html(!empty($settings['home_label']) ? $settings['home_label'] : __('Home', 'kinboni-core')); ?>
                  </a>
                  <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                  <span id="shop-breadcrumb-current" class="text-stone-900 dark:text-white font-semibold">
                    <?php echo esc_html(!empty($settings['breadcrumb_current']) ? $settings['breadcrumb_current'] : __('Shop Archive', 'kinboni-core')); ?>
                  </span>
                </nav>
                <?php endif; ?>

                <?php if (!empty($archive_subtitle)) : ?>
                <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400">
                  <?php echo esc_html($archive_subtitle); ?>
                </span>
                <?php endif; ?>

                <h1 id="shop-archive-title" class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white mt-1">
                  <?php echo esc_html($archive_title); ?>
                </h1>
              </div>

              <?php if (!empty($settings['archive_description'])) : ?>
              <p class="text-sm text-stone-600 dark:text-stone-400 max-w-md font-normal">
                <?php echo esc_html($settings['archive_description']); ?>
              </p>
              <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

              <?php if ($show_sidebar) : ?>
              <aside class="lg:col-span-3 space-y-6 bg-white/95 dark:bg-stone-900/90 backdrop-blur-md p-5 sm:p-6 rounded-2xl border border-stone-200/80 dark:border-stone-800 shadow-sm">
                
                <?php if ($show_search) : ?>
                <div class="widget woocommerce widget_product_search">
                  <h3 class="text-xs font-bold uppercase tracking-widest text-stone-900 dark:text-white mb-3 flex items-center justify-between">
                    <span><?php esc_html_e('Search Catalogue', 'kinboni-core'); ?></span>
                    <i data-lucide="search" class="w-3.5 h-3.5 text-gold-500"></i>
                  </h3>
                  <div class="relative">
                    <input 
                      type="text" 
                      id="shop-search-input" 
                      placeholder="<?php esc_attr_e('Search watches, leather, oud...', 'kinboni-core'); ?>" 
                      aria-label="<?php esc_attr_e('Search shop products', 'kinboni-core'); ?>"
                      class="w-full pl-3.5 pr-9 py-2.5 text-xs rounded-xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500 transition-colors"
                    />
                    <button type="button" onclick="window.ShopPageController ? window.ShopPageController.clearSearch() : null" id="shop-search-clear-btn" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-stone-700 dark:hover:text-white cursor-pointer" aria-label="<?php esc_attr_e('Clear search', 'kinboni-core'); ?>">
                      <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                  </div>
                </div>
                <?php endif; ?>

                <?php if ($show_cats) : ?>
                <div class="widget woocommerce widget_product_categories pt-5 border-t border-stone-200 dark:border-stone-800">
                  <h3 class="text-xs font-bold uppercase tracking-widest text-stone-900 dark:text-white mb-3">
                    <?php esc_html_e('Product Categories', 'kinboni-core'); ?>
                  </h3>
                  <ul id="shop-category-list" class="space-y-1.5 text-xs">
                    <?php foreach ($categories as $cat) : 
                        $is_all = ($cat['id'] === 'all');
                    ?>
                    <li>
                      <button 
                        type="button" 
                        onclick="window.ShopPageController ? window.ShopPageController.setCategory('<?php echo esc_attr($cat['id']); ?>') : null"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl transition-all <?php echo $is_all ? 'bg-gold-400 text-stone-950 font-bold shadow-sm' : 'text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800'; ?> cursor-pointer"
                        data-cat-id="<?php echo esc_attr($cat['id']); ?>"
                      >
                        <span><?php echo esc_html($cat['label']); ?></span>
                        <span class="text-xs px-2 py-0.5 rounded-full <?php echo $is_all ? 'bg-stone-950 text-gold-400' : 'bg-stone-200/70 dark:bg-stone-800 text-stone-600 dark:text-stone-400'; ?>">
                          <?php echo esc_html($cat['count']); ?>
                        </span>
                      </button>
                    </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>

                <?php if ($show_price) : ?>
                <div class="widget woocommerce widget_price_filter pt-5 border-t border-stone-200 dark:border-stone-800">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-stone-900 dark:text-white">
                      <?php esc_html_e('Filter by Price', 'kinboni-core'); ?>
                    </h3>
                    <span id="shop-price-display" class="text-xs font-bold text-gold-700 dark:text-gold-400">
                      <?php printf(esc_html__('Up to ৳%s', 'kinboni-core'), number_format($max_price)); ?>
                    </span>
                  </div>
                  <input 
                    type="range" 
                    id="shop-price-range" 
                    min="<?php echo esc_attr($min_price); ?>" 
                    max="<?php echo esc_attr($max_price); ?>" 
                    step="500" 
                    value="<?php echo esc_attr($max_price); ?>" 
                    aria-label="<?php esc_attr_e('Maximum price filter', 'kinboni-core'); ?>"
                    class="w-full accent-gold-500 cursor-pointer h-1.5 bg-stone-200 dark:bg-stone-800 rounded-lg"
                  />
                  <div class="flex justify-between text-xs text-stone-400 mt-1.5">
                    <span>৳<?php echo esc_html(number_format($min_price)); ?></span>
                    <span>৳<?php echo esc_html(number_format(round(($min_price + $max_price) / 2))); ?></span>
                    <span>৳<?php echo esc_html(number_format($max_price)); ?></span>
                  </div>
                </div>
                <?php endif; ?>

                <?php if ($show_priv) : ?>
                <div class="widget woocommerce pt-5 border-t border-stone-200 dark:border-stone-800 space-y-2.5">
                  <h3 class="text-xs font-bold uppercase tracking-widest text-stone-900 dark:text-white mb-2">
                    <?php esc_html_e('Archive Privileges', 'kinboni-core'); ?>
                  </h3>
                  <label class="flex items-center gap-2.5 text-xs text-stone-700 dark:text-stone-300 cursor-pointer">
                    <input type="checkbox" id="shop-filter-onsale" class="rounded border-stone-300 text-gold-500 focus:ring-gold-500 w-4 h-4 cursor-pointer" />
                    <span><?php esc_html_e('On Private Sale Only', 'kinboni-core'); ?></span>
                  </label>
                  <label class="flex items-center gap-2.5 text-xs text-stone-700 dark:text-stone-300 cursor-pointer">
                    <input type="checkbox" id="shop-filter-bestseller" class="rounded border-stone-300 text-gold-500 focus:ring-gold-500 w-4 h-4 cursor-pointer" />
                    <span><?php esc_html_e('Patron Best Sellers', 'kinboni-core'); ?></span>
                  </label>
                </div>
                <?php endif; ?>

                <div class="pt-4 border-t border-stone-200 dark:border-stone-800">
                  <button 
                    type="button" 
                    onclick="window.ShopPageController ? window.ShopPageController.resetFilters() : null"
                    class="w-full py-2.5 px-4 rounded-xl border border-stone-300 dark:border-stone-700 hover:border-gold-500 text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 hover:text-gold-600 dark:hover:text-gold-400 transition-colors flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                  >
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span><?php esc_html_e('Reset All Filters', 'kinboni-core'); ?></span>
                  </button>
                </div>

              </aside>
              <?php endif; ?>

              <div class="<?php echo $show_sidebar ? 'lg:col-span-9' : 'lg:col-span-12'; ?> space-y-6">

                <div class="bg-white/95 dark:bg-stone-900/90 backdrop-blur-md p-4 rounded-2xl border border-stone-200/80 dark:border-stone-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <p id="shop-result-count" class="woocommerce-result-count text-xs text-stone-600 dark:text-stone-400 font-medium">
                    <?php printf(esc_html__('Showing all %d results', 'kinboni-core'), $total_products); ?>
                  </p>

                  <div class="flex items-center gap-3 self-end sm:self-auto">
                    <form class="woocommerce-ordering" onsubmit="return false;">
                      <select 
                        id="shop-sort-select" 
                        aria-label="<?php esc_attr_e('Shop order', 'kinboni-core'); ?>" 
                        class="px-3.5 py-2 text-xs rounded-xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-stone-800 dark:text-stone-200 font-medium focus:outline-none focus:border-gold-500 cursor-pointer"
                      >
                        <option value="menu_order"><?php esc_html_e('Default sorting (Atelier Curated)', 'kinboni-core'); ?></option>
                        <option value="popularity"><?php esc_html_e('Sort by popularity (Reviews)', 'kinboni-core'); ?></option>
                        <option value="rating"><?php esc_html_e('Sort by average rating', 'kinboni-core'); ?></option>
                        <option value="price"><?php esc_html_e('Sort by price: low to high', 'kinboni-core'); ?></option>
                        <option value="price-desc"><?php esc_html_e('Sort by price: high to low', 'kinboni-core'); ?></option>
                      </select>
                    </form>

                    <?php if ($show_toggle) : ?>
                    <div class="flex items-center border border-stone-200 dark:border-stone-700 rounded-xl overflow-hidden bg-stone-50 dark:bg-stone-950">
                      <button 
                        type="button" 
                        id="shop-view-grid-btn"
                        onclick="window.ShopPageController ? window.ShopPageController.setViewMode('grid') : null" 
                        class="p-2 <?php echo ($default_view === 'grid') ? 'bg-gold-400 text-stone-950' : 'text-stone-500 hover:text-stone-900 dark:hover:text-white'; ?> transition-colors cursor-pointer"
                        aria-label="<?php esc_attr_e('Grid view', 'kinboni-core'); ?>"
                      >
                        <i data-lucide="grid" class="w-4 h-4"></i>
                      </button>
                      <button 
                        type="button" 
                        id="shop-view-list-btn"
                        onclick="window.ShopPageController ? window.ShopPageController.setViewMode('list') : null" 
                        class="p-2 <?php echo ($default_view === 'list') ? 'bg-gold-400 text-stone-950' : 'text-stone-500 hover:text-stone-900 dark:hover:text-white'; ?> transition-colors cursor-pointer"
                        aria-label="<?php esc_attr_e('List view', 'kinboni-core'); ?>"
                      >
                        <i data-lucide="list" class="w-4 h-4"></i>
                      </button>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if ($show_pills) : ?>
                <div id="shop-top-category-pills" class="flex items-center gap-2 overflow-x-auto pb-1">
                  <?php foreach ($categories as $cat) : 
                      $is_all = ($cat['id'] === 'all');
                  ?>
                  <button
                    type="button"
                    onclick="window.ShopPageController ? window.ShopPageController.setCategory('<?php echo esc_attr($cat['id']); ?>') : null"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all border <?php echo $is_all ? 'bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 border-transparent shadow-sm' : 'bg-white/90 dark:bg-stone-900/90 text-stone-700 dark:text-stone-300 border-stone-200/80 dark:border-stone-800 hover:border-gold-500'; ?> cursor-pointer"
                    data-cat-pill="<?php echo esc_attr($cat['id']); ?>"
                  >
                    <span><?php echo esc_html($cat['label']); ?></span>
                    <span class="ml-1 opacity-70">(<?php echo esc_html($cat['count']); ?>)</span>
                  </button>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div id="shop-active-filters" class="hidden items-center flex-wrap gap-2"></div>

                <div id="shop-products-container" class="products <?php echo ($default_view === 'list') ? 'space-y-4' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6'; ?>">
                  <?php 
                  $initial_items = array_slice($products, 0, $per_page);
                  foreach ($initial_items as $p) :
                      $pid          = $p['id'];
                      $pname        = $p['name'];
                      $plink        = $p['link'];
                      $price        = $p['price'];
                      $orig_price   = $p['originalPrice'];
                      $pimg         = $p['image'];
                      $phover       = $p['hoverImage'];
                      $badge        = $p['badge'];
                      $cat_label    = $p['categoryLabel'];
                      $rating       = $p['rating'];
                      $reviews      = $p['reviewsCount'];
                      $discount_pct = ($orig_price > $price) ? round((($orig_price - $price) / $orig_price) * 100) : 0;
                      ?>
                      <div class="kinboni-product-card group relative flex flex-col bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-luxury dark:hover:shadow-luxury-dark hover:border-gold-500/40 dark:hover:border-gold-500/40 transition-all duration-500 cursor-pointer" data-product-id="<?php echo esc_attr($pid); ?>">
                        <div class="relative w-full aspect-[4/5] bg-stone-100 dark:bg-stone-950 overflow-hidden cursor-pointer" onclick="if (!event.target.closest('button, a')) window.location.href='<?php echo esc_url($plink); ?>'">
                          <img 
                            src="<?php echo esc_url($pimg); ?>" 
                            alt="<?php echo esc_attr($pname); ?>" 
                            loading="lazy" 
                            class="w-full h-full object-cover object-center transition-all duration-700 ease-out group-hover:scale-105"
                          />
                          <?php if (!empty($phover) && $phover !== $pimg) : ?>
                          <img 
                            src="<?php echo esc_url($phover); ?>" 
                            alt="<?php echo esc_attr($pname); ?> alternate angle" 
                            loading="lazy" 
                            class="absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-700 ease-out group-hover:opacity-100 group-hover:scale-105 pointer-events-none"
                          />
                          <?php endif; ?>

                          <?php if ($show_badges && (!empty($badge) || $discount_pct > 0)) : ?>
                          <div class="absolute top-3 left-3 right-20 flex flex-row flex-wrap items-center gap-1.5 z-10 pointer-events-none">
                            <?php if (!empty($badge)) : ?>
                            <span class="w-fit flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] rounded-full bg-stone-950/90 text-stone-100 dark:bg-white/95 dark:text-stone-950 shadow-md backdrop-blur-sm border border-white/10 dark:border-stone-900/10">
                              <?php echo esc_html($badge); ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($discount_pct > 0) : ?>
                            <span class="w-fit flex-shrink-0 inline-flex items-center px-2 py-0.5 text-[10px] font-bold tracking-wider rounded-full bg-rose-600 text-white shadow-md">
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
                            <?php if ($rating > 0) : ?>
                            <div class="flex items-center gap-1 text-[11px] font-semibold text-stone-700 dark:text-stone-300 flex-shrink-0">
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
                            <a 
                              href="<?php echo esc_url($p['add_to_cart_url'] ?: $plink); ?>" 
                              data-quantity="1"
                              data-product_id="<?php echo esc_attr($pid); ?>"
                              class="ajax_add_to_cart add_to_cart_button flex-1 py-2.5 px-3 rounded-xl bg-stone-200/80 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-100 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer" 
                            >
                              <i data-lucide="shopping-bag" class="w-3.5 h-3.5 flex-shrink-0 text-stone-600 dark:text-stone-300"></i>
                              <span class="whitespace-nowrap"><?php echo esc_html($btn_cart_text); ?></span>
                            </a>

                            <button 
                              type="button" 
                              class="btn-trigger-quick-order flex-1 py-2.5 px-3 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm hover:shadow-gold-500/25 active:scale-95 cursor-pointer" 
                              data-product-id="<?php echo esc_attr($pid); ?>"
                              data-product-name="<?php echo esc_attr($pname); ?>"
                              data-product-price="<?php echo esc_attr($price); ?>"
                              data-product-image="<?php echo esc_url($pimg); ?>"
                            >
                              <i data-lucide="zap" class="w-3.5 h-3.5 fill-stone-950 flex-shrink-0"></i>
                              <span class="whitespace-nowrap"><?php echo esc_html($btn_cod_text); ?></span>
                            </button>
                          </div>
                        </div>
                      </div>
                  <?php endforeach; ?>
                </div>

                <nav id="shop-pagination" class="woocommerce-pagination pt-6 flex items-center justify-between border-t border-stone-200 dark:border-stone-800" aria-label="<?php esc_attr_e('Product Pagination', 'kinboni-core'); ?>">
                  <div class="flex items-center gap-2 text-xs text-stone-500">
                    <span><?php printf(esc_html__('Page 1 of %d (%d creations)', 'kinboni-core'), max(1, ceil($total_products / $per_page)), $total_products); ?></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-[11px] text-stone-400 mr-1"><?php esc_html_e('Show:', 'kinboni-core'); ?></span>
                    <button type="button" onclick="window.ShopPageController ? window.ShopPageController.setPerPage(12) : null" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all <?php echo ($per_page === 12) ? 'bg-gold-400 text-stone-950' : 'bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400'; ?> cursor-pointer">12 / Page</button>
                    <button type="button" onclick="window.ShopPageController ? window.ShopPageController.setPerPage(6) : null" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all <?php echo ($per_page === 6) ? 'bg-gold-400 text-stone-950' : 'bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400'; ?> cursor-pointer">6 / Page</button>
                    <button type="button" onclick="window.ShopPageController ? window.ShopPageController.goToPage(1) : null" class="w-9 h-9 rounded-xl text-xs font-bold transition-all bg-gold-400 text-stone-950 shadow-sm cursor-pointer">1</button>
                    <?php if (ceil($total_products / $per_page) > 1) : ?>
                    <button type="button" onclick="window.ShopPageController ? window.ShopPageController.goToPage(2) : null" class="w-9 h-9 rounded-xl text-xs font-bold transition-all bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-gold-500 cursor-pointer">2</button>
                    <?php endif; ?>
                  </div>
                </nav>

              </div>

            </div>

          </div>

          <script type="application/json" id="kinboni-shop-products-data">
            <?php echo wp_json_encode($products); ?>
          </script>
          <script type="application/json" id="kinboni-shop-categories-data">
            <?php echo wp_json_encode($categories); ?>
          </script>
        </main>
        <?php
    }
}

