<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}

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


// Get the saved feed data from the database
$feed_data = get_post_meta($feed_id, 'forminator_forms_feed_details', true);

// Set the default values for the form fields
$sheet_name = isset($feed_data['sheet_name']) ? esc_attr($feed_data['sheet_name']) : '';
$sheet_id = isset($feed_data['sheet_id']) ? esc_attr($feed_data['sheet_id']) : '';
$tab_name = isset($feed_data['tab_name']) ? esc_attr($feed_data['tab_name']) : '';
$tab_id = isset($feed_data['tab_id']) ? esc_attr($feed_data['tab_id']) : '';

$form_id = intval($form_id);
$form_settings = Forminator_API::get_form($form_id);
$form = $form_settings->fields;
?>

<div class="frmn-main-div">
    <div class="frmn-bread-crumb">

        <a href="?page=formntr-gsheet-config&tab=google-sheet">
            <button class="back-button"><span
                    class="back-icon">&#8592;</span><?php echo esc_html(__('Back to Forms List', 'gsheetconnector-forminator')); ?></button>
        </a>

        <a href="?page=formntr-gsheet-config&tab=google-sheet&form_id=<?php echo esc_attr($form_id); ?>">
            <button class="back-button"><span class="back-icon">&#8592;</span>
                <?php echo esc_html(__('Back to Feeds List', 'gsheetconnector-forminator')); ?></button>
        </a>

    </div>

    <div class="frmn-modal-content">
        <h2 class="info-headers">
            <?php echo esc_html(__('Edit Feed and Integrate with Google Sheets', 'gsheetconnector-forminator')); ?>
        </h2>

        <form id="edit-feed-form" method="post">
            <input type="hidden" id="edit-feed-id" name="edit_feed_id" value="<?php echo esc_attr($feed_id); ?>">
            <input type="hidden" id="edit-form-id" name="edit_form_id" value="<?php echo esc_attr($form_id); ?>">

            <label
                for="edit-sheet-name"><?php echo esc_html(__('Sheet Name:', 'gsheetconnector-forminator')); ?></label>
            <input type="text" id="edit-sheet-name" name="edit_sheet_name" value="<?php echo esc_attr($sheet_name); ?>">
            <br>
            <label for="edit-sheet-id"><?php echo esc_html(__('Sheet ID:', 'gsheetconnector-forminator')); ?></label>
            <input type="text" id="edit-sheet-id" name="edit_sheet_id" value="<?php echo esc_attr($sheet_id); ?>">
            <br>
            <label for="edit-tab-name"><?php echo esc_html(__('Tab Name:', 'gsheetconnector-forminator')); ?></label>
            <input type="text" id="edit-tab-name" name="edit_tab_name" value="<?php echo esc_attr($tab_name); ?>">
            <br>
            <label for="edit-tab-id"><?php echo esc_html(__('Tab ID:', 'gsheetconnector-forminator')); ?></label>
            <input type="text" id="edit-tab-id" name="edit_tab_id" value="<?php echo esc_attr($tab_id); ?>">
            <br>
            <?php if (!empty($sheet_id)) { ?>
                <label for="edit-tab-id"><?php echo esc_html(__('Sheet URL:', 'gsheetconnector-forminator')); ?></label>
            <?php
// Generate the Google Sheets link
$link = "https://docs.google.com/spreadsheets/d/" . $sheet_id . "/edit#gid=" . $tab_id;

echo '<a href="' . esc_url( $link ) . '" target="_blank" class="google-sheets-button">'
    . esc_html__( 'View in Google Sheets', 'gsheetconnector-forminator' )
    . '</a>';
}
echo '<br><br>';
?>

            <input type="submit" 
    name="execute-edit-feed-forminator" 
    value="<?php echo esc_attr__( 'Save Changes', 'gsheetconnector-forminator' ); ?>"
    id="execute-edit-feed-forminator" 
    class="button button-primary" />

<input type="hidden" 
    name="frmntr-form-gs-ajax-nonce" 
    id="frmntr-form-gs-ajax-nonce"
    value="<?php echo esc_attr( wp_create_nonce( 'frmntr-form-gs-ajax-nonce' ) ); ?>" />

        </form>
    </div>
</div>
<!-- free setting end -->
<div class="system-debug-logs" id="opener" style="position: relative;">
    <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" class="pro-link"
        target="_blank" style="text-decoration: none;"></a>
    <!-- AUTO METHOD OF 1st DIV -->
    <div class="auto-section" style="display:block">

        <div class="gs-fields">

            <h3><?php echo esc_html__('Auto Sheet and Tab Names', 'gsheetconnector-forminator'); ?><span
                    class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span></h3>

            <div class="sheet-details ">
                <p>
                    <label><?php echo esc_html(__('Google Spreadsheet Name', 'gsheetconnector-forminator')); ?></label>
                    <select name="forminatorform-gs[gs-wpcore-sheet-id]" id="gs-wpcore-sheet-id">
                        <option value=""><?php echo esc_html(__('Select', 'gsheetconnector-forminator')); ?></option>
                        <option value="create_new">
                            <?php echo esc_html(__('Create New', 'gsheetconnector-forminator')); ?>
                        </option>
                    </select>


                    <span class="error_msg" id="error_spread"></span>
                    <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

                </p>
                <i class="errorSelect errorSelectsheet"></i>
            </div>

            <p>
                <label><?php echo esc_html(__('Google Sheet Tab Name', 'gsheetconnector-forminator')); ?></label>

                <select name="forminatorform-gs[gs-sheet-tab-name]" id="gs-sheet-tab-name">

                </select>


            </p>
            <p class="sheet-url" id="sheet-url">

            </p>

            <i class="errorSelect errorSelecttabs"></i>

            <div class="create-ss-wrapper" style="display: none" ;>
                <label>
                    <?php echo esc_html(__('Create Spreadsheet', 'gsheetconnector-forminator')); ?>
                </label>
                <input type="text" name="_gs_forminator_setting_create_sheet" value=""
                    id="_gs_forminator_setting_create_sheet">
                <span class="error_msg" id="error_new_spread"></span>
            </div>

            <p id="gs-validation-message"></p>
            <p id="gs-valid-message"></p>

            <?php if (!empty(get_option('gs_formntr_verify')) && (get_option('gs_formntr_verify') == "valid")) { ?>
                <!-- Display synchronization option for valid permissions -->
                <p class="gs-wpcore-sync-row">
                    <a id="gs-wpcore-sync" data-init="yes"
                        class="sync-button"><?php echo esc_html__('Click here', 'gsheetconnector-forminator'); ?></a>
                    <?php echo esc_html__('to fetch sheets detail for "Forms Feed Settings".', 'gsheetconnector-forminator'); ?>
                    <span class="loading-sign-fetch-sheet">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

                </p>
            <?php } ?>

        </div>
    </div>
    <br class="clear">

    <!-- 1st tab form fields lists -->
    <div class="form-fields-list gs-frmnt-list-set1">

        <div class="forminatorgs-color-code">

            <div class="color-forminatorgs">

                <p><?php echo esc_html__('Field List', 'gsheetconnector-forminator'); ?><span
                        class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span></p>
                <p class="Fl"></p>
            </div>
            <div class="color-forminatorgs">

                <p><?php echo esc_html__('Special Mail Tags', 'gsheetconnector-forminator'); ?><span
                        class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span></p>
                <p class="SM"></p>
            </div>

        </div>
        <div class="toggle-button select-all-toggle">

            <label class="switch">
                <input type="checkbox" id="select-all-checkbox">
                <span class="slider round"></span>
            </label>
            <span class="label-text"><?php echo esc_html__('Select All', 'gsheetconnector-forminator'); ?></span>
        </div>
        <div id="sortable">

            <?php
            // Extract relevant information from the first array
            $field_data = array();
            foreach ($form as $field) {
                $raw = $field->raw; // Accessing protected property for example
                if ($field->raw['type'] == 'address') {

                    $field_data['Address'] = 'Address';
                } else {
                    $field_data[$raw['field_label']] = $raw['field_label'];
                }
            }
            $default_mail_tags = array(
                'Submission ID' => 'Submission ID',
                'Date Submitted' => 'Date Submitted',
                'Post ID' => 'Post ID',
                'Post Title' => 'Post Title',
                'Post URL' => 'Post URL',
                'Date & Time' => 'Date & Time',
                'Source URL' => 'Source URL',
                'User ID' => 'User ID',
                'User Agent' => 'User Agent',
                'IP Address' => 'IP Address',
                // Add more default mail tags as needed
            );

            // Merge both arrays
            $form_merged_array = array_merge($default_mail_tags, $field_data);

            foreach ($form_merged_array as $key => $field) {
                $field_label = $key;



                // Output HTML for other types of fields
                echo '<div class="card-pro  form-field-toggle">';
                echo '<div class="card-content">';


                if ($key == "Submission ID" || $key == "Date Submitted" || $key == "Post ID" || $key == "Post Title" || $key == "Post URL" || $key == "Date & Time" || $key == "Source URL" || $key == "User ID" || $key == "User Agent" || $key == "IP Address") {
                    echo '<div class="toggle-button special_mail_tags_bg">';
                } else {
                    echo '<div class="toggle-button field_list_bg">';
                }
                echo '<label class="switch">';
                // echo '<input type="checkbox" name="sheet_header[]" class="toggle-input" value="' . $field_label . '" ' . $is_selected . '>'; // Add a checkbox input
                echo '<input type="checkbox" name="sheet_header[' . esc_attr($field_label) . ']" class="toggle-input" value="1">';
                echo '<span class="slider round"></span>';
                echo '</label>';
                printf('<span class="label-text">%s</span>', esc_html($field_label));
                echo '<div class="info-row" id="' . esc_attr($field) . '_row">';
                // echo '<label>' . esc_html($field_label) . '</label>';
                echo '<input class="info-content" type="text" value="' . esc_html($field_label) . '">';

                echo '</div>'; // Close info-row

                echo '<input type="text" class="info-content" name="sheet_header_names[' . esc_attr($field_label) . ']" placeholder="' . esc_attr($field_label) . '" value="' . esc_attr($field_label) . '">';

                echo '</div>'; // Close toggle-button
                echo '</div>'; // Close card-content
                echo '</div>'; // Close card



            }
            ?>
        </div>
    </div>


    <!-- 3rd Div Of PRO freeze headers -->
    <div class="freez_order_sort">

        <h3><?php echo esc_html__('Header Settings', 'gsheetconnector-forminator'); ?> <span
                class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span></h3>
        <div class="gscfrmnt-cards gscfrmnt-card">
            <div class="toggle-button freeze-header-toggle">
                <label class="switch">
                    <input type="checkbox" id="freeze-header-checkbox" name="forminatorform-gs[freeze_header]"
                        value="true">
                    <span class="slider round"></span>
                </label>
                <div class="label-container">
                    <span
                        class="label-text"><?php echo esc_html__('Freeze Header', 'gsheetconnector-forminator'); ?></span>
                    <span class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span>
                </div>

            </div>
        </div>

        <!-- 3rd Div Of PRO freeze headers -->
        <div class="sheet_formatting">
            <div class="gscfrmnt-sheet_formatting gscfrmnt-sheet_formatting">
                <div class="toggle-button sheet_formatting-header-toggle">
                    <label class="switch" for="sheet_formatting-header-checkbox">
                        <input type="checkbox" id="sheet_formatting-header-checkbox"
                            name="forminatorform-gs[sheet_formatting_header]" value="1">
                        <span class="slider round"></span>
                    </label>
                    <span
                        class="label-texts"><?php echo esc_html__('Header - Font Settings', 'gsheetconnector-forminator'); ?></span>
                    <span class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span>
                </div>
            </div>

            <div class="font-styling-settings" id="font-styling-settings">

            </div>

            <!-- 4th Div OF PRO Sheet Background Colors -->
            <div class="misc-options-row">
                <div class="misc-options-wrapper">
                    <div class="toggle-button sheet-bg-toggle">
                        <label class="switch">
                            <input type="checkbox" id="sheet-bg-toggle-checkbox" name="forminatorform-gs[sheet_bg]"
                                value="1">
                            <span class="slider round"></span>
                        </label>
                        <span
                            class="label-texts"><?php echo esc_html__('Sheet Background Color', 'gsheetconnector-forminator'); ?></span>
                        <span class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span>

                    </div>
                </div>
            </div>
            <!--Row Font Setings 4th div of header settigns  -->
            <div class="sheet_formatting">
                <div class="gscfrmnt-sheet_formatting_row gscfrmnt-sheet_formatting_row">
                    <div class="toggle-button sheet_formatting-row-toggle">
                        <label class="switch" for="sheet_formatting-row-checkbox">
                            <input type="checkbox" id="sheet_formatting-row-checkbox"
                                name="forminatorform-gs[sheet_formatting_row]" value="1">
                            <span class="slider round"></span>
                        </label>
                        <span
                            class="label-texts"><?php echo esc_html__('Row - Font Settings', 'gsheetconnector-forminator'); ?></span>
                        <span class="pro-ver"><?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?></span>
                    </div>
                </div>
            </div>
        </div> <!-- freez_order_sort #end --->
    </div>

    <div class="gs-wpcore-sync-entries">
        <?php echo esc_html__('Sync Entries.', 'gsheetconnector-forminator'); ?>
        <span class="tooltip">
            <img src="<?php echo esc_url(GS_FORMNTR_URL . 'assets/img/help.png'); ?>" class="help-icon">
        </span>
        <a id="gs-wpcore-sync-entries" data-init="yes" class="sync-button-entries">
            <?php echo esc_html__('Click Here to Sync Entries.', 'gsheetconnector-forminator'); ?>
        </a>
        <span class="pro-ver">
            <?php echo esc_html__('PRO', 'gsheetconnector-forminator'); ?>
        </span>
    </div>
</div>