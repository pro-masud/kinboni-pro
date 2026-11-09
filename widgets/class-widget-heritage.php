<?php
/**
 * Kinboni Heritage & Craftsmanship Story - Elementor Widget
 *
 * Implements the luxury editorial "Our Heritage" section featuring
 * ambient gold glows, arched silhouettes, layered editorial photography,
 * a floating Master Guild seal of guarantee, brand storytelling copy,
 * 3-pillar craftsmanship cards, and an archive CTA link.
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
use Elementor\Repeater;
use Elementor\Utils;

class Kinboni_Widget_Heritage extends Widget_Base {

    public function get_name() {
        return 'kinboni_heritage';
    }

    public function get_title() {
        return esc_html__('Heritage & Craftsmanship Story', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-history';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('heritage', 'story', 'about', 'craftsmanship', 'atelier', 'pillars', 'guarantee', 'kinboni');
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: EDITORIAL STORY
        // ==========================================
        $this->start_controls_section(
            'section_editorial_story',
            array(
                'label' => esc_html__('Editorial Story', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'badge_text',
            array(
                'label'       => esc_html__('Subtitle / Badge Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Our Heritage', 'kinboni-core'),
                'placeholder' => esc_html__('Enter subtitle badge', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'badge_tag',
            array(
                'label'   => esc_html__('Subtitle HTML Tag', 'kinboni-core'),
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
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Where Ancient Mastery Meets Contemporary Refinement', 'kinboni-core'),
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
            'show_divider',
            array(
                'label'        => esc_html__('Show Gold Accent Line', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'story_p1',
            array(
                'label'       => esc_html__('Story Paragraph 1', 'kinboni-core'),
                'type'        => Controls_Manager::WYSIWYG,
                'default'     => 'Founded with a singular vision to resist the ephemeral nature of fast consumption, <strong class="font-semibold text-stone-900 dark:text-stone-100">KINBONI Atelier</strong> crafts enduring artifacts designed to acquire character with age.',
                'placeholder' => esc_html__('Enter first paragraph', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'story_p2',
            array(
                'label'       => esc_html__('Story Paragraph 2', 'kinboni-core'),
                'type'        => Controls_Manager::WYSIWYG,
                'default'     => 'Every stitch is cast with waxed French linen thread; every horological movement is individually tuned by master watchmakers; and each perfume is compounded in small batches using sustainably harvested natural botanical extraits.',
                'placeholder' => esc_html__('Enter second paragraph', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: EDITORIAL IMAGE & FLOATING STAMP
        // ==========================================
        $this->start_controls_section(
            'section_image_stamp',
            array(
                'label' => esc_html__('Editorial Image & Guarantee Stamp', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'main_image',
            array(
                'label'   => esc_html__('Craftsmanship Image', 'kinboni-core'),
                'type'    => Controls_Manager::MEDIA,
                'default' => array(
                    'url' => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=1000&q=85',
                ),
            )
        );

        $this->add_control(
            'image_alt',
            array(
                'label'       => esc_html__('Image Alt Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Artisan craftsmanship leather studio', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'image_aspect',
            array(
                'label'   => esc_html__('Image Aspect Ratio', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'aspect-[4/3]' => esc_html__('Standard 4:3 (Default)', 'kinboni-core'),
                    'aspect-[16/10]'=> esc_html__('Cinema 16:10', 'kinboni-core'),
                    'aspect-[1/1]'  => esc_html__('Square 1:1', 'kinboni-core'),
                ),
                'default' => 'aspect-[4/3]',
            )
        );

        $this->add_control(
            'stamp_heading_ctrl',
            array(
                'label'     => esc_html__('Master Guild Floating Stamp', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'show_stamp',
            array(
                'label'        => esc_html__('Show Guarantee Stamp', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'stamp_icon',
            array(
                'label'     => esc_html__('Stamp Lucide Icon', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => 'shield-check',
                'condition' => array('show_stamp' => 'yes'),
            )
        );

        $this->add_control(
            'stamp_title',
            array(
                'label'       => esc_html__('Stamp Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Master Guild', 'kinboni-core'),
                'condition'   => array('show_stamp' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'stamp_subtitle',
            array(
                'label'       => esc_html__('Stamp Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Unconditional Lifetime Guarantee', 'kinboni-core'),
                'condition'   => array('show_stamp' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: 3 PILLARS REPEATER
        // ==========================================
        $this->start_controls_section(
            'section_pillars_content',
            array(
                'label' => esc_html__('Atelier Pillars', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'pillar_number',
            array(
                'label'   => esc_html__('Pillar Number', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => '01',
            )
        );

        $repeater->add_control(
            'pillar_title',
            array(
                'label'   => esc_html__('Pillar Title', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Ethical Origin', 'kinboni-core'),
                'dynamic' => array('active' => true),
            )
        );

        $repeater->add_control(
            'pillar_desc',
            array(
                'label'   => esc_html__('Pillar Description', 'kinboni-core'),
                'type'    => Controls_Manager::TEXTAREA,
                'rows'    => 2,
                'default' => esc_html__('Traceable Tuscan leathers & certified metals.', 'kinboni-core'),
                'dynamic' => array('active' => true),
            )
        );

        $this->add_control(
            'pillars_list',
            array(
                'label'       => esc_html__('Pillar Cards', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ pillar_number }}} - {{{ pillar_title }}}',
                'default'     => array(
                    array(
                        'pillar_number' => '01',
                        'pillar_title'  => esc_html__('Ethical Origin', 'kinboni-core'),
                        'pillar_desc'   => esc_html__('Traceable Tuscan leathers & certified metals.', 'kinboni-core'),
                    ),
                    array(
                        'pillar_number' => '02',
                        'pillar_title'  => esc_html__('Zero Plastic', 'kinboni-core'),
                        'pillar_desc'   => esc_html__('100% biodegradable luxury gift packaging.', 'kinboni-core'),
                    ),
                    array(
                        'pillar_number' => '03',
                        'pillar_title'  => esc_html__('Bespoke Care', 'kinboni-core'),
                        'pillar_desc'   => esc_html__('Lifetime complimentary refurbishment.', 'kinboni-core'),
                    ),
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CTA LINK
        // ==========================================
        $this->start_controls_section(
            'section_cta_content',
            array(
                'label' => esc_html__('Archive Link CTA', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'cta_text',
            array(
                'label'       => esc_html__('CTA Link Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Explore The Atelier Archive', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'cta_url',
            array(
                'label'       => esc_html__('CTA URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => '#featured',
                'default'     => array(
                    'url'         => '#featured',
                    'is_external' => false,
                    'nofollow'    => false,
                ),
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
                'default'     => 'heritage',
                'placeholder' => 'heritage',
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
            'show_ambient_glows',
            array(
                'label'        => esc_html__('Show Gold Ambient Glows', 'kinboni-core'),
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
                    '{{WRAPPER}} .kinboni-heritage-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-heritage-section' => 'background-color: {{VALUE}};',
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
                'label' => esc_html__('Story Typography', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'badge_color',
            array(
                'label'     => esc_html__('Subtitle / Badge Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-heritage-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-heritage-badge',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Main Headline Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-heritage-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-heritage-title',
            )
        );

        $this->add_control(
            'story_p_color',
            array(
                'label'     => esc_html__('Story Text Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-heritage-story-text' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'story_p_typography',
                'selector' => '{{WRAPPER}} .kinboni-heritage-story-text',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: PILLARS & STAMP
        // ==========================================
        $this->start_controls_section(
            'section_style_pillars_stamp',
            array(
                'label' => esc_html__('Pillars & Floating Stamp', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'pillar_num_color',
            array(
                'label'     => esc_html__('Pillar Number Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-pillar-num' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'pillar_card_bg',
            array(
                'label'     => esc_html__('Pillar Card Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-pillar-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'stamp_card_bg',
            array(
                'label'     => esc_html__('Stamp Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-heritage-stamp' => 'background-color: {{VALUE}};',
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

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'heritage';

        $badge_tag = in_array($settings['badge_tag'], array('span', 'p', 'div', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4', 'div'), true) ? $settings['title_tag'] : 'h2';

        $badge_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'heritage_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $title_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'heritage_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');

        $image_url    = !empty($settings['main_image']['url']) ? $settings['main_image']['url'] : 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=1000&q=85';
        $image_alt    = !empty($settings['image_alt']) ? $settings['image_alt'] : esc_html__('Artisan craftsmanship leather studio', 'kinboni-core');
        $image_aspect = !empty($settings['image_aspect']) ? $settings['image_aspect'] : 'aspect-[4/3]';

        $show_stamp     = ('yes' === $settings['show_stamp']);
        $stamp_icon     = !empty($settings['stamp_icon']) ? sanitize_text_field($settings['stamp_icon']) : 'shield-check';
        $stamp_title    = !empty($settings['stamp_title']) ? $settings['stamp_title'] : esc_html__('Master Guild', 'kinboni-core');
        $stamp_subtitle = !empty($settings['stamp_subtitle']) ? $settings['stamp_subtitle'] : esc_html__('Unconditional Lifetime Guarantee', 'kinboni-core');

        $pillars = !empty($settings['pillars_list']) && is_array($settings['pillars_list']) ? $settings['pillars_list'] : array();

        $cta_text    = !empty($settings['cta_text']) ? $settings['cta_text'] : esc_html__('Explore The Atelier Archive', 'kinboni-core');
        $cta_url     = !empty($settings['cta_url']['url']) ? $settings['cta_url']['url'] : '#featured';
        $target_attr = !empty($settings['cta_url']['is_external']) ? ' target="_blank"' : '';
        $rel_attr    = !empty($settings['cta_url']['nofollow']) ? ' rel="nofollow noopener noreferrer"' : (!empty($settings['cta_url']['is_external']) ? ' rel="noopener noreferrer"' : '');
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-heritage-section relative overflow-hidden py-16 sm:py-24 bg-stone-100/60 dark:bg-stone-900/40 border-t border-b border-stone-200/70 dark:border-stone-800/80 scroll-mt-24">
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
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      <div class="lg:col-span-6 relative">
        <div class="<?php echo esc_attr($image_aspect); ?> rounded-2xl overflow-hidden shadow-2xl border border-stone-200/80 dark:border-stone-800 bg-stone-200 dark:bg-stone-900">
          <img loading="lazy" src="<?php echo esc_url($image_url); ?>" 
            alt="<?php echo esc_attr($image_alt); ?>" 
            class="w-full h-full object-cover object-center" 
            loading="lazy"
          />
        </div>

        <?php if ($show_stamp) : ?>
        <div class="kinboni-heritage-stamp absolute -bottom-6 -right-6 hidden sm:block p-5 rounded-2xl bg-white dark:bg-stone-900 shadow-xl border border-stone-200 dark:border-stone-800 max-w-[200px] text-center">
          <i data-lucide="<?php echo esc_attr($stamp_icon); ?>" class="w-8 h-8 text-gold-500 mx-auto mb-1"></i>
          <p class="font-serif text-sm font-bold text-stone-900 dark:text-white">
            <?php echo esc_html($stamp_title); ?>
          </p>
          <p class="text-sm uppercase tracking-wider text-stone-600 dark:text-stone-300">
            <?php echo esc_html($stamp_subtitle); ?>
          </p>
        </div>
        <?php endif; ?>
      </div>

      <div class="lg:col-span-6 space-y-6">
        <?php if (!empty($badge_text)) : ?>
        <<?php echo $badge_tag; ?> class="kinboni-heritage-badge text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400 inline-block">
          <?php echo esc_html($badge_text); ?>
        </<?php echo $badge_tag; ?>>
        <?php endif; ?>

        <?php if (!empty($title_text)) : ?>
        <<?php echo $title_tag; ?> class="kinboni-heritage-title font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white leading-tight">
          <?php echo esc_html($title_text); ?>
        </<?php echo $title_tag; ?>>
        <?php endif; ?>

        <?php if ('yes' === $settings['show_divider']) : ?>
        <div class="w-12 h-0.5 bg-gold-500"></div>
        <?php endif; ?>

        <?php if (!empty($settings['story_p1'])) : ?>
        <div class="kinboni-heritage-story-text text-stone-600 dark:text-stone-300 text-sm sm:text-base leading-relaxed font-normal">
          <?php echo wp_kses_post($settings['story_p1']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($settings['story_p2'])) : ?>
        <div class="kinboni-heritage-story-text text-stone-600 dark:text-stone-300 text-sm sm:text-base leading-relaxed font-normal">
          <?php echo wp_kses_post($settings['story_p2']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($pillars)) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
          <?php foreach ($pillars as $pillar) : ?>
          <div class="kinboni-pillar-card p-4 rounded-xl bg-white dark:bg-stone-900/80 border border-stone-200/80 dark:border-stone-800 shadow-sm">
            <?php if (!empty($pillar['pillar_number'])) : ?>
            <span class="kinboni-pillar-num text-gold-600 font-serif text-xl font-bold">
              <?php echo esc_html($pillar['pillar_number']); ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($pillar['pillar_title'])) : ?>
            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-white mt-1">
              <?php echo esc_html($pillar['pillar_title']); ?>
            </h4>
            <?php endif; ?>
            <?php if (!empty($pillar['pillar_desc'])) : ?>
            <p class="text-[11px] text-stone-600 dark:text-stone-300 mt-1 leading-relaxed">
              <?php echo esc_html($pillar['pillar_desc']); ?>
            </p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($cta_text)) : ?>
        <div class="pt-2">
          <a href="<?php echo esc_url($cta_url); ?>" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gold-700 dark:text-gold-400 hover:text-gold-700 dark:hover:text-gold-300 transition-colors"<?php echo $target_attr . $rel_attr; ?>>
            <span><?php echo esc_html($cta_text); ?></span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
        <?php
    }
}

