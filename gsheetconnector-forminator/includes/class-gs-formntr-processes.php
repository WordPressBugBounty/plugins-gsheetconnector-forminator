<?php

/*
 * Process class for edd google sheet connector pro
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

class GS_Formntr_Processes
{

    /**
     *  Set things up.
     *  @since 1.0.15
     */

    public function __construct()
    {
        /* verify google sheet integration */
        add_action('wp_ajax_verify_gs_formntr_integation', array($this, 'verify_gs_formntr_integation'));

        /* deactivate google sheet integration */
        add_action('wp_ajax_deactivate_gs_formntr_integation', array($this, 'deactivate_gs_formntr_integation'));

        /* clear debug log data */
        add_action('wp_ajax_gs_formntr_clear_logs', array($this, 'gs_formntr_clear_logs'));

        /*  clear debug logs method using ajax for system status tab */
        add_action('wp_ajax_frm_clear_debug_logs', array($this, 'frm_clear_debug_logs'));

        /*  get sheet name and tab name */
        add_action('wp_ajax_sync_formntr_google_account', array($this, 'sync_formntr_google_account'));

        /*  get sheet names */
        add_action('wp_ajax_get_tab_list', array($this, 'get_formntr_tab_list_by_sheetname'));

        /** Hide pro bar  */
        add_action('wp_ajax_dismiss_frmntr_pro_notice', array($this, 'dismiss_frmntr_pro_notice'));

        /** For getting details connected sheet  */
        add_action('wp_ajax_formntr_paginate_feed_list', array($this, 'formntr_paginate_feed_list'));

        /** Disable Notication Bar */
        add_action('wp_ajax_formntr_dismiss_notice', array($this, 'formntr_dismiss_notice'));

        /* Snooze notitiacation Bar */
        add_action('wp_ajax_formntr_snooze_notice', array($this, 'formntr_snooze_notice'));

        /** Clear Error Log  */
        add_action('wp_ajax_formntr_clear_error_logs', array($this, 'formntr_clear_error_logs'));

        /** Update general setting */
        add_action('wp_ajax_formntr_save_uninstall_settings', array($this, 'formntr_save_uninstall_settings'));

        /** Clear debug log */
        add_action('wp_ajax_formntrp_clear_debug_logs', array($this, 'formntrp_clear_debug_logs'));

        /** Create Forminator form Feed */
        add_action('wp_ajax_formntr_free_save_feed', array($this, 'formntr_free_save_feed'));

        /** Update Forminator form feed */
        add_action('wp_ajax_formntr_update_feed_name', array($this, 'formntr_update_feed_name'));

        /** Delete Forminator form feed */
        add_action('wp_ajax_formntr_free_feed_delete_ajax', array($this, 'formntr_free_feed_delete_ajax'));

        /** Enable / disable a Forminator form feed */
        add_action('wp_ajax_formntr_feed_status_toggle', array($this, 'formntr_feed_status_toggle'));
    }

    /**
     * AJAX function - verifies the token
     *
     * @since 1.0.15
     */

    public function verify_gs_formntr_integation($Code = "")
    {
        try {
            // nonce checksave_gs_settings
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
            /* sanitize incoming data */
            if (isset($_POST['gs_formntr_code'])) {
                $Code = sanitize_text_field(wp_unslash($_POST['gs_formntr_code']));
            }

            if (!empty($Code)) {
                update_option('gs_formntr_access_code', $Code);
            } else {
                return;
            }
            if (get_option('gs_formntr_access_code') != '') {
                include_once(GS_FORMNTR_ROOT . '/lib/google-sheets.php');
                FORMI_GSC_googlesheet::preauth(get_option('gs_formntr_access_code'));

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
     * @since 1.0.15
     */

    public function deactivate_gs_formntr_integation()
    {
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
     * @since 1.0.15
     */

    public function gs_formntr_clear_logs()
    {
        // nonce check
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
        $existDebugFile = get_option('frmgs_debug_log');
        $clear_file_msg = '';
        // check if debug unique log file exist or not then exists to clear file
        if (!empty($existDebugFile) && file_exists($existDebugFile)) {

            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }
            // $wp_filesystem->put_contents($existDebugFile, '', FS_CHMOD_FILE);
            if ($wp_filesystem->exists($existDebugFile)) {
                $wp_filesystem->delete($existDebugFile);
            }
            delete_option('frmgs_debug_log');


            $clear_file_msg = 'Logs are cleared.';
        } else {
            $clear_file_msg = 'No log file exists to clear logs.';
        }


        wp_send_json_success($clear_file_msg);
    }

    /**
     * AJAX function - clear log file for system status tab
     * @since 1.0.15
     */

    public function frm_clear_debug_logs()
    {
        // nonce check
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $wp_filesystem->put_contents(WP_CONTENT_DIR . '/debug.log', '', FS_CHMOD_FILE);

        wp_send_json_success();
    }

    /**
     * Function - sync with google account to fetch sheet and tab name
     * @since 1.0.15
     */

    public function sync_formntr_google_account()
    {
        $return_ajax = false;
        if (isset($_POST['isajax']) && $_POST['isajax'] == 'yes') {
            // nonce check
            check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
            // $init = sanitize_text_field($_POST['isinit']);
            if (isset($_POST['isinit'])) {
                $init = sanitize_text_field(wp_unslash($_POST['isinit']));
            }
            $return_ajax = true;
        }
        include_once(GS_FORMNTR_ROOT . '/lib/google-sheets.php');
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
     * @since 1.0.15
     */

    public function get_formntr_tab_list_by_sheetname()
    {
        // nonce check
        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');
        // $sheetname = sanitize_text_field($_POST['sheetname']); 
        if (isset($_POST['sheetname'])) {
            $sheetname = sanitize_text_field(wp_unslash($_POST['sheetname']));
        }
        $sheet_data = get_option('gs_formntr_feeds');
        $html = "";
        $tablist = "";
        if (!empty($sheet_data) && array_key_exists($sheetname, $sheet_data)) {
            $tablist = $sheet_data[$sheetname];
        }
        if (!empty($tablist)) {
            $html = '<option value="">' . esc_html__('Select', 'gsheetconnector-forminator') . '</option>';

            foreach ($tablist as $tab) {
                $html .= '<option value="' . $tab . '">' . $tab . '</option>';
            }
        }
        wp_send_json_success(htmlentities($html));
    }

    /**
     * AJAX function - dismiss the pro upgrade notice bar
     * @since 1.0.15
     */

    public function dismiss_frmntr_pro_notice()
    {

        /*$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';*/
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

        if (! wp_verify_nonce($nonce, 'frmntr-gs-ajax-nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        setcookie(
            'gsheet_frmntr_pro_notice_dismissed',
            '1',
            time() + (7 * 24 * 60 * 60),
            COOKIEPATH,
            COOKIE_DOMAIN
        );

        wp_send_json_success();
    }

    /**
     * AJAX function - fetch a paginated list of connected form feeds
     * @since 1.0.15
     */

    public function formntr_paginate_feed_list()
    {

        check_ajax_referer('formntr-ajax-nonce-pagination', 'security');
        $formntr_paged    = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
        $formntr_per_page = 3;

        $formntr_feed_sheet_list = GS_FORMNTR_Feed_Store::all();

        $formntr_result = $this->wpformntrreedb_render_feed_page($formntr_feed_sheet_list, $formntr_paged, $formntr_per_page);

        wp_send_json_success($formntr_result);
    }

    /**
     * Build the HTML for a single page of the feed list table plus its pagination links
     *
     * @since 1.0.15
     * @param array $formntr_feed_sheet_list Raw feed rows fetched from postmeta/posts.
     * @param int   $formntr_paged           Current page number being rendered.
     * @param int   $formntr_per_page        Number of feed rows to show per page.
     * @return array {
     *     @type string $rows_html       Rendered table rows for the current page.
     *     @type string $pagination_html Rendered pagination links.
     * }
     */

    function wpformntrreedb_render_feed_page($formntr_feed_sheet_list, $formntr_paged, $formntr_per_page)
    {
        $formntr_all_feeds = array();


        foreach ($formntr_feed_sheet_list as $value) {

            $gsSettings = is_array($value['data']) ? $value['data'] : array();

            $formntr_all_feeds[] = array(
                'form_id'    => $value['form_id'],
                'feed_id'    => $value['id'],
                'form_title' => get_the_title($value['form_id']),
                'feed_name'  => isset($value['feed_name']) ? $value['feed_name'] : '',
                'sheet_name' => isset($gsSettings['sheet_name']) ? $gsSettings['sheet_name'] : '',
                'sheet_id'   => isset($gsSettings['sheet_id']) ? $gsSettings['sheet_id'] : '',
                'tab_id'     => isset($gsSettings['tab_id']) ? $gsSettings['tab_id'] : '',
                'tab_name'   => isset($gsSettings['tab_name']) ? $gsSettings['tab_name'] : '',
            );
        }
			
			

        $formntr_total_rows  = count($formntr_all_feeds);
        $formntr_total_pages = (int) ceil($formntr_total_rows / $formntr_per_page);
        $formntr_paged       = max(1, min($formntr_paged, max(1, $formntr_total_pages)));
        $formntr_offset      = ($formntr_paged - 1) * $formntr_per_page;

        $formntr_list_paged = array_slice($formntr_all_feeds, $formntr_offset, $formntr_per_page);


        ob_start();
        if (!empty($formntr_list_paged)) {
            foreach ($formntr_list_paged as $feed) {
?>
                <tr>
                    <td><?php echo esc_html($feed['form_title']); ?></td>
                    <td>


                        <a href="<?php echo esc_url(admin_url('admin.php?page=formntr-gsheet-config&tab=google-sheet&form_id=' . urlencode($feed['form_id']) . '&feed_id=' . urlencode($feed['feed_id']))); ?>" target="_blank">
                            <?php echo esc_html($feed['feed_name']); ?>
                        </a>
                    </td>
                    <td>
                        <?php if (!empty($feed['sheet_id'])) { ?>
                            <a href="<?php echo esc_url('https://docs.google.com/spreadsheets/d/' . rawurlencode($feed['sheet_id']) . '/edit#gid=' . rawurlencode($feed['tab_id'])); ?>" target="_blank">
                                <?php echo esc_html($feed['sheet_name']); ?><?php echo !empty($feed['tab_name']) ? ' — ' . esc_html($feed['tab_name']) : ''; ?>
                            </a>
                        <?php } else { ?>
                            <span class="gscdf-not-connected"><?php echo esc_html__('Not connected', 'gsheetconnector-forminator'); ?></span>
                        <?php } ?>
                    </td>
                </tr>
            <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="3" class="formntr-feed-empty-cell">
                    <div class="formntr-feed-empty text-center">
                        <div class="heading">
                            <?php echo esc_html__('No Form Feeds Created Yet', 'gsheetconnector-forminator'); ?>
                        </div>
                        <p> <?php echo esc_html__('Connect your form to Google Sheets to automatically sync submissions in real time. Create a feed to start sending data to your spreadsheet.', 'gsheetconnector-forminator'); ?></p>
                        <a class="btn btn-primary link-hover-white formntr-create-feed-btn"
                            href="<?php echo esc_url(admin_url('admin.php?page=forminator-cform')); ?>" target="_blank">
                            <?php echo esc_html__('Create Feed', 'gsheetconnector-forminator'); ?>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
        }
        $formntr_rows_html = ob_get_clean();

        ob_start();
        if ($formntr_total_pages > 1) {
            for ($i = 1; $i <= $formntr_total_pages; $i++) {
            ?>
                <a href="javascript:void(0);"
                    class="text-decoration-none formntr-page-link <?php echo ($i === $formntr_paged) ? 'active' : ''; ?>"
                    data-page="<?php echo esc_attr($i); ?>">
                    <?php echo esc_html($i); ?>
                </a>
<?php
            }
        }
        $formntr_pagination_html = ob_get_clean();

        return [
            'rows_html'       => $formntr_rows_html,
            'pagination_html' => $formntr_pagination_html,
        ];
    }


    /**
     * AJAX function - Permanently dismiss a notification bar 
     * @since 1.0.15
     */

    public function formntr_dismiss_notice()
    {
        if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'frmntr-gs-ajax-nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if (!isset($_POST['key'])) {
            wp_send_json_error('Missing key');
        }

        $key = isset($_POST['key']) ? sanitize_text_field(wp_unslash($_POST['key'])) : '';

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        $key = sanitize_text_field($_POST['key']);
        update_option('formntr_notice_' . $key, 'dismissed');
        wp_send_json_success();
    }


    /**
     * AJAX function - Snooze a notification bar by storing its dismissal timestamp
     * @since 1.0.15
     */

    public function formntr_snooze_notice()
    {
        if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'frmntr-gs-ajax-nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        if (!isset($_POST['key'])) {
            wp_send_json_error('Missing key');
        }

        $key = sanitize_text_field(wp_unslash($_POST['key']));
        update_option('formntr_notice_' . $key . '_time', time());
        wp_send_json_success();
    }


    /**
     * AJAX function - Truncate the plugin's Error Log Database Table
     * @since 1.0.15
     */

    public function formntr_clear_error_logs()
    {
        if (
            !isset($_POST['nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'frmntr-gs-ajax-nonce')
        ) {
            wp_send_json_error(array(
                'message' => 'Security check failed.'
            ));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'gsformntr_error_logs';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching -- table-existence check performed immediately before a truncate; must always be fresh, caching risks acting on a stale existence result.
        $table_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare('SHOW TABLES LIKE %s', $table)
        );

        if ($table_exists !== $table) {
            wp_send_json_error(esc_html__('Error log table not found.', 'gsheetconnector-forminator'), 404);
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table is built entirely from $wpdb->prefix + a hardcoded literal ('gsformntr_error_logs'), no user input; TRUNCATE TABLE cannot use prepare() placeholders for identifiers.
        $wpdb->query("TRUNCATE TABLE {$table}");
        wp_send_json_success(array('message' => esc_html__('Logs are cleared.', 'gsheetconnector-forminator')));
    }

    /**
     * AJAX function - Save the "Remove Data on Uninstall" General Setting
     * @since 1.0.15
     */

    public function formntr_save_uninstall_settings()
    {
        check_ajax_referer('gs-formntr-setting-ajax-nonce', 'security');
        $value = isset($_POST['uninstall_setting']) ? intval($_POST['uninstall_setting']) : 0;
        update_option('gs_frmnt_unistall_plugin_settings', $value);
        wp_send_json_success();
    }

    /**
     * AJAX function - Clear the WordPress debug.log file
     * @since 1.0.15
     */

    public function formntrp_clear_debug_logs()
    {

        check_ajax_referer('frmntr-gs-ajax-nonce', 'security');

        /*  Initialize the WP_Filesystem global */
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }


        /*  Define log path */
        $debug_file = WP_CONTENT_DIR . '/debug.log';

        /*  If file exists and is writable, clear it; otherwise create an empty file */
        if ($wp_filesystem->exists($debug_file)) {
            if ($wp_filesystem->is_writable($debug_file)) {
                $result = $wp_filesystem->put_contents($debug_file, '', FS_CHMOD_FILE);
            } else {
                wp_send_json_error('Debug log is not writable', 403);
            }
        } else {
            /*  Create the file if it doesn’t exist */
            $result = $wp_filesystem->put_contents($debug_file, '', FS_CHMOD_FILE);
        }

        if (false === $result) {
            wp_send_json_error('Failed to clear debug log', 500);
        }

        wp_send_json_success();
    }

    /**
     * AJAX function - Create a new Forminator form feed
     * @since 1.0.15
     */

    public function formntr_free_save_feed()
    {
        try {
            check_ajax_referer('formntr-ajax-nonce', 'security');

            $feed_name = isset($_POST['feed_name'])
                ? sanitize_text_field(wp_unslash($_POST['feed_name']))
                : '';

            $form_id = isset($_POST['formntr'])
                ? absint(wp_unslash($_POST['formntr']))
                : 0;

            if (empty($feed_name) || empty($form_id)) {
                wp_send_json_error(__('Please enter a feed name and select a form.', 'gsheetconnector-forminator'));
            }

            if (GS_FORMNTR_Feed_Store::name_exists($form_id, $feed_name)) {
                wp_send_json_error(__('A feed with this name already exists for this form.', 'gsheetconnector-forminator'));
            }

            GS_FORMNTR_Feed_Store::create($form_id, $feed_name);

            wp_send_json_success(__('Feed added successfully.', 'gsheetconnector-forminator'));
        } catch (Exception $e) {
            GS_FORMNTR_Free_Utility::frmgs_debug_log($e->getMessage());
            wp_send_json_error(__('Something went wrong. Please try again.', 'gsheetconnector-forminator'));
        }
    }


    /**
     * AJAX function - Rename or Update an existing Forminator form feed
     * @since 1.0.15
     */

    function formntr_update_feed_name()
    {
        if (! check_ajax_referer('frmntr-gs-ajax-nonce', 'security', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token', 'gsheetconnector-forminator')
            ));
        }

        $feed_id   = isset($_POST['feed_id']) ? absint($_POST['feed_id']) : 0;
        $feed_name = isset($_POST['feed_name']) ? sanitize_text_field(wp_unslash($_POST['feed_name'])) : '';

        if (! $feed_id) {
            wp_send_json_error(array(
                'message' => __('Invalid feed ID', 'gsheetconnector-forminator')
            ));
        }

        if ('' === $feed_name) {
            wp_send_json_error(array(
                'message' => __('Feed name cannot be empty', 'gsheetconnector-forminator')
            ));
        }

        $feed = GS_FORMNTR_Feed_Store::get($feed_id);

        if (! $feed) {
            wp_send_json_error(array(
                'message' => __('Feed not found', 'gsheetconnector-forminator')
            ));
        }

        /* check for duplicate name on the same form, excluding this feed itself */
        if ($feed['form_id'] && GS_FORMNTR_Feed_Store::name_exists($feed['form_id'], $feed_name, $feed['id'])) {
            wp_send_json_error(array(
                'message' => __('A feed with this name already exists for this form.', 'gsheetconnector-forminator')
            ));
        }

        $updated = GS_FORMNTR_Feed_Store::rename($feed['id'], $feed_name);

        if (! $updated) {
            wp_send_json_error(array(
                'message' => __('Failed to update feed name', 'gsheetconnector-forminator')
            ));
        }

        wp_send_json_success(array(
            'feed_name' => $feed_name,
            'message'   => __('Feed name updated successfully', 'gsheetconnector-forminator')
        ));
    }


    /**
     * AJAX function - Delete a Forminator form feed
     * @since 1.0.15
     */

    public function formntr_free_feed_delete_ajax()
    {
        /* Verify nonce */
        if (! check_ajax_referer('frmntr-gs-ajax-nonce', 'security', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token', 'gsheetconnector-forminator')
            ));
        }

        $feed_id = isset($_POST['feed_id']) ? absint($_POST['feed_id']) : 0;

        if (! $feed_id) {
            wp_send_json_error(array(
                'message' => __('Invalid feed ID', 'gsheetconnector-forminator')
            ));
        }

        if (! GS_FORMNTR_Feed_Store::get($feed_id)) {
            wp_send_json_error(array(
                'message' => __('Feed not found', 'gsheetconnector-forminator')
            ));
        }

        $deleted = GS_FORMNTR_Feed_Store::delete($feed_id);

        if (! $deleted) {
            wp_send_json_error(array(
                'message' => __('Failed to delete feed', 'gsheetconnector-forminator')
            ));
        }

        wp_send_json_success(array(
            'message' => __('Feed deleted successfully', 'gsheetconnector-forminator')
        ));
    }

    /**
     * AJAX function - Enable or disable Google Sheet sync for a feed.
     *
     * @since 2.1.1
     */
    public function formntr_feed_status_toggle()
    {
        if (! check_ajax_referer('frmntr-gs-ajax-nonce', 'security', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token', 'gsheetconnector-forminator')
            ));
        }

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to change feed status.', 'gsheetconnector-forminator')
            ));
        }

        $feed_id = isset($_POST['feed_id']) ? absint($_POST['feed_id']) : 0;
        $status  = (isset($_POST['status']) && (int) $_POST['status'] === 1) ? 1 : 0;

        if (! $feed_id) {
            wp_send_json_error(array(
                'message' => __('Invalid feed ID', 'gsheetconnector-forminator')
            ));
        }

        if (! GS_FORMNTR_Feed_Store::get($feed_id)) {
            wp_send_json_error(array(
                'message' => __('Feed not found', 'gsheetconnector-forminator')
            ));
        }

        if (! GS_FORMNTR_Feed_Store::set_status($feed_id, $status)) {
            wp_send_json_error(array(
                'message' => __('Failed to update feed status', 'gsheetconnector-forminator')
            ));
        }

        wp_send_json_success(array(
            'status'  => $status,
            'message' => 1 === $status
                ? __('Feed enabled', 'gsheetconnector-forminator')
                : __('Feed disabled', 'gsheetconnector-forminator'),
        ));
    }
}
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$gs_formntr_processes = new GS_Formntr_Processes();
