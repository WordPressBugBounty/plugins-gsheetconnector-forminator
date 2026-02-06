<?php
/**
 * Plugin Name: GSheetConnector for Forminator Forms
 * Plugin URI: https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro
 * Description: Send your Forminator Forms data to your Google Sheets spreadsheet.
 * Author: GSheetConnector
 * Author URI: https://www.gsheetconnector.com/
 * Version: 1.0.17
 * Text Domain: gsheetconnector-forminator
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * Requires: forminator
 */
//
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (GS_FORMNTR_Init::gscfrmn_is_pugin_active('GS_FORMNTR_Init_PRO')) {
    return;
}

/*freemius*/
if (function_exists('is_plugin_active') && is_plugin_active('gsheetconnector-forminator/gsheetconnector-forminator.php')) {
    if (!function_exists('gfff_fs')) {

        // Create a helper function for easy SDK access.

        function gfff_fs()
        {
            global $gfff_fs;

            if (!isset($gfff_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/lib/vendor/freemius/start.php';

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
        do_action('gfff_fs_loaded');
    }

    /*freemius */
    /* Customizing the Opt Message Freemius  */

    function gs_forminator_form_free_custom_connect_message_on_update(
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

    gfff_fs()->add_filter('connect_message_on_update', 'gs_forminator_form_free_custom_connect_message_on_update', 10, 6);
    /* End Customizing the Opt Message Freemius  */
}
/**/


// Declare some global constants
define('GS_FORMNTR_VERSION', '1.0.17');
define('GS_FORMNTR_DB_VERSION', '1.0.17');
define('GS_FORMNTR_ROOT', dirname(__FILE__));
define('GS_FORMNTR_URL', plugins_url('/', __FILE__));
define('GS_FORMNTR_BASE_FILE', basename(dirname(__FILE__)) . '/gsheetconnector-forminator.php');
define('GS_FORMNTR_BASE_NAME', plugin_basename(__FILE__));
define('GS_FORMNTR_PATH', plugin_dir_path(__FILE__)); //use for include files to other files
define('GS_FORMNTR_CURRENT_THEME', get_stylesheet_directory());
define('GS_FORMNTR_API_URL', 'https://oauth.gsheetconnector.com/api-cred.php');
define('GS_FORMNTR_AUTH_REDIRECT_URI', admin_url('admin.php?page=formntr-gsheet-config'));
define('GS_FORMNTR_AUTH_PLUGIN_NAME', 'frmingsheetconnector');
define('GS_FORMNTR_AUTH_URL', 'https://oauth.gsheetconnector.com/index.php');
// define('GS_FORMNTR_TEXTDOMAIN', 'gsheetconnector-forminator');
// load_plugin_textdomain(GS_FORMNTR_TEXTDOMAIN, false, basename(dirname(__FILE__)) . '/languages');

// Include Utility Classes
if (!class_exists('GS_FORMNTR_Free_Utility')) {
    include(GS_FORMNTR_ROOT . '/includes/class-gs-formntr-utility.php');
}

//Include Library Files
require_once GS_FORMNTR_ROOT . '/lib/vendor/autoload.php';
include_once(GS_FORMNTR_ROOT . '/lib/google-sheets.php');
if (!class_exists('GS_FORMNTR_Service')) {
    include_once(GS_FORMNTR_PATH . 'includes/class-gs-formntr-services.php');
    //require_once GS_FORMNTR_PATH . 'includes/pages/forminator-panel.php';
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
        // for non-network sites only
        $this->run_for_site();
    }

    /**
     * Deactivates the plugin.
     *
     * @since 1.0.15
     */

    public function gs_formntr_deactivate($network_wide)
    {
    }

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

            // Redirect to the plugins page
            // wp_redirect(admin_url('plugins.php'));
            // exit; // Ensure that WordPress redirects immediately
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
            wp_enqueue_style(
                'gs-formntr-connector-css',
                GS_FORMNTR_URL . 'assets/css/gs-formntr-connector.css',
                GS_FORMNTR_VERSION,
                true
            );
            wp_enqueue_style(
                'font-awesome.min',
                GS_FORMNTR_URL . 'assets/css/font-awesome.min.css',
                GS_FORMNTR_VERSION,
                true
            );
            wp_enqueue_style(
                'gs-formntr-connector-css-font',
                GS_FORMNTR_URL . 'assets/css/fontawesome.css',
                GS_FORMNTR_VERSION,
                true
            );
            wp_enqueue_style(
                'gs-formntr-systeminfo',
                GS_FORMNTR_URL . 'assets/css/gs-formntr-systeminfo.css',
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
            wp_enqueue_script(
                'gs-formntr-connector',
                GS_FORMNTR_URL . 'assets/js/gs-formntr-connector.js',
                array(),
                GS_FORMNTR_VERSION,
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

    // public function upgrade_database_20() {
    //   global $wpdb;

    //   // look through each of the blogs and upgrade the DB
    //   if (function_exists('is_multisite') && is_multisite()) {
    //      //Get all blog ids;
    //      $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
    //      foreach ($blog_ids as $blog_id) {
    //         switch_to_blog($blog_id);
    //         $this->upgrade_helper_20();
    //         restore_current_blog();
    //      }
    //      return;
    //   }
    //   $this->upgrade_helper_20();
    // }

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
            '<a href="' . admin_url('admin.php?page=forminator&tab=integration') . '">' . __('Settings', 'gsheetconnector-forminator') . '</a>'
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

    /**
     * Build System Information String
     * @global object $wpdb
     * @return string
     * @since 1.0.15
     */

    public function get_formtr_system_info()
    {
        global $wpdb;
        // Get WordPress version
        $wp_version = get_bloginfo('version');
        // Get theme info
        $theme_data = wp_get_theme();
        $theme_name_version = $theme_data->get('Name') . ' ' . $theme_data->get('Version');
        $parent_theme = $theme_data->get('Template');

        if (!empty($parent_theme)) {
            $parent_theme_data = wp_get_theme($parent_theme);
            $parent_theme_name_version = $parent_theme_data->get('Name') . ' ' . $parent_theme_data->get('Version');
        } else {
            $parent_theme_name_version = 'N/A';
        }

        // Check plugin version and subscription plan
        $plugin_version = defined('GS_FORMNTR_VERSION') ? GS_FORMNTR_VERSION : 'N/A';
        $subscription_plan = 'FREE';

        // Check Google Account Authentication
        // $api_token = get_option('gs_token');
        // $google_sheet = new CF7GSC_googlesheet_PRO();
        // $email_account = $google_sheet->gsheet_print_google_account_email();

        $api_token_auto = get_option('gs_formntr_token');

        if (!empty($api_token_auto)) {
            // The user is authenticated through the auto method
            $google_sheet_auto = new FORMI_GSC_googlesheet();
            $email_account_auto = $google_sheet_auto->gsheet_print_google_account_email();
            $connected_email = !empty($email_account_auto) ? esc_html($email_account_auto) : 'Not Auth';
        } else {
            // Auto authentication is the only method available
            $connected_email = 'Not Auth';
        }

        // Check Google Permission
        $gs_verify_status = get_option('gs_formntr_verify');
        $search_permission = ($gs_verify_status === 'valid') ? 'Given' : 'Not Given';

        // Create the system info HTML
        $system_info = '<div class="system-statuswc">';
        $system_info .= '<h4><button id="show-info-button" class="info-button">GSheetConnector<span class="dashicons dashicons-arrow-down"></span></h4>';
        $system_info .= '<div id="info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>GSheetConnector</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Plugin Name</td><td>GSheetConnector for Forminator Forms</td></tr>';
        $system_info .= '<tr><td>Plugin Version</td><td>' . esc_html($plugin_version) . '</td></tr>';
        $system_info .= '<tr><td>Plugin Subscription Plan</td><td>' . esc_html($subscription_plan) . '</td></tr>';
        $system_info .= '<tr><td>Connected Email Account</td><td>' . $connected_email . '</td></tr>';
        if ($search_permission == "Given") {
            $gscpclass = 'gscpermission-given';
        } else {
            $gscpclass = 'gscpermission-notgiven';
        }

        $system_info .= '<tr><td>Google Drive Permission</td><td class="' . $gscpclass . '">' . esc_html($search_permission) . '</td></tr>';
        $system_info .= '<tr><td>Google Sheet Permission</td><td class="' . $gscpclass . '">' . esc_html($search_permission) . '</td></tr>';

        //$system_info .= '<tr><td>Google Drive Permission</td><td>' . esc_html($search_permission) . '</td></tr>';
        //        $system_info .= '<tr><td>Google Sheet Permission</td><td>' . esc_html($search_permission) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';
        // Add WordPress info
        // Create a button for WordPress info
        $system_info .= '<h2><button id="show-wordpress-info-button" class="info-button">WordPress Info<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="wordpress-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>WordPress Info</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Version</td><td>' . get_bloginfo('version') . '</td></tr>';
        $system_info .= '<tr><td>Site Language</td><td>' . get_bloginfo('language') . '</td></tr>';
        $system_info .= '<tr><td>Debug Mode</td><td>' . (WP_DEBUG ? 'Enabled' : 'Disabled') . '</td></tr>';
        $system_info .= '<tr><td>Home URL</td><td>' . get_home_url() . '</td></tr>';
        $system_info .= '<tr><td>Site URL</td><td>' . get_site_url() . '</td></tr>';
        $system_info .= '<tr><td>Permalink structure</td><td>' . get_option('permalink_structure') . '</td></tr>';
        $system_info .= '<tr><td>Is this site using HTTPS?</td><td>' . (is_ssl() ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is this a multisite?</td><td>' . (is_multisite() ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Can anyone register on this site?</td><td>' . (get_option('users_can_register') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is this site discouraging search engines?</td><td>' . (get_option('blog_public') ? 'No' : 'Yes') . '</td></tr>';
        $system_info .= '<tr><td>Default comment status</td><td>' . get_option('default_comment_status') . '</td></tr>';

        $server_ip = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {

            $server_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        if ($server_ip == '127.0.0.1' || $server_ip == '::1') {
            $environment_type = 'localhost';
        } else {
            $environment_type = 'production';
        }
        $system_info .= '<tr><td>Environment type</td><td>' . esc_html($environment_type) . '</td></tr>';

        $user_count = count_users();
        $total_users = $user_count['total_users'];
        $system_info .= '<tr><td>User Count</td><td>' . esc_html($total_users) . '</td></tr>';

        $system_info .= '<tr><td>Communication with WordPress.org</td><td>' . (get_option('blog_publicize') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // info about active theme
        $active_theme = wp_get_theme();

        $system_info .= '<h2><button id="show-active-info-button" class="info-button">Active Theme<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="active-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Active Theme</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Name</td><td>' . $active_theme->get('Name') . '</td></tr>';
        $system_info .= '<tr><td>Version</td><td>' . $active_theme->get('Version') . '</td></tr>';
        $system_info .= '<tr><td>Author</td><td>' . $active_theme->get('Author') . '</td></tr>';
        $system_info .= '<tr><td>Author website</td><td>' . $active_theme->get('AuthorURI') . '</td></tr>';
        $system_info .= '<tr><td>Theme directory location</td><td>' . $active_theme->get_template_directory() . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // Get a list of other plugins you want to check compatibility with
        $other_plugins = array(
            'plugin-folder/plugin-file.php', // Replace with the actual plugin slug
            // Add more plugins as needed
        );

        // Network Active Plugins
        if (is_multisite()) {
            $network_active_plugins = get_site_option('active_sitewide_plugins', array());
            if (!empty($network_active_plugins)) {
                $system_info .= '<h2><button id="show-netplug-info-button" class="info-button">Network Active plugins<span class="dashicons dashicons-arrow-down"></span></h2>';
                $system_info .= '<div id="netplug-info-container" class="info-content" style="display:none;">';
                $system_info .= '<h3>Network Active plugins</h3>';
                $system_info .= '<table>';
                foreach ($network_active_plugins as $plugin => $plugin_data) {
                    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                    $system_info .= '<tr><td>' . $plugin_data['Name'] . '</td><td>' . $plugin_data['Version'] . '</td></tr>';
                }
                // Add more network active plugin statuses here...
                $system_info .= '</table>';
                $system_info .= '</div>';
            }
        }
        // Active plugins
        $system_info .= '<h2><button id="show-acplug-info-button" class="info-button">Active plugins<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="acplug-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Active plugins</h3>';
        $system_info .= '<table>';

        // Retrieve all active plugins data
        $active_plugins_data = array();
        $active_plugins = get_option('active_plugins', array());
        foreach ($active_plugins as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            $active_plugins_data[$plugin] = array(
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'count' => 0, // Initialize the count to zero
            );
        }

        // Count the number of active installations for each plugin
        $all_plugins = get_plugins();
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            if (array_key_exists($plugin_file, $active_plugins_data)) {
                $active_plugins_data[$plugin_file]['count']++;
            }
        }

        // Sort plugins based on the number of active installations (descending order)
        uasort($active_plugins_data, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        // Display the top 5 most used plugins
        $counter = 0;
        foreach ($active_plugins_data as $plugin_data) {
            $system_info .= '<tr><td>' . $plugin_data['name'] . '</td><td>' . $plugin_data['version'] . '</td></tr>';
            // $counter++;
            // if ($counter >= 5) {
            //     break;
            // }
        }
        $system_info .= '</table>';
        $system_info .= '</div>';
        // Webserver Configuration
        $system_info .= '<h2><button id="show-server-info-button" class="info-button">Server<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="server-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Server</h3>';
        $system_info .= '<table>';
        $system_info .= '<p>The options shown below relate to your server setup. If changes are required, you may need your web host’s assistance.</p>';
        // Add Server information
        $system_info .= '<tr><td>Server Architecture</td><td>' . esc_html(php_uname('s')) . '</td></tr>';

        // $system_info .= '<tr><td>Web Server</td><td>' . esc_html($_SERVER['SERVER_SOFTWARE']) . '</td></tr>';
        $server_software = '';
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $server_software = sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']));
        }

        $system_info .= '<tr><td>Web Server</td><td>' . esc_html($server_software) . '</td></tr>';

        $system_info .= '<tr><td>PHP Version</td><td>' . esc_html(phpversion()) . '</td></tr>';
        $system_info .= '<tr><td>PHP SAPI</td><td>' . esc_html(php_sapi_name()) . '</td></tr>';
        $system_info .= '<tr><td>PHP Max Input Variables</td><td>' . esc_html(ini_get('max_input_vars')) . '</td></tr>';
        $system_info .= '<tr><td>PHP Time Limit</td><td>' . esc_html(ini_get('max_execution_time')) . ' seconds</td></tr>';
        $system_info .= '<tr><td>PHP Memory Limit</td><td>' . esc_html(ini_get('memory_limit')) . '</td></tr>';
        $system_info .= '<tr><td>Max Input Time</td><td>' . esc_html(ini_get('max_input_time')) . ' seconds</td></tr>';
        $system_info .= '<tr><td>Upload Max Filesize</td><td>' . esc_html(ini_get('upload_max_filesize')) . '</td></tr>';
        $system_info .= '<tr><td>PHP Post Max Size</td><td>' . esc_html(ini_get('post_max_size')) . '</td></tr>';
        $system_info .= '<tr><td>cURL Version</td><td>' . esc_html(curl_version()['version']) . '</td></tr>';
        $system_info .= '<tr><td>Is SUHOSIN Installed?</td><td>' . (extension_loaded('suhosin') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is the Imagick Library Available?</td><td>' . (extension_loaded('imagick') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Are Pretty Permalinks Supported?</td><td>' . (get_option('permalink_structure') ? 'Yes' : 'No') . '</td></tr>';

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $htaccess_path = ABSPATH . '.htaccess';

        $is_writable = $wp_filesystem->exists($htaccess_path) && $wp_filesystem->is_writable($htaccess_path);

        $system_info .= '<tr><td>.htaccess Rules</td><td>' . esc_html($is_writable ? 'Writable' : 'Non Writable') . '</td></tr>';

        // $system_info .= '<tr><td>.htaccess Rules</td><td>' . esc_html(is_writable('.htaccess') ? 'Writable' : 'Non Writable') . '</td></tr>';

        $system_info .= '<tr><td>Current Time</td><td>' . esc_html(current_time('mysql')) . '</td></tr>';
        $system_info .= '<tr><td>Current UTC Time</td><td>' . esc_html(current_time('mysql', true)) . '</td></tr>';
        $system_info .= '<tr><td>Current Server Time</td><td>' . esc_html(gmdate('Y-m-d H:i:s')) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // Database Configuration
        $system_info .= '<h2><button id="show-database-info-button" class="info-button">Database<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="database-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Database</h3>';
        $system_info .= '<table>';
        $database_extension = 'mysqli';
        $database_server_version = $wpdb->db_server_info();
        $database_client_version = $wpdb->db_version();
        $database_username = DB_USER;
        $database_host = DB_HOST;
        $database_name = DB_NAME;
        $table_prefix = $wpdb->prefix;
        $database_charset = $wpdb->charset;
        $database_collation = $wpdb->collate;
        $max_allowed_packet_size = $wpdb->get_var("SHOW VARIABLES LIKE 'max_allowed_packet'");
        $max_connections_number = $wpdb->get_var("SHOW VARIABLES LIKE 'max_connections'");

        $system_info .= '<tr><td>Extension</td><td>' . esc_html($database_extension) . '</td></tr>';
        $system_info .= '<tr><td>Server Version</td><td>' . esc_html($database_server_version) . '</td></tr>';
        $system_info .= '<tr><td>Client Version</td><td>' . esc_html($database_client_version) . '</td></tr>';
        $system_info .= '<tr><td>Database Username</td><td>' . esc_html($database_username) . '</td></tr>';
        $system_info .= '<tr><td>Database Host</td><td>' . esc_html($database_host) . '</td></tr>';
        $system_info .= '<tr><td>Database Name</td><td>' . esc_html($database_name) . '</td></tr>';
        $system_info .= '<tr><td>Table Prefix</td><td>' . esc_html($table_prefix) . '</td></tr>';
        $system_info .= '<tr><td>Database Charset</td><td>' . esc_html($database_charset) . '</td></tr>';
        $system_info .= '<tr><td>Database Collation</td><td>' . esc_html($database_collation) . '</td></tr>';
        $system_info .= '<tr><td>Max Allowed Packet Size</td><td>' . esc_html($max_allowed_packet_size) . '</td></tr>';
        $system_info .= '<tr><td>Max Connections Number</td><td>' . esc_html($max_connections_number) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // wordpress constants
        $system_info .= '<h2><button id="show-wrcons-info-button" class="info-button">WordPress Constants<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="wrcons-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>WordPress Constants</h3>';
        $system_info .= '<table>';
        // Add WordPress Constants information
        $system_info .= '<tr><td>ABSPATH</td><td>' . esc_html(ABSPATH) . '</td></tr>';
        $system_info .= '<tr><td>WP_HOME</td><td>' . esc_html(home_url()) . '</td></tr>';
        $system_info .= '<tr><td>WP_SITEURL</td><td>' . esc_html(site_url()) . '</td></tr>';
        $system_info .= '<tr><td>WP_CONTENT_DIR</td><td>' . esc_html(WP_CONTENT_DIR) . '</td></tr>';
        $system_info .= '<tr><td>WP_PLUGIN_DIR</td><td>' . esc_html(WP_PLUGIN_DIR) . '</td></tr>';
        $system_info .= '<tr><td>WP_MEMORY_LIMIT</td><td>' . esc_html(WP_MEMORY_LIMIT) . '</td></tr>';
        $system_info .= '<tr><td>WP_MAX_MEMORY_LIMIT</td><td>' . esc_html(WP_MAX_MEMORY_LIMIT) . '</td></tr>';
        $system_info .= '<tr><td>WP_DEBUG</td><td>' . (defined('WP_DEBUG') && WP_DEBUG ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>WP_DEBUG_DISPLAY</td><td>' . (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>SCRIPT_DEBUG</td><td>' . (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>WP_CACHE</td><td>' . (defined('WP_CACHE') && WP_CACHE ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>CONCATENATE_SCRIPTS</td><td>' . (defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>COMPRESS_SCRIPTS</td><td>' . (defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>COMPRESS_CSS</td><td>' . (defined('COMPRESS_CSS') && COMPRESS_CSS ? 'Yes' : 'No') . '</td></tr>';
        // Manually define the environment type (example values: 'development', 'staging', 'production')
        $environment_type = 'development';

        // Display the environment type
        $system_info .= '<tr><td>WP_ENVIRONMENT_TYPE</td><td>' . esc_html($environment_type) . '</td></tr>';

        $system_info .= '<tr><td>WP_DEVELOPMENT_MODE</td><td>' . (defined('WP_DEVELOPMENT_MODE') && WP_DEVELOPMENT_MODE ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>DB_CHARSET</td><td>' . esc_html(DB_CHARSET) . '</td></tr>';
        $system_info .= '<tr><td>DB_COLLATE</td><td>' . esc_html(DB_COLLATE) . '</td></tr>';

        $system_info .= '</table>';
        $system_info .= '</div>';

        // Filesystem Permission
        $system_info .= '<h2><button id="show-ftps-info-button" class="info-button">Filesystem Permission <span class="dashicons dashicons-arrow-down"></span></button></h2>';
        $system_info .= '<div id="ftps-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Filesystem Permission</h3>';
        $system_info .= '<p>Shows whether WordPress is able to write to the directories it needs access to.</p>';
        $system_info .= '<table>';
        // Filesystem Permission information.
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Define the paths
        $paths = array(
            'The main WordPress directory' => ABSPATH,
            'The wp-content directory' => WP_CONTENT_DIR,
            'The uploads directory' => wp_upload_dir()['basedir'],
            'The plugins directory' => WP_PLUGIN_DIR,
            'The themes directory' => get_theme_root(),
        );

        // Loop through and check writability using WP_Filesystem
        foreach ($paths as $label => $path) {
            $writable = $wp_filesystem->exists($path) && $wp_filesystem->is_writable($path);
            $system_info .= '<tr><td>' . esc_html($label) . '</td><td>' . esc_html($path) . '</td><td>' . esc_html($writable ? 'Writable' : 'Not Writable') . '</td></tr>';
        }


        $system_info .= '</table>';
        $system_info .= '</div>';

        return $system_info;
    }

    /**
     * Displays the last 100 lines from the debug log file in reversed order.
     *
     * @access public
     * @since 1.0.15
     */

    public function display_error_log()
    {
        // Define the path to your debug log file
        $debug_log_file = WP_CONTENT_DIR . '/debug.log';

        // Check if the debug log file exists
        if (file_exists($debug_log_file)) {
            // Read the contents of the debug log file
            $debug_log_contents = file_get_contents($debug_log_file);

            // Split the log content into an array of lines
            $log_lines = explode("\n", $debug_log_contents);

            // Get the last 100 lines in reversed order
            $last_100_lines = array_slice(array_reverse($log_lines), 0, 100);

            // Join the lines back together with line breaks
            $last_100_log = implode("\n", $last_100_lines);

            // Output the last 100 lines in reversed order in a textarea
            ?>
            <textarea class="errorlog" rows="20" cols="80"><?php echo esc_textarea($last_100_log); ?></textarea>
            <?php
        } else {
            echo 'Debug log file not found.';
        }
    }
}
// Initialize the google sheet connector class
$init = new GS_FORMNTR_Init();
