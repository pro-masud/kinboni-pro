<?php
/**
 * Kinboni Private Circle Newsletter - Elementor Widget
 *
 * Implements the luxury editorial "Join The Kinboni Circle" newsletter section
 * featuring gold badges, mail icon, serif typography, responsive email subscription form,
 * instant client-side validation, success notification, and privacy disclaimer.
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

class Kinboni_Widget_Newsletter extends Widget_Base {

    public function get_name() {
        return 'kinboni_newsletter';
    }

    public function get_title() {
        return esc_html__('Private Circle Newsletter', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-envelope';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('newsletter', 'subscribe', 'circle', 'email', 'invitation', 'privilege', 'atelier', 'kinboni');
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: EDITORIAL CONTENT
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
                'label'       => esc_html__('Badge Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Private Invitations', 'kinboni-core'),
                'placeholder' => esc_html__('Private Invitations', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'badge_icon',
            array(
                'label'   => esc_html__('Badge Lucide Icon', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => 'mail',
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
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Join The Kinboni Circle', 'kinboni-core'),
                'placeholder' => esc_html__('Enter headline', 'kinboni-core'),
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
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Subscribe to receive confidential notifications regarding private archive sales, limited numbered releases, and bespoke salon previews.', 'kinboni-core'),
                'placeholder' => esc_html__('Enter description', 'kinboni-core'),
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

        $this->add_control(
            'disclaimer_text',
            array(
                'label'       => esc_html__('Bottom Disclaimer / Note', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('We respect your discretion. You may withdraw membership at any instant.', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: FORM SETTINGS & MESSAGES
        // ==========================================
        $this->start_controls_section(
            'section_form_settings',
            array(
                'label' => esc_html__('Form & Notification', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'input_placeholder',
            array(
                'label'       => esc_html__('Input Placeholder Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Enter your confidential email...', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'btn_submit_text',
            array(
                'label'       => esc_html__('Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Subscribe', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'success_message',
            array(
                'label'       => esc_html__('Success Message (HTML Allowed)', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => '✨ Welcome to The Kinboni Circle! Your VIP invitation and private 20% privilege code (<strong>LUXE20</strong>) have been delivered.',
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'error_message',
            array(
                'label'       => esc_html__('Error Message (Invalid Email)', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Please enter a valid email address.', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'section_html_id',
            array(
                'label'       => esc_html__('Section HTML ID', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'newsletter-section',
                'placeholder' => 'newsletter-section',
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
                    '{{WRAPPER}} .kinboni-newsletter-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-newsletter-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: TYPOGRAPHY
        // ==========================================
        $this->start_controls_section(
            'section_style_typography',
            array(
                'label' => esc_html__('Typography & Colors', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'badge_color',
            array(
                'label'     => esc_html__('Badge Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-news-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-news-badge',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Headline Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-news-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-news-title',
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-news-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .kinboni-news-desc',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: BUTTON & INPUT
        // ==========================================
        $this->start_controls_section(
            'section_style_btn',
            array(
                'label' => esc_html__('Form Input & Button', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'btn_bg_color',
            array(
                'label'     => esc_html__('Button Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-news-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'btn_text_color',
            array(
                'label'     => esc_html__('Button Text Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-news-btn' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'btn_box_shadow',
                'selector' => '{{WRAPPER}} .kinboni-news-btn',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'newsletter-section';

        $badge_tag = in_array($settings['badge_tag'], array('span', 'div', 'p', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4', 'div'), true) ? $settings['title_tag'] : 'h2';
        $desc_tag  = in_array($settings['desc_tag'], array('p', 'div', 'span'), true) ? $settings['desc_tag'] : 'p';

        $badge_text  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'newsletter_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $badge_icon  = !empty($settings['badge_icon']) ? sanitize_text_field($settings['badge_icon']) : 'mail';
        $title_text  = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'newsletter_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');
        $desc_text   = !empty($settings['description_text']) ? $settings['description_text'] : '';
        $disclaimer  = !empty($settings['disclaimer_text']) ? $settings['disclaimer_text'] : '';

        $vip_code    = class_exists('Kinboni_Content') ? Kinboni_Content::get('vip_coupon_code', 'LUXE20') : 'LUXE20';
        $placeholder = !empty($settings['input_placeholder']) ? $settings['input_placeholder'] : esc_html__('Enter your confidential email...', 'kinboni-core');
        $btn_text    = !empty($settings['btn_submit_text']) ? $settings['btn_submit_text'] : esc_html__('Subscribe', 'kinboni-core');
        $default_success = sprintf(esc_html__('✨ Welcome to The Kinboni Circle! Your VIP invitation and private 20%% privilege code (%s) have been delivered.', 'kinboni-core'), $vip_code);
        $success_msg = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'success_message', 'newsletter_success', $default_success, $this) : (!empty($settings['success_message']) ? $settings['success_message'] : $default_success);
        $error_msg   = !empty($settings['error_message']) ? $settings['error_message'] : esc_html__('Please enter a valid email address.', 'kinboni-core');
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-newsletter-section py-16 sm:py-20 bg-stone-950 text-stone-100 border-t border-stone-800">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <?php if (!empty($badge_text)) : ?>
    <<?php echo $badge_tag; ?> class="kinboni-news-badge inline-flex items-center gap-2 text-xs uppercase font-bold tracking-[0.25em] text-gold-400 mb-3">
      <?php if (!empty($badge_icon)) : ?>
      <i data-lucide="<?php echo esc_attr($badge_icon); ?>" class="w-4 h-4"></i>
      <?php endif; ?>
      <span><?php echo esc_html($badge_text); ?></span>
    </<?php echo $badge_tag; ?>>
    <?php endif; ?>

    <?php if (!empty($title_text)) : ?>
    <<?php echo $title_tag; ?> class="kinboni-news-title font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-white mb-4">
      <?php echo esc_html($title_text); ?>
    </<?php echo $title_tag; ?>>
    <?php endif; ?>

    <?php if (!empty($desc_text)) : ?>
    <<?php echo $desc_tag; ?> class="kinboni-news-desc text-sm sm:text-base text-stone-400 font-normal max-w-xl mx-auto mb-8 leading-relaxed">
      <?php echo esc_html($desc_text); ?>
    </<?php echo $desc_tag; ?>>
    <?php endif; ?>

    <form 
      id="newsletter-form" 
      class="kinboni-newsletter-form max-w-md mx-auto" 
      novalidate
      data-success="<?php echo esc_attr($success_msg); ?>"
      data-error="<?php echo esc_attr($error_msg); ?>"
    >
      <div style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;" aria-hidden="true"><input type="text" name="kinboni_hp" value="" tabindex="-1" autocomplete="off"></div>
      <div class="flex flex-col sm:flex-row gap-3">
        <input
          type="email"
          id="newsletter-email"
          placeholder="<?php echo esc_attr($placeholder); ?>"
          aria-label="<?php echo esc_attr($placeholder); ?>"
          class="newsletter-email-input flex-grow px-5 py-3.5 rounded-xl bg-stone-900 border border-stone-700 text-stone-100 text-xs placeholder:text-stone-500 focus:outline-none focus:border-gold-500 transition-colors"
          required
        />
        <button
          type="submit"
          class="kinboni-news-btn px-7 py-3.5 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-md transition-all active:scale-95 flex-shrink-0"
        >
          <?php echo esc_html($btn_text); ?>
        </button>
      </div>
      <div id="newsletter-feedback" class="newsletter-feedback hidden"></div>
    </form>

    <?php if (!empty($disclaimer)) : ?>
    <p class="mt-4 text-[11px] text-stone-500">
      <?php echo esc_html($disclaimer); ?>
    </p>
    <?php endif; ?>
  </div>
</section>
        <?php
    }
}

