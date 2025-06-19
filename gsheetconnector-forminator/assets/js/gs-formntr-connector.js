jQuery(document).ready(function () {

  var length = jQuery('.formntr_order_state').length;
  var checkedlen = jQuery('.formntr_order_state:checked').length;
  if(length == checkedlen){
    jQuery("#checkAllformntrSheet").attr("checked", true);
  }


  jQuery(document).ready(function ($) {

        $('.forminator-connect-form to-googlesheet-btn').on('click', function (e) {
            e.preventDefault();
            var form_id = $(this).data('form-id');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'connect_form_to_google_sheet_callback',
                    form_id: form_id
                },
                success: function (response) {
                    $('#forminator-sheet-id').val(response.sheet_id);
                    $('#forminator-sheet-tab').val(response.sheet_tab);
                    $('#forminator-sheet-tab-id').val(response.sheet_tab_id);
                    $('#forminator-form-id').val(form_id);
                    $('#forminator-sheet-settings-dialog').show();
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        });

        $('#forminator-sheet-settings-cancel-btn').on('click', function (e) {
            e.preventDefault();
            $('#forminator-sheet-settings-dialog').hide();
        });

        $('#forminator-sheet-settings-form').on('submit', function (e) {
            e.preventDefault();
            var form_data = $(this).serialize();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'forminator_save_sheet_settings',
                    form_data: form_data
                },
                success: function (response) {
                    $('#forminator-sheet-settings-dialog').hide();
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        });
    });


   /**
   * verify the api code
   * @since 1.0
   */
   jQuery(document).on('click', '#save-gs-formntr-code', function (event) {
      console.log("====== FOrminator =====");
      event.preventDefault();
         jQuery( ".loading-sign" ).addClass( "loading" );
         var data = {
         action: 'verify_gs_formntr_integation',
         gs_formntr_code: jQuery('#gs-formntr-code').val(),
         security: jQuery('#frmntr-gs-ajax-nonce').val()
         };
         jQuery.post(ajaxurl, data, function (response ) {
            if( ! response.success ) { 
               jQuery( ".loading-sign" ).removeClass( "loading" );
               jQuery( "#gs-formntr-validation-message" ).empty();
               jQuery("<span class='error-message'>Access code Can't be blank.</span>").appendTo('#gs-formntr-validation-message');
            } else {
               jQuery( ".loading-sign" ).removeClass( "loading" );
               jQuery( "#gs-formntr-validation-message" ).empty();
               jQuery("<span class='formntr-valid-message'>Your Google Access Code is Authorized and Saved.</span> ").appendTo('#gs-formntr-validation-message');
               setTimeout(function () { 
                    window.location.href = jQuery("#redirect_auth").val();
                 }, 1000);
           }
         });
         
   });  

   /**
    * deactivate the api code
    * @since 1.0
    */
   jQuery(document).on('click', '#gs-formntr-deactivate-log', function () {
      jQuery(".loading-sign-deactive").addClass( "loading" );
    var txt;
    var r = confirm("Are You sure you want to deactivate Google Integration ?");
    if (r == true) {
       var data = {
          action: 'deactivate_gs_formntr_integation',
          security: jQuery('#frmntr-gs-ajax-nonce').val()
       };
       jQuery.post(ajaxurl, data, function (response ) {
          if ( response == -1 ) {
             return false; // Invalid nonce
          }
        
          if( ! response.success ) {
             alert('Error while deactivation');
             jQuery( ".loading-sign-deactive" ).removeClass( "loading" );
             jQuery( "#deactivate-msg" ).empty();
             
          } else {
             jQuery( ".loading-sign-deactive" ).removeClass( "loading" );
             jQuery( "#deactivate-msg" ).empty();
             jQuery("<span class='formntr-valid-message'>Your account is removed. Reauthenticate again to integrate formntr with Google Sheet.</span>").appendTo('#deactivate-msg');
             setTimeout(function () { location.reload(); }, 1000);
          }
       });
    } else {
       jQuery( ".loading-sign-deactive" ).removeClass( "loading" );
    }
         
  }); 


    function openFeedSettings(formId) {
      alert("Hello, world!");

      // Get the form data using AJAX
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'get_form_data',
          form_id: formId
        },
        success: function(response) {
          // Parse the response JSON and populate the feed settings form
          var formData = JSON.parse(response);
          // Code to populate the feed settings form fields with formData goes here

          // Display the feed settings modal
          var modal = document.getElementById("forminator-feed-settings-modal");
          modal.style.display = "block";
      
      // Add event listener to close the modal when the user clicks the close button
          var closeBtn = document.getElementsByClassName("forminator-modal-close")[0];
          closeBtn.addEventListener("click", function() {
            modal.style.display = "none";
          });
      
          // Add event listener to close the modal when the user clicks outside the modal
          window.addEventListener("click", function(event) {
            if (event.target == modal) {
              modal.style.display = "none";
            }
          });
        },
        error: function(error) {
          console.log(error);
        }
      });
    }


  function html_decode(input) {
      var doc = new DOMParser().parseFromString(input, "text/html");
      return doc.documentElement.textContent;
   }

   jQuery(document).on('click', '#gs-formntr-sync', function () {
      jQuery(this).parent().children(".loading-sign").addClass("loading");
      var integration = jQuery(this).data("init");
      var data = {
         action: 'sync_formntr_google_account',
         isajax: 'yes',
         isinit: integration,
         security: jQuery('#frmntr-gs-ajax-nonce').val()
      };

      jQuery.post(ajaxurl, data, function (response) {
         if (response == -1) {
            return false; // Invalid nonce
         }

         if (response.data.success == "yes") {
            jQuery(".loading-sign").removeClass("loading");
            jQuery("#gs-formntr-validation-message").empty();
            jQuery("<span class='formntr-valid-message'>Fetched latest sheet names.</span>").appendTo('#gs-formntr-validation-message');
            setTimeout(function () { location.reload(); }, 1000);
         } else {
            jQuery(this).parent().children(".loading-sign").removeClass( "loading" );
          location.reload(); // simply reload the page
         }
      });
   });

   /**
    * Clear debug
    */
   jQuery(document).on('click', '.debug-clear', function () {

      jQuery(".clear-loading-sign").addClass("loading");
      var data = {
         action: 'gs_formntr_clear_logs',
         security: jQuery('#frmntr-gs-ajax-nonce').val()
      };
     jQuery.post(ajaxurl, data, function (response ) {
              if (response == -1) {
                return false; // Invalid nonce
             }
            var clear_msg = response.data;
            if( response.success ) { 
               jQuery( ".clear-loading-sign" ).removeClass( "loading" );
               jQuery( "#gs-formntr-validation-message" ).empty();
               jQuery("<span class='gs-valid-message'>"+clear_msg+"</span>").appendTo('#gs-formntr-validation-message'); 
               setTimeout(function () {
                     location.reload();
                 }, 1000);
            }
        });
    });


    /**
     * Display Error logs
     */
   jQuery(document).ready(function($) {
    // Hide .frmgsc-system-Error-logs initially
    $('.frmgsc-system-Error-logs').hide();

    // Add a variable to track the state
    var isOpen = false;

    // Function to toggle visibility and button text
    function toggleLogs() {
        $('.frmgsc-system-Error-logs').toggle();
        // Change button text based on visibility
        $('.frmgsc-logs').text(isOpen ? 'View' : 'Close');
        isOpen = !isOpen; // Toggle the state
    }

    // Toggle visibility and button text when clicking .frmgsc-logs button
    $('.frmgsc-logs').on('click', function() {
        toggleLogs();
    });

    // Prevent the div from closing when clicking inside it
    $('.frmgsc-system-Error-logs').on('click', function(e) {
        e.stopPropagation(); // Stop the event from bubbling up
    });

    // Optional: You can add a specific button inside the div to close it explicitly
    $('.frmgsc-close-btn').on('click', function() {
        if (isOpen) {
            toggleLogs(); // Close the div when the close button is clicked
        }
    });
});


    
    /**
    * Clear debug for system status tab
    */
   jQuery(document).on('click', '.clear-content-logs-frmt', function () {

      jQuery(".clear-loading-sign-logs-frmt").addClass("loading");
      var data = {
         action: 'frm_clear_debug_logs',
         security: jQuery('#frmntr-gs-ajax-nonce').val()
      };
      jQuery.post(ajaxurl, data, function ( response ) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         
         if (response.success) {
            jQuery(".clear-loading-sign-logs-frmt").removeClass("loading");
            jQuery('.clear-content-logs-msg-frmt').html('Logs are cleared.');
            setTimeout(function () {
                        location.reload();
                    }, 1000);
         }
      });
   });

   /*Check All Option*/
   jQuery(document).on('click', '#checkAllformntrSheet',function () {
     jQuery('.formntr_order_state').not(this).prop('checked', this.checked);
  });

   

   /**
   * verify the api code
   * @since 1.0
   */
   // jQuery(document).on('click', '#gs-formntr-deactivate-auth', function (event) {
   //    event.preventDefault();
   //       jQuery( ".loading-sign" ).addClass( "loading" );
   //       var data = {
   //       action: 'deactivate_auth_token_gapi',
   //       security: jQuery('#frmntr-gs-ajax-nonce').val()
   //       };
   //       jQuery.post(ajaxurl, data, function (response ) {
   //          if( ! response.success ) { 
   //             jQuery( ".loading-sign" ).removeClass( "loading" );
   //             jQuery( "#gs-formntr-validation-message" ).empty();
   //             jQuery("<span class='error-message'>Access code Can't be blank.</span>").appendTo('#gs-formntr-validation-message');
   //          } else {
   //             jQuery( ".loading-sign" ).removeClass( "loading" );
   //             jQuery( "#gs-formntr-validation-message" ).empty();
   //             jQuery("<span class='formntr-valid-message'>Your Google Access Code is Authorized and Saved.</span> ").appendTo('#gs-formntr-validation-message');
   //          setTimeout(function () { location.reload(); }, 1000);
   //         }
   //       });
         
   // });

   


   /**
   * hide authentication button and display token input 
    @since 1.0
   */  

	jQuery(document).on('click', '#authlink_gsformntr', function (event) {
		jQuery(".wg_api_option_auth_url").hide();
		jQuery("#gs-formntr-client-token").show();

	});

   /**
   * reset form
    @since 1.0
   */  
	jQuery(document).on('click', '#save-formntr-reset', function (event) {
		jQuery("#gs-formntr-client-id").val('');
		jQuery("#gs-formntr-secret-id").val('');
		jQuery("#gs-formntr-client-token").val('');
        jQuery("#gs-formntr-client-id").removeAttr('disabled');
        jQuery("#gs-formntr-secret-id").removeAttr('disabled');
        jQuery("#save-gs-frmin-manual").removeAttr('disabled');
	});


   /*
   * Create New Sheet
   */
	jQuery(document).on('change', '#gs-formntr-sheet-id', function(){
		if(this.value=='create_new'){
			//jQuery('.sheet-tab-name').hide();
			jQuery('.sheet-url').hide();
			jQuery('.create-ss-wrapper').show();
		}
	})
   
	function gsformntr_processAjax ( $type, $page, $total_entries, $processed ) {

		var data = {
			'action' : 'gsformntr_sync_orders',
			'type' : $type,
			'processed' : $processed,
			'page' : $page,
			'total_entries' : $total_entries,
		};


		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			success: function( response ) {         

				if( response == -1 ) {
					return;
				}

				response = JSON.parse(response);
				if( typeof response.process_further != 'undefined' ) {

					var $process_further = response.process_further;

					if( $process_further == 1 ) {             
						$page = response.next_page;
						$total_entries = response.total_entries;
						$processed = response.processed;
						
						jQuery(".sync-message").text(response.text);
						jQuery(".sync-loader").show();
						
						setTimeout(function() {
							gsformntr_processAjax( $type, $page, $total_entries, $processed );
						}, 2000);

					}
					else {
						jQuery(".sync-message").text(response.text);
						jQuery(".sync-loader").hide();
					}
				}
			}
		});
	}

	jQuery(document).on('click', '.formntr-sync-orders', function (event) {

		var $this = jQuery(this);
		var $type = $this.attr( "data-type" );
		gsformntr_processAjax( $type, 1, 0, 0 );
	});
  
	jQuery(document).on('click', '.gscformntr_checkAll', function (event) {

		jQuery('.li-formntr-header input:checkbox').not(this).prop('checked', this.checked);
	});
	
	jQuery(document).on('click', '.edit_col_name', function (event) {

		var $parent = jQuery(this).parent();
		
		$parent.find(".label_text").fadeOut( "fast", function() {
			$parent.find('input').fadeIn();
		} );
		
		$parent.find(".edit_col_name").fadeOut( "fast", function() {
			$parent.find('.update_col_name').fadeIn();
		} );
		
		/*if( $parent.find(".label_text").is(":visible") ) {
			$parent.find(".label_text").fadeOut( "fast", function() {
				$parent.find('input').fadeIn();
			} );
		}
		else {
			$parent.find("input").fadeOut( "fast", function() {
				$parent.find('.label_text').fadeIn();
			} );
		}*/
		
	});
	
	jQuery(document).on('click', '.update_col_name', function (event) {

		var $parent = jQuery(this).parent();
		var $input_value = $parent.find('input').val();
		$parent.find(".label_text").text( $input_value );
		
		$parent.find("input").fadeOut( "fast", function() {
			$parent.find('.label_text').fadeIn();
		} );
		
		$parent.find(".update_col_name").fadeOut( "fast", function() {
			$parent.find('.edit_col_name').fadeIn();
		} );
		
	});
	
	
	jQuery(document).on('change', '.checkallradio', function (event) {
		
		var $val = jQuery(this).val();
		
		if( $val == "yes" ) {
			jQuery(".li-formntr-header input[value=0]").prop('checked', false);
			jQuery(".li-formntr-header input[value=1]").prop('checked', true);
		}
		else {
			jQuery(".li-formntr-header input[value=0]").prop('checked', true);
			jQuery(".li-formntr-header input[value=1]").prop('checked', false);
		}
		// jQuery('.li-formntr-header input:checkbox').not(this).prop('checked', this.checked);
	});

   jQuery(document).ready(function($) {
        $('#forminator-form-select').change(function() {
            var form_id = $(this).val();
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: { action: 'get_google_sheet_panel', form_id: form_id },
                success: function(response) {
                    $('#forminator-google-sheet-panel').html(response);
                    $('#forminator-google-sheet-panel').show(); // show the panel
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log('Error: ' + thrownError);
                }
            });
        });
    });

   jQuery(document).ready(function($) {
        $('.delete-feed').click(function() {
            var feedId = $(this).data('feed-id');
            var confirmDelete = confirm('Are you sure you want to delete this feed?');
            if (confirmDelete) {
                // Submit a POST request to delete the feed
                $.post(ajaxurl, {
                    action: 'delete_feed_forminator',
                    feed_id: feedId,
                    security: jQuery('#frmntr-gs-ajax-nonce').val()
                }, function(response) {
                    if (response === 'success') {
                        // Reload the page after successful deletion
                        location.reload();
                    } else {
                        // Handle error here
                        console.log('Error deleting feed');
                    }
                });
            }
        });
    });






  /* Aveshahmed - 05102021 */
  
  jQuery(document).on("click", ".gs-formntr-list-set", function (event){
      var $this = jQuery(this);
      var $id = $this.attr( "data-id" );

      if(jQuery(".gs-formntr-list-set"+$id).css("display") == "none") { 
        //jQuery(".gs-formntr-list-set"+$id).css("display", "block");
        jQuery(".gs-formntr-list-set"+$id).show('slow');
        jQuery(".mini_mize"+$id).show();
        jQuery(".maxi_mize"+$id).hide();
      }else{
        //jQuery(".gs-formntr-list-set"+$id).css("display", "none");
        jQuery(".gs-formntr-list-set"+$id).hide('slow');
        jQuery(".mini_mize"+$id).hide();
        jQuery(".maxi_mize"+$id).show();
      }  
  
  });



   /* drop down event for Google API */
   if(jQuery("#formntr_manual_setting").val() == '1')
   {
      jQuery("#gs_frmin_dro_option").val('frmin_manual');
      jQuery(".api_manual_setting_frmin").show();
      jQuery(".api_existing_setting").hide();
   }
   jQuery(document).on('change', '#gs_frmin_dro_option', function () {
          //alert(jQuery('option:selected', jQuery(this)).val());
          var option = jQuery('option:selected', jQuery(this)).val();
          if(option == "frmin_manual")
          {
            jQuery(".api_manual_setting_frmin").show();
            jQuery(".api_existing_setting").hide();
          }else{
            jQuery(".api_manual_setting_frmin").hide();
            jQuery(".api_existing_setting").show();
          }  
   });
   

   
// Show/hide Google Sheets settings box when Edit link is clicked
jQuery('.forminator-forms-list__edit-link').click(function(e) {
    e.preventDefault();

    // Get the form ID
    var formId = jQuery(this).data('form-id');

    // Show/hide the Google Sheets settings box
    jQuery('.forminator-form-settings').hide();
    jQuery('.forminator-form-settings--' + formId).show();
});


jQuery(document).on('submit', '#gsformntrSettingForm', function (event) {
   console.log('prevent the subitting the form');
   jQuery('#error_gsformntrTabName').html('');
   var submit = true;
   var gsTabName = jQuery('input.formntr_order_state:checked').length;
   var spreadsheetsName = jQuery('#gs-formntr-sheet-id').val();
   if(spreadsheetsName == ""){
      jQuery('#error_formntr_spread').html('* Please Select Spreadsheet Name !');
      submit = false;
   }
   if(gsTabName <= 0){
      jQuery('#error_gsformntrTabName').html('* Please select atleast one Tabs !');
      submit = false;
   }
   if(submit == false){
      event.preventDefault();
      window.scrollTo({ top: 10, behavior: 'smooth' });
   }
});


jQuery("#close-feed").hide();
    jQuery(".add-feed-popup").hide();
    jQuery(".connect-form-to-gsheet").hide();
    jQuery(".edit-feed-popup").hide();

    jQuery("#add-new-feed").on('click', function (e) {
        jQuery(".add-feed-popup").show();
        jQuery("#close-feed").show();
    });

    jQuery("#close-add-feed-popup, #close-feed").on('click',function() {
        jQuery(".add-feed-popup").hide();
        jQuery("#close-feed").hide();
    });

    // Submit feed form
    jQuery("#feed-form").submit(function(e) {
        e.preventDefault();
        var feedName = jQuery("#feed_name").val();
        if (feedName !== '') {
            jQuery(".add-feed-popup").hide();
            jQuery("#close-feed").hide();
            jQuery(".connect-form-to-gsheet").show();
        }
    });

    jQuery("#edit-feed-form").submit(function(e) {
      
      var sheet_name = jQuery("#edit-sheet-name").val();
      var sheet_id = jQuery("#edit-sheet-id").val();
      var tab_name = jQuery("#edit-tab-name").val();
      var tab_id = jQuery("#edit-tab-id").val();


      if ((sheet_name == '') || (sheet_id == '') || (tab_name == '') || (tab_id == '')) {
          alert("Please insert !");
          e.preventDefault();
      }
  });


   });



// Msg Hide ///
	
jQuery(document).ready(function($) {
    // Check if the message has already been hidden by looking in localStorage
    if (localStorage.getItem('googleDriveMsgHidden') === 'true') {
        jQuery('#google-drive-msg').hide(); // Hide the message if it's already hidden
    }

    // On button click, hide the #google-drive-msg div and store the hidden state in localStorage
    jQuery('.button_formgsc').on('click', function() {
        jQuery('#google-drive-msg').hide(); // Hide the message
        localStorage.setItem('googleDriveMsgHidden', 'true'); // Save the hidden state in localStorage
    });

    // On #deactivate-log click, show the #google-drive-msg div and clear localStorage
    jQuery('#gs-formntr-deactivate-log').on('click', function() {
        jQuery('#google-drive-msg').show(); // Show the message
        localStorage.removeItem('googleDriveMsgHidden'); // Remove the hidden state from localStorage
    });
});