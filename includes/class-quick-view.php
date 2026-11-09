<?php
/**
 * Kinboni Quick View Modal Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Quick_View {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('wp_ajax_kinboni_get_quick_view', array($this, 'get_quick_view_product'));
        add_action('wp_ajax_nopriv_kinboni_get_quick_view', array($this, 'get_quick_view_product'));
    }

    public function get_quick_view_product() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        if (!function_exists('wc_get_product')) {
            wp_send_json_error(array('message' => esc_html__('WooCommerce is required for Quick View.', 'kinboni-core')));
        }
        $product    = wc_get_product($product_id);

        if (!$product || $product->get_status() !== 'publish' || !$product->is_visible()) {
            wp_send_json_error(array('message' => esc_html__('Product not found.', 'kinboni-core')));
        }

        $image_id = $product->get_image_id();
        $image    = wp_get_attachment_image_url($image_id, 'kinboni-product-gallery');
        if (!$image) {
            $image = wc_placeholder_img_src('kinboni-product-gallery');
        }

        $gallery_ids = $product->get_gallery_image_ids();
        $thumbs      = array();
        $thumbs[]    = $image;
        foreach ($gallery_ids as $g_id) {
            $thumbs[] = wp_get_attachment_image_url($g_id, 'kinboni-product-gallery');
        }

        $terms = get_the_terms($product_id, 'product_cat');
        $cat   = ($terms && !is_wp_error($terms)) ? $terms[0]->name : esc_html__('Atelier Collection', 'kinboni-core');

        wp_send_json_success(array(
            'id'          => $product_id,
            'name'        => $product->get_name(),
            'price_html'  => $product->get_price_html(),
            'price_raw'   => $product->get_price(),
            'image'       => $image,
            'thumbs'      => array_slice($thumbs, 0, 4),
            'category'    => $cat,
            'rating'      => $product->get_average_rating(),
            'reviews'     => $product->get_review_count(),
            'in_stock'    => $product->is_in_stock(),
            'short_desc'  => wp_trim_words($product->get_short_description() ?: $product->get_description(), 25),
            'add_to_cart_url' => $product->add_to_cart_url(),
            'show_sizechart'     => get_option('kinboni_quickview_sizechart', 'yes') === 'yes',
            'show_delivery_info' => get_option('kinboni_quickview_delivery_info', 'yes') === 'yes',
            'delivery_time'      => get_option('kinboni_delivery_time_inside', '1 - 2 Business Days'),
        ));
    }
}

