<?php
/**
 * Kinboni Hero Banner Slider & Showcase - Elementor Widget
 *
 * Implements modern high-converting e-commerce dual-banner layout:
 * - Left: Interactive promotional banner slider (Swiper) with full clickable banners
 * - Right: High-impact featured promo banner with full clickable link (supports single or stacked banners)
 * - Bottom: 4 Trust & Benefit badges (Free Delivery, Vouchers, Easy Returns, Certified Genuine)
 *
 * Fully customizable via Elementor controls with instant zero-asset SVG fallbacks.
 *
 * @package Kinboni_Core
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

class Kinboni_Widget_Hero extends Widget_Base {

    public function get_name() {
        return 'kinboni_hero_triple_slider';
    }

    public function get_title() {
        return esc_html__('Hero Banner Slider & Showcase', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-slides';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('hero', 'slider', 'banner', 'swiper', 'showcase', 'ecommerce', 'ponnokino', 'kinboni');
    }

    public function get_script_depends() {
        return array('swiper-js', 'lucide-icons', 'kinboni-core-js');
    }

    public function get_style_depends() {
        return array('swiper-css', 'kinboni-core-style');
    }

    /**
     * Fallback SVG banner for Fast & Reliable Delivery (Left Slider 1)
     */
    private function get_default_delivery_banner() {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 880 450" width="100%" height="100%">
  <defs>
    <linearGradient id="sky_bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#f0f9ff"/>
      <stop offset="60%" stop-color="#e0f2fe"/>
      <stop offset="100%" stop-color="#f8fafc"/>
    </linearGradient>
    <linearGradient id="orange_grad" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="#ea580c"/>
      <stop offset="100%" stop-color="#f97316"/>
    </linearGradient>
    <linearGradient id="road_grad" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#22c55e"/>
      <stop offset="100%" stop-color="#15803d"/>
    </linearGradient>
    <linearGradient id="box_grad" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#d97706"/>
      <stop offset="100%" stop-color="#b45309"/>
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="6" flood-opacity="0.15"/>
    </filter>
  </defs>

  <rect width="880" height="450" fill="url(#sky_bg)"/>

  <path d="M420,320 L420,240 L450,240 L450,220 L480,220 L480,260 L510,260 L510,210 L540,210 L540,270 L570,270 L570,230 L600,230 L600,320 Z" fill="#bae6fd" opacity="0.45"/>
  <path d="M580,320 L580,250 L610,250 L610,200 L640,200 L640,260 L670,260 L670,240 L700,240 L700,320 Z" fill="#93c5fd" opacity="0.4"/>

  <path d="M380,450 Q560,340 880,360 L880,450 Z" fill="url(#road_grad)"/>
  <path d="M410,450 Q580,365 880,385 L880,450 Z" fill="#334155" opacity="0.9"/>
  <path d="M460,440 Q620,380 880,400" stroke="#f8fafc" stroke-width="3" stroke-dasharray="14,14" fill="none" opacity="0.8"/>

  <path d="M450,220 C490,140 550,110 630,130 S720,110 760,110" stroke="#ea580c" stroke-width="2.5" stroke-dasharray="6,6" fill="none" opacity="0.85"/>
  <g transform="translate(760, 95)" filter="url(#shadow)">
    <circle cx="0" cy="0" r="14" fill="#ea580c"/>
    <circle cx="0" cy="0" r="5" fill="#ffffff"/>
    <path d="M-6,9 L0,22 L6,9 Z" fill="#ea580c"/>
  </g>

  <g transform="translate(60, 60)">
    <g transform="translate(0, 0)">
      <rect x="0" y="2" width="34" height="26" rx="6" fill="#ea580c"/>
      <path d="M8,10 L26,10 L23,20 L11,20 Z" fill="#ffffff"/>
      <circle cx="13" cy="24" r="2.5" fill="#ffffff"/>
      <circle cx="21" cy="24" r="2.5" fill="#ffffff"/>
      <text x="44" y="22" font-family="system-ui, sans-serif" font-size="28" font-weight="900" fill="#0f172a" letter-spacing="-0.5">Ponno<tspan fill="#ea580c">kino</tspan></text>
      <text x="45" y="38" font-family="system-ui, sans-serif" font-size="11" font-weight="600" fill="#64748b" letter-spacing="0.5">Shop Smart, Live Better</text>
    </g>

    <text x="0" y="115" font-family="system-ui, sans-serif" font-size="38" font-weight="800" fill="#0f172a" letter-spacing="-0.5">Fast &amp; Reliable</text>
    <text x="0" y="175" font-family="system-ui, sans-serif" font-size="54" font-weight="900" fill="#ea580c" letter-spacing="-1">Delivery</text>
    <text x="0" y="220" font-family="system-ui, sans-serif" font-size="24" font-weight="700" fill="#1e293b">Across Bangladesh</text>

    <g transform="translate(0, 255)" filter="url(#shadow)">
      <rect width="165" height="48" rx="24" fill="#0f172a"/>
      <text x="28" y="30" font-family="system-ui, sans-serif" font-size="15" font-weight="700" fill="#ffffff">Shop Now</text>
      <g transform="translate(122, 18)">
        <path d="M0,6 L14,6 M9,1 L14,6 L9,11" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
      </g>
    </g>
  </g>

  <g transform="translate(680, 260)" filter="url(#shadow)">
    <rect x="-10" y="-45" width="45" height="40" rx="6" fill="#ea580c"/>
    <text x="12" y="-20" font-family="system-ui, sans-serif" font-size="12" font-weight="bold" fill="#ffffff" text-anchor="middle">P</text>
    <ellipse cx="25" cy="5" rx="35" ry="16" fill="#ea580c"/>
    <path d="M35,-5 L48,-35 L40,-35" stroke="#ea580c" stroke-width="6" stroke-linecap="round"/>
    <circle cx="48" cy="-38" r="6" fill="#f8fafc"/>
    <circle cx="-5" cy="22" r="16" fill="#1e293b"/>
    <circle cx="-5" cy="22" r="7" fill="#cbd5e1"/>
    <circle cx="55" cy="22" r="16" fill="#1e293b"/>
    <circle cx="55" cy="22" r="7" fill="#cbd5e1"/>
  </g>

  <g transform="translate(480, 100)" filter="url(#shadow)">
    <ellipse cx="65" cy="45" rx="26" ry="28" fill="#fed7aa"/>
    <path d="M38,36 C38,18 92,18 92,36 Z" fill="#ea580c"/>
    <path d="M38,36 Q80,24 110,34" stroke="#ea580c" stroke-width="8" stroke-linecap="round"/>
    <circle cx="78" cy="42" r="3" fill="#431407"/>
    <path d="M72,55 Q80,62 88,55" stroke="#9a3412" stroke-width="2.5" fill="none" stroke-linecap="round"/>
    <path d="M60,28 Q75,22 88,26" stroke="#431407" stroke-width="3" fill="none"/>
    
    <path d="M30,75 L100,75 L115,220 L15,220 Z" fill="#ea580c"/>
    <path d="M55,75 L65,105 L75,75 Z" fill="#ffffff"/>
    <text x="65" y="130" font-family="system-ui, sans-serif" font-size="14" font-weight="900" fill="#ffffff" text-anchor="middle">P</text>

    <g transform="translate(-25, 140)">
      <rect width="130" height="95" rx="6" fill="url(#box_grad)"/>
      <rect width="130" height="18" fill="#92400e" opacity="0.3"/>
      <rect x="52" y="0" width="26" height="95" fill="#fde68a" opacity="0.8"/>
      <text x="65" y="58" font-family="system-ui, sans-serif" font-size="15" font-weight="900" fill="#78350f" text-anchor="middle">Ponnokino</text>
      <text x="65" y="70" font-family="system-ui, sans-serif" font-size="8" font-weight="bold" fill="#78350f" text-anchor="middle">Shop Smart, Live Better</text>
    </g>

    <path d="M22,88 Q-10,130 -15,165 Q0,180 15,160" fill="#ea580c"/>
    <circle cx="-15" cy="165" r="12" fill="#fed7aa"/>
    <path d="M108,88 Q140,130 135,165 Q120,180 105,160" fill="#ea580c"/>
    <circle cx="135" cy="165" r="12" fill="#fed7aa"/>
  </g>
</svg>
SVG;
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    /**
     * Fallback SVG banner for Pure Honey (Right Promo Banner)
     */
    private function get_default_honey_banner() {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 450 450" width="100%" height="100%">
  <defs>
    <linearGradient id="honey_bg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#fffbeb"/>
      <stop offset="60%" stop-color="#fef3c7"/>
      <stop offset="100%" stop-color="#fde68a"/>
    </linearGradient>
    <linearGradient id="amber_grad" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#f59e0b"/>
      <stop offset="50%" stop-color="#d97706"/>
      <stop offset="100%" stop-color="#b45309"/>
    </linearGradient>
    <filter id="h_shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="6" flood-opacity="0.12"/>
    </filter>
  </defs>

  <rect width="450" height="450" fill="url(#honey_bg)"/>

  <g stroke="#f59e0b" stroke-width="1.5" fill="none" opacity="0.35" transform="translate(10, 180)">
    <polygon points="20,0 40,11 40,34 20,45 0,34 0,11"/>
    <polygon points="55,20 75,31 75,54 55,65 35,54 35,31"/>
    <polygon points="20,45 40,56 40,79 20,90 0,79 0,56"/>
    <polygon points="55,65 75,76 75,99 55,110 35,99 35,76"/>
    <polygon points="20,90 40,101 40,124 20,135 0,124 0,101"/>
  </g>

  <g transform="translate(130, 20)">
    <rect x="0" y="2" width="26" height="20" rx="4" fill="#ea580c"/>
    <path d="M6,7 L20,7 L18,15 L8,15 Z" fill="#ffffff"/>
    <circle cx="9" cy="18" r="2" fill="#ffffff"/>
    <circle cx="17" cy="18" r="2" fill="#ffffff"/>
    <text x="34" y="18" font-family="system-ui, sans-serif" font-size="20" font-weight="900" fill="#0f172a">Ponno<tspan fill="#ea580c">kino</tspan></text>
    <text x="35" y="30" font-family="system-ui, sans-serif" font-size="9" font-weight="600" fill="#64748b">Shop Smart, Live Better</text>
  </g>

  <g transform="translate(225, 80)" text-anchor="middle">
    <text y="0" font-family="system-ui, sans-serif" font-size="22" font-weight="800" fill="#16a34a" letter-spacing="-0.3">Pure &amp; Natural</text>
    <text y="42" font-family="system-ui, sans-serif" font-size="44" font-weight="900" fill="#b45309" letter-spacing="-1">Honey</text>
    <text y="64" font-family="system-ui, sans-serif" font-size="12" font-weight="700" fill="#78350f">Healthy Choice for a Healthier Life</text>
  </g>

  <g transform="translate(70, 160)" filter="url(#h_shadow)">
    <g transform="rotate(-35, 60, 50)">
      <rect x="40" y="45" width="70" height="8" rx="4" fill="#a16207"/>
      <ellipse cx="35" cy="49" rx="14" ry="12" fill="#ca8a04"/>
      <ellipse cx="30" cy="49" rx="12" ry="10" fill="#a16207"/>
      <ellipse cx="25" cy="49" rx="10" ry="8" fill="#ca8a04"/>
      <path d="M25,56 C25,75 35,80 35,88 C35,93 30,97 25,97 C20,97 15,93 15,88 C15,80 25,75 25,56 Z" fill="url(#amber_grad)"/>
    </g>

    <g transform="translate(100, 20)">
      <rect x="0" y="25" width="120" height="110" rx="20" fill="url(#amber_grad)"/>
      <rect x="8" y="32" width="104" height="95" rx="15" fill="#f59e0b" opacity="0.6"/>
      <rect x="15" y="10" width="90" height="18" rx="6" fill="#fef08a"/>
      <line x1="12" y1="20" x2="108" y2="20" stroke="#78350f" stroke-width="3"/>
      <g transform="translate(68, 22) rotate(10)">
        <polygon points="0,0 26,0 34,16 26,32 0,32" fill="#fffbeb" stroke="#ca8a04" stroke-width="1.5"/>
        <text x="14" y="20" font-family="system-ui, sans-serif" font-size="7" font-weight="900" fill="#78350f" text-anchor="middle">Natural</text>
      </g>
    </g>

    <g transform="translate(180, 125)">
      <ellipse cx="10" cy="10" rx="14" ry="7" fill="#16a34a" transform="rotate(-30 10 10)"/>
      <ellipse cx="24" cy="5" rx="12" ry="6" fill="#22c55e" transform="rotate(20 24 5)"/>
      <circle cx="2" cy="0" r="5" fill="#ffffff"/>
      <circle cx="2" cy="0" r="2.5" fill="#facc15"/>
    </g>
  </g>

  <g transform="translate(320, 175)">
    <g transform="translate(0, 0)">
      <rect width="115" height="28" rx="14" fill="#ffffff" stroke="#86efac" stroke-width="1.5"/>
      <circle cx="14" cy="14" r="8" fill="#16a34a"/>
      <path d="M10,14 L13,17 L18,11" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round"/>
      <text x="30" y="18" font-family="system-ui, sans-serif" font-size="11" font-weight="700" fill="#15803d">100% Pure</text>
    </g>
    <g transform="translate(0, 36)">
      <rect width="115" height="28" rx="14" fill="#ffffff" stroke="#86efac" stroke-width="1.5"/>
      <circle cx="14" cy="14" r="8" fill="#16a34a"/>
      <path d="M10,14 L13,17 L18,11" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round"/>
      <text x="27" y="18" font-family="system-ui, sans-serif" font-size="10" font-weight="700" fill="#15803d">Trusted Quality</text>
    </g>
    <g transform="translate(0, 72)">
      <rect width="115" height="28" rx="14" fill="#ffffff" stroke="#86efac" stroke-width="1.5"/>
      <circle cx="14" cy="14" r="8" fill="#16a34a"/>
      <path d="M10,14 L13,17 L18,11" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round"/>
      <text x="30" y="18" font-family="system-ui, sans-serif" font-size="10.5" font-weight="700" fill="#15803d">Fast Delivery</text>
    </g>
  </g>

  <g transform="translate(305, 385)" filter="url(#h_shadow)">
    <rect width="125" height="40" rx="20" fill="#ea580c"/>
    <text x="22" y="25" font-family="system-ui, sans-serif" font-size="13" font-weight="800" fill="#ffffff">Shop Now</text>
    <path d="M92,20 L102,20 M98,16 L102,20 L98,24" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" fill="none"/>
  </g>
</svg>
SVG;
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    /**
     * Fallback SVG banner for Mega Flash Sale (Left Slider 2)
     */
    private function get_default_sale_banner() {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 880 450" width="100%" height="100%">
  <defs>
    <linearGradient id="sale_bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#1e1b4b"/>
      <stop offset="50%" stop-color="#581c87"/>
      <stop offset="100%" stop-color="#9a3412"/>
    </linearGradient>
    <linearGradient id="gold_grad" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="#f59e0b"/>
      <stop offset="100%" stop-color="#facc15"/>
    </linearGradient>
    <filter id="s_shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="6" flood-opacity="0.25"/>
    </filter>
  </defs>

  <rect width="880" height="450" fill="url(#sale_bg)"/>

  <circle cx="780" cy="120" r="180" fill="#a855f7" opacity="0.15"/>
  <circle cx="640" cy="360" r="140" fill="#f97316" opacity="0.12"/>
  <circle cx="100" cy="400" r="160" fill="#3b82f6" opacity="0.1"/>

  <g transform="translate(60, 50)">
    <rect x="0" y="2" width="34" height="26" rx="6" fill="#f59e0b"/>
    <path d="M8,10 L26,10 L23,20 L11,20 Z" fill="#1e1b4b"/>
    <circle cx="13" cy="24" r="2.5" fill="#1e1b4b"/>
    <circle cx="21" cy="24" r="2.5" fill="#1e1b4b"/>
    <text x="44" y="22" font-family="system-ui, sans-serif" font-size="28" font-weight="900" fill="#ffffff" letter-spacing="-0.5">Ponno<tspan fill="#f59e0b">kino</tspan></text>
    <text x="45" y="38" font-family="system-ui, sans-serif" font-size="11" font-weight="600" fill="#cbd5e1" letter-spacing="0.5">Shop Smart, Live Better</text>
  </g>

  <g transform="translate(60, 140)">
    <rect width="175" height="30" rx="15" fill="#f59e0b" opacity="0.2"/>
    <rect x="1" y="1" width="173" height="28" rx="14" stroke="#f59e0b" stroke-width="1.5" fill="none"/>
    <text x="87" y="19" font-family="system-ui, sans-serif" font-size="12" font-weight="800" fill="#facc15" text-anchor="middle" letter-spacing="1">⚡ LIMITED TIME OFFER</text>

    <text x="0" y="70" font-family="system-ui, sans-serif" font-size="44" font-weight="900" fill="#ffffff" letter-spacing="-1">MEGA FLASH SALE</text>
    <text x="0" y="125" font-family="system-ui, sans-serif" font-size="52" font-weight="900" fill="url(#gold_grad)" letter-spacing="-1">UP TO 60% OFF</text>
    <text x="0" y="165" font-family="system-ui, sans-serif" font-size="18" font-weight="600" fill="#e2e8f0">Top Brands, Gadgets, Fashion &amp; Lifestyle</text>

    <g transform="translate(0, 195)" filter="url(#s_shadow)">
      <rect width="180" height="48" rx="24" fill="#f59e0b"/>
      <text x="32" y="30" font-family="system-ui, sans-serif" font-size="15" font-weight="800" fill="#1e1b4b">Explore Deals</text>
      <g transform="translate(140, 18)">
        <path d="M0,6 L14,6 M9,1 L14,6 L9,11" stroke="#1e1b4b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
      </g>
    </g>
  </g>

  <g transform="translate(680, 220)" filter="url(#s_shadow)">
    <circle cx="0" cy="0" r="110" fill="#f59e0b"/>
    <circle cx="0" cy="0" r="102" fill="#1e1b4b"/>
    <circle cx="0" cy="0" r="95" stroke="#f59e0b" stroke-dasharray="8,6" stroke-width="2.5" fill="none"/>
    <text x="0" y="-30" font-family="system-ui, sans-serif" font-size="20" font-weight="900" fill="#facc15" text-anchor="middle" letter-spacing="2">SAVE BIG</text>
    <text x="0" y="25" font-family="system-ui, sans-serif" font-size="58" font-weight="900" fill="#ffffff" text-anchor="middle">60%</text>
    <text x="0" y="60" font-family="system-ui, sans-serif" font-size="22" font-weight="800" fill="#facc15" text-anchor="middle">DISCOUNT</text>
  </g>
</svg>
SVG;
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    /**
     * Fallback default trust badges matching reference design
     */
    private function get_default_badges() {
        return array(
            array(
                'icon'       => 'truck',
                'title'      => esc_html__('Free Delivery', 'kinboni-core'),
                'subtitle'   => esc_html__('Orders over ৳2,000', 'kinboni-core'),
                'icon_color' => '#ea580c',
            ),
            array(
                'icon'       => 'ticket-percent',
                'title'      => esc_html__('৳500 Vouchers', 'kinboni-core'),
                'subtitle'   => esc_html__('Collect & save', 'kinboni-core'),
                'icon_color' => '#f59e0b',
            ),
            array(
                'icon'       => 'rotate-ccw',
                'title'      => esc_html__('Easy Returns', 'kinboni-core'),
                'subtitle'   => esc_html__('7-day policy', 'kinboni-core'),
                'icon_color' => '#ea580c',
            ),
            array(
                'icon'       => 'shield-check',
                'title'      => esc_html__('100% Authentic', 'kinboni-core'),
                'subtitle'   => esc_html__('Certified brands', 'kinboni-core'),
                'icon_color' => '#10b981',
            ),
        );
    }

    /**
     * Get fallback banner image for a given slide index
     */
    private function get_default_banner_image($index = 0) {
        if ($index % 2 === 0) {
            return $this->get_default_delivery_banner();
        }
        return $this->get_default_sale_banner();
    }

    protected function register_controls() {

        // =====================================================================
        // TAB 1: CONTENT
        // =====================================================================

        // 1. Left Banner Slider Controls
        $this->start_controls_section(
            'section_left_slider',
            array(
                'label' => esc_html__('Left Banner Slider (Main)', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $slide_repeater = new Repeater();

        $slide_repeater->add_control(
            'slide_image',
            array(
                'label'       => esc_html__('Banner Image', 'kinboni-core'),
                'type'        => Controls_Manager::MEDIA,
                'dynamic'     => array('active' => true),
                'default'     => array('url' => ''),
                'description' => esc_html__('Upload your banner image (Recommended: 880x450px or 16:9 ratio). Leave empty for high-definition illustrated SVG fallback.', 'kinboni-core'),
            )
        );

        $slide_repeater->add_control(
            'slide_link',
            array(
                'label'       => esc_html__('Full Banner Link URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => 'https://...',
                'dynamic'     => array('active' => true),
                'default'     => array(
                    'url'         => '#',
                    'is_external' => false,
                    'nofollow'    => false,
                ),
                'description' => esc_html__('When customers click anywhere on this banner slide, they will be redirected to this URL.', 'kinboni-core'),
            )
        );

        $slide_repeater->add_control(
            'slide_title',
            array(
                'label'       => esc_html__('Banner Title / Image Alt', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Fast & Reliable Delivery Across Bangladesh', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $this->add_control(
            'slider_slides',
            array(
                'label'       => esc_html__('Banner Slides', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $slide_repeater->get_controls(),
                'default'     => array(
                    array(
                        'slide_title' => esc_html__('Fast & Reliable Delivery Across Bangladesh', 'kinboni-core'),
                        'slide_link'  => array('url' => '#'),
                    ),
                    array(
                        'slide_title' => esc_html__('Super Mega Sale - Up to 60% Off', 'kinboni-core'),
                        'slide_link'  => array('url' => '#'),
                    ),
                ),
                'title_field' => '{{{ slide_title }}}',
            )
        );

        $this->add_control(
            'slider_heading_settings',
            array(
                'label'     => esc_html__('Carousel Settings', 'kinboni-core'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'slider_autoplay',
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
            'slider_autoplay_delay',
            array(
                'label'     => esc_html__('Autoplay Delay (ms)', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 4500,
                'min'       => 1500,
                'max'       => 15000,
                'step'      => 500,
                'condition' => array('slider_autoplay' => 'yes'),
            )
        );

        $this->add_control(
            'slider_loop',
            array(
                'label'        => esc_html__('Infinite Loop', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'slider_speed',
            array(
                'label'   => esc_html__('Transition Speed (ms)', 'kinboni-core'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 600,
                'min'     => 200,
                'max'     => 2500,
                'step'    => 100,
            )
        );

        $this->add_control(
            'slider_effect',
            array(
                'label'   => esc_html__('Transition Effect', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'slide',
                'options' => array(
                    'slide' => esc_html__('Slide', 'kinboni-core'),
                    'fade'  => esc_html__('Fade', 'kinboni-core'),
                ),
            )
        );

        $this->add_control(
            'slider_show_arrows',
            array(
                'label'        => esc_html__('Show Navigation Arrows', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'slider_show_pagination',
            array(
                'label'        => esc_html__('Show Pagination Dots', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // 2. Right Featured Banner Controls
        $this->start_controls_section(
            'section_right_banner',
            array(
                'label' => esc_html__('Right Featured Banner (Secondary)', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'right_banner_image',
            array(
                'label'       => esc_html__('Banner Image', 'kinboni-core'),
                'type'        => Controls_Manager::MEDIA,
                'dynamic'     => array('active' => true),
                'default'     => array('url' => ''),
                'description' => esc_html__('Upload your banner image (Recommended: 450x450px or square/portrait ratio). Leave empty for high-definition illustrated SVG fallback.', 'kinboni-core'),
            )
        );

        $this->add_control(
            'right_banner_link',
            array(
                'label'       => esc_html__('Full Banner Link URL', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => 'https://...',
                'dynamic'     => array('active' => true),
                'default'     => array(
                    'url'         => '#',
                    'is_external' => false,
                    'nofollow'    => false,
                ),
                'description' => esc_html__('Redirect URL when clicking on this banner.', 'kinboni-core'),
            )
        );

        $this->add_control(
            'right_banner_title',
            array(
                'label'       => esc_html__('Banner Title / Image Alt', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Pure & Natural Honey - Healthy Choice for a Healthier Life', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $this->add_control(
            'enable_second_right_banner',
            array(
                'label'        => esc_html__('Add Second Stacked Banner', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'no',
                'description'  => esc_html__('Enable to stack two smaller promo banners on the right instead of one large banner.', 'kinboni-core'),
            )
        );

        $this->add_control(
            'right_banner_2_image',
            array(
                'label'     => esc_html__('Second Banner Image', 'kinboni-core'),
                'type'      => Controls_Manager::MEDIA,
                'dynamic'   => array('active' => true),
                'condition' => array('enable_second_right_banner' => 'yes'),
            )
        );

        $this->add_control(
            'right_banner_2_link',
            array(
                'label'     => esc_html__('Second Banner Link URL', 'kinboni-core'),
                'type'      => Controls_Manager::URL,
                'condition' => array('enable_second_right_banner' => 'yes'),
                'default'   => array('url' => '#'),
            )
        );

        $this->add_control(
            'right_banner_2_title',
            array(
                'label'     => esc_html__('Second Banner Title / Alt', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'condition' => array('enable_second_right_banner' => 'yes'),
                'default'   => esc_html__('Special Promotion Banner', 'kinboni-core'),
            )
        );

        $this->end_controls_section();

        // 3. Bottom Trust & Benefit Badges Controls
        $this->start_controls_section(
            'section_trust_badges',
            array(
                'label' => esc_html__('Bottom Trust & Feature Badges', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_badges',
            array(
                'label'        => esc_html__('Display Trust Badges', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Show', 'kinboni-core'),
                'label_off'    => esc_html__('Hide', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $badge_repeater = new Repeater();

        $badge_repeater->add_control(
            'icon',
            array(
                'label'       => esc_html__('Lucide Icon Name', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'truck',
                'description' => esc_html__('e.g. truck, ticket-percent, rotate-ccw, shield-check, award, gift, zap, badge-percent', 'kinboni-core'),
            )
        );

        $badge_repeater->add_control(
            'title',
            array(
                'label'       => esc_html__('Title', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Free Delivery', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $badge_repeater->add_control(
            'subtitle',
            array(
                'label'       => esc_html__('Subtitle', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Orders over ৳2,000', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $badge_repeater->add_control(
            'link',
            array(
                'label'       => esc_html__('Card Link (Optional)', 'kinboni-core'),
                'type'        => Controls_Manager::URL,
                'placeholder' => 'https://...',
            )
        );

        $badge_repeater->add_control(
            'icon_color',
            array(
                'label'   => esc_html__('Icon Accent Color', 'kinboni-core'),
                'type'    => Controls_Manager::COLOR,
                'default' => '#f59e0b',
            )
        );

        $this->add_control(
            'badges_list',
            array(
                'label'       => esc_html__('Badges Items (4 Columns)', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $badge_repeater->get_controls(),
                'default'     => array(
                    array(
                        'icon'       => 'truck',
                        'title'      => esc_html__('Free Delivery', 'kinboni-core'),
                        'subtitle'   => esc_html__('Orders over ৳2,000', 'kinboni-core'),
                        'icon_color' => '#ea580c',
                    ),
                    array(
                        'icon'       => 'ticket-percent',
                        'title'      => esc_html__('৳500 Vouchers', 'kinboni-core'),
                        'subtitle'   => esc_html__('Collect & save', 'kinboni-core'),
                        'icon_color' => '#f59e0b',
                    ),
                    array(
                        'icon'       => 'rotate-ccw',
                        'title'      => esc_html__('Easy Returns', 'kinboni-core'),
                        'subtitle'   => esc_html__('7-day policy', 'kinboni-core'),
                        'icon_color' => '#ea580c',
                    ),
                    array(
                        'icon'       => 'shield-check',
                        'title'      => esc_html__('100% Authentic', 'kinboni-core'),
                        'subtitle'   => esc_html__('Certified brands', 'kinboni-core'),
                        'icon_color' => '#10b981',
                    ),
                ),
                'title_field' => '{{{ title }}}',
                'condition'   => array('show_badges' => 'yes'),
            )
        );

        $this->end_controls_section();

        // =====================================================================
        // TAB 2: STYLE
        // =====================================================================

        // 1. Layout & Size
        $this->start_controls_section(
            'section_style_layout',
            array(
                'label' => esc_html__('Banner Layout & Size', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'hero_height',
            array(
                'label'      => esc_html__('Banner Height (px)', 'kinboni-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'vh'),
                'range'      => array(
                    'px' => array('min' => 140, 'max' => 700, 'step' => 5),
                    'vh' => array('min' => 20, 'max' => 85),
                ),
                'desktop_default' => array(
                    'unit' => 'px',
                    'size' => 430,
                ),
                'tablet_default'  => array(
                    'unit' => 'px',
                    'size' => 280,
                ),
                'mobile_default'  => array(
                    'unit' => 'px',
                    'size' => 195,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .hero-slider-box' => 'height: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} .hero-right-box'  => 'height: {{SIZE}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_responsive_control(
            'banner_border_radius',
            array(
                'label'      => esc_html__('Border Radius', 'kinboni-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'rem'),
                'default'    => array(
                    'top'      => '20',
                    'right'    => '20',
                    'bottom'   => '20',
                    'left'     => '20',
                    'unit'     => 'px',
                    'isLinked' => true,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .hero-slider-box'   => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .hero-right-box'    => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .hero-banner-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'banner_gap',
            array(
                'label'      => esc_html__('Columns Gap (px)', 'kinboni-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array('min' => 8, 'max' => 40),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 18,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .hero-banner-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // 2. Navigation & Pagination Styling
        $this->start_controls_section(
            'section_style_navigation',
            array(
                'label' => esc_html__('Navigation & Pagination', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'arrow_bg_color',
            array(
                'label'     => esc_html__('Arrows Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'default'   => 'rgba(255, 255, 255, 0.9)',
                'selectors' => array(
                    '{{WRAPPER}} .hero-banner-prev, {{WRAPPER}} .hero-banner-next' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'arrow_icon_color',
            array(
                'label'     => esc_html__('Arrows Icon Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#1c1917',
                'selectors' => array(
                    '{{WRAPPER}} .hero-banner-prev, {{WRAPPER}} .hero-banner-next' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'dot_active_color',
            array(
                'label'     => esc_html__('Active Pagination Dot', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ea580c',
                'selectors' => array(
                    '{{WRAPPER}} .hero-banner-pagination .swiper-pagination-bullet-active' => 'background-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_section();

        // 3. Badges Styling
        $this->start_controls_section(
            'section_style_badges',
            array(
                'label'     => esc_html__('Trust Badges Style', 'kinboni-core'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => array('show_badges' => 'yes'),
            )
        );

        $this->add_control(
            'badge_card_bg',
            array(
                'label'     => esc_html__('Card Background', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .hero-trust-badge-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'badge_card_bg_dark',
            array(
                'label'     => esc_html__('Card Background (Dark Mode)', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '.dark {{WRAPPER}} .hero-trust-badge-card, html.dark {{WRAPPER}} .hero-trust-badge-card' => 'background-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'badge_card_border',
            array(
                'label'     => esc_html__('Card Border Color', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .hero-trust-badge-card' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'badge_card_border_dark',
            array(
                'label'     => esc_html__('Card Border Color (Dark Mode)', 'kinboni-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '.dark {{WRAPPER}} .hero-trust-badge-card, html.dark {{WRAPPER}} .hero-trust-badge-card' => 'border-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget on frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $slides = !empty($settings['slider_slides']) ? $settings['slider_slides'] : array();
        if (empty($slides)) {
            $slides = array(
                array(
                    'slide_title' => esc_html__('Fast & Reliable Delivery Across Bangladesh', 'kinboni-core'),
                    'slide_link'  => array('url' => '#'),
                ),
                array(
                    'slide_title' => esc_html__('Super Mega Sale - Up to 60% Off', 'kinboni-core'),
                    'slide_link'  => array('url' => '#'),
                ),
            );
        }

        $autoplay_enabled = (!isset($settings['slider_autoplay']) || $settings['slider_autoplay'] === 'yes') ? 'true' : 'false';
        $autoplay_delay   = !empty($settings['slider_autoplay_delay']) ? absint($settings['slider_autoplay_delay']) : 4500;
        $loop_enabled     = (!isset($settings['slider_loop']) || $settings['slider_loop'] === 'yes') ? 'true' : 'false';
        $speed            = !empty($settings['slider_speed']) ? absint($settings['slider_speed']) : 600;
        $effect           = !empty($settings['slider_effect']) ? esc_attr($settings['slider_effect']) : 'slide';
        $show_pagination  = !isset($settings['slider_show_pagination']) || $settings['slider_show_pagination'] === 'yes';
        $show_arrows      = !isset($settings['slider_show_arrows']) || $settings['slider_show_arrows'] === 'yes';
        $show_badges      = !isset($settings['show_badges']) || $settings['show_badges'] === 'yes';
        $badges_list      = !empty($settings['badges_list']) ? $settings['badges_list'] : $this->get_default_badges();
        ?>

        <section class="kinboni-hero-banner-section max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-3 sm:py-5 font-sans" aria-label="Hero Banners Showcase">
          <div class="hero-banner-grid grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4 lg:gap-5 items-stretch">
            
            <!-- LEFT BANNER SLIDER (8 COLS on desktop ~66-68%) -->
            <div class="lg:col-span-8 flex flex-col">
              <div class="hero-slider-box relative overflow-hidden rounded-2xl shadow-sm border border-stone-200/70 dark:border-stone-800/80 bg-stone-100 dark:bg-stone-900 group">
                <div class="swiper hero-banner-swiper h-full w-full"
                     data-autoplay="<?php echo esc_attr($autoplay_enabled); ?>"
                     data-delay="<?php echo esc_attr($autoplay_delay); ?>"
                     data-loop="<?php echo esc_attr($loop_enabled); ?>"
                     data-speed="<?php echo esc_attr($speed); ?>"
                     data-effect="<?php echo esc_attr($effect); ?>">
                  
                  <div class="swiper-wrapper">
                    <?php foreach ($slides as $index => $slide) : 
                      $link_key = 'slide_link_' . $index;
                      $has_link = !empty($slide['slide_link']['url']);
                      if ($has_link) {
                          $this->add_link_attributes($link_key, $slide['slide_link']);
                      }
                      $slide_img = !empty($slide['slide_image']['url']) ? $slide['slide_image']['url'] : $this->get_default_banner_image($index);
                      $slide_alt = !empty($slide['slide_title']) ? $slide['slide_title'] : esc_html__('Promotional Banner', 'kinboni-core');
                    ?>
                      <div class="swiper-slide h-full w-full">
                        <?php if ($has_link) : ?>
                          <a <?php echo $this->get_render_attribute_string($link_key); ?> class="hero-banner-inner block w-full h-full relative overflow-hidden group/slide cursor-pointer" aria-label="<?php echo esc_attr($slide_alt); ?>">
                            <img src="<?php echo esc_url($slide_img); ?>" alt="<?php echo esc_attr($slide_alt); ?>" class="hero-banner-img w-full h-full object-cover transition-transform duration-700 ease-out group-hover/slide:scale-[1.02]" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>" />
                          </a>
                        <?php else : ?>
                          <div class="hero-banner-inner block w-full h-full relative overflow-hidden">
                            <img src="<?php echo esc_url($slide_img); ?>" alt="<?php echo esc_attr($slide_alt); ?>" class="hero-banner-img w-full h-full object-cover" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>" />
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <?php if ($show_pagination) : ?>
                    <div class="swiper-pagination hero-banner-pagination"></div>
                  <?php endif; ?>

                  <?php if ($show_arrows) : ?>
                    <button type="button" class="hero-banner-prev absolute left-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/90 dark:bg-stone-900/90 text-stone-800 dark:text-stone-100 shadow-md flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-gold-500 hover:text-white transition-all duration-300 cursor-pointer backdrop-blur-sm" aria-label="Previous Slide">
                      <i data-lucide="chevron-left" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </button>
                    <button type="button" class="hero-banner-next absolute right-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/90 dark:bg-stone-900/90 text-stone-800 dark:text-stone-100 shadow-md flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-gold-500 hover:text-white transition-all duration-300 cursor-pointer backdrop-blur-sm" aria-label="Next Slide">
                      <i data-lucide="chevron-right" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </button>
                  <?php endif; ?>

                </div>
              </div>
            </div>

            <!-- RIGHT FEATURED BANNER (4 COLS on desktop ~32-34%) -->
            <div class="lg:col-span-4 flex flex-col">
              <?php if (!empty($settings['enable_second_right_banner']) && $settings['enable_second_right_banner'] === 'yes') : ?>
                <!-- Two stacked banners -->
                <div class="flex flex-col gap-3 sm:gap-4 h-full">
                  <?php 
                    // Banner 1
                    $r1_has_link = !empty($settings['right_banner_link']['url']);
                    if ($r1_has_link) { $this->add_link_attributes('right_banner_link', $settings['right_banner_link']); }
                    $r1_img = !empty($settings['right_banner_image']['url']) ? $settings['right_banner_image']['url'] : $this->get_default_honey_banner();
                    $r1_alt = !empty($settings['right_banner_title']) ? $settings['right_banner_title'] : esc_html__('Promotional Banner 1', 'kinboni-core');
                  ?>
                  <div class="hero-right-box relative overflow-hidden rounded-2xl shadow-sm border border-stone-200/70 dark:border-stone-800/80 bg-stone-100 dark:bg-stone-900 flex-1 group/right1">
                    <?php if ($r1_has_link) : ?>
                      <a <?php echo $this->get_render_attribute_string('right_banner_link'); ?> class="hero-banner-inner block w-full h-full relative overflow-hidden cursor-pointer" aria-label="<?php echo esc_attr($r1_alt); ?>">
                        <img src="<?php echo esc_url($r1_img); ?>" alt="<?php echo esc_attr($r1_alt); ?>" class="hero-banner-img w-full h-full object-cover transition-transform duration-700 ease-out group-hover/right1:scale-[1.025]" loading="eager" />
                      </a>
                    <?php else : ?>
                      <div class="hero-banner-inner block w-full h-full relative overflow-hidden">
                        <img src="<?php echo esc_url($r1_img); ?>" alt="<?php echo esc_attr($r1_alt); ?>" class="hero-banner-img w-full h-full object-cover" loading="eager" />
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php 
                    // Banner 2
                    $r2_has_link = !empty($settings['right_banner_2_link']['url']);
                    if ($r2_has_link) { $this->add_link_attributes('right_banner_2_link', $settings['right_banner_2_link']); }
                    $r2_img = !empty($settings['right_banner_2_image']['url']) ? $settings['right_banner_2_image']['url'] : $this->get_default_sale_banner();
                    $r2_alt = !empty($settings['right_banner_2_title']) ? $settings['right_banner_2_title'] : esc_html__('Promotional Banner 2', 'kinboni-core');
                  ?>
                  <div class="hero-right-box relative overflow-hidden rounded-2xl shadow-sm border border-stone-200/70 dark:border-stone-800/80 bg-stone-100 dark:bg-stone-900 flex-1 group/right2">
                    <?php if ($r2_has_link) : ?>
                      <a <?php echo $this->get_render_attribute_string('right_banner_2_link'); ?> class="hero-banner-inner block w-full h-full relative overflow-hidden cursor-pointer" aria-label="<?php echo esc_attr($r2_alt); ?>">
                        <img src="<?php echo esc_url($r2_img); ?>" alt="<?php echo esc_attr($r2_alt); ?>" class="hero-banner-img w-full h-full object-cover transition-transform duration-700 ease-out group-hover/right2:scale-[1.025]" loading="lazy" />
                      </a>
                    <?php else : ?>
                      <div class="hero-banner-inner block w-full h-full relative overflow-hidden">
                        <img src="<?php echo esc_url($r2_img); ?>" alt="<?php echo esc_attr($r2_alt); ?>" class="hero-banner-img w-full h-full object-cover" loading="lazy" />
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php else : 
                // Single large banner matching left slider
                $right_has_link = !empty($settings['right_banner_link']['url']);
                if ($right_has_link) {
                    $this->add_link_attributes('right_banner_link', $settings['right_banner_link']);
                }
                $right_img = !empty($settings['right_banner_image']['url']) ? $settings['right_banner_image']['url'] : $this->get_default_honey_banner();
                $right_alt = !empty($settings['right_banner_title']) ? $settings['right_banner_title'] : esc_html__('Featured Promotional Banner', 'kinboni-core');
              ?>
                <div class="hero-right-box relative overflow-hidden rounded-2xl shadow-sm border border-stone-200/70 dark:border-stone-800/80 bg-stone-100 dark:bg-stone-900 group/right">
                  <?php if ($right_has_link) : ?>
                    <a <?php echo $this->get_render_attribute_string('right_banner_link'); ?> class="hero-banner-inner block w-full h-full relative overflow-hidden cursor-pointer" aria-label="<?php echo esc_attr($right_alt); ?>">
                      <img src="<?php echo esc_url($right_img); ?>" alt="<?php echo esc_attr($right_alt); ?>" class="hero-banner-img w-full h-full object-cover transition-transform duration-700 ease-out group-hover/right:scale-[1.025]" loading="eager" />
                    </a>
                  <?php else : ?>
                    <div class="hero-banner-inner block w-full h-full relative overflow-hidden">
                      <img src="<?php echo esc_url($right_img); ?>" alt="<?php echo esc_attr($right_alt); ?>" class="hero-banner-img w-full h-full object-cover" loading="eager" />
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>

          </div>

          <!-- BOTTOM TRUST & BENEFIT BADGES -->
          <?php if ($show_badges && !empty($badges_list)) : ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 mt-3 sm:mt-5">
              <?php foreach ($badges_list as $b_idx => $badge) : 
                $badge_has_link = !empty($badge['link']['url']);
                $b_link_key = 'badge_link_' . $b_idx;
                if ($badge_has_link) {
                    $this->add_link_attributes($b_link_key, $badge['link']);
                }
                $icon = !empty($badge['icon']) ? sanitize_text_field($badge['icon']) : 'check-circle';
                $tag = $badge_has_link ? 'a' : 'div';
                $tag_attrs = $badge_has_link ? $this->get_render_attribute_string($b_link_key) : '';
              ?>
                <<?php echo $tag; ?> <?php echo $tag_attrs; ?> class="hero-trust-badge-card flex items-center gap-3 sm:gap-3.5 p-3 sm:p-4 rounded-xl sm:rounded-2xl border shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md <?php echo $badge_has_link ? 'cursor-pointer hover:border-amber-400' : ''; ?>">
                  <div class="hero-trust-badge-icon w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="<?php echo esc_attr($icon); ?>" class="w-5 h-5 sm:w-5.5 sm:h-5.5"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <h4 class="hero-badge-title font-bold text-xs sm:text-sm tracking-tight leading-snug truncate">
                      <?php echo esc_html($badge['title']); ?>
                    </h4>
                    <?php if (!empty($badge['subtitle'])) : ?>
                      <p class="hero-badge-subtitle text-[11px] sm:text-xs mt-0.5 truncate leading-tight">
                        <?php echo esc_html($badge['subtitle']); ?>
                      </p>
                    <?php endif; ?>
                  </div>
                </<?php echo $tag; ?>>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
        <?php
    }
}
