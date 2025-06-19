<?php
//require_once( forminator_plugin_dir() . '/admin/class-forminator-form-editor-panel.php' );

/*
 * Service class for eddcommerce google sheet connector pro
 * @since 1.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
/**
 * GS_FORMNTR_Service class
 * @since 1.0
 */
class GS_FORMNTR_Service {
    public $class_name = "GS_FORMNTR_Service";
    public $entry_id;
    public $form_id;

	//protected $form_settings_instance;

    public function init() {
        // delete feed
         add_action('wp_ajax_delete_feed_forminator', array( $this, 'delete_feed_forminator'));
        //Forminator hooks
        // form submit send entry in sheet
        add_filter( 'forminator_custom_form_submit_field_data', array( $this, 'send_form_submission_to_google_sheets'), 100, 20 );
       // form feed settings sheet details save in table
         add_action( 'admin_init', array($this,'execute_post_data'));
        
         // add feed name
        add_action( 'admin_init', array($this,'add_form_feed_name'),10,3);
    }


    /**
     * form submit send entry in sheet.
     *
     * @since 1.0.0
     */
    public function send_form_submission_to_google_sheets($field_data_array, $form_id) {

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
            // Define the name of the wp_postmeta table
            $table_name = $wpdb->prefix . 'postmeta';
            // Define the query to retrieve the meta values for the given post ID
            $query = "
                SELECT *
                FROM $table_name
                WHERE post_id = %d AND meta_key = 'forminator_forms_feed'
            ";
            // Use the $wpdb object to prepare and execute the query with the post ID
            $results = $wpdb->get_results($wpdb->prepare($query, $form_id), ARRAY_A);
            $feedIdsArr = array_column($results, 'meta_id');
            $feedIds = implode(',', $feedIdsArr);
            // Loop through the results to extract the meta values
            $meta_values = array();
            foreach ($results as $result) {
                $meta_key = $result['meta_key'];
                $meta_value = $result['meta_value'];
                $meta_values[$meta_key] = $meta_value;
            }
            $query1 = $wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE post_id IN ($feedIds);");
            $feeds_details = $wpdb->get_results($query1, ARRAY_A);
            if (!empty($feeds_details)) {
                foreach ($feeds_details as $key => $value) {
                    $meta_value = $value['meta_value'];
                    // Unserialize the meta value to get an array
                    $meta_array = unserialize($meta_value);
                    // Extract the sheet name from the array
                    $sheet_name = $meta_array['sheet_name'];
                    $sheet_id = $meta_array['sheet_id'];
                    $tab_name = $meta_array['tab_name'];
                    $tab_id = $meta_array['tab_id'];

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
     * @since 1.0.0
     */
    public function delete_feed_forminator() {
        try {
            check_ajax_referer( 'frmntr-gs-ajax-nonce', 'security' );
            $feedId = intval($_POST['feed_id']);
            
            if ($feedId) {
                $deleted = delete_metadata('post', $feedId, 'forminator_forms_feed_details');
                $deleted1 = delete_metadata_by_mid('post', $feedId);

                if ( $deleted1) {
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

    public function forminator_success_notice() {
    try{
         $success_msg = GS_FORMNTR_Free_Utility::instance()->admin_notice( array(
        'type'       => 'update',
        'message'    => __( 'Forminator Data Settings saved successfully.', 'gsheetconnector-forminator' )
         ) );
         echo $success_msg;
         } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return;
            
        }
     }

    /**
     * // save feed settings in table.
     *
     * @since 1.0.0
     */
    public function execute_post_data() {
        try {
            if (isset($_POST['execute-edit-feed-forminator'])) {
                  // nonce check
                if ( ! wp_verify_nonce( $_POST['frmntr-form-gs-ajax-nonce'], 'frmntr-form-gs-ajax-nonce' ) ) {
                       wp_die( 'Invalid nonce' ); // Die with an error message if nonce fails verification
                 }

                // Check if the user is logged in and has permissions to edit feeds
                if (!is_user_logged_in() || !current_user_can('edit_posts')) {
                    echo 'You do not have permission to edit feeds.';
                    exit;
                }
               

                // Get the feed ID and form ID from the form
                $feed_id = isset($_POST['edit_feed_id']) ? sanitize_text_field($_POST['edit_feed_id']) : "";
                $form_id = isset($_POST['edit_form_id']) ? sanitize_text_field($_POST['edit_form_id']) : "";

                // Get the updated feed data from the form
                $sheet_name = isset($_POST['edit_sheet_name']) ? sanitize_text_field($_POST['edit_sheet_name']) : "";
                $sheet_id = isset($_POST['edit_sheet_id']) ? sanitize_text_field($_POST['edit_sheet_id']) : "";
                $tab_name = isset($_POST['edit_tab_name']) ? sanitize_text_field($_POST['edit_tab_name']) : "";
                $tab_id = isset($_POST['edit_tab_id']) ? sanitize_text_field($_POST['edit_tab_id']) : "";

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
                    add_action( 'admin_notices', array( $this, 'forminator_success_notice' ) );
                }
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
    }

    public function forminator_feed_success_notice() {
      try{
         $success_msg = GS_FORMNTR_Free_Utility::instance()->admin_notice( array(
        'type'       => 'update',
        'message'    => __( 'Feed added successfully.', 'gsheetconnector-forminator' )
         ) );
         echo $success_msg;
         } catch (Exception $e) {
            return;
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
     }

      public function forminator_feed_error_notice() {
        try{
         $success_msg = GS_FORMNTR_Free_Utility::instance()->admin_notice( array(
        'type'       => 'error',
        'message'    => __( 'Feed name already exists in the list, Please enter unique name of feed.', 'gsheetconnector-forminator' )
         ) );
         echo $success_msg;
         } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return;
            
        }
     }

    /**
     * Adding Feed.
     *
     * @since 1.0.0
     */

    public function add_form_feed_name(){
        try{
            // Check if the form has been submitted
            if (isset($_POST['execute-submit-feed-forminator'])) {
            // nonce check
              if ( ! wp_verify_nonce( $_POST['frmntr-gs-feed-ajax-nonce'], 'frmntr-gs-feed-ajax-nonce' ) ) {
                       wp_die( 'Invalid nonce' ); // Die with an error message if nonce fails verification
               }

                // Sanitize the input
                $feed_name = sanitize_text_field($_POST['feed_name']);
                $form_id = isset(($_GET['form_id'])) ? sanitize_text_field(intval($_GET['form_id'])) : "";

                // Insert the feed name and form ID into the database
                if($form_id != ""){
                    $meta_key = 'forminator_forms_feed';
                    $meta_value = array(
                        'feed_name' => $feed_name,
                        'form_id' => $form_id,
                    );
                    add_post_meta($form_id, $meta_key, $meta_value);
                    add_action( 'admin_notices', array( $this, 'forminator_feed_success_notice' ) );
                }
                else{
                   add_action( 'admin_notices', array( $this, 'forminator_feed_error_notice' ) );
                }
                
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
    }

    public function get_feed_details(){
        $feed_data = array();
        try{
            $form_id = isset(($_GET['form_id'])) ? sanitize_text_field(intval($_GET['form_id'])) : "";
            global $wpdb;
            $table_name = $wpdb->prefix . 'postmeta';
            $query = "SELECT post_id, meta_id, meta_value FROM $table_name WHERE meta_key = 'forminator_forms_feed' AND post_id = $form_id";
            $feed_data = $wpdb->get_results($query, ARRAY_A);
            
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
        return $feed_data;
    }

    public function get_form_list(){
        $forms =array();
        try{
            global $wpdb;
            $forms_table = $wpdb->prefix  . 'posts';
            $forms = $wpdb->get_results( "SELECT * FROM $forms_table WHERE post_type = 'forminator_forms' AND (post_status = 'publish' OR post_status = 'draft')" );
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
        }
        return $forms;
    }

    /**
     * Function - fetch contant form list that is connected with google sheet
     * @since 1.0
     */

     public function get_forms_connected_to_sheet(){
        global $wpdb;

        $query = $wpdb->get_results( "SELECT ID,post_title,meta_value,meta_key,meta_id from " . $wpdb->prefix . "posts as p JOIN " . $wpdb->prefix . "postmeta as pm on p.ID = pm.post_id where pm.meta_key='forminator_forms_feed' AND p.post_type='forminator_forms'" );
        return $query;
    }
}
     
$gs_FORMNTR_service = new GS_FORMNTR_Service();
$gs_FORMNTR_service->init();