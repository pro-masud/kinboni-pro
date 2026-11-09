<?php
/**
 * Kinboni FAQ & Guarantee Policies - Elementor Widget
 *
 * Implements the luxury editorial FAQ & Client Assistance section featuring
 * ambient architectural glows, header copy, interactive smooth accordion with plus/minus icons,
 * and policy summary cards (Doorstep Inspection, 7-Day Exchange, 2-Year Movement Warranty).
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
use Elementor\Repeater;

class Kinboni_Widget_Faq extends Widget_Base {

    public function get_name() {
        return 'kinboni_faq';
    }

    public function get_title() {
        return esc_html__('FAQ & Policy Guarantee', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-help-o';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('faq', 'questions', 'accordion', 'guarantee', 'policy', 'exchange', 'warranty', 'inspection', 'kinboni');
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
                'default'     => esc_html__('Client Assistance & Policies', 'kinboni-core'),
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
                'label'       => esc_html__('Main Headline', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Frequently Asked Questions & Guarantee', 'kinboni-core'),
                'placeholder' => esc_html__('Enter main title', 'kinboni-core'),
                'condition'   => array('show_header' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'title_tag',
            array(
                'label'     => esc_html__('Headline HTML Tag', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                ),
                'default'   => 'h1',
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'desc_text',
            array(
                'label'       => esc_html__('Description Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Transparent policies regarding Cash on Delivery inspection, 7-day exchanges, authenticity verification, and lifetime servicing.', 'kinboni-core'),
                'placeholder' => esc_html__('Enter description', 'kinboni-core'),
                'condition'   => array('show_header' => 'yes'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: ACCORDION ITEMS
        // ==========================================
        $this->start_controls_section(
            'section_accordion_content',
            array(
                'label' => esc_html__('FAQ Accordion Items', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'question',
            array(
                'label'       => esc_html__('Question', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Can I inspect my timepiece or leather creation before paying Cash on Delivery?', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'answer',
            array(
                'label'       => esc_html__('Answer', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 5,
                'default'     => esc_html__('Yes. Every KINBONI Atelier dispatch includes doorstep verification. You may unseal the outer courier parcel and inspect the wooden presentation box and serial-numbered creation in the presence of our courier partner before handing over cash.', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'faq_list',
            array(
                'label'       => esc_html__('FAQ Items', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ question }}}',
                'default'     => array(
                    array(
                        'question' => esc_html__('Can I inspect my timepiece or leather creation before paying Cash on Delivery?', 'kinboni-core'),
                        'answer'   => esc_html__('Yes. Every KINBONI Atelier dispatch includes doorstep verification. You may unseal the outer courier parcel and inspect the wooden presentation box and serial-numbered creation in the presence of our courier partner before handing over cash.', 'kinboni-core'),
                    ),
                    array(
                        'question' => esc_html__('How fast is delivery inside Dhaka and across Bangladesh?', 'kinboni-core'),
                        'answer'   => esc_html__('Inside Dhaka City, white-glove courier delivery is completed within 24 to 48 hours (৳60 fee, or complimentary on all orders over ৳2,500). Outside Dhaka, nationwide express delivery takes 2 to 4 business days (৳120 fee, or complimentary over ৳2,500).', 'kinboni-core'),
                    ),
                    array(
                        'question' => esc_html__('How do I redeem the 20% privilege code LUXE20?', 'kinboni-core'),
                        'answer'   => esc_html__('Simply enter code LUXE20 inside the Slide-Out Shopping Bag, the Cart Page, the Checkout Page, or the 3-Field Quick COD Modal. The 20% privilege deduction is calculated automatically.', 'kinboni-core'),
                    ),
                    array(
                        'question' => esc_html__('What is covered under the Lifetime Master Guild Warranty?', 'kinboni-core'),
                        'answer'   => esc_html__('All full-grain Tuscan leather goods qualify for complimentary lifetime conditioning, edge re-burnishing, and linen stitch servicing at our Gulshan-2 Flagship Salon. Automatic timepieces include a 2-year comprehensive movement calibration warranty.', 'kinboni-core'),
                    ),
                    array(
                        'question' => esc_html__('What is your 7-Day Exchange Policy?', 'kinboni-core'),
                        'answer'   => esc_html__('If you wish to exchange your creation for another piece in our archive, notify our concierge within 7 days of delivery. Provided the item is unused with its original presentation box and authenticity card intact, our courier will arrange a doorstep exchange.', 'kinboni-core'),
                    ),
                ),
            )
        );

        $this->add_control(
            'first_item_open',
            array(
                'label'        => esc_html__('First Item Open by Default', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'accordion_behavior',
            array(
                'label'   => esc_html__('Accordion Behavior', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'single'   => esc_html__('Single Open (Auto-close others)', 'kinboni-core'),
                    'multiple' => esc_html__('Multiple Open Allowed', 'kinboni-core'),
                ),
                'default' => 'single',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: POLICY SUMMARY CARDS
        // ==========================================
        $this->start_controls_section(
            'section_policy_cards_content',
            array(
                'label' => esc_html__('Policy Summary Cards', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_policy_cards',
            array(
                'label'        => esc_html__('Show Policy Summary Cards', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Show', 'kinboni-core'),
                'label_off'    => esc_html__('Hide', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $policy_repeater = new Repeater();

        $policy_repeater->add_control(
            'title',
            array(
                'label'       => esc_html__('Policy Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Doorstep Inspection', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $policy_repeater->add_control(
            'description',
            array(
                'label'       => esc_html__('Policy Description', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Open and verify your timepiece or leather creation in front of the courier before paying cash.', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'policy_cards_list',
            array(
                'label'       => esc_html__('Cards', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $policy_repeater->get_controls(),
                'title_field' => '{{{ title }}}',
                'condition'   => array('show_policy_cards' => 'yes'),
                'default'     => array(
                    array(
                        'title'       => esc_html__('Doorstep Inspection', 'kinboni-core'),
                        'description' => esc_html__('Open and verify your timepiece or leather creation in front of the courier before paying cash.', 'kinboni-core'),
                    ),
                    array(
                        'title'       => esc_html__('7-Day Exchange', 'kinboni-core'),
                        'description' => esc_html__('Unused creations in original wooden presentation packaging qualify for instant 7-day exchange.', 'kinboni-core'),
                    ),
                    array(
                        'title'       => esc_html__('2-Year Movement Warranty', 'kinboni-core'),
                        'description' => esc_html__('Every automatic calibre and leather seam is covered by our Gulshan-2 Master Guild service center.', 'kinboni-core'),
                    ),
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: AMBIENT DECOR & SETTINGS
        // ==========================================
        $this->start_controls_section(
            'section_decor_settings',
            array(
                'label' => esc_html__('Ambient Decor & Settings', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_ambient_shapes',
            array(
                'label'        => esc_html__('Show Ambient Glow & Architectural Shapes', 'kinboni-core'),
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
                'default'     => 'page-view-faq',
                'placeholder' => 'page-view-faq',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: SECTION CONTAINER
        // ==========================================
        $this->start_controls_section(
            'section_style_container',
            array(
                'label' => esc_html__('Section Container', 'kinboni-core'),
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
                    '{{WRAPPER}} .kinboni-faq-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: HEADER TYPOGRAPHY
        // ==========================================
        $this->start_controls_section(
            'section_style_header',
            array(
                'label'     => esc_html__('Header Style', 'kinboni-core'),
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

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .kinboni-faq-badge',
            )
        );

        $this->add_control(
            'badge_color',
            array(
                'label'     => esc_html__('Badge Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'heading_title_style',
            array(
                'label'     => esc_html__('Headline', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-faq-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Headline Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-title' => 'color: {{VALUE}};',
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

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .kinboni-faq-desc',
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: ACCORDION ITEM STYLE
        // ==========================================
        $this->start_controls_section(
            'section_style_accordion',
            array(
                'label' => esc_html__('Accordion Style', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'item_bg_color',
            array(
                'label'     => esc_html__('Card Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-item' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'item_border_radius',
            array(
                'label'      => esc_html__('Card Border Radius', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-faq-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'item_border_color',
            array(
                'label'     => esc_html__('Default Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-item:not(.is-active)' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'item_active_border_color',
            array(
                'label'     => esc_html__('Active Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-item.is-active' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'heading_q_style',
            array(
                'label'     => esc_html__('Question Text', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'q_typography',
                'selector' => '{{WRAPPER}} .kinboni-faq-question',
            )
        );

        $this->add_control(
            'q_color',
            array(
                'label'     => esc_html__('Question Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-question' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'heading_a_style',
            array(
                'label'     => esc_html__('Answer Text', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'a_typography',
                'selector' => '{{WRAPPER}} .kinboni-faq-answer',
            )
        );

        $this->add_control(
            'a_color',
            array(
                'label'     => esc_html__('Answer Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-answer' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'heading_icon_style',
            array(
                'label'     => esc_html__('Icon Badge', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'icon_bg_color',
            array(
                'label'     => esc_html__('Icon Badge Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-icon-box' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => esc_html__('Icon Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-faq-icon-box'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kinboni-faq-icon-box svg' => 'color: {{VALUE}}; stroke: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: POLICY SUMMARY CARDS
        // ==========================================
        $this->start_controls_section(
            'section_style_policy_cards',
            array(
                'label'     => esc_html__('Policy Summary Cards', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_policy_cards' => 'yes'),
            )
        );

        $this->add_control(
            'policy_card_bg',
            array(
                'label'     => esc_html__('Card Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-policy-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'policy_card_border_color',
            array(
                'label'     => esc_html__('Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-policy-card' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'policy_card_radius',
            array(
                'label'      => esc_html__('Border Radius', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-policy-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'heading_policy_title',
            array(
                'label'     => esc_html__('Title Style', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'policy_title_typo',
                'selector' => '{{WRAPPER}} .kinboni-policy-title',
            )
        );

        $this->add_control(
            'policy_title_color',
            array(
                'label'     => esc_html__('Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-policy-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'heading_policy_desc',
            array(
                'label'     => esc_html__('Description Style', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'policy_desc_typo',
                'selector' => '{{WRAPPER}} .kinboni-policy-desc',
            )
        );

        $this->add_control(
            'policy_desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-policy-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render Widget Output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'page-view-faq';

        $show_decor = (!empty($settings['show_ambient_shapes']) && $settings['show_ambient_shapes'] === 'yes');

        $show_header = (!empty($settings['show_header']) && $settings['show_header'] === 'yes');
        $badge_tag = in_array($settings['badge_tag'], array('span', 'p', 'div', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_tag = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'h4'), true) ? $settings['title_tag'] : 'h1';

        $badge_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'badge_text', 'faq_badge', '', $this) : (!empty($settings['badge_text']) ? $settings['badge_text'] : '');
        $title_text = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'title_text', 'faq_title', '', $this) : (!empty($settings['title_text']) ? $settings['title_text'] : '');

        $faq_list = !empty($settings['faq_list']) && is_array($settings['faq_list']) ? $settings['faq_list'] : array();
        $first_item_open = (!empty($settings['first_item_open']) && $settings['first_item_open'] === 'yes');
        $behavior = !empty($settings['accordion_behavior']) ? $settings['accordion_behavior'] : 'single';

        $show_policy = (!empty($settings['show_policy_cards']) && $settings['show_policy_cards'] === 'yes');
        $policy_list = !empty($settings['policy_cards_list']) && is_array($settings['policy_cards_list']) ? $settings['policy_cards_list'] : array();

        if (empty($faq_list) && empty($policy_list)) {
            return;
        }
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-faq-section wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]" data-behavior="<?php echo esc_attr($behavior); ?>">

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

  <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

    <?php if ($show_header) : ?>
    <div class="text-center space-y-3">
      <?php if (!empty($badge_text)) : ?>
      <<?php echo $badge_tag; ?> class="kinboni-faq-badge block text-[11px] font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400">
        <?php echo esc_html($badge_text); ?>
      </<?php echo $badge_tag; ?>>
      <?php endif; ?>

      <?php if (!empty($title_text)) : ?>
      <<?php echo $title_tag; ?> class="kinboni-faq-title font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white">
        <?php echo esc_html($title_text); ?>
      </<?php echo $title_tag; ?>>
      <?php endif; ?>

      <?php if (!empty($settings['desc_text'])) : ?>
      <p class="kinboni-faq-desc text-sm text-stone-600 dark:text-stone-300 max-w-xl mx-auto font-normal">
        <?php echo esc_html($settings['desc_text']); ?>
      </p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="kinboni-faq-accordion space-y-3.5">
      <?php foreach ($faq_list as $idx => $item) : 
        $isOpen = ($idx === 0 && $first_item_open);
        $q = !empty($item['question']) ? $item['question'] : '';
        $a = !empty($item['answer']) ? $item['answer'] : '';
      ?>
      <div class="kinboni-faq-item rounded-2xl bg-white/95 dark:bg-stone-900/90 backdrop-blur-md border <?php echo $isOpen ? 'border-gold-500 shadow-md is-active' : 'border-stone-200/80 dark:border-stone-800'; ?> overflow-hidden transition-all duration-300">
        <button 
          type="button" 
          class="kinboni-faq-btn w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none"
          aria-expanded="<?php echo $isOpen ? 'true' : 'false'; ?>"
        >
          <span class="kinboni-faq-question font-serif text-lg sm:text-xl font-medium text-stone-900 dark:text-white leading-snug">
            <?php echo esc_html($q); ?>
          </span>
          <span class="kinboni-faq-icon-box w-8 h-8 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center flex-shrink-0 text-gold-700 dark:text-gold-400">
            <i data-lucide="<?php echo $isOpen ? 'minus' : 'plus'; ?>" class="w-4 h-4"></i>
          </span>
        </button>
        <div class="kinboni-faq-answer px-5 pb-6 sm:px-6 text-xs sm:text-sm text-stone-600 dark:text-stone-300 font-normal leading-relaxed border-t border-stone-100 dark:border-stone-800/80 pt-4 <?php echo $isOpen ? 'block' : 'hidden'; ?>">
          <?php echo wp_kses_post(wpautop($a)); ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($show_policy && !empty($policy_list)) : ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-6">
      <?php foreach ($policy_list as $p_item) : 
        $p_title = !empty($p_item['title']) ? $p_item['title'] : '';
        $p_desc  = !empty($p_item['description']) ? $p_item['description'] : '';
      ?>
      <div class="kinboni-policy-card p-5 rounded-2xl bg-white/95 dark:bg-stone-900/90 border border-stone-200 dark:border-stone-800 space-y-2">
        <?php if (!empty($p_title)) : ?>
        <h4 class="kinboni-policy-title text-xs font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400">
          <?php echo esc_html($p_title); ?>
        </h4>
        <?php endif; ?>
        <?php if (!empty($p_desc)) : ?>
        <p class="kinboni-policy-desc text-sm text-stone-600 dark:text-stone-400 font-normal leading-relaxed">
          <?php echo esc_html($p_desc); ?>
        </p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>
        <?php
    }
}

