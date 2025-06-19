<?php

if (!defined('ABSPATH'))
    exit;

include_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

class FORMI_GSC_googlesheet
{

    private $token;
    private $spreadsheet;
    private $worksheet;


    private static $instance;

    public function __construct()
    {

    }

    public static function setInstance(Google_Client $instance = null)
    {
        self::$instance = $instance;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            throw new LogicException("Invalid Client");
        }

        return self::$instance;
    }

    /**
     * Generate token for the user and refresh the token if it's expired.
     *
     * @return array
     */
    public static function get_auth_url($frmingsc_clientId = '', $frmingsc_clientSecert = '')
    {
        $frmingsc_client = new Google_Client();
        $frmingsc_client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $frmingsc_client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $frmingsc_client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $frmingsc_client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $frmingsc_client->setClientId($frmingsc_clientId);
        $frmingsc_client->setClientSecret($frmingsc_clientSecert);
        $frmingsc_client->setRedirectUri(esc_html(admin_url('admin.php?page=wpfrmin-google-sheet-config')));
        $frmingsc_client->setAccessType('offline');
        $frmingsc_client->setApprovalPrompt('force');
        try {
            $frmingsc_auth_url = $frmingsc_client->createAuthUrl();
            return $frmingsc_auth_url;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    //constructed on call
    public static function preauth($access_code)
    {
        if (is_multisite()) {
            // Fetch API creds
            $api_creds = get_site_option('forminatorgsc_api_creds');
        } else {
            // Fetch API creds
            $api_creds = get_option('forminatorgsc_api_creds');
        }
        $newClientSecret = get_option('is_new_client_secret_FORMINGSC');
        $clientId = ($newClientSecret == 1) ? $api_creds['client_id_web'] : $api_creds['client_id_desk'];
        $clientSecret = ($newClientSecret == 1) ? $api_creds['client_secret_web'] : $api_creds['client_secret_desk'];

        $client = new Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri('https://oauth.gsheetconnector.com');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $client->setScopes(Google_Service_Oauth2::USERINFO_EMAIL);
        $client->setAccessType('offline');
        $token = $client->fetchAccessTokenWithAuthCode($access_code);
        $tokenData = $client->getAccessToken();

        FORMI_GSC_googlesheet::updateToken($tokenData);
    }

    public static function updateToken($tokenData)
    {
        $expires_in = isset($tokenData['expires_in']) ? intval($tokenData['expires_in']) : 0;
        $tokenData['expire'] = time() + $expires_in;
        try {
            $tokenJson = json_encode($tokenData);
            update_option('gs_formntr_token', $tokenJson);
            if (isset($tokenData['scope'])) {
                $permission = explode(" ", $tokenData['scope']);
                if ((in_array("https://www.googleapis.com/auth/drive.metadata.readonly", $permission)) && (in_array("https://www.googleapis.com/auth/spreadsheets", $permission))) {
                    update_option('gs_formntr_verify', 'valid');
                } else {
                    update_option('gs_formntr_verify', 'invalid-auth');
                }
            }
            //resolved - google sheet permission issues - END
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log("Token write fail! - " . $e->getMessage());
        }
    }

    public function auth()
    {
        $maunal_setting = get_option('gs_formntr_manual_setting') != "" ? get_option('gs_formntr_manual_setting') : '0';
        if ($maunal_setting == '1')
            $tokenData = json_decode(get_option('gs_frmin_token_manual'), true);
        else
            $tokenData = json_decode(get_option('gs_formntr_token'), true);

        ;
        //$tokenData = json_decode(get_option('gs_formntr_token'), true);
        if (!isset($tokenData['refresh_token']) || empty($tokenData['refresh_token'])) {
            throw new LogicException("Auth, Invalid OAuth2 access token");
            exit();
        }

        try {
            $client = new Google_Client();


            if ($maunal_setting == '1') {
                $gs_frmin_client_id = get_option('gs_frmin_client_id');
                $gs_frmin_secret_id = get_option('gs_frmin_secret_id');
                $client->setClientId($gs_frmin_client_id);
                $client->setClientSecret($gs_frmin_secret_id);

            } else {
                if (is_multisite()) {
                    // Fetch API creds
                    $api_creds = get_site_option('forminatorgsc_api_creds');
                } else {
                    // Fetch API creds
                    $api_creds = get_option('forminatorgsc_api_creds');
                }

                $newClientSecret = get_option('is_new_client_secret_FORMINGSC');
                $clientId = ($newClientSecret == 1) ? $api_creds['client_id_web'] : $api_creds['client_id_desk'];
                $clientSecret = ($newClientSecret == 1) ? $api_creds['client_secret_web'] : $api_creds['client_secret_desk'];

                $client->setClientId($clientId);
                $client->setClientSecret($clientSecret);
            }


            $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
            $client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
            $client->refreshToken($tokenData['refresh_token']);
            $client->setAccessType('offline');


            if ($maunal_setting == '1')
                FORMI_GSC_googlesheet::updateToken_manual($tokenData);
            else
                FORMI_GSC_googlesheet::updateToken($tokenData);


            self::setInstance($client);
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            throw new LogicException("Auth, Error fetching OAuth2 access token, message: " . $e->getMessage());
            exit();
        }
    }

    public static function updateToken_manual($tokenData)
    {
        $tokenData['expire'] = time() + intval($tokenData['expires_in']);
        try {
            $tokenJson = json_encode($tokenData);
            update_option('gs_frmin_token_manual', $tokenJson);
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log("Token write fail! - " . $e->getMessage());
        }
    }

    public function get_user_data()
    {
        $client = self::getInstance();

        $results = $this->get_spreadsheets();

        echo '<pre>';
        print_r($results);
        echo '</pre>';
        $spreadsheets = $this->get_worktabs('1mRuDMnZveDFQrmzHM9s5YkPA4F_dZkHJ1Gh81BvYB2k');
        echo '<pre>';
        print_r($spreadsheets);
        echo '</pre>';
        $this->setSpreadsheetId('1mRuDMnZveDFQrmzHM9s5YkPA4F_dZkHJ1Gh81BvYB2k');
        $this->setWorkTabId('Foglio1');
        $worksheetTab = $this->list_rows();
        echo '<pre>';
        print_r($worksheetTab);
        echo '</pre>';
    }

    //preg_match is a key of error handle in this case
    public function setSpreadsheetId($id)
    {
        $this->spreadsheet = $id;
    }

    public function getSpreadsheetId()
    {

        return $this->spreadsheet;
    }

    public function setWorkTabId($id)
    {
        $this->worksheet = $id;
    }

    public function getWorkTabId()
    {
        return $this->worksheet;
    }

    public function add_row($data, $field_data_array)
    {
        try {

            $client = self::getInstance();
            $service = new Google_Service_Sheets($client);
            $spreadsheetId = $this->getSpreadsheetId();
            $work_sheets = $service->spreadsheets->get($spreadsheetId);

            if (!empty($work_sheets) && !empty($data)) {
                foreach ($work_sheets as $sheet) {
                    $properties = $sheet->getProperties();
                    $sheet_id = $properties->getSheetId();

                    $worksheet_id = $this->getWorkTabId();

                    if ($sheet_id == $worksheet_id) {
                        $worksheet_id = $properties->getTitle();
                        $worksheetCell = $service->spreadsheets_values->get($spreadsheetId, $worksheet_id . "!1:1");
                        $insert_data = array();
                        if (isset($worksheetCell->values[0])) {
                            foreach ($worksheetCell->values[0] as $k => $name) {
                                if (isset($data[$name]) && $data[$name] != '') {
                                    $insert_data[] = $data[$name];
                                } else {
                                    $insert_data[] = '';
                                }
                            }
                        }

                        /* RASHID */
                        $tab_name = $worksheet_id;
                        $full_range = $tab_name . "!A1:Z";
                        $response = $service->spreadsheets_values->get($spreadsheetId, $full_range);
                        $get_values = $response->getValues();

                        if ($get_values) {
                            $row = count($get_values) + 1;
                        } else {
                            $row = 1;
                        }
                        $range = $tab_name . "!A" . $row . ":Z";

                        $range_new = $worksheet_id;

                        // Create the value range Object
                        $valueRange = new Google_Service_Sheets_ValueRange();

                        // set values of inserted data
                        $valueRange->setValues(["values" => $insert_data]);

                        // Add two values
                        // Then you need to add configuration
                        $conf = ["valueInputOption" => "USER_ENTERED", "insertDataOption" => "INSERT_ROWS"];
                        $conf = ["valueInputOption" => "USER_ENTERED"];

                        // append the spreadsheet(add new row in the sheet)
                        // $result = $service->spreadsheets_values->append( $spreadsheetId, $range_new, $valueRange, $conf );
                        $result = $service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $conf);
                    }
                }
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return $field_data_array;
            //exit();
        }
    }



    //get all the spreadsheets
    public function get_spreadsheets()
    {
        $all_sheets = array();
        try {
            $client = self::getInstance();

            $service = new Google_Service_Drive($client);

            $optParams = array(
                'q' => "mimeType='application/vnd.google-apps.spreadsheet'"
            );
            $results = $service->files->listFiles($optParams);
            foreach ($results->files as $spreadsheet) {
                if (isset($spreadsheet['kind']) && $spreadsheet['kind'] == 'drive#file') {
                    $all_sheets[] = array(
                        'id' => $spreadsheet['id'],
                        'title' => $spreadsheet['name'],
                    );
                }
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return null;
            exit();
        }
        return $all_sheets;
    }

    //get worksheets title
    public function get_worktabs($spreadsheet_id)
    {
        $work_tabs_list = array();
        try {
            $client = self::getInstance();
            $service = new Google_Service_Sheets($client);
            $work_sheets = $service->spreadsheets->get($spreadsheet_id);

            foreach ($work_sheets as $sheet) {
                $properties = $sheet->getProperties();
                $work_tabs_list[] = array(
                    'id' => $properties->getSheetId(),
                    'title' => $properties->getTitle(),
                );
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return null;
            exit();
        }

        return $work_tabs_list;
    }






    /*     * **************************************************************************** */
    /*     * ******************************  VERSION 3.1 ******************************** */
    /*     * **************************************************************************** */

    /**
     * GFGSC_googlesheet::sync_with_google_account
     * Fetch Spreadsheets
     * @since 3.1 
     * */
    public function sync_with_google_account()
    {
        return;

        $return_ajax = false;

        if (isset($_POST['isajax']) && $_POST['isajax'] == 'yes') {
            check_ajax_referer('gf-ajax-nonce', 'security');
            $init = sanitize_text_field($_POST['isinit']);
            $return_ajax = true;
        }

        include_once(GS_CONNECTOR_PRO_ROOT . '/lib/google-sheets.php');
        $worksheet_array = array();
        $sheetdata = array();
        $doc = new GFGSC_googlesheet();
        $doc->auth();
        $spreadsheetFeed = $doc->get_spreadsheets();

        if (!$spreadsheetFeed) {
            return false;
        }

        foreach ($spreadsheetFeed as $sheetfeeds) {
            $sheetId = $sheetfeeds['id'];
            $sheetname = $sheetfeeds['title'];

            $worksheetFeed = $doc->get_worktabs($sheetId);

            foreach ($worksheetFeed as $worksheet) {
                $tab_id = $worksheet['id'];
                $tab_name = $worksheet['title'];

                $worksheet_array[] = $tab_name;
                $worksheet_ids[$tab_name] = $tab_id;
            }

            $sheetId_array[$sheetname] = array(
                "id" => $sheetId,
                "tabId" => $worksheet_ids
            );

            unset($worksheet_ids);
            $sheetdata[$sheetname] = $worksheet_array;
            unset($worksheet_array);
        }

        update_option('gfgs_sheetId', $sheetId_array);
        update_option('gfgs_feeds', $sheetdata);

        if ($return_ajax == true) {
            if ($init == 'yes') {
                wp_send_json_success(array("success" => 'yes'));
            } else {
                wp_send_json_success(array("success" => 'no'));
            }
        }
    }

    /**
     * GFGSC_googlesheet::gsheet_get_google_account
     * Get Google Account
     * @since 3.1 
     * @retun $user
     * */
    public function gsheet_get_google_account()
    {

        try {
            $client = $this->getInstance();

            if (!$client) {
                return false;
            }

            $service = new Google_Service_Oauth2($client);
            $user = $service->userinfo->get();
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log(__METHOD__ . " Error in fetching user info: \n " . $e->getMessage());
            return false;
        }

        return $user;
    }

    /**
     * GFGSC_googlesheet::gsheet_get_google_account_email
     * Get Google Account Email
     * @since 3.1 
     * @retun string $email
     * */
    public function gsheet_get_google_account_email()
    {
        $google_account = $this->gsheet_get_google_account();

        if ($google_account) {
            return $google_account->email;
        } else {
            return "";
        }
    }

    /**
     * GFGSC_googlesheet::gsheet_print_google_account_email
     * Get Google Account Email
     * @since 3.1 
     * @retun string $google_account
     * */
    public function gsheet_print_google_account_email()
    {
        try {
            // $google_account = get_option("frmingf_email_account");

            // if ($google_account) {
            //     return $google_account;
            // } else {

            $google_sheet = new FORMI_GSC_googlesheet();
            $google_sheet->auth();
            $email = $google_sheet->gsheet_get_google_account_email();
            update_option('frmingf_email_account', $email);
            return $email;
            // }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return false;
        }
    }

    /**
     * Generate token for the user and refresh the token if it's expired.
     *
     * @return array
     */
    public static function getClient_auth($flag = 0, $gscfrmin_clientId = '', $gscfrmin_clientSecert = '')
    {
        $gscfrmin_client = new Google_Client();
        $gscfrmin_client->setApplicationName('Manage frmin Forms with Google Spreadsheet');
        $gscfrmin_client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $gscfrmin_client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $gscfrmin_client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $gscfrmin_client->addScope('https://www.googleapis.com/auth/userinfo.email');
        //$gscfrmin_client->addScope( 'https://www.googleapis.com/auth/userinfo.profile' );
        $gscfrmin_client->setClientId($gscfrmin_clientId);
        $gscfrmin_client->setClientSecret($gscfrmin_clientSecert);
        $gscfrmin_client->setRedirectUri(esc_html(admin_url('admin.php?page=wpfrmin-google-sheet-config')));
        $gscfrmin_client->setAccessType('offline');
        $gscfrmin_client->setApprovalPrompt('force');
        try {
            if (empty($gscfrmin_auth_token)) {
                $gscfrmin_auth_url = $gscfrmin_client->createAuthUrl();
                return $gscfrmin_auth_url;
            }
            if (!empty($gscfrmin_gscfrmin_accessToken)) {
                $gscfrmin_accessToken = json_decode($gscfrmin_gscfrmin_accessToken, true);
            } else {
                if (empty($gscfrmin_auth_token)) {
                    $gscfrmin_auth_url = $gscfrmin_client->createAuthUrl();
                    return $gscfrmin_auth_url;
                }

            }

            $gscfrmin_client->setAccessToken($gscfrmin_accessToken);
            // Refresh the token if it's expired.
            if ($gscfrmin_client->isAccessTokenExpired()) {
                // save refresh token to some variable
                $gscfrmin_refreshTokenSaved = $gscfrmin_client->getRefreshToken();
                $gscfrmin_client->fetchAccessTokenWithRefreshToken($gscfrmin_client->getRefreshToken());
                // pass access token to some variable
                $gscfrmin_accessTokenUpdated = $gscfrmin_client->getAccessToken();
                // append refresh token
                $gscfrmin_accessTokenUpdated['refresh_token'] = $gscfrmin_refreshTokenSaved;
                //Set the new acces token
                $gscfrmin_accessToken = $gscfrmin_refreshTokenSaved;
                gscfrmin::gscfrmin_update_option('frminsheets_google_accessToken', json_encode($gscfrmin_accessTokenUpdated));
                $gscfrmin_accessToken = json_decode(json_encode($gscfrmin_accessTokenUpdated), true);
                $gscfrmin_client->setAccessToken($gscfrmin_accessToken);
            }
        } catch (Exception $e) {
            if ($flag) {
                GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
                return $e->getMessage();
            } else {
                return false;
            }
        }
        return $gscfrmin_client;
    }

    public static function revokeToken_auto($access_code)
    {
        if (is_multisite()) {
            // Fetch API creds
            $api_creds = get_site_option('forminatorgsc_api_creds');
        } else {
            // Fetch API creds
            $api_creds = get_option('forminatorgsc_api_creds');
        }
        $newClientSecret = get_option('is_new_client_secret_FORMINGSC');
        $clientId = ($newClientSecret == 1) ? $api_creds['client_id_web'] : $api_creds['client_id_desk'];
        $clientSecret = ($newClientSecret == 1) ? $api_creds['client_secret_web'] : $api_creds['client_secret_desk'];

        $client = new Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $tokendecode = json_decode($access_code);
        $token = $tokendecode->access_token;
        $client->revokeToken($token);
    }

}