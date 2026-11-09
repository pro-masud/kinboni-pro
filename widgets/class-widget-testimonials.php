<?php
/**
 * Kinboni Patron Acclaim Testimonials Slider - Elementor Widget
 *
 * Implements the luxury editorial "Testimonials" section featuring
 * ambient gold glows, architectural geometry, Swiper carousel with custom
 * navigation arrows, star ratings, verified patron pills, and editorial patron cards.
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

class Kinboni_Widget_Testimonials extends Widget_Base {

    public function get_name() {
        return 'kinboni_testimonials';
    }

    public function get_title() {
        return esc_html__('Patron Acclaim Testimonials Slider', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-testimonial-carousel';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('testimonial', 'reviews', 'patron', 'acclaim', 'quotes', 'slider', 'carousel', 'swiper', 'kinboni');
    }

    public function get_script_depends() {
        return array('swiper-js', 'lucide-icons', 'kinboni-core-js');
    }

    public function get_style_depends() {
        return array('swiper-css', 'kinboni-core-style');
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
                'default'     => esc_html__('Patron Acclaim', 'kinboni-core'),
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
                'default'     => esc_html__('Words from the Discerning', 'kinboni-core'),
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
                'default'     => esc_html__('Trusted by connoisseurs, collectors, and taste-makers across Dhaka, London, Zurich, and Dubai.', 'kinboni-core'),
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

        $this->add_control(
            'show_navigation',
            array(
                'label'        => esc_html__('Show Navigation Arrows', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: TESTIMONIALS ITEMS
        // ==========================================
        $this->start_controls_section(
            'section_testimonials_content',
            array(
                'label' => esc_html__('Testimonial Cards', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'client_name',
            array(
                'label'       => esc_html__('Patron Name', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Tahmid Rahman', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'client_role',
            array(
                'label'       => esc_html__('Location & Role', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Dhaka • Collector', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'acquired_item',
            array(
                'label'       => esc_html__('Acquired Creation', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Acquired: Chronos Royale', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'quote',
            array(
                'label'       => esc_html__('Testimonial Quote', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 4,
                'default'     => esc_html__('"The Chronos Royale feels indistinguishable from Swiss timepieces four times its price. The weight, the sapphire anti-reflection, and the hand-finished indices are sublime."', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'client_image',
            array(
                'label'   => esc_html__('Patron Avatar', 'kinboni-core'),
                'type'    => Controls_Manager::MEDIA,
                'default' => array(
                    'url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
                ),
            )
        );

        $repeater->add_control(
            'rating',
            array(
                'label'   => esc_html__('Star Rating', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    '5' => '★★★★★ (5/5)',
                    '4' => '★★★★☆ (4/5)',
                    '3' => '★★★☆☆ (3/5)',
                ),
                'default' => '5',
            )
        );

        $repeater->add_control(
            'is_verified',
            array(
                'label'        => esc_html__('Verified Patron Badge', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'testimonials_list',
            array(
                'label'       => esc_html__('Testimonials', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ client_name }}}',
                'default'     => array(
                    array(
                        'client_name'   => esc_html__('Tahmid Rahman', 'kinboni-core'),
                        'client_role'   => esc_html__('Dhaka • Collector', 'kinboni-core'),
                        'acquired_item' => esc_html__('Acquired: Chronos Royale', 'kinboni-core'),
                        'quote'         => esc_html__('"The Chronos Royale feels indistinguishable from Swiss timepieces four times its price. The weight, the sapphire anti-reflection, and the hand-finished indices are sublime."', 'kinboni-core'),
                        'client_image'  => array('url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80'),
                        'rating'        => '5',
                        'is_verified'   => 'yes',
                    ),
                    array(
                        'client_name'   => esc_html__('Elena Rostova', 'kinboni-core'),
                        'client_role'   => esc_html__('London • Creative Director', 'kinboni-core'),
                        'acquired_item' => esc_html__('Acquired: Saddle Briefcase', 'kinboni-core'),
                        'quote'         => esc_html__('"The Atelier Saddle Briefcase has accompanied me to board meetings in London and Singapore. The patina it has developed over six months is utterly breathtaking."', 'kinboni-core'),
                        'client_image'  => array('url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80'),
                        'rating'        => '5',
                        'is_verified'   => 'yes',
                    ),
                    array(
                        'client_name'   => esc_html__('Zayd Al-Mansoor', 'kinboni-core'),
                        'client_role'   => esc_html__('Dubai • Connoisseur', 'kinboni-core'),
                        'acquired_item' => esc_html__('Acquired: Nocturne Oud 100ml', 'kinboni-core'),
                        'quote'         => esc_html__('"Nocturne Oud is pure alchemy. The projection is commanding yet never suffocating. The presentation box arrived with white-glove courier precision."', 'kinboni-core'),
                        'client_image'  => array('url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80'),
                        'rating'        => '5',
                        'is_verified'   => 'yes',
                    ),
                    array(
                        'client_name'   => esc_html__('Farhana Chowdhury', 'kinboni-core'),
                        'client_role'   => esc_html__('Chittagong • Architect', 'kinboni-core'),
                        'acquired_item' => esc_html__('Acquired: Aurelia Solis', 'kinboni-core'),
                        'quote'         => esc_html__('"The Aurelia Solis shades are a masterclass in Italian acetate craftsmanship. The packaging, engraved authenticity card, and cash-on-delivery service were unmatched."', 'kinboni-core'),
                        'client_image'  => array('url' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80'),
                        'rating'        => '5',
                        'is_verified'   => 'yes',
                    ),
                    array(
                        'client_name'   => esc_html__('Jean-Luc Mercier', 'kinboni-core'),
                        'client_role'   => esc_html__('Geneva • Horology Specialist', 'kinboni-core'),
                        'acquired_item' => esc_html__('Acquired: Bifold Wallet', 'kinboni-core'),
                        'quote'         => esc_html__('"Exquisite attention to detail. The saddle stitching on the French calfskin matches the precision of heritage Genevan watchmakers. Truly quiet luxury."', 'kinboni-core'),
                        'client_image'  => array('url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=150&q=80'),
                        'rating'        => '5',
                        'is_verified'   => 'yes',
                    ),
                    array(
                        'client_name'   => esc_html__('Nusrat Jahan', 'kinboni-core'),
                        'client_role'   => esc_html__('Sylhet • Verified Patron', 'kinboni-core'),
                        'acquired_item' => esc_html__('Acquired: Cashmere Wrap', 'kinboni-core'),
                        'quote'         => esc_html__('"The Cashmere wrap is unimaginably soft and ethereal. Ordered with 3 simple details and received in Sylhet in under 48 hours in a silk-lined atelier box."', 'kinboni-core'),
                        'client_image'  => array('url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=150&q=80'),
                        'rating'        => '5',
                        'is_verified'   => 'yes',
                    ),
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: SLIDER SETTINGS
        // ==========================================
        $this->start_controls_section(
            'section_slider_settings',
            array(
                'label' => esc_html__('Slider Settings', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'autoplay',
            array(
                'label'        => esc_html__('Autoplay', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'autoplay_delay',
            array(
                'label'     => esc_html__('Autoplay Delay (ms)', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 4500,
                'step'      => 500,
                'min'       => 2000,
                'max'       => 15000,
                'condition' => array('autoplay' => 'yes'),
            )
        );

        $this->add_control(
            'loop',
            array(
                'label'        => esc_html__('Loop Slides', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'speed',
            array(
                'label'   => esc_html__('Slide Transition Speed (ms)', 'kinboni-core'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 750,
                'step'    => 50,
                'min'     => 300,
                'max'     => 3000,
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
                'default'     => 'testimonials',
                'placeholder' => 'testimonials',
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
                    '{{WRAPPER}} .kinboni-testimonials-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-testimonials-section' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .kinboni-t-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-t-badge',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Main Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-t-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-t-title',
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-t-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .kinboni-t-desc',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: CARDS
        // ==========================================
        $this->start_controls_section(
            'section_style_cards',
            array(
                'label' => esc_html__('Testimonial Cards', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .kinboni-testimonial-card',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'testimonials';

        $badge_tag = in_array($settings['badge_tag'], array('span', 'p', 'div', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4', 'div'), true) ? $settings['title_tag'] : 'h2';
        $desc_tag  = in_array($settings['desc_tag'], array('p', 'div', 'span'), true) ? $settings['desc_tag'] : 'p';

        $badge_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'testimonials_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $title_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'testimonials_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');

        $testimonials = !empty($settings['testimonials_list']) && is_array($settings['testimonials_list']) ? $settings['testimonials_list'] : array();
        if (empty($testimonials)) {
            return;
        }

        $autoplay_data = ('yes' === $settings['autoplay']) ? 'true' : 'false';
        $delay_data    = !empty($settings['autoplay_delay']) ? intval($settings['autoplay_delay']) : 4500;
        $loop_data     = ('yes' === $settings['loop']) ? 'true' : 'false';
        $speed_data    = !empty($settings['speed']) ? intval($settings['speed']) : 750;
        ?>
<section 
  id="<?php echo esc_attr($section_id); ?>" 
  class="kinboni-testimonials-section relative py-16 sm:py-24 scroll-mt-24 overflow-hidden"
  data-autoplay="<?php echo esc_attr($autoplay_data); ?>"
  data-delay="<?php echo esc_attr($delay_data); ?>"
  data-loop="<?php echo esc_attr($loop_data); ?>"
  data-speed="<?php echo esc_attr($speed_data); ?>"
>
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
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 sm:mb-14 gap-6">
      <div>
        <?php if (!empty($badge_text)) : ?>
        <<?php echo $badge_tag; ?> class="kinboni-t-badge text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400 inline-block">
          <?php echo esc_html($badge_text); ?>
        </<?php echo $badge_tag; ?>>
        <?php endif; ?>

        <?php if (!empty($title_text)) : ?>
        <<?php echo $title_tag; ?> class="kinboni-t-title font-serif text-3xl sm:text-4xl font-medium text-stone-900 dark:text-white mt-1">
          <?php echo esc_html($title_text); ?>
        </<?php echo $title_tag; ?>>
        <?php endif; ?>

        <?php if ('yes' === $settings['show_divider']) : ?>
        <div class="w-12 h-0.5 bg-gold-500 mt-3 mb-3"></div>
        <?php endif; ?>

        <?php if (!empty($settings['description_text'])) : ?>
        <<?php echo $desc_tag; ?> class="kinboni-t-desc text-sm text-stone-600 dark:text-stone-400 font-normal max-w-xl">
          <?php echo esc_html($settings['description_text']); ?>
        </<?php echo $desc_tag; ?>>
        <?php endif; ?>
      </div>

      <?php if ('yes' === $settings['show_navigation']) : ?>
      <div class="flex items-center gap-3">
        <button
          id="testimonial-prev-btn"
          type="button"
          class="testimonial-prev-btn w-11 h-11 rounded-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 flex items-center justify-center text-stone-700 dark:text-stone-300 hover:border-gold-500 hover:text-gold-600 dark:hover:border-gold-400 dark:hover:text-gold-400 hover:scale-105 active:scale-95 transition-all shadow-sm"
          aria-label="<?php esc_attr_e('Previous testimonial', 'kinboni-core'); ?>"
        >
          <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </button>
        <button
          id="testimonial-next-btn"
          type="button"
          class="testimonial-next-btn w-11 h-11 rounded-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 flex items-center justify-center text-stone-700 dark:text-stone-300 hover:border-gold-500 hover:text-gold-600 dark:hover:border-gold-400 dark:hover:text-gold-400 hover:scale-105 active:scale-95 transition-all shadow-sm"
          aria-label="<?php esc_attr_e('Next testimonial', 'kinboni-core'); ?>"
        >
          <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </button>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="relative">
      <div class="swiper testimonial-swiper">
        <div class="swiper-wrapper">
          <?php foreach ($testimonials as $item) :
            $name     = !empty($item['client_name']) ? $item['client_name'] : '';
            $role     = !empty($item['client_role']) ? $item['client_role'] : '';
            $acquired = !empty($item['acquired_item']) ? $item['acquired_item'] : '';
            $quote    = !empty($item['quote']) ? $item['quote'] : '';
            $avatar   = !empty($item['client_image']['url']) ? $item['client_image']['url'] : Utils::get_placeholder_image_src();
            $rating   = !empty($item['rating']) ? intval($item['rating']) : 5;
            $verified = ('yes' === $item['is_verified']);
          ?>
          <div class="swiper-slide h-auto">
            <div class="kinboni-testimonial-card w-full h-full p-7 sm:p-8 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 shadow-sm hover:shadow-xl hover:border-gold-500/50 dark:hover:border-gold-500/40 transition-all duration-300 flex flex-col justify-between group">
              <div>
                <div class="flex items-center justify-between mb-4">
                  <div class="flex text-amber-400">
                    <?php for ($i = 0; $i < $rating; $i++) : ?>
                      <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <?php endfor; ?>
                  </div>
                  <?php if ($verified) : ?>
                  <span class="inline-flex items-center gap-1 text-xs uppercase font-bold tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                    <i data-lucide="check" class="w-3 h-3"></i> <?php esc_html_e('Verified', 'kinboni-core'); ?>
                  </span>
                  <?php endif; ?>
                </div>
                <p class="font-serif text-lg sm:text-xl italic text-stone-800 dark:text-stone-200 leading-relaxed mb-6 font-normal">
                  <?php echo esc_html($quote); ?>
                </p>
              </div>
              <div class="flex items-center gap-4 pt-4 border-t border-stone-100 dark:border-stone-800">
                <div class="w-12 h-12 rounded-full ring-2 ring-gold-400/40 overflow-hidden flex-shrink-0">
                  <img loading="lazy" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>" class="w-full h-full object-cover" loading="lazy" />
                </div>
                <div class="min-w-0">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-white truncate">
                    <?php echo esc_html($name); ?>
                  </h4>
                  <?php if (!empty($role)) : ?>
                  <p class="text-[11px] text-gold-700 dark:text-gold-400 font-medium">
                    <?php echo esc_html($role); ?>
                  </p>
                  <?php endif; ?>
                  <?php if (!empty($acquired)) : ?>
                  <span class="text-xs text-stone-500 dark:text-stone-400 block truncate">
                    <?php echo esc_html($acquired); ?>
                  </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>
</section>
        <?php
    }
}

