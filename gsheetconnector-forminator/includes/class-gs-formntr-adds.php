<?php

/*
 * Class for displaying Gsheet Connector PRO adds
 * @since 2.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit;
}

/**
 * GS_Connector_Adds Class
 * @since 2.8
 */
class Formntr_gs_Connector_Adds {
   public function __construct() {
    

      // notifiation when auth expired
      add_action( 'admin_init', array( $this, 'formntr_gs_display_auth_expired_adds_block' ) );
      add_action( 'wp_ajax_formntr_gs_set_auth_expired_adds_interval', array( $this, 'formntr_gs_set_auth_expired_adds_interval' ) );
      add_action( 'wp_ajax_formntr_gs_close_auth_expired_adds_interval', array( $this, 'formntr_gs_close_auth_expired_adds_interval' ) );


   }
   
   public function formntr_gs_display_auth_expired_adds_block() {
      $get_display_interval = get_option( 'formntr_gs_auth_expired_display_add_interval' );
      $close_add_interval = get_option( 'formntr_gs_auth_expired_close_add_interval' );
      
      if( $close_add_interval === "off" ) {
         return;
      }
      
      if ( ! empty( $get_display_interval ) ) {
         $adds_interval_date_object = DateTime::createFromFormat( "Y-m-d", $get_display_interval );
         $adds_interval_timestamp = $adds_interval_date_object->getTimestamp();
      }

      $auth_expired = get_option("formntr_gs_auth_expired_free");
      if($auth_expired == "true"){
         if ( empty( $get_display_interval ) || current_time( 'timestamp' ) > $adds_interval_timestamp ) {
          add_action( 'admin_notices', array( $this, 'show_formntr_gs_auth_expired_adds' ) );
        }   
     }
           
   }

  public function formntr_gs_set_auth_expired_adds_interval() {
      // check nonce
      check_ajax_referer( 'formntr_gs_auth_expired_adds_ajax_nonce', 'security' );
      $time_interval = date( 'Y-m-d', strtotime( '+30 day' ) );
      update_option( 'formntr_gs_auth_expired_display_add_interval', $time_interval );
      wp_send_json_success();
   }

   public function formntr_gs_close_auth_expired_adds_interval() {
      // check nonce
      check_ajax_referer( 'formntr_gs_auth_expired_adds_ajax_nonce', 'security' );
      update_option( 'formntr_gs_auth_expired_close_add_interval', 'off' );
      wp_send_json_success();
   }


   public function show_formntr_gs_auth_expired_adds() {
      $ajax_nonce   = wp_create_nonce( "formntr_gs_auth_expired_adds_ajax_nonce" );
      $review_text = '<div class="formntr-gs-auth-expired-adds-notice ">';
      $review_text .= 'Forminator Google Sheet Connector FREE is installed but it is not connected to your Google account, so you are missing out the submission entries.
         <a href="admin.php?page=formntr-gsheet-config&tab=integration" target="_blank">Connect now</a>. It only takes 30 seconds!. 
          ';
      $review_text .= '<ul class="review-rating-list">';
      $review_text .= '<li><a href="javascript:void(0);" class="formntr-gs-set-auth-expired-adds-interval" title="Nope, may be later">Nope, may be later.</a></li>';
      $review_text .= '<li><a href="javascript:void(0);" class="formntr-gs-close-auth-expired-adds-interval" title="Dismiss">Dismiss</a></li>';
      $review_text .= '</ul>';
      $review_text .= '<input type="hidden" name="formntr_gs_auth_expired_adds_ajax_nonce" id="formntr_gs_auth_expired_adds_ajax_nonce" value="' . $ajax_nonce . '" /></div>';

      $rating_block = GS_FORMNTR_Free_Utility::instance()->admin_notice( array(
         'type'    => 'auth-expired-notice',
         'message' => $review_text
      ) );
      echo $rating_block;
   }
   
}
// construct an instance so that the actions get loaded
$Formntr_gs_connector_adds = new Formntr_gs_Connector_Adds();