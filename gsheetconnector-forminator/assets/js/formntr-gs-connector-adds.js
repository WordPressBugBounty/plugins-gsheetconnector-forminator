jQuery(document).ready(function () {

   /**
    * Sends AJAX request to set auth expired interval and hides notice on success.
    *
    * @since 1.0.15
    */

   jQuery('.formntr-gs-set-auth-expired-adds-interval').click(function () {
      var data = {
         action: 'formntr_gs_set_auth_expired_adds_interval',
         security: jQuery('#formntr_gs_auth_expired_adds_ajax_nonce').val()
      };

      jQuery.post(ajaxurl, data, function (response) {
         if (response.success) {
            jQuery('.formntr-gs-auth-expired-adds').slideUp('slow');
         }
      });
   });

   /**
    * Sends AJAX request to close auth expired notice and hides it on success.
    *
    * @since 1.0.15
    */

   jQuery('.formntr-gs-close-auth-expired-adds-interval').click(function () {
      var data = {
         action: 'formntr_gs_close_auth_expired_adds_interval',
         security: jQuery('#formntr_gs_auth_expired_adds_ajax_nonce').val()
      };

      jQuery.post(ajaxurl, data, function (response) {
         if (response.success) {
            jQuery('.formntr-gs-auth-expired-adds').slideUp('slow');
         }
      });
   });

});