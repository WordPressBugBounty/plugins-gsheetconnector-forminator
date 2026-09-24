<?php

/**
 * Plugin Name: GSheetConnector for Forminator Forms
 * Plugin URI: https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro
 * Description: Send your Forminator Forms data to your Google Sheets spreadsheet.
 * Author: GSheetConnector
 * Author URI: https://www.gsheetconnector.com/
 * Version: 2.0.0
 * Text Domain: gsheetconnector-forminator
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * Requires: forminator
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (GS_FORMNTR_Init::gscfrmn_is_pugin_active('GS_FORMNTR_Init_PRO')) {
    return;
}

define('GS_FORMNTR_VERSION', '2.0.0');
define('GS_FORMNTR_DB_VERSION', '2.0.0');
define('GS_FORMNTR_ROOT', dirname(__FILE__));
define('GS_FORMNTR_URL', plugins_url('/', __FILE__));
define('GS_FORMNTR_BASE_FILE', basename(dirname(__FILE__)) . '/gsheetconnector-forminator.php');
define('GS_FORMNTR_BASE_NAME', plugin_basename(__FILE__));
define('GS_FORMNTR_PATH', plugin_dir_path(__FILE__)); //use for include files to other files
define('GS_FORMNTR_CURRENT_THEME', get_stylesheet_directory());
define('GS_FORMNTR_API_URL', 'https://oauth.gsheetconnector.com/api-cred.php');
define('GS_FORMNTR_AUTH_REDIRECT_URI', admin_url('admin.php?page=formntr-gsheet-config&tab=integration'));
define('GS_FORMNTR_AUTH_PLUGIN_NAME', 'frmingsheetconnector');
define('GS_FORMNTR_AUTH_URL', 'https://oauth.gsheetconnector.com/index.php');
define('GS_FORMNTR_TEXTDOMAIN', 'gsheetconnector-forminator');

/*freemius*/
if (function_exists('is_plugin_active') && is_plugin_active('gsheetconnector-forminator/gsheetconnector-forminator.php')) {
    if (!function_exists('gfff_fs')) {

        // Create a helper function for easy SDK access.

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Freemius SDK boilerplate; this is the standard Freemius-generated accessor naming convention, not part of this plugin's own public API.
        function gfff_fs()
        {
            global $gfff_fs;

            if (!isset($gfff_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/lib/vendor/freemius/start.php';

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Freemius SDK boilerplate global paired with the gfff_fs() accessor; standard Freemius-generated naming convention.
                $gfff_fs = fs_dynamic_init(array(
                    'id' => '13370',
                    'slug' => 'gsheetconnector-for-forminator-forms',
                    'type' => 'plugin',
                    'public_key' => 'pk_bc410aed4ec4f870a557038234458',
                    'is_premium' => false,
                    'has_addons' => false,
                    'has_paid_plans' => false,
                    'is_org_compliant' => true,
                    'menu' => array(
                        'slug' => 'gsheetconnector-for-forminator-forms',
                        'first-path' => (!is_multisite() ? 'admin.php?page=formntr-gsheet-config' : 'plugins.php'),
                        'account' => false,
                        'support' => false,
                    ),
                ));
            }

            return $gfff_fs;
        }

        // Init Freemius.
        gfff_fs();

        // Signal that SDK was initiated.
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Freemius SDK boilerplate action hook, standard Freemius-generated naming convention; must not be renamed as it's a public extension point.
        do_action('gfff_fs_loaded');
    }

    /*freemius */
    /* Customizing the Opt Message Freemius  */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    function gs_formntr_custom_connect_message_on_update( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
        $message,
        $user_first_name,
        $plugin_title,
        $user_login,
        $site_link,
        $freemius_link
    ) {
        return sprintf(
            // translators: %1$s is the user's first name.
            __('Hey %1$s', 'gsheetconnector-forminator') . ',<br>' .
                // translators: %2$s is the plugin title, %5$s is the Freemius link.
                __('Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'gsheetconnector-forminator'),
            $user_first_name,
            '<b>' . $plugin_title . '</b>',
            '<b>' . $user_login . '</b>',
            $site_link,
            $freemius_link
        );
    }

    gfff_fs()->add_filter('connect_message_on_update', 'gs_formntr_custom_connect_message_on_update', 10, 6);
    /* End Customizing the Opt Message Freemius  */
}
/**/



// Include Utility Classes
if (!class_exists('GS_FORMNTR_Free_Utility')) {
    include(GS_FORMNTR_ROOT . '/includes/class-gs-formntr-utility.php');
}

// Feed storage abstraction (custom tables + legacy postmeta bridge)
if (!class_exists('GS_FORMNTR_Feed_Store')) {
    include(GS_FORMNTR_ROOT . '/includes/class-gs-formntr-feed-store.php');
}

//Include Library Files
include_once(GS_FORMNTR_ROOT . '/lib/google-sheets.php');
if (!class_exists('GS_FORMNTR_Service')) {
    include_once(GS_FORMNTR_PATH . 'includes/class-gs-formntr-services.php');
}

class GS_FORMNTR_Init
{

    /**
     *  Set things up.
     *  @since 1.0.15
     */

    public function __construct()
    {
        //run on activation of plugin
        register_activation_hook(__FILE__, array($this, 'gs_formntr_activate'));

        //run on deactivation of plugin
        register_deactivation_hook(__FILE__, array($this, 'gs_formntr_deactivate'));

        //run on uninstall
        register_uninstall_hook(__FILE__, array('GS_FORMNTR_Init', 'gs_formntr_uninstall'));

        // validate is Forminator Form plugin exist
        add_action('admin_init', array($this, 'validate_parent_plugin_exists'));

        //run_on_upgrade
        add_action('admin_init', array($this, 'run_on_upgrade'));

        // redirect to the dashboard right after the plugin is activated
        add_action('admin_init', array($this, 'gs_formntr_activation_redirect'));

        // register admin menu under "Contact" > "Integration"
        add_action('admin_menu', array($this, 'register_gs_menu_pages'), 70);

        // load the js and css files
        add_action('init', array($this, 'load_css_and_js_files'));

        // load the classes
        add_action('init', array($this, 'load_all_classes'));

        // Add custom link for our plugin
        add_filter('plugin_action_links_' . GS_FORMNTR_BASE_NAME, array($this, 'formntr_gs_connector_plugin_action_links'));

        // Display widget to dashboard
        add_action('wp_dashboard_setup', array($this, 'add_formntr_gs_connector_summary_widget'));

        /*  Add custom link for our plugin */
        add_filter('plugin_action_links_' . GS_FORMNTR_BASE_NAME,  array($this, 'forminator_gs_connector_pro_plugin_action_links'));

        /** For using Row Meta */
        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);
    }

    /**
     * Add function to check plugins is Activate or not
     * @param string $class of plugins main class .
     * @return true/false    * 
     * @since 1.0.15
     */
    public static function gscfrmn_is_pugin_active($class)
    {
        if (class_exists($class)) {
            return true;
        }
        return false;
    }

    /**
     * Do things on plugin activation
     * @since 1.0.15
     */

    public function gs_formntr_activate($network_wide)
    {
        global $wpdb;
        $this->run_on_activation();
        if (function_exists('is_multisite') && is_multisite()) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ($network_wide) {
                // Get all site IDs in the network
                $sites = get_sites(array('fields' => 'ids'));
                foreach ($sites as $blog_id) {
                    switch_to_blog($blog_id);
                    $this->run_for_site();
                    restore_current_blog();
                }
                return;
            }
        }
        $this->run_for_site();

        // flag a redirect to the dashboard on the next admin page load
        // (skip for network-wide activation - there's no single site to land on)
        if (!$network_wide) {
            set_transient('gs_formntr_activation_redirect', true, 30);
        }
    }

    /**
     * Redirect to the plugin dashboard once, right after activation.
     * @since 2.0.0
     */

    public function gs_formntr_activation_redirect()
    {
        if (!get_transient('gs_formntr_activation_redirect')) {
            return;
        }

        delete_transient('gs_formntr_activation_redirect');

        // don't redirect on bulk plugin activation or during AJAX/cron
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['activate-multi']) || wp_doing_ajax() || (defined('DOING_CRON') && DOING_CRON)) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        wp_safe_redirect(admin_url('admin.php?page=formntr-gsheet-config&tab=dashboard'));
        exit;
    }

    /**
     * Deactivates the plugin.
     *
     * @since 1.0.15
     */

    public function gs_formntr_deactivate($network_wide) {}
    /**
     *  Runs on plugin uninstall.
     *  a static class method or function can be used in an uninstall hook
     *
     *  @since 1.0.15
     */
    public static function gs_formntr_uninstall()
    {
        global $wpdb;
        GS_FORMNTR_Init::run_on_uninstall();
        if (function_exists('is_multisite') && is_multisite()) {
            //Get all blog ids; foreach of them call the uninstall procedure

            // $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
            $blog_ids = get_sites(array('fields' => 'ids'));

            //Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                GS_FORMNTR_Init::delete_for_site();
                restore_current_blog();
            }
            return;
        }
        GS_FORMNTR_Init::delete_for_site();
    }

    /**
     * Validate parent Plugin FORMNTR exist and activated
     * @access public
     * @since 1.0.15
     */
    public function validate_parent_plugin_exists()
    {
        $plugin = plugin_basename(__FILE__);
        if ((!is_plugin_active('forminator/forminator.php')) || (!file_exists(plugin_dir_path(__DIR__) . 'forminator/forminator.php'))) {
            add_action('admin_notices', array($this, 'formntr_missing_notice'));
            add_action('network_admin_notices', array($this, 'formntr_missing_notice'));
            deactivate_plugins($plugin);
            if (isset($_GET['activate']) && check_admin_referer('activate-plugin_' . $plugin)) {
                unset($_GET['activate']);
            }
        }
    }

    /**
     * If FORMNTR plugin is not installed or activated then throw the error
     *
     * @access public
     * @return mixed error_message, an array containing the error message
     *
     * @since 1.0.15 
     */

    public function formntr_missing_notice()
    {
        $plugin_error = GS_FORMNTR_Free_Utility::instance()->admin_notice(array(
            'type' => 'error',
            'message' => __('Forminator Google Sheet Connector Add-on requires Forminator plugin to be installed and activated.', 'gsheetconnector-forminator')
        ));
        echo wp_kses_post($plugin_error);
    }

    /**
     * Create/Register menu items for the plugin.
     * @since 1.0.15
     */
    public function register_gs_menu_pages()
    {
        $current_role = GS_FORMNTR_Free_Utility::instance()->get_current_user_role();
        if ($current_role === "administrator" || current_user_can('manage_shop_settings')) {
            add_submenu_page('forminator', __('Google Sheet', 'gsheetconnector-forminator'), __('Google Sheet', 'gsheetconnector-forminator'), $current_role, 'formntr-gsheet-config', array($this, 'google_sheet_configuration'));
        }
    }

    /**
     * Google Sheets page action.
     * This method is called when the menu item "Google Sheets" is clicked.
     * @since 1.0.15
     */
    public function google_sheet_configuration()
    {
        include(GS_FORMNTR_PATH . "includes/pages/google-sheet-settings.php");
    }

    /**
     * Load all the classes - as part of init action hook
     * @since 1.0.15
     */
    public function load_all_classes()
    {
        if (!class_exists('GS_Formntr_Processes')) {
            include(GS_FORMNTR_PATH . 'includes/class-gs-formntr-processes.php');
        }
        if (!class_exists('Formntr_gs_Connector_Adds')) {
            include(GS_FORMNTR_PATH . 'includes/class-gs-formntr-adds.php');
        }
        if (!class_exists('GS_Formntr_Extensions')) {
            include(GS_FORMNTR_PATH . 'includes/pages/extensions/gs-Formntr-extension-service.php');
        }
        if (!class_exists('GSCFORMNTR_Free_Error_Logs')) {
            include(GS_FORMNTR_PATH . 'includes/class-gs-formntr-error-logs.php');
        }
    }

    /**
     * Loads required CSS and JS files in the admin area.
     *
     * @access public
     * @since 1.0.15
     */
    public function load_css_and_js_files()
    {
        add_action('admin_print_styles', array($this, 'add_css_files'));
        add_action('admin_print_scripts', array($this, 'add_js_files'));
    }

    /**
     * Enqueue CSS files
     * @since 1.0.15
     */
    public function add_css_files()
    {
        if (
            is_admin()
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            && isset($_GET['page'])
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            && sanitize_text_field(wp_unslash($_GET['page'])) === 'formntr-gsheet-config'
        ) {
            $gs_formntr_connector_css = GS_FORMNTR_ROOT . '/assets/css/gs-formntr-connector.css';
            wp_enqueue_style(
                'gs-formntr-connector-css',
                GS_FORMNTR_URL . 'assets/css/gs-formntr-connector.css',
                array(),
                file_exists($gs_formntr_connector_css) ? filemtime($gs_formntr_connector_css) : GS_FORMNTR_VERSION,
                'all'
            );

            /** New CSS added */
            wp_enqueue_style(
                'gs-formntr-extra-style',
                GS_FORMNTR_URL . 'assets/css/extra-style.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );

            wp_enqueue_style(
                'gs-formntr-footer',
                GS_FORMNTR_URL . 'assets/css/footer.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );

            wp_enqueue_style(
                'gs-formntr-global',
                GS_FORMNTR_URL . 'assets/css/global.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );

            wp_enqueue_style(
                'gs-formntr-header',
                GS_FORMNTR_URL . 'assets/css/header.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );

            wp_enqueue_style(
                'gs-formntr-pro-feature',
                GS_FORMNTR_URL . 'assets/css/pro-feature.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );

            wp_enqueue_style(
                'gs-formntr-responsive',
                GS_FORMNTR_URL . 'assets/css/responsive.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );

            wp_enqueue_style(
                'gs-formntr-fontawesome',
                GS_FORMNTR_URL . 'assets/css/fontawesome.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );

            wp_enqueue_style(
                'gs-formntr-fontawesome-min',
                GS_FORMNTR_URL . 'assets/css/font-awesome.min.css',
                array(),
                GS_FORMNTR_VERSION,
                'all'
            );
        }
    }

    /**
     * Enqueue JS files
     * @since 1.0.15
     */

    public function add_js_files()
    {
        if (
            is_admin()
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            && (isset($_GET['page'])
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                && ($_GET['page'] == 'formntr-gsheet-config'))
        ) {
            wp_enqueue_script('jquery-ui-sortable');
            $gs_formntr_connector_js = GS_FORMNTR_ROOT . '/assets/js/gs-formntr-connector.js';
            wp_enqueue_script(
                'gs-formntr-connector',
                GS_FORMNTR_URL . 'assets/js/gs-formntr-connector.js',
                array('jquery'),
                file_exists($gs_formntr_connector_js) ? filemtime($gs_formntr_connector_js) : GS_FORMNTR_VERSION,
                true
            );
        }
        if (is_admin()) {
            wp_enqueue_script(
                'formntr-gs-connector-adds',
                GS_FORMNTR_URL . 'assets/js/formntr-gs-connector-adds.js',
                array(),
                GS_FORMNTR_VERSION,
                true
            );
        }
        if (is_admin()) {
            wp_enqueue_script(
                'gs-connector-extensions',
                GS_FORMNTR_URL . 'assets/js/gs-connector-extensions.js',
                array(),
                GS_FORMNTR_VERSION,
                true
            );
        }
        if (is_admin()) {
            //  New file enqueue
            wp_enqueue_script(
                'gs-formntr-systeminfo',
                GS_FORMNTR_URL . 'assets/js/gs-formntr-systeminfo.js',
                array('jquery'),
                GS_FORMNTR_VERSION,
                true
            );
        }
    }

    /**
     * called on upgrade. 
     * checks the current version and applies the necessary upgrades from that version onwards
     * @since 1.0.15
     */

    public function run_on_upgrade()
    {
        $plugin_options = get_site_option('gs_formntr_info');
        if (is_array($plugin_options) && isset($plugin_options['version']) && $plugin_options['version'] == '1.0.14') {
            $this->upgrade_database_18();
        }

        // update the version value
        $google_sheet_info = array(
            'version' => GS_FORMNTR_VERSION,
            'db_version' => GS_FORMNTR_DB_VERSION
        );

        // check if debug log file exists or not
        $logFilePathToDelete = GS_FORMNTR_PATH . "logs/log.txt";
        // Check if the log file exists before attempting to delete
        if (file_exists($logFilePathToDelete)) {
            wp_delete_file($logFilePathToDelete);
        }

        update_site_option('gs_formntr_info', $google_sheet_info);

        // Fetch and save the API credentails.
        GS_FORMNTR_Free_Utility::instance()->save_api_credentials();

        $this->create_debug_log_table();

        // Custom feed tables: run dbDelta only when the stored DB version is
        // behind (or missing), not on every admin request.
        $stored_db_version = is_array($plugin_options) && isset($plugin_options['db_version'])
            ? $plugin_options['db_version']
            : '0';
        if (version_compare($stored_db_version, GS_FORMNTR_DB_VERSION, '<')) {
            GS_FORMNTR_Feed_Store::install();
        }

        // One-time back-fill from postmeta into the custom tables. Runs to
        // completion here (re-runs on the next admin_init until finished).
        // Self-guards once done.
        wp_clear_scheduled_hook('gs_formntr_migrate_feeds_cron');
        GS_FORMNTR_Feed_Store::migrate_all();
    }


    public function create_debug_log_table()
    {

        global $wpdb;

        $table = $wpdb->prefix . 'gsformntr_error_logs';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            error_id VARCHAR(191) NOT NULL,
            code INT NOT NULL,
            message TEXT NOT NULL,
            details LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY error_id (error_id),
            KEY code (code)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Upgrades database to version 18 for single and multisite installations.
     *
     * @access public
     * @since 1.0.15
     */

    public function upgrade_database_18()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            // $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
            $blog_ids = get_sites(array('fields' => 'ids'));
            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                $this->upgrade_helper_18();
                restore_current_blog();
            }
        }
        $this->upgrade_helper_18();
    }

    /**
     * Saves API credentials during database upgrade to version 18.
     *
     * @access public
     * @since 1.0.15
     */

    public function upgrade_helper_18()
    {
        // Fetch and save the API credentails.
        GS_FORMNTR_Free_Utility::instance()->save_api_credentials();
    }

    /**
     * Called on activation.
     * Creates the site_options (required for all the sites in a multi-site setup)
     * If the current version doesn't match the new version, runs the upgrade
     * @since 1.0.15
     */

    private function run_on_activation()
    {
        $plugin_options = get_site_option('gs_formntr_info');
        if (false === $plugin_options) {
            $google_sheet_info = array(
                'version' => GS_FORMNTR_VERSION,
                'db_version' => GS_FORMNTR_DB_VERSION
            );
            update_site_option('gs_formntr_info', $google_sheet_info);
        } else if (GS_FORMNTR_DB_VERSION != $plugin_options['version']) {
            $this->run_on_upgrade();
        }
        if (!wp_next_scheduled('google_sheet_check_expiration')) {
            wp_schedule_event(time(), 'google_sheet_weekly', 'google_sheet_check_expiration');
        }
        // Fetch and save the API credentails.
        GS_FORMNTR_Free_Utility::instance()->save_api_credentials();

        /**Create error log table  */
        $this->create_debug_log_table();

        /** Custom feed tables + one-time back-fill from postmeta */
        wp_clear_scheduled_hook('gs_formntr_migrate_feeds_cron');
        GS_FORMNTR_Feed_Store::install();
        GS_FORMNTR_Feed_Store::migrate_all();
    }

    /**
     * Called on activation.
     * Creates the options and DB (required by per site)
     * @since 1.0.15
     */

    private function run_for_site()
    {
        if (!get_option('gs_formntr_access_code')) {
            update_option('gs_formntr_access_code', '');
        }
        if (!get_option('gs_formntr_verify')) {
            update_option('gs_formntr_verify', 'invalid');
        }
        if (!get_option('gs_formntr_token')) {
            update_option('gs_formntr_token', '');
        }
        if (!get_option('gs_formntr_feeds')) {
            update_option('gs_formntr_feeds', '');
        }
        if (!get_option('gs_formntr_sheetId')) {
            update_option('gs_formntr_sheetId', '');
        }
        if (!get_option('gs_formntr_settings')) {
            update_option('gs_formntr_settings', '');
        }
        if (!get_option('gs_formntr_checkbox_settings')) {
            update_option('gs_formntr_checkbox_settings', array());
        }
        if (!get_option('gs_formntr_tab_roles_setting')) {
            update_option("gs_formntr_tab_roles_setting", array());
        }
    }

    /**
     * Called on uninstall - deletes site specific options
     *
     * @since 1.0.15
     */

    private static function delete_for_site()
    {
        try {
            // Only remove feed data if the site owner opted in via
            // Settings > General > "Remove Data on Uninstall".
            if ((int) get_option('gs_frmnt_unistall_plugin_settings') === 1) {
                if (!class_exists('GS_FORMNTR_Feed_Store')) {
                    include_once GS_FORMNTR_ROOT . '/includes/class-gs-formntr-feed-store.php';
                }
                GS_FORMNTR_Feed_Store::uninstall();
                delete_option('gs_formntr_pro_feeds_migrated');
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log('Something went wrong: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Called on uninstall - deletes site_options
     *
     * @since 1.0.15
     */

    private static function run_on_uninstall()
    {
        if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
            exit();
        delete_site_option('gs_formntr_info');
    }

    /**
     * Add custom link for the plugin beside activate/deactivate links
     * @param array $links Array of links to display below our plugin listing.
     * @return array Amended array of links.    * 
     * @since 1.0.15
     */

    public function formntr_gs_connector_plugin_action_links($links)
    {
        // We shouldn't encourage editing our plugin directly.
        unset($links['edit']);
        // Add our custom links to the returned array value.[16102021]
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=formntr-gsheet-config&tab=dashboard') . '">' . __('Settings', 'gsheetconnector-forminator') . '</a>'
        ), $links);
    }

    /**
     * Adds a widget to the WordPress dashboard.
     *
     * @since 1.0.15
     */

    public function add_formntr_gs_connector_summary_widget()
    {
        $title = "<img style='width:30px;margin-right: 10px;' src='" . GS_FORMNTR_URL . "assets/img/forminator-gsc.svg'><span>" . __('GSheetConnector for Forminator Forms', 'gsheetconnector-forminator') . "</span>";
        wp_add_dashboard_widget('formntr_gs_dashboard', $title, array($this, 'formntr_gs_connector_summary_dashboard'));
    }

    /**
     * Displays widget contents.
     *
     * @since 1.0.15
     */
    public function formntr_gs_connector_summary_dashboard()
    {
        include_once(GS_FORMNTR_PATH . '/includes/pages/gs-formntr-dashboard-widget.php');
    }


    public function forminator_gs_connector_pro_plugin_action_links($links)
    {
        /* Define the text for the "Upgrade to Pro" link */
        $go_pro_text = esc_html__('Upgrade to Pro', 'gsheetconnector-forminator');

        /*  Check if the Pro version of the plugin is installed and activated */
        if (is_plugin_active('gsheetconnector-forminator-pro/gsheetconnector-forminator-pro.php')) {
            /*  If Pro version is active, return the links without adding the "Upgrade to Pro" link */
            return $links;
        }

        /*  Add the action link to the plugin page with green color styling */
        $links['go_pro'] = sprintf(
            '<a href="%s" target="_blank" class="gsheetconnector-pro-link" style="color: green;">%s</a>',
            esc_url('https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro'),
            $go_pro_text
        );

        return $links;
    }

    /**
* Plugin row meta.
*
* Adds row meta links to the plugin list table
*/
public function plugin_row_meta($plugin_meta, $plugin_file)
{
  if (GS_FORMNTR_BASE_NAME === $plugin_file) {
   $row_meta = [
    'docs' => '<a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector" target="_blank" aria-label="' . esc_attr(esc_html__('View Documentation', 'gsheetconnector-forminator')) . '" target="_blank">' . esc_html__('Docs', 'gsheetconnector-forminator') . '</a>',
    'ideo' => '<a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/" aria-label="' . esc_attr(esc_html__('Get Support', 'gsheetconnector-forminator')) . '" target="_blank">' . esc_html__('Support', 'gsheetconnector-forminator') . '</a>',
  ];

  $plugin_meta = array_merge($plugin_meta, $row_meta);
}

return $plugin_meta;
}
}
// Initialize the google sheet connector class
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$init = new GS_FORMNTR_Init();
