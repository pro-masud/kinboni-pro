<?php
/**
 * Kinboni Lightweight Visitor & Front-end Event Tracker
 *
 * Silently records authentic page views, device breakdowns, referrers,
 * search queries, and newsletter subscriptions into custom database tables.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Tracker {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Track page visits on template_redirect
        add_action('template_redirect', array($this, 'track_visit'));

        // Handle AJAX Newsletter Subscription
        add_action('wp_ajax_kinboni_subscribe_newsletter', array($this, 'ajax_subscribe_newsletter'));
        add_action('wp_ajax_nopriv_kinboni_subscribe_newsletter', array($this, 'ajax_subscribe_newsletter'));
    }

    /**
     * Record clean front-end visits
     */
    public function track_visit() {
        // Skip admin, feeds, robots, or background requests
        if (is_admin() || is_feed() || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        // Skip logged-in administrators to avoid polluting analytics
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }

        // Identify bot user agents
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
        if (empty($ua) || preg_match('/(bot|crawl|spider|slurp|facebookexternalhit|bingpreview|headless)/i', $ua)) {
            return;
        }

        global $wpdb;
        $table_analytics = $wpdb->prefix . 'kinboni_analytics';

        // Anonymize IP address for privacy
        $ip = class_exists('Kinboni_Core') ? Kinboni_Core::get_client_ip() : (isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '127.0.0.1');
        $salt = defined('NONCE_SALT') ? NONCE_SALT : 'kinboni_luxury_salt';
        $visitor_id = hash_hmac('sha256', $ip . '|' . $ua . '|' . gmdate('Y-m-d'), $salt);

        // Determine Page Type & ID
        $page_type = 'page';
        $page_id = 0;

        if (is_front_page() || is_home()) {
            $page_type = 'home';
        } elseif (function_exists('is_product') && is_product()) {
            $page_type = 'product';
            $page_id = get_the_ID();
        } elseif (function_exists('is_shop') && is_shop()) {
            $page_type = 'shop';
        } elseif (function_exists('is_cart') && is_cart()) {
            $page_type = 'cart';
        } elseif (function_exists('is_checkout') && is_checkout()) {
            $page_type = 'checkout';
        } elseif (is_search()) {
            $page_type = 'search';
            $this->log_search_query(get_search_query());
        }

        // Device Detection
        $device = 'desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $device = 'tablet';
        } elseif (preg_match('/(mobile|ipod|iphone|android|blackberry|iemobile)/i', $ua)) {
            $device = 'mobile';
        }

        // Referrer
        $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
        $referer_clean = '';
        if (!empty($referer)) {
            $ref_host = parse_url($referer, PHP_URL_HOST);
            $curr_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
            if ($ref_host && stripos($ref_host, $curr_host) === false) {
                $referer_clean = substr($ref_host, 0, 250);
            }
        }

        $current_url = substr(home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? '')), 0, 250);

        // Session Deduplication: Only 1 log per visitor per page per hour
        $dedupe_cutoff = date('Y-m-d H:i:s', current_time('timestamp') - 3600);
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_analytics} 
             WHERE visitor_id = %s AND page_type = %s AND page_id = %d 
             AND created_at >= %s LIMIT 1",
            $visitor_id,
            $page_type,
            $page_id,
            $dedupe_cutoff
        ));

        if (!$existing) {
            $inserted = $wpdb->insert(
                $table_analytics,
                array(
                    'visitor_id' => $visitor_id,
                    'page_type'  => $page_type,
                    'page_id'    => $page_id,
                    'url'        => $current_url,
                    'referer'    => $referer_clean,
                    'device'     => $device,
                    'created_at' => current_time('mysql'),
                ),
                array('%s', '%s', '%d', '%s', '%s', '%s', '%s')
            );

            // Increment single product view count postmeta ONLY on authentic new view insertion
            if ($inserted && $page_type === 'product' && $page_id > 0) {
                $current_views = (int) get_post_meta($page_id, '_kinboni_views_count', true);
                update_post_meta($page_id, '_kinboni_views_count', $current_views + 1);
            }
        }
    }

    /**
     * Record Search Queries with 15-Minute Deduplication Guard
     *
     * @param string $term
     * @param int $count
     */
    public function log_search_query($term, $count = null) {
        $clean_term = sanitize_text_field(trim($term));
        $term_len   = function_exists('mb_strlen') ? mb_strlen($clean_term, 'UTF-8') : strlen($clean_term);
        if (empty($clean_term) || $term_len < 2) {
            return;
        }

        global $wpdb;
        $table_searches = $wpdb->prefix . 'kinboni_searches';

        // 15-minute search deduplication
        $cutoff_15m = date('Y-m-d H:i:s', current_time('timestamp') - 900);
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_searches} WHERE search_term = %s AND created_at >= %s LIMIT 1",
            $clean_term,
            $cutoff_15m
        ));

        if ($existing) {
            return;
        }

        if ($count === null) {
            global $wp_query;
            $count = isset($wp_query->found_posts) ? (int)$wp_query->found_posts : 0;
        }

        $wpdb->insert(
            $table_searches,
            array(
                'search_term'   => function_exists('mb_substr') ? mb_substr($clean_term, 0, 128, 'UTF-8') : substr($clean_term, 0, 128),
                'results_count' => (int) $count,
                'user_id'       => get_current_user_id(),
                'created_at'    => current_time('mysql'),
            ),
            array('%s', '%d', '%d', '%s')
        );
    }

    /**
     * AJAX Subscriber Form Submission
     */
    public function ajax_subscribe_newsletter() {
        check_ajax_referer('kinboni_core_nonce', 'nonce');

        // Honeypot check
        if (!empty($_POST['kinboni_hp'])) {
            wp_send_json_success(array(
                'message' => esc_html__('Welcome to The Kinboni Circle!', 'kinboni-core')
            ));
        }

        // Rate limiting: max 5 attempts per hour per IP
        $client_ip  = class_exists('Kinboni_Core') ? Kinboni_Core::get_client_ip() : (isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0.0.0.0');
        $rate_key   = 'kb_sub_' . md5($client_ip);
        $attempts   = (int) get_transient($rate_key);
        if ($attempts >= 5) {
            wp_send_json_error(array('message' => esc_html__('Too many requests. Please try again later.', 'kinboni-core')));
        }
        set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $name  = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => esc_html__('Please provide a valid email address.', 'kinboni-core')));
        }

        $vip_code = class_exists('Kinboni_Core') ? Kinboni_Core::ensure_vip_coupon() : 'LUXE20';
        if (!empty($vip_code)) {
            /* translators: %s: coupon code */
            $success_message = sprintf(esc_html__('Welcome to The Kinboni Circle! Your VIP code is %s.', 'kinboni-core'), $vip_code);
        } else {
            $success_message = esc_html__('Welcome to The Kinboni Circle!', 'kinboni-core');
        }

        global $wpdb;
        $table_subscribers = $wpdb->prefix . 'kinboni_subscribers';

        $existing = $wpdb->get_row($wpdb->prepare("SELECT id, status FROM {$table_subscribers} WHERE email = %s", $email));

        if ($existing) {
            if ($existing->status === 'unsubscribed') {
                $wpdb->update(
                    $table_subscribers,
                    array('status' => 'subscribed', 'subscribed_at' => current_time('mysql'), 'unsubscribed_at' => null),
                    array('id' => $existing->id),
                    array('%s', '%s', null),
                    array('%d')
                );
            }
            // Generic success message to prevent account enumeration
            wp_send_json_success(array('message' => $success_message));
        }

        $inserted = $wpdb->insert(
            $table_subscribers,
            array(
                'email'         => $email,
                'name'          => $name,
                'status'        => 'subscribed',
                'subscribed_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s')
        );

        if ($inserted) {
            wp_send_json_success(array('message' => $success_message));
        } else {
            wp_send_json_error(array('message' => esc_html__('Subscription failed. Please try again later.', 'kinboni-core')));
        }
    }
}

