<?php
/**
 * Kinboni Trust Badges & Benefits - Elementor Widget
 *
 * Implements the luxury editorial Trust & Benefits section featuring
 * 4 (or custom count) trust cards with gold icon boxes, bold uppercase headings,
 * and certified value propositions (e.g. Secure Checkout, Mastercrafted Goods, Express Delivery, Patron Concierge).
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
use Elementor\Icons_Manager;

class Kinboni_Widget_Trust_Benefits extends Widget_Base {

    public function get_name() {
        return 'kinboni_trust_benefits';
    }

    public function get_title() {
        return esc_html__('Trust Badges & Benefits', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-shield-check';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('trust', 'badges', 'benefits', 'features', 'guarantee', 'security', 'delivery', 'support', 'kinboni');
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: BENEFITS ITEMS
        // ==========================================
        $this->start_controls_section(
            'section_benefits_content',
            array(
                'label' => esc_html__('Benefits & Trust Badges', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'title',
            array(
                'label'       => esc_html__('Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Secure Checkout', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'description',
            array(
                'label'       => esc_html__('Subtitle / Guarantee', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('256-Bit Bank Grade SSL', 'kinboni-core'),
                'label_block' => true,
                'dynamic'     => array('active' => true),
            )
        );

        $repeater->add_control(
            'icon_type',
            array(
                'label'   => esc_html__('Icon Type', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'lucide'    => esc_html__('Lucide Icon Name', 'kinboni-core'),
                    'elementor' => esc_html__('Elementor Icon / SVG', 'kinboni-core'),
                ),
                'default' => 'lucide',
            )
        );

        $repeater->add_control(
            'lucide_icon',
            array(
                'label'       => esc_html__('Lucide Icon Name', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => 'shield-check',
                'description' => esc_html__('E.g. shield-check, gem, truck, headphones, award, lock, package, clock', 'kinboni-core'),
                'condition'   => array('icon_type' => 'lucide'),
            )
        );

        $repeater->add_control(
            'elementor_icon',
            array(
                'label'     => esc_html__('Choose Icon', 'kinboni-core'),
                'type'      => Controls_Manager::ICONS,
                'condition' => array('icon_type' => 'elementor'),
            )
        );

        $repeater->add_control(
            'link',
            array(
                'label'       => esc_html__('Optional Card Link', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'kinboni-core'),
                'dynamic'     => array('active' => true),
            )
        );

        $this->add_control(
            'benefits_list',
            array(
                'label'       => esc_html__('Badges List', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ title }}}',
                'default'     => array(
                    array(
                        'title'       => esc_html__('Secure Checkout', 'kinboni-core'),
                        'description' => esc_html__('256-Bit Bank Grade SSL', 'kinboni-core'),
                        'icon_type'   => 'lucide',
                        'lucide_icon' => 'shield-check',
                    ),
                    array(
                        'title'       => esc_html__('Mastercrafted Goods', 'kinboni-core'),
                        'description' => esc_html__('Certified Origin & Serial', 'kinboni-core'),
                        'icon_type'   => 'lucide',
                        'lucide_icon' => 'gem',
                    ),
                    array(
                        'title'       => esc_html__('Express Delivery', 'kinboni-core'),
                        'description' => esc_html__('White-Glove Courier Service', 'kinboni-core'),
                        'icon_type'   => 'lucide',
                        'lucide_icon' => 'truck',
                    ),
                    array(
                        'title'       => esc_html__('Patron Concierge', 'kinboni-core'),
                        'description' => esc_html__('24/7 Dedicated Support', 'kinboni-core'),
                        'icon_type'   => 'lucide',
                        'lucide_icon' => 'headphones',
                    ),
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: LAYOUT SETTINGS
        // ==========================================
        $this->start_controls_section(
            'section_layout_settings',
            array(
                'label' => esc_html__('Grid & Layout', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
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
            'title_tag',
            array(
                'label'   => esc_html__('Title HTML Tag', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'options' => array(
                    'h2'   => 'h2',
                    'h3'   => 'h3',
                    'h4'   => 'h4',
                    'h5'   => 'h5',
                    'h6'   => 'h6',
                    'div'  => 'div',
                    'span' => 'span',
                    'p'    => 'p',
                ),
                'default' => 'h4',
            )
        );

        $this->add_control(
            'section_html_id',
            array(
                'label'       => esc_html__('Section HTML ID', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '',
                'placeholder' => 'trust-benefits-section',
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
                'label'      => esc_html__('Section Padding', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-trust-benefits-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'section_bg_color',
            array(
                'label'     => esc_html__('Custom Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-trust-benefits-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'show_border_top',
            array(
                'label'        => esc_html__('Border Top', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Show', 'kinboni-core'),
                'label_off'    => esc_html__('Hide', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->add_control(
            'show_border_bottom',
            array(
                'label'        => esc_html__('Border Bottom', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Show', 'kinboni-core'),
                'label_off'    => esc_html__('Hide', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'border_custom_color',
            array(
                'label'     => esc_html__('Custom Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-trust-benefits-section' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: BENEFIT CARDS
        // ==========================================
        $this->start_controls_section(
            'section_style_cards',
            array(
                'label' => esc_html__('Cards Style', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_bg_color',
            array(
                'label'     => esc_html__('Card Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-benefit-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .kinboni-benefit-card',
            )
        );

        $this->add_responsive_control(
            'card_border_radius',
            array(
                'label'      => esc_html__('Border Radius', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-benefit-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'card_padding',
            array(
                'label'      => esc_html__('Card Padding', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-benefit-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .kinboni-benefit-card',
            )
        );

        $this->add_control(
            'card_hover_lift',
            array(
                'label'        => esc_html__('Hover Lift Effect', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: ICON BOX
        // ==========================================
        $this->start_controls_section(
            'section_style_icon',
            array(
                'label' => esc_html__('Icon Badge', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'icon_box_bg',
            array(
                'label'     => esc_html__('Icon Background Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-benefit-icon-box' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => esc_html__('Icon Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-benefit-icon-box'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kinboni-benefit-icon-box svg' => 'color: {{VALUE}}; stroke: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_box_size',
            array(
                'label'      => esc_html__('Icon Box Size', 'kinboni-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'rem'),
                'range'      => array(
                    'px' => array('min' => 32, 'max' => 80),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-benefit-icon-box' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_size',
            array(
                'label'      => esc_html__('Icon Size', 'kinboni-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'rem'),
                'range'      => array(
                    'px' => array('min' => 16, 'max' => 48),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-benefit-icon-box svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .kinboni-benefit-icon-box i'   => 'font-size: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_box_radius',
            array(
                'label'      => esc_html__('Icon Box Radius', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', 'rem', '%'),
                'selectors'  => array(
                    '{{WRAPPER}} .kinboni-benefit-icon-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: TYPOGRAPHY & TEXT
        // ==========================================
        $this->start_controls_section(
            'section_style_typography',
            array(
                'label' => esc_html__('Typography & Text', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'heading_title_style',
            array(
                'label'     => esc_html__('Title', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .kinboni-benefit-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label'     => esc_html__('Title Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-benefit-title' => 'color: {{VALUE}};',
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
                'selector' => '{{WRAPPER}} .kinboni-benefit-desc',
            )
        );

        $this->add_control(
            'desc_color',
            array(
                'label'     => esc_html__('Description Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .kinboni-benefit-desc' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render Widget Output on the Frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $benefits_list = !empty($settings['benefits_list']) && is_array($settings['benefits_list']) ? $settings['benefits_list'] : array();
        if (empty($benefits_list)) {
            return;
        }

        $section_id = !empty($settings['section_html_id']) ? sanitize_html_class($settings['section_html_id']) : '';

        // Grid columns mapping
        $cols_desktop = !empty($settings['columns']) ? $settings['columns'] : '4';
        $cols_tablet  = !empty($settings['columns_tablet']) ? $settings['columns_tablet'] : '2';
        $cols_mobile  = !empty($settings['columns_mobile']) ? $settings['columns_mobile'] : '1';

        $col_classes = array();
        // Desktop
        switch ($cols_desktop) {
            case '1':
                $col_classes[] = 'lg:grid-cols-1';
                break;
            case '2':
                $col_classes[] = 'lg:grid-cols-2';
                break;
            case '3':
                $col_classes[] = 'lg:grid-cols-3';
                break;
            default:
                $col_classes[] = 'lg:grid-cols-4';
                break;
        }

        // Tablet
        switch ($cols_tablet) {
            case '1':
                $col_classes[] = 'sm:grid-cols-1';
                break;
            case '3':
                $col_classes[] = 'sm:grid-cols-3';
                break;
            default:
                $col_classes[] = 'sm:grid-cols-2';
                break;
        }

        // Mobile
        switch ($cols_mobile) {
            case '2':
                $col_classes[] = 'grid-cols-2';
                break;
            default:
                $col_classes[] = 'grid-cols-1';
                break;
        }

        $grid_class = implode(' ', $col_classes);

        // Border classes
        $border_classes = array();
        if (!empty($settings['show_border_top']) && $settings['show_border_top'] === 'yes') {
            $border_classes[] = 'border-t border-stone-200/80 dark:border-stone-800/80';
        }
        if (!empty($settings['show_border_bottom']) && $settings['show_border_bottom'] === 'yes') {
            $border_classes[] = 'border-b border-stone-200/80 dark:border-stone-800/80';
        }
        $border_str = implode(' ', $border_classes);

        $hover_lift_class = (!empty($settings['card_hover_lift']) && $settings['card_hover_lift'] === 'yes') ? 'transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md' : 'transition-all duration-300';

        $title_tag = in_array($settings['title_tag'], array('h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p'), true) ? $settings['title_tag'] : 'h4';
        ?>
<section <?php if (!empty($section_id)) : ?>id="<?php echo esc_attr($section_id); ?>" <?php endif; ?>class="kinboni-trust-benefits-section py-12 bg-stone-100/40 dark:bg-stone-950/40 <?php echo esc_attr($border_str); ?>">
  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid <?php echo esc_attr($grid_class); ?> gap-6">
      <?php foreach ($benefits_list as $index => $item) : 
        $title = !empty($item['title']) ? $item['title'] : '';
        if (class_exists('Kinboni_Content')) {
            $badge_idx = $index + 1;
            $global_badge = Kinboni_Content::get('trust_badge_' . $badge_idx);
            if (empty($title) && !empty($global_badge)) {
                $title = $global_badge;
            }
        }
        $desc  = !empty($item['description']) ? $item['description'] : '';
        $icon_type = !empty($item['icon_type']) ? $item['icon_type'] : 'lucide';
        $lucide_icon = !empty($item['lucide_icon']) ? sanitize_text_field($item['lucide_icon']) : 'shield-check';

        $has_link = !empty($item['link']['url']);
        $tag = $has_link ? 'a' : 'div';
        $link_attr = '';
        if ($has_link) {
            $link_attr = 'href="' . esc_url($item['link']['url']) . '"';
            if (!empty($item['link']['is_external'])) {
                $link_attr .= ' target="_blank"';
            }
            if (!empty($item['link']['nofollow'])) {
                $link_attr .= ' rel="nofollow"';
            }
        }
      ?>
      <<?php echo $tag; ?> <?php echo $link_attr; ?> class="kinboni-benefit-card flex items-center gap-4 p-4 rounded-2xl bg-white/70 dark:bg-stone-900/70 border border-stone-200/60 dark:border-stone-800 shadow-sm <?php echo esc_attr($hover_lift_class); ?>">
        <div class="kinboni-benefit-icon-box w-12 h-12 rounded-xl bg-gold-400/20 text-gold-700 dark:text-gold-400 flex items-center justify-center flex-shrink-0">
          <?php if ($icon_type === 'elementor' && !empty($item['elementor_icon']['value'])) : ?>
            <?php Icons_Manager::render_icon($item['elementor_icon'], array('aria-hidden' => 'true', 'class' => 'w-6 h-6')); ?>
          <?php else : ?>
            <i data-lucide="<?php echo esc_attr($lucide_icon); ?>" class="w-6 h-6"></i>
          <?php endif; ?>
        </div>
        <div class="min-w-0">
          <?php if (!empty($title)) : ?>
          <<?php echo $title_tag; ?> class="kinboni-benefit-title text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-white truncate sm:text-clip">
            <?php echo esc_html($title); ?>
          </<?php echo $title_tag; ?>>
          <?php endif; ?>
          <?php if (!empty($desc)) : ?>
          <p class="kinboni-benefit-desc text-[11px] text-stone-600 dark:text-stone-300 mt-0.5">
            <?php echo esc_html($desc); ?>
          </p>
          <?php endif; ?>
        </div>
      </<?php echo $tag; ?>>
      <?php endforeach; ?>
    </div>
  </div>
</section>
        <?php
    }
}

