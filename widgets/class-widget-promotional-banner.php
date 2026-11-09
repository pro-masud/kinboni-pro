<?php
/**
 * Kinboni Promotional Privilege Banner - Elementor Widget
 *
 * Implements the luxury editorial "Promotional Banner" section featuring
 * ambient gold glows, radial gold dot matrix, customizable editorial copy,
 * click-to-copy promo code box with instant feedback, and call-to-action button.
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

class Kinboni_Widget_Promotional_Banner extends Widget_Base {

    public function get_name() {
        return 'kinboni_promotional_banner';
    }

    public function get_title() {
        return esc_html__('Promotional Privilege Banner', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-call-to-action';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('banner', 'promo', 'promotion', 'coupon', 'privilege', 'discount', 'archive', 'call to action', 'kinboni');
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: EDITORIAL COPY
        // ==========================================
        $this->start_controls_section(
            'section_editorial_content',
            array(
                'label' => esc_html__('Editorial Copy', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'badge_text',
            array(
                'label'       => esc_html__('Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Private Mid-Season Archive', 'kinboni-core'),
                'placeholder' => esc_html__('Enter badge label', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'badge_tag',
            array(
                'label'   => esc_html__('Badge HTML Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'span' => 'span',
                    'div'  => 'div',
                    'p'    => 'p',
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
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Claim 20% Privilege on Exceptional Handcrafted Goods', 'kinboni-core'),
                'placeholder' => esc_html__('Enter main headline', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'title_tag',
            array(
                'label'   => esc_html__('Title HTML Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h1'  => 'h1',
                    'h2'  => 'h2',
                    'h3'  => 'h3',
                    'h4'  => 'h4',
                    'div' => 'div',
                ),
                'default' => 'h2',
            )
        );

        $this->add_control(
            'description_text',
            array(
                'label'       => esc_html__('Description Text', 'kinboni-core'),
                'type'        => Controls_Manager::WYSIWYG,
                'default'     => 'Enter private code <strong class="text-gold-400 font-bold tracking-wider">LUXE20</strong> at checkout to redeem complimentary discount privileges across all full-grain leather pieces and Swiss chronographs.',
                'placeholder' => esc_html__('Enter description text (HTML supported)', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'desc_tag',
            array(
                'label'   => esc_html__('Description HTML Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'p'    => 'p',
                    'div'  => 'div',
                    'span' => 'span',
                ),
                'default' => 'p',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: PROMO CODE & CTA
        // ==========================================
        $this->start_controls_section(
            'section_promobox_content',
            array(
                'label' => esc_html__('Promo Code Box & CTA', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'box_label',
            array(
                'label'       => esc_html__('Box Top Label', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Privilege Code', 'kinboni-core'),
                'placeholder' => esc_html__('e.g. Privilege Code', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'promo_code',
            array(
                'label'       => esc_html__('Coupon / Promo Code', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'LUXE20',
                'placeholder' => 'LUXE20',
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'copied_notice_text',
            array(
                'label'       => esc_html__('Copied Toast Notification', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Privilege code "%s" copied! Use at checkout for discount.', 'kinboni-core'),
                'description' => esc_html__('Use %s for dynamic code insertion', 'kinboni-core'),
            )
        );

        $this->add_control(
            'btn_cta_text',
            array(
                'label'       => esc_html__('CTA Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Shop Private Archive', 'kinboni-core'),
                'placeholder' => esc_html__('Shop Private Archive', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'cta_action_type',
            array(
                'label'   => esc_html__('CTA Action Type', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'scroll_bestsellers' => esc_html__('Filter Sale & Scroll to Best Sellers (#bestsellers)', 'kinboni-core'),
                    'link'               => esc_html__('Custom Link / Shop Page', 'kinboni-core'),
                ),
                'default' => 'scroll_bestsellers',
            )
        );

        $this->add_control(
            'cta_link',
            array(
                'label'       => esc_html__('CTA Link', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-domain.com/shop', 'kinboni-core'),
                'default'     => array(
                    'url'         => '#bestsellers',
                    'is_external' => false,
                    'nofollow'    => false,
                ),
                'condition'   => array('cta_action_type' => 'link'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: ATMOSPHERE & BACKGROUND
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
                'default'     => 'promotional-banner',
                'placeholder' => 'promotional-banner',
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
            'show_ambient_glow',
            array(
                'label'        => esc_html__('Show Gold Ambient Glow', 'kinboni-core'),
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
                'label' => esc_html__('Section Styling', 'kinboni-core'),
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
                    '{{WRAPPER}} .kinboni-promo-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: EDITORIAL TYPOGRAPHY & COLORS
        // ==========================================
        $this->start_controls_section(
            'section_style_editorial',
            array(
                'label' => esc_html__('Editorial Styling', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'badge_heading_style',
            array(
                'label'     => esc_html__('Badge Pill', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'badge_text_color',
            array(
                'label'     => esc_html__('Badge Text Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'badge_bg_color',
            array(
                'label'     => esc_html__('Badge Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-badge' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'badge_border_color',
            array(
                'label'     => esc_html__('Badge Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-badge' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-promo-badge',
            )
        );

        $this->add_control(
            'title_heading_style',
            array(
                'label'     => esc_html__('Main Headline', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Headline Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-promo-title',
            )
        );

        $this->add_control(
            'desc_heading_style',
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
                    '{{WRAPPER}} .kinboni-promo-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .kinboni-promo-desc',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: PROMO CARD & CTA
        // ==========================================
        $this->start_controls_section(
            'section_style_promobox',
            array(
                'label' => esc_html__('Promo Card & Buttons', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_bg_color',
            array(
                'label'     => esc_html__('Card Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_border_color',
            array(
                'label'     => esc_html__('Card Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-card' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .kinboni-promo-card',
            )
        );

        $this->add_control(
            'cta_btn_heading_style',
            array(
                'label'     => esc_html__('CTA Button', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'cta_btn_bg_color',
            array(
                'label'     => esc_html__('CTA Button Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-cta' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'cta_btn_text_color',
            array(
                'label'     => esc_html__('CTA Button Text Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-promo-cta' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'promotional-banner';

        $badge_tag = in_array($settings['badge_tag'], array('span', 'div', 'p', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4', 'div'), true) ? $settings['title_tag'] : 'h2';
        $desc_tag  = in_array($settings['desc_tag'], array('p', 'div', 'span'), true) ? $settings['desc_tag'] : 'p';

        $badge_text  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'promo_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $title_text  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'promo_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');
        $desc_text   = !empty($settings['description_text']) ? $settings['description_text'] : '';
        $box_label   = !empty($settings['box_label']) ? $settings['box_label'] : esc_html__('Privilege Code', 'kinboni-core');
        $promo_code  = class_exists('Kinboni_Content') ? sanitize_text_field(Kinboni_Content::resolve($settings, 'promo_code', 'vip_coupon_code', 'LUXE20', $this)) : (!empty($settings['promo_code']) ? sanitize_text_field($settings['promo_code']) : 'LUXE20');
        $cta_text    = !empty($settings['btn_cta_text']) ? $settings['btn_cta_text'] : esc_html__('Shop Private Archive', 'kinboni-core');
        $toast_notice= !empty($settings['copied_notice_text']) ? esc_attr(sprintf($settings['copied_notice_text'], $promo_code)) : sprintf(esc_attr__('Privilege code "%s" copied!', 'kinboni-core'), $promo_code);

        $action_type = !empty($settings['cta_action_type']) ? $settings['cta_action_type'] : 'scroll_bestsellers';
        $cta_onclick = '';
        $cta_href    = '#bestsellers';
        $target_attr = '';
        $rel_attr    = '';

        if ('scroll_bestsellers' === $action_type) {
            $cta_onclick = "if (window.ProductRenderer && typeof window.ProductRenderer.renderBestSellers === 'function') { ProductRenderer.renderBestSellers('sale'); } const el = document.getElementById('bestsellers'); if (el) { el.scrollIntoView({ behavior: 'smooth' }); }";
            $cta_href    = '#bestsellers';
        } else {
            $cta_href    = !empty($settings['cta_link']['url']) ? $settings['cta_link']['url'] : '#bestsellers';
            $target_attr = !empty($settings['cta_link']['is_external']) ? ' target="_blank"' : '';
            $rel_attr    = !empty($settings['cta_link']['nofollow']) ? ' rel="nofollow noopener noreferrer"' : (!empty($settings['cta_link']['is_external']) ? ' rel="noopener noreferrer"' : '');
        }
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-promo-section relative py-20 bg-stone-900 text-stone-100 overflow-hidden scroll-mt-24">
  <?php if ('yes' === $settings['show_radial_dots']) : ?>
  <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
  <?php endif; ?>

  <?php if ('yes' === $settings['show_ambient_glow']) : ?>
  <div class="absolute top-0 right-0 w-80 h-80 bg-gold-500/10 rounded-full blur-3xl pointer-events-none"></div>
  <?php endif; ?>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
      <div class="lg:col-span-8 text-center lg:text-left space-y-4">
        <?php if (!empty($badge_text)) : ?>
        <<?php echo $badge_tag; ?> class="kinboni-promo-badge inline-block px-3 py-1 rounded-full bg-gold-500/20 border border-gold-500/40 text-gold-300 text-xs font-bold uppercase tracking-widest">
          <?php echo esc_html($badge_text); ?>
        </<?php echo $badge_tag; ?>>
        <?php endif; ?>

        <?php if (!empty($title_text)) : ?>
        <<?php echo $title_tag; ?> class="kinboni-promo-title font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-white leading-tight">
          <?php echo esc_html($title_text); ?>
        </<?php echo $title_tag; ?>>
        <?php endif; ?>

        <?php if (!empty($desc_text)) : ?>
        <<?php echo $desc_tag; ?> class="kinboni-promo-desc text-stone-400 text-sm sm:text-base max-w-2xl font-normal leading-relaxed">
          <?php echo wp_kses_post($desc_text); ?>
        </<?php echo $desc_tag; ?>>
        <?php endif; ?>
      </div>

      <div class="lg:col-span-4 flex flex-col items-center lg:items-end justify-center">
        <div class="kinboni-promo-card p-6 sm:p-7 rounded-2xl bg-stone-800/90 backdrop-blur-md border border-stone-700/80 text-center w-full max-w-sm shadow-2xl">
          <span class="text-xs uppercase tracking-widest text-stone-400 font-semibold block mb-2">
            <?php echo esc_html($box_label); ?>
          </span>

          <button
            type="button"
            class="btn-copy-promo-code w-full py-3 px-4 rounded-xl bg-stone-950 border border-gold-500/60 text-gold-400 font-mono text-lg font-bold tracking-widest mb-4 flex items-center justify-center gap-2 hover:bg-stone-900 hover:border-gold-400 transition-all group active:scale-95"
            data-code="<?php echo esc_attr($promo_code); ?>"
            data-notice="<?php echo esc_attr($toast_notice); ?>"
            title="<?php esc_attr_e('Click to copy code', 'kinboni-core'); ?>"
          >
            <span class="promo-code-text"><?php echo esc_html($promo_code); ?></span>
            <i data-lucide="copy" class="promo-copy-icon w-4 h-4 text-stone-500 group-hover:text-gold-400 transition-colors"></i>
          </button>

          <?php if ('scroll_bestsellers' === $action_type) : ?>
          <button
            type="button"
            onclick="<?php echo esc_attr($cta_onclick); ?>"
            class="kinboni-promo-cta w-full inline-block py-3 px-6 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-md hover:shadow-gold-500/25 transition-all text-center active:scale-95"
          >
            <?php echo esc_html($cta_text); ?>
          </button>
          <?php else : ?>
          <a
            href="<?php echo esc_url($cta_href); ?>"
            class="kinboni-promo-cta w-full inline-block py-3 px-6 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-md hover:shadow-gold-500/25 transition-all text-center active:scale-95"
            <?php echo $target_attr . $rel_attr; ?>
          >
            <?php echo esc_html($cta_text); ?>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
        <?php
    }
}

