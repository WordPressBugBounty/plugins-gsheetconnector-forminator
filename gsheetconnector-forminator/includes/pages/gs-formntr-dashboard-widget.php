<?php
if (!defined('ABSPATH')) {
   exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="dashboard-content">
   <?php
   $gs_forminator_connector_service = new GS_FORMNTR_Service();
   $forms_list = $gs_forminator_connector_service->get_forms_connected_to_sheet();
   ?>
   <div class="main-content">
      <div>
         <p><?php echo esc_html__("Forminator Forms Connected with Google Sheets.", 'gsheetconnector-forminator'); ?></p>
         <?php if (! empty($forms_list)) { ?>
            <table class="widget-table">
               <thead>
                  <tr>
                     <th><?php esc_html_e('Form Name', 'gsheetconnector-forminator'); ?></th>
                     <th><?php esc_html_e('Feed Name', 'gsheetconnector-forminator'); ?></th>
                     <th><?php esc_html_e('Sheet Name', 'gsheetconnector-forminator'); ?></th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                  foreach ($forms_list as $key => $value) {
                     $feed_data = is_array($value->data) ? $value->data : array();

                     $form_id = $value->form_id;
                     $form_name  = $value->form_title;
                     $feed_id = (!empty($value->feed_id) ? $value->feed_id : "");

                     $feed_name = $value->feed_name;

                     $sheet_id = isset($feed_data['sheet_id']) ? esc_attr($feed_data['sheet_id']) : '';
                     $tab_id = isset($feed_data['tab_id']) ? esc_attr($feed_data['tab_id']) : '';
                     $sheet_name = isset($feed_data['sheet_name']) ? esc_attr($feed_data['sheet_name']) : '';
                     $tab_name = isset($feed_data['tab_name']) ? esc_attr($feed_data['tab_name']) : '';


                     if ($sheet_name != '' && $tab_name != '') {
                        $sheet_tab_name = $sheet_name . ' — ' . $tab_name;
                     } else {
                        $sheet_tab_name = esc_html__('Sheet URL', 'gsheetconnector-forminator');
                     }
                  ?>
                     <tr>
                        <td><?php echo esc_html($form_name); ?></td>
                        <td><a href="<?php echo esc_url(admin_url('admin.php?page=formntr-gsheet-config&tab=google-sheet&form_id=' . esc_html($form_id) . '&feed_id=' . esc_html($feed_id))); ?>" target="_blank">
                              <?php echo esc_attr($feed_name); ?>
                           </a></td>
                        <td>
                           <?php if ($sheet_id != '' && $tab_id != '') {  ?>
                              <a href="https://docs.google.com/spreadsheets/d/<?php echo esc_attr($sheet_id); ?>/edit#gid=<?php echo esc_attr($tab_id); ?>" target="_blank"><?php echo esc_html($sheet_tab_name); ?> </a>
                        </td>
                     <?php } else {
                              // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                              echo  $sheet_id = esc_html__('Not connected', 'gsheetconnector-forminator');
                           } ?>
                     </tr>
                  <?php } ?>
               </tbody>
            </table>
         <?php } else { ?>
            <p>
               <?php echo esc_html__("No Forminator Forms are connected with Google Sheets", 'gsheetconnector-forminator'); ?>
            </p>
         <?php } ?>
      </div>
   </div> <!-- main-content end -->
</div> <!-- dashboard-content end -->