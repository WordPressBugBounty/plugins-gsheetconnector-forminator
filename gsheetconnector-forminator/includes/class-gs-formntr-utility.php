<?php
/*
 * Utilities class for edd google sheet connector pro
 * @since 1.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
/**
 * GS_FORMNTR_Free_Utility class - singleton class
 * @since 1.0
 */
class GS_FORMNTR_Free_Utility
{
    private function __construct()
    {
        // Do Nothing
    }
    /**
     * Get the singleton instance of the GS_FORMNTR_Free_Utility class
     *
     * @return singleton instance of GS_FORMNTR_Free_Utility
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
     * Prints message (string or array) in the debug.log file
     *
     * @param mixed $message
     */
    public function logger($message)
    {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
    /**
     * Display error or success message in the admin section
     *
     * @param array $data containing type and message
     * @return string with html containing the error message
     * 
     * @since 1.0 initial version
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
                $message = __('There\'s something wrong with your code...', 'gsheetconnector-forminator');
                $admin_notice = "<div id=\"message\" class=\"error\">\n";
                break;
        }
        $admin_notice .= "    <p>" . __($message, 'gsheetconnector-forminator') . "</p>\n";
        $admin_notice .= "</div>\n";
        return $admin_notice;
    }
    /**
     * Utility function to get the current user's role
     *
     * @since 1.0
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
     * @since 1.0.14
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
     * @since 1.0
     */
    public static function frmgs_debug_log($error)
    {
        try {
            if (!is_dir(GS_FORMNTR_PATH . 'logs')) {
                mkdir(GS_FORMNTR_PATH . 'logs', 0755, true);
            }
        } catch (Exception $e) {

        }
        try {
            // check if debug log file exists or not
            $logFilePathToDelete = GS_FORMNTR_PATH . "logs/log.txt";
            // Check if the log file exists before attempting to delete
            if (file_exists($logFilePathToDelete)) {
                unlink($logFilePathToDelete);
            }
            // check if debug unique log file exists or not
            $existDebugFile = get_option('frmgs_debug_log');
            if (!empty($existDebugFile) && file_exists($existDebugFile)) {
                $log = fopen($existDebugFile, 'a');
                if (is_array($error)) {
                    fwrite($log, print_r(date_i18n('j F Y H:i:s', current_time('timestamp')) . " \t PHP " . phpversion(), TRUE));
                    fwrite($log, print_r($error, TRUE));
                } else {
                    $result = fwrite($log, print_r(date_i18n('j F Y H:i:s', current_time('timestamp')) . " \t PHP " . phpversion() . " \t $error \r\n", TRUE));
                }
                fclose($log);
            } else {
                // if unique log file not exists then create new file code
                // Your log content (you can customize this)
                $unique_log_content = "Log created at " . date('Y-m-d H:i:s');
                // Create the log file
                $logfileName = 'log-' . uniqid() . '.txt';
                // Define the file path
                $logUniqueFile = GS_FORMNTR_PATH . "logs/" . $logfileName;
                if (file_put_contents($logUniqueFile, $unique_log_content)) {
                    // save debug unique file in table
                    update_option('frmgs_debug_log', $logUniqueFile);
                    // Success message
                    // echo "Log file created successfully: " . $logUniqueFile;
                    $log = fopen($logUniqueFile, 'a');
                    if (is_array($error)) {
                        fwrite($log, print_r(date_i18n('j F Y H:i:s', current_time('timestamp')) . " \t PHP " . phpversion(), TRUE));
                        fwrite($log, print_r($error, TRUE));
                    } else {
                        $result = fwrite($log, print_r(date_i18n('j F Y H:i:s', current_time('timestamp')) . " \t PHP " . phpversion() . " \t $error \r\n", TRUE));
                    }
                    fclose($log);

                } else {
                    // Error message
                    echo "Error - Not able to create Log File.";
                }
            }

        } catch (Exception $e) {

        }
    }
    /**
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
            $selected_row .= __($display_name, 'gsheetconnector-forminator');
            $selected_row .= "</label>";
        }
        echo esc_html($selected_row);
    }
    /*
     * Get all editable roles except for subscriber role
     * @return array
     * @since 1.1
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