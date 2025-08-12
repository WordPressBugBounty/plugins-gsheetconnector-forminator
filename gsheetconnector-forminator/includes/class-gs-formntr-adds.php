<?php

/*
 * Class for displaying Gsheet Connector PRO adds
 * @since 1.0.15
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
   exit;
}

/**
 * GS_Connector_Adds Class
 * @since 1.0.15
 */

class Formntr_gs_Connector_Adds
{

   /**
    *  Set things up.
    *  @since 1.0.15
    */

   public function __construct()
   {
      // notification when auth expired
      add_action('admin_init', array($this, 'formntr_gs_display_auth_expired_adds_block'));

      // add a link to the settings page
      add_action('wp_ajax_formntr_gs_set_auth_expired_adds_interval', array($this, 'formntr_gs_set_auth_expired_adds_interval'));

      // AJAX action to close the authentication expired notice and update the interval.  
      add_action('wp_ajax_formntr_gs_close_auth_expired_adds_interval', array($this, 'formntr_gs_close_auth_expired_adds_interval'));
   }

   /**
    * Display authentication expired notice block in the admin area.
    *
    * - Skips display if ads are disabled via the "close_add_interval" option.
    * - Checks if the saved display interval date has passed.
    * - If authentication is expired and conditions are met, hooks the notice display.
    *
    * @since 1.0.15
    */

   public function formntr_gs_display_auth_expired_adds_block()
   {
      $get_display_interval = get_option('formntr_gs_auth_expired_display_add_interval');
      $close_add_interval = get_option('formntr_gs_auth_expired_close_add_interval');

      if ($close_add_interval === "off") {
         return;
      }

      if (! empty($get_display_interval)) {
         $adds_interval_date_object = DateTime::createFromFormat("Y-m-d", $get_display_interval);
         $adds_interval_timestamp = $adds_interval_date_object->getTimestamp();
      }

      $auth_expired = get_option("formntr_gs_auth_expired_free");
      if ($auth_expired == "true") {
         if (empty($get_display_interval) || current_time('timestamp') > $adds_interval_timestamp) {
            add_action('admin_notices', array($this, 'show_formntr_gs_auth_expired_adds'));
         }
      }
   }

   /**
    * Set the display interval for the authentication expired notice.
    *
    * - Verifies AJAX nonce for security.
    * - Sets the display interval to 30 days from the current date.
    * - Updates the option storing the next display date.
    * - Returns a JSON success response.
    *
    * @since 1.0.15
    */

   public function formntr_gs_set_auth_expired_adds_interval()
   {
      // check nonce
      check_ajax_referer('formntr_gs_auth_expired_adds_ajax_nonce', 'security');
      $time_interval = gmdate('Y-m-d', strtotime('+30 day'));
      update_option('formntr_gs_auth_expired_display_add_interval', $time_interval);
      wp_send_json_success();
   }

   /**
    * Disable the authentication expired notice permanently.
    *
    * - Verifies AJAX nonce for security.
    * - Updates the option to turn off the notice display.
    * - Returns a JSON success response.
    *
    * @since 1.0.15
    */

   public function formntr_gs_close_auth_expired_adds_interval()
   {
      // check nonce
      check_ajax_referer('formntr_gs_auth_expired_adds_ajax_nonce', 'security');
      update_option('formntr_gs_auth_expired_close_add_interval', 'off');
      wp_send_json_success();
   }

   /**
    * Display authentication expired admin notice.
    *
    * - Creates a nonce for AJAX security.
    * - Outputs a styled message prompting the user to connect Google account.
    * - Includes options to defer or dismiss the notice via AJAX actions.
    * - Uses utility method to format the notice.
    *
    * @since 1.0.15
    */

   public function show_formntr_gs_auth_expired_adds()
   {
      $ajax_nonce   = wp_create_nonce("formntr_gs_auth_expired_adds_ajax_nonce");
      $review_text = '<div class="formntr-gs-auth-expired-adds-notice ">';
      $review_text .= 'Forminator Google Sheet Connector FREE is installed but it is not connected to your Google account, so you are missing out the submission entries.
         <a href="admin.php?page=formntr-gsheet-config&tab=integration" target="_blank">Connect now</a>. It only takes 30 seconds!. 
          ';
      $review_text .= '<ul class="review-rating-list">';
      $review_text .= '<li><a href="javascript:void(0);" class="formntr-gs-set-auth-expired-adds-interval" title="Nope, may be later">Nope, may be later.</a></li>';
      $review_text .= '<li><a href="javascript:void(0);" class="formntr-gs-close-auth-expired-adds-interval" title="Dismiss">Dismiss</a></li>';
      $review_text .= '</ul>';
      $review_text .= '<input type="hidden" name="formntr_gs_auth_expired_adds_ajax_nonce" id="formntr_gs_auth_expired_adds_ajax_nonce" value="' . $ajax_nonce . '" /></div>';

      $rating_block = GS_FORMNTR_Free_Utility::instance()->admin_notice(array(
         'type'    => 'auth-expired-notice',
         'message' => $review_text
      ));
      echo wp_kses_post($rating_block);
   }
}
// construct an instance so that the actions get loaded
$Formntr_gs_connector_adds = new Formntr_gs_Connector_Adds();
