<?php
/**
 * Kinboni Wishlist Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Wishlist {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('wp_ajax_kinboni_get_wishlist_items', array($this, 'get_wishlist_items'));
        add_action('wp_ajax_nopriv_kinboni_get_wishlist_items', array($this, 'get_wishlist_items'));
        add_action('wp_ajax_kinboni_move_all_wishlist_to_bag', array($this, 'move_all_to_bag'));
        add_action('wp_ajax_nopriv_kinboni_move_all_wishlist_to_bag', array($this, 'move_all_to_bag'));
        add_action('wp_ajax_kinboni_track_wishlist', array($this, 'track_wishlist'));
        add_action('wp_ajax_nopriv_kinboni_track_wishlist', array($this, 'track_wishlist'));
    }

    public function track_wishlist() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');
        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $action     = isset($_POST['wishlist_action']) ? sanitize_key(wp_unslash($_POST['wishlist_action'])) : 'add';

        if ($product_id <= 0) {
            wp_send_json_error(array('message' => esc_html__('Invalid product specified.', 'kinboni-core')));
        }

        // Validate product_id is a published 'product'
        if (get_post_type($product_id) !== 'product' || get_post_status($product_id) !== 'publish') {
            wp_send_json_error(array('message' => esc_html__('Product not found or not published.', 'kinboni-core')));
        }

        $user_id      = get_current_user_id();
        $visitor_hash = md5(Kinboni_Core::get_client_ip() . (isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : ''));

        // Per-visitor rate limit (max 30 actions / min)
        $rate_key = 'kb_wl_rate_' . ($user_id ? $user_id : $visitor_hash);
        $attempts = (int) get_transient($rate_key);
        if ($attempts >= 30) {
            wp_send_json_error(array('message' => esc_html__('Too many requests. Please slow down.', 'kinboni-core')));
        }
        set_transient($rate_key, $attempts + 1, MINUTE_IN_SECONDS);

        // Retrieve visitor list
        $transient_key = 'kb_wl_' . $visitor_hash;
        if ($user_id) {
            $raw_list = get_user_meta($user_id, '_kinboni_wishlist_ids', true);
            $list     = is_array($raw_list) ? $raw_list : array();
        } else {
            $raw_list = get_transient($transient_key);
            $list     = is_array($raw_list) ? $raw_list : array();
        }

        $count = (int) get_post_meta($product_id, '_kinboni_wishlist_count', true);
        $count = max(0, $count);

        if ($action === 'add') {
            if (!in_array($product_id, $list, true)) {
                $list[] = $product_id;
                $count++;
            }
        } elseif ($action === 'remove') {
            $key = array_search($product_id, $list, true);
            if ($key !== false) {
                unset($list[$key]);
                $list = array_values($list);
                $count = max(0, $count - 1);
            }
        }

        // Save visitor list
        if ($user_id) {
            update_user_meta($user_id, '_kinboni_wishlist_ids', $list);
        } else {
            set_transient($transient_key, $list, 30 * DAY_IN_SECONDS);
        }

        update_post_meta($product_id, '_kinboni_wishlist_count', $count);
        wp_send_json_success(array('count' => $count));
    }

    public function get_wishlist_items() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        $ids = isset($_POST['product_ids']) ? array_slice(array_filter(array_map('absint', (array) wp_unslash($_POST['product_ids']))), 0, 50) : array();
        $items = array();

        if (!empty($ids) && class_exists('WooCommerce')) {
            foreach ($ids as $id) {
                $p = wc_get_product($id);
                if ($p && $p->is_visible()) {
                    $main_img_id = $p->get_image_id();
                    $main_img    = $main_img_id ? wp_get_attachment_image_url($main_img_id, 'large') : wc_placeholder_img_src('large');
                    $gallery_ids = $p->get_gallery_image_ids();
                    $hover_img   = !empty($gallery_ids) ? wp_get_attachment_image_url($gallery_ids[0], 'large') : $main_img;

                    $terms = get_the_terms($id, 'product_cat');
                    $cat_label = ($terms && !is_wp_error($terms)) ? $terms[0]->name : esc_html__('Atelier Collection', 'kinboni-core');

                    $price     = (float) $p->get_price();
                    $reg_price = (float) $p->get_regular_price();
                    $is_sale   = $p->is_on_sale();
                    $discount_pct = ($is_sale && $reg_price > $price) ? round((($reg_price - $price) / $reg_price) * 100) : 0;

                    $items[] = array(
                        'id'              => $id,
                        'name'            => $p->get_name(),
                        'price_html'      => $p->get_price_html(),
                        'price'           => $price,
                        'original_price'  => ($is_sale && $reg_price > $price) ? $reg_price : 0,
                        'discount_pct'    => $discount_pct,
                        'image'           => $main_img,
                        'hover_image'     => $hover_img,
                        'category'        => $cat_label,
                        'rating'          => (float) $p->get_average_rating(),
                        'reviews_count'   => (int) $p->get_review_count(),
                        'in_stock'        => $p->is_in_stock(),
                        'link'            => $p->get_permalink(),
                        'cart_url'        => $p->add_to_cart_url(),
                    );
                }
            }
        }

        wp_send_json_success(array('items' => $items));
    }

    public function move_all_to_bag() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        $ids = isset($_POST['product_ids']) ? array_slice(array_filter(array_map('absint', (array) wp_unslash($_POST['product_ids']))), 0, 50) : array();
        $added = 0;

        if (!empty($ids) && function_exists('WC')) {
            foreach ($ids as $id) {
                $p = wc_get_product($id);
                if ($p && $p->is_purchasable() && $p->is_in_stock()) {
                    WC()->cart->add_to_cart($id, 1);
                    $added++;
                }
            }
        }

        wp_send_json_success(array(
            'added_count' => $added,
            'cart_count'  => function_exists('WC') ? WC()->cart->get_cart_contents_count() : 0,
        ));
    }
}
