<?php
/**
 * Kinboni Centralized Widget Content Helper
 *
 * Provides central content defaults, fallback content for Elementor widgets,
 * and storefront branding strings with 3-tier priority resolution:
 * 1. Explicitly configured Elementor content
 * 2. Dashboard Global Content (Kinboni_Content::get())
 * 3. Existing hardcoded default content
 *
 * @package Kinboni_Core
 * @version 1.0.3
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Content {

    /**
     * Retrieve content setting with fallback
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = '') {
        // 1. Direct kinboni_content_ option (Storefront Central Atelier Content)
        $option_key = 'kinboni_content_' . $key;
        $val = get_option($option_key);
        if ($val !== false && $val !== '') {
            return $val;
        }

        // 2. Direct kinboni_ option
        $alt_key = 'kinboni_' . $key;
        $alt_val = get_option($alt_key);
        if ($alt_val !== false && $alt_val !== '') {
            return $alt_val;
        }

        // 3. Fallback to direct key if already fully qualified
        $direct_val = get_option($key);
        if ($direct_val !== false && $direct_val !== '') {
            return $direct_val;
        }

        // Specific aliases
        if ($key === 'concierge_phone') {
            $wa = get_option('kinboni_whatsapp_phone');
            if ($wa !== false && $wa !== '') {
                return $wa;
            }
        } elseif ($key === 'whatsapp_phone') {
            $wa = get_option('kinboni_whatsapp_phone');
            if ($wa !== false && $wa !== '') {
                return $wa;
            }
        }

        $defaults = self::get_defaults();
        return isset($defaults[$key]) ? $defaults[$key] : $default;
    }

    /**
     * Resolve widget content following the strict priority:
     * 1. Explicitly configured Elementor content ($settings or raw editor data)
     * 2. Dashboard Global Content (Kinboni_Content::get)
     * 3. Existing hardcoded default content
     *
     * @param array $settings Widget display settings array
     * @param string $control_id Control name in Elementor widget
     * @param string $global_key Global content key in Kinboni_Content
     * @param mixed $hardcoded_default Hardcoded fallback value
     * @param object|null $widget Elementor widget instance (optional, for get_data('settings') check)
     * @return mixed
     */
    public static function resolve($settings, $control_id, $global_key, $hardcoded_default = '', $widget = null) {
        // Priority 1: Check if explicitly configured in Elementor editor data
        if (is_object($widget) && method_exists($widget, 'get_data')) {
            $raw = $widget->get_data('settings');
            if (is_array($raw) && isset($raw[$control_id]) && $raw[$control_id] !== '') {
                return $raw[$control_id];
            }
        }

        // Check display settings value
        $setting_val = isset($settings[$control_id]) ? $settings[$control_id] : null;

        // Priority 2: Dashboard Global Content
        $global_val = self::get($global_key, null);
        if ($global_val !== null && $global_val !== false && $global_val !== '') {
            // If the setting matches hardcoded default, was never touched, or is empty, use the Dashboard Global Content
            if ($setting_val === null || $setting_val === '' || $setting_val === $hardcoded_default) {
                return $global_val;
            }
        }

        // Priority 1 (fallback): If setting is non-empty and differs from hardcoded default, use it
        if ($setting_val !== null && $setting_val !== '') {
            return $setting_val;
        }

        // Priority 3: Existing hardcoded default content
        return $hardcoded_default;
    }

    /**
     * Get default storefront content strings
     *
     * @return array
     */
    public static function get_defaults() {
        return array(
            'brand_name'               => 'KINBONI',
            'brand_tagline'            => esc_html__('Artisanal Luxury & Master Craftsmanship', 'kinboni-core'),
            'brand_description'        => esc_html__('Fine chronographs, Tuscan leather goods and artisanal extraits, made in small batches for people who keep what they buy.', 'kinboni-core'),
            'concierge_phone'          => '8801700000000',
            'concierge_email'          => 'concierge@kinboni.com',
            'concierge_address'        => 'Banani, Road 11, Dhaka, Bangladesh',
            'whatsapp_phone'           => '8801700000000',
            'whatsapp_label'           => 'Instant Concierge',
            'trust_badge_1'            => esc_html__('100% Authentic Creations', 'kinboni-core'),
            'trust_badge_2'            => esc_html__('White-Glove Nationwide Dispatch', 'kinboni-core'),
            'trust_badge_3'            => esc_html__('Bespoke Atelier Packaging', 'kinboni-core'),
            'hero_headline'            => esc_html__('Objects made to be kept for life.', 'kinboni-core'),
            'hero_subtitle'            => esc_html__('Hand-burnished leather, precision chronographs and rare botanical extraits, made in small batches and delivered across Bangladesh.', 'kinboni-core'),
            'free_shipping_threshold'    => 2500,
            'delivery_fee_inside'        => 70,
            'delivery_fee_outside'       => 130,
            'vip_coupon_code'          => 'LUXE20',
            'bestsellers_title'        => esc_html__('Most Acquired Atelier Creations', 'kinboni-core'),
            'bestsellers_badge'        => esc_html__('Curated Archive', 'kinboni-core'),
            'featured_title'           => esc_html__('Featured Masterpieces', 'kinboni-core'),
            'featured_badge'           => esc_html__('Limited Editions', 'kinboni-core'),
            'flash_deals_title'        => esc_html__('Time-Sensitive Atelier Acquisitions', 'kinboni-core'),
            'flash_deals_badge'        => esc_html__('Privilege Windows', 'kinboni-core'),
            'categories_title'         => esc_html__('Atelier Departments', 'kinboni-core'),
            'categories_badge'         => esc_html__('Archive Taxonomy', 'kinboni-core'),
            'shop_title'               => esc_html__('The Complete Atelier Archive', 'kinboni-core'),
            'shop_badge'               => esc_html__('Curated Collection', 'kinboni-core'),
            'testimonials_title'       => esc_html__('Patron Appraisals & Testimonials', 'kinboni-core'),
            'testimonials_badge'       => esc_html__('Voices of Patrons', 'kinboni-core'),
            'faq_title'                => esc_html__('Patron Inquiries & Guidance', 'kinboni-core'),
            'faq_badge'                => esc_html__('Atelier Service', 'kinboni-core'),
            'heritage_title'           => esc_html__('Our Heritage of Uncompromising Craft', 'kinboni-core'),
            'heritage_badge'           => esc_html__('Master Guild Legacy', 'kinboni-core'),
            'size_guide_title'         => esc_html__('Atelier Measurement & Dimension Guide', 'kinboni-core'),
            'size_guide_badge'         => esc_html__('Precision Fit', 'kinboni-core'),
            'contact_title'            => esc_html__('Visit or Contact Our Atelier', 'kinboni-core'),
            'contact_badge'            => esc_html__('Patron Care & Concierge', 'kinboni-core'),
            'newsletter_title'         => esc_html__('The Kinboni Circle', 'kinboni-core'),
            'newsletter_badge'         => esc_html__('Private Invitation', 'kinboni-core'),
            'promo_title'              => esc_html__('Private Archive Acquisition', 'kinboni-core'),
            'promo_badge'              => esc_html__('Seasonal Privilege', 'kinboni-core'),
        );
    }
}
