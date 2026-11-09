<?php
/**
 * Kinboni Private Salon Contact & Concierge - Elementor Widget
 *
 * Implements the luxury editorial Flagship Salon Contact & Appointment section
 * featuring Gulshan-2 Flagship salon concierge details, instant WhatsApp button,
 * private horology viewing showcase, and dynamic Contact Form 7 integration
 * with full responsive styling and fallback built-in form support.
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

class Kinboni_Widget_Contact extends Widget_Base {

    public function get_name() {
        return 'kinboni_contact';
    }

    public function get_title() {
        return esc_html__('Private Salon Contact & Concierge', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('contact', 'form', 'contact form 7', 'cf7', 'salon', 'flagship', 'concierge', 'appointment', 'gulshan', 'kinboni');
    }

    /**
     * Retrieve list of Contact Form 7 forms
     */
    private function get_cf7_forms() {
        $forms = array(
            '' => esc_html__('— Select Contact Form 7 —', 'kinboni-core'),
        );

        if (class_exists('WPCF7_ContactForm') || post_type_exists('wpcf7_contact_form')) {
            $posts = get_posts(array(
                'post_type'      => 'wpcf7_contact_form',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ));

            if (!empty($posts)) {
                foreach ($posts as $post) {
                    $forms[$post->ID] = $post->post_title . ' (ID: ' . $post->ID . ')';
                }
            }
        }

        return $forms;
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
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'badge_text',
            array(
                'label'       => esc_html__('Badge Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Private Salon & Concierge', 'kinboni-core'),
                'placeholder' => esc_html__('Private Salon & Concierge', 'kinboni-core'),
                'dynamic'     => array('active' => true),
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'badge_tag',
            array(
                'label'     => esc_html__('Badge HTML Tag', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'span' => 'span',
                    'div'  => 'div',
                    'p'    => 'p',
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
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'default'     => esc_html__('Visit Our Gulshan-2 Flagship or Book an Appointment', 'kinboni-core'),
                'placeholder' => esc_html__('Enter headline...', 'kinboni-core'),
                'dynamic'     => array('active' => true),
                'condition'   => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'title_tag',
            array(
                'label'     => esc_html__('Headline HTML Tag', 'kinboni-core'),
                'type'      => Controls_Manager::SELECT,
                'options'   => array(
                    'h1'   => 'H1',
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'div'  => 'div',
                ),
                'default'   => 'h1',
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: FLAGSHIP SALON INFO (LEFT)
        // ==========================================
        $this->start_controls_section(
            'section_salon_info',
            array(
                'label' => esc_html__('Flagship Salon Info (Left)', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'salon_badge',
            array(
                'label'       => esc_html__('Salon Badge', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Dhaka Flagship Salon', 'kinboni-core'),
                'placeholder' => esc_html__('Dhaka Flagship Salon', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'salon_title',
            array(
                'label'       => esc_html__('Salon Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('KINBONI Haute Atelier', 'kinboni-core'),
                'placeholder' => esc_html__('KINBONI Haute Atelier', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'salon_address',
            array(
                'label'       => esc_html__('Address', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'default'     => esc_html__('Avenue 4, Road 11, Gulshan-2, Dhaka 1212, Bangladesh', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'salon_phone',
            array(
                'label'       => esc_html__('Concierge Phone & WhatsApp', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('+880 1711-000000 (Direct Concierge & WhatsApp)', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'salon_email',
            array(
                'label'       => esc_html__('Concierge Email', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('concierge@kinboni.com', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'salon_hours',
            array(
                'label'       => esc_html__('Salon Hours', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Monday – Saturday: 10:00 AM – 9:00 PM', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'show_whatsapp_btn',
            array(
                'label'        => esc_html__('Show WhatsApp Concierge Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            )
        );

        $this->add_control(
            'whatsapp_btn_text',
            array(
                'label'       => esc_html__('WhatsApp Button Text', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Instant WhatsApp Concierge', 'kinboni-core'),
                'condition'   => array('show_whatsapp_btn' => 'yes'),
            )
        );

        $this->add_control(
            'whatsapp_url',
            array(
                'label'       => esc_html__('WhatsApp URL or Number', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://wa.me/8801711000000', 'kinboni-core'),
                'default'     => array(
                    'url'         => 'https://wa.me/8801711000000',
                    'is_external' => true,
                ),
                'condition'   => array('show_whatsapp_btn' => 'yes'),
            )
        );

        $this->add_control(
            'show_secondary_card',
            array(
                'label'        => esc_html__('Show Horology Viewing Card', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            )
        );

        $this->add_control(
            'secondary_card_title',
            array(
                'label'       => esc_html__('Viewing Card Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Private Horology Viewing', 'kinboni-core'),
                'condition'   => array('show_secondary_card' => 'yes'),
            )
        );

        $this->add_control(
            'secondary_card_desc',
            array(
                'label'       => esc_html__('Viewing Card Description', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__('Request a private salon viewing of our Chronos Royale and Vanguard Skeleton movements with complimentary espresso and valet parking.', 'kinboni-core'),
                'condition'   => array('show_secondary_card' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: CONCIERGE FORM & CONTACT FORM 7 (RIGHT)
        // ==========================================
        $this->start_controls_section(
            'section_form_content',
            array(
                'label' => esc_html__('Contact Form 7 & Inquiry (Right)', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'form_title',
            array(
                'label'       => esc_html__('Form Headline', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => esc_html__('Send a Private Inquiry or Book Salon Visit', 'kinboni-core'),
                'placeholder' => esc_html__('Send a Private Inquiry or Book Salon Visit', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'form_subtitle',
            array(
                'label'       => esc_html__('Form Subtitle / Response Time', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'default'     => esc_html__('Our Senior Concierge responds to all inquiries within 30 minutes during salon hours.', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'form_source',
            array(
                'label'   => esc_html__('Form Source', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'cf7'       => esc_html__('Contact Form 7 (Select Form)', 'kinboni-core'),
                    'shortcode' => esc_html__('Custom Form Shortcode', 'kinboni-core'),
                    'built_in'  => esc_html__('Kinboni Atelier Built-in Form (Interactive)', 'kinboni-core'),
                ),
                'default' => 'cf7',
            )
        );

        $this->add_control(
            'cf7_form_id',
            array(
                'label'       => esc_html__('Select Contact Form 7', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT,
                'options'     => $this->get_cf7_forms(),
                'default'     => '',
                'condition'   => array('form_source' => 'cf7'),
                'description' => esc_html__('Select your Contact Form 7 form. If none appear, install Contact Form 7 and create a form under WP Admin > Contact.', 'kinboni-core'),
            )
        );

        $this->add_control(
            'custom_shortcode',
            array(
                'label'       => esc_html__('Form Shortcode', 'kinboni-core'),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'placeholder' => '[contact-form-7 id="123" title="Flagship Inquiry"]',
                'condition'   => array('form_source' => 'shortcode'),
            )
        );

        $this->add_control(
            'cf7_code_help',
            array(
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => '<div style="font-size:11px; line-height:1.4; color:#6b7280; background:#f3f4f6; padding:10px; border-radius:8px; border:1px solid #e5e7eb;"><strong>Luxury CF7 Tags Tip:</strong><br/>To get the matching 2-column layout in Contact Form 7, wrap your fields in <code>&lt;div class="grid grid-cols-1 sm:grid-cols-2 gap-4"&gt;</code>.<br/>All CF7 inputs, selects, textareas, and submit buttons are automatically styled with Kinboni Atelier luxury gold & stone themes.</div>',
                'content_classes' => 'elementor-descriptor',
                'condition'       => array('form_source' => array('cf7', 'shortcode')),
            )
        );

        // Built-in form controls
        $this->add_control(
            'builtin_name_label',
            array(
                'label'     => esc_html__('Name Field Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Your Name *', 'kinboni-core'),
                'condition' => array('form_source' => 'built_in'),
            )
        );

        $this->add_control(
            'builtin_phone_label',
            array(
                'label'     => esc_html__('Phone Field Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Mobile Phone *', 'kinboni-core'),
                'condition' => array('form_source' => 'built_in'),
            )
        );

        $this->add_control(
            'builtin_topic_label',
            array(
                'label'     => esc_html__('Topic Field Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Inquiry Topic', 'kinboni-core'),
                'condition' => array('form_source' => 'built_in'),
            )
        );

        $this->add_control(
            'builtin_date_label',
            array(
                'label'     => esc_html__('Date Field Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Preferred Date (Optional)', 'kinboni-core'),
                'condition' => array('form_source' => 'built_in'),
            )
        );

        $this->add_control(
            'builtin_msg_label',
            array(
                'label'     => esc_html__('Message Field Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Your Message *', 'kinboni-core'),
                'condition' => array('form_source' => 'built_in'),
            )
        );

        $this->add_control(
            'builtin_btn_text',
            array(
                'label'     => esc_html__('Submit Button Text', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Dispatch Inquiry to Concierge', 'kinboni-core'),
                'condition' => array('form_source' => 'built_in'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: AMBIENT GLOW & LAYOUT
        // ==========================================
        $this->start_controls_section(
            'section_layout_settings',
            array(
                'label' => esc_html__('Ambient Glow & Layout', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_ambient_shapes',
            array(
                'label'        => esc_html__('Ambient Luxury Shapes & Glow', 'kinboni-core'),
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
                'default'     => 'page-view-contact',
                'placeholder' => 'page-view-contact',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: SECTION & AMBIENCE
        // ==========================================
        $this->start_controls_section(
            'section_style_main',
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
                'size_units' => array('px', 'em', 'rem'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-contact-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Section Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-contact-section' => 'background-color: {{VALUE}};',
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
                'label'     => esc_html__('Header Style', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_header' => 'yes'),
            )
        );

        $this->add_control(
            'badge_color',
            array(
                'label'     => esc_html__('Badge Subtitle Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-contact-badge' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'badge_typography',
                'label'    => esc_html__('Badge Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} .kinboni-contact-badge',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-contact-title' => 'color: {{VALUE}} !important;',
                ),
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'label'    => esc_html__('Title Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} .kinboni-contact-title',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: LEFT SALON INFO CARDS
        // ==========================================
        $this->start_controls_section(
            'section_style_salon_cards',
            array(
                'label' => esc_html__('Left Info Cards Style', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'salon_card_bg',
            array(
                'label'     => esc_html__('Salon Card Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-salon-card' => 'background-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'salon_card_border_color',
            array(
                'label'     => esc_html__('Salon Card Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-salon-card' => 'border-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'salon_card_title_color',
            array(
                'label'     => esc_html__('Salon Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-salon-title' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'horology_card_bg',
            array(
                'label'     => esc_html__('Viewing Card Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-viewing-card' => 'background-color: {{VALUE}} !important;',
                ),
                'separator' => 'before',
            )
        );

        $this->add_control(
            'horology_card_border_color',
            array(
                'label'     => esc_html__('Viewing Card Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-viewing-card' => 'border-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: FORM & CONTACT FORM 7 STYLING
        // ==========================================
        $this->start_controls_section(
            'section_style_form',
            array(
                'label' => esc_html__('Form & CF7 Styling', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'form_box_bg',
            array(
                'label'     => esc_html__('Form Container Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-form-container' => 'background-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'form_box_border_color',
            array(
                'label'     => esc_html__('Form Container Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-form-container' => 'border-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'form_heading_color',
            array(
                'label'     => esc_html__('Form Headline Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-form-headline' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'form_btn_bg',
            array(
                'label'     => esc_html__('Submit Button Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-contact-submit, {{WRAPPER}} .wpcf7-submit' => 'background-color: {{VALUE}} !important;',
                ),
                'separator' => 'before',
            )
        );

        $this->add_control(
            'form_btn_text_color',
            array(
                'label'     => esc_html__('Submit Button Text Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-contact-submit, {{WRAPPER}} .wpcf7-submit' => 'color: {{VALUE}} !important;',
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

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : 'page-view-contact';
        $show_decor = (!empty($settings['show_ambient_shapes']) && $settings['show_ambient_shapes'] === 'yes');

        // Header
        $show_header = (!empty($settings['show_header']) && $settings['show_header'] === 'yes');
        $badge_text  = !empty($settings['badge_text']) ? $settings['badge_text'] : '';
        $badge_tag   = in_array($settings['badge_tag'], array('span', 'div', 'p', 'h5', 'h6'), true) ? $settings['badge_tag'] : 'span';
        $title_text  = !empty($settings['title_text']) ? $settings['title_text'] : '';
        $title_tag   = in_array($settings['title_tag'], array('h1', 'h2', 'h3', 'div'), true) ? $settings['title_tag'] : 'h1';

        // Salon info (Left)
        $salon_badge   = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'salon_badge', 'contact_badge', '', $this) : (!empty($settings['salon_badge']) ? $settings['salon_badge'] : '');
        $salon_title   = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'salon_title', 'brand_name', 'KINBONI Haute Atelier', $this) : (!empty($settings['salon_title']) ? $settings['salon_title'] : '');
        $salon_address = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'salon_address', 'concierge_address', 'Avenue 4, Road 11, Gulshan-2, Dhaka 1212, Bangladesh', $this) : (!empty($settings['salon_address']) ? $settings['salon_address'] : '');
        $salon_phone   = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'salon_phone', 'concierge_phone', '+880 1711-000000 (Direct Concierge & WhatsApp)', $this) : (!empty($settings['salon_phone']) ? $settings['salon_phone'] : '');
        $salon_email   = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'salon_email', 'concierge_email', 'concierge@kinboni.com', $this) : (!empty($settings['salon_email']) ? $settings['salon_email'] : '');
        $salon_hours   = !empty($settings['salon_hours']) ? $settings['salon_hours'] : '';

        // WhatsApp
        $wa_phone_clean = preg_replace('/\D/', '', (string) (class_exists('Kinboni_Content') ? Kinboni_Content::get('whatsapp_phone', '8801700000000') : '8801700000000'));
        $wa_default_url = !empty($wa_phone_clean) ? ('https://wa.me/' . $wa_phone_clean) : 'https://wa.me/8801700000000';
        $show_wa        = (!empty($settings['show_whatsapp_btn']) && $settings['show_whatsapp_btn'] === 'yes');
        $wa_text        = class_exists('Kinboni_Content') ? Kinboni_Content::resolve($settings, 'whatsapp_btn_text', 'whatsapp_label', esc_html__('Instant WhatsApp Concierge', 'kinboni-core'), $this) : (!empty($settings['whatsapp_btn_text']) ? $settings['whatsapp_btn_text'] : esc_html__('Instant WhatsApp Concierge', 'kinboni-core'));
        $wa_url         = !empty($settings['whatsapp_url']['url']) ? $settings['whatsapp_url']['url'] : $wa_default_url;
        $wa_target  = !empty($settings['whatsapp_url']['is_external']) ? ' target="_blank"' : '';
        $wa_rel     = !empty($settings['whatsapp_url']['nofollow']) ? ' rel="nofollow"' : '';

        // Horology viewing card
        $show_horology = (!empty($settings['show_secondary_card']) && $settings['show_secondary_card'] === 'yes');
        $horology_title = !empty($settings['secondary_card_title']) ? $settings['secondary_card_title'] : '';
        $horology_desc  = !empty($settings['secondary_card_desc']) ? $settings['secondary_card_desc'] : '';

        // Form (Right)
        $form_title    = !empty($settings['form_title']) ? $settings['form_title'] : '';
        $form_subtitle = !empty($settings['form_subtitle']) ? $settings['form_subtitle'] : '';
        $form_source   = !empty($settings['form_source']) ? $settings['form_source'] : 'cf7';

        $cf7_id        = !empty($settings['cf7_form_id']) ? absint($settings['cf7_form_id']) : 0;
        $shortcode     = !empty($settings['custom_shortcode']) ? trim($settings['custom_shortcode']) : '';

        // Built-in form labels
        $b_name_label  = !empty($settings['builtin_name_label']) ? $settings['builtin_name_label'] : esc_html__('Your Name *', 'kinboni-core');
        $b_phone_label = !empty($settings['builtin_phone_label']) ? $settings['builtin_phone_label'] : esc_html__('Mobile Phone *', 'kinboni-core');
        $b_topic_label = !empty($settings['builtin_topic_label']) ? $settings['builtin_topic_label'] : esc_html__('Inquiry Topic', 'kinboni-core');
        $b_date_label  = !empty($settings['builtin_date_label']) ? $settings['builtin_date_label'] : esc_html__('Preferred Date (Optional)', 'kinboni-core');
        $b_msg_label   = !empty($settings['builtin_msg_label']) ? $settings['builtin_msg_label'] : esc_html__('Your Message *', 'kinboni-core');
        $b_btn_text    = !empty($settings['builtin_btn_text']) ? $settings['builtin_btn_text'] : esc_html__('Dispatch Inquiry to Concierge', 'kinboni-core');

        $is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();
        ?>
<section id="<?php echo esc_attr($section_id); ?>" class="kinboni-contact-section wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]">

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

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

    <?php if ($show_header) : ?>
    <div class="mb-8 pb-6 border-b border-stone-200 dark:border-stone-800">
      <?php if (!empty($badge_text)) : ?>
      <<?php echo $badge_tag; ?> class="kinboni-contact-badge text-[11px] font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400 block">
        <?php echo esc_html($badge_text); ?>
      </<?php echo $badge_tag; ?>>
      <?php endif; ?>

      <?php if (!empty($title_text)) : ?>
      <<?php echo $title_tag; ?> class="kinboni-contact-title font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-stone-900 dark:text-white mt-1">
        <?php echo esc_html($title_text); ?>
      </<?php echo $title_tag; ?>>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <div class="lg:col-span-5 space-y-5">
        <div class="kinboni-salon-card p-6 sm:p-7 rounded-3xl bg-white/95 dark:bg-stone-900/90 backdrop-blur-md border border-stone-200/80 dark:border-stone-800 space-y-4 shadow-sm">
          <?php if (!empty($salon_badge)) : ?>
          <span class="inline-block px-3 py-1 rounded-full bg-gold-400/20 text-gold-700 dark:text-gold-300 text-xs font-bold uppercase tracking-widest">
            <?php echo esc_html($salon_badge); ?>
          </span>
          <?php endif; ?>

          <?php if (!empty($salon_title)) : ?>
          <h3 class="kinboni-salon-title font-serif text-2xl font-medium text-stone-900 dark:text-white">
            <?php echo esc_html($salon_title); ?>
          </h3>
          <?php endif; ?>

          <div class="space-y-3 text-xs sm:text-sm text-stone-600 dark:text-stone-300">
            <?php if (!empty($salon_address)) : ?>
            <p class="flex items-start gap-3">
              <i data-lucide="map-pin" class="w-4 h-4 text-gold-500 flex-shrink-0 mt-1"></i>
              <span><?php echo esc_html($salon_address); ?></span>
            </p>
            <?php endif; ?>

            <?php if (!empty($salon_phone)) : ?>
            <p class="flex items-center gap-3">
              <i data-lucide="phone" class="w-4 h-4 text-gold-500 flex-shrink-0"></i>
              <span><?php echo esc_html($salon_phone); ?></span>
            </p>
            <?php endif; ?>

            <?php if (!empty($salon_email)) : ?>
            <p class="flex items-center gap-3">
              <i data-lucide="mail" class="w-4 h-4 text-gold-500 flex-shrink-0"></i>
              <span><?php echo esc_html($salon_email); ?></span>
            </p>
            <?php endif; ?>

            <?php if (!empty($salon_hours)) : ?>
            <p class="flex items-center gap-3">
              <i data-lucide="clock" class="w-4 h-4 text-gold-500 flex-shrink-0"></i>
              <span><?php echo esc_html($salon_hours); ?></span>
            </p>
            <?php endif; ?>
          </div>

          <?php if ($show_wa) : ?>
          <div class="pt-4 border-t border-stone-200 dark:border-stone-800">
            <a href="<?php echo esc_url($wa_url); ?>"<?php echo $wa_target . $wa_rel; ?> class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-colors">
              <i data-lucide="message-circle" class="w-4 h-4"></i>
              <span><?php echo esc_html($wa_text); ?></span>
            </a>
          </div>
          <?php endif; ?>
        </div>

        <?php if ($show_horology) : ?>
        <div class="kinboni-viewing-card p-6 rounded-3xl bg-stone-950 text-stone-200 border border-gold-500/30 space-y-2">
          <?php if (!empty($horology_title)) : ?>
          <h4 class="font-serif text-xl text-white"><?php echo esc_html($horology_title); ?></h4>
          <?php endif; ?>
          <?php if (!empty($horology_desc)) : ?>
          <p class="text-sm text-stone-400 font-normal leading-relaxed">
            <?php echo esc_html($horology_desc); ?>
          </p>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="lg:col-span-7 kinboni-form-container bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-8 shadow-sm">
        <?php if (!empty($form_title)) : ?>
        <h3 class="kinboni-form-headline font-serif text-2xl font-medium text-stone-900 dark:text-white mb-2">
          <?php echo esc_html($form_title); ?>
        </h3>
        <?php endif; ?>

        <?php if (!empty($form_subtitle)) : ?>
        <p class="text-sm text-stone-500 mb-6">
          <?php echo esc_html($form_subtitle); ?>
        </p>
        <?php endif; ?>

        <div class="kinboni-cf7-wrap">
          <?php
          $rendered_form = false;

          // SOURCE 1: Contact Form 7 ID
          if ($form_source === 'cf7' && !empty($cf7_id)) {
              echo do_shortcode('[contact-form-7 id="' . $cf7_id . '"]');
              $rendered_form = true;
          }

          // SOURCE 2: Custom Shortcode
          elseif ($form_source === 'shortcode' && !empty($shortcode)) {
              echo do_shortcode($shortcode);
              $rendered_form = true;
          }

          // Fallback / SOURCE 3: Built-in Luxury Form
          if (!$rendered_form) {
              if ($form_source === 'cf7' && $is_edit && empty($cf7_id)) {
                  echo '<div class="p-3 mb-4 text-xs rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300">';
                  echo esc_html__('Notice: No Contact Form 7 form is selected in the widget panel. Displaying the built-in luxury concierge form preview below.', 'kinboni-core');
                  echo '</div>';
              }
              ?>
              <form id="wc-contact-form" class="space-y-4" novalidate>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php echo esc_html($b_name_label); ?></label>
                    <input type="text" id="wc-contact-name" required placeholder="<?php esc_attr_e('Full Name', 'kinboni-core'); ?>" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:border-gold-500 focus:outline-none transition-colors" />
                  </div>
                  <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php echo esc_html($b_phone_label); ?></label>
                    <input type="tel" id="wc-contact-phone" required placeholder="<?php esc_attr_e('017XXXXXXXX', 'kinboni-core'); ?>" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:border-gold-500 focus:outline-none transition-colors" />
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php echo esc_html($b_topic_label); ?></label>
                    <select id="wc-contact-topic" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:border-gold-500 focus:outline-none transition-colors">
                      <option><?php esc_html_e('Private Salon Viewing Appointment', 'kinboni-core'); ?></option>
                      <option><?php esc_html_e('Bespoke Corporate / Wedding Gift Order', 'kinboni-core'); ?></option>
                      <option><?php esc_html_e('Order Dispatch / Exchange Assistance', 'kinboni-core'); ?></option>
                      <option><?php esc_html_e('Watch & Leather Care Servicing', 'kinboni-core'); ?></option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php echo esc_html($b_date_label); ?></label>
                    <input type="date" id="wc-contact-date" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:border-gold-500 focus:outline-none transition-colors" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php echo esc_html($b_msg_label); ?></label>
                  <textarea id="wc-contact-msg" rows="4" required placeholder="<?php esc_attr_e('Specify the creations you wish to inspect or any questions for our artisans...', 'kinboni-core'); ?>" class="w-full px-4 py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:border-gold-500 focus:outline-none transition-colors"></textarea>
                </div>
                <button type="submit" class="kinboni-contact-submit w-full py-4 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-md transition-all active:scale-[0.99] cursor-pointer">
                  <?php echo esc_html($b_btn_text); ?>
                </button>
              </form>
              <?php
          }
          ?>
        </div>
      </div>

    </div>

  </div>
</section>
        <?php
    }
}

