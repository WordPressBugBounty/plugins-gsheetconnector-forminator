<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$show_setting = 0;
$selected_method = '';
$authenticated = get_option('gs_formntr_token');
$gsc_formntr_is_valid = get_option('gs_formntr_verify');




$active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'integration'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended


$active_tab_name = '';
if ($active_tab == 'general-settings') {
    $active_tab_name = 'Settings';
}
$sub_tab = isset($_GET['sub_tab']) ? sanitize_text_field(wp_unslash($_GET['sub_tab'])) : 'general_settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended


?>

<?php
switch ($active_tab) {
    case 'general-settings':
        // Render sub-navigation
        echo '<div class="gsc-settings-tabs d-flex gap-15 pl-15 pr-15">';
        $sub_tabs = array(
            'general_settings' => esc_html__('General Settings', 'gsheetconnector-forminator'),
            'role_settings' => esc_html__('Role Permissions', 'gsheetconnector-forminator'),
            'beta_version' => esc_html__('Version Control', 'gsheetconnector-forminator'),

        );

        foreach ($sub_tabs as $sub => $label) {
            $class = ($sub === $sub_tab) ? 'gsc-tab active' : 'gsc-tab';
            echo '<a class="' . esc_attr($class) . '" href="' . esc_url(admin_url('admin.php?page=formntr-gsheet-config&tab=general-settings&sub_tab=' . urlencode($sub))) . '">' . esc_html($label) . '</a>';
        }
        echo '</div> <div class="gsc-settings-card">';

        // Load correct sub-tab content
        switch ($sub_tab) {
            case 'general_settings':
                /** General settings start  */
?>
                <div class="wrap w-100 m-0">
                    <div class="system-general_setting  inner-wrap w-100 bg-white p-40">
                        <div class="info-container">
                            <form method="post">
                                <div class="gsc-access-wrapper">
                                    <div>
                                        <div class="heading mt-0">
                                            <?php echo esc_html__('Plugin Preferences', 'gsheetconnector-forminator'); ?>
                                        </div>
                                        <p><?php echo esc_html(__('Manage how plugin settings and data are handled when the plugin is uninstalled.', 'gsheetconnector-forminator')); ?>
                                        <div class="gsc-setting-text d-flex justify-between align-center pt-15 pb-15 mt-30 bg-white">
                                            <div>
                                                <div class="systemifo fw-600 text-dark">
                                                    <?php echo esc_html__("Delete Plugin Data on Uninstall", 'gsheetconnector-forminator'); ?>
                                                </div>
                                                <label for="formntr_forminatorform_uninstall_settings_free" class="fw-400">
                                                    <?php echo esc_html__("Removes all plugin data (options, metadata) when the plugin is deleted.", 'gsheetconnector-forminator'); ?>
                                                </label>
                                            </div>
                                            <div>
                                                <?php
                                                $get_formntr_uninstall_setting = get_option('gs_frmnt_unistall_plugin_settings');  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                                ?>
                                                <input type="hidden" name="gs_formntr_uninstall_settings" value="No">
                                                <div class="custom-check">
                                                    <input type="checkbox" class="formntr-check-toggle"
                                                        id="gs_formntr_uninstall_settings"
                                                        name="gs_formntr_uninstall_settings" value="Yes"
                                                        <?php checked(1, $get_formntr_uninstall_setting); ?>>

                                                    <label for="gs_formntr_uninstall_settings"
                                                        class="button-toggle"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="gsc-access-info">
                                        <div class='para-heading fw-600 mb-20'>
                                            <?php esc_html_e('Uninstall Data Notice', 'gsheetconnector-forminator'); ?></div>

                                        <ul>
                                            <li><?php echo esc_html__("Enable this option only if you want a complete cleanup", 'gsheetconnector-forminator'); ?>
                                            </li>
                                            <li><?php echo esc_html__("All plugin settings and data will be permanently removed", 'gsheetconnector-forminator'); ?>
                                            </li>
                                            <li><?php echo esc_html__("Incorrect settings can delete all sensitive form and user data", 'gsheetconnector-forminator'); ?>
                                            </li>
                                        </ul>

                                    </div>
                                </div>

                                <div class="text-right mt-30">
                                    <span class="loading-uninstall-free"></span>
                                    <input type="button" class="btn btn-primary gs_formntr_save_uninstall_settings"
                                        name="gs_formntr_save_uninstall_settings"
                                        value="<?php echo esc_html__("Save Settings", "gsheetconnector-forminator"); ?>" />
                                    <div id="formntr-uninstall-msg-free"
                                        class="gsc-msg d-none fw-400 text-dark text-center pt-10 pb-10 manual-margin"></div>


                                    <input type="hidden" name="gs-formntr-setting-ajax-nonce" id="gs-formntr-setting-ajax-nonce"
                                        value="<?php echo esc_attr(wp_create_nonce('gs-formntr-setting-ajax-nonce')); ?>" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="formntr-confirm-uninstall-data-popup-free" class="frmntr-popup-overlay d-none">
                    <div class="frmntr-popup text-center free-to-pro-data">

                        <div class="gsc-modal-title gsc-uninstall-modal-title">
                            <?php echo esc_html__('Confirm Data Deletion', 'gsheetconnector-forminator'); ?>
                        </div>
                        <p class="gsc-modal-text">
                            <?php echo esc_html__('Enabling this option will permanently delete all plugin data, including settings and integrations, when the plugin is uninstalled.', 'gsheetconnector-forminator'); ?>
                        </p>
                        <p class="gsc-modal-text">
                            <?php echo esc_html__('If you plan to upgrade from Free to Pro, you may lose your existing configuration data.', 'gsheetconnector-forminator'); ?>
                        </p>

                        <p class="gsc-modal-text">
                            <?php echo esc_html__('Proceed only if you want a complete cleanup. This action cannot be undone.', 'gsheetconnector-forminator'); ?>
                        </p>


                        <div class="popup-actions d-flex justify-center gap-10">
                            <button type="button" class="btn deactivate-btn" id="formntr-cancel-uninstall-free">
                                <?php echo esc_html__('Cancel', 'gsheetconnector-forminator'); ?>
                            </button>
                            <button type="button" class="btn btn-primary" id="formntr-confirm-enable-uninstall-free">
                                <?php echo esc_html__('Enable Deletion', 'gsheetconnector-forminator'); ?>
                            </button>
                        </div>
                    </div>
                </div>
        <?php
                /** General settings end */
                break;
            case 'role_settings':
                include GS_FORMNTR_PATH . "includes/pages/gs-formntr-role-setting.php";
                break;
            case 'beta_version':
                include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-beta-version.php");
                break;
        }

        echo '</div>';

        ?>
        <div class="gsc-support-card mt-30 welcome-wrapper ml-15">

            <!-- LEFT SIDE -->
            <div class="gsc-support-left">

                <div class="gsc-support-icon d-flex justify-center align-center">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19.8335 14V3.50004C19.8335 3.19062 19.7106 2.89388 19.4918 2.67508C19.273 2.45629 18.9762 2.33337 18.6668 2.33337H3.50016C3.19074 2.33337 2.894 2.45629 2.6752 2.67508C2.45641 2.89388 2.3335 3.19062 2.3335 3.50004V19.8334L7.00016 15.1667H18.6668C18.9762 15.1667 19.273 15.0438 19.4918 14.825C19.7106 14.6062 19.8335 14.3095 19.8335 14ZM24.5002 7.00004H22.1668V17.5H7.00016V19.8334C7.00016 20.1428 7.12308 20.4395 7.34187 20.6583C7.56066 20.8771 7.85741 21 8.16683 21H21.0002L25.6668 25.6667V8.16671C25.6668 7.85729 25.5439 7.56054 25.3251 7.34175C25.1063 7.12296 24.8096 7.00004 24.5002 7.00004Z" fill="#141B38"></path>
                    </svg>
                </div>

                <div class="gsc-content">
                    <div class="support-headings"><?php echo esc_html__('Need more support? We\'re here to help.', 'gsheetconnector-forminator'); ?></div>

                    <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/" target="_blank" class="btn btn-primary mt-10 link-hover-white text-decoration-none">
                        <?php echo esc_html__('Submit a Support Ticket', 'gsheetconnector-forminator'); ?>
                        <svg width="10" height="10" viewBox="0 0 6 8" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.66681 0L0.726807 0.94L3.78014 4L0.726807 7.06L1.66681 8L5.66681 4L1.66681 0Z"></path>
                        </svg>
                    </a>
                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="gsc-support-right">
                <div class="gsc-avatars justify-center">
                    <img src="<?php echo esc_url(GS_FORMNTR_URL); ?>/assets/img/avatar-2.jfif" alt="">
                    <img src="<?php echo esc_url(GS_FORMNTR_URL); ?>/assets/img/avatar-3.png" alt="">
                    <img src="<?php echo esc_url(GS_FORMNTR_URL); ?>/assets/img/avatar-5.jfif" alt="">
                    <img src=" <?php echo esc_url(GS_FORMNTR_URL); ?>/assets/img/avatar-4.png" alt="">
                    <img src="<?php echo esc_url(GS_FORMNTR_URL); ?>/assets/img/avatar.jpeg" alt="">
                </div>
                <p class="text-center"><?php echo esc_html__('Our fast and friendly support team is always happy to help!', 'gsheetconnector-forminator'); ?></p>
            </div>
        </div>
        <?php
        break;
}
