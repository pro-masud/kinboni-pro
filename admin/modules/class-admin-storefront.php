<?php
/**
 * Kinboni Admin Module: Storefront Experience & Brand Controls
 *
 * Implements:
 * - Live Sales Social Proof Controls (Toggle, Interval, Sound, Curated vs Real)
 * - Floating WhatsApp Concierge Button (Position, Color, Number, Enable/Disable)
 * - Theme Customization (Luxury Gold Accent, Dark/Light Mode default, Fonts)
 * - Shopping Experience Settings (Cart recommendations, Recently viewed, Low-stock "Only X left" badge, Quick View size chart & delivery info)
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Storefront {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Save
        add_action('wp_ajax_kinboni_save_storefront_settings', array($this, 'ajax_save_storefront_settings'));

        // Front-end Hooks
        add_action('wp_footer', array($this, 'render_floating_whatsapp_btn'), 30);
        add_action('wp_head', array($this, 'render_storefront_dynamic_css'), 50);

        // Low stock text on single product
        add_action('woocommerce_single_product_summary', array($this, 'render_low_stock_urgency_badge'), 25);
    }

    /**
     * Render the Storefront Admin Page
     */
    public function render() {
        $settings = $this->get_storefront_settings();

        $view_file = KINBONI_CORE_PATH . 'admin/views/view-storefront.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Storefront view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Retrieve All Storefront Settings
     */
    public function get_storefront_settings() {
        return array(
            // Live Sales
            'live_sales_enabled'      => get_option('kinboni_live_sales_enabled', 'yes'),
            'live_sales_interval'     => (int) get_option('kinboni_live_sales_interval', 15),
            'live_sales_sound'        => get_option('kinboni_live_sales_sound', 'yes'),
            'live_sales_real_names'   => get_option('kinboni_live_sales_real_names', 'no'),
            // WhatsApp
            'whatsapp_enabled'        => get_option('kinboni_whatsapp_enabled', 'yes'),
            'whatsapp_phone'          => get_option('kinboni_whatsapp_phone', '8801700000000'),
            'whatsapp_position'       => get_option('kinboni_whatsapp_position', 'bottom-right'),
            'whatsapp_color'          => get_option('kinboni_whatsapp_color', '#25D366'),
            'whatsapp_label'          => get_option('kinboni_whatsapp_label', 'Instant Concierge'),
            // Theme Customization
            'theme_accent_color'      => get_option('kinboni_theme_accent_color', '#D4AF37'),
            'theme_mode_default'      => get_option('kinboni_theme_mode_default', 'system'),
            'theme_heading_font'      => get_option('kinboni_theme_heading_font', 'Cinzel, Georgia, serif'),
            // Shopping Experience
            'cart_recommendations'    => get_option('kinboni_cart_recommendations', 'yes'),
            'recently_viewed_enabled' => get_option('kinboni_recently_viewed_enabled', 'yes'),
            'low_stock_text_enabled'  => get_option('kinboni_low_stock_text_enabled', 'yes'),
            'low_stock_threshold'     => (int) get_option('kinboni_stock_threshold', 5),
            'quickview_sizechart'     => get_option('kinboni_quickview_sizechart', 'yes'),
            'quickview_delivery_info' => get_option('kinboni_quickview_delivery_info', 'yes'),
            // Payment Settings
            'bkash_merchant_number'   => get_option('kinboni_bkash_merchant_number', ''),
            // Central Content & Branding
            'brand_name'              => class_exists('Kinboni_Content') ? Kinboni_Content::get('brand_name', 'KINBONI') : 'KINBONI',
            'brand_tagline'           => class_exists('Kinboni_Content') ? Kinboni_Content::get('brand_tagline', 'Artisanal Luxury & Master Craftsmanship') : 'Artisanal Luxury & Master Craftsmanship',
            'concierge_email'         => class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_email', 'concierge@kinboni.com') : 'concierge@kinboni.com',
            'concierge_address'       => class_exists('Kinboni_Content') ? Kinboni_Content::get('concierge_address', 'Banani, Road 11, Dhaka, Bangladesh') : 'Banani, Road 11, Dhaka, Bangladesh',
        );
    }

    /**
     * AJAX: Save Storefront Settings
     */
    public function ajax_save_storefront_settings() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('storefront') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        // Live Sales
        $ls_enabled    = isset($_POST['live_sales_enabled']) && wp_unslash($_POST['live_sales_enabled']) === 'yes' ? 'yes' : 'no';
        $ls_interval   = isset($_POST['live_sales_interval']) ? max(5, absint(wp_unslash($_POST['live_sales_interval']))) : 15;
        $ls_sound      = isset($_POST['live_sales_sound']) && wp_unslash($_POST['live_sales_sound']) === 'yes' ? 'yes' : 'no';
        $ls_real_names = isset($_POST['live_sales_real_names']) && wp_unslash($_POST['live_sales_real_names']) === 'yes' ? 'yes' : 'no';

        // WhatsApp
        $wa_enabled  = isset($_POST['whatsapp_enabled']) && wp_unslash($_POST['whatsapp_enabled']) === 'yes' ? 'yes' : 'no';
        $wa_phone    = isset($_POST['whatsapp_phone']) ? sanitize_text_field(wp_unslash($_POST['whatsapp_phone'])) : '8801700000000';
        $wa_pos      = isset($_POST['whatsapp_position']) ? sanitize_key(wp_unslash($_POST['whatsapp_position'])) : 'bottom-right';
        $wa_color    = isset($_POST['whatsapp_color']) ? sanitize_hex_color(wp_unslash($_POST['whatsapp_color'])) : '#25D366';
        $wa_label    = isset($_POST['whatsapp_label']) ? sanitize_text_field(wp_unslash($_POST['whatsapp_label'])) : 'Instant Concierge';

        // Theme Customization
        $theme_accent = isset($_POST['theme_accent_color']) ? sanitize_hex_color(wp_unslash($_POST['theme_accent_color'])) : '#D4AF37';
        $theme_mode   = isset($_POST['theme_mode_default']) ? sanitize_key(wp_unslash($_POST['theme_mode_default'])) : 'system';
        $theme_font   = isset($_POST['theme_heading_font']) ? sanitize_text_field(wp_unslash($_POST['theme_heading_font'])) : 'Cinzel, Georgia, serif';

        // Shopping Experience
        $cart_rec     = isset($_POST['cart_recommendations']) && wp_unslash($_POST['cart_recommendations']) === 'yes' ? 'yes' : 'no';
        $recent_view  = isset($_POST['recently_viewed_enabled']) && wp_unslash($_POST['recently_viewed_enabled']) === 'yes' ? 'yes' : 'no';
        $low_stock_on = isset($_POST['low_stock_text_enabled']) && wp_unslash($_POST['low_stock_text_enabled']) === 'yes' ? 'yes' : 'no';
        $low_thresh   = isset($_POST['low_stock_threshold']) ? max(1, absint(wp_unslash($_POST['low_stock_threshold']))) : 5;
        $qv_size      = isset($_POST['quickview_sizechart']) && wp_unslash($_POST['quickview_sizechart']) === 'yes' ? 'yes' : 'no';
        $qv_del       = isset($_POST['quickview_delivery_info']) && wp_unslash($_POST['quickview_delivery_info']) === 'yes' ? 'yes' : 'no';
        $bkash_num    = isset($_POST['bkash_merchant_number']) ? sanitize_text_field(wp_unslash($_POST['bkash_merchant_number'])) : '';

        update_option('kinboni_live_sales_enabled', $ls_enabled);
        update_option('kinboni_live_sales_interval', $ls_interval);
        update_option('kinboni_live_sales_sound', $ls_sound);
        update_option('kinboni_live_sales_real_names', $ls_real_names);

        update_option('kinboni_whatsapp_enabled', $wa_enabled);
        update_option('kinboni_whatsapp_phone', $wa_phone);
        update_option('kinboni_whatsapp_position', $wa_pos);
        update_option('kinboni_whatsapp_color', $wa_color);
        update_option('kinboni_whatsapp_label', $wa_label);

        update_option('kinboni_theme_accent_color', $theme_accent);
        update_option('kinboni_theme_mode_default', $theme_mode);
        update_option('kinboni_theme_heading_font', $theme_font);

        update_option('kinboni_cart_recommendations', $cart_rec);
        update_option('kinboni_recently_viewed_enabled', $recent_view);
        update_option('kinboni_low_stock_text_enabled', $low_stock_on);
        update_option('kinboni_stock_threshold', $low_thresh);
        update_option('kinboni_quickview_sizechart', $qv_size);
        update_option('kinboni_quickview_delivery_info', $qv_del);
        update_option('kinboni_bkash_merchant_number', $bkash_num);

        if (isset($_POST['brand_name'])) {
            update_option('kinboni_content_brand_name', sanitize_text_field(wp_unslash($_POST['brand_name'])));
        }
        if (isset($_POST['brand_tagline'])) {
            update_option('kinboni_content_brand_tagline', sanitize_text_field(wp_unslash($_POST['brand_tagline'])));
        }
        if (isset($_POST['concierge_email'])) {
            update_option('kinboni_content_concierge_email', sanitize_email(wp_unslash($_POST['concierge_email'])));
        }
        if (isset($_POST['concierge_address'])) {
            update_option('kinboni_content_concierge_address', sanitize_text_field(wp_unslash($_POST['concierge_address'])));
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('storefront_settings_updated', 'Updated Storefront experience, WhatsApp and theme preferences.');
        }

        wp_send_json_success(array('message' => esc_html__('Storefront configurations saved successfully.', 'kinboni-core')));
    }

    /**
     * Render Floating WhatsApp Button in Front-end wp_footer
     */
    public function render_floating_whatsapp_btn() {
        if (is_admin()) return;

        $enabled = get_option('kinboni_whatsapp_enabled', 'yes');
        if ($enabled !== 'yes') return;

        $phone = get_option('kinboni_whatsapp_phone', '8801700000000');
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean_phone) || $clean_phone === '8801700000000') return;

        $pos_class = (get_option('kinboni_whatsapp_position', 'bottom-right') === 'bottom-left') ? 'left:24px;' : 'right:24px;';
        $color = get_option('kinboni_whatsapp_color', '#25D366');
        $label = get_option('kinboni_whatsapp_label', 'Instant Concierge');
        $wa_url = 'https://wa.me/' . $clean_phone;

        ?>
        <!-- KINBONI Pro Floating WhatsApp Concierge -->
        <a href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer" id="kinboni-floating-wa" style="position:fixed; z-index:999990; bottom:24px; <?php echo esc_attr($pos_class); ?> display:flex; align-items:center; gap:8px; background:<?php echo esc_attr($color); ?>; color:#FFF; padding:10px 16px; border-radius:30px; box-shadow:0 8px 24px rgba(0,0,0,0.2); text-decoration:none; font-family:-apple-system,sans-serif; font-size:13px; font-weight:700; transition:transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
            <svg style="width:20px; height:20px; fill:#FFF;" viewBox="0 0 24 24">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.586-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.075-2.227-.557-1.745-.72-2.884-2.483-2.973-2.6-.087-.118-.708-.941-.708-1.796 0-.854.448-1.275.607-1.448.16-.172.348-.216.464-.216.116 0 .232.002.333.007.106.005.249-.04.39.298.144.347.491 1.2.534 1.288.043.088.072.19.014.305-.058.115-.087.187-.174.289-.087.102-.183.228-.261.306-.087.087-.178.181-.077.354.102.174.451.744.969 1.205.666.593 1.228.777 1.402.864.174.088.276.073.379-.044.103-.117.436-.508.552-.682.116-.174.232-.145.39-.087.16.058 1.013.477 1.187.564.174.087.29.13.333.203.044.073.044.419-.1 1.824z"/>
            </svg>
            <span><?php echo esc_html($label); ?></span>
        </a>
        <?php
    }

    /**
     * Render Dynamic Theme CSS Variables in wp_head
     */
    public function render_storefront_dynamic_css() {
        if (is_admin()) return;

        $accent = get_option('kinboni_theme_accent_color', '#D4AF37');
        $font   = get_option('kinboni_theme_heading_font', 'Cinzel, Georgia, serif');

        ?>
        <style id="kinboni-dynamic-theme-css">
            :root {
                --kinboni-accent-gold: <?php echo esc_attr($accent); ?>;
                --kinboni-luxury-font: <?php echo esc_attr($font); ?>;
            }
        </style>
        <?php
    }

    /**
     * Low-Stock Urgency Badge on Single Product Pages
     */
    public function render_low_stock_urgency_badge() {
        if (get_option('kinboni_low_stock_text_enabled', 'yes') !== 'yes') {
            return;
        }

        global $product;
        if (!$product || !$product->is_in_stock()) {
            return;
        }

        $stock = $product->get_stock_quantity();
        $threshold = (int) get_option('kinboni_stock_threshold', 5);

        if ($stock !== null && $stock > 0 && $stock <= $threshold) {
            ?>
            <div class="kinboni-low-stock-alert" style="display:inline-flex; align-items:center; gap:6px; background:#FEF2F2; color:#DC2626; border:1px solid #FCA5A5; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:700; margin:10px 0;">
                <span class="dashicons dashicons-clock" style="font-size:16px; width:16px; height:16px;"></span>
                <span><?php printf(esc_html__('Urgent: Only %d piece(s) left in the atelier!', 'kinboni-core'), $stock); ?></span>
            </div>
            <?php
        }
    }
}

