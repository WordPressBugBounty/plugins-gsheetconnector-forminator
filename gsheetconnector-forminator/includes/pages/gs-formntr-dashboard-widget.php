<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="dashboard-content">
    <?php
    $gs_forminator_connector_service = new GS_FORMNTR_Service();
    $forms_list = $gs_forminator_connector_service->get_forms_connected_to_sheet();

    if (!empty($forms_list)) {
    ?>
        <h3><?php esc_html_e("Forminator Forms connected with Google Sheets.", 'gsheetconnector-forminator'); ?></h3>
        <div class="main-content">
            <div class="gs_formntr_dash_widget">
                <div class="gs_formntr_conn_sheet">
                    <p style="font-weight: 500!important; color: #9C5C90!important; text-decoration: underline!important;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=formntr-gsheet-config&tab=google-sheet')); ?>">
                            <?php esc_html_e("Connected Sheets Name", 'gsheetconnector-forminator'); ?>
                        </a>
                    </p>
                    <ul>
                        <?php
                        foreach ($forms_list as $fl) {
                            $feed_id = $fl->meta_id;
                            $feed_data = get_post_meta($feed_id, 'forminator_forms_feed_details', true);

                            if (!empty($feed_data) && isset($feed_data['sheet_id'], $feed_data['sheet_name'], $feed_data['tab_id'])) {
                                $selected_sheet_id = esc_attr($feed_data['sheet_id']);
                                $selected_sheet_name = esc_html($feed_data['sheet_name']);
                                $selected_tab_id = esc_attr($feed_data['tab_id']);
                        ?>
                                <li>
                                    <a href="<?php echo esc_url('https://docs.google.com/spreadsheets/d/' . $selected_sheet_id . '/edit#gid=' . $selected_tab_id); ?>" target="_blank">
                                        <?php echo esc_html($selected_sheet_name); ?>
                                    </a>
                                </li>
                        <?php
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <p><?php esc_html_e("No Forminator Forms are connected with Google Sheets.", 'gsheetconnector-forminator'); ?></p>
    <?php } ?>
</div>