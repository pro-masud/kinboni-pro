<?php
/**
 * Kinboni Activity Logger
 *
 * Records administrator actions, status changes, and configurations
 * into the activity log table for auditability and security.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Activity_Logger {

    /**
     * Record an administrative event
     *
     * @param string $action Short identifier (e.g. 'order_status_update', 'product_quick_edit')
     * @param string $details Human-readable description
     * @return bool
     */
    public static function log($action, $details) {
        global $wpdb;

        $table = $wpdb->prefix . 'kinboni_activity_logs';
        $user  = wp_get_current_user();
        $ip    = class_exists('Kinboni_Core') ? Kinboni_Core::get_client_ip() : (isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '0.0.0.0');

        // Never log sensitive data such as passwords or tokens
        $safe_details = sanitize_textarea_field($details);

        return (bool) $wpdb->insert(
            $table,
            array(
                'user_id'    => $user ? $user->ID : 0,
                'user_name'  => $user && $user->exists() ? $user->display_name : 'System',
                'action'     => sanitize_key($action),
                'details'    => $safe_details,
                'ip_address' => $ip,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Record an error event to the activity logs and PHP error log
     *
     * @param string $context Context or module identifier (e.g. 'checkout', 'email', 'cron')
     * @param string $message Error message or description
     * @param array  $data    Additional debug data
     * @return bool
     */
    public static function error($context, $message, $data = array()) {
        global $wpdb;

        $safe_context = sanitize_key($context);
        $safe_message = sanitize_textarea_field($message);
        $payload_str  = !empty($data) ? ' | ' . wp_json_encode($data) : '';
        $full_details = "[{$safe_context}] {$safe_message}{$payload_str}";

        // Write to PHP error log if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("KINBONI ERROR [{$safe_context}]: {$safe_message}" . (!empty($data) ? ' ' . wp_json_encode($data) : ''));
        }

        $table = $wpdb->prefix . 'kinboni_activity_logs';
        $user  = function_exists('wp_get_current_user') ? wp_get_current_user() : null;
        $ip    = class_exists('Kinboni_Core') ? Kinboni_Core::get_client_ip() : (isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '0.0.0.0');

        return (bool) $wpdb->insert(
            $table,
            array(
                'user_id'    => $user && $user->exists() ? $user->ID : 0,
                'user_name'  => $user && $user->exists() ? $user->display_name : 'System',
                'action'     => 'error',
                'details'    => $full_details,
                'ip_address' => $ip,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
    }
}

