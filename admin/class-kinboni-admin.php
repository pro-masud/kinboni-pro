<?php
/**
 * Kinboni Admin Manager
 *
 * Registers top-level KINBONI Pro menu, all 11 submenus,
 * asset enqueuing, permission checks, and module routing.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Sync custom administrator capabilities only in admin context
        if (is_admin()) {
            self::sync_admin_capabilities();
        }

        // Register Admin Menus
        add_action('admin_menu', array($this, 'register_admin_menus'), 25);

        // Enqueue Admin Scripts & Styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Admin Bar Notification Badge
        add_action('admin_bar_menu', array($this, 'add_admin_bar_order_badge'), 90);

        // AJAX Poller for Live New Orders
        add_action('wp_ajax_kinboni_check_new_orders', array($this, 'ajax_check_new_orders'));

        // Load Admin Modules
        $this->load_admin_modules();
    }

    /**
     * Load Available Admin Modules
     */
    private function load_admin_modules() {
        $is_admin_or_bg = (is_admin() || wp_doing_ajax() || wp_doing_cron() || (defined('WP_CLI') && WP_CLI) || php_sapi_name() === 'cli');

        // Module 01: Overview
        if ($is_admin_or_bg && file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-overview.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-overview.php';
            Kinboni_Admin_Overview::instance();
        }

        // Module 02: Orders
        if ($is_admin_or_bg && file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-orders.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-orders.php';
            Kinboni_Admin_Orders::instance();
        }

        // Module 03: Products
        if ($is_admin_or_bg && file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-products.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-products.php';
            Kinboni_Admin_Products::instance();
        }

        // Module 04: Customers
        if ($is_admin_or_bg && file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-customers.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-customers.php';
            Kinboni_Admin_Customers::instance();
        }

        // Module 05: Marketing
        if (file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-marketing.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-marketing.php';
            Kinboni_Admin_Marketing::instance();
        }

        // Module 06: Analytics
        if ($is_admin_or_bg && file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-analytics.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-analytics.php';
            Kinboni_Admin_Analytics::instance();
        }

        // Module 07: Delivery
        if (file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-delivery.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-delivery.php';
            Kinboni_Admin_Delivery::instance();
        }

        // Module 08: Notifications
        if (file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-notifications.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-notifications.php';
            Kinboni_Admin_Notifications::instance();
        }

        // Module 09: Storefront
        if (file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-storefront.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-storefront.php';
            Kinboni_Admin_Storefront::instance();
        }

        // Module 10: Reports
        if (file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-reports.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-reports.php';
            Kinboni_Admin_Reports::instance();
        }

        // Module 11: System
        if (file_exists(KINBONI_CORE_PATH . 'admin/modules/class-admin-system.php')) {
            require_once KINBONI_CORE_PATH . 'admin/modules/class-admin-system.php';
            Kinboni_Admin_System::instance();
        }
    }

    /**
     * Ensure Administrator role has custom staff capabilities
     */
    public static function sync_admin_capabilities() {
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $caps = array('manage_kinboni_orders', 'manage_kinboni_products', 'manage_kinboni_marketing', 'manage_kinboni_analytics');
            foreach ($caps as $cap) {
                if (!$admin_role->has_cap($cap)) {
                    $admin_role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Centralized capability / permission check for Kinboni admin modules
     *
     * @param string $module
     * @param int|null $user_id
     * @return bool
     */
    public static function user_can($module, $user_id = null) {
        if ($user_id) {
            $user = get_user_by('id', $user_id);
        } else {
            $user = wp_get_current_user();
        }

        if (!$user || !$user->exists()) {
            return false;
        }

        // Administrator always maintains full unrestricted access
        if (user_can($user, 'manage_options')) {
            return true;
        }

        // Core system & destructive tools require administrator
        if (in_array($module, array('system', 'backup', 'permissions', 'database', 'errors'), true)) {
            return false;
        }

        $module_cap_map = array(
            'overview'      => 'manage_woocommerce',
            'orders'        => 'manage_kinboni_orders',
            'products'      => 'manage_kinboni_products',
            'customers'     => 'manage_woocommerce',
            'marketing'     => 'manage_kinboni_marketing',
            'analytics'     => 'manage_kinboni_analytics',
            'delivery'      => 'manage_woocommerce',
            'notifications' => 'manage_woocommerce',
            'storefront'    => 'manage_woocommerce',
            'reports'       => 'manage_woocommerce',
        );

        $required_cap = isset($module_cap_map[$module]) ? $module_cap_map[$module] : $module;

        // Check if Shop Manager has explicit setting
        if (in_array('shop_manager', (array) $user->roles, true)) {
            $staff_caps = get_option('kinboni_staff_caps_shop_manager', array());
            if (isset($staff_caps[$required_cap])) {
                return $staff_caps[$required_cap] === 'yes';
            }
        }

        return user_can($user, $required_cap) || user_can($user, 'manage_woocommerce');
    }

    /**
     * Register Main Menu & 11 Submenus
     */
    public function register_admin_menus() {
        // Count actionable orders (pending, processing, on-hold) for Submenu Badge
        $pending_count = function_exists('wc_orders_count')
            ? ((int) wc_orders_count('pending') + (int) wc_orders_count('processing') + (int) wc_orders_count('on-hold'))
            : 0;
        $order_badge = $pending_count > 0 ? sprintf(' <span class="awaiting-mod update-plugins count-%1$d"><span class="pending-count">%1$d</span></span>', $pending_count) : '';

        // 1. Top-Level Menu
        add_menu_page(
            esc_html__('KINBONI Pro', 'kinboni-core'),
            esc_html__('KINBONI Pro', 'kinboni-core'),
            'manage_woocommerce',
            'kinboni-pro',
            array($this, 'render_overview_page'),
            'dashicons-vault',
            55
        );

        // Submenu 01: Overview (Dashboard)
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Overview — KINBONI Pro', 'kinboni-core'),
            esc_html__('Overview', 'kinboni-core'),
            'manage_woocommerce',
            'kinboni-pro',
            array($this, 'render_overview_page')
        );

        // Submenu 02: Orders
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Orders Management — KINBONI Pro', 'kinboni-core'),
            esc_html__('Orders', 'kinboni-core') . $order_badge,
            'manage_kinboni_orders',
            'kinboni-orders',
            array($this, 'render_orders_page')
        );

        // Submenu 03: Products
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Products & Inventory — KINBONI Pro', 'kinboni-core'),
            esc_html__('Products', 'kinboni-core'),
            'manage_kinboni_products',
            'kinboni-products',
            array($this, 'render_products_page')
        );

        // Submenu 04: Customers
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Patrons & Customers — KINBONI Pro', 'kinboni-core'),
            esc_html__('Customers', 'kinboni-core'),
            'manage_woocommerce',
            'kinboni-customers',
            array($this, 'render_customers_page')
        );

        // Submenu 05: Marketing
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Marketing & Subscriptions — KINBONI Pro', 'kinboni-core'),
            esc_html__('Marketing', 'kinboni-core'),
            'manage_kinboni_marketing',
            'kinboni-marketing',
            array($this, 'render_marketing_page')
        );

        // Submenu 06: Analytics
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Live Analytics & Traffic — KINBONI Pro', 'kinboni-core'),
            esc_html__('Analytics', 'kinboni-core'),
            'manage_kinboni_analytics',
            'kinboni-analytics',
            array($this, 'render_analytics_page')
        );

        // Submenu 07: Delivery
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Delivery & Logistics — KINBONI Pro', 'kinboni-core'),
            esc_html__('Delivery', 'kinboni-core'),
            'manage_woocommerce',
            'kinboni-delivery',
            array($this, 'render_delivery_page')
        );

        // Submenu 08: Notifications
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Alerts & Notifications — KINBONI Pro', 'kinboni-core'),
            esc_html__('Notifications', 'kinboni-core'),
            'manage_woocommerce',
            'kinboni-notifications',
            array($this, 'render_notifications_page')
        );

        // Submenu 09: Storefront
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Storefront & Experience — KINBONI Pro', 'kinboni-core'),
            esc_html__('Storefront', 'kinboni-core'),
            'manage_woocommerce',
            'kinboni-storefront',
            array($this, 'render_storefront_page')
        );

        // Submenu 10: Reports
        add_submenu_page(
            'kinboni-pro',
            esc_html__('Financial Reports & P&L — KINBONI Pro', 'kinboni-core'),
            esc_html__('Reports', 'kinboni-core'),
            'manage_woocommerce',
            'kinboni-reports',
            array($this, 'render_reports_page')
        );

        // Submenu 11: System
        add_submenu_page(
            'kinboni-pro',
            esc_html__('System Health & Logs — KINBONI Pro', 'kinboni-core'),
            esc_html__('System', 'kinboni-core'),
            'manage_options',
            'kinboni-system',
            array($this, 'render_system_page')
        );
    }

    /**
     * Enqueue Admin Assets on Kinboni Pages
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'kinboni') === false) {
            return;
        }

        // Enqueue WP Media Library on Products & Marketing Pages
        if (strpos($hook, 'kinboni-products') !== false || strpos($hook, 'kinboni-marketing') !== false) {
            wp_enqueue_media();
        }

        // Admin CSS
        wp_enqueue_style(
            'kinboni-admin-style',
            KINBONI_CORE_URL . 'admin/assets/css/kinboni-admin.css',
            array('dashicons'),
            KINBONI_CORE_VERSION
        );

        // Admin JS
        wp_enqueue_script(
            'kinboni-admin-js',
            KINBONI_CORE_URL . 'admin/assets/js/kinboni-admin.js',
            array('jquery'),
            KINBONI_CORE_VERSION,
            true
        );

        // Localize script data
        wp_localize_script('kinboni-admin-js', 'kinboniAdminData', array(
            'ajax_url'            => admin_url('admin-ajax.php'),
            'nonce'               => wp_create_nonce('kinboni_admin_nonce'),
            'currency_symbol'     => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳',
            'order_sound_enabled' => get_option('kinboni_order_sound_enabled', 'yes') === 'yes',
            'sound_tone'          => sanitize_key(get_option('kinboni_sound_tone', 'gold_bell')),
        ));
    }

    /**
     * Admin Bar New Order Notification Badge
     */
    public function add_admin_bar_order_badge($wp_admin_bar) {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        if (get_option('kinboni_admin_badge_enabled', 'yes') !== 'yes') {
            return;
        }

        $pending_count = function_exists('wc_orders_count')
            ? ((int) wc_orders_count('pending') + (int) wc_orders_count('processing') + (int) wc_orders_count('on-hold'))
            : 0;
        if ($pending_count <= 0) {
            return;
        }

        $title = sprintf(
            '<span class="ab-icon dashicons-vault"></span> <span class="ab-label">%1$s <span class="awaiting-mod" style="background:#D4AF37;color:#121214;padding:1px 6px;border-radius:10px;font-weight:bold;margin-left:4px;">%2$d</span></span>',
            esc_html__('KINBONI Orders', 'kinboni-core'),
            $pending_count
        );

        $wp_admin_bar->add_node(array(
            'id'    => 'kinboni-order-alerts',
            'title' => $title,
            'href'  => admin_url('admin.php?page=kinboni-orders&status=pending'),
            'meta'  => array('title' => esc_html__('View Pending Kinboni Orders', 'kinboni-core')),
        ));
    }

    /**
     * AJAX Live Order Poller
     */
    public function ajax_check_new_orders() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (!self::user_can('orders')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $user_id = get_current_user_id();

        $latest_order = wc_get_orders(array(
            'type'    => 'shop_order',
            'limit'   => 1,
            'orderby' => 'date',
            'order'   => 'DESC',
            'return'  => 'ids',
        ));

        $latest_id = !empty($latest_order) ? $latest_order[0] : 0;
        $last_seen_id = (int) get_user_meta($user_id, 'kinboni_last_seen_order_id', true);

        $has_new = false;
        if ($last_seen_id > 0 && $latest_id > $last_seen_id) {
            $has_new = true;
        }

        if ($latest_id > 0) {
            update_user_meta($user_id, 'kinboni_last_seen_order_id', $latest_id);
        }

        $pending_count = function_exists('wc_orders_count')
            ? ((int) wc_orders_count('pending') + (int) wc_orders_count('processing') + (int) wc_orders_count('on-hold'))
            : 0;

        wp_send_json_success(array(
            'has_new'       => $has_new,
            'latest_id'     => $latest_id,
            'pending_count' => $pending_count,
        ));
    }

    // =========================================================================
    // Page Renderers
    // =========================================================================

    public function render_overview_page() {
        if (!self::user_can('overview')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Overview')) {
            Kinboni_Admin_Overview::instance()->render();
        }
    }

    public function render_orders_page() {
        if (!self::user_can('orders')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Orders')) {
            Kinboni_Admin_Orders::instance()->render();
        } else {
            $this->render_module_placeholder('Orders Management', 'Order list table, HPOS integration, printable invoices, suspicious order detection, and CSV export.');
        }
    }

    public function render_products_page() {
        if (!self::user_can('products')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Products')) {
            Kinboni_Admin_Products::instance()->render();
        } else {
            $this->render_module_placeholder('Products & Inventory', 'Direct product upload/creator, stock quantity controls, bulk price editing, and duplication.');
        }
    }

    public function render_customers_page() {
        if (!self::user_can('customers')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Customers')) {
            Kinboni_Admin_Customers::instance()->render();
        } else {
            $this->render_module_placeholder('Patrons & Customers', 'Customer insights, lifetime spend, VIP tags, and one-click WhatsApp chat link.');
        }
    }

    public function render_marketing_page() {
        if (!self::user_can('marketing')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Marketing')) {
            Kinboni_Admin_Marketing::instance()->render();
        } else {
            $this->render_module_placeholder('Marketing Engine', 'Subscriber management, coupon manager, abandoned cart recovery, and tracking IDs.');
        }
    }

    public function render_analytics_page() {
        if (!self::user_can('analytics')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Analytics')) {
            Kinboni_Admin_Analytics::instance()->render();
        } else {
            $this->render_module_placeholder('Analytics & Traffic', 'Real unique visitor trends, device breakdown, conversion rate, search logs, and wishlists.');
        }
    }

    public function render_delivery_page() {
        if (!self::user_can('delivery')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Delivery')) {
            Kinboni_Admin_Delivery::instance()->render();
        } else {
            $this->render_module_placeholder('Delivery & Logistics', 'Inside and outside Dhaka pricing, free delivery thresholds, and Thana/District cascading.');
        }
    }

    public function render_notifications_page() {
        if (!self::user_can('notifications')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Notifications')) {
            Kinboni_Admin_Notifications::instance()->render();
        } else {
            $this->render_module_placeholder('Alerts & Notifications', 'Order notification sounds, admin bar badges, and out-of-stock email configuration.');
        }
    }

    public function render_storefront_page() {
        if (!self::user_can('storefront')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Storefront')) {
            Kinboni_Admin_Storefront::instance()->render();
        } else {
            $this->render_module_placeholder('Storefront Experience', 'Live sales social proof toggles, WhatsApp floating button styling, and theme color controls.');
        }
    }

    public function render_reports_page() {
        if (!self::user_can('reports')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_Reports')) {
            Kinboni_Admin_Reports::instance()->render();
        } else {
            $this->render_module_placeholder('Reports & P&L', 'Daily, weekly, and monthly sales reports, Cost of Goods Sold (COGS), gross profit, and PDF exports.');
        }
    }

    public function render_system_page() {
        if (!self::user_can('system')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'kinboni-core'));
        }
        if (class_exists('Kinboni_Admin_System')) {
            Kinboni_Admin_System::instance()->render();
        } else {
            $this->render_module_placeholder('System & Security', 'Backup/restore JSON, administrator activity logging, staff permissions, and error log viewer.');
        }
    }

    private function render_module_placeholder($title, $description) {
        ?>
        <div class="kinboni-admin-wrap">
            <header class="kinboni-header">
                <div class="kinboni-header-left">
                    <div class="kinboni-logo-badge">K</div>
                    <div>
                        <h1 class="kinboni-title">KINBONI Pro <span><?php echo esc_html($title); ?></span></h1>
                        <p class="kinboni-subtitle"><?php echo esc_html($description); ?></p>
                    </div>
                </div>
            </header>
            <div class="kinboni-panel" style="padding: 40px; text-align: center;">
                <span class="dashicons dashicons-hammer" style="font-size: 40px; width: 40px; height: 40px; color: var(--kb-gold-600); margin-bottom: 12px;"></span>
                <h3 style="margin: 0 0 8px; font-size: 18px;">Module in Active Development</h3>
                <p style="color: var(--kb-text-secondary); max-width: 480px; margin: 0 auto;">
                    This module is scheduled in our systematic deployment pipeline and will be loaded with its complete functionality and database integrations in the subsequent step.
                </p>
            </div>
        </div>
        <?php
    }
}

