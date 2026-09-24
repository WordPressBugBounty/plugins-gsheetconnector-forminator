<?php
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/* Prevent Subscribers from seeing sensitive info */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have permission to access this page.', 'gsheetconnector-forminator' ) );
}

$formtr_gs_tools_service = new GS_FORMNTR_Init();
?>
<!-- SYSTEM INFO SECTION -->
<div class="formntr-free-info-container">
    <div class="heading mt-0">
        <?php esc_html_e('System Information', 'gsheetconnector-forminator'); ?>
    </div>

    <p>
        <?php esc_html_e(
            'View detailed information about your plugin, server, and WordPress setup for troubleshooting and support.',
            'gsheetconnector-forminator'
        ); ?>
    </p>

    <div class="text-right d-flex justify-end mb-20">
        <button id="formntr-copy-system-info"
            class="btn btn-primary d-flex align-center gap-10">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2" />
                <rect x="2" y="2" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2" />
            </svg>
            <?php esc_html_e('Copy System Info', 'gsheetconnector-forminator'); ?>
        </button>
    </div>
    <?php
    global $wpdb;
    /*  Get WordPress version. */
    $wp_version = get_bloginfo('version');

    /*  Get theme info. */
    $theme_data = wp_get_theme();
    $theme_name_version = $theme_data->get('Name') . ' ' . $theme_data->get('Version');
    $parent_theme = $theme_data->get('Template');

    if (!empty($parent_theme)) {
        $parent_theme_data = wp_get_theme($parent_theme);
        $parent_theme_name_version = $parent_theme_data->get('Name') . ' ' . $parent_theme_data->get('Version');
    } else {
        $parent_theme_name_version = 'N/A';
    }

    /*  Check plugin version and subscription plan. */
    if (! function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    $plugin_name = 'GSheetConnector for Forminator Forms';

    /*  Check plugin version and subscription plan. */
    $plugin_version = defined('GS_FORMNTR_VERSION') ? GS_FORMNTR_VERSION : 'N/A';
    $subscription_plan = 'Free';
    $api_token_auto = get_option('gs_formntr_token');

    $formntr_auth_method = 0;
    $auth = 'Authenticated Using Existing Method';




    if (!empty($api_token_auto) ) {
        $google_sheet_auto = new FORMI_GSC_googlesheet();
        $email_account_auto = $google_sheet_auto->gsheet_print_google_account_email();
        $connected_email = !empty($email_account_auto) ? esc_html($email_account_auto) : 'Not Auth';
    }  else {
        $connected_email = 'Not Connected';
    }

    /*  Check Google Permission. */
    $formntr_verify_status = get_option('gs_formntr_verify');
    $formntr_search_permission = ($formntr_verify_status === 'valid') ? 'Granted' : 'Denied';

    /*  Create the system info HTML. */
    $system_info = '';
    ?>
    <div class="mb-20 mt-20"><button id="formntr-show-info-button" class="info-button">GSheetConnector Status<span
                class="dashicons dashicons-arrow-down"></span></button></div>
    <div id="formntr-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <table>
            <tr>
                <td>Plugin Name</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($plugin_name); ?></td>
            </tr>
            <tr>
                <td>Plugin Version</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($plugin_version); ?></td>
            </tr>
            <tr>
                <td>Plugin Subscription Plan</td>
                <td class="fw-600 common-badge-table pro-badge"><?php echo  esc_html($subscription_plan); ?></td>
            </tr>
            <tr>
                <td>Connected Email Account</td>
                <td class="fw-600"><?php echo esc_html($connected_email); ?></td>
            </tr>
            <tr>
                <td>Authentication method for connecting to Google Sheets</td>
                <td class="fw-600"><?php echo esc_html($auth); ?></td>
            </tr>
            <?php


            if (!empty($api_token_auto) && $formntr_auth_method == 0) {
                $permission_class = ($formntr_search_permission === 'Granted') ? 'permission-given' : 'permission-not-given';
            ?>
                <tr>
                    <td>Google Drive Permission</td>
                    <td class="fw-700 permission-badge <?php echo esc_attr($permission_class); ?> ">
                        <?php echo esc_html($formntr_search_permission); ?>
                    </td>
                </tr>
                <tr>
                    <td>Google Sheet Permission</td>
                    <td class="fw-700 permission-badge <?php echo esc_attr($permission_class); ?> ">
                        <?php echo esc_html($formntr_search_permission); ?>
                    </td>
                </tr>
            <?php
            }
            ?>



        </table>
    </div>
    <?php
    /* Add WordPress info.
 Create a button for WordPress info.*/
    ?>
    <div class="mb-20 mt-20"><button id="formntr-show-wordpress-info-button" class="info-button">WordPress<span
                class="dashicons dashicons-arrow-down"></span></button></div>
    <div id="formntr-wordpress-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <table>
            <tr>
                <td>Version</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html(get_bloginfo('version')); ?></td>
            </tr>
            <tr>
                <td>Site Language</td>
                <td class="fw-600"><?php echo esc_html(get_bloginfo('language')); ?></td>
            </tr>
            <tr>
                <td>Debug Mode</td>
                <td class="fw-600 common-badge-table info-name-yellow"><?php echo (WP_DEBUG ? 'Enabled' : 'Disabled'); ?>
                </td>
            </tr>
            <tr>
                <td>Home URL</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_url(get_home_url()); ?></td>
            </tr>
            <tr>
                <td>Site URL</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_url(get_site_url()); ?></td>
            </tr>
            <tr>
                <td>Permalink structure</td>
                <td class="fw-600"><?php echo esc_html(get_option('permalink_structure')); ?></td>
            </tr>
            <tr>
                <td>Is this site using HTTPS?</td>
                <td class="fw-600"><?php echo (is_ssl() ? 'Yes' : 'No'); ?></td>
            </tr>
            <tr>
                <td>Is this a multisite?</td>
                <td class="fw-600"><?php echo (is_multisite() ? 'Yes' : 'No'); ?></td>
            </tr>
            <tr>
                <td>Can anyone register on this site?</td>
                <td class="fw-600"><?php echo (get_option('users_can_register') ? 'Yes' : 'No'); ?></td>
            </tr>
            <tr>
                <td>Is this site discouraging search engines?</td>
                <td class="fw-600"><?php echo (get_option('blog_public') ? 'No' : 'Yes'); ?></td>
            </tr>
            <tr>
                <td>Default comment status</td>
                <td class="fw-600"><?php echo esc_html(get_option('default_comment_status')); ?></td>
            </tr>
            <?php

            $server_ip = isset($_SERVER['REMOTE_ADDR']) ? filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP) : '';

            if (filter_var($server_ip, FILTER_VALIDATE_IP) === false) {
                /*  Invalid IP address, handle this error (you might want to set a default value). */
                $environment_type = 'unknown';
            } else {
                /*  Validate against known local addresses.*/
                $known_local_ips = array('127.0.0.1', '::1');

                $isLocalhost = in_array($server_ip, $known_local_ips);

                $environment_type = $isLocalhost ? 'localhost' : 'production';
            }

            ?>

            <tr>
                <td>Environment type</td>
                <td class="fw-600 common-badge-table info-name-yellow"><?php echo esc_html($environment_type); ?></td>
            </tr>
            <?php
            $user_count = count_users();
            $total_users = $user_count['total_users'];
            ?>
            <tr>
                <td>User Count</td>
                <td class="fw-600"><?php echo esc_html($total_users); ?></td>
            </tr>

            <tr>
                <td>Communication with WordPress.org</td>
                <td class="fw-600"><?php echo (get_option('blog_publicize') ? 'Yes' : 'No'); ?></td>
            </tr>
        </table>
    </div>


    <?php

    /*  info about active theme. */
    $active_theme = wp_get_theme();
    ?>
    <div class="mb-20 mt-20">
        <button id="formntr-show-active-info-button" class="info-button">
            Active Theme <span class="dashicons dashicons-arrow-down"></span>
        </button>
    </div>

    <div id="formntr-active-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <table>
            <tr>
                <td>Name</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($active_theme->get('Name')); ?>
                </td>
            </tr>
            <tr>
                <td>Version</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($active_theme->get('Version')); ?>
                </td>
            </tr>
            <tr>
                <td>Author</td>
                <td class="fw-600"><?php echo esc_html($active_theme->get('Author')); ?></td>
            </tr>
            <tr>
                <td>Author website</td>
                <td class="fw-600"><?php echo esc_url($active_theme->get('AuthorURI')); ?></td>
            </tr>
            <tr>
                <td>Theme directory location</td>
                <td class="fw-600"><?php echo esc_html($active_theme->get_template_directory()); ?></td>
            </tr>
        </table>
    </div>
    <?php
    /*  Get a list of other plugins you want to check compatibility with*/
    $other_plugins = array(
        'plugin-folder/plugin-file.php', /*  Replace with the actual plugin slug */
        /*  Add more plugins as needed. */
    );

    /*  Network Active Plugins. */
    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        if (!empty($network_active_plugins)) {
    ?>
            <div class="mb-20 mt-20">
                <button id="formntr-show-netplug-info-button" class="info-button">
                    Network Active plugins <span class="dashicons dashicons-arrow-down"></span>
                </button>
            </div>

            <div id="formntr-netplug-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
                <table>
                    <?php
                    foreach ($network_active_plugins as $plugin => $plugin_data) {
                        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                        $system_info .= '<tr><td>' . $plugin_data['Name'] . '</td><td>' . $plugin_data['Version'] . '</td></tr>';
                    }

                    /*  Add more network active plugin statuses here... */
                    ?>
                </table>
            </div>
    <?php
        }
    }
    /*  Active plugins.*/
    $active_plugins = get_option('active_plugins', array());

    /*  Total active plugins count*/
    $total_active_plugins = count($active_plugins);

    ?>
    <div class="mb-20  mt-20"><button id="formntr-show-acplug-info-button" class="info-button">Active plugins
            (<?php echo esc_html($total_active_plugins); ?>)<span class="dashicons dashicons-arrow-down"></span></button></div>
    <div id="formntr-acplug-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <table>
            <?php
            /*  Retrieve all active plugins data. */
            $active_plugins_data = array();
            $active_plugins = get_option('active_plugins', array());

            /*  Include the necessary WordPress file. */
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            $all_plugins = get_plugins();
            foreach ($active_plugins as $plugin) {
                if (isset($all_plugins[$plugin])) {
                    $plugin_data = $all_plugins[$plugin];
                    $active_plugins_data[$plugin] = array(
                        'name' => $plugin_data['Name'],
                        'version' => $plugin_data['Version'],
                        'count' => 0, /*  Initialize the count to zero. */
                    );
                }
            }

            /*  Count the number of active installations for each plugin. */
            foreach ($all_plugins as $plugin_file => $plugin_data) {
                if (array_key_exists($plugin_file, $active_plugins_data)) {
                    ++$active_plugins_data[$plugin_file]['count'];
                }
            }

            /*  Sort plugins based on the number of active installations (descending order). */
            uasort(
                $active_plugins_data,
                function ($a, $b) {
                    return $b['count'] - $a['count'];
                }
            );

            /*  Display the top 5 most used plugins. */
            $counter = 0;
            foreach ($active_plugins_data as $plugin_data) {
            ?>
                <tr>
                    <td><?php echo esc_html($plugin_data['name']); ?></td>
                    <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($plugin_data['version']); ?></td>
                </tr>
            <?php } ?>

        </table>
    </div>
    <?php
    /*  Webserver Configuration. */
    ?>
    <div class="mb-20 mt-20">
        <button id="formntr-show-server-info-button" class="info-button">
            Server <span class="dashicons dashicons-arrow-down"></span>
        </button>
    </div>

    <div id="formntr-server-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <p class="text-dark">
            <b>The options shown below relate to your server setup. If changes are required, you may need your web host's
                assistance.</b>
        </p>
        <?php
        /*  Add Server information. */
        ?>
        <table>
            <tr>
                <td>Server Architecture</td>
                <td class="fw-600"><?php echo esc_html(php_uname('s')); ?></td>
            </tr>

            <tr>
                <td>Web Server</td>
                <td class="fw-600"><?php
                                    $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '';
                                    echo esc_html(wp_kses(esc_attr($server_software), 'post'));
                                    ?></td>
            </tr>

            <tr>
                <td>PHP Version</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html(phpversion()); ?></td>
            </tr>

            <tr>
                <td>PHP SAPI</td>
                <td class="fw-600"><?php echo esc_html(php_sapi_name()); ?></td>
            </tr>

            <tr>
                <td>PHP Max Input Variables</td>
                <td class="fw-600"><?php echo esc_html(ini_get('max_input_vars')); ?></td>
            </tr>

            <tr>
                <td>PHP Time Limit</td>
                <td class="fw-600"><?php echo esc_html(ini_get('max_execution_time')); ?> seconds</td>
            </tr>

            <tr>
                <td>PHP Memory Limit</td>
                <td class="fw-600"><?php echo esc_html(ini_get('memory_limit')); ?></td>
            </tr>

            <tr>
                <td>Max Input Time</td>
                <td class="fw-600"><?php echo esc_html(ini_get('max_input_time')); ?> seconds</td>
            </tr>

            <tr>
                <td>Upload Max Filesize</td>
                <td class="fw-600"><?php echo esc_html(ini_get('upload_max_filesize')); ?></td>
            </tr>

            <tr>
                <td>PHP Post Max Size</td>
                <td class="fw-600"><?php echo esc_html(ini_get('post_max_size')); ?></td>
            </tr>

            <tr>
                <td>cURL Version</td>
                <td class="fw-600"><?php echo esc_html(curl_version()['version']); ?></td>
            </tr>

            <tr>
                <td>Is SUHOSIN Installed?</td>
                <td class="fw-600"><?php echo esc_html(extension_loaded('suhosin') ? 'Yes' : 'No'); ?></td>
            </tr>

            <tr>
                <td>Is the Imagick Library Available?</td>
                <td class="fw-600"><?php echo esc_html(extension_loaded('imagick') ? 'Yes' : 'No'); ?></td>
            </tr>

            <tr>
                <td>Are Pretty Permalinks Supported?</td>
                <td class="fw-600"><?php echo esc_html(get_option('permalink_structure') ? 'Yes' : 'No'); ?></td>
            </tr>
            <?php
            global $wp_filesystem;

            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            $htaccess_path = ABSPATH . '.htaccess';

            $is_writable = $wp_filesystem->exists($htaccess_path) && $wp_filesystem->is_writable($htaccess_path);
            ?>
            <tr>
                <td>.htaccess Rules</td>
                <td class="fw-600"><?php echo esc_html($is_writable ? 'Writable' : 'Non Writable'); ?></td>
            </tr>

            <tr>
                <td>Current Time</td>
                <td class="fw-600"><?php echo esc_html(current_time('mysql')); ?></td>
            </tr>

            <tr>
                <td>Current UTC Time</td>
                <td class="fw-600"><?php echo esc_html(current_time('mysql', true)); ?></td>
            </tr>

            <tr>
                <td>Current Server Time</td>
                <td class="fw-600"><?php echo esc_html(gmdate('Y-m-d H:i:s')); ?></td>
            </tr>
        </table>
    </div>
    <?php /*  Database Configuration. */ ?>
    <div class="mb-20 mt-20">
        <button id="formntr-show-database-info-button" class="info-button">
            Database <span class="dashicons dashicons-arrow-down"></span>
        </button>
    </div>

    <div id="formntr-database-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <table>

            <?php
            $database_extension = 'mysqli';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $database_server_version = $wpdb->get_var('SELECT VERSION() as version');
            $database_client_version = $wpdb->db_version();
            $database_username = DB_USER;
            $database_host = DB_HOST;
            $database_name = DB_NAME;
            $table_prefix = $wpdb->prefix;
            $database_charset = $wpdb->charset;
            $database_collation = $wpdb->collate;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $max_allowed_packet_size = $wpdb->get_var("SHOW VARIABLES LIKE 'max_allowed_packet'");
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $max_connections_number = $wpdb->get_var("SHOW VARIABLES LIKE 'max_connections'");

            ?>

            <tr>
                <td>Extension</td>
                <td class="fw-600"><?php echo esc_html($database_extension); ?></td>
            </tr>

            <tr>
                <td>Server Version</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($database_server_version); ?></td>
            </tr>

            <tr>
                <td>Client Version</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($database_client_version); ?></td>
            </tr>

            <tr>
                <td>Database Username</td>
                <td class="fw-600"><?php echo esc_html($database_username); ?></td>
            </tr>

            <tr>
                <td>Database Host</td>
                <td class="fw-600 common-badge-table info-name-yellow"><?php echo esc_html($database_host); ?></td>
            </tr>

            <tr>
                <td>Database Name</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html($database_name); ?></td>
            </tr>

            <tr>
                <td>Table Prefix</td>
                <td class="fw-600"><?php echo esc_html($table_prefix); ?></td>
            </tr>

            <tr>
                <td>Database Charset</td>
                <td class="fw-600"><?php echo esc_html($database_charset); ?></td>
            </tr>

            <tr>
                <td>Database Collation</td>
                <td class="fw-600"><?php echo esc_html($database_collation); ?></td>
            </tr>

            <tr>
                <td>Max Allowed Packet Size</td>
                <td class="fw-600"><?php echo esc_html($max_allowed_packet_size); ?></td>
            </tr>

            <tr>
                <td>Max Connections Number</td>
                <td class="fw-600"><?php echo esc_html($max_connections_number); ?></td>
            </tr>
        </table>
    </div>
    <?php
    /*  WordPress constants. */
    ?>
    <div class="mb-20 mt-20">
        <button id="formntr-show-wrcons-info-button" class="info-button">
            WordPress Constants <span class="dashicons dashicons-arrow-down"></span>
        </button>
    </div>

    <div id="formntr-wrcons-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <table>
            <?php
            /*  Add WordPress Constants information. */
            ?>

            <tr>
                <td>ABSPATH</td>
                <td class="fw-600"><?php echo esc_html(ABSPATH); ?></td>
            </tr>

            <tr>
                <td>WP_HOME</td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html(home_url()); ?></td>
            </tr>

            <tr>
                <td>WP_SITEURL</td>
                <td class="fw-600"><?php echo esc_html(site_url()); ?></td>
            </tr>

            <tr>
                <td>WP_CONTENT_DIR</td>
                <td class="fw-600"><?php echo esc_html(WP_CONTENT_DIR); ?></td>
            </tr>

            <tr>
                <td>WP_PLUGIN_DIR</td>
                <td class="fw-600"><?php echo esc_html(WP_PLUGIN_DIR); ?></td>
            </tr>

            <tr>
                <td>WP_MEMORY_LIMIT</td>
                <td class="fw-600"><?php echo esc_html(WP_MEMORY_LIMIT); ?></td>
            </tr>

            <tr>
                <td>WP_MAX_MEMORY_LIMIT</td>
                <td class="fw-600"><?php echo esc_html(WP_MAX_MEMORY_LIMIT); ?></td>
            </tr>

            <tr>
                <td>WP_DEBUG</td>
                <td class="fw-600"><?php echo (defined('WP_DEBUG') && WP_DEBUG ? 'Yes' : 'No'); ?></td>
            </tr>

            <tr>
                <td>WP_DEBUG_DISPLAY</td>
                <td class="fw-600">
                    <?php echo (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'Yes' : 'No'); ?></td>
            </tr>

            <tr>
                <td>SCRIPT_DEBUG</td>
                <td class="fw-600"><?php echo (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Yes' : 'No'); ?></td>
            </tr>

            <tr>
                <td>WP_CACHE</td>
                <td class="fw-600"><?php echo (defined('WP_CACHE') && WP_CACHE ? 'Yes' : 'No'); ?></td>
            </tr>

            <tr>
                <td>CONCATENATE_SCRIPTS</td>
                <td class="fw-600">
                    <?php echo (defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS ? 'Yes' : 'No'); ?>
                </td>
            </tr>

            <tr>
                <td>COMPRESS_SCRIPTS</td>
                <td class="fw-600">
                    <?php echo (defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS ? 'Yes' : 'No'); ?></td>
            </tr>

            <tr>
                <td>COMPRESS_CSS</td>
                <td class="fw-600"><?php echo (defined('COMPRESS_CSS') && COMPRESS_CSS ? 'Yes' : 'No'); ?></td>
            </tr>

            <?php
            /*  Manually define the environment type (example values: 'development', 'staging', 'production'). */
            $environment_type = 'development';

            /*  Display the environment type. */
            ?>

            <tr>
                <td>WP_ENVIRONMENT_TYPE</td>
                <td class="fw-600"><?php echo esc_html($environment_type); ?></td>
            </tr>

            <tr>
                <td>WP_DEVELOPMENT_MODE</td>
                <td class="fw-600">
                    <?php echo (defined('WP_DEVELOPMENT_MODE') && WP_DEVELOPMENT_MODE ? 'Yes' : 'No'); ?>
                </td>
            </tr>

            <tr>
                <td>DB_CHARSET</td>
                <td class="fw-600"><?php echo esc_html(DB_CHARSET); ?></td>
            </tr>

            <tr>
                <td>DB_COLLATE</td>
                <td class="fw-600"><?php echo esc_html(DB_COLLATE); ?></td>
            </tr>
        </table>
    </div>
    <?php
    /*  Filesystem Permission. */
    ?>
    <div class="mb-20 mt-20">
        <button id="formntr-show-ftps-info-button" class="info-button">
            Filesystem Permissions <span class="dashicons dashicons-arrow-down"></span>
        </button>
    </div>

    <div id="formntr-ftps-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        <p class="text-dark">
            <b>Shows whether WordPress is able to write to the directories it needs access to.</b>
        </p>

        <table>
            <?php
            /*  Filesystem Permission information. */
            global $wp_filesystem;

            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            /*  Define the paths */
            $fonts_dir = WP_CONTENT_DIR . '/fonts';
            if (function_exists('wp_get_font_dir')) {
                $fonts_dir = wp_get_font_dir()['basedir'];// phpcs:ignore PluginCheck.CodeAnalysis.Constants.NewConstant, WordPress.WP.AlternativeFunctions
            }

            $paths = array(
                'The main WordPress directory' => ABSPATH,
                'The wp-content directory' => WP_CONTENT_DIR,
                'The uploads directory' => wp_upload_dir()['basedir'],
                'The plugins directory' => WP_PLUGIN_DIR,
                'The themes directory' => get_theme_root(),
                'The fonts directory' => $fonts_dir,
                'The must-use plugins directory' => WPMU_PLUGIN_DIR,
            );

            /*  Loop through and check writability using WP_Filesystem */
            foreach ($paths as $label => $path) {
                $writable = $wp_filesystem->exists($path) && $wp_filesystem->is_writable($path);
                $permission_value = '';
                if ($writable != '') {
                    $permission_value = esc_html($writable ? 'Writable' : 'Not Writable');
                } else {
                    $permission_value = __('Does not exist', 'gsheetconnector-forminator');
                }


            ?>
                <tr>
                    <td><?php echo esc_html($label); ?></td>
                    <td><?php echo esc_html($path); ?></td>
                    <td class="fw-600"><?php echo esc_html($permission_value); ?></td>
                </tr>
            <?php
            }
            ?>
        </table>
    </div>


    <?php $debug_log_file = WP_CONTENT_DIR . '/debug.log'; ?>
    <?php if (file_exists($debug_log_file)) { ?>

        <div class="system-error shadow-box mt-40 p-30">

            <!-- ERROR LOG SECTION -->
            <div class="system-error">
                <div class="error-container">

                    <div class="error-log-head flex-wrap gap-20">

                        <div class="heading mt-0 mb-0">
                            <?php esc_html_e('Debug Log', 'gsheetconnector-forminator'); ?>

                        </div>

                        <?php if (file_exists($debug_log_file) && filesize($debug_log_file) > 0) { ?>
                            <div class="errorlog-button-list">
                                <div class="loading-sign-setting"></div>
                                <button type="button"
                                    class="button btn-logs formntr-clear-debug-logs">
                                    <?php esc_html_e('Clear Logs', 'gsheetconnector-forminator'); ?>
                                </button>

                                <button type="button"
                                    class="button button-primary"
                                    id="formntr-download-csv">
                                    <?php esc_html_e('Download CSV', 'gsheetconnector-forminator'); ?>
                                </button>

                                <button type="button"
                                    class="button btn-logs"
                                    id="formntr-copy-logs">
                                    <?php esc_html_e('Copy Logs', 'gsheetconnector-forminator'); ?>
                                </button>

                                <div class="gsc-copy-msg d-none"></div>

                            </div>
                        <?php } ?>

                    </div>

                </div>

                <div class="clear-content-logs-msg-forminator-pro"></div>

                <input type="hidden" name="frmntr-gs-ajax-nonce" id="frmntr-gs-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('frmntr-gs-ajax-nonce')); ?>" />

                <div class="copy-message" style="display:none;">
                    <?php esc_html_e('Copied successfully', 'gsheetconnector-forminator'); ?>
                </div>
                <?php


                $log_lines = file($debug_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                // Reverse and limit to last 100
                $log_lines = array_slice(array_reverse($log_lines), 0, 100);
                ?>
                <div style="max-height:500px; overflow:auto;">
                    <table class="widefat striped mt-30">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Date', 'gsheetconnector-forminator'); ?></th>
                                <th><?php echo esc_html__('Type', 'gsheetconnector-forminator'); ?></th>
                                <th><?php echo esc_html__('Message', 'gsheetconnector-forminator'); ?></th>
                                <th><?php echo esc_html__('File', 'gsheetconnector-forminator'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $has_logs = false;

                            foreach ($log_lines as $line) {
                                if (preg_match('/\[(.*?)\]\s(.*?):\s(.*)/', $line, $matches)) {

                                    $has_logs = true;

                                    $date    = str_replace(' UTC', '', $matches[1]);

                                    /*  Convert to a timestamp first */
                                    $timestamp = strtotime($date);

                                    /*  Format using the site's configured date and time format settings */
                                    $formatted_date = date_i18n(
                                        get_option('date_format') . ' ' . get_option('time_format'),
                                        $timestamp
                                    );


                                    $type    = str_replace('PHP ', '', $matches[2]);
                                    $message = $matches[3];

                                    $file = '-';
                                    if (preg_match('/in (.*?) on line/', $message, $file_match)) {
                                        $file = $file_match[1];
                                    }
                            ?>
                                    <tr>
                                        <td><?php echo esc_html($formatted_date); ?></td>
                                        <td><?php echo esc_html($type); ?></td>
                                        <td><?php echo esc_html($message); ?></td>
                                        <td><?php echo esc_html($file); ?></td>
                                    </tr>
                                <?php
                                }
                            }

                            if (! $has_logs) : ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <?php echo esc_html__('No debug logs found', 'gsheetconnector-forminator'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
</div>