<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}
?>


<!-- uninstall plugin settings -->
<div class="system-general_setting" style="position: relative;">
   
    <div class="info-container">

        <h2 class="systemifo">
            <?php echo esc_html("General Settings", "gsheetconnector-forminator"); ?>
        </h2>
        <h3 class="systemifo">
            <?php echo esc_html("Uninstall Plugin Settings", "gsheetconnector-forminator"); ?>
        </h3>
         <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" class="pro-link"
        target="_blank" style="text-decoration: none;"></a>
        <form method="post">
            <!-- Hidden field ensures a value is always submitted -->
            <input type="hidden" name="gs_formntr_uninstall_settings" value="No">

            <!-- Checkbox (conditionally checked based on saved value) -->
            <input type="checkbox" id="gs_formntr_uninstall_settings" name="gs_formntr_uninstall_settings" value="Yes">
            <label for="gs_formntr_uninstall_settings">
                <?php echo esc_html("Enable to Delete all the settings while deleting the plugin (meta data, options, etc. created by this plugin.)", "gsheetconnector-forminator"); ?>
            </label>

            <br /><br />
            <input type="submit" class="button btn-primary uninstall-settings-save"
                name="gs_formntr_save_uninstall_settings"
                value="<?php echo esc_html("Save", "gsheetconnector-forminator"); ?>" />

            <input type="hidden" name="gs-formntr-setting-ajax-nonce" id="gs-formntr-setting-ajax-nonce"
                value="<?php echo esc_attr(wp_create_nonce('gs-formntr-setting-ajax-nonce')); ?>" />
        </form>
    </div>
</div>