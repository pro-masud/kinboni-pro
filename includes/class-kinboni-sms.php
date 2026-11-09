<?php
/**
 * Kinboni SMS Gateway Engine (Greenweb, BulkSMSBD & Custom API)
 *
 * Provides automated SMS order confirmations, OTP verification,
 * and administrative customer broadcast capability.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_SMS {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Trigger automated SMS when order is created or reaches processing
        add_action('kinboni_order_created', array($this, 'on_order_created'), 15);
        add_action('woocommerce_order_status_processing', array($this, 'on_order_status_processing'), 15, 2);

        // AJAX: Send Test SMS
        add_action('wp_ajax_kinboni_send_test_sms', array($this, 'ajax_send_test_sms'));
    }

    /**
     * Check if SMS Gateway is enabled
     *
     * @return bool
     */
    public static function is_enabled() {
        return get_option('kinboni_sms_enabled', 'no') === 'yes';
    }

    /**
     * Send an SMS to a Bangladeshi 11-digit mobile number
     *
     * @param string $phone
     * @param string $message
     * @return array array('success' => bool, 'message' => string)
     */
    public function send($phone, $message) {
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean_phone) === 13 && substr($clean_phone, 0, 2) === '88') {
            $clean_phone = substr($clean_phone, 2);
        }

        if (!preg_match('/^01[3-9]\d{8}$/', $clean_phone)) {
            return array('success' => false, 'message' => esc_html__('Invalid Bangladeshi mobile number format.', 'kinboni-core'));
        }

        $provider  = get_option('kinboni_sms_provider', 'greenweb');
        $api_key   = trim(get_option('kinboni_sms_api_key', ''));
        $sender_id = trim(get_option('kinboni_sms_sender_id', ''));

        if (empty($api_key)) {
            return array('success' => false, 'message' => esc_html__('SMS API Key is not configured.', 'kinboni-core'));
        }

        $formatted_number = '88' . $clean_phone;
        $response_success = false;
        $response_msg = '';

        if ($provider === 'greenweb') {
            // Greenweb API (https://api.greenweb.com.bd/api.php)
            $url = 'https://api.greenweb.com.bd/api.php';
            $params = array(
                'token'   => $api_key,
                'to'      => $formatted_number,
                'message' => $message,
            );
            $res = wp_remote_post($url, array(
                'body'    => $params,
                'timeout' => 15,
            ));
            if (!is_wp_error($res)) {
                $body = wp_remote_retrieve_body($res);
                if (stripos($body, 'Ok') !== false || stripos($body, 'success') !== false || stripos($body, '100') !== false) {
                    $response_success = true;
                    $response_msg = 'Dispatched via Greenweb: ' . substr($body, 0, 100);
                } else {
                    $response_msg = 'Greenweb Error: ' . substr($body, 0, 150);
                }
            } else {
                $response_msg = 'Connection error: ' . $res->get_error_message();
            }

        } elseif ($provider === 'bulksmsbd') {
            // BulkSMSBD API (http://bulksmsbd.net/api/smsapi)
            $url = 'http://bulksmsbd.net/api/smsapi';
            $params = array(
                'api_key'  => $api_key,
                'type'     => 'text',
                'number'   => $formatted_number,
                'senderid' => !empty($sender_id) ? $sender_id : '8809617612345',
                'message'  => $message,
            );
            $res = wp_remote_post($url, array(
                'body'    => $params,
                'timeout' => 15,
            ));
            if (!is_wp_error($res)) {
                $body = wp_remote_retrieve_body($res);
                $json = json_decode($body, true);
                if (isset($json['response_code']) && $json['response_code'] == 202) {
                    $response_success = true;
                    $response_msg = 'Dispatched via BulkSMSBD';
                } else {
                    $response_msg = 'BulkSMSBD Error: ' . ($json['error_message'] ?? $body);
                }
            } else {
                $response_msg = 'Connection error: ' . $res->get_error_message();
            }

        } else {
            // Generic Custom HTTP Webhook
            $url = trim(get_option('kinboni_sms_custom_url', ''));
            if (!empty($url)) {
                $res = wp_remote_post($url, array(
                    'headers' => array('Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $api_key),
                    'body'    => wp_json_encode(array(
                        'recipient' => $formatted_number,
                        'message'   => $message,
                        'sender_id' => $sender_id,
                    )),
                    'timeout' => 15,
                ));
                $response_success = !is_wp_error($res) && wp_remote_retrieve_response_code($res) < 300;
                $response_msg = $response_success ? 'Dispatched via Custom Webhook' : 'Custom Webhook error';
            }
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log(
                $response_success ? 'sms_dispatched' : 'sms_failed',
                sprintf('SMS to %s: %s (%s)', $clean_phone, ($response_success ? 'SUCCESS' : 'FAILED'), $response_msg)
            );
        }

        return array('success' => $response_success, 'message' => $response_msg);
    }

    /**
     * Parse template tags for order confirmation
     */
    public function format_order_sms($order, $template = '') {
        if (empty($template)) {
            $template = get_option('kinboni_sms_order_template', 'Dear {customer_name}, your order #{order_id} of ৳{order_total} is confirmed. Track & concierge: {site_url} - {site_name}');
        }

        $customer_name = $order->get_billing_first_name() ?: 'Patron';
        $order_id      = (string) $order->get_id();
        $order_total   = (string) $order->get_total();
        $site_name     = get_bloginfo('name');
        $site_url      = site_url();

        $replacements = array(
            '{customer_name}' => $customer_name,
            '{order_id}'      => $order_id,
            '{order_total}'   => $order_total,
            '{site_name}'     => $site_name,
            '{site_url}'      => $site_url,
        );

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Automated Order Confirmation SMS
     */
    public function on_order_created($order) {
        $this->process_order_sms($order);
    }

    public function on_order_status_processing($order_id, $order = null) {
        if (!$order) {
            $order = wc_get_order($order_id);
        }
        if ($order) {
            $this->process_order_sms($order);
        }
    }

    private function process_order_sms($order) {
        if (!self::is_enabled()) {
            return;
        }

        // Deduplication guard
        if ($order->get_meta('_kinboni_sms_sent') === 'yes') {
            return;
        }

        $phone = $order->get_billing_phone();
        if (empty($phone)) {
            return;
        }

        $message = $this->format_order_sms($order);
        $result = $this->send($phone, $message);

        if ($result['success']) {
            $order->update_meta_data('_kinboni_sms_sent', 'yes');
            $order->add_order_note(sprintf('Confirmation SMS dispatched to %s', $phone));
            $order->save();
        }
    }

    /**
     * AJAX: Send Test SMS
     */
    public function ajax_send_test_sms() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        if (empty($phone)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a test mobile number.', 'kinboni-core')));
        }

        $msg = sprintf(
            'Test communication from KINBONI Pro Atelier at %s. SMS Gateway integration operational.',
            current_time('H:i:s')
        );

        $result = $this->send($phone, $msg);

        if ($result['success']) {
            wp_send_json_success(array('message' => sprintf(esc_html__('Test SMS sent successfully to %s!', 'kinboni-core'), $phone)));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
}
