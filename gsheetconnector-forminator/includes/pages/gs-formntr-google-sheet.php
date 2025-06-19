<?php
$service_obj = new GS_FORMNTR_Service();
$forms = $service_obj->get_form_list();
$num_forms = count($forms); // Count the number of active forms

// Define $entry_type and $sql_month_start_date
$entry_type = 'forminator_custom_form'; // Assuming the entry type is forminator_custom_form
$sql_month_start_date = date('Y-m-d', strtotime('-30 days')); // Calculate the date 30 days ago

// Count total entries from last 30 days.
$total_entries_from_last_month = count(Forminator_Form_Entry_Model::get_newer_entry_ids($entry_type, $sql_month_start_date));

$most_entry = Forminator_Form_Entry_Model::get_most_entry($entry_type);

// Check if the user is authenticated
$authenticated = get_option('gs_formntr_token');
$per = get_option('gs_formntr_verify');

// Check user is authenticated when save existing API method
$show_setting = 0;

if (!empty($authenticated) && $per == "valid") {
    $show_setting = 1;
} else {
    ?>
    <p class="wc-display-note">
        <?php 
        echo __('<strong>Authentication Required:</strong>
              You must authenticate using your Google Account along with Google Drive and Google Sheets Permissions to enable the settings for configuration.', 'gsheetconnector-forminator');
        ?>
    </p>
    <?php 
}

if ($show_setting == 1) {
    ?>
    <div class="from-feed-header">
        <img class="custom-image-feed" src="<?php echo GS_FORMNTR_URL ?>/assets/img/gsc-forminator.png" alt="Logo" class="logo">
        <div class="forms-counter"><span id="num-forms"><?php echo esc_html($num_forms); ?></span> <?php echo __('Active Forms', 'gsheetconnector-forminator'); ?>
            <div class="search-container">
                <input type="text" id="form-search" placeholder="Search form.." name="search">
                <button type="button" id="search-button"><i class="fa fa-search"></i></button>
            </div>
        </div>
        <div class="submissions">
            <div class="submission-item">
                <?php
                $last_submission_time = forminator_get_latest_entry_time($entry_type); // Fetch the last submission time using the existing function
                ?>
               <?php echo __('Last Submission:', 'gsheetconnector-forminator'); ?>  <?php echo __($last_submission_time, 'gsheetconnector-forminator'); ?>
            </div>
            <div class="submission-item">
              <?php echo __('Submissions in the last 30 days:', 'gsheetconnector-forminator'); ?>   <span class="sui-list-detail"><?php echo esc_html($total_entries_from_last_month); ?></span>
            </div>
        </div>
    </div>

    <div id="form-list">
        <?php if (empty($forms)) : ?>
            <div class="forminator-forms-list-row">
                <div class="add-feed-row">
                    <p><?php echo __('No forms are currently available.', 'gsheetconnector-forminator'); ?> <a href="admin.php?page=forminator-cform" target="_blank"><?php echo __('Create a new form', 'gsheetconnector-forminator'); ?></a> <?php echo __('to get started.', 'gsheetconnector-forminator'); ?></p>
                </div>
            </div>
        <?php else : ?>
            <?php foreach ($forms as $form) : ?>
                <?php
                $form_id = $form->ID;
                $form_name = $form->post_title; // Directly using post_title as the form name
                $form_url = get_edit_post_link($form_id);
                $last_submission_time = forminator_get_latest_entry_time($entry_type); // Fetch the last submission time for each form
                ?>
                <div class="forminator-forms-list-row">
                    <div class="add-feed-row">
                        <div class="id-no">
                            <?php echo __($form_id, 'gsheetconnector-forminator'); ?>
                           
                        </div>
                        <div class="form-name">
                            <?php echo __($form_name, 'gsheetconnector-forminator'); ?>
                            
                        </div>
                        <a href="?page=formntr-gsheet-config&tab=google-sheet&form_id=<?php echo $form_id ?>" class="forminator-connect-form to-googlesheet-btn" data-form-id="<?php echo esc_attr($form_id); ?>" data-sheet-id="your_sheet_id">
                            <span class="forminator-icon"></span><?php echo __('Set Up Forms Integration with Google Sheets', 'gsheetconnector-forminator'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('form-search');
    const formList = document.getElementById('form-list');
    const forms = formList.getElementsByClassName('add-feed-row');

    searchInput.addEventListener('input', function() {
        const filter = searchInput.value.toLowerCase();
        Array.from(forms).forEach(function(form) {
            const formName = form.querySelector('.form-name').textContent.toLowerCase();
            if (formName.includes(filter)) {
                form.style.display = '';
            } else {
                form.style.display = 'none';
            }
        });
    });
});
</script>





