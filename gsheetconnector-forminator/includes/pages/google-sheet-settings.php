<?php
/*
 * Google Sheet configuration and settings page
 * @since 1.0.15
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab-switcher parameter for display only, not a state-changing action; value is properly sanitized.
$active_tab = (isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])))
    ? sanitize_text_field(wp_unslash($_GET['tab'])) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab-switcher parameter for display only, not a state-changing action; value is properly sanitized.
    : 'integration';

$active_tab_name = '';
if ($active_tab == 'integration') {
    $active_tab_name = 'Integration';
} elseif ($active_tab == 'google-sheet') {
    $active_tab_name = 'Form Feeds';
} elseif ($active_tab == 'general-settings') {
    $active_tab_name = 'Settings';
} elseif ($active_tab == 'system-status') {
    $active_tab_name = 'System Status';
} elseif ($active_tab == 'extensions') {
    $active_tab_name = 'Extensions';
} elseif ($active_tab == 'dashboard') {
    $active_tab_name = 'Dashboard';
}


// Check plugin version and subscription plan
$plugin_version = defined('GS_FORMNTR_VERSION') ? GS_FORMNTR_VERSION : 'N/A';

$selected_method = ""; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$authenticated = get_option('gs_formntr_token'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$gsfrm_manual_setting = 0; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$gsc_gsfrm_is_valid = get_option('gs_formntr_verify'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$is_authenticated = false; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ((!empty($authenticated) && $gsc_gsfrm_is_valid == 'valid' && $gsfrm_manual_setting == 0)) {
    $selected_method = esc_html__('Existing', 'gsheetconnector-forminator'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $is_authenticated = true; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
} else {
    $selected_method = esc_html__('Auth Required', 'gsheetconnector-forminator'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
}


/** notification code start */
$show_auth_notice =  !$is_authenticated; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$show_showpro_notice = // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    !formntr_is_dismissed('showpro') &&
    !formntr_is_snoozed('showpro');


$show_enhance_notice = // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    !formntr_is_dismissed('enhance') &&
    !formntr_is_snoozed('enhance');

if (!get_option('formntr_plugin_activated_at')) {
    update_option('formntr_plugin_activated_at', time());
}


$install_time = (get_option('formntr_plugin_activated_at')); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$is_time_passed = $install_time && (time() -  $install_time >= 2 * DAY_IN_SECONDS); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound


$is_dismissed = formntr_is_dismissed('review'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$is_snoozed = formntr_is_snoozed('review'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$show_review_notice = // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $is_time_passed &&
    !$is_dismissed &&
    !$is_snoozed;


function formntr_is_dismissed($key) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
{
    return get_option('formntr_notice_' . $key) === 'dismissed';
}

function formntr_is_snoozed($key) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
{
    $time = get_option('formntr_notice_' . $key . '_time');
    return $time && (time() - $time < 15 * DAY_IN_SECONDS);
}

$has_notice = // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $show_showpro_notice ||
    $show_review_notice ||
    $show_auth_notice ||
    $show_enhance_notice;
?>

<div class="d-none">
    <div class="gsfrm-selected-method" data-value="<?php echo esc_attr($selected_method); ?>">
    </div>
</div>

<div class="gsfrm-free">
    <!--Start NOTICE BAR-->
    <?php if (! isset($_COOKIE['gsheet_frmntr_pro_notice_dismissed'])) { ?>
        <div id="pro-notice-bar" class="pro-header-notice">
            <span
                class="pro-notice-bar-message"><?php echo esc_html__("You`re using GSheetConnector for Forminator Forms. To unlock more features consider ", 'gsheetconnector-forminator'); ?><a
                    href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" target="_blank"
                    rel="noopener"><?php echo esc_html__('upgrading to Pro', 'gsheetconnector-forminator'); ?></a></span>
            <button type="button" id="gsfrm-pro-dismiss-header-notice" title="Dismiss this message" data-page="overview"
                class="pro-dismiss">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M15.8327 5.34175L14.6577 4.16675L9.99935 8.82508L5.34102 4.16675L4.16602 5.34175L8.82435 10.0001L4.16602 14.6584L5.34102 15.8334L9.99935 11.1751L14.6577 15.8334L15.8327 14.6584L11.1744 10.0001L15.8327 5.34175Z"
                        fill="white"></path>
                </svg>
            </button>

            <input type="hidden" id="frmntr-gs-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('frmntr-gs-ajax-nonce')); ?>" />
        </div>
    <?php } ?>

    <!-- notification start  -->
    <div class="notification-formntr-notice-slider">
        <input type="hidden" id="frmntr-gs-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('frmntr-gs-ajax-nonce')); ?>" />
        <div class="notification-formntr-slider-track">
            <!--  slider one-->
            <?php if ($show_showpro_notice && $is_authenticated) { ?>
                <div class="notification-formntr-slide">
                    <div class="formntr-Showpro-banner">

                        <div class="formntr-Showpro-content">
                            <div class="formntr-Showpro-heading">
                                <?php esc_html_e('Unlock Advance Features of Forminator Forms Pro version 🚀', 'gsheetconnector-forminator'); ?>
                            </div>

                            <p>
                                <?php esc_html_e('Use advanced features like Manual Authentication and automatic field mapping, no need to create columns in Google Sheets manually.', 'gsheetconnector-forminator'); ?>
                            </p>
                            <p>
                                <?php esc_html_e('Choose only the fields you need with simple toggles, use advanced tags, sync past form entries, and get priority support.', 'gsheetconnector-forminator'); ?>
                            </p>

                            <div class="formntr-notification-actions">

                                <a href="<?php echo esc_url('https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro'); ?>"
                                    target="_blank" rel="noopener noreferrer" class="formntr-btn-Showpro gsc-review-btn link-hover-white">
                                    <?php esc_html_e('View License Types', 'gsheetconnector-forminator'); ?>
                                </a>

                                <a href="<?php echo esc_url('https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro#compare'); ?>"
                                    target="_blank" rel="noopener noreferrer" class="formntr-btn-secondary">
                                    <?php esc_html_e('Compare Features', 'gsheetconnector-forminator'); ?>
                                </a>

                                <button class="formntr-Showpro-btn-later" data-key="showpro">
                                    <?php esc_html_e('Maybe Later', 'gsheetconnector-forminator'); ?>
                                </button>

                            </div>
                        </div>
                        <button class="formntr-showpro-close" data-key="showpro">✕</button>
                    </div>
                </div>
            <?php } ?>

            <!--  slider two-->

            <?php if ($show_review_notice && $is_authenticated) { ?>
                <div class="notification-formntr-slide">
                    <div class="formntr-review-banner">

                        <div class="formntr-review-content">
                            <div class="formntr-review-heading">
                                <?php esc_html_e('Enjoying the Plugin?', 'gsheetconnector-forminator'); ?>
                            </div>

                            <p>
                                <?php esc_html_e('If you are enjoying the plugin, please consider leaving a 5-star review. Your support helps us improve and grow.', 'gsheetconnector-forminator'); ?>
                            </p>

                            <div class="formntr-notification-actions">

                                <a href="<?php echo esc_url('https://wordpress.org/plugins/gsheetconnector-forminator/#reviews'); ?>"
                                    target="_blank" rel="noopener noreferrer" class="formntr-btn-review gsc-review-btn link-hover-white">
                                    <?php esc_html_e('Ok, you deserve it!', 'gsheetconnector-forminator'); ?>
                                </a>

                                <button class="formntr-review-dismiss-btn formntr-dismiss-btn" data-key="review">
                                    <?php esc_html_e('I already did', 'gsheetconnector-forminator'); ?>
                                </button>


                                <a href="<?php echo esc_url('https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/introduction'); ?>"
                                    target="_blank" rel="noopener noreferrer" class="formntr-btn-secondary">
                                    <?php esc_html_e('I need help', 'gsheetconnector-forminator'); ?>
                                </a>


                                <button class="formntr-review-btn-later" data-key="review">
                                    <?php esc_html_e('Maybe Later', 'gsheetconnector-forminator'); ?>
                                </button>

                            </div>
                        </div>
                        <button class="formntr-review-close" data-key="review">✕</button>
                    </div>
                </div>
            <?php } ?>

            <!-- Slider three-->
            <?php if ($show_enhance_notice && $is_authenticated) { ?>
                <div class="notification-formntr-slide">
                    <div class="formntr-enhance-banner">
                        <div class="formntr-enhance-content">

                            <div class="formntr-enhance-content-header">
                                <?php esc_html_e(' Enhance Your Setup', 'gsheetconnector-forminator'); ?>
                            </div>
                            <p>

                                <?php esc_html_e('Extend your workflow with our add-ons.', 'gsheetconnector-forminator'); ?>
                            </p>
                            <p>
                                <?php esc_html_e('Discover tools that integrate seamlessly and help you get more done.', 'gsheetconnector-forminator'); ?>
                            </p>
                            <div class="formntr-notification-actions ">
                                <a href="https://www.gsheetconnector.com/plugins" target="_blank" class="gsc-btn-ad link-hover-white">
                                    Explore Add-ons
                                </a>
                                <a href="<?php echo esc_url('https://www.gsheetconnector.com/docs'); ?>" target="_blank"
                                    class="formntr-btn-enhance link-hover-white">
                                    <?php esc_html_e('View Details', 'gsheetconnector-forminator'); ?>
                                </a>
                                <button class="formntr-enhance-btn-later" data-key="enhance">Maybe Later</button>
                            </div>

                        </div>

                        <button class="formntr-enhance-close" data-key="enhance">✕</button>
                    </div>
                </div>
            <?php } ?>

            <!--  slider four -->
            <?php if ($show_auth_notice) { ?>
                <div class="notification-formntr-slide">
                    <div class="formntr-activate-banner">

                        <div class="formntr-activate-content">
                            <div class="formntr-activate-content-header">
                                <?php esc_html_e('Authenticate with Your Google Account', 'gsheetconnector-forminator'); ?>
                            </div>

                            <p>
                                <?php esc_html_e('Your connection has expired or hasn’t been set up yet.', 'gsheetconnector-forminator'); ?>
                            </p>

                            <p>
                                <?php esc_html_e('Please reauthenticate with your google account to continue syncing data without interruptions.', 'gsheetconnector-forminator'); ?>
                            </p>

                            <div class="formntr-activate-actions">

                                <a href="<?php echo esc_url(admin_url('admin.php?page=formntr-gsheet-config&tab=integration')); ?>"
                                    class="formntr-btn-activate link-hover-white text-decoration-none">
                                    <?php esc_html_e('Authenticate Now', 'gsheetconnector-forminator'); ?>
                                </a>


                                <a href="<?php echo esc_url('https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector'); ?>"
                                    target="_blank" rel="noopener noreferrer" class="formntr-btn-secondary">
                                    <?php esc_html_e('Learn How', 'gsheetconnector-forminator'); ?>
                                </a>

                            </div>

                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- Right Side Arrows -->

            <?php if ($has_notice) { ?>
                <div class="notification-formntr-slider-arrows">
                    <button class="notification-formntr-slider-btn prev">❮</button>
                    <button class="notification-formntr-slider-btn next">❯</button>
                </div>
            <?php } ?>

        </div>
    </div>

    <!-- notification end  -->


    <!--Start Gsheet-Header Section-->
    <div class="gsheet-header-wrapper pt-10 pb-10 justify-between bg-white">
        <div class="container">
            <div class="row justify-between align-center">
                <div class="left-gsheet-header d-flex align-center">
                    <div class="gsheet-header-logo">
                        <a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector" target="_blank"><i
                                class="d-block"></i></a>
                    </div>
                    <div class="gsheet-header-logo-text">
                        <a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector"
                            class="text-decoration-none" target="_blank">
                            <div class="line-height-zero m-0">
                                <span
                                    class="title fw-600"><?php echo esc_html__('GSheetConnector for Forminator Forms', 'gsheetconnector-forminator'); ?></span>
                            </div>
                        </a>
                        <small
                            class="p-0"><?php echo esc_html__('v', 'gsheetconnector-forminator'); ?><?php echo esc_html($plugin_version); ?>
                        </small>
                    </div>
                </div>
                <div class="right-gsheet-header">
                    <ul class="d-flex gap-10">
                        <li>
                            <a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector"
                                class="d-flex justify-center align-center bg-white" title="Document" target="_blank">
                                <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M18 6.00002V6.75002H18.75V6.00002H18ZM15.7172 2.32614L15.6111 1.58368L15.7172 2.32614ZM4.91959 3.86865L4.81353 3.12619H4.81353L4.91959 3.86865ZM5.07107 6.75002H18V5.25002H5.07107V6.75002ZM18.75 6.00002V4.30604H17.25V6.00002H18.75ZM15.6111 1.58368L4.81353 3.12619L5.02566 4.61111L15.8232 3.0686L15.6111 1.58368ZM4.81353 3.12619C3.91638 3.25435 3.25 4.0227 3.25 4.92895H4.75C4.75 4.76917 4.86749 4.63371 5.02566 4.61111L4.81353 3.12619ZM18.75 4.30604C18.75 2.63253 17.2678 1.34701 15.6111 1.58368L15.8232 3.0686C16.5763 2.96103 17.25 3.54535 17.25 4.30604H18.75ZM5.07107 5.25002C4.89375 5.25002 4.75 5.10627 4.75 4.92895H3.25C3.25 5.9347 4.06532 6.75002 5.07107 6.75002V5.25002Z"
                                        fill="#666"></path>
                                    <path d="M8 12H16" stroke="#666" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path d="M8 15.5H13.5" stroke="#666" stroke-width="1.5" stroke-linecap="round">
                                    </path>
                                    <path
                                        d="M4 6V19C4 20.6569 5.34315 22 7 22H17C18.6569 22 20 20.6569 20 19V14M4 6V5M4 6H17C18.6569 6 20 7.34315 20 9V10"
                                        stroke="#666" stroke-width="1.5" stroke-linecap="round"></path>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/"
                                class="d-flex justify-center align-center bg-white" title="Support" target="_blank">
                                <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M18 6L14.8284 9.17157M14.8284 9.17157C14.1046 8.44772 13.1046 8 12 8C10.8954 8 9.89543 8.44772 9.17157 9.17157M14.8284 9.17157C15.5523 9.89543 16 10.8954 16 12C16 13.1046 15.5523 14.1046 14.8284 14.8284M18 18L14.8284 14.8284M14.8284 14.8284C14.1046 15.5523 13.1046 16 12 16C10.8954 16 9.89543 15.5523 9.17157 14.8284M6 18L9.17157 14.8284M9.17157 14.8284C8.44772 14.1046 8 13.1046 8 12C8 10.8954 8.44772 9.89543 9.17157 9.17157M6 6L9.17157 9.17157M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                        stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/plugins/gsheetconnector-forminator/#developers"
                                class="d-flex justify-center align-center bg-white" title="Changelog" target="_blank">
                                <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M19.4423 2.60315C19.7838 2.77155 20 3.11926 20 3.50001V7.46482L20.8906 8.05856C22.2084 8.93711 23 10.4162 23 12C23 13.5838 22.2084 15.0629 20.8906 15.9415L20 16.5352V19.5C20 19.8774 19.7876 20.2226 19.4507 20.3927C19.1139 20.5627 18.7101 20.5287 18.4064 20.3048C18.4064 20.3048 18.4064 20.3048 18.4064 20.3048C18.4064 20.3048 18.4064 20.3047 18.4063 20.3047L18.4063 20.3047L18.4054 20.3041L18.4012 20.301L18.3831 20.2876L18.3098 20.2344C18.2453 20.1876 18.1506 20.1194 18.0313 20.0349C17.7926 19.8657 17.4571 19.6319 17.0712 19.3747C16.2873 18.8523 15.3391 18.2625 14.5765 17.9059C13.1878 17.2566 11.7408 16.7733 10.6322 16.4513C10.1547 16.3125 9.74373 16.2048 9.43209 16.1275C8.63487 17.4199 8.92926 19.1226 10.1451 20.0682C11.3765 21.026 10.6993 23 9.13919 23H6C5.59997 23 5.23843 22.7616 5.08085 22.3939L4.69925 21.5035C3.87957 19.5909 3.83735 17.4342 4.58156 15.491L4.62696 15.3725C2.51738 14.8594 1 12.9633 1 10.7539C1 8.12839 3.12838 6.00001 5.75387 6.00001H9C9.02628 6.00001 9.05256 6.00104 9.07876 6.00311C9.07893 6.00313 9.0791 6.00314 9.07927 6.00315C9.07943 6.00317 9.07959 6.00318 9.07974 6.00319C9.07975 6.00319 9.07975 6.00319 9.07976 6.00319L9.08164 6.00333L9.10038 6.00461C9.1185 6.00579 9.14773 6.00754 9.18726 6.00945C9.26636 6.01329 9.38647 6.01774 9.54125 6.01952C9.85127 6.02309 10.2977 6.01586 10.8305 5.97193C11.9038 5.8834 13.2878 5.64894 14.6043 5.08164C15.3591 4.75639 16.2945 4.1762 17.0738 3.64858C17.456 3.38981 17.7874 3.15279 18.023 2.98068C18.1406 2.89473 18.2339 2.82527 18.2972 2.77773L18.369 2.72362L18.3866 2.71022L18.3906 2.70716L18.3913 2.70662L18.3913 2.70658L18.3914 2.70655C18.6934 2.47485 19.1009 2.43476 19.4423 2.60315ZM8 8.00001H5.75387C4.23295 8.00001 3 9.23295 3 10.7539C3 12.1213 4.00336 13.2816 5.35646 13.4789L6.14107 13.5933L8 13.8515V8.00001ZM10 14.2079C10.3214 14.2886 10.7267 14.396 11.1901 14.5306C12.3557 14.8692 13.9087 15.3859 15.4235 16.0941C16.2629 16.4866 17.2274 17.082 18 17.5909V16V8.00001V5.43572C17.2289 5.9496 16.2582 6.54673 15.3957 6.91837C13.8127 7.6005 12.1967 7.86604 10.9949 7.96516C10.6233 7.9958 10.2876 8.01083 10 8.0169V14.2079ZM7.36806 15.7829L6.64962 15.6832L6.44927 16.2063C5.89112 17.6637 5.92278 19.2812 6.53754 20.7157L6.6594 21H8.22938C6.9697 19.5684 6.63958 17.5343 7.36806 15.7829ZM20 14.1152C20.6294 13.5985 21 12.8238 21 12C21 11.1762 20.6294 10.4015 20 9.88478V14.1152Z"
                                        fill="#666" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--End Gsheet-Header Section-->

    <!--Start Breadcrumb Section-->
    <div class="breadcrumb-wrapper pt-13 pb-13 text-uppercase fw-500 text-gray">
        <div class="container">
            <a href="<?php echo esc_html(admin_url('admin.php?page=formntr-gsheet-config&tab=dashboard')); ?>"
                class="text-primary text-decoration-none"><?php echo esc_html(__('Google Sheet', 'gsheetconnector-forminator')); ?></a>
            <span>/</span>
            <span><?php echo esc_html($active_tab_name); ?></span>
        </div>
    </div>
    <!--End Breadcrumb Section-->

    <?php
    $tabs = array(
        'dashboard'        => esc_html(__('Dashboard', 'gsheetconnector-forminator')),
        'integration'      => esc_html(__('Integration', 'gsheetconnector-forminator')),
        'google-sheet'     => esc_html(__('Form Feeds', 'gsheetconnector-forminator')),
        'general-settings' => esc_html(__('Settings', 'gsheetconnector-forminator')),
        'system-status'    => esc_html(__('System Status', 'gsheetconnector-forminator')),
        'extensions'       => esc_html(__('Extensions', 'gsheetconnector-forminator')),
    );


    echo '<div class="nav-tab-wrapper d-flex justify-flex-start w-100 m-0">';
    foreach ($tabs as $tab => $name) {
        $class = ($tab == $active_tab) ? ' nav-tab-active' : '';


        echo '<a class="nav-tab  fw-500 text-center text-decoration-none position-relative' .
            esc_attr($class) .
            '" 
    href="' .
            esc_url("?page=formntr-gsheet-config&tab=" . urlencode($tab)) .
            '" >' .
            esc_html($name) .
            "</a>";
    }
    echo "</div>";
    switch ($active_tab) {

        case "dashboard":
            include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-dashboard.php");
            break;

        case 'integration':
            echo '<div class="wrap w-100 m-0"><div class="inner-wrap w-100 bg-white p-40">';
            include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-integration.php");
            echo '</div></div>';
            break;

        case 'google-sheet':
            echo '<div class="wrap  w-100 m-0"><div class="inner-wrap w-100 bg-white p-40">';
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- isset() checks only, used to route to the correct admin sub-page template; no data is read or written, no state-changing action.
            if (isset($_GET['form_id']) && isset($_GET['feed_id'])) {
                include(GS_FORMNTR_PATH . "includes/pages/edit-feed.php"); //edit
            } else if (isset($_GET['form_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- isset() check only, used to route to the correct admin sub-page template; no data is read or written, no state-changing action.
                include(GS_FORMNTR_PATH . "includes/pages/edit-sheet.php"); //form edit

            } else {
                include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-google-sheet.php"); //form list

            }
            echo '</div></div>';
            break;
        case 'general-settings':
            include GS_FORMNTR_PATH . "includes/pages/gs-formntr-general-settings.php";
            break;
        case 'system-status':
            echo '<div class="wrap w-100 m-0"><div class="inner-wrap  w-100 bg-white p-40">';
            include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-systeminfo.php");
            echo '</div></div>';
            break;
        case 'extensions':
            include(GS_FORMNTR_PATH . "includes/pages/extensions/extensions.php");
            break;
    }
    ?>
</div>


<!--Start Common Pro Feature-->
<div class="gsfrm-free">
    <?php if ($active_tab != 'dashboard') { ?>
        <div class="common-section-gsc-promo-wrapper">
            <!-- Left Image Area -->
            <div class="d-flex flex-wrap gap-50 align-center">
                <div class="formnator-to-gsheet">
                    <img src="<?php echo esc_url(GS_FORMNTR_URL); ?>/assets/img/forminator-addon.webp">
                </div>
                <!-- Right Content -->
                <div class="common-section-gsc-promo-content">
                    <div class="common-section-heading"><?php echo esc_html(__('Advanced Tools for Easy Spreadsheet Control','gsheetconnector-forminator')); ?></div>
                    <p class="mb-0"><?php echo esc_html(__('Improve your sheet management with smart automation and flexible customization features.','gsheetconnector-forminator')); ?></p>
                    <div class="d-flex gap-40">
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
                    <div class="mt-30 d-flex align-center gap-20">
                        <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" target="_blank" class="btn btn-primary link-hover-white text-decoration-none">Upgrad Now</a>
                        <a class="text-decoration-none free-pro-btn" href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro#compare" target="_blank">Free vs Pro</a>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <!--End Common Pro Feature-->
</div>

<?php include(GS_FORMNTR_PATH . "/includes/pages/admin-footer.php"); ?>