<?php

/*
 * Utilities class for gsheetconnector forminator
 * @since 1.0.15
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GS_FORMNTR_Free_Utility class - singleton class
 * @since 1.0.15
 */

class GS_FORMNTR_Free_Utility
{

    /**
     *  Set things up.
     *  @since 1.0.15
     */

    private function __construct()
    {
        // Do Nothing
    }

    /**
     * Get the singleton instance of GS_FORMNTR_Free_Utility.
     *
     * Ensures only one instance of the utility class exists.
     *
     * @since 1.0.15
     *
     * @return GS_FORMNTR_Free_Utility The singleton instance.
     */

    public static function instance()
    {
        static $instance = NULL;
        if (is_null($instance)) {
            $instance = new GS_FORMNTR_Free_Utility();
        }
        return $instance;
    }

    /**
     * Logs a debug message if WP_DEBUG is enabled.
     *
     * Handles both string messages and exceptions by logging their messages.
     *
     * @since 1.0.15
     *
     */

    public function logger( $message ) {
    if ( WP_DEBUG === true ) {
        if ( is_array( $message ) || is_object( $message ) ) {
        error_log( print_r( $message, true ) );
        } else {
        error_log( $message );
        }
    }
    }

    /**
     * Display error or success message in the admin section
     *
     * @param array $data containing type and message
     * @return string with html containing the error message
     * 
     * @since 1.0.15
     */

    public function admin_notice($data = array())
    {
        // extract message and type from the $data array
        $message = isset($data['message']) ? $data['message'] : "";
        $message_type = isset($data['type']) ? $data['type'] : "";
        switch ($message_type) {
            case 'error':
                $admin_notice = '<div id="message" class="error notice is-dismissible">';
                break;
            case 'update':
                $admin_notice = '<div id="message" class="updated notice is-dismissible">';
                break;
            case 'update-nag':
                $admin_notice = '<div id="message" class="update-nag">';
                break;
            case 'upgrade':
                $admin_notice = '<div id="message" class="error notice edds-gs-upgrade is-dismissible">';
                break;
            case 'auth-expired-notice':
                $admin_notice = '<div id="message" class="error notice formntr-gs-auth-expired-adds is-dismissible">';
                break;
            default:
                $message = 'There\'s something wrong with your code...';
                $admin_notice = "<div id=\"message\" class=\"error\">\n";
                break;
        }
        $admin_notice .= "    <p>" . ($message === 'There\'s something wrong with your code...' ? __('There\'s something wrong with your code...', 'gsheetconnector-forminator') : $message) . "</p>\n";
        $admin_notice .= "</div>\n";
        return $admin_notice;
    }

    /**
     * Utility function to get the current user's role
     *
     * @since 1.0.15
     */

    public function get_current_user_role()
    {
        global $wp_roles;
        foreach ($wp_roles->role_names as $role => $name):
            if (current_user_can($role))
                return $role;
        endforeach;
    }

    /**
     * Fetch and save Auto Integration API credentials
     *
     * @since 1.0.15
     */

    public function save_api_credentials()
    {
        // Create a nonce
        $nonce = wp_create_nonce('forminatorgsc_api_creds');

        // Prepare parameters for the API call
        $params = array(
            'action' => 'get_data',
            'nonce' => $nonce,
            'plugin' => 'FORMINATORGSC',
            'method' => 'get',
        );

        // Add nonce and any other security parameters to the API request
        $api_url = add_query_arg($params, GS_FORMNTR_API_URL);

        // Make the API call using wp_remote_get
        $response = wp_remote_get($api_url);

        // Check for errors
        if (is_wp_error($response)) {
            // Handle error
            self::frmgs_debug_log(__METHOD__ . ' Error: ' . $response->get_error_message());
        } else {
            // API call was successful, process the data
            $response = wp_remote_retrieve_body($response);

            $decoded_response = json_decode($response);

            if (isset($decoded_response->api_creds) && (!empty($decoded_response->api_creds))) {
                $api_creds = wp_parse_args($decoded_response->api_creds);
                if (is_multisite()) {
                    // If it's a multisite, update the site option (network-wide)
                    update_site_option('forminatorgsc_api_creds', $api_creds);
                } else {
                    // If it's not a multisite, update the regular option
                    update_option('forminatorgsc_api_creds', $api_creds);
                }
            }
        }
    }

    /**
     * Utility function to get the current user's role
     *
     * @since 1.0.15
     */

    public static function frmgs_debug_log($error)
    {
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if (!WP_Filesystem()) {
            return;
        }
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir['basedir']) . 'gsc-forminator-logs/';
        $log_file = get_option('frmgs_debug_log');
        $timestamp = gmdate('Y-m-d H:i:s') . "\t PHP " . phpversion() . "\t";
        try {
            if (!$wp_filesystem->is_dir($log_dir)) {
                $wp_filesystem->mkdir($log_dir, FS_CHMOD_DIR);
            }
            // Protect directory with .htaccess
            $wp_filesystem->put_contents($log_dir . '.htaccess', "Deny from all\n", FS_CHMOD_FILE);
            $old_file = $log_dir . 'log.txt';
            if ($wp_filesystem->exists($old_file)) {
                $wp_filesystem->delete($old_file);
            }
            $log_message = is_array($error) || is_object($error)
                ? $timestamp . wp_json_encode($error) . "\r\n"
                : $timestamp . $error . "\r\n";
            if (!empty($log_file) && $wp_filesystem->exists($log_file)) {
                $existing = $wp_filesystem->get_contents($log_file);
                $wp_filesystem->put_contents($log_file, $existing . $log_message, FS_CHMOD_FILE);
            } else {
                $new_log_file = $log_dir . 'log-' . uniqid() . '.txt';
                $log_content = "Log created at " . gmdate('Y-m-d H:i:s') . "\r\n" . $log_message;
                if ($wp_filesystem->put_contents($new_log_file, $log_content, FS_CHMOD_FILE)) {
                    update_option('frmgs_debug_log', $new_log_file);
                }
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log('Exception in frmgs_debug_log: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Render a multi-checkbox for user roles
     *
     * @since 1.0.15
     * 
     * @param string $setting_name
     * @param array $selected_roles
     */

    public function gs_FORMNTR_checkbox_roles_multi($setting_name, $selected_roles)
    {
        $selected_row = '';
        $checked = '';
        $roles = array();
        $system_roles = $this->get_system_roles();
        if (!empty($selected_roles)) {
            foreach ($selected_roles as $role => $display_name) {
                array_push($roles, $role);
            }
        }
        $selected_row .= "<label style='display: block;'> <input type='checkbox' class='woforms-gs-checkbox' disabled='disabled' checked='checked'/>";
        $selected_row .= __("Administrator", 'gsheetconnector-forminator');
        $selected_row .= "</label>";
        foreach ($system_roles as $role => $display_name) {
            if ($role === "administrator") {
                continue;
            }
            if (!empty($roles) && is_array($roles) && in_array(esc_attr($role), $roles)) { // preselect specified role
                $checked = " ' checked='checked' ";
            } else {
                $checked = '';
            }
            $selected_row .= "<label style='display: block;'> <input type='checkbox' class='gs-checkbox'
			  name='" . $setting_name . "' value='" . esc_attr($role) . "'" . $checked . "/>";
            $selected_row .= esc_html($display_name);
            $selected_row .= "</label>";
        }
        echo esc_html($selected_row);
    }

    /** 
     * Get all editable roles except the subscriber role.
     *
     * @since 1.0.15
     *
     * @return array List of editable roles.
     */

    public function get_system_roles()
    {
        $participating_roles = array();
        $editable_roles = get_editable_roles();
        foreach ($editable_roles as $role => $details) {
            $participating_roles[$role] = $details['name'];
        }
        return $participating_roles;
    }
}
