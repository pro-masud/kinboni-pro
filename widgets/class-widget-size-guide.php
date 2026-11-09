<?php
/**
 * Kinboni Saved Wishlist Archive - Elementor Widget
 *
 * Implements the luxury editorial Saved Wishlist page section featuring
 * ambient page glows, architectural shapes, breadcrumbs, "Add All to Bag" action,
 * and a real-time reactive grid connected to the user's saved wishlist creations.
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

class Kinboni_Widget_Size_Guide extends Widget_Base {

    public function get_name() {
        return 'kinboni_size_guide';
    }

    public function get_title() {
        return esc_html__('Saved Wishlist Archive', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-heart';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('wishlist', 'saved', 'bookmarks', 'favorites', 'archive', 'products', 'woocommerce', 'kinboni');
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
            'show_breadcrumbs',
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
            'breadcrumb_home_text',
            array(
                'label'     => esc_html__('Home Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Home', 'kinboni-core'),
                'condition' => array('show_breadcrumbs' => 'yes'),
            )
        );

        $this->add_control(
            'breadcrumb_current_text',
            array(
                'label'     => esc_html__('Current Page Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Saved Wishlist', 'kinboni-core'),
                'condition' => array('show_breadcrumbs' => 'yes'),
            )
        );

        $this->add_control(
            'badge_text',
            array(
                'label'       => esc_html__('Badge Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Private Bookmarks', 'kinboni-core'),
                'placeholder' => esc_html__('Private Bookmarks', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'badge_tag',
            array(
                'label'   => esc_html__('Badge Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'span' => 'span',
                    'p'    => 'p',
                    'div'  => 'div',
                    'h5'   => 'h5',
                    'h6'   => 'h6',
                ),
                'default' => 'span',
            )
        );

        $this->add_control(
            'title_text',
            array(
                'label'       => esc_html__('Main Headline', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Your Saved Wishlist', 'kinboni-core'),
                'placeholder' => esc_html__('Your Saved Wishlist', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'title_tag',
            array(
                'label'   => esc_html__('Headline Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                ),
                'default' => 'h1',
            )
        );

        $this->add_control(
            'show_move_all_btn',
            array(
                'label'        => esc_html__('Show "Add All to Bag" Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'btn_move_all_text',
            array(
                'label'       => esc_html__('Action Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Add All Saved Items to Bag', 'kinboni-core'),
                'condition'   => array('show_move_all_btn' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: EMPTY STATE SETTINGS
        // ==========================================
        $this->start_controls_section(
            'section_empty_state_content',
            array(
                'label' => esc_html__('Empty Wishlist State', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'empty_title',
            array(
                'label'       => esc_html__('Empty State Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Your Wishlist is Empty', 'kinboni-core'),
                'placeholder' => esc_html__('Your Wishlist is Empty', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'empty_description',
            array(
                'label'       => esc_html__('Empty State Message', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Tap the heart icon on any creation to bookmark it in your personal archive.', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'empty_btn_text',
            array(
                'label'       => esc_html__('Browse Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Explore Shop Archive', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'empty_btn_url',
            array(
                'label'       => esc_html__('Browse Button URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('Leave empty for default WooCommerce shop URL', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: AMBIENT GLOWS & GRID
        // ==========================================
        $this->start_controls_section(
            'section_ambient_settings',
            array(
                'label' => esc_html__('Layout & Ambient Glow', 'kinboni-core'),
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

        $this->add_responsive_control(
            'columns',
            array(
                'label'          => esc_html__('Columns', 'kinboni-core'),
                'type'           => Controls_Manager::SELECT,
                'default'        => '4',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options'        => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ),
            )
        );

        $this->add_control(
            'section_html_id',
            array(
                'label'       => esc_html__('Section HTML ID', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'page-view-wishlist',
                'placeholder' => 'page-view-wishlist',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: SECTION STYLE
        // ==========================================
        $this->start_controls_section(
            'section_style_general',
            array(
                'label' => esc_html__('Section Style', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'section_padding',
            array(
                'label'      => esc_html__('Padding', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-wishlist-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Custom Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-wishlist-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: ACTION BUTTON STYLE
        // ==========================================
        $this->start_controls_section(
            'section_style_button',
            array(
                'label'     => esc_html__('"Add All to Bag" Button', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_move_all_btn' => 'yes'),
            )
        );

        $this->add_control(
            'btn_bg_color',
            array(
                'label'     => esc_html__('Button Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .btn-wishlist-move-all-to-bag' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'btn_text_color',
            array(
                'label'     => esc_html__('Button Text Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .btn-wishlist-move-all-to-bag' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Preview sample products for Elementor Editor
     */
    private function get_editor_preview_products() {
        return array(
            array(
                'id'             => 1,
                'name'           => 'Chronos Royale Tourbillon 42mm',
                'category'       => 'Horology',
                'price'          => 18500,
                'original_price' => 22000,
                'discount_pct'   => 16,
                'image'          => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                'link'           => '#',
            ),
            array(
                'id'             => 2,
                'name'           => 'Atelier Saddle Leather Briefcase',
                'category'       => 'Leathercraft',
                'price'          => 14200,
                'original_price' => 16500,
                'discount_pct'   => 14,
                'image'          => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
                'link'           => '#',
            ),
            array(
                'id'             => 3,
                'name'           => 'Aurelia 18K Solid Gold Signet Ring',
                'category'       => 'Haute Joaillerie',
                'price'          => 24800,
                'original_price' => 29000,
                'discount_pct'   => 15,
                'image'          => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80',
                'link'           => '#',
            ),
            array(
                'id'             => 4,
                'name'           => 'Kyoto Mulberry Silk Foulard Scarf',
                'category'       => 'Silk & Cashmere',
                'price'          => 4800,
                'original_price' => 5600,
                'discount_pct'   => 14,
                'image'          => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=800&q=80',
                'link'           => '#',
            ),
        );
    }

    /**
     * Render Widget Output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'page-view-wishlist';
        $show_decor = (!empty($settings['show_ambient_shapes']) && $settings['show_ambient_shapes'] === 'yes');

        // Breadcrumb
        $show_crumbs = (!empty($settings['show_breadcrumbs']) && $settings['show_breadcrumbs'] === 'yes');
        $home_text   = !empty($settings['breadcrumb_home_text']) ? $settings['breadcrumb_home_text'] : esc_html__('Home', 'kinboni-core');
        $crumb_text  = !empty($settings['breadcrumb_current_text']) ? $settings['breadcrumb_current_text'] : esc_html__('Saved Wishlist', 'kinboni-core');

        // Header copy
        $badge_text  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'size_guide_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $badge_tag   = in_array($settings['badge_tag'], array('span', 'p', 'div', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_text  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'size_guide_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');
        $title_tag   = in_array($settings['title_tag'], array('h1', 'h2', 'h3'), true) ? $settings['title_tag'] : 'h1';

        $show_move_all = (!empty($settings['show_move_all_btn']) && $settings['show_move_all_btn'] === 'yes');
        $btn_move_all  = !empty($settings['btn_move_all_text']) ? $settings['btn_move_all_text'] : esc_html__('Add All Saved Items to Bag', 'kinboni-core');

        // Empty state settings
        $empty_title = !empty($settings['empty_title']) ? $settings['empty_title'] : esc_html__('Your Wishlist is Empty', 'kinboni-core');
        $empty_desc  = !empty($settings['empty_description']) ? $settings['empty_description'] : esc_html__('Tap the heart icon on any creation to bookmark it in your personal archive.', 'kinboni-core');
        $empty_btn   = !empty($settings['empty_btn_text']) ? $settings['empty_btn_text'] : esc_html__('Explore Shop Archive', 'kinboni-core');

        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
        if (!empty($settings['empty_btn_url']['url'])) {
            $shop_url = $settings['empty_btn_url']['url'];
        }

        $is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $preview_products = $this->get_editor_preview_products();
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-wishlist-section wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]">

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

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 pb-6 border-b border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <?php if ($show_crumbs) : ?>
        <nav class="woocommerce-breadcrumb flex items-center gap-2 text-xs text-stone-600 dark:text-stone-300 mb-3" aria-label="Breadcrumb">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors"><?php echo esc_html($home_text); ?></a>
          <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
          <span class="text-stone-900 dark:text-white font-semibold"><?php echo esc_html($crumb_text); ?></span>
        </nav>
        <?php endif; ?>

        <?php if (!empty($badge_text)) : ?>
        <<?php echo $badge_tag; ?> class="kinboni-wish-badge text-[11px] font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400 block">
          <?php echo esc_html($badge_text); ?>
        </<?php echo $badge_tag; ?>>
        <?php endif; ?>

        <?php if (!empty($title_text)) : ?>
        <<?php echo $title_tag; ?> class="kinboni-wish-title font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white mt-1">
          <?php echo esc_html($title_text); ?>
        </<?php echo $title_tag; ?>>
        <?php endif; ?>
      </div>

      <?php if ($show_move_all) : ?>
      <button 
        type="button" 
        class="btn-wishlist-move-all-to-bag px-5 py-2.5 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest transition-all active:scale-95 shadow-sm"
      >
        <?php echo esc_html($btn_move_all); ?>
      </button>
      <?php endif; ?>
    </div>

    <div 
      id="wishlist-page-grid" 
      class="kinboni-wishlist-page-grid min-h-[300px]"
      data-shop-url="<?php echo esc_url($shop_url); ?>"
      data-empty-title="<?php echo esc_attr($empty_title); ?>"
      data-empty-desc="<?php echo esc_attr($empty_desc); ?>"
      data-empty-btn="<?php echo esc_attr($empty_btn); ?>"
    >
      <?php if ($is_edit) : ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($preview_products as $p) : ?>
        <div class="kinboni-product-card group relative flex flex-col bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:border-gold-500/40 transition-all duration-300">
          <div class="relative w-full aspect-[4/5] bg-stone-100 dark:bg-stone-950 overflow-hidden">
            <img loading="lazy" src="<?php echo esc_url($p['image']); ?>" alt="<?php echo esc_attr($p['name']); ?>" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute top-3 right-3 z-10">
              <button type="button" class="w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-rose-500 shadow-md">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </div>
            <?php if (!empty($p['discount_pct'])) : ?>
            <div class="absolute top-3 left-3 z-10">
              <span class="inline-flex px-2.5 py-1 text-[11px] font-bold tracking-wider rounded-full bg-rose-600 text-white shadow-md">
                -<?php echo esc_html($p['discount_pct']); ?>%
              </span>
            </div>
            <?php endif; ?>
          </div>
          <div class="p-4 sm:p-5 flex flex-col flex-grow">
            <span class="text-xs uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400 mb-1"><?php echo esc_html($p['category']); ?></span>
            <h3 class="font-serif text-lg font-bold text-stone-900 dark:text-white leading-snug truncate mb-2"><?php echo esc_html($p['name']); ?></h3>
            <div class="flex items-baseline gap-2 mb-3">
              <span class="font-serif text-xl font-bold text-stone-950 dark:text-white">৳<?php echo esc_html(number_format($p['price'])); ?></span>
              <span class="text-xs text-stone-400 line-through">৳<?php echo esc_html(number_format($p['original_price'])); ?></span>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-auto pt-3 border-t border-stone-100 dark:border-stone-800/80">
              <button type="button" class="py-2.5 px-3 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-wider rounded-xl text-center">Add to Bag</button>
              <button type="button" class="py-2.5 px-3 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 text-xs font-bold uppercase tracking-wider rounded-xl text-center">Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else : ?>
      <div class="wishlist-loading py-20 text-center text-xs text-stone-500 animate-pulse">
        <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-2 text-gold-400 animate-spin"></i>
        <span><?php esc_html_e('Accessing your private saved archive...', 'kinboni-core'); ?></span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
        <?php
    }
}
