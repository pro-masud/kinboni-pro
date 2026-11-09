<?php
/**
 * Kinboni Admin Module: System, Security & Operations Engine
 *
 * Implements:
 * - Comprehensive System Information & Environment Diagnostics
 * - JSON Backup & Restore with Schema Validation
 * - Administrator Activity Auditor (from wp_kinboni_activity_logs)
 * - Staff Capabilities & Submenu Permission Matrix
 * - Database Maintenance & Transients Optimization
 * - WordPress Error Log Viewer with Severity Filtering
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_System {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_optimize_database', array($this, 'ajax_optimize_database'));
        add_action('wp_ajax_kinboni_clear_transients', array($this, 'ajax_clear_transients'));
        add_action('wp_ajax_kinboni_clear_activity_logs', array($this, 'ajax_clear_activity_logs'));
        add_action('wp_ajax_kinboni_clear_error_logs', array($this, 'ajax_clear_error_logs'));
        add_action('wp_ajax_kinboni_save_staff_permissions', array($this, 'ajax_save_staff_permissions'));
        add_action('wp_ajax_kinboni_save_system_settings', array($this, 'ajax_save_system_settings'));
        add_action('wp_ajax_kinboni_import_system_backup', array($this, 'ajax_import_system_backup'));
        add_action('wp_ajax_kinboni_restore_default_settings', array($this, 'ajax_restore_default_settings'));

        // Maintenance Cron
        add_action('kinboni_cron_cleanup', array($this, 'cron_cleanup_tables'));

        // Exports
        add_action('admin_init', array($this, 'handle_exports'));
    }

    /**
     * Render the System Admin Page
     */
    public function render() {
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'info';
        $allowed = array('info', 'backup', 'activity', 'permissions', 'database', 'errors');
        if (!in_array($active_tab, $allowed, true)) {
            $active_tab = 'info';
        }

        $system_info    = ($active_tab === 'info') ? $this->get_system_info() : array();
        $activity_data  = ($active_tab === 'activity') ? $this->query_activity_logs() : array();
        $db_status       = ($active_tab === 'database') ? $this->get_database_table_status() : array();
        $permissions     = ($active_tab === 'permissions') ? $this->get_staff_permissions() : array();
        $error_logs      = ($active_tab === 'errors') ? $this->get_error_logs() : array();
        $system_settings = $this->get_system_settings();

        $view_file = KINBONI_CORE_PATH . 'admin/views/view-system.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('System view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    // =========================================================================
    // 1. SYSTEM INFORMATION
    // =========================================================================

    public function get_system_info() {
        global $wpdb;

        return array(
            'wordpress_version'   => get_bloginfo('version'),
            'woocommerce_version' => defined('WC_VERSION') ? WC_VERSION : 'N/A',
            'plugin_version'      => defined('KINBONI_CORE_VERSION') ? KINBONI_CORE_VERSION : '1.0.0',
            'php_version'         => phpversion(),
            'server_software'     => $_SERVER['SERVER_SOFTWARE'] ?? 'Nginx',
            'mysql_version'       => $wpdb->db_version(),
            'memory_limit'        => ini_get('memory_limit'),
            'wp_memory_limit'     => defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : '40M',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size'       => ini_get('post_max_size'),
            'max_execution_time'  => ini_get('max_execution_time') . 's',
            'hpos_enabled'        => class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? 'Yes (HPOS Active)' : 'Legacy Posts',
            'curl_version'        => function_exists('curl_version') ? curl_version()['version'] : 'Disabled',
            'site_url'            => site_url(),
            'home_url'            => home_url(),
        );
    }

    // =========================================================================
    // 2. ACTIVITY LOGS AUDITOR
    // =========================================================================

    public function query_activity_logs() {
        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_activity_logs';

        $paged    = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $per_page = 25;
        $offset   = ($paged - 1) * $per_page;

        $action_filter = isset($_GET['act']) ? sanitize_text_field(wp_unslash($_GET['act'])) : '';
        $search_term   = isset($_GET['s']) ? sanitize_text_field(trim(wp_unslash($_GET['s']))) : '';

        $where = array('1=1');
        $params = array();

        if (!empty($action_filter)) {
            $where[] = 'action = %s';
            $params[] = $action_filter;
        }

        if (!empty($search_term)) {
            $where[] = '(user_name LIKE %s OR details LIKE %s OR ip_address LIKE %s)';
            $like = '%' . $wpdb->esc_like($search_term) . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $where_sql = implode(' AND ', $where);

        $total_sql = "SELECT COUNT(id) FROM {$table} WHERE {$where_sql}";
        $total = !empty($params) ? $wpdb->get_var($wpdb->prepare($total_sql, $params)) : $wpdb->get_var($total_sql);

        $query = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $data_params = array_merge($params, array($per_page, $offset));
        $logs = $wpdb->get_results($wpdb->prepare($query, $data_params), ARRAY_A);

        // Distinct actions for dropdown filter
        $distinct_actions = $wpdb->get_col("SELECT DISTINCT action FROM {$table} ORDER BY action ASC");

        return array(
            'logs'             => $logs,
            'total'            => (int) $total,
            'max_pages'        => ceil($total / $per_page) ?: 1,
            'current_page'     => $paged,
            'distinct_actions' => $distinct_actions,
        );
    }

    /**
     * AJAX: Clear Activity Logs
     */
    public function ajax_clear_activity_logs() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'kinboni_activity_logs';
        $wpdb->query("TRUNCATE TABLE {$table}");

        Kinboni_Activity_Logger::log('activity_logs_purged', 'Purged administrative activity logs table.');

        wp_send_json_success(array('message' => esc_html__('Activity logs truncated successfully.', 'kinboni-core')));
    }

    // =========================================================================
    // 3. DATABASE MAINTENANCE
    // =========================================================================

    public function get_database_table_status() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'kinboni_analytics',
            $wpdb->prefix . 'kinboni_searches',
            $wpdb->prefix . 'kinboni_subscribers',
            $wpdb->prefix . 'kinboni_abandoned_carts',
            $wpdb->prefix . 'kinboni_activity_logs',
        );

        $statuses = array();

        foreach ($tables as $tbl) {
            $status = $wpdb->get_row($wpdb->prepare("SHOW TABLE STATUS LIKE %s", $tbl), ARRAY_A);
            if ($status) {
                $data_size = (float) ($status['Data_length'] ?? 0);
                $index_size= (float) ($status['Index_length'] ?? 0);
                $total_kb  = round(($data_size + $index_size) / 1024, 2);

                $statuses[] = array(
                    'name'       => $tbl,
                    'rows'       => (int) ($status['Rows'] ?? 0),
                    'size_kb'    => $total_kb,
                    'engine'     => $status['Engine'] ?? 'InnoDB',
                    'collation'  => $status['Collation'] ?? 'utf8mb4_unicode_ci',
                );
            }
        }

        // Count expired transients
        $expired_transients = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(option_id) FROM {$wpdb->options} 
             WHERE option_name LIKE %s AND option_value < %d",
            '_transient_timeout_%',
            time()
        ));

        return array(
            'tables'             => $statuses,
            'expired_transients' => $expired_transients,
        );
    }

    /**
     * AJAX: Optimize Database Tables
     */
    public function ajax_optimize_database() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        global $wpdb;
        $tables = array(
            $wpdb->prefix . 'kinboni_analytics',
            $wpdb->prefix . 'kinboni_searches',
            $wpdb->prefix . 'kinboni_subscribers',
            $wpdb->prefix . 'kinboni_abandoned_carts',
            $wpdb->prefix . 'kinboni_activity_logs',
        );

        foreach ($tables as $t) {
            $wpdb->query("OPTIMIZE TABLE {$t}");
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('database_optimized', 'Optimized all KINBONI custom database tables.');
        }

        wp_send_json_success(array('message' => esc_html__('Database tables optimized successfully.', 'kinboni-core')));
    }

    /**
     * AJAX: Clear Expired Transients
     */
    public function ajax_clear_transients() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        global $wpdb;
        $time = time();

        // Delete expired transients and their timeouts
        $wpdb->query($wpdb->prepare(
            "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
             WHERE a.option_name LIKE %s
             AND a.option_name = CONCAT('_transient_', SUBSTRING(b.option_name, 20))
             AND b.option_name LIKE %s
             AND b.option_value < %d",
            '_transient_%',
            '_transient_timeout_%',
            $time
        ));

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('transients_purged', 'Purged expired transients and temporary cache.');
        }

        wp_send_json_success(array('message' => esc_html__('Expired transients and cache cleared.', 'kinboni-core')));
    }

    /**
     * Daily Cron: Cleanup old analytics, search logs, activity logs, and expired transients in batches
     */
    public function cron_cleanup_tables() {
        global $wpdb;

        $retention_days = (int) get_option('kinboni_log_retention_days', 90);
        if ($retention_days < 7) {
            $retention_days = 90;
        }

        $cutoff_date = date('Y-m-d H:i:s', current_time('timestamp') - ($retention_days * DAY_IN_SECONDS));

        // 1. Analytics table (batch delete in chunks of 1000)
        $table_analytics = $wpdb->prefix . 'kinboni_analytics';
        for ($i = 0; $i < 5; $i++) {
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$table_analytics} WHERE created_at < %s LIMIT 1000",
                $cutoff_date
            ));
            if (!$deleted || $deleted < 1000) {
                break;
            }
        }

        // 2. Searches table
        $table_searches = $wpdb->prefix . 'kinboni_searches';
        for ($i = 0; $i < 5; $i++) {
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$table_searches} WHERE created_at < %s LIMIT 1000",
                $cutoff_date
            ));
            if (!$deleted || $deleted < 1000) {
                break;
            }
        }

        // 3. Activity logs table
        $table_activity = $wpdb->prefix . 'kinboni_activity_logs';
        for ($i = 0; $i < 5; $i++) {
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$table_activity} WHERE created_at < %s LIMIT 1000",
                $cutoff_date
            ));
            if (!$deleted || $deleted < 1000) {
                break;
            }
        }

        // 3b. Abandoned carts table (purge old records beyond retention window)
        $table_carts = $wpdb->prefix . 'kinboni_abandoned_carts';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_carts} WHERE updated_at < %s LIMIT 1000",
            $cutoff_date
        ));

        // 4. Expired transients purge
        $time = time();
        $wpdb->query($wpdb->prepare(
            "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
             WHERE a.option_name LIKE %s
             AND a.option_name = CONCAT('_transient_', SUBSTRING(b.option_name, 20))
             AND b.option_name LIKE %s
             AND b.option_value < %d",
            '_transient_%',
            '_transient_timeout_%',
            $time
        ));

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('cron_cleanup_executed', sprintf('Daily maintenance cron purged records older than %d days and cleared expired transients.', $retention_days));
        }
    }

    // =========================================================================
    // 4. STAFF PERMISSIONS
    // =========================================================================

    public function get_staff_permissions() {
        $roles = wp_roles()->get_names();
        $shop_mgr_role = get_role('shop_manager');

        $caps = array(
            'manage_kinboni_orders'    => 'Manage Orders, Delivery Slips & Invoices',
            'manage_kinboni_products'  => 'Create & Bulk Edit Products & Stock',
            'manage_kinboni_marketing' => 'Access Coupon Engine & Subscribers',
            'manage_kinboni_analytics' => 'Access Live Traffic Analytics & Reports',
        );

        $saved_caps = get_option('kinboni_staff_caps_shop_manager', array(
            'manage_kinboni_orders'   => 'yes',
            'manage_kinboni_products' => 'yes',
            'manage_kinboni_marketing'=> 'no',
            'manage_kinboni_analytics'=> 'no',
        ));

        return array(
            'roles'      => $roles,
            'caps'       => $caps,
            'saved_caps' => $saved_caps,
        );
    }

    /**
     * AJAX: Save Staff Permissions
     */
    public function ajax_save_staff_permissions() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $allowed_caps = array('manage_kinboni_orders', 'manage_kinboni_products', 'manage_kinboni_marketing', 'manage_kinboni_analytics');
        $updated = array();

        $role = get_role('shop_manager');

        foreach ($allowed_caps as $c) {
            $is_on = isset($_POST[$c]) && wp_unslash($_POST[$c]) === 'yes';
            $updated[$c] = $is_on ? 'yes' : 'no';

            if ($role) {
                if ($is_on) {
                    $role->add_cap($c);
                } else {
                    $role->remove_cap($c);
                }
            }
        }

        update_option('kinboni_staff_caps_shop_manager', $updated);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('staff_permissions_updated', 'Updated Shop Manager role capabilities.');
        }

        wp_send_json_success(array('message' => esc_html__('Staff role permissions updated successfully.', 'kinboni-core')));
    }

    /**
     * Retrieve System & Environment Settings
     */
    public function get_system_settings() {
        return array(
            'trust_proxy_headers'      => get_option('kinboni_trust_proxy_headers', 'no'),
            'delete_data_on_uninstall' => get_option('kinboni_delete_data_on_uninstall', 'no'),
        );
    }

    /**
     * AJAX: Save System & Security Settings
     */
    public function ajax_save_system_settings() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $trust_proxy = isset($_POST['kinboni_trust_proxy_headers']) && wp_unslash($_POST['kinboni_trust_proxy_headers']) === 'yes' ? 'yes' : 'no';
        $delete_data = isset($_POST['kinboni_delete_data_on_uninstall']) && wp_unslash($_POST['kinboni_delete_data_on_uninstall']) === 'yes' ? 'yes' : 'no';

        update_option('kinboni_trust_proxy_headers', $trust_proxy);
        update_option('kinboni_delete_data_on_uninstall', $delete_data);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('system_settings_updated', sprintf('Updated system settings: Trust Proxy Headers=%s, Delete on Uninstall=%s', $trust_proxy, $delete_data));
        }

        wp_send_json_success(array('message' => esc_html__('System settings saved successfully.', 'kinboni-core')));
    }

    // =========================================================================
    // 5. ERROR LOG VIEWER
    // =========================================================================

    public function get_error_logs() {
        $log_file = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR . '/debug.log' : ABSPATH . 'wp-content/debug.log';
        $entries  = array();
        $is_active = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;

        if (file_exists($log_file) && is_readable($log_file)) {
            $lines = array();
            $fp = @fopen($log_file, 'rb');
            if ($fp) {
                $size = filesize($log_file);
                $read_bytes = min($size, 65536); // Read at most last 64KB for memory safety
                if ($read_bytes > 0) {
                    fseek($fp, -$read_bytes, SEEK_END);
                    $chunk = fread($fp, $read_bytes);
                    $raw_lines = explode("
", (string) $chunk);
                    if ($size > $read_bytes && count($raw_lines) > 1) {
                        array_shift($raw_lines); // Drop partial first line
                    }
                    $lines = array_values(array_filter(array_map('trim', $raw_lines)));
                }
                fclose($fp);
            }
            if ($lines) {
                // Get last 100 lines
                $lines = array_slice($lines, -100);
                foreach ($lines as $line) {
                    $severity = 'Notice';
                    if (stripos($line, 'Fatal error') !== false || stripos($line, 'Parse error') !== false) {
                        $severity = 'Fatal';
                    } elseif (stripos($line, 'Warning') !== false) {
                        $severity = 'Warning';
                    }

                    $entries[] = array(
                        'raw'      => esc_html($line),
                        'severity' => $severity,
                    );
                }
            }
        }

        return array(
            'file_exists' => file_exists($log_file),
            'log_path'    => $log_file,
            'is_active'   => $is_active,
            'entries'     => array_reverse($entries),
        );
    }

    /**
     * AJAX: Clear Error Log
     */
    public function ajax_clear_error_logs() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $log_file = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR . '/debug.log' : ABSPATH . 'wp-content/debug.log';

        if (file_exists($log_file) && is_writable($log_file)) {
            file_put_contents($log_file, '');
            wp_send_json_success(array('message' => esc_html__('Error log file cleared successfully.', 'kinboni-core')));
        }

        wp_send_json_error(array('message' => esc_html__('Error log file does not exist or is not writable.', 'kinboni-core')));
    }

    // =========================================================================
    // 6. BACKUP & RESTORE (JSON)
    // =========================================================================

    /**
     * AJAX: Import System Backup JSON
     */
    public function ajax_import_system_backup() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        if (empty($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => esc_html__('Please select a valid JSON backup file.', 'kinboni-core')));
        }

        // Limit file size (<= 1MB)
        if (!empty($_FILES['backup_file']['size']) && $_FILES['backup_file']['size'] > 1048576) {
            wp_send_json_error(array('message' => esc_html__('Backup file size exceeds the 1MB limit.', 'kinboni-core')));
        }

        // Check file extension (.json)
        $filename  = sanitize_file_name($_FILES['backup_file']['name']);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension !== 'json') {
            wp_send_json_error(array('message' => esc_html__('Invalid file extension. Please upload a .json file.', 'kinboni-core')));
        }

        $json_content = file_get_contents($_FILES['backup_file']['tmp_name']);
        $payload = json_decode($json_content, true);

        if (!$payload || !isset($payload['schema']) || $payload['schema'] !== 'kinboni-pro-settings') {
            wp_send_json_error(array('message' => esc_html__('Invalid backup file. Missing KINBONI Pro schema validation.', 'kinboni-core')));
        }

        $settings = isset($payload['settings']) && is_array($payload['settings']) ? $payload['settings'] : array();
        $whitelist = class_exists('Kinboni_DB') ? Kinboni_DB::get_default_options() : array();
        $imported_count = 0;

        // Recursive sanitizer helper (text/int/float/array)
        $sanitize_by_type = function ($val, $template) use (&$sanitize_by_type) {
            if (is_array($val)) {
                $clean_arr = array();
                foreach ($val as $k => $v) {
                    $clean_k = sanitize_key($k);
                    $clean_arr[$clean_k] = is_array($v) ? $sanitize_by_type($v, null) : sanitize_text_field((string)$v);
                }
                return $clean_arr;
            }
            if (is_int($template)) {
                return (int) $val;
            }
            if (is_float($template)) {
                return (float) $val;
            }
            if (is_bool($template)) {
                return (bool) $val;
            }
            return sanitize_text_field((string) $val);
        };

        foreach ($settings as $key => $val) {
            // NEVER import kinboni_staff_caps_* keys
            if (strpos($key, 'kinboni_staff_caps_') === 0) {
                continue;
            }
            // Only accept keys that exist in the whitelist built from Kinboni_DB defaults
            if (array_key_exists($key, $whitelist)) {
                $sanitized_val = $sanitize_by_type($val, $whitelist[$key]);
                update_option($key, $sanitized_val);
                $imported_count++;
            }
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('settings_backup_restored', sprintf('Restored %d settings from backup file: %s', $imported_count, sanitize_text_field($_FILES['backup_file']['name'])));
        }

        wp_send_json_success(array(
            'message' => sprintf(esc_html__('Successfully restored %d settings from backup.', 'kinboni-core'), $imported_count)
        ));
    }

    /**
     * AJAX: Restore Factory Default Settings
     */
    public function ajax_restore_default_settings() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $defaults = class_exists('Kinboni_DB') ? Kinboni_DB::get_default_options() : array(
            'kinboni_delivery_fee_inside'          => 70,
            'kinboni_delivery_fee_outside'         => 130,
            'kinboni_free_shipping_threshold'      => 2500,
            'kinboni_whatsapp_phone'               => '8801700000000',
            'kinboni_whatsapp_position'            => 'bottom-right',
            'kinboni_whatsapp_enabled'             => 'yes',
            'kinboni_live_sales_enabled'           => 'yes',
            'kinboni_live_sales_interval'          => 15,
            'kinboni_live_sales_sound'             => 'yes',
            'kinboni_live_sales_real_names'        => 'no',
            'kinboni_order_sound_enabled'          => 'yes',
            'kinboni_order_email_enabled'          => 'yes',
            'kinboni_stock_email_enabled'          => 'yes',
            'kinboni_stock_threshold'              => 5,
            'kinboni_low_stock_text_enabled'       => 'yes',
            'kinboni_theme_accent_color'           => '#D4AF37',
            'kinboni_theme_mode_default'           => 'system',
            'kinboni_cart_recommendations'         => 'yes',
            'kinboni_recently_viewed_enabled'      => 'yes',
            'kinboni_quickview_sizechart'          => 'yes',
            'kinboni_quickview_delivery_info'      => 'yes',
            'kinboni_automated_weekly_report'      => 'yes',
            'kinboni_report_recipients'            => get_option('admin_email'),
            'kinboni_abandoned_threshold_min'      => 30,
        );

        $defaults['kinboni_staff_caps_shop_manager'] = array(
            'manage_kinboni_orders'    => 'yes',
            'manage_kinboni_products'  => 'yes',
            'manage_kinboni_marketing' => 'no',
            'manage_kinboni_analytics' => 'no',
        );

        foreach ($defaults as $key => $val) {
            update_option($key, $val);
        }

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log('factory_defaults_restored', 'Restored all KINBONI Pro configuration options to factory defaults.');
        }

        wp_send_json_success(array('message' => esc_html__('All KINBONI Pro configurations have been reset to factory defaults.', 'kinboni-core')));
    }

    /**
     * Handle Exports (JSON Backup & Activity CSV)
     */
    public function handle_exports() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'kinboni-system') return;
        if (!current_user_can('manage_options')) return;

        // 1. Export Settings JSON Backup
        if (isset($_GET['action']) && $_GET['action'] === 'export_backup_json') {
            check_admin_referer('kinboni_system_export', '_kb_nonce');

            global $wpdb;
            $options_raw = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'kinboni_%'", ARRAY_A);

            $excluded_keys = array(
                'kinboni_courier_api_key',
                'kinboni_courier_secret_key',
                'kinboni_db_version',
            );

            $settings = array();
            foreach ($options_raw as $opt) {
                $opt_name = $opt['option_name'];
                if (in_array($opt_name, $excluded_keys, true)) {
                    continue;
                }
                if (strpos($opt_name, 'kinboni_staff_caps_') === 0) {
                    continue;
                }
                $settings[$opt_name] = maybe_unserialize($opt['option_value']);
            }

            $payload = array(
                'schema'          => 'kinboni-pro-settings',
                'version'         => KINBONI_CORE_VERSION,
                'created_at'      => current_time('mysql'),
                'site_url'        => site_url(),
                'settings_count'  => count($settings),
                'settings'        => $settings,
            );

            $filename = 'kinboni-backup-' . date('Y-m-d-His') . '.json';
            header('Content-Type: application/json; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');

            echo wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // 2. Export Activity Logs CSV
        if (isset($_GET['action']) && $_GET['action'] === 'export_activity_csv') {
            check_admin_referer('kinboni_system_export', '_kb_nonce');

            global $wpdb;
            $table = $wpdb->prefix . 'kinboni_activity_logs';
            $logs = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A);

            $filename = 'kinboni-activity-' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');

            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, array('Log ID', 'User ID', 'User Name', 'Action', 'Details', 'IP Address', 'Timestamp'));

            $csv_esc = array('Kinboni_Core', 'sanitize_csv_cell');
            foreach ($logs as $l) {
                fputcsv($out, array(
                    $l['id'],
                    $l['user_id'],
                    call_user_func($csv_esc, $l['user_name']),
                    call_user_func($csv_esc, $l['action']),
                    call_user_func($csv_esc, $l['details']),
                    call_user_func($csv_esc, $l['ip_address']),
                    $l['created_at'],
                ));
            }

            fclose($out);
            exit;
        }
    }
}

