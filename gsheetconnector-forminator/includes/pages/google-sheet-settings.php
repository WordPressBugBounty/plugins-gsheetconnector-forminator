<?php
/*
 * Google Sheet configuration and settings page
 * @since 1.0.15
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}
$active_tab = (isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])))
    ? sanitize_text_field(wp_unslash($_GET['tab']))
    : 'integration';

$active_tab_name = '';
if ($active_tab == 'integration') {
    $active_tab_name = 'Integration';
} elseif ($active_tab == 'google-sheet') {
    $active_tab_name = 'Form Feed';
} elseif ($active_tab == 'general-settings') {
    $active_tab_name = 'General Settings';
} elseif ($active_tab == 'system-status') {
    $active_tab_name = 'System Status';
} elseif ($active_tab == 'extensions') {
    $active_tab_name = 'Extensions';
}


// Check plugin version and subscription plan
$plugin_version = defined('GS_FORMNTR_VERSION') ? GS_FORMNTR_VERSION : 'N/A';

?>
<div class="gsheet-header">
    <div class="gsheet-logo">
        <a href="https://www.gsheetconnector.com/"><i></i></a>
    </div>
    <h1 class="gsheet-logo-text">
        <span><?php echo esc_html("Forminator GSheetConnector", 'gsheetconnector-forminator'); ?></span>
        <small><?php echo esc_html("Version :", 'gsheetconnector-forminator'); ?>
            <?php echo esc_html($plugin_version, 'gsheetconnector-forminator'); ?> </small>
    </h1>
    <a href="https://support.gsheetconnector.com/kb" title="gsheet Knowledge Base" target="_blank"
        class="button gsheet-help"><i class="dashicons dashicons-editor-help"></i></a>
</div>
<span class="dashboard-gsc"><?php echo esc_html(__('DASHBOARD', 'gsheetconnector-forminator')); ?></span>
<span class="divider-gsc"> / </span>
<span class="modules-gsc"> <?php echo esc_html($active_tab_name); ?></span>
<div class="wrap">
    <?php
    $tabs = array(
        'integration' => __('Integration', 'gsheetconnector-forminator'),
        'google-sheet' => __('Form Feed', 'gsheetconnector-forminator'),
        'general-settings' => __('General Settings', 'gsheetconnector-forminator'),
        'system-status' => __('System Status', 'gsheetconnector-forminator'),
        'extensions' => __('Extensions', 'gsheetconnector-forminator'),
    );

    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach ($tabs as $tab => $name) {
        $class = ($tab == $active_tab) ? ' nav-tab-active' : '';
        printf(
            '<a class="nav-tab%1$s" href="?page=formntr-gsheet-config&tab=%2$s">%3$s</a>',
            esc_attr($class),
            esc_attr($tab),
            esc_attr($name),
        );
    }
    echo '</h2>';
    switch ($active_tab) {
        case 'integration':
            include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-integration.php");
            break;

        case 'google-sheet':
            if (isset($_GET['form_id']) && isset($_GET['feed_id'])) {
                include(GS_FORMNTR_PATH . "includes/pages/edit-feed.php"); //edit
            } else if (isset($_GET['form_id'])) {
                include(GS_FORMNTR_PATH . "includes/pages/edit-sheet.php"); //form edit
    
            } else {
                include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-google-sheet.php"); //form list
    
            }
            break;
        case 'general-settings':
            include GS_FORMNTR_PATH . "includes/pages/gs-formntr-general-settings.php";
            break;
        case 'system-status':
            include(GS_FORMNTR_PATH . "includes/pages/gs-formntr-systeminfo.php");
            break;
        case 'extensions':
            include(GS_FORMNTR_PATH . "includes/pages/extensions/extensions.php");
            break;
    }
    ?>
</div>

<?php include(GS_FORMNTR_PATH . "/includes/pages/admin-footer.php"); ?>