<?php
//require_once( forminator_plugin_dir() . '/admin/class-forminator-form-editor-panel.php' );

/*
 * Service class for eddcommerce google sheet connector pro
 * @since 1.0.15
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GS_FORMNTR_Service class
 * @since 1.0.15
 */

class GS_FORMNTR_Service
{
    public $class_name = "GS_FORMNTR_Service";
    public $entry_id;
    public $form_id;
    //protected $form_settings_instance;

    /**
     * Initialize plugin hooks and actions.
     *
     * - Registers AJAX handler for deleting a feed.
     * - Hooks into Forminator form submission to send data to Google Sheets.
     * - Handles saving feed settings on admin initialization.
     * - Adds form feed names during admin initialization.
     *
     * @since 1.0.15
     */

    public function init()
    {
        // delete feed
        add_action('wp_ajax_delete_feed_forminator', array($this, 'delete_feed_forminator'));

        // form submit send entry in sheet
        add_filter('forminator_custom_form_submit_field_data', array($this, 'send_form_submission_to_google_sheets'), 100, 20);

        // form feed settings sheet details save in table
        add_action('admin_init', array($this, 'execute_post_data'));

        // add feed name
        add_action('admin_init', array($this, 'add_form_feed_name'), 10, 3);
    }

    /**
     * Send form submission entry to Google Sheets.
     *
     * Triggered on form submit to send entry data to the connected Google Sheet.
     *
     * @since 1.0.15
     */

    public function send_form_submission_to_google_sheets($field_data_array, $form_id)
    {

        $data = array();
        $feeds_details = array();

        try {
            $Forminator_API = new Forminator_API();
            $form_settings = $Forminator_API->get_form($form_id);
            $form = $form_settings->fields;

            $form_field_label = [];
            $form_field_labels = array();

            foreach ($form as $item) {
                if (isset($item->raw['field_label'])) {
                    $form_field_label[$item->slug] = $item->raw['field_label'];
                    $form_field_labels[] = $item->raw['field_label'];
                }
            }

            foreach ($field_data_array as $key => $value) {

                $field_value = "";
                $field_label = (isset($form_field_label[$value['name']]) && $form_field_label[$value['name']] != "")
                    ? $form_field_label[$value['name']]
                    : $value['name'];

                if (isset($value['field_type']) && $value['field_type'] === 'select') {
                    $selected_value = $value['value'];
                    foreach ($value['field_array']['options'] as $option) {
                        if ($option['value'] === $selected_value) {
                            $field_value = $option['label'];
                            break;
                        }
                    }
                } elseif (isset($value['field_type']) && $value['field_type'] === 'radio') {
                    $selected_value = $value['value'];
                    foreach ($value['field_array']['options'] as $option) {
                        if ($option['value'] === $selected_value) {
                            $field_value = $option['label'];
                            break;
                        }
                    }
                } elseif (is_array($value['value'])) {
                    if (isset($value['field_type']) && $value['field_type'] === 'time') {
                        $field_value = implode(':', $value['value']);
                    } elseif (isset($value['field_type']) && $value['field_type'] === 'upload') {
                        $field_value = $value['value']['file']['name'];
                    } elseif (isset($value['field_type']) && $value['field_type'] === 'address') {
                        if (isset($value['field_array'])) {
                            $address_mapping = [
                                'street_address' => $value['field_array']['street_address_label'] ?? 'Street Address',
                                'address_line' => $value['field_array']['address_line_label'] ?? 'Apartment, suite, etc',
                                'city' => $value['field_array']['address_city_label'] ?? 'City',
                                'state' => $value['field_array']['address_state_label'] ?? 'State/Province',
                                'zip' => $value['field_array']['address_zip_label'] ?? 'ZIP / Postal Code',
                                'country' => $value['field_array']['address_country_label'] ?? 'Country',
                            ];
                            foreach ($address_mapping as $field_key => $field_label) {
                                if (isset($value['value'][$field_key])) {
                                    $data[$field_label] = $value['value'][$field_key];
                                }
                            }
                        }
                    } else {
                        $field_value = isset($value['value']['formatting_result'])
                            ? $value['value']['formatting_result']
                            : implode(',', $value['value']);
                    }
                } else {
                    $field_value = $value['value'];
                }

                if (isset($field_value) && (strpos($field_value, '+') === 0 || strpos($field_value, '=') === 0)) {
                    $field_value = "'" . $field_value;
                }

                if (!isset($value['field_type']) || $value['field_type'] != 'address') {
                    $data[$field_label] = $field_value;
                }
            }


            // Replace 'meta_key_name' with the actual name of the meta key you want to retrieve the meta ID for
            $meta_key_name = 'forminator_forms_feed';
            // Get a reference to the global WordPress database object
            global $wpdb;

            // Define the query to retrieve the meta values for the given post ID
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
                    $form_id,
                    'forminator_forms_feed'
                ),
                ARRAY_A
            );
            $feedIdsArr = array_column($results, 'meta_id');
            $feedIds = implode(',', $feedIdsArr);
            // Loop through the results to extract the meta values
            $meta_values = array();
            foreach ($results as $result) {
                $meta_key = $result['meta_key'];
                $meta_value = $result['meta_value'];
                $meta_values[$meta_key] = $meta_value;
            }

            // $query1 = $wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE post_id IN ($feedIds);");
            $feeds_details = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->postmeta} WHERE post_id IN ($feedIds)"
                ),
                ARRAY_A
            );

            if (!empty($feeds_details)) {
                foreach ($feeds_details as $key => $value) {
                    $meta_value = $value['meta_value'];
                    // Unserialize the meta value to get an array
                    // $meta_array = unserialize($meta_value);
                    if (is_serialized($meta_value)) {
                        $meta_array = unserialize($meta_value);
                    } else {
                        $meta_array = $meta_value; // Or maybe [] if you always expect an array
                    }
                    // Extract the sheet name from the array
                    $meta_array = maybe_unserialize($meta_value);
                    $sheet_name = $meta_array['sheet_name'] ?? '';
                    $sheet_id = $meta_array['sheet_id'] ?? '';
                    $tab_name = $meta_array['tab_name'] ?? '';
                    $tab_id = $meta_array['tab_id'] ?? '';


                    // Get the form data
                    include_once(GS_FORMNTR_ROOT . '/lib/google-sheets.php');
                    //$tokendata = get_option('gs_formntr_token');
                    $doc = new FORMI_GSC_googlesheet();
                    $doc->auth();
                    $doc->setSpreadsheetId($sheet_id);
                    $doc->setWorkTabId($tab_id);
                    // Fetch the local date and time
                    $local_date = date_i18n(get_option('date_format'));
                    $local_time = date_i18n(get_option('time_format'));

                    // Check if the user has manually added a header for date and time
                    $manual_date_header = isset($meta_values['date_header']) ? $meta_values['date_header'] : 'date';
                    $manual_time_header = isset($meta_values['time_header']) ? $meta_values['time_header'] : 'time';

                    // Pass the date and time to the data array using the headers
                    $data[$manual_date_header] = $local_date;
                    $data[$manual_time_header] = $local_time;



                    // Add the row to Google Sheets
                    $doc->add_row($data, $field_data_array);
                }
            }
            return $field_data_array;
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return $field_data_array;
        }
        return $field_data_array;
    }

    /**
     * Deleting Feed.
     *
     * @since 1.0.15
     */

    public function delete_feed_forminator()
    {
        try {
            check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
            $feedId = isset($_POST['feed_id'])
                ? intval(sanitize_text_field(wp_unslash($_POST['feed_id'])))
                : 0;



            if ($feedId) {
                $deleted = delete_metadata('post', $feedId, 'forminator_forms_feed_details');
                $deleted1 = delete_metadata_by_mid('post', $feedId);

                if ($deleted1) {
                    echo 'success';
                } else {
                    echo 'error';
                }
            }
            wp_die();
        } catch (Exeption $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
    }

    /**
     * Display success notice when Forminator data settings are saved.
     *
     * @since 1.0.15
     */

    public function forminator_success_notice()
    {
        try {
            $success_msg = GS_FORMNTR_Free_Utility::instance()->admin_notice(array(
                'type' => 'update',
                'message' => __('Forminator Data Settings saved successfully.', 'gsheetconnector-forminator')
            ));
            echo wp_kses_post($success_msg);
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return;
        }
    }

    /**
     * Process and handle POST data for various admin settings and feed edits.
     *
     * - Saves uninstall plugin settings with nonce verification.
     * - Handles feed edit form submission with permission and nonce checks.
     * - Updates feed meta data in the database.
     *
     * @since 1.0.15
     */

    public function execute_post_data()
    {
        try {
           
            if (isset($_POST['execute-edit-feed-forminator'])) {
                // nonce check
                if (
                    !isset($_POST['frmntr-form-gs-ajax-nonce']) ||
                    !wp_verify_nonce(
                        sanitize_text_field(wp_unslash($_POST['frmntr-form-gs-ajax-nonce'])),
                        'frmntr-form-gs-ajax-nonce'
                    )
                ) {
                    wp_die('Invalid nonce'); // Die with an error message if nonce fails verification
                }

                // Check if the user is logged in and has permissions to edit feeds
                if (!is_user_logged_in() || !current_user_can('edit_posts')) {
                    echo 'You do not have permission to edit feeds.';
                    exit;
                }

                // Get the feed ID and form ID from the form
                $feed_id = isset($_POST['edit_feed_id']) ? sanitize_text_field(wp_unslash($_POST['edit_feed_id'])) : "";
                $form_id = isset($_POST['edit_form_id']) ? sanitize_text_field(wp_unslash($_POST['edit_form_id'])) : "";

                // Get the updated feed data from the form
                $sheet_name = isset($_POST['edit_sheet_name']) ? sanitize_text_field(wp_unslash($_POST['edit_sheet_name'])) : "";
                $sheet_id = isset($_POST['edit_sheet_id']) ? sanitize_text_field(wp_unslash($_POST['edit_sheet_id'])) : "";
                $tab_name = isset($_POST['edit_tab_name']) ? sanitize_text_field(wp_unslash($_POST['edit_tab_name'])) : "";
                $tab_id = isset($_POST['edit_tab_id']) ? sanitize_text_field(wp_unslash($_POST['edit_tab_id'])) : '';

                // Update the feed data in the database
                if ($feed_id !== "" && $sheet_name !== "" && $sheet_id !== "" && $tab_name !== "" && $tab_id !== "") {
                    $meta_key = 'forminator_forms_feed_details';
                    $meta_value = array(
                        'sheet_name' => $sheet_name,
                        'sheet_id' => $sheet_id,
                        'tab_name' => $tab_name,
                        'tab_id' => $tab_id,
                    );
                    update_post_meta($feed_id, $meta_key, $meta_value);

                    // Fetch the form field labels dynamically
                    // $form_settings = Forminator_API::get_form($form_id);
                    // $form = $form_settings->fields;

                    // $final_header_array = [];
                    // foreach ($form as $item) {
                    //     if (isset($item->raw['field_label'])) {
                    //         $final_header_array[] = $item->raw['field_label'];
                    //     }
                    // }
                    add_action('admin_notices', array($this, 'forminator_success_notice'));
                }
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
    }

    /**
     * Display a success notice when a feed is added successfully.
     *
     * @since 1.0.15
     */

    public function forminator_feed_success_notice()
    {
        try {
            $success_msg = GS_FORMNTR_Free_Utility::instance()->admin_notice(array(
                'type' => 'update',
                'message' => __('Feed added successfully.', 'gsheetconnector-forminator')
            ));
            echo wp_kses_post($success_msg);
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return;
        }
    }

    /**
     * Display an error notice when a feed name already exists.
     *
     * @since 1.0.15
     */

    public function forminator_feed_error_notice()
    {
        try {
            $success_msg = GS_FORMNTR_Free_Utility::instance()->admin_notice(array(
                'type' => 'error',
                'message' => __('Feed name already exists in the list, Please enter unique name of feed.', 'gsheetconnector-forminator')
            ));
            echo wp_kses_post($success_msg);
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return;
        }
    }

    /**
     * Adding Feed.
     *
     * @since 1.0.15
     */

    public function add_form_feed_name()
    {
        try {
            if (isset($_POST['execute-submit-feed-forminator'])) {

                // Nonce check
                if (
                    !isset($_POST['frmntr-gs-feed-ajax-nonce']) ||
                    !wp_verify_nonce(
                        sanitize_text_field(wp_unslash($_POST['frmntr-gs-feed-ajax-nonce'])),
                        'frmntr-gs-feed-ajax-nonce'
                    )
                ) {
                    wp_die('Invalid nonce');
                }

                // Sanitize input
                $feed_name = isset($_POST['feed_name'])
                    ? sanitize_text_field(wp_unslash($_POST['feed_name']))
                    : '';

                $form_id = isset($_GET['form_id'])
                    ? intval(sanitize_text_field(wp_unslash($_GET['form_id'])))
                    : '';

                if ($form_id != "") {
                    $meta_key = 'forminator_forms_feed';
                    $existing_feeds = get_post_meta($form_id, $meta_key);
                    $duplicate_found = false;
                    if (!empty($existing_feeds)) {
                        foreach ($existing_feeds as $feed) {
                            if (isset($feed['feed_name']) && strtolower($feed['feed_name']) === strtolower($feed_name)) {
                                $duplicate_found = true;
                                break;
                            }
                        }
                    }

                    if ($duplicate_found) {
                        // Show error
                        add_action('admin_notices', array($this, 'forminator_feed_error_notice'));
                    } else {
                        // Insert new feed
                        $meta_value = array(
                            'feed_name' => $feed_name,
                            'form_id'   => $form_id,
                        );
                        add_post_meta($form_id, $meta_key, $meta_value);
                        add_action('admin_notices', array($this, 'forminator_feed_success_notice'));
                    }
                } else {
                    add_action('admin_notices', array($this, 'forminator_feed_error_notice'));
                }
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
    }


    /**
     * Retrieve feed details for a given form ID.
     *
     * Fetches the 'forminator_forms_feed' meta data for the specified form.
     *
     * @since 1.0.15
     *
     * @return array Feed data array or empty array on failure.
     */

    public function get_feed_details()
    {
        $feed_data = array();
        try {
            $form_id = isset($_GET['form_id']) ? sanitize_text_field(wp_unslash($_GET['form_id'])) : '';
            global $wpdb;
            $table_name = esc_sql($wpdb->prefix . 'postmeta');
            // $query = "SELECT post_id, meta_id, meta_value FROM $table_name WHERE meta_key = 'forminator_forms_feed' AND post_id = $form_id";
            // $feed_data = $wpdb->get_results($query, ARRAY_A);
            $feed_data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT post_id, meta_id, meta_value 
                    FROM {$table_name} 
                    WHERE meta_key = %s 
                    AND post_id = %d",
                    'forminator_forms_feed',
                    $form_id
                ),
                ARRAY_A
            );
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
        return $feed_data;
    }

    /**
     * Retrieve the list of Forminator forms.
     *
     * Fetches all posts of post type 'forminator_forms' with status 'publish' or 'draft'.
     *
     * @since 1.0.15
     *
     * @return array List of form posts.
     */

    public function get_form_list()
    {
        $forms = array();
        try {
            global $wpdb;
            $forms_table = $wpdb->prefix . 'posts';
            $forms = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$forms_table} 
                    WHERE post_type = %s 
                    AND (post_status = %s OR post_status = %s)",
                    'forminator_forms',
                    'publish',
                    'draft'
                )
            );
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
        return $forms;
    }

    /**
     * Function - fetch contant form list that is connected with google sheet
     * @since 1.0.15
     */

    public function get_forms_connected_to_sheet()
    {
        global $wpdb;
        $query = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT ID, post_title, meta_value, meta_key, meta_id
                FROM {$wpdb->prefix}posts AS p
                JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
                WHERE pm.meta_key = %s
                  AND p.post_type = %s
                ",
                'forminator_forms_feed',
                'forminator_forms'
            )
        );
        return $query;
    }

    /**
     * Displays success notice after uninstall plugin settings are saved.
     *
     * @access public
     * @since 1.0.15
     */

    public function gs_formntr_uninstall_plugin_notice()
    {
        $success_msg = GS_FORMNTR_Free_Utility::instance()->admin_notice(array(
            'type' => 'update',
            'message' => esc_html('Uninstall Plugin Settings saved successfully.', 'gsheetconnector-forminator')
        ));
        echo wp_kses_post($success_msg);
    }

    /**
     * Displays error message on plugin settings page if triggered by form submission.
     *
     * @access public
     * @since 1.0.15
     */

    public function error_message($error)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (is_admin() && isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'admin.php?page=formntr-gsheet-config') {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (
                isset($_POST['formntr-save-btn'], $_POST['formntr-save-nonce']) &&
                wp_verify_nonce(
                    sanitize_text_field(wp_unslash($_POST['formntr-save-nonce'])),
                    'formntr-save-action'
                )
            ) {
                $plugin_error = GS_FORMNTR_Free_Utility::instance()->admin_notice(array(
                    'type' => 'error',
                    'message' => esc_html($error, 'gsheetconnector-forminator')
                ));
                echo esc_html($plugin_error, 'gsheetconnector-forminator');
            }
        }
    }
}


$gs_FORMNTR_service = new GS_FORMNTR_Service();
$gs_FORMNTR_service->init();
