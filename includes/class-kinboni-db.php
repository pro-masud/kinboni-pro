<?php
/**
 * Kinboni Database Installer & Migration Manager
 *
 * Handles creation and upgrades of custom database tables for
 * analytics, searches, newsletter subscribers, abandoned carts, and activity logs.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_DB {

    const DB_VERSION = '1.0.3';

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        if (did_action('plugins_loaded')) {
            $this->check_version();
        } else {
            add_action('plugins_loaded', array($this, 'check_version'), 5);
        }
    }

    /**
     * Check if database tables need installation, recovery, or upgrade
     *
     * @return bool True if tables verified and version updated, false if verification failed
     */
    public function check_version() {
        $installed_version = get_option('kinboni_db_version', '0.0.0');
        $tables_exist      = $this->verify_tables_exist();

        if (!$tables_exist || version_compare($installed_version, self::DB_VERSION, '<')) {
            $this->install_tables();

            // Verify that all required database tables are successfully created or updated after dbDelta()
            $missing = $this->get_missing_tables();
            if (!empty($missing)) {
                // Log migration failures and allow safe retries (do NOT update kinboni_db_version)
                if (class_exists('Kinboni_Activity_Logger')) {
                    Kinboni_Activity_Logger::error(
                        'db_migration_failed',
                        'Database table verification failed after dbDelta(). Missing tables: ' . implode(', ', $missing),
                        array(
                            'installed_version' => $installed_version,
                            'target_version'    => self::DB_VERSION,
                            'missing_tables'    => $missing,
                        )
                    );
                }
                return false;
            }

            // Tables verified successfully
            $this->run_migrations($installed_version, self::DB_VERSION);
            $this->set_default_options();

            // Update kinboni_db_version only after successful verification
            update_option('kinboni_db_version', self::DB_VERSION);
            return true;
        }

        return true;
    }

    /**
     * Get list of missing required custom tables
     *
     * @return array
     */
    public function get_missing_tables() {
        global $wpdb;
        $required_tables = array(
            $wpdb->prefix . 'kinboni_analytics',
            $wpdb->prefix . 'kinboni_searches',
            $wpdb->prefix . 'kinboni_subscribers',
            $wpdb->prefix . 'kinboni_abandoned_carts',
            $wpdb->prefix . 'kinboni_activity_logs',
        );

        $missing = array();
        foreach ($required_tables as $table) {
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) {
                $missing[] = $table;
            }
        }
        return $missing;
    }

    /**
     * Verify that all custom Kinboni database tables actually exist
     *
     * @return bool
     */
    public function verify_tables_exist() {
        return empty($this->get_missing_tables());
    }

    /**
     * Versioned migration runner
     *
     * @param string $from_version
     * @param string $to_version
     */
    public function run_migrations($from_version, $to_version) {
        if (version_compare($from_version, '1.0.1', '<')) {
            // 1.0.1: Ensure courier and notifications defaults
        }
        if (version_compare($from_version, '1.0.2', '<')) {
            // 1.0.2: Ensure options baseline
        }
        if (version_compare($from_version, '1.0.3', '<')) {
            // 1.0.3: Seed proxy trust, bKash merchant number, and VIP coupon settings
            if (get_option('kinboni_trust_proxy_headers') === false) {
                update_option('kinboni_trust_proxy_headers', 'no');
            }
            if (get_option('kinboni_bkash_merchant_number') === false) {
                update_option('kinboni_bkash_merchant_number', '');
            }
            if (get_option('kinboni_vip_coupon_code') === false) {
                update_option('kinboni_vip_coupon_code', 'LUXE20');
            }
        }
    }

    /**
     * Create or upgrade custom database tables using dbDelta
     */
    public function install_tables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        // 1. Analytics & Visitor Tracking
        $table_analytics = $wpdb->prefix . 'kinboni_analytics';
        $sql_analytics = "CREATE TABLE {$table_analytics} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            visitor_id varchar(64) NOT NULL DEFAULT '',
            page_type varchar(32) NOT NULL DEFAULT 'page',
            page_id bigint(20) unsigned NOT NULL DEFAULT 0,
            url varchar(255) NOT NULL DEFAULT '',
            referer varchar(255) NOT NULL DEFAULT '',
            device varchar(16) NOT NULL DEFAULT 'desktop',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY visitor_id (visitor_id),
            KEY created_at_page (created_at, page_type),
            KEY page_id (page_id)
        ) {$charset_collate};";
        dbDelta($sql_analytics);

        // 2. Search Queries Tracking
        $table_searches = $wpdb->prefix . 'kinboni_searches';
        $sql_searches = "CREATE TABLE {$table_searches} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            search_term varchar(128) NOT NULL DEFAULT '',
            results_count int(10) unsigned NOT NULL DEFAULT 0,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY search_term (search_term),
            KEY created_at (created_at)
        ) {$charset_collate};";
        dbDelta($sql_searches);

        // 3. Subscribers / VIP Newsletter
        $table_subscribers = $wpdb->prefix . 'kinboni_subscribers';
        $sql_subscribers = "CREATE TABLE {$table_subscribers} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(191) NOT NULL,
            name varchar(128) NOT NULL DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'subscribed',
            subscribed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY subscribed_at (subscribed_at)
        ) {$charset_collate};";
        dbDelta($sql_subscribers);

        // 4. Abandoned Carts
        $table_carts = $wpdb->prefix . 'kinboni_abandoned_carts';
        $sql_carts = "CREATE TABLE {$table_carts} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            session_id varchar(64) NOT NULL DEFAULT '',
            customer_email varchar(191) NOT NULL DEFAULT '',
            customer_phone varchar(32) NOT NULL DEFAULT '',
            customer_name varchar(128) NOT NULL DEFAULT '',
            cart_data longtext NOT NULL,
            cart_total decimal(12,2) NOT NULL DEFAULT 0.00,
            reminder_count int(10) unsigned NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'abandoned',
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY session_id (session_id),
            KEY customer_email (customer_email),
            KEY status (status),
            KEY updated_at (updated_at)
        ) {$charset_collate};";
        dbDelta($sql_carts);

        // 5. Activity Logs
        $table_activity = $wpdb->prefix . 'kinboni_activity_logs';
        $sql_activity = "CREATE TABLE {$table_activity} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            user_name varchar(128) NOT NULL DEFAULT '',
            action varchar(64) NOT NULL DEFAULT '',
            details text NOT NULL,
            ip_address varchar(45) NOT NULL DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY created_at (created_at)
        ) {$charset_collate};";
        dbDelta($sql_activity);
    }

    /**
     * Get default options mapping for the plugin
     *
     * @return array
     */
    public static function get_default_options() {
        return array(
            'kinboni_delivery_fee_inside'      => 70,
            'kinboni_delivery_fee_outside'     => 130,
            'kinboni_free_shipping_threshold'  => 2500,
            'kinboni_delivery_time_inside'     => '1 - 2 Business Days',
            'kinboni_delivery_time_outside'    => '2 - 4 Business Days',
            'kinboni_primary_courier'          => 'Steadfast Courier',
            'kinboni_courier_api_key'          => '',
            'kinboni_courier_secret_key'       => '',
            'kinboni_enable_geo_selector'      => 'yes',
            'kinboni_cod_inside_enabled'       => 'yes',
            'kinboni_cod_outside_enabled'      => 'yes',
            'kinboni_allow_inspection'         => 'yes',
            'kinboni_whatsapp_phone'           => '8801700000000',
            'kinboni_whatsapp_position'        => 'bottom-right',
            'kinboni_whatsapp_enabled'         => 'yes',
            'kinboni_whatsapp_label'           => '',
            'kinboni_whatsapp_color'           => '#25D366',
            'kinboni_live_sales_enabled'       => 'yes',
            'kinboni_live_sales_interval'      => 15,
            'kinboni_live_sales_sound'         => 'yes',
            'kinboni_live_sales_real_names'    => 'no',
            'kinboni_order_sound_enabled'      => 'yes',
            'kinboni_sound_tone'               => 'bell',
            'kinboni_order_email_enabled'      => 'yes',
            'kinboni_stock_email_enabled'      => 'yes',
            'kinboni_stock_threshold'          => 5,
            'kinboni_low_stock_enabled'        => 'yes',
            'kinboni_low_stock_text_enabled'   => 'yes',
            'kinboni_notification_recipients'  => get_option('admin_email'),
            'kinboni_admin_badge_enabled'      => 'yes',
            'kinboni_theme_accent_color'       => '#D4AF37',
            'kinboni_theme_mode_default'       => 'system',
            'kinboni_theme_heading_font'       => 'Cinzel',
            'kinboni_cart_recommendations'     => 'yes',
            'kinboni_recently_viewed_enabled'  => 'yes',
            'kinboni_quickview_sizechart'      => 'yes',
            'kinboni_quickview_delivery_info'  => 'yes',
            'kinboni_default_cogs_pct'         => 60,
            'kinboni_automated_weekly_report'  => 'yes',
            'kinboni_automated_monthly_report' => 'no',
            'kinboni_report_recipients'        => get_option('admin_email'),
            'kinboni_abandoned_threshold_min'  => 30,
            'kinboni_abandoned_email_subject'  => '',
            'kinboni_abandoned_email_coupon'   => '',
            'kinboni_popup_banner_enabled'     => 'no',
            'kinboni_popup_title'              => '',
            'kinboni_popup_subtitle'           => '',
            'kinboni_popup_image_url'          => '',
            'kinboni_popup_btn_text'           => '',
            'kinboni_popup_btn_url'            => '',
            'kinboni_popup_delay_sec'          => 5,
            'kinboni_popup_start_date'         => '',
            'kinboni_popup_end_date'           => '',
            'kinboni_fb_pixel_enabled'         => 'no',
            'kinboni_fb_pixel_id'              => '',
            'kinboni_ga4_enabled'              => 'no',
            'kinboni_ga4_measurement_id'       => '',
            'kinboni_trust_proxy_headers'      => 'no',
            'kinboni_bkash_merchant_number'    => '',
            'kinboni_vip_coupon_code'          => 'LUXE20',
            'kinboni_delete_data_on_uninstall' => 'no',
            'kinboni_content_brand_name'       => 'KINBONI',
            'kinboni_content_brand_tagline'    => 'Artisanal Luxury & Master Craftsmanship',
            'kinboni_content_concierge_email'  => 'concierge@kinboni.com',
            'kinboni_content_concierge_address'=> 'Banani, Road 11, Dhaka, Bangladesh',
        );
    }

    /**
     * Seed default options if not already configured
     */
    public function set_default_options() {
        $defaults = self::get_default_options();

        foreach ($defaults as $key => $val) {
            if (get_option($key) === false) {
                update_option($key, $val);
            }
        }
    }
}
