<?php
/*
 * Process class for edd google sheet connector pro
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
class GS_Formntr_Processes {
    public function __construct() {
        add_action('wp_ajax_verify_gs_formntr_integation', array($this, 'verify_gs_formntr_integation'));
        //deactivate google sheet integration
        add_action('wp_ajax_deactivate_gs_formntr_integation', array($this, 'deactivate_gs_formntr_integation'));
        // clear debug log data
        add_action('wp_ajax_gs_formntr_clear_logs', array($this, 'gs_formntr_clear_logs'));
        // clear debug logs method using ajax for system status tab
        add_action('wp_ajax_frm_clear_debug_logs', array($this, 'frm_clear_debug_logs'));
        // get sheet name and tab name
        add_action('wp_ajax_sync_formntr_google_account', array($this, 'sync_formntr_google_account'));
        // get sheet names
        add_action('wp_ajax_get_tab_list', array($this, 'get_formntr_tab_list_by_sheetname'));
    
     }

    


   
    /**
     * AJAX function - verifies the token
     *
     * @since 1.0
     */
    public function verify_gs_formntr_integation($Code="") {
       try{
              // nonce checksave_gs_settings
              check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
              /* sanitize incoming data */
              $Code = sanitize_text_field($_POST["gs_formntr_code"]);

              if (!empty($Code)) {
                 update_option('gs_formntr_access_code', $Code);
              } else {
                 return;
              }
              if (get_option('gs_formntr_access_code') != '') {
                 include_once( GS_FORMNTR_ROOT . '/lib/google-sheets.php');
                 FORMI_GSC_googlesheet::preauth(get_option('gs_formntr_access_code'));
                 //update_option('ffforms_gs_verify', 'valid');
                 wp_send_json_success();
              } else {
                 update_option('gs_formntr_verify', 'invalid');
                 wp_send_json_error();
              } 
       } catch (Exception $e) {
         GS_FORMNTR_Free_Utility::frmgs_debug_log("Something Wrong : - " . $e->getMessage());
         wp_send_json_error();
      } 
   }

   
    /**
     * AJAX function - deactivate activation
     * @since 1.2
     */
    public function deactivate_gs_formntr_integation() {
        // nonce check
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
        if (get_option('gs_formntr_token') !== '') {
            delete_option('gs_formntr_feeds');
            delete_option('gs_formntr_sheetId');
            delete_option('gs_formntr_token');
            delete_option('gs_formntr_verify');
            delete_option('gs_formntr_access_code');
            update_option('gs_formntr_manual_setting', '0');
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
        //}
    }
   
    /**
     * AJAX function - clear log file
     * @since 1.0
     */
    public function gs_formntr_clear_logs() {
        // nonce check
      check_ajax_referer( 'frmntr-gs-ajax-nonce', 'security' );
      $existDebugFile = get_option('frmgs_debug_log');
      $clear_file_msg ='';
      // check if debug unique log file exist or not then exists to clear file
      if (!empty($existDebugFile) && file_exists($existDebugFile)) {
       
         $handle = fopen ( $existDebugFile, 'w');
        
        fclose( $handle );
        $clear_file_msg ='Logs are cleared.';
       }
       else{
        $clear_file_msg = 'No log file exists to clear logs.';
       }
     
      
      wp_send_json_success($clear_file_msg);
    }

    /**
    * AJAX function - clear log file for system status tab
    * @since 2.1
    */
    public function frm_clear_debug_logs() {
        // nonce check
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
        $handle = fopen(WP_CONTENT_DIR . '/debug.log', 'w');
        fclose($handle);
        wp_send_json_success();
    }
    /**
     * Function - sync with google account to fetch sheet and tab name
     * @since 1.0
     */
    public function sync_formntr_google_account() {
        $return_ajax = false;
        if (isset($_POST['isajax']) && $_POST['isajax'] == 'yes') {
            // nonce check
            check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
            $init = sanitize_text_field($_POST['isinit']);
            $return_ajax = true;
        }
        include_once( GS_FORMNTR_ROOT . '/lib/google-sheets.php');
        $doc = new GSC_Formntr_Googlesheet();
        $doc->auth();
        // Get all spreadsheets
        $spreadsheetFeed = $doc->get_spreadsheets();
        foreach ($spreadsheetFeed as $sheetfeeds) {
            $sheetId = $sheetfeeds['id'];
            $sheetname = $sheetfeeds['title'];
            $sheet_array[$sheetId] = array(
                "sheet_name" => $sheetname
            );
        }
        update_option('gs_formntr_sheet_feeds', $sheet_array);
        if ($return_ajax == true) {
            if ($init == 'yes') {
                wp_send_json_success(array("success" => 'yes'));
            } else {
                wp_send_json_success(array("success" => 'no'));
            }
        }
    }
    /**
     * AJAX function - Fetch tab list by sheet name
     * @since 1.0
     */
    public function get_formntr_tab_list_by_sheetname() {
        // nonce check
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
        $sheetname = sanitize_text_field($_POST['sheetname']);
        $sheet_data = get_option('gs_formntr_feeds');
        $html = "";
        $tablist = "";
        if (!empty($sheet_data) && array_key_exists($sheetname, $sheet_data)) {
            $tablist = $sheet_data[$sheetname];
        }
        if (!empty($tablist)) {
            $html = '<option value="">' . __("Select", "gs-edd") . '</option>';
            foreach ($tablist as $tab) {
                $html .= '<option value="' . $tab . '">' . $tab . '</option>';
            }
        }
        wp_send_json_success(htmlentities($html));
    }
   
   
}
$gs_processes = new GS_Formntr_Processes();