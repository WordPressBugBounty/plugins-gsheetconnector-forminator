<?php
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

$formntr_auth_method           = get_option('gs_formntr_manual_setting'); // NEW
$formntr_authenticated         = get_option('gs_formntr_token'); // Auto
$formntr_per                   = get_option('gs_formntr_verify');
$formntr_per_msg               = __('invalid', 'gsheetconnector-forminator');
$formntr_show_setting          = 0;
$formntr_selected_method       = '';
$formntr_email_account         = '';

if ($formntr_per !== $formntr_per_msg) {
    $formntr_google_sheet =  new FORMI_GSC_googlesheet();
    $formntr_email_account = $formntr_google_sheet->gsheet_print_google_account_email();
    if (!empty($formntr_email_account) && $formntr_per == 'valid') {
        $formntr_selected_method = esc_html__('Use Existing Client/Secret Key (Auto Google API Configuration)', 'gsheetconnector-forminator');
        $formntr_is_authenticated = true;
    } else {
        $formntr_selected_method = esc_html__('Auth Required', 'gsheetconnector-forminator');
    }
} else {
    $formntr_selected_method = esc_html__('Auth Required', 'gsheetconnector-forminator');
}
?>
<div class="wrap w-100 m-0">
    <div class="inner-wrap  w-100 bg-white p-40">
        <div class="gsc-dashboard">

            <div class="row">
                <div class="col-6">
                    <div class="dashboard-left-wrapper mr-15">
                        <!---Start Welcome-Header Section--->
                        <div class="welcome-wrapper mb-30">
                            <div class="welcome-content">
                                <div class="welcome-heading mb-20">
                                    <span><?php echo esc_html__('Welcome To GSheetConnector', 'gsheetconnector-forminator'); ?></span>
                                </div>
                                <p>
                                    <?php echo esc_html__('GSheetConnector is a powerful automation plugin that syncs WordPress data with Google Sheets in real time. It supports WooCommerce, Easy Digital Downloads, and popular form plugins such as Forminator, Gravity Forms, Elementor Forms, along with 10+ additional WordPress integrations for efficient data management.', 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>
                            <div class="unlock-pro-button-sections mt-20">
                                <?php
                                if (!empty($formntr_email_account)) { ?>
                                    <div class="gsfrm-integration-box">
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
                                                            <span class="gsc-connected-label">
                                                                <?php echo esc_html__('Connected Email Account', 'gsheetconnector-forminator'); ?>
                                                            </span>
                                                            <span class="connected-account-manual gsc-connected-email">
                                                                <?php echo esc_html($formntr_email_account); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="gsc-google-auth-right">
                                                    <div class="gsc-connected-pill">
                                                        <span class="dot"></span>
                                                        <?php
                                                        echo esc_html__('Connected', 'gsheetconnector-forminator');

                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="formntr-feed-table-wrap">
                                        <table class="widget-table" id="formntr-feed-table" data-page="1">
                                            <thead>
                                                <tr>
                                                    <th><?php echo esc_html__('Form Name', 'gsheetconnector-forminator'); ?></th>
                                                    <th><?php echo esc_html__('Feed Name', 'gsheetconnector-forminator'); ?></th>
                                                    <th><?php echo esc_html__('Sheet Name', 'gsheetconnector-forminator'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="formntr-feed-table-body">
                                                <tr class="formntr-feed-loading-row">
                                                    <td colspan="3">
                                                        <span class="formntr-loader"></span>
                                                        <span class="formntr-loader-text"><?php echo esc_html__('Loading feeds...', 'gsheetconnector-forminator'); ?></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div class="formntr-pagination d-flex justify-center gap-10 pb-10" id="formntr-pagination-wrap">
                                            <!-- page links injected via AJAX -->
                                        </div>

                                        <input type="hidden" id="formntr-ajax-nonce-pagination" value="<?php echo esc_attr(wp_create_nonce('formntr-ajax-nonce-pagination')); ?>" />
                                    </div>
                                <?php } else {

                                    $let_connect_url = ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

                                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                    $let_connect_url = esc_html(admin_url('admin.php?page=formntr-gsheet-config&tab=integration'));

                                ?>
                                    <a class="btn btn-primary link-hover-white text-decoration-none" href="<?php echo esc_html($let_connect_url); ?>">
                                        <?php echo esc_html__("Let's Connect", 'gsheetconnector-forminator'); ?>
                                    </a>

                                <?php } ?>
                            </div>
                        </div>
                        <!---End Welcome-Header Section--->

                        <!-- HERO -->
                        <div class="set-up-guid-wrapper welcome-wrapper">
                            <div class="welcome-content">
                                <div class="welcome-heading mb-10">
                                    <span><?php echo esc_html__('Setup Guide & Troubleshooting', 'gsheetconnector-forminator'); ?></span>
                                </div>
                                <p>
                                    <?php echo esc_html__('Sync Forminator data with Google Sheets in real-time effortlessly and accurately.', 'gsheetconnector-forminator'); ?>
                                </p>
                            </div>

                            <div class="setup-content-data mt-20">
                                <div class="setup-row d-flex justify-between gap-20">
                                    <div class="google-api-setting-guide">
                                        <div class="dashboard-pro-small-head"><?php echo esc_html__('Getting Started', 'gsheetconnector-forminator'); ?></div>
                                        <ul>
                                            <li><a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/installation-process-free-version" target="_blank"><?php echo esc_html__('Installation Process', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/integration-with-google-existing-method" target="_blank"><?php echo esc_html__('Integration with Google (Existing Method)', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/plugin-settings-free-version" target="_blank"><?php echo esc_html__('Plugin Settings', 'gsheetconnector-forminator'); ?></a></li>
                                        </ul>
                                    </div>
                                    <div class="google-api-setting-guide">
                                        <div class="dashboard-pro-small-head"><?php echo esc_html__('Docs & Troubleshooting', 'gsheetconnector-forminator'); ?></div>
                                        <ul>
                                            <li><a href="https://www.gsheetconnector.com/docs/general/licence-management" target="_blank"><?php echo esc_html__('Licence Management', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/general/renew-subscription" target="_blank"><?php echo esc_html__('Renew Subscription', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/general/cancel-subscription" target="_blank"><?php echo esc_html__('Cancel Subscription', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/general/upgrade-plugin-pro-versions" target="_blank"><?php echo esc_html__('Upgrade Plugin', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/general/how-to-enable-debugging-in-wordpress" target="_blank"><?php echo esc_html__('How to Enable Debugging in WordPress', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/general/common-errors-issues#toc-heading-1" target="_blank"><?php echo esc_html__('Invalid OAuth2 token', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/general/how-to-change-date-time-format-and-time-zone-in-google-sheets" target="_blank"><?php echo esc_html__('Change Date/Time Format and Time Zone in Google Sheets', 'gsheetconnector-forminator'); ?></a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="setup-row">
                                    <div class="google-api-setting-guide">
                                        <div class="dashboard-pro-small-head"><?php echo esc_html__('Additional Resources', 'gsheetconnector-forminator'); ?></div>
                                        <ul>
                                            <li><a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/integration-with-google-manual-method" target="_blank"><?php echo esc_html__('Integration with Google (Manual Method)', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/service-account-setting-pro-version" target="_blank"><?php echo esc_html__('Integration with Google (Service Method)', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/plugin-settings-pro-version" target="_blank"><?php echo esc_html__('Plugin Feed Pro Settings', 'gsheetconnector-forminator'); ?></a></li>
                                            <li><a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/system-status" target="_blank"><?php echo esc_html__('System Status', 'gsheetconnector-forminator'); ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="plugin-category-wrapper welcome-wrapper ml-15">
                        <div class="welcome-heading mb-10">
                            <span><?php echo esc_html__('Plugins by Category', 'gsheetconnector-forminator'); ?></span>
                        </div>
                        <p>
                            <?php echo esc_html__('Find the perfect connector for your WordPress workflow.', 'gsheetconnector-forminator'); ?>
                        </p>
                        <div class="plugin-category-section mt-30">
                            <a href="https://www.gsheetconnector.com/plugins" target="_blank" class="plugin-category-box text-decoration-none">
                                <div class="plugin-category-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text w-6 h-6 text-emerald-600" aria-hidden="true">
                                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                        <path d="M10 9H8"></path>
                                        <path d="M16 13H8"></path>
                                        <path d="M16 17H8"></path>
                                    </svg>
                                </div>
                                <div class="plugin-category-content">
                                    <div class="plugin-category-name fw-600">
                                        <?php echo esc_html__('Contact Form Connectors', 'gsheetconnector-forminator'); ?>
                                    </div>
                                    <div class="plugin-category-badge">
                                        <?php echo esc_html__('6 plugins available', 'gsheetconnector-forminator'); ?>
                                    </div>
                                </div>
                                <div class="plugin-category-arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>

                            <a href="https://www.gsheetconnector.com/plugins" target="_blank" class="plugin-category-box text-decoration-none">
                                <div class="plugin-category-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-6 h-6 text-emerald-600" aria-hidden="true">
                                        <circle cx="8" cy="21" r="1"></circle>
                                        <circle cx="19" cy="21" r="1"></circle>
                                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                                    </svg>
                                </div>
                                <div class="plugin-category-content">
                                    <div class="plugin-category-name fw-600">
                                        <?php echo esc_html__('eCommerce Connectors', 'gsheetconnector-forminator'); ?>
                                    </div>
                                    <div class="plugin-category-badge">
                                        <?php echo esc_html__('2 plugins available', 'gsheetconnector-forminator'); ?>
                                    </div>
                                </div>
                                <div class="plugin-category-arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>

                            <a href="https://www.gsheetconnector.com/plugins" target="_blank" class="plugin-category-box text-decoration-none">
                                <div class="plugin-category-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-panels-top-left w-6 h-6 text-emerald-600" aria-hidden="true">
                                        <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                        <path d="M3 9h18"></path>
                                        <path d="M9 21V9"></path>
                                    </svg>
                                </div>
                                <div class="plugin-category-content">
                                    <div class="plugin-category-name fw-600">
                                        <?php echo esc_html__('Page Builder Forms', 'gsheetconnector-forminator'); ?>
                                    </div>
                                    <div class="plugin-category-badge">
                                        <?php echo esc_html__('3 plugins available', 'gsheetconnector-forminator'); ?>
                                    </div>
                                </div>
                                <div class="plugin-category-arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                            <a href="https://www.gsheetconnector.com/gsheetconnector-for-wp-core" target="_blank" class="plugin-category-box text-decoration-none">
                                <div class="plugin-category-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-database w-6 h-6 text-emerald-600" aria-hidden="true">
                                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                        <path d="M3 5V19A9 3 0 0 0 21 19V5"></path>
                                        <path d="M3 12A9 3 0 0 0 21 12"></path>
                                    </svg>
                                </div>
                                <div class="plugin-category-content">
                                    <div class="plugin-category-name fw-600">
                                        <?php echo esc_html__('WP Core Connector', 'gsheetconnector-forminator'); ?>
                                    </div>
                                    <div class="plugin-category-badge">
                                        <?php echo esc_html__('1 plugin available', 'gsheetconnector-forminator'); ?>
                                    </div>
                                </div>
                                <div class="plugin-category-arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>

                            <a href="https://profiles.wordpress.org/westerndeal/" target="_blank" class="plugin-category-box text-decoration-none">
                                <div class="plugin-category-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gift w-6 h-6 text-emerald-600" aria-hidden="true">
                                        <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                                        <path d="M12 8v13"></path>
                                        <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                                        <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                                    </svg>
                                </div>
                                <div class="plugin-category-content">
                                    <div class="plugin-category-name fw-600">
                                        <?php echo esc_html__('Free Plugins', 'gsheetconnector-forminator'); ?>
                                    </div>
                                    <div class="plugin-category-badge">
                                        <?php echo esc_html__('12 plugins available', 'gsheetconnector-forminator'); ?>
                                    </div>
                                </div>
                                <div class="plugin-category-arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                        </div>
                    </div>
                    <!---Start Support  ticket--->

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

                    <!---End Support  ticket--->
                </div>
            </div>


            <!---Start PRO FEATURE--->
            <div class="pro-container mt-30 welcome-wrapper">
                <span class="pro-badge"><?php echo esc_html(__('Amazing Key Features', 'gsheetconnector-forminator')); ?></span>
                <div class="welcome-heading mb-15 mt-20"><?php echo esc_html(__('Everything You Need to Sync Data', 'gsheetconnector-forminator')); ?></div>
                <p>
                    <?php echo esc_html(__('Common features shared across every GSheetConnector Pro add-on built for reliability, flexibility, and scale.', 'gsheetconnector-forminator')); ?>
                </p>

                <!-- LEFT -->
                <div class="d-flex gap-30 mt-30 d-flex-responsiveness">
                    <div class="pro-left w-50">
                        <div class="list dashboard-pro-features">
                            <ul>
                                <li><?php echo esc_html__('Google Sheets API v4', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('One-Click Authentication', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Authenticated Email Display', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Click & Fetch Automation', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Create New Spreadsheet', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Manually Adding Sheet Name & Tab Name', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Freeze Header / Color Option', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Automated Sheet Name & Tab Name', 'gsheetconnector-forminator'); ?></li>
                            </ul>
                            <ul>
                                <li><?php echo esc_html__('Multiple Forms to Sheets', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Send Missed Entries to GSheet', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Custom Ordering', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Drag-and-Drop Column Order', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Enable/Disable Sheet Headers', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Image / PDF Attachment Link', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Dynamic Fields & Advanced Fields', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Sync Past Entries', 'gsheetconnector-forminator'); ?></li>
                            </ul>
                            <ul>
                                <li><?php echo esc_html__('Role Management', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Quick Configuration', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Multi-Language Support', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Multi-Site Support', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Latest WP & PHP Support', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Excellent Priority Customer Support', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Comprehensive Documentation', 'gsheetconnector-forminator'); ?></li>
                            </ul>
                        </div>


                        <div class="pro-actions mt-30 gap-20">
                            <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" target="_blank"><button class="pro-btn"> <?php echo esc_html(__('Upgrade to Pro', 'gsheetconnector-forminator')); ?></button></a>
                            <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro#features" target="_blank"><?php echo esc_html(__('View Full Features', 'gsheetconnector-forminator')); ?></a>
                        </div>


                    </div>

                    <!-- RIGHT -->
                    <div class="pro-right w-50">

                        <div class="right-card">

                            <!-- FLOW -->
                            <div class="flow-ui">
                                <div class="flow-step"><?php echo esc_html(__('Form', 'gsheetconnector-forminator')); ?></div>
                                <div class="line"></div>
                                <div class="flow-step mid"><?php echo esc_html(__('Processing', 'gsheetconnector-forminator')); ?></div>
                                <div class="line"></div>
                                <div class="flow-step success"><?php echo esc_html(__('Sheet', 'gsheetconnector-forminator')); ?></div>
                            </div>

                            <!-- STATS -->
                            <div class="sync-stats">
                                <div>
                                    <strong><?php echo esc_html(__('Instant', 'gsheetconnector-forminator')); ?></strong>
                                    <p><?php echo esc_html(__('Real-time updates', 'gsheetconnector-forminator')); ?></p>
                                </div>
                                <div>
                                    <strong><?php echo esc_html(__('100%', 'gsheetconnector-forminator')); ?></strong>
                                    <p><?php echo esc_html(__('Accuracy', 'gsheetconnector-forminator')); ?></p>
                                </div>
                                <div>
                                    <strong><?php echo esc_html(__('Flexible', 'gsheetconnector-forminator')); ?></strong>
                                    <p><?php echo esc_html(__('Custom mapping', 'gsheetconnector-forminator')); ?></p>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>
            </div>
            <!---End PRO FEATURE--->
            <!---Start Video Tutorial Section--->

            <!---End Video Tutorial Section--->
        </div>
    </div>
</div>