<?php
/**
 * Kinboni Haute Atelier Footer Showcase - Elementor Widget
 *
 * Implements the luxury editorial 4-column footer matching the prototype 100%:
 * - Col 1: Brand Monogram, Wordmark, Atelier Narrative, and Social Media Links
 * - Col 2: Luxury Collections (Dynamic WooCommerce Categories or Custom Links)
 * - Col 3: Storefront & Patron Care Navigation Links
 * - Col 4: Boutique Address, Phone, Concierge Email & Operating Hours
 * - Bottom Bar: Dynamic Copyright notice and Payment Badges (VISA, MasterCard, bKash, Nagad, COD)
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

class Kinboni_Widget_Footer extends Widget_Base {

    public function get_name() {
        return 'kinboni_footer';
    }

    public function get_title() {
        return esc_html__('Haute Atelier Footer Showcase', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-footer';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('footer', 'navigation', 'links', 'social', 'copyright', 'payment', 'atelier', 'kinboni');
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: COLUMN 1 - BRAND & STORY
        // ==========================================
        $this->start_controls_section(
            'section_brand_column',
            array(
                'label' => esc_html__('Column 1: Brand & Atelier Story', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'brand_letter',
            array(
                'label'       => esc_html__('Monogram Letter', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'K',
            )
        );

        $this->add_control(
            'brand_title',
            array(
                'label'       => esc_html__('Brand Wordmark', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'KINBONI',
            )
        );

        $this->add_control(
            'brand_subtitle',
            array(
                'label'       => esc_html__('Brand Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'HAUTE ATELIER',
            )
        );

        $this->add_control(
            'brand_description',
            array(
                'label'   => esc_html__('Atelier Narrative', 'kinboni-core'),
                'type'    => Controls_Manager::TEXTAREA,
                'default' => esc_html__('Fine chronographs, Tuscan leather goods and artisanal extraits, made in small batches for people who keep what they buy.', 'kinboni-core'),
            )
        );

        $this->add_control(
            'instagram_url',
            array(
                'label'       => esc_html__('Instagram URL', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '#',
            )
        );

        $this->add_control(
            'facebook_url',
            array(
                'label'       => esc_html__('Facebook URL', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '#',
            )
        );

        $this->add_control(
            'youtube_url',
            array(
                'label'       => esc_html__('YouTube URL', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '#',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: COLUMN 2 - COLLECTIONS
        // ==========================================
        $this->start_controls_section(
            'section_collections_column',
            array(
                'label' => esc_html__('Column 2: Collections', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'col2_title',
            array(
                'label'       => esc_html__('Column Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Collections', 'kinboni-core'),
            )
        );

        $this->add_control(
            'col2_source',
            array(
                'label'   => esc_html__('Source', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'dynamic',
                'options' => array(
                    'dynamic' => esc_html__('Dynamic WooCommerce Categories', 'kinboni-core'),
                    'custom'  => esc_html__('Custom Links Repeater', 'kinboni-core'),
                ),
            )
        );

        $repeater_col2 = new Repeater();
        $repeater_col2->add_control(
            'link_label',
            array(
                'label'       => esc_html__('Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Collection Item',
            )
        );
        $repeater_col2->add_control(
            'link_url',
            array(
                'label'       => esc_html__('URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com/shop', 'kinboni-core'),
                'default'     => array('url' => '#'),
            )
        );
        $repeater_col2->add_control(
            'cat_slug',
            array(
                'label'       => esc_html__('Category Slug (for shop filter)', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '',
            )
        );

        $this->add_control(
            'col2_items',
            array(
                'label'       => esc_html__('Collection Links', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater_col2->get_controls(),
                'title_field' => '{{{ link_label }}}',
                'default'     => array(
                    array('link_label' => 'Chronographs', 'cat_slug' => 'timepieces'),
                    array('link_label' => 'Leather Goods', 'cat_slug' => 'leather'),
                    array('link_label' => 'Fine Fragrance', 'cat_slug' => 'fragrance'),
                    array('link_label' => 'Fine Jewelry', 'cat_slug' => 'jewelry'),
                    array('link_label' => 'Eyewear', 'cat_slug' => 'eyewear'),
                    array('link_label' => 'Silk & Cashmere', 'cat_slug' => 'silk'),
                ),
                'condition'   => array('col2_source' => 'custom'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: COLUMN 3 - PATRON CARE
        // ==========================================
        $this->start_controls_section(
            'section_patron_care_column',
            array(
                'label' => esc_html__('Column 3: Storefront & Patron Care', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'col3_title',
            array(
                'label'       => esc_html__('Column Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Storefront & Patron Care', 'kinboni-core'),
            )
        );

        $repeater_col3 = new Repeater();
        $repeater_col3->add_control(
            'item_title',
            array(
                'label'       => esc_html__('Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Patron Care Link',
            )
        );
        $repeater_col3->add_control(
            'item_url',
            array(
                'label'       => esc_html__('URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com', 'kinboni-core'),
                'default'     => array('url' => '#'),
            )
        );

        $this->add_control(
            'col3_items',
            array(
                'label'       => esc_html__('Navigation Links', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater_col3->get_controls(),
                'title_field' => '{{{ item_title }}}',
                'default'     => array(
                    array('item_title' => 'Shop Full Archive', 'item_url' => array('url' => '/shop')),
                    array('item_title' => 'Private Flash Deals (-20%)', 'item_url' => array('url' => '/flash-deals')),
                    array('item_title' => 'Saved Wishlist', 'item_url' => array('url' => '/wishlist')),
                    array('item_title' => 'My Account Portal', 'item_url' => array('url' => '/my-account')),
                    array('item_title' => 'FAQ, Shipping & Returns', 'item_url' => array('url' => '/faq')),
                    array('item_title' => 'Gulshan-2 Flagship Salon', 'item_url' => array('url' => '/contact')),
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: COLUMN 4 - VISIT & CONTACT
        // ==========================================
        $this->start_controls_section(
            'section_contact_column',
            array(
                'label' => esc_html__('Column 4: Visit or Contact', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'col4_title',
            array(
                'label'       => esc_html__('Column Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Visit or contact', 'kinboni-core'),
            )
        );

        $this->add_control(
            'contact_address',
            array(
                'label'       => esc_html__('Address', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Avenue 4, Road 11, Gulshan-2, Dhaka 1212',
            )
        );

        $this->add_control(
            'contact_phone',
            array(
                'label'       => esc_html__('Phone Number', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '+880 1711 000000',
            )
        );

        $this->add_control(
            'contact_email',
            array(
                'label'       => esc_html__('Email Address', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'concierge@kinboni.com',
            )
        );

        $this->add_control(
            'contact_hours',
            array(
                'label'       => esc_html__('Operating Hours', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'Mon to Sat, 10 AM to 9 PM',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: BOTTOM COPYRIGHT & PAYMENTS
        // ==========================================
        $this->start_controls_section(
            'section_bottom_bar',
            array(
                'label' => esc_html__('Bottom Bar & Payment Badges', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'copyright_text',
            array(
                'label'       => esc_html__('Copyright Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '© {year} KINBONI Atelier. All rights reserved.',
            )
        );

        $this->add_control(
            'show_payment_badges',
            array(
                'label'        => esc_html__('Show Payment Badges', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: COLORS & STYLING
        // ==========================================
        $this->start_controls_section(
            'section_style_footer',
            array(
                'label' => esc_html__('Footer Styling', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'footer_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} footer' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'footer_border_color',
            array(
                'label'     => esc_html__('Top Accent Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} footer' => 'border-top-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'headings_color',
            array(
                'label'     => esc_html__('Column Headings Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} h4' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'links_color',
            array(
                'label'     => esc_html__('Links Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} ul a, {{WRAPPER}} p' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render the Footer Widget Output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $brand_letter = !empty($settings['brand_letter']) ? $settings['brand_letter'] : 'K';
        $brand_title  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'brand_title', 'brand_name', 'KINBONI', $this) : (!empty($settings['brand_title']) ? $settings['brand_title'] : 'KINBONI');
        $brand_sub    = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'brand_subtitle', 'brand_tagline', 'HAUTE ATELIER', $this) : (!empty($settings['brand_subtitle']) ? $settings['brand_subtitle'] : 'HAUTE ATELIER');
        $brand_desc   = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'brand_description', 'brand_description', '', $this) : (!empty($settings['brand_description']) ? $settings['brand_description'] : '');

        $insta_url    = !empty($settings['instagram_url']) ? $settings['instagram_url'] : '#';
        $fb_url       = !empty($settings['facebook_url']) ? $settings['facebook_url'] : '#';
        $yt_url       = !empty($settings['youtube_url']) ? $settings['youtube_url'] : '#';

        $col2_title   = !empty($settings['col2_title']) ? $settings['col2_title'] : __('Collections', 'kinboni-core');
        $col2_source  = !empty($settings['col2_source']) ? $settings['col2_source'] : 'dynamic';

        $col3_title   = !empty($settings['col3_title']) ? $settings['col3_title'] : __('Storefront & Patron Care', 'kinboni-core');
        $col4_title   = !empty($settings['col4_title']) ? $settings['col4_title'] : __('Visit or contact', 'kinboni-core');

        $address      = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'contact_address', 'concierge_address', 'Avenue 4, Road 11, Gulshan-2, Dhaka 1212', $this) : (!empty($settings['contact_address']) ? $settings['contact_address'] : 'Avenue 4, Road 11, Gulshan-2, Dhaka 1212');
        $phone        = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'contact_phone', 'concierge_phone', '+880 1711 000000', $this) : (!empty($settings['contact_phone']) ? $settings['contact_phone'] : '+880 1711 000000');
        $email        = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'contact_email', 'concierge_email', 'concierge@kinboni.com', $this) : (!empty($settings['contact_email']) ? $settings['contact_email'] : 'concierge@kinboni.com');
        $hours        = !empty($settings['contact_hours']) ? $settings['contact_hours'] : 'Mon to Sat, 10 AM to 9 PM';

        $show_badges  = ('yes' === $settings['show_payment_badges']);
        $copyright    = !empty($settings['copyright_text']) 
            ? str_replace('{year}', date('Y'), $settings['copyright_text']) 
            : sprintf('© %s KINBONI Atelier. All rights reserved.', date('Y'));

        $shop_page_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
        ?>
        <footer class="bg-stone-950 text-stone-400 border-t-2 border-gold-500/70">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 pb-12 border-b border-stone-800">
              
              <!-- Column 1: Brand & Atelier Story -->
              <div class="lg:col-span-4 space-y-5">
                <a
                  href="<?php echo esc_url(home_url('/')); ?>"
                  class="inline-flex items-center gap-3"
                  aria-label="<?php echo esc_attr($brand_title); ?>"
                >
                  <span
                    class="grid place-items-center w-11 h-11 rounded-full bg-gold-400 text-stone-950 font-serif text-xl font-semibold shadow-sm"
                  ><?php echo esc_html($brand_letter); ?></span>
                  <span class="flex flex-col leading-none">
                    <span
                      class="font-serif text-2xl font-semibold tracking-[0.22em] text-white"
                    ><?php echo esc_html($brand_title); ?></span>
                    <span
                      class="text-[11px] tracking-[0.4em] text-gold-400 font-semibold mt-1"
                    ><?php echo esc_html($brand_sub); ?></span>
                  </span>
                </a>
                
                <?php if (!empty($brand_desc)) : ?>
                <p class="text-sm leading-relaxed max-w-sm font-normal text-stone-400">
                  <?php echo esc_html($brand_desc); ?>
                </p>
                <?php endif; ?>

                <div class="flex items-center gap-2.5">
                  <?php if (!empty($insta_url) && $insta_url !== '#disabled') : ?>
                  <a
                    href="<?php echo esc_url($insta_url); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-9 h-9 rounded-full border border-stone-800 flex items-center justify-center hover:bg-gold-400 hover:text-stone-950 hover:border-gold-400 transition-colors text-stone-400"
                    aria-label="Instagram"
                  >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                      <path
                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                      />
                    </svg>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($fb_url) && $fb_url !== '#disabled') : ?>
                  <a
                    href="<?php echo esc_url($fb_url); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-9 h-9 rounded-full border border-stone-800 flex items-center justify-center hover:bg-gold-400 hover:text-stone-950 hover:border-gold-400 transition-colors text-stone-400"
                    aria-label="Facebook"
                  >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                      <path
                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                      />
                    </svg>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($yt_url) && $yt_url !== '#disabled') : ?>
                  <a
                    href="<?php echo esc_url($yt_url); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-9 h-9 rounded-full border border-stone-800 flex items-center justify-center hover:bg-gold-400 hover:text-stone-950 hover:border-gold-400 transition-colors text-stone-400"
                    aria-label="YouTube"
                  >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                      <path
                        d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
                      />
                    </svg>
                  </a>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Column 2: Collections -->
              <div class="lg:col-span-2">
                <h4 class="text-sm font-semibold text-white mb-4"><?php echo esc_html($col2_title); ?></h4>
                <ul class="space-y-3 text-sm">
                  <?php
                  if ('dynamic' === $col2_source && taxonomy_exists('product_cat')) {
                      $terms = get_terms(array(
                          'taxonomy'   => 'product_cat',
                          'hide_empty' => false,
                          'number'     => 6,
                      ));
                      if (!empty($terms) && !is_wp_error($terms)) {
                          foreach ($terms as $t) {
                              $term_url = add_query_arg('category', $t->slug, $shop_page_url);
                              ?>
                              <li>
                                <a
                                  href="<?php echo esc_url($term_url); ?>"
                                  class="block text-stone-400 hover:text-gold-400 transition-colors"
                                ><?php echo esc_html($t->name); ?></a>
                              </li>
                              <?php
                          }
                      } else {
                          // Fallback default atelier collections
                          $default_cols = array(
                              'timepieces' => 'Chronographs',
                              'leather'    => 'Leather Goods',
                              'fragrance'  => 'Fine Fragrance',
                              'jewelry'    => 'Fine Jewelry',
                              'eyewear'    => 'Eyewear',
                              'silk'       => 'Silk & Cashmere',
                          );
                          foreach ($default_cols as $slug => $label) {
                              $term_url = add_query_arg('category', $slug, $shop_page_url);
                              ?>
                              <li>
                                <a
                                  href="<?php echo esc_url($term_url); ?>"
                                  class="block text-stone-400 hover:text-gold-400 transition-colors"
                                ><?php echo esc_html($label); ?></a>
                              </li>
                              <?php
                          }
                      }
                  } elseif (!empty($settings['col2_items'])) {
                      foreach ($settings['col2_items'] as $item) {
                          $item_url = !empty($item['link_url']['url']) ? $item['link_url']['url'] : '#';
                          if (!empty($item['cat_slug'])) {
                              $item_url = add_query_arg('category', $item['cat_slug'], $shop_page_url);
                          }
                          ?>
                          <li>
                            <a
                              href="<?php echo esc_url($item_url); ?>"
                              class="block text-stone-400 hover:text-gold-400 transition-colors"
                            ><?php echo esc_html($item['link_label']); ?></a>
                          </li>
                          <?php
                      }
                  }
                  ?>
                </ul>
              </div>

              <!-- Column 3: Storefront & Patron Care -->
              <div class="lg:col-span-3">
                <h4 class="text-sm font-semibold text-white mb-4">
                  <?php echo esc_html($col3_title); ?>
                </h4>
                <ul class="space-y-2.5 text-sm">
                  <?php if (!empty($settings['col3_items'])) : ?>
                    <?php foreach ($settings['col3_items'] as $c3_item) : 
                        $raw_url = !empty($c3_item['item_url']['url']) ? $c3_item['item_url']['url'] : '#';
                        // Convert relative shortcuts to full dynamic WordPress URLs
                        if ($raw_url === '/shop' || strpos($raw_url, 'shop') !== false && !strpos($raw_url, 'http')) {
                            $real_url = $shop_page_url;
                        } elseif ($raw_url === '/cart' || strpos($raw_url, 'cart') !== false && !strpos($raw_url, 'http')) {
                            $real_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart');
                        } elseif ($raw_url === '/checkout' || strpos($raw_url, 'checkout') !== false && !strpos($raw_url, 'http')) {
                            $real_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout');
                        } elseif ($raw_url === '/my-account' || strpos($raw_url, 'my-account') !== false && !strpos($raw_url, 'http')) {
                            $real_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account');
                        } elseif ($raw_url === '/wishlist' || strpos($raw_url, 'wishlist') !== false && !strpos($raw_url, 'http')) {
                            $real_url = home_url('/wishlist');
                        } elseif ($raw_url === '/flash-deals' || strpos($raw_url, 'flash-deals') !== false && !strpos($raw_url, 'http')) {
                            $real_url = home_url('/flash-deals');
                        } elseif ($raw_url === '/faq' || strpos($raw_url, 'faq') !== false && !strpos($raw_url, 'http')) {
                            $real_url = home_url('/faq');
                        } elseif ($raw_url === '/contact' || strpos($raw_url, 'contact') !== false && !strpos($raw_url, 'http')) {
                            $real_url = home_url('/contact');
                        } else {
                            $real_url = home_url($raw_url);
                        }
                    ?>
                    <li>
                      <a
                        href="<?php echo esc_url($real_url); ?>"
                        class="block text-stone-400 hover:text-gold-400 transition-colors"
                      ><?php echo esc_html($c3_item['item_title']); ?></a>
                    </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>
              </div>

              <!-- Column 4: Visit or Contact -->
              <div class="lg:col-span-3">
                <h4 class="text-sm font-semibold text-white mb-4">
                  <?php echo esc_html($col4_title); ?>
                </h4>
                <div class="space-y-3 text-sm">
                  <?php if (!empty($address)) : ?>
                  <p class="flex items-start gap-2.5">
                    <i
                      data-lucide="map-pin"
                      class="w-4 h-4 text-gold-400 flex-shrink-0 mt-0.5"
                    ></i><span><?php echo esc_html($address); ?></span>
                  </p>
                  <?php endif; ?>

                  <?php if (!empty($phone)) : ?>
                  <p class="flex items-center gap-2.5">
                    <i
                      data-lucide="phone"
                      class="w-4 h-4 text-gold-400 flex-shrink-0"
                    ></i><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="hover:text-gold-400 transition-colors"><?php echo esc_html($phone); ?></a>
                  </p>
                  <?php endif; ?>

                  <?php if (!empty($email)) : ?>
                  <p class="flex items-center gap-2.5">
                    <i
                      data-lucide="mail"
                      class="w-4 h-4 text-gold-400 flex-shrink-0"
                    ></i><a href="mailto:<?php echo esc_attr($email); ?>" class="hover:text-gold-400 transition-colors"><?php echo esc_html($email); ?></a>
                  </p>
                  <?php endif; ?>

                  <?php if (!empty($hours)) : ?>
                  <p class="text-sm text-stone-500 pl-6">
                    <?php echo esc_html($hours); ?>
                  </p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Bottom Copyright & Payment Trust Icons -->
            <div
              class="pt-7 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-stone-500"
            >
              <p><?php echo esc_html($copyright); ?></p>
              
              <?php if ($show_badges) : ?>
              <div class="flex flex-wrap items-center justify-center gap-2">
                <span
                  class="px-2.5 py-1 rounded border border-stone-800 text-xs font-bold text-stone-300"
                >VISA</span>
                <span
                  class="px-2.5 py-1 rounded border border-stone-800 text-xs font-bold text-stone-300"
                >MASTERCARD</span>
                <span
                  class="px-2.5 py-1 rounded border border-stone-800 text-xs font-bold text-stone-300"
                >bKash</span>
                <span
                  class="px-2.5 py-1 rounded border border-stone-800 text-xs font-bold text-stone-300"
                >Nagad</span>
                <span
                  class="px-2.5 py-1 rounded border border-stone-800 text-xs font-bold text-gold-400"
                >Cash on delivery</span>
              </div>
              <?php endif; ?>
            </div>

          </div>
        </footer>
        <?php
    }
}

