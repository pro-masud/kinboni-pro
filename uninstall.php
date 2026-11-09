<?php
/**
 * Kinboni Core Uninstall
 *
 * Triggered when the plugin is uninstalled via WordPress admin.
 *
 * @package Kinboni_Core
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// 0. Clear all scheduled background cron hooks on uninstall
wp_clear_scheduled_hook('kinboni_cron_abandoned_cart');
wp_clear_scheduled_hook('kinboni_cron_cleanup');
wp_clear_scheduled_hook('kinboni_cron_weekly_report');
wp_clear_scheduled_hook('kinboni_cron_monthly_report');
wp_clear_scheduled_hook('kinboni_email_retry');

// 1. Read delete data flag FIRST, before any delete
$delete_data = get_option('kinboni_delete_data_on_uninstall', 'no');

// 2. Delete plugin transients using prepare and esc_like
$like_kb             = $wpdb->esc_like('_transient_kb_') . '%';
$like_timeout_kb     = $wpdb->esc_like('_transient_timeout_kb_') . '%';
$like_kinboni         = $wpdb->esc_like('_transient_kinboni_') . '%';
$like_timeout_kinboni = $wpdb->esc_like('_transient_timeout_kinboni_') . '%';

$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
        $like_kb,
        $like_timeout_kb,
        $like_kinboni,
        $like_timeout_kinboni
    )
);

// If not requested to delete data, stop here and keep settings and tables
if ($delete_data !== 'yes') {
    return;
}

// 3. Delete all options starting with 'kinboni_' (esc_like so underscore is literal)
$like_options = $wpdb->esc_like('kinboni_') . '%';
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $like_options
    )
);

// 4. Drop the 5 custom tables
$tables = array(
    $wpdb->prefix . 'kinboni_analytics',
    $wpdb->prefix . 'kinboni_searches',
    $wpdb->prefix . 'kinboni_subscribers',
    $wpdb->prefix . 'kinboni_abandoned_carts',
    $wpdb->prefix . 'kinboni_activity_logs',
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// 5. Delete user meta 'kinboni_last_seen_order_id'
delete_metadata('user', 0, 'kinboni_last_seen_order_id', '', true);

// 6. Remove capabilities from every role
if (function_exists('wp_roles')) {
    $roles = wp_roles()->role_objects;
    $caps  = array(
        'manage_kinboni_orders',
        'manage_kinboni_products',
        'manage_kinboni_marketing',
        'manage_kinboni_analytics',
    );
    if (!empty($roles) && is_array($roles)) {
        foreach ($roles as $role) {
            foreach ($caps as $cap) {
                $role->remove_cap($cap);
            }
        }
    }
}
