<?php
/**
 * Plugin Name: Kinboni Core & Elementor Addons
 * Plugin URI: https://kinboni.com
 * Description: Core functionality and Elementor widgets suite for KINBONI Atelier luxury storefront. Includes 3-Field Fast Cash on Delivery checkout, Slide-over Mini-cart, 15s Live Sales Social Proof, Quick View, and Wishlist.
 * Version: 1.0.3
 * Author: Kinboni Dev Team
 * Author URI: https://kinboni.com
 * Text Domain: kinboni-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.3
 * Requires Plugins: woocommerce, elementor
 * Elementor tested up to: 3.25
 * Elementor Pro tested up to: 3.25
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define Plugin Constants
define('KINBONI_CORE_VERSION', '1.0.3');
define('KINBONI_CORE_FILE', __FILE__);
define('KINBONI_CORE_PATH', plugin_dir_path(__FILE__));
define('KINBONI_CORE_URL', plugin_dir_url(__FILE__));

/**
 * Register custom monthly cron schedule for automated P&L reports
 */
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['monthly'])) {
        $schedules['monthly'] = array(
            'interval' => 30 * DAY_IN_SECONDS,
            'display'  => esc_html__('Once Monthly', 'kinboni-core'),
        );
    }
    return $schedules;
});

/**
 * Declare WooCommerce High-Performance Order Storage (HPOS) Compatibility
 */
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Plugin Activation Handler
 */
function kinboni_core_activate() {
    // 1. Install or upgrade custom tables and seed options
    if (!class_exists('Kinboni_DB')) {
        require_once KINBONI_CORE_PATH . 'includes/class-kinboni-db.php';
    }
    Kinboni_DB::instance()->check_version();

    // 2. Schedule automated background crons
    if (!wp_next_scheduled('kinboni_cron_abandoned_cart')) {
        wp_schedule_event(time(), 'hourly', 'kinboni_cron_abandoned_cart');
    }
    if (!wp_next_scheduled('kinboni_cron_cleanup')) {
        wp_schedule_event(time() + 3600, 'daily', 'kinboni_cron_cleanup');
    }
    if (!wp_next_scheduled('kinboni_cron_weekly_report')) {
        wp_schedule_event(time() + 86400, 'weekly', 'kinboni_cron_weekly_report');
    }
    if (!wp_next_scheduled('kinboni_cron_monthly_report')) {
        wp_schedule_event(time() + (2 * DAY_IN_SECONDS), 'monthly', 'kinboni_cron_monthly_report');
    }

    // 3. Synchronize administrator capabilities
    if (!class_exists('Kinboni_Admin')) {
        require_once KINBONI_CORE_PATH . 'admin/class-kinboni-admin.php';
    }
    Kinboni_Admin::sync_admin_capabilities();
}
register_activation_hook(__FILE__, 'kinboni_core_activate');

/**
 * Plugin Deactivation Handler
 */
function kinboni_core_deactivate() {
    wp_clear_scheduled_hook('kinboni_cron_abandoned_cart');
    wp_clear_scheduled_hook('kinboni_cron_cleanup');
    wp_clear_scheduled_hook('kinboni_cron_weekly_report');
    wp_clear_scheduled_hook('kinboni_cron_monthly_report');
}
register_deactivation_hook(__FILE__, 'kinboni_core_deactivate');

/**
 * Load Main Plugin Class
 */
require_once KINBONI_CORE_PATH . 'includes/class-kinboni-core.php';

/**
 * Initialize Kinboni Core
 */
function kinboni_core() {
    return Kinboni_Core::instance();
}

// Run the plugin
kinboni_core();

