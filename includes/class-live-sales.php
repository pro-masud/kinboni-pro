<?php
/**
 * Kinboni 15-Second Live Sales Social Proof & Chime Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Live_Sales {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('wp_ajax_kinboni_get_live_sales', array($this, 'get_live_sales'));
        add_action('wp_ajax_nopriv_kinboni_get_live_sales', array($this, 'get_live_sales'));
    }

    public function get_live_sales() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        if (get_option('kinboni_live_sales_enabled', 'yes') !== 'yes') {
            wp_send_json_success(array('sales' => array()));
        }

        $sales = array();

        // 1. Try to get recent real orders from WooCommerce
        if (class_exists('WooCommerce')) {
            $orders = wc_get_orders(array(
                'limit'   => 5,
                'status'  => array('wc-completed', 'wc-processing'),
                'orderby' => 'date',
                'order'   => 'DESC',
            ));

            if (!empty($orders)) {
                $show_real_names = get_option('kinboni_live_sales_real_names', 'no');

                foreach ($orders as $order) {
                    $items = $order->get_items();
                    foreach ($items as $item) {
                        $product = $item->get_product();
                        if ($product) {
                            $city = $order->get_billing_city() ?: 'Dhaka';
                            if ($show_real_names === 'yes') {
                                $first_name = $order->get_billing_first_name() ?: esc_html__('Someone', 'kinboni-core');
                                $customer_str = sprintf(esc_html__('%1$s from %2$s', 'kinboni-core'), $first_name, $city);
                            } else {
                                $customer_str = sprintf(esc_html__('Someone from %s', 'kinboni-core'), $city);
                            }

                            $date_created = $order->get_date_created();
                            $time_ago = $date_created ? sprintf(esc_html__('%s ago', 'kinboni-core'), human_time_diff($date_created->getTimestamp())) : esc_html__('A few moments ago', 'kinboni-core');

                            $sales[] = array(
                                'id'       => $product->get_id(),
                                'name'     => $product->get_name(),
                                'price'    => wc_price($product->get_price()),
                                'image'    => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src(),
                                'customer' => $customer_str,
                                'time'     => $time_ago,
                            );
                            break; // 1 product per order
                        }
                    }
                }
            }
        }

        // 2. Fallback to curated published products if no recent orders exist yet
        if (empty($sales) && class_exists('WooCommerce')) {
            $fallback_products = wc_get_products(array(
                'status'  => 'publish',
                'limit'   => 5,
                'orderby' => 'popularity',
                'order'   => 'DESC',
            ));
            $cities = array('Gulshan, Dhaka', 'Banani, Dhaka', 'Dhanmondi, Dhaka', 'Chattogram', 'Sylhet');
            $times  = array('2 mins ago', '7 mins ago', '14 mins ago', '22 mins ago', '35 mins ago');
            foreach ($fallback_products as $idx => $prod) {
                $sales[] = array(
                    'id'       => $prod->get_id(),
                    'name'     => $prod->get_name(),
                    'price'    => wc_price($prod->get_price()),
                    'image'    => wp_get_attachment_image_url($prod->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src(),
                    'customer' => sprintf(esc_html__('Someone from %s', 'kinboni-core'), $cities[$idx % count($cities)]),
                    'time'     => $times[$idx % count($times)],
                );
            }
        }

        wp_send_json_success(array('sales' => $sales));
    }
}

