<?php
/**
 * Kinboni Pro Admin View: System, Security & Operations Engine
 *
 * @package Kinboni_Core
 * @version 1.0.0
 * @var string $active_tab
 * @var array $system_info
 * @var array $activity_data
 * @var array $db_status
 * @var array $permissions
 * @var array $error_logs
 * @var array $system_settings
 */

if (!defined('ABSPATH')) {
    exit;
}

$base_url = admin_url('admin.php?page=kinboni-system');
?>

<div class="kinboni-admin-wrap" id="kinboni-system-app">

    <!-- Top Header -->
    <header class="kinboni-header">
        <div class="kinboni-header-left">
            <div class="kinboni-logo-badge">K</div>
            <div>
                <h1 class="kinboni-title">KINBONI Pro <span>System & Operations</span></h1>
                <p class="kinboni-subtitle">Environment Diagnostics &bull; Backup & Restore &bull; Activity Auditor &bull; Staff Permissions</p>
            </div>
        </div>

        <div class="kinboni-header-right">
            <?php if ($active_tab === 'backup') : ?>
                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('page' => 'kinboni-system', 'action' => 'export_backup_json'), admin_url('admin.php')), 'kinboni_system_export', '_kb_nonce')); ?>" class="btn-action-gold">
                    <span class="dashicons dashicons-download"></span> Export JSON Backup
                </a>
            <?php elseif ($active_tab === 'activity') : ?>
                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('page' => 'kinboni-system', 'action' => 'export_activity_csv'), admin_url('admin.php')), 'kinboni_system_export', '_kb_nonce')); ?>" class="btn-action-outline">
                    <span class="dashicons dashicons-download"></span> Export Activity CSV
                </a>
                <button type="button" id="btn-clear-activity-logs" class="btn-action-outline" style="color:#DC2626; border-color:#FCA5A5;">
                    <span class="dashicons dashicons-trash"></span> Clear Logs
                </button>
            <?php elseif ($active_tab === 'database') : ?>
                <button type="button" id="btn-optimize-db" class="btn-action-gold">
                    <span class="dashicons dashicons-database"></span> Optimize Tables Now
                </button>
                <button type="button" id="btn-clear-transients" class="btn-action-outline">
                    <span class="dashicons dashicons-trash"></span> Clear Expired Transients
                </button>
            <?php elseif ($active_tab === 'errors') : ?>
                <button type="button" id="btn-clear-error-logs" class="btn-action-outline" style="color:#DC2626; border-color:#FCA5A5;">
                    <span class="dashicons dashicons-trash"></span> Clear Error Log
                </button>
            <?php endif; ?>

            <button type="button" class="kinboni-btn-refresh" onclick="window.location.reload();" title="Refresh">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </header>

    <!-- Navigation Tabs Bar -->
    <div class="kinboni-tabs-bar" style="margin-bottom: 24px;">
        <a href="<?php echo esc_url(add_query_arg('tab', 'info', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'info' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-info"></span> System Information
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'backup', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'backup' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-backup"></span> Backup & Restore
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'activity', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'activity' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-visibility"></span> Activity Logs
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'permissions', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'permissions' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-admin-network"></span> Staff Permissions
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'database', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'database' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-database"></span> Database Maintenance
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'errors', $base_url)); ?>" class="tab-pill <?php echo $active_tab === 'errors' ? 'is-active' : ''; ?>">
            <span class="dashicons dashicons-warning"></span> Error Logs
        </a>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 1: SYSTEM INFORMATION                                           -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'info') : ?>
        <div class="grid-2col" style="gap: 24px; align-items: start;">

            <!-- Core Environment Spec Table -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Core Environment Diagnostics</h3>
                </div>
                <div class="panel-body p-0">
                    <table class="kinboni-table">
                        <tbody>
                            <tr>
                                <td><strong>WordPress Version</strong></td>
                                <td class="text-right font-bold text-dark"><?php echo esc_html($system_info['wordpress_version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>WooCommerce Version</strong></td>
                                <td class="text-right font-bold text-dark"><?php echo esc_html($system_info['woocommerce_version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>KINBONI Pro Core</strong></td>
                                <td class="text-right font-bold" style="color:var(--kb-gold-600);"><?php echo esc_html($system_info['plugin_version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Order Storage Architecture</strong></td>
                                <td class="text-right font-bold" style="color:#059669;"><?php echo esc_html($system_info['hpos_enabled']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version</strong></td>
                                <td class="text-right font-bold text-dark"><?php echo esc_html($system_info['php_version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Web Server</strong></td>
                                <td class="text-right text-subtle text-xs"><?php echo esc_html($system_info['server_software']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>MySQL Database Version</strong></td>
                                <td class="text-right text-subtle text-xs"><?php echo esc_html($system_info['mysql_version']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Memory Limits & Sizing -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Resource Allocations & Limits</h3>
                </div>
                <div class="panel-body p-0">
                    <table class="kinboni-table">
                        <tbody>
                            <tr>
                                <td><strong>PHP Memory Limit</strong></td>
                                <td class="text-right font-bold text-dark"><?php echo esc_html($system_info['memory_limit']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>WordPress Memory Limit</strong></td>
                                <td class="text-right font-bold text-dark"><?php echo esc_html($system_info['wp_memory_limit']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Upload Max Filesize</strong></td>
                                <td class="text-right text-dark"><?php echo esc_html($system_info['upload_max_filesize']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Post Max Size</strong></td>
                                <td class="text-right text-dark"><?php echo esc_html($system_info['post_max_size']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Max Execution Time</strong></td>
                                <td class="text-right text-dark"><?php echo esc_html($system_info['max_execution_time']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>cURL Protocol Support</strong></td>
                                <td class="text-right text-subtle text-xs"><?php echo esc_html($system_info['curl_version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Atelier URL</strong></td>
                                <td class="text-right text-subtle text-xs"><?php echo esc_html($system_info['home_url']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Security, Proxy & Lifecycle Configuration -->
            <div class="kinboni-panel" style="grid-column: 1 / -1; margin-top: 8px;">
                <div class="panel-header">
                    <h3 class="panel-title"><?php esc_html_e('Security & Environment Configuration', 'kinboni-core'); ?></h3>
                </div>
                <form id="form-system-settings" class="panel-body">
                    <div class="form-group mb-4">
                        <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="kinboni_trust_proxy_headers" value="yes" <?php checked(($system_settings['trust_proxy_headers'] ?? 'no'), 'yes'); ?> style="margin-top:2px;" />
                            <div>
                                <span class="font-bold text-dark text-sm block"><?php esc_html_e('Trust Reverse Proxy Headers (Cloudflare / LiteSpeed / Load Balancer)', 'kinboni-core'); ?></span>
                                <span class="text-xs text-subtle"><?php esc_html_e('When enabled, the plugin inspects CF-Connecting-IP and X-Forwarded-For to accurately identify client IPs behind reverse proxies. Only enable if your server is behind a trusted reverse proxy.', 'kinboni-core'); ?></span>
                            </div>
                        </label>
                    </div>

                    <div class="form-group mb-4" style="border-top:1px solid #E8E6DF; padding-top:14px;">
                        <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="kinboni_delete_data_on_uninstall" value="yes" <?php checked(($system_settings['delete_data_on_uninstall'] ?? 'no'), 'yes'); ?> style="margin-top:2px;" />
                            <div>
                                <span class="font-bold text-sm block" style="color:#DC2626;"><?php esc_html_e('Danger Zone: Delete All Plugin Data on Uninstall', 'kinboni-core'); ?></span>
                                <span class="text-xs text-subtle"><?php esc_html_e('When enabled, deleting the plugin will purge custom database tables and kinboni_* options. WooCommerce orders, customer profiles, and products are NEVER deleted.', 'kinboni-core'); ?></span>
                            </div>
                        </label>
                    </div>

                    <button type="submit" id="btn-save-system-settings" class="btn-action-gold" style="padding:10px 24px;">
                        <span class="dashicons dashicons-saved"></span> <?php esc_html_e('Save System Configuration', 'kinboni-core'); ?>
                    </button>
                </form>
            </div>

        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 2: BACKUP & RESTORE                                             -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'backup') : ?>
        <div class="grid-2col" style="gap: 24px; align-items: start;">

            <!-- Export Settings Card -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Export Settings Backup</h3>
                </div>
                <div class="panel-body">
                    <p class="text-sm text-subtle mb-4">
                        Download a verified JSON backup containing all configured delivery rates, coupon restrictions, notification routing, live sales intervals, and storefront theme preferences.
                    </p>
                    <p class="text-xs text-subtle mb-4">
                        <?php esc_html_e('API keys and staff permissions are not included in backups.', 'kinboni-core'); ?>
                    </p>
                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('page' => 'kinboni-system', 'action' => 'export_backup_json'), admin_url('admin.php')), 'kinboni_system_export', '_kb_nonce')); ?>" class="btn-action-gold" style="padding:12px 24px; display:inline-flex;">
                        <span class="dashicons dashicons-download"></span> Download Backup JSON File
                    </a>
                </div>
            </div>

            <!-- Import Settings Card -->
            <div class="kinboni-panel">
                <div class="panel-header">
                    <h3 class="panel-title">Restore Settings from Backup</h3>
                </div>
                <form id="form-import-backup" class="panel-body" enctype="multipart/form-data">
                    <p class="text-sm text-subtle mb-4">
                        Restore configurations from a previously exported `.json` file. The file is validated against the KINBONI Pro schema before applying.
                    </p>
                    <div class="form-group mb-4">
                        <input type="file" name="backup_file" id="input-backup-file" accept=".json" class="form-control" required style="padding:8px;" />
                    </div>
                    <button type="submit" id="btn-submit-import-backup" class="btn-action-gold" style="padding:10px 22px;">
                        <span class="dashicons dashicons-upload"></span> Validate & Restore Settings
                    </button>
                </form>
            </div>

        </div>

        <!-- Factory Reset Card -->
        <div class="kinboni-panel" style="margin-top: 24px; border-left: 4px solid #DC2626;">
            <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3 class="panel-title" style="color:#DC2626;">Factory Default Reset</h3>
                <span class="badge-status status-processing" style="color:#DC2626; background:#FEE2E2;">Careful Zone</span>
            </div>
            <div class="panel-body" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div>
                    <h4 class="font-bold text-dark text-sm mb-1">Reset all KINBONI Pro configurations to factory defaults</h4>
                    <p class="text-xs text-subtle" style="margin:0; max-width:620px;">
                        This will reset delivery rates, notification email routing, live sales interval, staff roles, and storefront appearance to pristine defaults.
                        <strong style="color:#374151;">Your WooCommerce orders, customers, and product catalog will NOT be deleted or altered.</strong>
                    </p>
                </div>
                <button type="button" id="btn-restore-defaults" class="btn-action-outline" style="color:#DC2626; border-color:#FCA5A5; padding:10px 20px;">
                    <span class="dashicons dashicons-image-rotate"></span> Reset to Factory Defaults
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 3: ACTIVITY LOGS AUDITOR                                        -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'activity') : 
        $logs        = $activity_data['logs'] ?? array();
        $total_logs  = $activity_data['total'] ?? 0;
        $max_pages   = $activity_data['max_pages'] ?? 1;
        $curr_page   = $activity_data['current_page'] ?? 1;
        $actions     = $activity_data['distinct_actions'] ?? array();
        $curr_action = isset($_GET['act']) ? sanitize_text_field(wp_unslash($_GET['act'])) : '';
        $search_s    = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    ?>
        <!-- Search & Filter Toolbar -->
        <div class="kinboni-toolbar-panel" style="margin-bottom:16px;">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="kinboni-filter-form">
                <input type="hidden" name="page" value="kinboni-system" />
                <input type="hidden" name="tab" value="activity" />

                <div class="filter-group flex-search">
                    <span class="dashicons dashicons-search icon-search"></span>
                    <input type="text" name="s" value="<?php echo esc_attr($search_s); ?>" placeholder="Search activity by user, details, or IP..." class="input-search" />
                </div>

                <div class="filter-group">
                    <select name="act" class="filter-select">
                        <option value="">All Action Types</option>
                        <?php foreach ($actions as $act) : ?>
                            <option value="<?php echo esc_attr($act); ?>" <?php selected($curr_action, $act); ?>><?php echo esc_html(str_replace('_', ' ', ucfirst($act))); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-filter-apply">Filter</button>
                <?php if (!empty($curr_action) || !empty($search_s)) : ?>
                    <a href="<?php echo esc_url(add_query_arg('tab', 'activity', $base_url)); ?>" class="btn-filter-reset">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Activity Table -->
        <div class="kinboni-panel">
            <div class="panel-body p-0">
                <?php if (!empty($logs)) : ?>
                    <table class="kinboni-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Administrator</th>
                                <th>Action</th>
                                <th>Audit Details</th>
                                <th width="120">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $l) : ?>
                                <tr>
                                    <td class="text-subtle text-xs" width="160">
                                        <?php echo esc_html(date_i18n('M j, Y H:i:s', strtotime($l['created_at']))); ?>
                                    </td>
                                    <td>
                                        <span class="font-bold text-dark text-xs"><?php echo esc_html($l['user_name']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-status status-completed" style="font-size:10px;">
                                            <?php echo esc_html($l['action']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-xs"><?php echo esc_html($l['details']); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-subtle text-xs"><?php echo esc_html($l['ip_address']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-visibility"></span>
                        <h3>No Activity Recorded Yet</h3>
                        <p>Administrative modifications, order status transitions, and price adjustments will be logged here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($max_pages > 1) : ?>
            <div class="kinboni-pagination">
                <?php for ($p = 1; $p <= $max_pages; $p++) : ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $p)); ?>" class="page-link <?php echo $curr_page == $p ? 'is-active' : ''; ?>">
                        <?php echo esc_html($p); ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 4: STAFF PERMISSIONS                                            -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'permissions') : 
        $caps       = $permissions['caps'] ?? array();
        $saved_caps = $permissions['saved_caps'] ?? array();
    ?>
        <div class="kinboni-panel" style="max-width: 680px;">
            <div class="panel-header">
                <h3 class="panel-title">Shop Manager Capabilities</h3>
            </div>
            <form id="form-staff-permissions" class="panel-body">
                <p class="text-sm text-subtle mb-4">
                    Control which submenus and operational capabilities are accessible to users with the <strong>Shop Manager</strong> role.
                    Administrators always maintain unrestricted access.
                </p>

                <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:24px;">
                    <?php foreach ($caps as $cap_key => $cap_title) : ?>
                        <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; background:#FAF9F6; padding:12px 14px; border-radius:6px; border:1px solid #E8E6DF;">
                            <input type="checkbox" name="<?php echo esc_attr($cap_key); ?>" value="yes" <?php checked(($saved_caps[$cap_key] ?? 'no'), 'yes'); ?> style="margin-top:2px;" />
                            <div>
                                <span class="font-bold text-dark text-sm block"><?php echo esc_html($cap_title); ?></span>
                                <span class="text-xs text-subtle">Capability: <code><?php echo esc_html($cap_key); ?></code></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" id="btn-save-staff-permissions" class="btn-action-gold" style="padding:10px 24px;">
                    <span class="dashicons dashicons-saved"></span> Update Staff Capabilities
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 5: DATABASE MAINTENANCE                                         -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'database') : 
        $tables     = $db_status['tables'] ?? array();
        $transients = $db_status['expired_transients'] ?? 0;
    ?>
        <div class="kinboni-panel">
            <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3 class="panel-title">Custom Database Tables & Storage Health</h3>
                <span class="text-xs text-subtle">Expired Transients: <strong><?php echo esc_html($transients); ?></strong></span>
            </div>
            <div class="panel-body p-0">
                <table class="kinboni-table">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Storage Engine</th>
                            <th>Collation</th>
                            <th width="100" class="text-right">Total Rows</th>
                            <th width="120" class="text-right">Size (KB)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $t) : ?>
                            <tr>
                                <td>
                                    <span class="font-bold text-dark text-xs"><code><?php echo esc_html($t['name']); ?></code></span>
                                </td>
                                <td><span class="badge-status status-completed"><?php echo esc_html($t['engine']); ?></span></td>
                                <td class="text-subtle text-xs"><?php echo esc_html($t['collation']); ?></td>
                                <td class="text-right font-bold text-dark"><?php echo esc_html(number_format($t['rows'])); ?></td>
                                <td class="text-right text-subtle text-xs"><?php echo esc_html($t['size_kb']); ?> KB</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- TAB 6: ERROR LOGS                                                   -->
    <!-- =================================================================== -->
    <?php if ($active_tab === 'errors') : 
        $entries   = $error_logs['entries'] ?? array();
        $is_active = $error_logs['is_active'] ?? false;
        $log_path  = $error_logs['log_path'] ?? '';
    ?>
        <div class="kinboni-panel">
            <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 class="panel-title">Recent WordPress Debug & Error Log</h3>
                    <p class="text-xs text-subtle" style="margin-top:2px;">Target: <code><?php echo esc_html($log_path); ?></code></p>
                </div>
                <span class="badge-status <?php echo $is_active ? 'status-completed' : 'status-processing'; ?>">
                    <?php echo $is_active ? 'WP_DEBUG_LOG Active' : 'Debug Logging Inactive'; ?>
                </span>
            </div>
            <div class="panel-body p-0">
                <?php if (!empty($entries)) : ?>
                    <div style="max-height: 480px; overflow-y: auto; padding: 12px; background: #121214; color: #E5E7EB; font-family: monospace; font-size: 11px; line-height: 1.6;">
                        <?php foreach ($entries as $e) : ?>
                            <div style="padding: 4px 0; border-bottom: 1px solid #27272A;">
                                <span style="font-weight:bold; color:<?php echo ($e['severity'] === 'Fatal') ? '#EF4444' : (($e['severity'] === 'Warning') ? '#F59E0B' : '#3B82F6'); ?>;">
                                    [<?php echo esc_html($e['severity']); ?>]
                                </span>
                                <span><?php echo esc_html($e['raw']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="kinboni-empty-state">
                        <span class="dashicons dashicons-yes-alt" style="color:#059669;"></span>
                        <h3>No Errors Logged</h3>
                        <p>Your WordPress environment is performing without recorded exceptions or PHP errors.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

