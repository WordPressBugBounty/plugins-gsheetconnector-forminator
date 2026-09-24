<?php
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$service_obj = new GS_FORMNTR_Service();
$forms = $service_obj->get_form_list();
$num_forms = count($forms); // Count the number of active forms

// Define $entry_type and $sql_month_start_date
$entry_type = 'forminator_custom_form'; // Assuming the entry type is forminator_custom_form
$sql_month_start_date = gmdate('Y-m-d', strtotime('-30 days')); // Calculate the date 30 days ago

// Count total entries from last 30 days.
$total_entries_from_last_month = count(Forminator_Form_Entry_Model::get_newer_entry_ids($entry_type, $sql_month_start_date));

$most_entry = Forminator_Form_Entry_Model::get_most_entry($entry_type);

// Check if the user is authenticated
$authenticated = get_option('gs_formntr_token');
$per = get_option('gs_formntr_verify');

// Check user is authenticated when save existing API method
$show_setting = 0;

if (!empty($authenticated) && $per == "valid") {
    $show_setting = 1;
} else {
    $formntr_selected_method = '   Existing Client / Secret Key (Auto Setup) ';
?>
    <div class="gsc-setup-alert">
        <div class="gsc-alert-icon">
            <svg width="30px" height="30px" viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.2202 21.25H5.78015C5.14217 21.2775 4.50834 21.1347 3.94373 20.8364C3.37911 20.5381 2.90402 20.095 2.56714 19.5526C2.23026 19.0101 2.04372 18.3877 2.02667 17.7494C2.00963 17.111 2.1627 16.4797 2.47015 15.92L8.69013 5.10999C9.03495 4.54078 9.52077 4.07013 10.1006 3.74347C10.6804 3.41681 11.3346 3.24518 12.0001 3.24518C12.6656 3.24518 13.3199 3.41681 13.8997 3.74347C14.4795 4.07013 14.9654 4.54078 15.3102 5.10999L21.5302 15.92C21.8376 16.4797 21.9907 17.111 21.9736 17.7494C21.9566 18.3877 21.7701 19.0101 21.4332 19.5526C21.0963 20.095 20.6211 20.5381 20.0565 20.8364C19.4919 21.1347 18.8581 21.2775 18.2202 21.25V21.25Z" stroke="#9a3412" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M10.8809 17.15C10.8809 17.0021 10.9102 16.8556 10.9671 16.7191C11.024 16.5825 11.1074 16.4586 11.2125 16.3545C11.3175 16.2504 11.4422 16.1681 11.5792 16.1124C11.7163 16.0567 11.8629 16.0287 12.0109 16.03C12.2291 16.034 12.4413 16.1021 12.621 16.226C12.8006 16.3499 12.9398 16.5241 13.0211 16.7266C13.1023 16.9292 13.122 17.1512 13.0778 17.3649C13.0335 17.5786 12.9272 17.7745 12.7722 17.9282C12.6172 18.0818 12.4203 18.1863 12.2062 18.2287C11.9921 18.2711 11.7703 18.2494 11.5685 18.1663C11.3666 18.0833 11.1938 17.9426 11.0715 17.7618C10.9492 17.5811 10.8829 17.3683 10.8809 17.15ZM11.2409 14.42L11.1009 9.20001C11.0876 9.07453 11.1008 8.94766 11.1398 8.82764C11.1787 8.70761 11.2424 8.5971 11.3268 8.5033C11.4112 8.40949 11.5144 8.33449 11.6296 8.28314C11.7449 8.2318 11.8697 8.20526 11.9959 8.20526C12.1221 8.20526 12.2469 8.2318 12.3621 8.28314C12.4774 8.33449 12.5805 8.40949 12.6649 8.5033C12.7493 8.5971 12.8131 8.70761 12.852 8.82764C12.8909 8.94766 12.9042 9.07453 12.8909 9.20001L12.7609 14.42C12.7609 14.6215 12.6808 14.8149 12.5383 14.9574C12.3957 15.0999 12.2024 15.18 12.0009 15.18C11.7993 15.18 11.606 15.0999 11.4635 14.9574C11.321 14.8149 11.2409 14.6215 11.2409 14.42Z" fill="#9a3412" />
            </svg>
        </div>
        <div class="gsc-alert-content">
            <div class="feed-alert-header"><?php esc_html_e('Google Sheets Setup Required', 'gsheetconnector-forminator'); ?></div>
            <p><?php esc_html_e('your selected Method is : ', 'gsheetconnector-forminator'); ?><?php echo esc_html($formntr_selected_method); ?></p>
            <p><?php esc_html_e('To start sending form entries to Google Sheets, please connect your Google account first.', 'gsheetconnector-forminator'); ?></p>
            <ul>
                <li><?php esc_html_e('✔ Click on the Sign in with Google button', 'gsheetconnector-forminator'); ?></li>
                <li><?php esc_html_e('✔ Log in using your Google account', 'gsheetconnector-forminator'); ?></li>
                <li><?php esc_html_e('✔ Select the Google account where your Sheets are stored', 'gsheetconnector-forminator'); ?></li>
                <li><?php esc_html_e('✔ Grant access to: Google Drive & Google Sheets', 'gsheetconnector-forminator'); ?></li>
                <li><?php esc_html_e('✔ Save the authentication code if prompted', 'gsheetconnector-forminator'); ?></li>
            </ul>
            <a href="admin.php?page=formntr-gsheet-config&tab=integration" class="gsc-alert-btn link-hover-white">
                <?php esc_html_e('Go to Integration Setup', 'gsheetconnector-forminator'); ?>
            </a>
        </div>
    </div>
<?php
}

if ($show_setting == 1) { ?>

    <div class="feed-error-message gsc-msg gsc-error d-none d-block fw-400 text-dark text-center pt-10 pb-10"></div>
    <div class="feed-success-message gsc-msg gsc-success d-none d-block fw-400 text-dark text-center pt-10 pb-10"></div>
    <div class="formntr-main">
        <div class="heading mt-0"> <?php echo esc_html(__('Sheet Sync Feeds', 'gsheetconnector-forminator')); ?></div>
        <p><?php echo esc_html(__("Create, connect, manage, and monitor how your form submissions are automatically synced to Google Sheets in real time.", 'gsheetconnector-forminator')); ?>
        <div class="formntr-row">
            <div>
                <button class="btn btn-primary formntr-btn mt-20" id="add-new-feed">
                    <?php echo esc_html__('+ Add Form Feed', 'gsheetconnector-forminator'); ?>
                </button>
                <button class="btn btn-primary formntr-close-btn mt-20" id="close-feed" style="display:none">
                    <?php echo esc_html__('- Close Form Feed', 'gsheetconnector-forminator'); ?>
                </button>
            </div>
            <div class="formntr-pro-feed shadow-box mt-50 p-30">
                <div class="heading mt-0"> <?php echo esc_html(__('Create Sheet Sync Feeds', 'gsheetconnector-forminator')); ?></div>
                <p><?php echo esc_html(__("Select a form and give your feed a name to start syncing form submissions with Google Sheets in real time.", 'gsheetconnector-forminator')); ?>
                <div class="add-feed-form">
                    <form method="post">
                        <div class="row">
                            <div class="col-4">
                                <div class="mr-10">
                                    <label
                                        for="feed_name"><?php echo esc_html__('Feed Name', 'gsheetconnector-forminator'); ?></label><br />
                                    <input type="text" id="formntr_feed_name" class="feedName form-control mt-5" name="feed_name" />
                                    <div class="input-msg d-none"><?php echo esc_html__('Please fill out this field', 'gsheetconnector-forminator'); ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mr-10">
                                    <label
                                        for="gsc-formntr_form_select"><?php echo esc_html__('Select Form', 'gsheetconnector-forminator'); ?></label><br />
                                    <div class="auto-select mt-5 w-100">
                                        <select id="formntr_form_select" name="formntr_formId" class="formntr_formId auto-select-display w-100">
                                            <option value="" class="auto-select-options"><?php echo esc_html__('Select Form', 'gsheetconnector-forminator'); ?>
                                            </option>

                                            <?php
                                            /*  Display fetched Forminator Forms */

                                            if (!empty($forms)) {
                                                foreach ($forms as $formntr_pro_form) { ?>
                                                    <option value="<?php echo esc_attr($formntr_pro_form->ID); ?>">
                                                        <?php echo esc_html($formntr_pro_form->post_title); ?>
                                                    </option>
                                            <?php }
                                            }
                                            ?>
                                        </select>
                                        <div class="input-msg d-none"><?php echo esc_html('Please Select this field', 'gsheetconnector-forminator'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 mt-25">
                                <div class="mr-10">
                                    <input type="hidden" name="formntr-ajax-nonce" id="formntr-ajax-nonce"
                                        value="<?php echo esc_attr(wp_create_nonce('formntr-ajax-nonce')); ?>" />
                                    <input type="button" name="execute-submit-feed-formntr" class="gsc-formntr-sub-btn btn btn-primary"
                                        value="<?php echo esc_html__('Submit', 'gsheetconnector-forminator'); ?>">
                                    <span class="formntr-fetch-load">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php
    $feedList = GS_FORMNTR_Feed_Store::all();
    if (!empty($feedList)) {




    ?>

        <div class="formntr-forminatorform-feeds-list form-feed-table-common mt-30">
            <table id="gsc-forminatorformformtable" class="full-table">
                <tr class="pt-30">
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Form ID', 'gsheetconnector-forminator'); ?></th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Form Name', 'gsheetconnector-forminator'); ?></th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Feed Name', 'gsheetconnector-forminator'); ?></th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Timestamp', 'gsheetconnector-forminator'); ?></th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Actions', 'gsheetconnector-forminator'); ?></th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Spreadsheet Connected', 'gsheetconnector-forminator'); ?></th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Status', 'gsheetconnector-forminator'); ?></th>
                </tr>
                <?php

                foreach ($feedList as $fl) {
                    $form_id = $fl['form_id'];
                    $feed_id = $fl['id'];

                    $feed_name = $fl['feed_name'];
                    $form_name = get_the_title($form_id);
                    $form_url = admin_url('admin.php?page=forminator-cform-wizard&id=' . $form_id);
                    $feed_url = admin_url('admin.php?page=formntr-gsheet-config&tab=google-sheet&form_id=' . $form_id . '&feed_id=' . $feed_id);

                    $get_feed_sheet = is_array($fl['data']) ? $fl['data'] : array();
                    $sheet_id = isset($get_feed_sheet['sheet_id']) ? $get_feed_sheet['sheet_id'] : '';
                    $sheet_name   = isset($get_feed_sheet['sheet_name']) ? $get_feed_sheet['sheet_name'] : '';
                    $tab_id   = isset($get_feed_sheet['tab_id']) ? $get_feed_sheet['tab_id'] : '';
                    $tab_name   = isset($get_feed_sheet['tab_name']) ? $get_feed_sheet['tab_name'] : '';


                    $get_feed_status = isset($fl['status']) ? (int) $fl['status'] : 1;

                    $formntr_is_disabled = ($get_feed_status === 0);

                    $created_raw = isset($fl['created_at']) ? $fl['created_at'] : '';

                    if ($created_raw !== '' && $created_raw !== '1970-01-01 00:00:00') {
                        $format = get_option('date_format') . ' ' . get_option('time_format');

                        // Format the raw value directly — no timezone conversion
                        $created_display = date($format, strtotime($created_raw));// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

                        // Get the timezone name label only (does not affect the time shown above)
                        $tz_string = get_option('timezone_string');
                        if ($tz_string) {
                            $tz_label = $tz_string; // e.g. "Asia/Kolkata"
                        } else {
                            // Fallback: some sites use a UTC offset instead of a named timezone
                            $gmt_offset = get_option('gmt_offset');
                            $tz_label = 'UTC' . ($gmt_offset >= 0 ? '+' : '') . $gmt_offset;
                        }

                        $created_display .= ' (' . $tz_label . ')';
                    } else {
                        $created_display = '—';
                    }

                ?>

                    <tr id="feed-<?php echo esc_html($feed_id); ?>" class="<?php echo $formntr_is_disabled ? 'row-disabled' : ''; ?>">
                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">
                            <?php echo esc_html($form_id); ?>
                        </td>
                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">
                            <?php echo esc_html($form_name); ?>
                        </td>
                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">
                            <div class="feed-info">
                                <div class="feed-name-wrapper" data-feed-id=" <?php echo esc_html($feed_id); ?>">
                                    <span class="feed-title feed-name-text"> <?php echo esc_html($feed_name); ?></span>

                                    <a href="#" class="rename-feed-link" data-feed-id="<?php echo esc_html($feed_id); ?>">
                                        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                    </a>

                                    <span class="feed-name-edit-row d-none">
                                        <input
                                            type="text"
                                            class="feed-name-input"
                                            value="<?php echo esc_html($feed_name); ?>"
                                            aria-label="Feed name" data-feed-id=" <?php echo esc_html($feed_id); ?>" />
                                        <button
                                            type="button"
                                            class="feed-name-save-btn"
                                            aria-label="Save feed name" data-feed-id=" <?php echo esc_html($feed_id); ?>">
                                            <span class="dashicons dashicons-yes-alt" aria-hidden="true" data-feed-id=" <?php echo esc_html($feed_id); ?>"></span>
                                        </button>
                                        <button
                                            type="button"
                                            class="feed-name-cancel-btn"
                                            aria-label="Cancel renaming feed" data-feed-id=" <?php echo esc_html($feed_id); ?>">
                                            <span class="dashicons dashicons-no-alt" aria-hidden="true" data-feed-id=" <?php echo esc_html($feed_id); ?>"></span>
                                        </button>
                                        <span class="feed-name-spinner" data-feed-id=" <?php echo esc_html($feed_id); ?>"></span>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">
                            <?php echo esc_html($created_display); ?>
                        </td>

                        <!-- ACTIONS -->
                        <td class="pt-15 pb-15 pl-20 pr-20 text-left d-flex align-center actions-col">
                            <a href="<?php echo esc_url($form_url); ?>" target="_blank">
                                <i class="fa-regular fa-eye"></i>
                            </a>

                            <a href="<?php echo esc_html($feed_url); ?>">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>

                            <a href="#" data-feed-id="<?php echo esc_html($feed_id); ?>" class="delete formntr-gs-btn formntr-pro-delete-feed-btn">
                                <i class="fa-regular fa-trash-can"></i><span class="loading-sign-delete-feed-<?php echo esc_html($feed_id); ?>" data-feed-id="<?php echo esc_html($feed_id); ?>"></span>
                            </a>
                        </td>

                        <!-- SPREADSHEET CONNECTED -->
                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">
                            <?php
                            if ($sheet_id != '' && $tab_id != '') {
                                $connected_sheet = $sheet_name . ' - ' . $tab_name;
                                $connected_sheet_url = 'https://docs.google.com/spreadsheets/d/' . $sheet_id . '/edit?gid=' . $tab_id . '#gid=' . $tab_id;
                            ?>
                                <a href="<?php echo esc_url($connected_sheet_url); ?>"
                                    class="formntr-spreadsheet-link text-decoration-none"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Open Google Spreadsheet"
                                    aria-label="Open Google Spreadsheet">
                                    <?php echo esc_html($connected_sheet); ?>
                                </a>
                            <?php
                            } else {
                                echo esc_html__('Not Connected', 'gsheetconnector-forminator');
                            } ?>
                            <!-- OR, if not connected: -->
                            <!-- <span class="formntr-spreadsheet-not-connected">Not Connected</span> -->
                        </td>

                        <td class="pt-15 pb-15 pl-20 pr-20 text-left status-col">
                            <div class="custom-check">
                                <input
                                    type="checkbox"
                                    class="check-toggle feed-status-toggle"
                                    id="formntr_feed_status_<?php echo esc_attr($feed_id); ?>"
                                    data-feed-id="<?php echo esc_attr($feed_id); ?>"
                                    value="1"
                                    aria-label="<?php esc_attr_e('Enable or disable Google Sheet sync for this feed', 'gsheetconnector-forminator'); ?>"
                                    <?php checked((int) $get_feed_status, 1); ?>>
                                <label for="formntr_feed_status_<?php echo esc_attr($feed_id); ?>" class="button-toggle"></label>
                                <span class="feed-status-spinner"></span>
                            </div>
                        </td>
                    </tr>

                    <div id="formntr-confirm-delete-popup-<?php echo esc_html($feed_id); ?>" class="frmntr-popup-overlay formntr-pro-delete-feed d-none">
                        <div class="frmntr-popup text-center">
                            <div class="gsc-modal-icon">
                                <svg width="30px" height="30px" viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18.2202 21.25H5.78015C5.14217 21.2775 4.50834 21.1347 3.94373 20.8364C3.37911 20.5381 2.90402 20.095 2.56714 19.5526C2.23026 19.0101 2.04372 18.3877 2.02667 17.7494C2.00963 17.111 2.1627 16.4797 2.47015 15.92L8.69013 5.10999C9.03495 4.54078 9.52077 4.07013 10.1006 3.74347C10.6804 3.41681 11.3346 3.24518 12.0001 3.24518C12.6656 3.24518 13.3199 3.41681 13.8997 3.74347C14.4795 4.07013 14.9654 4.54078 15.3102 5.10999L21.5302 15.92C21.8376 16.4797 21.9907 17.111 21.9736 17.7494C21.9566 18.3877 21.7701 19.0101 21.4332 19.5526C21.0963 20.095 20.6211 20.5381 20.0565 20.8364C19.4919 21.1347 18.8581 21.2775 18.2202 21.25V21.25Z" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M10.8809 17.15C10.8809 17.0021 10.9102 16.8556 10.9671 16.7191C11.024 16.5825 11.1074 16.4586 11.2125 16.3545C11.3175 16.2504 11.4422 16.1681 11.5792 16.1124C11.7163 16.0567 11.8629 16.0287 12.0109 16.03C12.2291 16.034 12.4413 16.1021 12.621 16.226C12.8006 16.3499 12.9398 16.5241 13.0211 16.7266C13.1023 16.9292 13.122 17.1512 13.0778 17.3649C13.0335 17.5786 12.9272 17.7745 12.7722 17.9282C12.6172 18.0818 12.4203 18.1863 12.2062 18.2287C11.9921 18.2711 11.7703 18.2494 11.5685 18.1663C11.3666 18.0833 11.1938 17.9426 11.0715 17.7618C10.9492 17.5811 10.8829 17.3683 10.8809 17.15ZM11.2409 14.42L11.1009 9.20001C11.0876 9.07453 11.1008 8.94766 11.1398 8.82764C11.1787 8.70761 11.2424 8.5971 11.3268 8.5033C11.4112 8.40949 11.5144 8.33449 11.6296 8.28314C11.7449 8.2318 11.8697 8.20526 11.9959 8.20526C12.1221 8.20526 12.2469 8.2318 12.3621 8.28314C12.4774 8.33449 12.5805 8.40949 12.6649 8.5033C12.7493 8.5971 12.8131 8.70761 12.852 8.82764C12.8909 8.94766 12.9042 9.07453 12.8909 9.20001L12.7609 14.42C12.7609 14.6215 12.6808 14.8149 12.5383 14.9574C12.3957 15.0999 12.2024 15.18 12.0009 15.18C11.7993 15.18 11.606 15.0999 11.4635 14.9574C11.321 14.8149 11.2409 14.6215 11.2409 14.42Z" fill="#d97706"></path>
                                </svg>
                            </div>
                            <div class="gsc-modal-title">
                                <?php echo esc_html__('Delete Feed', 'gsheetconnector-forminator'); ?>
                            </div>
                            <p class="gsc-modal-text">
                                <?php echo esc_html__('Are you sure you want to delete this feed', 'gsheetconnector-forminator'); ?>
                            </p>

                            <div class="popup-actions d-flex justify-center gap-10">
                                <button type="button" class="btn deactivate-btn formntr-delete-popup-cancel" data-feed-id="<?php echo  esc_html($feed_id); ?>" id="formntr-delete-popup-cancel-<?php echo esc_html($feed_id); ?>">
                                    <?php echo esc_html__('Cancel', 'gsheetconnector-forminator'); ?>
                                </button>
                                <button type="button" class="btn btn-primary formntr-delete-popup-confirm" data-feed-id="<?php echo esc_html($feed_id); ?>" id="formntr-delete-popup-confirm-<?php echo esc_html($feed_id); ?>">
                                    <?php echo esc_html__('Delete', 'gsheetconnector-forminator'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
<?php
}
?>