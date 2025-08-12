<?php

/**
 * Extension class for GS Forminator Forms Google Sheet Connector extensions operations
 * @since 1.0.15
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GS_Formntr_Extensions
 * @since 1.0.15
 */

class GS_Formntr_Extensions
{

    /**
     *  Set things up.
     *
     *  @since 1.0.15
     */

    public function __construct()
    {
        // Install Forminator Forms plugin
        add_action('wp_ajax_gs_ff_install_plugin', array($this, 'gs_ff_install_plugin'));

        // Activate Forminator Forms plugin
        add_action('wp_ajax_gs_ff_activate_plugin', array($this, 'gs_ff_activate_plugin'));

        // Deactivate Forminator Forms plugin
        add_action('wp_ajax_gs_ff_deactivate_plugin', array($this, 'gs_ff_deactivate_plugin'));
    }

    /**
     * Deactivate Forminator Forms plugin
     *
     * @since 1.0.15
     */

    function gs_ff_deactivate_plugin()
    {
        // nonce check
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error('You do not have permission to deactivate plugins.');
        }

        if (!isset($_POST['plugin_slug'])) {
            wp_send_json_error('Plugin slug is missing.');
        }

        $plugin_slug = sanitize_text_field(wp_unslash($_POST['plugin_slug']));

        if (empty($plugin_slug)) {
            wp_send_json_error('Invalid plugin.');
        }

        // Ensure plugin exists before attempting to deactivate
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
            wp_send_json_error('Plugin not found.');
        }

        deactivate_plugins($plugin_slug);

        if (is_plugin_active($plugin_slug)) {
            wp_send_json_error('Failed to deactivate plugin.');
        }

        
        wp_send_json_success('Plugin deactivated successfully.');
    }

    /**
     * Installs or upgrades a plugin via AJAX using provided slug and download URL.
     *
     * @access public
     * @since 1.0.15
     */

    function gs_ff_install_plugin()
    {
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
        if (!isset($_POST['plugin_slug'], $_POST['download_url'])) {
            wp_send_json_error(['message' => 'Missing required parameters.']);
        }

        $plugin_slug = sanitize_text_field(wp_unslash($_POST['plugin_slug']));
        $download_url = isset($_POST['download_url']) ? sanitize_text_field(wp_unslash($_POST['download_url'])) : '';


        if (empty($plugin_slug) || empty($download_url)) {
            wp_send_json_error(['message' => 'Invalid plugin data.']);
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/update.php';

        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());

        // Get the list of installed plugins
        $installed_plugins = get_plugins();
        $plugin_path = '';

        // Find the correct plugin file path
        foreach ($installed_plugins as $path => $details) {
            if (strpos($path, $plugin_slug . '/') === 0) {
                $plugin_path = $path;
                break;
            }
        }

        // Check if the plugin is already installed
        if ($plugin_path) {
            // Plugin is installed, check for updates
            $update_plugins = get_site_transient('update_plugins');

            if (isset($update_plugins->response[$plugin_path])) {
                // Upgrade the plugin
                $result = $upgrader->upgrade($plugin_path);

                if (is_wp_error($result)) {
                    wp_send_json_error(['message' => 'Upgrade failed: ' . $result->get_error_message()]);
                }

                wp_send_json_success(['message' => 'Plugin upgraded successfully.']);
            } else {
                wp_send_json_error(['message' => 'No updates available for this plugin.']);
            }
        } else {
            // Plugin is NOT installed, install it
            $result = $upgrader->install($download_url);

            if (is_wp_error($result)) {
                wp_send_json_error(['message' => 'Installation failed: ' . $result->get_error_message()]);
            }

            wp_send_json_success();
        }
    }

    /**
     * Activates a plugin via AJAX using the provided plugin slug.
     *
     * @access public
     * @since 1.0.15
     */

    function gs_ff_activate_plugin()
    {
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        if (!isset($_POST['plugin_slug'])) {
            wp_send_json_error(['message' => 'Missing plugin slug.']);
        }

        $plugin_slug = sanitize_text_field(wp_unslash($_POST['plugin_slug']));

        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        // Check if plugin file exists
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
            wp_send_json_error(['message' => 'Plugin file not found: ' . WP_PLUGIN_DIR . '/' . $plugin_slug]);
        }

        $activated = activate_plugin($plugin_slug);

        if (is_wp_error($activated)) {
            wp_send_json_error(['message' => $activated->get_error_message()]);
        }

        wp_send_json_success();
    }
}
$GS_Formntr_Extensions = new GS_Formntr_Extensions();
