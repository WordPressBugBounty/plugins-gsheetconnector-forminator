<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if (!current_user_can('administrator')) {
?>
    <span class="per_not_allo"><?php echo esc_html__("Permission Not Allowed", 'gsheetconnector-forminator'); ?></span>
<?php
    return;
}
$gsheetconnector_allowed_accress_roles  = array('administrator', 'editor', 'author', 'contributor');
$participating_roles = array();
$editable_roles = get_editable_roles();

foreach ($editable_roles as $role => $details) {

    if (in_array($role, $gsheetconnector_allowed_accress_roles)) {
        $participating_roles[$role] = $details['name'];
    }
}
?>
<form id="gsc_forminator_role_settings_form" method="post" action="options.php">
    <!--Start Pro Setting(Roll Permissions)-->
    <div class="gsc-pro-promo ml-15 mr-pro-15">

        <div class="gsc-pro-header">
            <div class="gsc-pro-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 19c-1 1-2 1-3 1 0-1 0-2 1-3l4-4"></path>
                    <path d="M14 3l7 7"></path>
                    <path d="M9 18l-4 4"></path>
                    <path d="M15 3c2 0 6 4 6 6-2 2-6 6-8 8l-6-6c2-2 6-8 8-8z"></path>
                    <circle cx="15" cy="9" r="1.5"></circle>
                </svg>

            </div>

            <div>
                <div class="unlock-header">
                    <?php echo esc_html(__('Unlock Role-Based Access Control', 'gsheetconnector-forminator')); ?>
                </div>
                <span
                    class="gsc-pro-badge"><?php echo esc_html(__('Advanced options are available in PRO', 'gsheetconnector-forminator')); ?></span>
            </div>
        </div>

        <!-- Feature Tabs -->
        <div class="gsc-pro-tabs pt-20 pb-20 pl-20 pr-20">
            <div>
                <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
                    <?php echo esc_html(__('Role Permissions', 'gsheetconnector-forminator')); ?></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Allow specific WordPress roles', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Enable/disable integration access', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Control form feed visibility', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Restrict settings management', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
                    <?php echo esc_html(__('Security Control', 'gsheetconnector-forminator')); ?></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Prevent unauthorized changes', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Secure Google Sheet credentials', 'gsheetconnector-forminator'); ?>
                        </li>
                        <li><?php esc_html_e('Role-based configuration control', 'gsheetconnector-forminator'); ?>
                        </li>
                        <li><?php esc_html_e('Protect integration settings', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
                    <?php echo esc_html(__('Management Benefits', 'gsheetconnector-forminator')); ?></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Grant access to trusted editors', 'gsheetconnector-forminator'); ?>
                        </li>
                        <li><?php esc_html_e('Hide settings from subscribers', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Team-based permission structure', 'gsheetconnector-forminator'); ?>
                        </li>
                        <li><?php esc_html_e('Improved dashboard security', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
                    <?php echo esc_html(__('Audit & Monitoring', 'gsheetconnector-forminator')); ?></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Track role-based changes', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Monitor integration access', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Review permission updates', 'gsheetconnector-forminator'); ?></li>
                        <li><?php esc_html_e('Maintain admin accountability', 'gsheetconnector-forminator'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="gsc-pro-footer text-center">
            <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" target="_blank"
                class="btn btn-primary text-decoration-none link-hover-white">
                <?php echo esc_html(__('Upgrade to Unlock', 'gsheetconnector-forminator')); ?>
            </a>
        </div>

    </div>
    <!--End Pro Setting(Roll Permissions)-->

    <div class="formntr-role-settings" id="gsc-googlesheet">
        <div class="wrap w-100 m-0">
            <div class="inner-wrap w-100 bg-white p-40 blur-pro-feature">
                <div class="heading mt-0"><?php echo esc_html__('Access Management', 'gsheetconnector-forminator'); ?></div>
                <p><?php echo esc_html__('Control which user roles are allowed to access and manage the Google Sheets integration for your forms.', 'gsheetconnector-forminator'); ?></p>

                <div class="formntr_forminator-card">
                    <div class="gsc-access-wrapper mt-30">
                        <div class="gsc-access-box bg-white pt-15 pb-15 pl-15 pr-15">
                            <div class="para-heading fw-600 mb-20"><?php echo esc_html__('Plugin Access Control', 'gsheetconnector-forminator'); ?></div>
                            <p><?php echo esc_html__('Control who can access and manage the GSheetConnector plugin settings.', 'gsheetconnector-forminator'); ?></p>
                            <div class="gsc-role-card mb-10">
                                <div class="custom-check d-flex justify-between alien-center">
                                    <label class="role-label gsc-switch"><?php echo esc_html__('Administrator', 'gsheetconnector-forminator'); ?></label>
                                    <input type="checkbox" class="check-toggle" disabled="disabled" checked="checked" />
                                    <label class="button-toggle"></label>
                                </div>
                            </div>

                            <?php foreach ($participating_roles as $role => $display_name) : ?>
                                <?php
                                if ($role === 'administrator') {
                                    continue;
                                }
                                $checked = (!empty($roles) && is_array($roles) && in_array(esc_attr($role), $roles)) ? 'checked="checked"' : '';
                                ?>
                                <div class="gsc-role-card mb-10">
                                    <div class="custom-check d-flex justify-between alien-center">
                                        <label class="role-label gsc-switch"><?php echo esc_html($display_name); ?></label>
                                        <input type="checkbox" class="check-toggle"
                                            name="<?php echo esc_attr($role); ?>[]"
                                            value="<?php echo esc_attr($role); ?>"
                                            <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $checked only ever holds the hardcoded literal 'checked="checked"' or '' from line 144, not user input. ?> />
                                        <label class="button-toggle"></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="gsc-access-info">
                            <div class="para-heading fw-600 mb-20"><?php echo esc_html__('Permission Guidelines', 'gsheetconnector-forminator'); ?></div>
                            <ul>
                                <li><?php echo esc_html__('Control which user roles can access the GSheetConnector plugin. Grant access only to users you trust.', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Allow selected users to manage Google Sheets integration settings', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Restrict access to sensitive features like feeds, logs, and configurations', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Only enabled roles will see the plugin menu in the admin panel', 'gsheetconnector-forminator'); ?></li>
                                <li><?php echo esc_html__('Recommended: Allow only Administrators and trusted Editors', 'gsheetconnector-forminator'); ?></li>
                            </ul>
                        </div>
                    </div>


                    <div class="select-info text-right mt-30">
                        <input type="submit" class="btn btn-primary button-large" name="formntr_settings"
                            value="<?php echo esc_html__('Save Settings', 'gsheetconnector-forminator'); ?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>