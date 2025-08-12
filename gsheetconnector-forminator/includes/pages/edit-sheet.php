<?php
/*
 * Google Sheet configuration and settings page
 * @since 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}
//add_action('wp_ajax_delete_feed_forminator', 'delete_feed_forminator');

$forms = Forminator_API::get_forms();
$form_id = '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if (isset($_GET['form_id'])) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $form_id = sanitize_text_field(wp_unslash($_GET['form_id']));
}

$form_id = intval($form_id);
$feedList = get_metadata('post', 0, 'forminator-google-sheet', true);
$service_obj = new GS_FORMNTR_Service();
$feeds = $service_obj->get_feed_details();

?>


<div class="frmn-main-div">
    <div class="frmn-bread-crumb">

        <a href="?page=formntr-gsheet-config&tab=google-sheet">
            <button class="back-button">
                <span class="back-icon">&#8592;</span> <?php echo esc_html__(' Back to Forms List', 'gsheetconnector-forminator'); ?>
            </button>
        </a>
        <button class="frmn-btn" id="add-new-feed">
            <?php echo esc_html__('+ Add Feed to Form', 'gsheetconnector-forminator'); ?>
        </button>
    </div>

    <!-- nonce check -->
    <input type="hidden" name="frmntr-gs-ajax-nonce" id="frmntr-gs-ajax-nonce"
        value="<?php echo esc_attr(wp_create_nonce('frmntr-gs-ajax-nonce')); ?>" />
    <div class="frmn-main">

        <div class="frmn-row">
            <table>
                <thead>
                    <?php if (!empty($feeds)) { ?>
                        <tr>
                            <th scope="col"><?php echo esc_html__('Sr No', 'gsheetconnector-forminator'); ?></th>
                            <th scope="col"><?php echo esc_html__('Feed Name', 'gsheetconnector-forminator'); ?></th>
                            <th scope="col"><?php echo esc_html__('Edit Feed', 'gsheetconnector-forminator'); ?></th>
                            <th scope="col"><?php echo esc_html__('Delete Feed', 'gsheetconnector-forminator'); ?></th>
                        </tr>
                    <?php } ?>
                </thead>
                <tbody>
                    <?php
                    if (!empty($feeds)) {
                        foreach ($feeds as  $key => $feed) {
                            $meta_value = unserialize($feed['meta_value']);
                            $feed_name = $meta_value['feed_name'];
                            $form_id = $feed['post_id'];
                            $feed_id = $feed['meta_id'];
                    ?>
                            <tr>
                                <td><?php echo esc_html($key + 1); ?></td>
                                <td data-label="Feed Name">
                                    <?php echo isset($feed_name) ? esc_html($feed_name) : "Unnamed Feed"; ?>
                                </td>
                                <td data-label="Edit Feed">
                                    <a href="?page=formntr-gsheet-config&tab=google-sheet&form_id=<?php echo esc_attr($form_id); ?>&feed_id=<?php echo esc_attr($feed_id); ?>">
                                        <button class="edit-feed">
                                            <span class="button-text"><?php echo esc_html__(' Edit Feed and Integrate with Google Sheets', 'gsheetconnector-forminator'); ?></span>
                                        </button>

                                    </a>
                                </td>
                                <td data-label="Delete Feed">
                                    <button class="delete-feed" data-feed-id="<?php echo esc_attr($feed_id); ?>">
                                        <span class="button-text"><?php echo esc_html__('Delete this feed.', 'gsheetconnector-forminator'); ?></span>
                                    </button>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="3">
                                <h3> <?php echo esc_html__('No Feeds Found!', 'gsheetconnector-forminator'); ?></h3>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- frmn-main !-->

    <div class="add-feed-popup" style="display:none;">
        <div class="add-feed-popup-container">
            <form method="post">
                <div class="add-feed-popup-header">
                    <h3><?php echo esc_html__('Add Feed', 'gsheetconnector-forminator'); ?></h3>
                    <button type="button" class="frmn-close-btn" id="close-add-feed-popup">&times;</button>
                </div>
                <div class="add-feed-popup-body">
                    <label for="feed_name"><?php echo esc_html__('Feed Name', 'gsheetconnector-forminator'); ?></label>
                    <input type="text" id="feed_name" name="feed_name" required>
                </div>
                <!-- nonce check -->
                <input type="hidden" name="frmntr-gs-feed-ajax-nonce" id="frmntr-gs-feed-ajax-nonce"
                    value="<?php echo esc_attr(wp_create_nonce('frmntr-gs-feed-ajax-nonce')); ?>" />
                <div class="add-feed-popup-footer">
                    <input type="submit" name="execute-submit-feed-forminator" class="frmn-gs-sub-btn" value="<?php echo esc_html__("Submit", "gsheetconnector-forminator"); ?>">
                </div>
            </form>
        </div>
    </div>
</div>