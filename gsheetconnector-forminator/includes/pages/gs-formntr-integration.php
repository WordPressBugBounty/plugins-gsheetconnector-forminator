<?php
$gs_frmin_client_id = get_option('gs_frmin_client_id');
$gs_frmin_secret_id = get_option('gs_frmin_secret_id');
$gs_frmin_manual_code_db = get_option('gs_frmin_access_manual_code');
$gs_formntr_manual_setting = get_option('gs_formntr_manual_setting');
$frmingsc_code = sanitize_text_field(isset($_GET['code']) ? wp_unslash($_GET['code']) : '');
$header = esc_url_raw(admin_url('admin.php?page=formntr-gsheet-config'));

if (isset($_GET['code'])) {
    // Validate the 'code' parameter's format and length
    $frmingsc_code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    $decoded_code = urldecode($frmingsc_code);
    if ($decoded_code === false) {
        // Handle invalid code format or length
        wp_die('Invalid code format or length');
    }

    // Sanitize and escape the 'code' parameter for output safety
    $frmingsc_code = esc_attr($decoded_code);

    // Update the option to indicate a new client secret
    update_option('is_new_client_secret_FORMINGSC', 1);

    // Set the redirect URL to the admin page
    $header = esc_url_raw(admin_url('admin.php?page=formntr-gsheet-config'));
} else {
    // Initialize variables with default values
    $frmingsc_code = '';
    $header = '';
}

?>

<input type="hidden" name="redirect_auth" id="redirect_auth"
    value="<?php echo isset($header) ? esc_attr($header) : ''; ?>">

<input type="hidden" name="get_code_frmin" id="get_code_frmin"
    value="<?php echo (isset($_GET['code']) && $_GET['code'] != "") ? '1' : '0'; ?>">
<input type="hidden" name="frmin_manual_setting" id="frmin_manual_setting"
    value="<?php echo esc_attr($gs_formntr_manual_setting); ?>">

<!--------------------------- Auto setting page -------------->

<div class="card-formntr">
    <div class="lbl-drop-down-select">
        <label
            for="gs_formntr_dro_option"><?php echo esc_html__('Choose Google API Setting :', 'gsheetconnector-forminator'); ?></label>
    </div>
    <div class="drop-down-select-btn">
        <select id="gs_formntr_dro_option" name="gs_formntr_dro_option">
            <option value="formntr_existing" selected>
                <?php echo esc_html__('Use Existing Client/Secret Key (Auto Google API Configuration)', 'gsheetconnector-forminator'); ?>
            </option>
            <option value="formntr_manual" disabled="">
                <?php echo esc_html__('Use Manual Client/Secret Key (Use Your Google API Configuration) (Upgrade To PRO)', 'gsheetconnector-forminator'); ?>
            </option>
        </select>
        <p class="int-meth-btn-formntr"><a
                href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro"
                target="_blank"><input type="button" name="save-method-api-formntr" id=""
                    value="<?php esc_attr_e('Upgrade To PRO', 'gsheetconnector-forminator'); ?>" class="button " /></a>
            <span class="tooltip"> <img src="<?php echo esc_url(GS_FORMNTR_URL); ?>assets/img/help.png"
                    class="help-icon"> <span
                    class="tooltiptext tooltip-right"><?php esc_html_e('Manual Client/Secret Key (Use Your Google API Configuration) method is available in the PRO version of the plugin.', 'gsheetconnector-forminator'); ?></span></span>
        </p>
    </div>
</div>

<div class="wrap gs-form">
    <?php if ($gs_formntr_manual_setting == 0) { ?>
        <div class="card api_existing_setting_frmin" id="googlesheet">
            <h2>
                <span class="title1"><?php echo esc_html('Forminator- ', 'gsheetconnector-forminator'); ?></span>
                <span class="title"><?php echo esc_html('Google Sheet Integration', 'gsheetconnector-forminator'); ?></span>
            </h2>
            <hr>
            <div class="inside">

                <?php if (empty($Code)) { ?>
                    <div class="wpform-gs-alert-kk" id="google-drive-msg">
                        <p class="wpform-gs-alert-heading">
                            <?php echo esc_html__('Authenticate with your Google account, follow these steps:', 'gsheetconnector-forminator'); ?>
                        </p>
                        <ol class="wpform-gs-alert-steps">
                            <li><?php echo esc_html__('Click on the "Sign In With Google" button.', 'gsheetconnector-forminator'); ?>
                            </li>
                            <li><?php echo esc_html__('Grant permissions for the following:', 'gsheetconnector-forminator'); ?>
                                <ul class="wpform-gs-alert-permissions">
                                    <li><?php echo esc_html__('Google Drive', 'gsheetconnector-forminator'); ?></li>
                                    <li><?php echo esc_html__('Google Sheets', 'gsheetconnector-forminator'); ?></li>
                                </ul>
                                <p class="wpform-gs-alert-note">
                                    <?php echo esc_html__('Ensure that you enable the checkbox for each of these services.', 'gsheetconnector-forminator'); ?>
                                </p>
                            </li>
                            <li><?php echo esc_html__('This will allow the integration to access your Google Drive and Google Sheets.', 'gsheetconnector-forminator'); ?>
                            </li>
                        </ol>
                    </div>
                <?php } ?>
                <p class="gs-integration-box">
                    <label
                        style="color: #242628;
                    font-size: 14px;
                    font-weight: 600;
                    line-height: 2.3;"><?php echo esc_html__('Google Access Code', 'gsheetconnector-forminator'); ?></label>
                    <?php
                    $token = get_option('gs_formntr_token');
                    if (!empty($token) && $token !== "") {
                        ?>
                        <input type="text" name="gs-formntr-code" id="gs-formntr-code"
                            value="<?php echo esc_attr(isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : ''); ?>"
                            disabled
                            placeholder="<?php echo esc_html(__('Currently Active', 'gsheetconnector-forminator')); ?>" />
                        <input type="button" name="gs-formntr-deactivate-log" id="gs-formntr-deactivate-log"
                            value="<?php esc_attr_e('Deactivate', 'gsheetconnector-forminator'); ?>"
                            class="button button-primary" />
                        <span class="tooltip"> <img src="<?php echo esc_url(GS_FORMNTR_URL); ?>assets/img/help.png"
                                class="help-icon">
                            <span
                                class="tooltiptext tooltip-right"><?php echo esc_html__('On deactivation, all your data saved with authentication
                        will be removed and you need to reauthenticate with your google account.', 'gsheetconnector-forminator'); ?></span></span>
                        <span class="loading-sign-deactive">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <?php
                    } else {
                        //$frmingsc_auth_url = FORMI_GSC_googlesheet::get_auth_url(FORMI_GSC_googlesheet::clientId,FORMI_GSC_googlesheet::clientSecret);
                        $frmingsc_auth_url = GS_FORMNTR_AUTH_URL . "?client_admin_url=" . GS_FORMNTR_AUTH_REDIRECT_URI . "&plugin=" . GS_FORMNTR_AUTH_PLUGIN_NAME;

                        ?>

                        <input type="text" name="gs-formntr-code" id="gs-formntr-code"
                            value="<?php echo esc_attr($frmingsc_code); ?>"
                            placeholder="<?php echo esc_html(__('Click Sign in with Google', 'gsheetconnector-forminator')); ?>"
                            disabled />
                        <?php if (empty($frmingsc_code)) { ?>
                            <a href="https://oauth.gsheetconnector.com/index.php?client_admin_url=<?php echo esc_url(GS_FORMNTR_AUTH_REDIRECT_URI); ?>&plugin=woocommercegsheetconnector"
                                class="button_forgsc">
                                <img class="custom-image button_formgsc"
                                    src="<?php echo esc_url(GS_FORMNTR_URL); ?>/assets/img/btn_google_signin_dark_pressed_web.gif" />
                            </a>
                        <?php } ?>

                    <?php } ?>
                    <?php
    }
    //resolved - google sheet permission issues - END
    ?>
                <?php if (!empty($_GET['code'])) { ?>
                    <button type="buttonfor" name="save-gs-formntr-code"
                        id="save-gs-formntr-code"><?php echo esc_html(__('Save & Authenticate', 'gsheetconnector-forminator')); ?></button>
                <?php } ?>


                <?php if (empty(get_option('gs_formntr_token'))) { ?>
                <p>
                    <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

                </p>
            <?php } ?>
            </p>


            <?php
            //resolved - google sheet permission issues - START
            if (!empty(get_option('gs_formntr_verify')) && (get_option('gs_formntr_verify') == "invalid-auth")) { ?>
                <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;">
                    <?php echo esc_html(__('Something went wrong! It looks you have not given the permission of Google Drive and Google Sheets from your google account.Please Deactivate Auth and Re-Authenticate again with the permissions.', 'gsheetconnector-forminator')); ?>
                </p>
                <p style="color:#c80d0d;border: 1px solid;padding: 8px;"><img width="350px"
                        src="<?php echo esc_url(GS_FORMNTR_URL); ?>assets/img/permission_screen.png"></p>
                <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;">
                    <?php echo esc_html(__('Also,', 'gsheetconnector-forminator')); ?><a
                        href="https://myaccount.google.com/permissions" target="_blank">
                        <?php echo esc_html(__('Click Here ', 'gsheetconnector-forminator')); ?></a>
                    <?php echo esc_html(__('and if it displays GSheetConnector for WP Contact Forms" under Third-party apps with account access then remove it.', 'gsheetconnector-forminator')); ?>
                </p>
            <?php }
            //resolved - google sheet permission issues - END
            else {

                $wp_token = get_option('gs_formntr_token');
                if (!empty($token) && $token !== "") {
                    $google_sheet = new FORMI_GSC_googlesheet();
                    $email_account = $google_sheet->gsheet_print_google_account_email();

                    if ($email_account) {
                        update_option('formntr_gs_auth_expired_free', 'false');
                        ?>
                        <p class="connected-account">
                            <?php
                            printf(
                                // Translators: %s is the connected email account address.
                                wp_kses_post(__('Connected email account: <u>%s</u>', 'gsheetconnector-forminator')),
                                esc_html($email_account)
                            ); ?>
                        </p>

                    <?php } else {
                        update_option('formntr_gs_auth_expired_free', 'true'); ?>
                        <p style="color:red">
                            <?php echo esc_html(__('Something wrong ! Your Auth Code may be wrong or expired. Please deactivate and do Re-Authentication again. ', 'gsheetconnector-forminator')); ?>
                        </p>
                        <?php
                    }
                }
            }
            ?>

            <div id="frm-gsc-cta" class="frm-gsc-privacy-box">
                <div class="frm-gsc-table">
                    <div class="frm-gsc-less-free">
                        <p><i class="dashicons dashicons-lock"></i>
                            <?php echo esc_html(__('We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused.', 'gsheetconnector-forminator')); ?>
                        </p> <a href="https://gsheetconnector.com/usage-tracking/" target="_blank"
                            rel="noopener noreferrer"><?php echo esc_html(__('Learn more.', 'gsheetconnector-forminator')); ?></a>
                    </div>
                </div>
            </div>

            <p>
                <label><?php echo esc_html('Debug Log', 'gsheetconnector-forminator'); ?></label>
                <button class="frmgsc-logs"><?php echo esc_html('View', 'gsheetconnector-forminator'); ?></button>

                <label><a
                        class="debug-clear"><?php echo esc_html('Clear', 'gsheetconnector-forminator'); ?></a></label><span
                    class="clear-loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </button>
            </p>

            <!-- set nonce -->
           <input type="hidden" 
    name="frmntr-gs-ajax-nonce" 
    id="frmntr-gs-ajax-nonce"
    value="<?php echo esc_attr( wp_create_nonce( 'frmntr-gs-ajax-nonce' ) ); ?>" />

            <p id="gs-formntr-validation-message"></p>
            <span id="deactivate-msg"></span>
          

        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var googleDriveMsg = document.getElementById('google-drive-msg');
            if (googleDriveMsg) {
                // Check if the 'gfgs_token' option is not empty
                if ('<?php echo esc_html(get_option('gs_formntr_token')); ?>' !== '') {
                    googleDriveMsg.style.display = 'none';
                }
            }
        });
    </script>

    <div class="frmgsc-system-Error-logs">
        <button id="copy-logs-btn" onclick="copyLogs()"><?php echo esc_html('Copy Logs', 'gsheetconnector-forminator'); ?></button>

       <div id="logs-content" class="frmdisplayLogs">
    <?php
    $exist_debug_file = get_option( 'frmgs_debug_log' );

    // Check if debug log file exists.
    if ( ! empty( $exist_debug_file ) && file_exists( $exist_debug_file ) ) {
        $display_frm_logs = file_get_contents( $exist_debug_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

        if ( ! empty( $display_frm_logs ) ) {
            // Escape HTML and preserve line breaks.
            echo nl2br( esc_html( $display_frm_logs ) );
        } else {
            echo esc_html__( 'No errors found.', 'gsheetconnector-forminator' );
        }
    } else {
        // Debug log file does not exist.
        echo esc_html__( 'No log file exists as no errors are generated.', 'gsheetconnector-forminator' );
    }
    ?>
</div>

    </div>

    <div class="two-col frmgsc-math-box-help12">
        <div class="col frmgsc-math-box12">
            <header>
                <h3> <?php echo esc_html("Next steps…", 'gsheetconnector-forminator'); ?></h3>
            </header>
            <div class="frmgsc-math-box-content12">
                <ul class="frmgsc-math-list-icon12">

                    <li>
                        <a href="https://wordpress.org/plugins/gsheetconnector-forminator/" target="_blank">
                            <div>
                                <button class="icon-button">
                                    <span class="dashicons dashicons-download"></span>
                                </button>
                                <strong><?php echo esc_html("Upgrade to PRO", 'gsheetconnector-forminator'); ?></strong>
                                <p><?php echo esc_html("Sync Sheets,Entries and much more.", 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>
                        </a>
                    </li>

                    <li>
                        <a href="https://wordpress.org/plugins/gsheetconnector-forminator/" target="_blank">
                            <div>
                                <button class="icon-button">
                                    <span class="dashicons dashicons-download"></span>
                                </button>
                                <strong><?php echo esc_html("Compatibility", 'gsheetconnector-forminator'); ?></strong>
                                <p><?php echo esc_html("Compatibility with Forminator Third-Party Plugins", 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="https://wordpress.org/plugins/gsheetconnector-forminator/" target="_blank">
                            <div>
                                <button class="icon-button">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                </button>
                                <strong><?php echo esc_html("Multi Languages", 'gsheetconnector-forminator'); ?></strong>
                                <p><?php echo esc_html("This plugin supports multi-languages as well!", 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>
                        </a>
                    </li>

                </ul>
            </div>
        </div>

        <!-- 2nd div -->
        <div class="col frmgsc-math-box13">
            <header>
                <h3><?php echo esc_html("Product Support", 'gsheetconnector-forminator'); ?></h3>
            </header>
            <div class="frmgsc-math-box-content13">
                <ul class="frmgsc-math-list-icon13">
                    <li>
                        <a href="https://wordpress.org/plugins/gsheetconnector-forminator/" target="_blank">
                            <span class="dashicons dashicons-book"></span>
                            <div>
                                <strong><?php echo esc_html("Online Documentation", 'gsheetconnector-forminator'); ?></strong>
                                <p><?php echo esc_html("Understand all the capabilities of Forminator GSheetConnector", 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="https://wordpress.org/plugins/gsheetconnector-forminator/" target="_blank">
                            <span class="dashicons dashicons-sos"></span>
                            <div>
                                <strong><?php echo esc_html("Ticket Support", 'gsheetconnector-forminator'); ?></strong>
                                <p><?php echo esc_html("Direct help from our qualified support team", 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="https://wordpress.org/plugins/gsheetconnector-forminator/" target="_blank">
                            <span class="dashicons dashicons-admin-links"></span>
                            <div>
                                <strong><?php echo esc_html("Affiliate Program", 'gsheetconnector-forminator'); ?></strong>
                                <p><?php echo esc_html("Earn flat 30% on every sale!", 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>