<?php

if (!defined('ABSPATH'))
    exit;
class FORMI_GSC_googlesheet
{

    private $token;
    private $spreadsheet;
    private $worksheet;
    /**
     *  Set things up.
     *  @since 1.0.15
     */

    public function __construct() {}
 
      /**
    * Thin wrapper around wp_remote_request() for Google REST calls.
    *
    * @param string $method HTTP method.
    * @param string $url    Full REST endpoint URL.
    * @param string $token  Bearer access token.
    * @param array  $args   Extra wp_remote_request() args (body/headers).
    * @return array|WP_Error Decoded JSON body, or WP_Error on failure.
    */
   private static function request($method, $url, $token, $args = array())
   {
      $args['method']  = $method;
      $args['headers'] = array_merge(
         array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
         ),
         isset($args['headers']) ? $args['headers'] : array()
      );

      if (isset($args['body']) && is_array($args['body'])) {
         $args['body'] = wp_json_encode($args['body']);
      }

      $response = wp_remote_request($url, $args);

      if (is_wp_error($response)) {
         return $response;
      }

      $code = wp_remote_retrieve_response_code($response);
      $body = json_decode(wp_remote_retrieve_body($response), true);

      if ($code < 200 || $code >= 300) {
         $message = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown Google API error.';
         return new WP_Error('gsc_api_error', $message, array('status' => $code, 'body' => $body));
      }

      return $body;
   }


    private static function base64url($data)
   {
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
   }


    private static function creds()
   {
   return is_multisite()
   ? get_site_option('forminatorgsc_api_creds')
   : get_option('forminatorgsc_api_creds');
   }



   /**
    * Retrieve the client ID/secret pair to use for OAuth requests.
    *
    * @return array{0: string, 1: string} [client_id, client_secret]
    */
   private static function client_credentials()
   {
      $creds           = self::creds();
      $newClientSecret = get_option('is_new_client_secret_FORMINGSC');

      $clientId     = ($newClientSecret == 1) ? $creds['client_id_web'] : $creds['client_id_desk'];
      $clientSecret = ($newClientSecret == 1) ? $creds['client_secret_web'] : $creds['client_secret_desk'];

      return array($clientId, $clientSecret);
   }


   /**
    * Fetch a spreadsheet's sheet/tab metadata via the Sheets REST API.
    *
    * @param string $spreadsheet_id Google Spreadsheet ID.
    * @return array|WP_Error List of sheet entries (each with a `properties` array), or WP_Error on failure.
    */
   private function get_spreadsheet_meta($spreadsheet_id)
   {
      $url = 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($spreadsheet_id) . '?fields=' . rawurlencode('sheets.properties');

      $body = self::request('GET', $url, $this->token);

      if (is_wp_error($body)) {
         return $body;
      }

      return isset($body['sheets']) ? $body['sheets'] : array();
   }



   /**
 * Preauthorize Google Client using the provided OAuth access code.
 *
 * Retrieves stored API credentials, initializes the Google Client,
 * exchanges the access code for an access token, and stores it.
 *
 * @param string $access_code OAuth authorization code.
 * @return void
 */
//constructed on call
   public static function preauth($access_code)
   {

      try {
         $creds = self::creds();
         if (!$creds) return;

         $response = wp_remote_post(
            'https://oauth2.googleapis.com/token',
            [
            'body' => [
               'code'          => $access_code,
               'client_id'     => $creds['client_id_web'],
               'client_secret' => $creds['client_secret_web'],
               'redirect_uri'  => 'https://oauth.gsheetconnector.com',
               'grant_type'    => 'authorization_code'
            ]
            ]
         );
         if (is_wp_error($response)) {
            return false;
         }

         $body = json_decode(wp_remote_retrieve_body($response), true);
         if (!is_array($body)) {
            $body = [];
         }

         self::updateToken($body);

         return !empty($body['access_token']);
      } catch (Exception $e) {
         GS_FORMNTR_Free_Utility::frmgs_debug_log('[Auth Exception]. ' . $e->getMessage());
         throw new LogicException('Auth error: ' . esc_html($e->getMessage()));
      }
   }



/**
 * Update and store the OAuth access token.
 *
 * Adds expiration time, validates required scopes,
 * and saves token data in WordPress options.
 *
 * @param array $tokenData Token data returned from Google OAuth.
 * @return void
 */
 public static function updateToken($tokenData)
   {
      $expires_in = isset($tokenData['expires_in']) ? intval($tokenData['expires_in']) : 0;
      $tokenData['expire'] = time() + $expires_in;
      try {

         if (isset($tokenData['scope'])) {
            $permission = explode(" ", $tokenData['scope']);
            if ((in_array("https://www.googleapis.com/auth/drive.metadata.readonly", $permission)) && (in_array("https://www.googleapis.com/auth/spreadsheets", $permission))) {
               update_option('gs_formntr_verify', 'valid');
            } else {
               update_option('gs_formntr_verify', 'invalid-auth');

                // Log permission error to error logs
                 if (class_exists('GSCFORMNTR_Free_Error_Logs')) {
                  GSCFORMNTR_Free_Error_Logs::log_to_db(
                    'Google_Auth_Permission_Error',
                    403,
                    'Google Drive and Google Sheets permissions not granted',
                    [
                      'error_type' => 'Missing Permissions',
                      'message' => 'User did not grant Google Drive and/or Google Sheets permissions during OAuth authentication',
                      'granted_scopes' => $tokenData['scope'] ?? '',
                      'required_drive_scope' => 'https://www.googleapis.com/auth/drive.file OR https://www.googleapis.com/auth/drive.metadata.readonly',
                      'required_sheets_scope' => 'https://www.googleapis.com/auth/spreadsheets',
                    ]
                  );
                }
            }
         }
         $tokenJson = json_encode($tokenData);
         update_option('gs_formntr_token', $tokenJson);


      } catch (Exception $e) {
         	GS_FORMNTR_Free_Utility::frmgs_debug_log("Token write fail! - " . $e->getMessage());
      }
   }


    /**
     * Generates a token for the user and refreshes it if expired.
     *
     * @since 1.0.15
     */

    public static function get_auth_url($frmingsc_clientId = '', $frmingsc_clientSecert = '')
    {
        $params = array(
            'client_id'       => $frmingsc_clientId,
            'redirect_uri'    => admin_url('admin.php?page=wpfrmin-google-sheet-config'),
            'response_type'   => 'code',
            'access_type'     => 'offline',
            'approval_prompt' => 'force',
            'scope'           => implode(' ', array(
                'https://www.googleapis.com/auth/drive.metadata.readonly',
                'https://www.googleapis.com/auth/spreadsheets',
                'https://www.googleapis.com/auth/userinfo.email',
            )),
        );

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }



    /**
     * Authenticates the user with Google Sheets API.
     * 
     * @throws LogicException If the OAuth2 access token is invalid or missing.
     * @since 1.0.15
     * */

    public function auth()
    {
        $maunal_setting = get_option('gs_formntr_manual_setting') != "" ? get_option('gs_formntr_manual_setting') : '0';
        if ($maunal_setting == '1')
            $tokenData = json_decode(get_option('gs_frmin_token_manual'), true);
        else
            $tokenData = json_decode(get_option('gs_formntr_token'), true);;
        //$tokenData = json_decode(get_option('gs_formntr_token'), true);
        if (!isset($tokenData['refresh_token']) || empty($tokenData['refresh_token'])) {
            throw new LogicException("Auth, Invalid OAuth2 access token");
            exit();
        }

        try {
            if ($maunal_setting == '1') {
                $clientId     = get_option('gs_frmin_client_id');
                $clientSecret = get_option('gs_frmin_secret_id');
            } else {
                list($clientId, $clientSecret) = self::client_credentials();
            }

            $response = wp_remote_post(
                'https://oauth2.googleapis.com/token',
                array(
                    'body' => array(
                        'refresh_token' => $tokenData['refresh_token'],
                        'client_id'     => $clientId,
                        'client_secret' => $clientSecret,
                        'grant_type'    => 'refresh_token',
                    ),
                )
            );

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $refreshed = json_decode(wp_remote_retrieve_body($response), true);

            if (empty($refreshed['access_token'])) {
                throw new Exception(isset($refreshed['error_description']) ? $refreshed['error_description'] : 'Unknown error refreshing token.');
            }

            // Google does not resend the refresh token on a refresh grant; keep the existing one.
            $refreshed['refresh_token'] = $tokenData['refresh_token'];

            if ($maunal_setting == '1')
                FORMI_GSC_googlesheet::updateToken_manual($refreshed);
            else
                FORMI_GSC_googlesheet::updateToken($refreshed);

            $this->token = $refreshed['access_token'];
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            throw new LogicException("Auth, Error fetching OAuth2 access token, message: " . esc_html($e->getMessage()));
            exit();
        }
    }

    /**
     * Updates the token data manually.
     * @param array $tokenData The token data to update.
     * @since 1.0.15
     * */

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

 
    //preg_match is a key of error handle in this case

    /**
     * Sets the Google Spreadsheet ID.
     *
     * @param string $id Spreadsheet ID.
     * @since 1.0.15
     */

    public function setSpreadsheetId($id)
    {
        $this->spreadsheet = $id;
    }

    /**
     * Retrieves the Google Spreadsheet ID.
     *
     * @return string Spreadsheet ID.
     * @since 1.0.15
     */

    public function getSpreadsheetId()
    {

        return $this->spreadsheet;
    }

    /**
     * Sets the Google Sheet tab (worksheet) ID.
     *
     * @param string $id Worksheet ID.
     * @since 1.0.15
     */

    public function setWorkTabId($id)
    {
        $this->worksheet = $id;
    }

    /**
     * Retrieves the Google Sheet tab (worksheet) ID.
     *
     * @return string Worksheet ID.
     * @since 1.0.15
     */

    public function getWorkTabId()
    {
        return $this->worksheet;
    }

    /**
     * Adds a new row to the Google Sheet.
     *
     * @param array $data             Row data to insert.
     * @param array $field_data_array  Original Forminator field data (returned as-is).
     * @param int   $feed_status       Feed status: 1 = enabled (send), 0 = disabled (skip). Default 1.
     * @since 1.0.15
     */

    public function add_row($data, $field_data_array, $feed_status = 1)
    {
        try {

            // Only push the submission to Google Sheets when the feed is enabled.
            if ((int) $feed_status === 0) {
                return $field_data_array;
            }

            $spreadsheetId = $this->getSpreadsheetId();
            $work_sheets = $this->get_spreadsheet_meta($spreadsheetId);

            if (is_wp_error($work_sheets)) {
                GS_FORMNTR_Free_Utility::frmgs_debug_log($work_sheets->get_error_message());
                return $field_data_array;
            }

            if (!empty($work_sheets) && !empty($data)) {
                foreach ($work_sheets as $sheet) {
                    $properties = isset($sheet['properties']) ? $sheet['properties'] : array();
                    $sheet_id = isset($properties['sheetId']) ? $properties['sheetId'] : null;

                    $worksheet_id = $this->getWorkTabId();

                    if ($sheet_id == $worksheet_id) {
                        $tab_name = $properties['title'];

                        $header_url = 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($spreadsheetId)
                            . '/values/' . rawurlencode($tab_name . '!1:1');
                        $worksheetCell = self::request('GET', $header_url, $this->token);

                        $insert_data = array();
                        if (!is_wp_error($worksheetCell) && isset($worksheetCell['values'][0])) {
                            foreach ($worksheetCell['values'][0] as $k => $name) {
                                if (isset($data[$name]) && $data[$name] != '') {
                                    $insert_data[] = $data[$name];
                                } else {
                                    $insert_data[] = '';
                                }
                            }
                        }

                        $full_range = $tab_name . "!A1:Z";
                        $values_url = 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($spreadsheetId)
                            . '/values/' . rawurlencode($full_range);
                        $response = self::request('GET', $values_url, $this->token);
                        $get_values = (!is_wp_error($response) && isset($response['values'])) ? $response['values'] : null;

                        if ($get_values) {
                            $row = count($get_values) + 1;
                        } else {
                            $row = 1;
                        }
                        $range = $tab_name . "!A" . $row . ":Z";

                        // append the spreadsheet(add new row in the sheet)
                        $append_url = 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($spreadsheetId)
                            . '/values/' . rawurlencode($range) . ':append?valueInputOption=USER_ENTERED';

                        $result = self::request('POST', $append_url, $this->token, array(
                            'body' => array('values' => array($insert_data)),
                        ));

                        if (is_wp_error($result)) {
                            GS_FORMNTR_Free_Utility::frmgs_debug_log($result->get_error_message());
                        }
                    }
                }
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return $field_data_array;
            //exit();
        }
    }

    /**
     * Retrieves all Google Spreadsheets from the connected account.
     *
     * @since 1.0.15
     */

    public function get_spreadsheets()
    {
        $all_sheets = array();
        try {
            $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query(array(
                'q'      => "mimeType='application/vnd.google-apps.spreadsheet'",
                'fields' => 'files(id,name,kind)',
            ));

            $results = self::request('GET', $url, $this->token);

            if (is_wp_error($results)) {
                GS_FORMNTR_Free_Utility::frmgs_debug_log($results->get_error_message());
                return null;
            }

            $files = isset($results['files']) ? $results['files'] : array();
            foreach ($files as $spreadsheet) {
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

    /**
     * Retrieves all work tabs (sheets) from a specific Google Spreadsheet.
     *
     * @since 1.0.15
     */

    public function get_worktabs($spreadsheet_id)
    {
        $work_tabs_list = array();
        try {
            $work_sheets = $this->get_spreadsheet_meta($spreadsheet_id);

            if (is_wp_error($work_sheets)) {
                GS_FORMNTR_Free_Utility::frmgs_debug_log($work_sheets->get_error_message());
                return null;
            }

            foreach ($work_sheets as $sheet) {
                $properties = isset($sheet['properties']) ? $sheet['properties'] : array();
                $work_tabs_list[] = array(
                    'id' => isset($properties['sheetId']) ? $properties['sheetId'] : null,
                    'title' => isset($properties['title']) ? $properties['title'] : '',
                );
            }
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return null;
            exit();
        }

        return $work_tabs_list;
    }

    /**
     * Fetches all spreadsheets from the connected Google account.
     *
     * @since 1.0.15
     */

    public function sync_with_google_account()
    {
        return;

        $return_ajax = false;

        if (isset($_POST['isajax']) && $_POST['isajax'] == 'yes') {
            check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
            // $init = sanitize_text_field($_POST['isinit']);
            if (isset($_POST['isinit'])) {
                $init = sanitize_text_field(wp_unslash($_POST['isinit']));
            }

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
     * Retrieves the connected Google account details.
     *
     * @since 1.0.15
     * @return object $user Google account information.
     */

    public function gsheet_get_google_account()
    {

        try {
            if (empty($this->token)) {
                return false;
            }

            $user = self::request('GET', 'https://www.googleapis.com/oauth2/v2/userinfo', $this->token);

            if (is_wp_error($user)) {
                GS_FORMNTR_Free_Utility::frmgs_debug_log(__METHOD__ . " Error in fetching user info: \n " . $user->get_error_message());
                return false;
            }

            $user = (object) $user;
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log(__METHOD__ . " Error in fetching user info: \n " . $e->getMessage());
            return false;
        }

        return $user;
    }

    /**
     * Retrieves the connected Google account email address.
     *
     * @since 1.0.15
     * @return string $email Google account email.
     */

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
     * Prints the connected Google account email address.
     *
     * @since 1.0.15
     * @return string $google_account Google account email.
     */

    public function gsheet_print_google_account_email()
    {
        try {
           
            $google_sheet = new FORMI_GSC_googlesheet();
            $google_sheet->auth();
            $email = $google_sheet->gsheet_get_google_account_email();
            update_option('frmingf_email_account', $email);
            return $email;
        
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            return false;
        }
    }

    /**
     * Builds the Google OAuth2 authorization URL.
     *
     * @since 1.0.15
     *
     * @param int    $flag                  Flag to determine if debug logging is enabled.
     * @param string $gscfrmin_clientId     Google Client ID.
     * @param string $gscfrmin_clientSecret Google Client Secret.
     *
     * @return string|false Authorization URL, or false on failure.
     */

    public static function getClient_auth($flag = 0, $gscfrmin_clientId = '', $gscfrmin_clientSecert = '')
    {
        try {
            return self::get_auth_url($gscfrmin_clientId, $gscfrmin_clientSecert);
        } catch (Exception $e) {
            if ($flag) {
                GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
                return $e->getMessage();
            } else {
                return false;
            }
        }
    }

    /**
     * Revokes the Google OAuth token (auto mode).
     *
     * Fetches API credentials, initializes Google Client, and revokes the provided access token.
     *
     * @param string $access_code JSON-encoded access token data.
     * @since 1.0.15
     */

    public static function revokeToken_auto($access_code)
    {
        $tokendecode = json_decode($access_code);

        if (empty($tokendecode->access_token)) {
            return;
        }

        $response = wp_remote_post(
            'https://oauth2.googleapis.com/revoke',
            array(
                'body' => array('token' => $tokendecode->access_token),
            )
        );

        if (is_wp_error($response)) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($response->get_error_message());
        }
    }
}
