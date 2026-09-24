<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$form_id = '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if (isset($_GET['form_id'])) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $form_id = filter_var(wp_unslash($_GET['form_id']), FILTER_SANITIZE_NUMBER_INT);
}

$feed_id = '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if (isset($_GET['feed_id'])) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $feed_id = filter_var(wp_unslash($_GET['feed_id']), FILTER_SANITIZE_NUMBER_INT);
}


// $feed_id may be a new {prefix}forminatorgs_feeds.id or a legacy postmeta meta_id
// (old bookmarked URL) - the feed store resolves either.
$feed_record = GS_FORMNTR_Feed_Store::get($feed_id);
if ($feed_record) {
    $feed_id = $feed_record['id'];
}

$feed_data = ($feed_record && is_array($feed_record['data'])) ? $feed_record['data'] : array();
$feed_name = $feed_record ? $feed_record['feed_name'] : '';

// Feed creation timestamp (stored MySQL datetime) for the Feed Details panel.
$feed_created_raw = ($feed_record && isset($feed_record['created_at'])) ? $feed_record['created_at'] : '';
if ($feed_created_raw !== '' && $feed_created_raw !== '1970-01-01 00:00:00') {
    $feed_created_display = date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($feed_created_raw));// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

    $feed_tz_string = get_option('timezone_string');
    if ($feed_tz_string) {
        $feed_tz_label = $feed_tz_string;
    } else {
        $feed_gmt_offset = get_option('gmt_offset');
        $feed_tz_label = 'UTC' . ($feed_gmt_offset >= 0 ? '+' : '') . $feed_gmt_offset;
    }

    $feed_created_display .= ' (' . $feed_tz_label . ')';
} else {
    $feed_created_display = '—';
}



// Set the default values for the form fields
$sheet_name = isset($feed_data['sheet_name']) ? esc_attr($feed_data['sheet_name']) : '';
$sheet_id = isset($feed_data['sheet_id']) ? esc_attr($feed_data['sheet_id']) : '';
$tab_name = isset($feed_data['tab_name']) ? esc_attr($feed_data['tab_name']) : '';
$tab_id = isset($feed_data['tab_id']) ? esc_attr($feed_data['tab_id']) : '';

$form_id = intval($form_id);
$form_settings = Forminator_API::get_form($form_id);
$form = $form_settings->fields;

$form_name = get_the_title($form_id);

$selected_method = esc_html__('Use Existing Client/Secret Key (Auto Google API Configuration)', 'gsheetconnector-forminator');
$formntr_authenticated         = get_option('gs_formntr_token'); // Auto
$formntr_per                   = get_option('gs_formntr_verify');
$formntr_per_msg               = __('invalid', 'gsheetconnector-forminator');
$formntr_show_setting          = 0;
$formntr_selected_method       = '';
$formntr_email_account         = '';

$formntr_google_sheet =  new FORMI_GSC_googlesheet();
$formntr_email_account = $formntr_google_sheet->gsheet_print_google_account_email();
if (!empty($formntr_email_account) && $formntr_per == 'valid') {
    $formntr_selected_method = esc_html__('Use Existing Client/Secret Key (Auto Google API Configuration)', 'gsheetconnector-forminator');
    $formntr_is_authenticated = true;
} else {
    $formntr_selected_method = esc_html__('Not Connected', 'gsheetconnector-forminator');
}

?>
<div class="gsfrmnp-main-div">
    <div class="gsc-msg gsc-success d-none fw-400 text-dark text-center pt-10 pb-10"></div>
    <div class="gsc-msg gsc-error fw-400 d-none text-dark text-center pt-10 pb-10"></div>

    <div class="gsfrmnp-bread-crumb">
        <a href="<?php echo esc_url(admin_url('admin.php?page=formntr-gsheet-config&tab=google-sheet')); ?>" class="back-btn btn-spacer btn btn-primary text-decoration-none d-inline-flex align-center gap-10">
            <svg class="back-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span><?php echo esc_html__('Back to Feeds List', 'gsheetconnector-forminator'); ?></span>
        </a>
    </div>

    <?php if ($formntr_email_account != '') { ?>
        <div class="gsfrmnp-integration-box">
            <div class="gsc-google-auth-card mt-30 mb-30">
                <div>
                    <div class="heading mt-0 mb-30">
                        <?php echo esc_html__('Google Account Connection', 'gsheetconnector-forminator'); ?>
                        <span class="badge"><?php echo esc_html($formntr_selected_method); ?></span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-20 justify-between align-center">
                    <div class="gsc-google-auth-left d-flex flex-wrap align-center gap-15">
                        <div class="gsc-google-icon">G</div>
                        <div class="connected-account">
                            <div class="gsc-connected-left d-flex">
                                <span class="gsc-connected-label"><?php echo esc_html__('Connected Email Account', 'gsheetconnector-forminator'); ?></span>
                                <span class="connected-account-manual gsc-connected-email"><?php echo esc_html($formntr_email_account); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="gsc-google-auth-right">
                        <div class="gsc-connected-pill">
                            <span class="dot"></span>
                            <?php echo esc_html__('Connected', 'gsheetconnector-forminator'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <form id="edit-feed-form" method="post">

        <input type="hidden" id="edit-feed-id" name="edit_feed_id" value="<?php echo esc_attr($feed_id); ?>">
        <input type="hidden" id="edit-form-id" name="edit_form_id" value="<?php echo esc_attr($form_id); ?>">

        <!-- FEED DETAILS -->
        <div class="feed-informtion-inner shadow-box mt-40 p-30">
            <div class="heading mt-0"><?php echo esc_html__('Feed Details', 'gsheetconnector-forminator'); ?></div>
            <p><?php echo esc_html__('Displays the form and feed information used for Google Sheets synchronization.', 'gsheetconnector-forminator'); ?></p>

            <div class="gsc-info-grid mt-20">
                <div>
                    <div class="feed-info-heading mt-20"><?php echo esc_html__('Form ID', 'gsheetconnector-forminator'); ?></div>
                    <div class="feed-info-sub-head fw-700"><?php echo esc_html($form_id); ?></div>
                </div>
                <div>
                    <div class="feed-info-heading mt-20"><?php echo esc_html__('Feed Name', 'gsheetconnector-forminator'); ?></div>
                    <div class="feed-info-sub-head fw-700"><?php echo esc_html($feed_name); ?></div>
                </div>
                <div>
                    <div class="feed-info-heading mt-20"><?php echo esc_html__('Form Name', 'gsheetconnector-forminator'); ?></div>
                    <div class="feed-info-sub-head fw-700"><?php echo esc_html($form_name); ?></div>
                </div>
                <div>
                    <div class="feed-info-heading mt-20"><?php echo esc_html__('Timestamp', 'gsheetconnector-forminator'); ?></div>
                    <div class="feed-info-sub-head fw-700"><?php echo esc_html($feed_created_display); ?></div>
                </div>
            </div>
        </div>

        <div class="shadow-box mt-40 p-30">
            <div class="heading">
                <?php echo esc_html(__('Manual Google Sheets Configuration', 'gsheetconnector-forminator')); ?>
            </div>
            <p><?php echo esc_html(__("Connect your Google Sheet by entering the required sheet information.", 'gsheetconnector-forminator')); ?></p>
            <div class="row">
                <div class="col-6 res-top-20">
                    <div class="form-group field-row mr-10">
                        <label
                            for="edit-sheet-name"><?php echo esc_html(__('Sheet Name', 'gsheetconnector-forminator')); ?>
                            <span class="tooltip"
                                data-tooltip="<?php echo esc_html(__("Enter the exact name of your Google Spreadsheet (as shown in Google Sheets).", 'gsheetconnector-forminator')); ?>"
                                data-tooltip-pos="right" data-tooltip-length="medium">
                                <i class="fa-solid fa-circle-question help-icon"></i>
                            </span>
                        </label>
                        <input type="text" class="form-control" id="edit-sheet-name" name="edit_sheet_name"
                            value="<?php echo esc_attr($sheet_name); ?>">
                        <span class="field-error-msg" id="edit-sheet-name-error"></span>
                    </div>
                </div>
                <div class="col-6 res-top-20">
                    <div class="form-group field-row mr-10">
                        <label
                            for="edit-sheet-id"><?php echo esc_html(__('Sheet ID', 'gsheetconnector-forminator')); ?>
                            <span class="tooltip"
                                data-tooltip="<?php echo esc_html(__("Paste the Spreadsheet ID from your Google Sheet URL. (Example: ", 'gsheetconnector-forminator')); ?> https://docs.google.com/spreadsheets/d/**SPREADSHEET_ID**/edit)"
                                data-tooltip-pos="right" data-tooltip-length="medium">
                                <i class="fa-solid fa-circle-question help-icon"></i>
                            </span>
                        </label>
                        <input type="text" class="form-control" id="edit-sheet-id" name="edit_sheet_id"
                            value="<?php echo esc_attr($sheet_id); ?>">
                        <span class="field-error-msg" id="edit-sheet-id-error"></span>
                    </div>
                </div>
                <div class="col-6 mt-20">
                    <div class="form-group field-row mr-10">
                        <label
                            for="edit-tab-name"><?php echo esc_html(__('Tab Name', 'gsheetconnector-forminator')); ?>
                            <span class="tooltip"
                                data-tooltip="<?php echo esc_html(__("Enter the exact sheet tab name (e.g., Sheet1) from the bottom of your Google Spreadsheet.", 'gsheetconnector-forminator')); ?>"
                                data-tooltip-pos="right" data-tooltip-length="medium">
                                <i class="fa-solid fa-circle-question help-icon"></i>
                            </span>
                        </label>
                        <input type="text" class="form-control" id="edit-tab-name" name="edit_tab_name"
                            value="<?php echo esc_attr($tab_name); ?>">
                        <span class="field-error-msg" id="edit-tab-name-error"></span>
                    </div>
                </div>
                <div class="col-6 mt-20">
                    <div class="form-group field-row mr-10">
                        <label
                            for="edit-tab-id"><?php echo esc_html(__('Tab ID', 'gsheetconnector-forminator')); ?>
                            <span class="tooltip"
                                data-tooltip="<?php echo esc_html(__("Get the Tab ID from your sheet URL after gid=.", 'gsheetconnector-forminator')); ?>"
                                data-tooltip-pos="right" data-tooltip-length="medium">
                                <i class="fa-solid fa-circle-question help-icon"></i>
                            </span>
                        </label>
                        <input type="text" class="form-control" id="edit-tab-id" name="edit_tab_id"
                            value="<?php echo esc_attr($tab_id); ?>">
                        <span class="field-error-msg" id="edit-tab-id-error"></span>
                    </div> <!-- field row #end -->
                </div>




                <div class="sheet-url field-row mt-20" id="sheet-url">
                    <?php if ((isset($sheet_id) && $sheet_id != "") && (isset($tab_id) && $tab_id != "")) : ?>
                        <div class="d-flex align-center gap-10">
                            <a class="sheet-url-forminatorform common-sheet-url text-decoration-none ml-ten-btn"
                                href="<?php echo esc_url('https://docs.google.com/spreadsheets/d/' . $sheet_id . '/edit#gid=' . $tab_id); ?>"
                                target="_blank" hover-tooltip="<?php echo esc_attr__('View Spreadsheet', 'gsheetconnector-forminator'); ?>">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 5C6.5 5 2 12 2 12C2 12 6.5 19 12 19C17.5 19 22 12 22 12C22 12 17.5 5 12 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
                                </svg>
                            </a>
                            <span
                                class="download-url-forminatorform common-download-url text-dark fw-700 download-spreadsheet mr-20"
                                hover-tooltip="<?php echo esc_attr__('Spreadsheet Download (PRO)', 'gsheetconnector-forminator'); ?>">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 4V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M5 20H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>




                <?php $gs_formntr_auth_method = get_option('gs_formntr_manual_setting', '0'); ?>
                <div class="field-row mt-20" id="">
                    <input type="submit"
                        name="execute-edit-feed-forminator"
                        id="execute-edit-feed-forminator"
                        class="btn btn-primary common-disable"
                        value="<?php echo esc_html('Save Settings'); ?>">

                    <input type="hidden" name="frmntr-form-gs-ajax-nonce" id="frmntr-form-gs-ajax-nonce"
                        value="<?php echo esc_attr(wp_create_nonce('frmntr-form-gs-ajax-nonce')); ?>" />
                </div>
            </div>
        </div>
    </form>
</div>

<div class="feed-informtion-inner shadow-box mt-40 p-30">
    <!-- Free setting End -->
    <!----Start Pro Feed Setting Features----->
    <div class="gsc-pro-promo">

        <div class="gsc-pro-header">
            <div class="gsc-pro-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 19c-1 1-2 1-3 1 0-1 0-2 1-3l4-4"></path>
                    <path d="M14 3l7 7"></path>
                    <path d="M9 18l-4 4"></path>
                    <path d="M15 3c2 0 6 4 6 6-2 2-6 6-8 8l-6-6c2-2 6-8 8-8z"></path>
                    <circle cx="15" cy="9" r="1.5"></circle>
                </svg>

            </div>

            <div>
                <div class="unlock-header"><?php echo esc_html(__('Unlock Advanced Features with Form Feeds', 'gsheetconnector-forminator')); ?></div>
                <span class="gsc-pro-badge"><?php echo esc_html(__('FREE users get special upgrade pricing', 'gsheetconnector-forminator')); ?></span>
            </div>
        </div>

        <!-- Feature Tabs -->
        <div class="gsc-pro-tabs gsc-formntr-free-tabs pt-20 pb-20 pl-20 pr-20 ">
            <div>
                <div class="mb-20"><a href="#auto-googlesheet-configuration"><?php echo esc_html(__('Automatically Google Sheet Configuration', 'gsheetconnector-forminator')); ?></a></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Auto fetch Google Sheets list', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Auto detect sheet tabs', 'gsheetconnector-forminator'); ?> </li>
                        <li><?php esc_html_e('One-click configuration', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Real-time entry sync', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20"><a href="#field-mapping"><?php echo esc_html(__('Select Fields to Sync & Conditional Logic', 'gsheetconnector-forminator')); ?> </a>
                </div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Drag & drop field reordering', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Rename column headers', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Select specific fields to sync', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Apply rules based on form values', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Sync data only when conditions match', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20"><a href="#header-settings-sheet-sorting"><?php echo esc_html(__('Header Settings & Sheet Sorting', 'gsheetconnector-forminator')); ?></a></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Freeze header row', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Custom font styling', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Header & row color control', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Sort by any column', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20"><a href="#google-sheet-data-sync-div"><?php echo esc_html(__('Google Sheets Data Sync', 'gsheetconnector-forminator')); ?></a></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Bulk sync past entries', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Date range sync', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Real-time entry updates', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="gsc-pro-footer text-center">
            <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro"
                target="_blank"
                class="btn btn-primary text-decoration-none link-hover-white">
                <?php echo esc_html(__('Upgrade to Unlock', 'gsheetconnector-forminator')); ?>
            </a>
        </div>

    </div>
</div>

<div class="system-debug-logs" id="opener">
    <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" class="pro-link"
        target="_blank" style="text-decoration: none;"></a>

    <div class="auto-section shadow-box mt-40 p-30" id="auto-googlesheet-configuration" name="auto-googlesheet-configuration" style="display:block;">
        <div class="gsc-fields">
            <div class="sheet-details">
                <div class="heading mt-0"> <?php echo esc_html(__('Automatic Google Sheets Configurationn', 'gsheetconnector-forminator')); ?>
                    <span class="pro-ver"><?php esc_html_e('PRO', 'gsheetconnector-forminator'); ?></span>
                </div>
                <p><?php echo esc_html(__("Automatically configure your Google Sheets and start syncing form submissions in real time.", 'gsheetconnector-forminator')); ?></P>
                <div class="row">
                    <div class="col-4">
                        <div class="mr-10">
                            <label><?php echo esc_html(__('Sheet Name', 'gsheetconnector-forminator')); ?></label>
                            <select name="gscf-ff[gsc-fluentform-sheet-id]" class="auto-select-display w-100 mt-5" id="gsc-fluentform-sheet-id">
                                <option value="">
                                    <?php echo esc_html(__('Select', 'gsheetconnector-forminator')); ?>
                                </option>
                                <option value="create_new">
                                    <?php echo esc_html(__('Create New', 'gsheetconnector-forminator')); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <span class="error_msg" id="error_spread"></span>
                    <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <i class="errorSelect errorSelectsheet"></i>
                    <div class="col-4">
                        <label><?php echo esc_html(__('Sheet Tab Name', 'gsheetconnector-forminator')); ?></label>
                        <select name="gscf-ff[gs-sheet-tab-name]" class="auto-select-display w-100 mt-5" id="gscfff-sheet-tab-name">
                            <option value="">
                                <?php echo esc_html(__('Select', 'gsheetconnector-forminator')); ?>
                            </option>

                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="gsc-sheet-actions flex-wrap gap-12 gsc-sheet-card-status">
            <div>
                <div class="heading mt-0 mb-0">
                    <?php echo esc_html__('Fetch Sheets', 'gsheetconnector-forminator'); ?>
                </div>

                <p class="gscelementorform-sync-row mt-20">
                    <?php esc_html_e('Spreadsheet Name and URL not showing?', 'gsheetconnector-forminator'); ?>
                    <a id="gscelementorform-sync" data-init="yes" class="sync-button">
                        <?php esc_html_e('Click Here', 'gsheetconnector-forminator'); ?>
                    </a>
                    <?php esc_html_e('to fetch sheets.', 'gsheetconnector-forminator'); ?>
                    <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </p>
            </div>
        </div>
    </div>


    <!-- SELECT FIELDS TO SYNC -->
    <div class="form-fields-list gsfrmnp-list-set shadow-box mt-40 p-30" id="field-mapping">
        <div class="heading mt-0"> <?php echo esc_html(__('Select Fields to Sync', 'gsheetconnector-forminator')); ?>
            <span class="pro-ver"><?php esc_html_e('PRO', 'gsheetconnector-forminator'); ?></span>
        </div>
        <p><?php esc_html_e('Enable the fields you want to send to Google Sheets and rename columns if needed.', 'gsheetconnector-forminator'); ?></p>

        <?php
        // Build the list of actual Forminator form fields (mirrors old dynamic logic).
        $sync_field_data = array();
        foreach ($form as $sync_field) {
            $sync_raw = $sync_field->raw;

            if (isset($sync_raw['type']) && $sync_raw['type'] === 'address') {
                $sync_field_data['Address'] = 'Address';
            } elseif (!empty($sync_raw['field_label'])) {
                $sync_field_data[$sync_raw['field_label']] = $sync_raw['field_label'];
            }
        }

        // Submission info / system tags. "Submission ID" is rendered separately below
        // as a pinned, always-selected field.
        $sync_default_mail_tags = array(
            'Date Submitted' => 'Date Submitted',
            'Post ID'        => 'Post ID',
            'Post Title'     => 'Post Title',
            'Post URL'       => 'Post URL',
            'Date & Time'    => 'Date & Time',
            'Source URL'     => 'Source URL',
            'User ID'        => 'User ID',
            'User Agent'     => 'User Agent',
            'IP Address'     => 'IP Address',
        );
        ?>
        <div class="gsfrmnp-color-code d-flex align-center flex-wrap gap-20 pt-10">
            <div class="color-ffgs field-type-form">
                <span class="field-list-pill align-center fw-700"><?php esc_html_e('Field List', 'gsheetconnector-forminator'); ?></span>
            </div>
            <div class="color-ffgs field-type-system">
                <span class="align-center fw-700">Submission Info</span>
            </div>
        </div>

        <div class="toggle-button select-all-toggle">
            <div class="mt-30 mb-20 select-all-field">
                <label class="switch">
                    <input type="checkbox" id="select-all-checkbox" checked disabled>
                    <span class="slider round button-toggle"></span>
                </label>
                <span class="label-text fw-600"><?php esc_html_e('Select All Fields', 'gsheetconnector-forminator'); ?></span></span>
            </div>

            <div id="forminator-gsc-sortable" class="ui-sortable">

                <!-- Example: Submission ID (special/system tag, non-sortable, checkbox disabled) -->
                <div class="card form-field-toggle active non-sortable" data-field="sheet_header[Submission ID]" data-field-key="Submission ID">
                    <div class="card-content">
                        <div class="toggle-button special_mail_tags_bg justify-between">
                            <div class="field-label-wrap">
                                <span class="drag-icon">
                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="6" cy="5" r="1.5" fill="currentColor"></circle>
                                        <circle cx="14" cy="5" r="1.5" fill="currentColor"></circle>
                                        <circle cx="6" cy="10" r="1.5" fill="currentColor"></circle>
                                        <circle cx="14" cy="10" r="1.5" fill="currentColor"></circle>
                                        <circle cx="6" cy="15" r="1.5" fill="currentColor"></circle>
                                        <circle cx="14" cy="15" r="1.5" fill="currentColor"></circle>
                                    </svg>
                                </span>
                                <div class="field-view-mode d-flex flex-column">
                                    <span class="label-text card-label">Submission ID</span>
                                    <span class="card-sublabel">submission_id</span>
                                    <!-- No edit icon for Submission ID -->
                                </div>

                                <div class="field-edit-mode d-none">
                                    <input type="text" class="field-input disable-cursor" name="sheet_header_names[Submission ID]" value="Submission ID" readonly>
                                    <!-- No save icon for Submission ID -->
                                </div>
                            </div>

                            <label class="switch field-list-new">
                                <input type="checkbox" class="toggle-input" name="sheet_header[Submission ID]" value="1" checked disabled>
                                <span class="slider round button-toggle"></span>
                            </label>

                            <div class="info-row">
                                <input type="text" class="field-input" value="Submission ID" readonly>
                            </div>


                        </div>
                    </div>
                </div>

                <?php
                // Other special/system tags (Date Submitted, Post ID, IP Address, etc.).
                foreach ($sync_default_mail_tags as $sync_tag_key => $sync_tag_label) :
                    $sync_tag_slug = str_replace('-', '_', sanitize_title($sync_tag_label));
                ?>
                    <div class="card form-field-toggle" data-field="sheet_header[<?php echo esc_attr($sync_tag_key); ?>]" data-field-key="<?php echo esc_attr($sync_tag_key); ?>">
                        <div class="card-content">
                            <div class="toggle-button special_mail_tags_bg justify-between">
                                <div class="field-label-wrap">
                                    <span class="drag-icon">
                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="6" cy="5" r="1.5" fill="currentColor"></circle>
                                            <circle cx="14" cy="5" r="1.5" fill="currentColor"></circle>
                                            <circle cx="6" cy="10" r="1.5" fill="currentColor"></circle>
                                            <circle cx="14" cy="10" r="1.5" fill="currentColor"></circle>
                                            <circle cx="6" cy="15" r="1.5" fill="currentColor"></circle>
                                            <circle cx="14" cy="15" r="1.5" fill="currentColor"></circle>
                                        </svg>
                                    </span>
                                    <div class="field-view-mode d-flex flex-column">
                                        <span class="label-text card-label"><?php echo esc_html($sync_tag_label); ?></span>
                                        <span class="card-sublabel"><?php echo esc_html($sync_tag_slug); ?></span>
                                        <i class="dashicons dashicons-edit field-edit-icon" role="button" tabindex="0" aria-label="Edit label"></i>
                                    </div>

                                    <div class="field-edit-mode d-none">
                                        <input type="text" class="field-input" name="sheet_header_names[<?php echo esc_attr($sync_tag_key); ?>]" value="<?php echo esc_attr($sync_tag_label); ?>">
                                        <i class="dashicons dashicons-yes field-save-icon" role="button" tabindex="0"></i>
                                    </div>
                                </div>

                                <label class="switch field-list-new">
                                    <input type="checkbox" class="toggle-input" name="sheet_header[<?php echo esc_attr($sync_tag_key); ?>]" value="1" disabled>
                                    <span class="slider round button-toggle"></span>
                                </label>

                                <div class="info-row">
                                    <input type="text" class="field-input" value="<?php echo esc_attr($sync_tag_label); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php
                // Actual Forminator form fields.
                foreach ($sync_field_data as $sync_field_key => $sync_field_label) :
                    $sync_field_slug = str_replace('-', '_', sanitize_title($sync_field_label));
                ?>
                    <div class="card form-field-toggle" data-field="sheet_header[<?php echo esc_attr($sync_field_key); ?>]" data-field-key="<?php echo esc_attr($sync_field_key); ?>">
                        <div class="card-content">
                            <div class="toggle-button field_list_bg justify-between">
                                <div class="field-label-wrap">
                                    <span class="drag-icon">
                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="6" cy="5" r="1.5" fill="currentColor"></circle>
                                            <circle cx="14" cy="5" r="1.5" fill="currentColor"></circle>
                                            <circle cx="6" cy="10" r="1.5" fill="currentColor"></circle>
                                            <circle cx="14" cy="10" r="1.5" fill="currentColor"></circle>
                                            <circle cx="6" cy="15" r="1.5" fill="currentColor"></circle>
                                            <circle cx="14" cy="15" r="1.5" fill="currentColor"></circle>
                                        </svg>
                                    </span>
                                    <div class="field-view-mode d-flex flex-column">
                                        <span class="label-text card-label"><?php echo esc_html($sync_field_label); ?></span>
                                        <span class="card-sublabel"><?php echo esc_html($sync_field_slug); ?></span>
                                        <i class="dashicons dashicons-edit field-edit-icon" role="button" tabindex="0" aria-label="Edit label"></i>
                                    </div>

                                    <div class="field-edit-mode d-none">
                                        <input type="text" class="field-input" name="sheet_header_names[<?php echo esc_attr($sync_field_key); ?>]" value="<?php echo esc_attr($sync_field_label); ?>">
                                        <i class="dashicons dashicons-yes field-save-icon" role="button" tabindex="0"></i>
                                    </div>
                                </div>
                                <label class="switch field-list-new">
                                    <input type="checkbox" class="toggle-input" name="sheet_header[<?php echo esc_attr($sync_field_key); ?>]" value="1" checked disabled>
                                    <span class="slider round button-toggle"></span>
                                </label>
                                <div class="info-row">
                                    <input type="text" class="field-input" value="<?php echo esc_attr($sync_field_label); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- conditional local start  -->
    <div class="card-gs misc-card shadow-box mt-40 p-30">
        <div class="cd-faq-items">
            <ul id="basics" class="cd-faq-group">
                <li class="">
                    <div class="cd-faq-content" style="display: block">
                        <div class="gs-demo-fields gs-third-block">
                            <div class="misc-conditional-row">
                                <div class="misc-options-wrapper">

                                    <div class="d-flex align-items-center justify-between">


                                        <div class="heading mt-0"> <?php echo esc_html(__('Conditional Logic', 'gsheetconnector-forminator')); ?>
                                            <span class="pro-ver"><?php esc_html_e('PRO', 'gsheetconnector-forminator'); ?></span>
                                        </div>
                                        <label for="enable-conditional-logic" class="switch">
                                            <input type="checkbox" class="gscfff-conditional-toggle" name="gscf-frmn[enable_conditional_logic]" id="enable-conditional-logic" value="1" checked disabled>
                                            <span class="slider round button-toggle"></span>
                                        </label>
                                    </div>
                                    <div class="misc-conditional-inner gscfff-misc-conditional-inner" style="">
                                        <p class="mb-30 mt-20"> <?php esc_html_e('The Enable Conditional Logic option in the field settings allows you to create rules to dynamically display or hide the submission to google sheet based on values.', 'gsheetconnector-forminator'); ?></p>
                                        <div> <?php esc_html_e('Process this feed if', 'gsheetconnector-forminator'); ?>
                                            <select name="gscf-frmn[enable_conditional_logic_type]" class="enableConditionalLogic conditional-logic" disabled>
                                                <option value="all" selected="selected">
                                                    All</option>
                                                <option value="any">
                                                    Any </option>
                                            </select> of the following match:
                                        </div>
                                        <div class="conditional-logic-container mt-30">
                                            <div class="conditional-logic-row d-flex flex-wrap gap-10 mb-10">
                                                <select name="gscf-frmn[conditional][0][enable_conditional_logic_field_name]" class="enableConditionalLogic conditional-field" disabled>
                                                    <option value="Submission ID">Submission ID</option>
                                                    <option value="Post ID">Post ID</option>
                                                </select>
                                                <select name="gscf-frmn[conditional][0][enable_conditional_logic_rule_select]" class="enableConditionalLogic conditional-operator" disabled>
                                                    <option value="is" selected="selected">
                                                        is </option>
                                                    <option value="isnot">
                                                        is not </option>
                                                    <option value="greaterthan">
                                                        greater than </option>
                                                    <option value="lessthan">
                                                        less than </option>
                                                    <option value="contains">
                                                        contains </option>
                                                    <option value="starts_with">
                                                        starts with </option>
                                                    <option value="ends_with">
                                                        ends with </option>
                                                </select>

                                                <input type="text" name="gscf-frmn[conditional][0][enable_conditional_logic_rule_value]" value="" placeholder="Enter a value" class="enableConditionalLogic conditional-value" readonly>

                                                <button type="button" class="add_field_choice circle-plus" title="Add rule" disabled>+</button>
                                                <button type="button" class="remove_field_choice circle-minus" title="Remove rule" style="display: none;">−</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- conditional local end -->

    <!-- HEADER SETTINGS  Start -->
    <div class="freez_order_sort form-fields-list gscfff-list-set shadow-box mt-40 p-30" id="header-settings-sheet-sorting">
        <div class="heading mt-0"> <?php echo esc_html(__('Header Settings', 'gsheetconnector-forminator')); ?>
            <span class="pro-ver"><?php esc_html_e('PRO', 'gsheetconnector-forminator'); ?></span>
        </div>
        <p><?php echo esc_html(__('Customize the appearance and behavior of your sheet headers and rows for better readability and organization.', 'gsheetconnector-forminator')); ?></p>
        <div class="header-styling-sheet d-flex gap-20">

            <!-- Card 1: Header Behavior -->
            <div class="settings-card mb-20 w-100 bg-white">
                <div class="mt-0 header-settings-ineer-size fw-600 mb-15"><?php esc_html_e('Header Behavior', 'gsheetconnector-forminator'); ?></div>

                <div class="gscfrmnt-cards gscfrmnt-card setting-row">
                    <div class="toggle-button freeze-header-toggle d-flex align-items-center justify-between mb-15">
                        <span class="label-text fw-400"><?php esc_html_e('Freeze Header', 'gsheetconnector-forminator'); ?></span>
                        <label class="switch">
                            <input type="checkbox" id="freeze-header-checkbox" name="forminatorform-gs[freeze_header]" value="true" disabled>
                            <span class="slider round button-toggle"></span>
                        </label>
                    </div>
                </div>

                <div class="sheet_sorting sheet_formatting mb-15">
                    <div class="gscfrmnt-cards">
                        <div class="toggle-button sheet-sorting-toggle d-flex align-items-center justify-between">
                            <span class="label-text fw-400"><?php esc_html_e('Sheet Sorting', 'gsheetconnector-forminator'); ?></span>
                            <label class="switch" for="sheet-sorting-checkbox">
                                <input type="checkbox" id="sheet-sorting-checkbox" name="forminatorform-gs[sheet_sorting]" value="1" checked disabled>
                                <span class="slider round button-toggle"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Locked: pro-locked-section disables every field inside -->
                    <div class="sheet-sorting-settings pro-locked-section" id="sheet-sorting-settings">
                        <div class="settings-grid">
                            <div class="gscfrmnt-row form-group">
                                <label for="sort-column-name"><?php esc_html_e('Sort Column', 'gsheetconnector-forminator'); ?></label>
                                <select id="sort-column-name" name="forminatorform-gs[sort_column]" disabled>
                                    <option value="">Select Column</option>
                                    <!-- Options are populated dynamically based on enabled sync fields -->
                                    <option value="Submission ID">Submission ID</option>
                                    <option value="Entry Date">Entry Date</option>
                                    <option value="Full Name">Full Name</option>
                                </select>
                            </div>
                            <div class="gscfrmnt-row form-group">
                                <label for="sort-order">Sort Order</label>
                                <select id="sort-order" name="forminatorform-gs[sort_order]" disabled>
                                    <option value="ASCENDING">Ascending</option>
                                    <option value="DESCENDING">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-0 header-settings-ineer-size fw-600 mb-15"><?php esc_html_e('Header Style', 'gsheetconnector-forminator'); ?></div>

                <!-- Header Appearance -->
                <div class="sheet_formatting setting-row">
                    <div class="gscfrmnt-sheet_formatting gscfrmnt-sheet_formatting">
                        <div class="toggle-button sheet_formatting-header-toggle d-flex align-items-center justify-between mb-15">
                            <span class="label-texts fw-400"><?php esc_html_e('Header Appearance', 'gsheetconnector-forminator'); ?></span>
                            <label class="switch" for="sheet_formatting-header-checkbox">
                                <input type="checkbox" id="sheet_formatting-header-checkbox" name="forminatorform-gs[sheet_formatting_header]" value="1" checked disabled>
                                <span class="slider round button-toggle"></span>
                            </label>
                        </div>
                    </div>

                    <div class="font-styling-settings pro-locked-section" id="font-styling-settings">
                        <div class="settings-grid">
                            <div class="font-style row-format form-group">
                                <label>Font Style</label>
                                <div class="d-flex gap-5">
                                    <label class="style-btn"><input type="checkbox" name="font_styles[]" value="normal" disabled> Normal</label>
                                    <label class="style-btn"><input type="checkbox" name="font_styles[]" value="italic" disabled> Italic</label>
                                    <label class="style-btn"><input type="checkbox" name="font_styles[]" value="bold" disabled> Bold</label>
                                </div>
                            </div>
                            <div class="font-size row-format form-group">
                                <label for="font-size">Font Size</label>
                                <select id="font-size" name="forminatorform-gs[font_size]" disabled>
                                    <option value="10">10</option>
                                    <option value="12">12</option>
                                    <option value="14">14</option>
                                    <option value="16">16</option>
                                </select>
                            </div>
                            <div class="font-color row-format form-group">
                                <label for="font-color"><?php echo esc_html__('Font Color', 'gsheetconnector-forminator'); ?></label>
                                <input type="color" id="font-color" name="forminatorform-gs[font_color]" class="bg-color-set-input" value="#000000" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Header Style -->
            <div class="settings-card mb-20 w-100 bg-white">
                <div class="mt-0 header-settings-ineer-size fw-600 mb-15"><?php esc_html_e('Row Style', 'gsheetconnector-forminator'); ?></div>

                <!-- Row Appearance -->
                <div class="sheet_formatting">
                    <div class="gscfrmnt-sheet_formatting_row  mb-20">
                        <div class="toggle-button sheet_color_formatting-row-toggle d-flex align-items-center justify-between">
                            <span class="label-texts fw-400">Color Appearance</span>
                            <label class="switch" for="sheet-bg-toggle-checkbox">
                                <input type="checkbox" id="sheet-bg-toggle-checkbox" name="forminatorform-gs[sheet_bg]" value="1" checked disabled>
                                <span class="slider round button-toggle"></span>
                            </label>
                        </div>
                    </div>

                    <div id="sheet-bg-settings-row" class="sheet-bg-settings-row pro-locked-section">
                        <div class="settings-grid">
                            <div class="gscfrmnt-cards-sbg form-group">
                                <label for="header-color"><?php esc_html_e('Header Background', 'gsheetconnector-forminator'); ?></label>
                                <input type="color" id="header-color" name="forminatorform-gs[header-color]" class="bg-color-set-input" value="#ffffff" disabled>
                            </div>
                            <div class="gscfrmnt-cards-sbg form-group">
                                <label for="odd-color"><?php esc_html_e('Odd Row Color', 'gsheetconnector-forminator'); ?></label>
                                <input type="color" id="odd-color" name="forminatorform-gs[odd-color]" class="bg-color-set-input" value="#ffffff" disabled>
                            </div>
                            <div class="gscfrmnt-cards-sbg form-group">
                                <label for="even-color"><?php esc_html_e('Even Row Color', 'gsheetconnector-forminator'); ?></label>
                                <input type="color" id="even-color" name="forminatorform-gs[even-color]" class="bg-color-set-input" value="#ffffff" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="gscfrmnt-sheet_formatting_row">
                        <div class="toggle-button sheet_formatting-row-toggle d-flex align-items-center justify-between">
                            <span class="label-texts fw-400"><?php esc_html_e('Row Appearance', 'gsheetconnector-forminator'); ?></span>
                            <label class="switch" for="sheet_formatting-row-checkbox">
                                <input type="checkbox" id="sheet_formatting-row-checkbox" name="forminatorform-gs[sheet_formatting_row]" value="1" checked disabled>
                                <span class="slider round button-toggle"></span>
                            </label>
                        </div>
                    </div>

                    <div class="font-styling-settings-row pro-locked-section" id="font-styling-settings-row">
                        <div class="settings-grid">
                            <div class="font-style row-format form-group">
                                <label>Font Style</label>
                                <div class="d-flex gap-5">
                                    <label class="style-btn"><input type="checkbox" name="font_styles_row[]" value="normal" disabled> Normal</label>
                                    <label class="style-btn"><input type="checkbox" name="font_styles_row[]" value="italic" disabled> Italic</label>
                                    <label class="style-btn"><input type="checkbox" name="font_styles_row[]" value="bold" disabled> Bold</label>
                                </div>
                            </div>
                            <div class="font-size row-format form-group">
                                <label for="font-size-row"><?php esc_html_e('Font Size', 'gsheetconnector-forminator'); ?></label>
                                <select id="font-size-row" name="forminatorform-gs[font_size_row]" disabled>
                                    <option value="10">10</option>
                                    <option value="12">12</option>
                                    <option value="14">14</option>
                                    <option value="16">16</option>
                                </select>
                            </div>
                            <div class="font-color row-format form-group">
                                <label for="font-color-row"><?php esc_html_e('Font Color', 'gsheetconnector-forminator'); ?></label>
                                <input type="color" id="font-color-row" name="forminatorform-gs[font_color_row]" class="bg-color-set-input" value="#000000" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- HEADER SETTINGS  end -->

    <div class="google-sheet-sync shadow-box mt-40 p-30" id="google-sheet-data-sync-div">
        <div class="current-toggle-wrapper d-flex justify-between align-center mb-20">
            <div class="heading mt-0"> <?php echo esc_html(__('Google Sheets Data Sync', 'gsheetconnector-forminator')); ?>
                <span class="pro-ver"><?php esc_html_e('PRO', 'gsheetconnector-forminator'); ?></span>
            </div>
            <div class="custom-check d-flex align-center gap-10">
                <input type="checkbox" id="gsfrmnp-enable-sync-options" data-feed-id="" checked disabled>
                <label for="gsfrmnp-enable-sync-options" class="button-toggle"></label>
            </div>
        </div>

        <p class="mb-0"><?php esc_html_e('Easily sync past form submissions to your connected Google Sheet. Choose a date range and ensure your spreadsheet stays complete and up to date.', 'gsheetconnector-forminator'); ?></p>

        <div class="gs-wpcore-sync-entries">

            <div id="gsfrmnp-sync-radio-options" class=" mt-20 mb-20">
                <div class="radio-group d-flex gap-20 mb-20">
                    <label>
                        <input type="radio" name="sync_type" value="all" disabled>
                        <?php esc_html_e(' Sync All Submissions', 'gsheetconnector-forminator'); ?>
                    </label>
                    <label>
                        <input type="radio" name="sync_type" value="date_range" checked disabled>
                        <?php esc_html_e(' Sync by Date Range', 'gsheetconnector-forminator'); ?>
                    </label>
                </div>

                <div id="gsfrmnp-date-range-fields" class="">
                    <div class="d-flex align-center gap-10">
                        <div class="form-group w-100">
                            <label for="sync-from-date"><?php esc_html_e('From Date', 'gsheetconnector-forminator'); ?></label>
                            <input type="date" id="sync-from-date" name="sync_from_date" class="wpgs-date-picker" value="" min="" max="" disabled>
                        </div>
                        <div class="form-group w-100">
                            <label for="sync-to-date"><?php esc_html_e('To Date', 'gsheetconnector-forminator'); ?></label>
                            <input type="date" id="sync-to-date" name="sync_to_date" class="wpgs-date-picker" value="" min="" max="" disabled>
                        </div>
                    </div>
                </div>

                <a id="gs-wpcore-sync-entries" data-init="yes" class="sync-button-entries back-btn btn btn-primary text-decoration-none mt-20" disabled>
                    <?php esc_html_e('Sync Entries', 'gsheetconnector-forminator'); ?>
                </a>

                <input type="hidden" name="form_id" id="sync-form-id" value="">
                <input type="hidden" name="feed_id" id="sync-feed-id" value="">
                <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <input type="hidden" name="frmntr-sync-gs-ajax-nonce" id="frmntr-sync-gs-ajax-nonce" value="">
            </div>

            <div id="gs-message-popup" class="d-none">
                <div class="popup-content">
                    <p id="popup-message">Starting...</p>
                    <p id="popup-progress" style="font-weight:bold;">0 / 0</p>
                    <button id="popup-close">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>