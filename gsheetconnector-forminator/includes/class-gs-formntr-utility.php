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
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r, WordPress.PHP.DevelopmentFunctions.error_log_error_log -- intentional debug logger, gated behind WP_DEBUG; print_r() used with true (return, not echo) to serialize arrays/objects for logging.
        error_log( print_r( $message, true ) );
        } else {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_error_log -- intentional debug logger, gated behind WP_DEBUG; logs plain string messages only.
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
         /** Insert error login in table */
      if (class_exists('GSCFORMNTR_Free_Error_Logs')) {
         GSCFORMNTR_Free_Error_Logs::formntr_log_from_debug($error);
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
