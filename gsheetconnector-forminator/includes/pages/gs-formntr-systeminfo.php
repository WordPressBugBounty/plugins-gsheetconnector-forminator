<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit();
}
// 🔒 Prevent Subscribers from seeing sensitive info
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'You do not have permission to access this page.', 'gsheetconnector-for-elementor-forms' ) );
}

$formtr_gs_tools_service = new GS_FORMNTR_Init();
?>
<div class="system-statuswc">
    <div class="info-container">
        <h2 class="systemifo"><?php echo esc_html(__('System Info', 'gsheetconnector-forminator')); ?></h2>
        <button onclick="copySystemInfo()" class="copy"><?php echo esc_html(__('Copy System Info to Clipboard', 'gsheetconnector-forminator')); ?></button>
        <?php echo wp_kses_post($formtr_gs_tools_service->get_formtr_system_info()); ?>
    </div>
</div>

<div class="system-Error">
    <div class="error-container">
        <h2 class="systemerror"><?php echo esc_html(__('Error Log', 'gsheetconnector-forminator')); ?></h2>
        <p><?php echo esc_html(__('If you have', 'gsheetconnector-forminator')); ?> <a href="https://www.gsheetconnector.com/how-to-enable-debugging-in-wordpress" target="_blank"><?php echo esc_html(__('WP_DEBUG_LOG', 'gsheetconnector-forminator')); ?></a> <?php echo esc_html(__('enabled, errors are stored in a log file. Here you can find the last 100 lines in reversed order so that you or the GSheetConnector support team can view it easily. The file cannot be edited here.', 'gsheetconnector-forminator')); ?></p>
        <button onclick="copyErrorLog()" class="copy"><?php echo esc_html(__('Copy Error Log to Clipboard', 'gsheetconnector-forminator')); ?></button>
        <button class="clear-content-logs-frmt"><?php echo esc_html(__('Clear', 'gsheetconnector-forminator')); ?></button>
        <span class="clear-loading-sign-logs-frmt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <div class="clear-content-logs-msg-frmt"></div>
        <input type="hidden" name="frmntr-gs-ajax-nonce" id="frmntr-gs-ajax-nonce"
            value="<?php echo esc_attr(wp_create_nonce('frmntr-gs-ajax-nonce')); ?>" />

        <div class="copy-message" style="display: none;"><?php echo esc_html(__('Copied', 'gsheetconnector-forminator')); ?></div> <!-- Add a hidden div for the copy message -->
        <?php echo wp_kses_post($formtr_gs_tools_service->display_error_log()); ?>
    </div>
</div>