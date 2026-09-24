jQuery(document).ready(function () {
  /**
   * Checks 'select all' checkbox if all individual checkboxes are selected.
   *
   * @since 1.0.15
   */

  var length = jQuery(".formntr_order_state").length;
  var checkedlen = jQuery(".formntr_order_state:checked").length;
  if (length == checkedlen) {
    jQuery("#checkAllformntrSheet").attr("checked", true);
  }

  /**
   * Handles connecting a form to Google Sheets, showing settings dialog,
   * and saving sheet settings via AJAX.
   *
   * @since 1.0.15
   */

  jQuery(document).ready(function ($) {
    $(".forminator-connect-form to-googlesheet-btn").on("click", function (e) {
      e.preventDefault();
      var form_id = $(this).data("form-id");
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "connect_form_to_google_sheet_callback",
          form_id: form_id,
        },
        success: function (response) {
          $("#forminator-sheet-id").val(response.sheet_id);
          $("#forminator-sheet-tab").val(response.sheet_tab);
          $("#forminator-sheet-tab-id").val(response.sheet_tab_id);
          $("#forminator-form-id").val(form_id);
          $("#forminator-sheet-settings-dialog").show();
        },
        error: function (xhr, status, error) {
          console.log(xhr.responseText);
        },
      });
    });

    $("#forminator-sheet-settings-cancel-btn").on("click", function (e) {
      e.preventDefault();
      $("#forminator-sheet-settings-dialog").hide();
    });

    $("#forminator-sheet-settings-form").on("submit", function (e) {
      e.preventDefault();
      var form_data = $(this).serialize();
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "forminator_save_sheet_settings",
          form_data: form_data,
        },
        success: function (response) {
          $("#forminator-sheet-settings-dialog").hide();
        },
        error: function (xhr, status, error) {
          console.log(xhr.responseText);
        },
      });
    });
  });

  /**
   * Verifies the API code.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", "#save-gs-formntr-code", function (event) {
    event.preventDefault();
    jQuery(".loading-sign").addClass("loading");
    jQuery("#save-gs-formntr-code")
      .prop("disabled", true)
      .addClass("common-disable");

    var data = {
      action: "verify_gs_formntr_integation",
      gs_formntr_code: jQuery("#gs-formntr-code").val(),
      security: jQuery("#frmntr-gs-ajax-nonce").val(),
    };
    jQuery.post(ajaxurl, data, function (response) {
      if (!response.success) {
        jQuery(".loading-sign").removeClass("loading");
        jQuery("#gs-formntr-validation-message").empty();
        jQuery(
          "<div class='formntr-valid-message gsc-msg gsc-error fw-400 text-dark text-center mt-10 pt-10 pb-10 manual-margin'>Access code Can't be blank.</div>",
        ).appendTo("#gs-formntr-validation-message");
      } else {
        jQuery(".loading-sign").removeClass("loading");
        jQuery("#gs-formntr-validation-message").empty();
        jQuery(
          "<div class='formntr-valid-message gsc-msg gsc-success fw-400 text-dark text-center mt-10 pt-10 pb-10 manual-margin'>Your Google Access Code is Authorized and Saved.</div> ",
        ).appendTo("#gs-formntr-validation-message");
        setTimeout(function () {
          window.location.href = jQuery("#redirect_auth").val();
        }, 1000);
      }
    });
  });

  /**
   * Deactivates the API code.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", "#gs-formntr-deactivate-log", function () {
    jQuery(".loading-sign-deactive").addClass("loading");
    jQuery("#frmntr-confirm-deactive-popup-free").addClass("d-none");
    var data = {
      action: "deactivate_gs_formntr_integation",
      security: jQuery("#frmntr-gs-ajax-nonce").val(),
    };
    jQuery.post(ajaxurl, data, function (response) {
      if (response == -1) {
        return false; // Invalid nonce
      }

      if (!response.success) {
        jQuery(".loading-sign-deactive").removeClass("loading");
        jQuery("#gs-formntr-deactivate-message").empty();
      } else {
        jQuery(".loading-sign-deactive").removeClass("loading");
        jQuery("#gs-formntr-deactivate-message").empty();
        jQuery(
          "<div class='formntr-valid-message gsc-msg gsc-success fw-400 text-dark text-center mt-10 pt-10 pb-10 manual-margin'>Your account is removed. Reauthenticate again to integrate Forminator Forms with Google Sheet.</span>",
        ).appendTo("#gs-formntr-deactivate-message");
        setTimeout(function () {
          location.reload();
        }, 1000);
      }
    });
  });

  /**
   * Opens feed settings modal, retrieves form data via AJAX,
   * and populates the form fields.
   *
   * @since 1.0.15
   */

  function openFeedSettings(formId) {
    alert("Hello, world!");

    // Get the form data using AJAX
    jQuery.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        action: "get_form_data",
        form_id: formId,
      },
      success: function (response) {
        // Parse the response JSON and populate the feed settings form
        var formData = JSON.parse(response);
        // Code to populate the feed settings form fields with formData goes here

        // Display the feed settings modal
        var modal = document.getElementById("forminator-feed-settings-modal");
        modal.style.display = "block";

        // Add event listener to close the modal when the user clicks the close button
        var closeBtn = document.getElementsByClassName(
          "forminator-modal-close",
        )[0];
        closeBtn.addEventListener("click", function () {
          modal.style.display = "none";
        });

        // Add event listener to close the modal when the user clicks outside the modal
        window.addEventListener("click", function (event) {
          if (event.target == modal) {
            modal.style.display = "none";
          }
        });
      },
      error: function (error) {
        console.log(error);
      },
    });
  }

  /**
   * Decodes HTML entities in a string and returns plain text.
   *
   * @since 1.0.15
   */

  function html_decode(input) {
    var doc = new DOMParser().parseFromString(input, "text/html");
    return doc.documentElement.textContent;
  }

  /**
   * Syncs Google account to fetch latest sheet names and updates the UI.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", "#gs-formntr-sync", function () {
    jQuery(this).parent().children(".loading-sign").addClass("loading");
    var integration = jQuery(this).data("init");
    var data = {
      action: "sync_formntr_google_account",
      isajax: "yes",
      isinit: integration,
      security: jQuery("#frmntr-gs-ajax-nonce").val(),
    };

    jQuery.post(ajaxurl, data, function (response) {
      if (response == -1) {
        return false; // Invalid nonce
      }

      if (response.data.success == "yes") {
        jQuery(".loading-sign").removeClass("loading");
        jQuery("#gs-formntr-validation-message").empty();
        jQuery(
          "<span class='formntr-valid-message'>Fetched latest sheet names.</span>",
        ).appendTo("#gs-formntr-validation-message");
        setTimeout(function () {
          location.reload();
        }, 1000);
      } else {
        jQuery(this).parent().children(".loading-sign").removeClass("loading");
        location.reload(); // simply reload the page
      }
    });
  });

  /**
   * Clears debug logs via AJAX and shows confirmation message.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", ".debug-clear", function () {
    jQuery(".clear-loading-sign").addClass("loading");
    var data = {
      action: "gs_formntr_clear_logs",
      security: jQuery("#frmntr-gs-ajax-nonce").val(),
    };
    jQuery.post(ajaxurl, data, function (response) {
      if (response == -1) {
        return false; // Invalid nonce
      }
      var clear_msg = response.data;
      if (response.success) {
        jQuery(".clear-loading-sign").removeClass("loading");
        jQuery("#gs-formntr-validation-message").empty();
        jQuery(
          "<span class='gs-valid-message'>" + clear_msg + "</span>",
        ).appendTo("#gs-formntr-validation-message");
        setTimeout(function () {
          location.reload();
        }, 1000);
      }
    });
  });

  /**
   * Toggles system error logs visibility and updates button text.
   *
   * @since 1.0.15
   */

  jQuery(document).ready(function ($) {
    // Hide .frmgsc-system-Error-logs initially
    $(".frmgsc-system-Error-logs").hide();

    // Add a variable to track the state
    var isOpen = false;

    // Function to toggle visibility and button text
    function toggleLogs() {
      $(".frmgsc-system-Error-logs").toggle();
      // Change button text based on visibility
      $(".frmgsc-logs").text(isOpen ? "View" : "Close");
      isOpen = !isOpen; // Toggle the state
    }

    // Toggle visibility and button text when clicking .frmgsc-logs button
    $(".frmgsc-logs").on("click", function () {
      toggleLogs();
    });

    // Prevent the div from closing when clicking inside it
    $(".frmgsc-system-Error-logs").on("click", function (e) {
      e.stopPropagation(); // Stop the event from bubbling up
    });

    // Optional: You can add a specific button inside the div to close it explicitly
    $(".frmgsc-close-btn").on("click", function () {
      if (isOpen) {
        toggleLogs(); // Close the div when the close button is clicked
      }
    });
  });

  /**
   * Clears debug logs for the system status tab.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", ".clear-content-logs-frmt", function () {
    jQuery(".clear-loading-sign-logs-frmt").addClass("loading");
    var data = {
      action: "frm_clear_debug_logs",
      security: jQuery("#frmntr-gs-ajax-nonce").val(),
    };
    jQuery.post(ajaxurl, data, function (response) {
      if (response == -1) {
        return false; // Invalid nonce
      }

      if (response.success) {
        jQuery(".clear-loading-sign-logs-frmt").removeClass("loading");
        jQuery(".clear-content-logs-msg-frmt").html("Logs are cleared.");
        setTimeout(function () {
          location.reload();
        }, 1000);
      }
    });
  });

  /*Check All Option*/
  jQuery(document).on("click", "#checkAllformntrSheet", function () {
    jQuery(".formntr_order_state").not(this).prop("checked", this.checked);
  });

  /**
   * Verifies the API code.
   *
   * @since 1.0.15
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
   * Hides authentication button and displays token input.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", "#authlink_gsformntr", function (event) {
    jQuery(".wg_api_option_auth_url").hide();
    jQuery("#gs-formntr-client-token").show();
  });

  /**
   * Resets the form.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", "#save-formntr-reset", function (event) {
    jQuery("#gs-formntr-client-id").val("");
    jQuery("#gs-formntr-secret-id").val("");
    jQuery("#gs-formntr-client-token").val("");
    jQuery("#gs-formntr-client-id").removeAttr("disabled");
    jQuery("#gs-formntr-secret-id").removeAttr("disabled");
    jQuery("#save-gs-frmin-manual").removeAttr("disabled");
  });

  /**
   * Creates a new Google Sheet.
   *
   * @since 1.0.15
   */

  jQuery(document).on("change", "#gs-formntr-sheet-id", function () {
    if (this.value == "create_new") {
      //jQuery('.sheet-tab-name').hide();
      jQuery(".sheet-url").hide();
      jQuery(".create-ss-wrapper").show();
    }
  });

  /**
   * Processes order synchronization in batches via AJAX with recursive calls until complete.
   *
   * @since 1.0.15
   */

  function gsformntr_processAjax($type, $page, $total_entries, $processed) {
    var data = {
      action: "gsformntr_sync_orders",
      type: $type,
      processed: $processed,
      page: $page,
      total_entries: $total_entries,
    };

    jQuery.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function (response) {
        if (response == -1) {
          return;
        }

        response = JSON.parse(response);
        if (typeof response.process_further != "undefined") {
          var $process_further = response.process_further;

          if ($process_further == 1) {
            $page = response.next_page;
            $total_entries = response.total_entries;
            $processed = response.processed;

            jQuery(".sync-message").text(response.text);
            jQuery(".sync-loader").show();

            setTimeout(function () {
              gsformntr_processAjax($type, $page, $total_entries, $processed);
            }, 2000);
          } else {
            jQuery(".sync-message").text(response.text);
            jQuery(".sync-loader").hide();
          }
        }
      },
    });
  }

  /**
   * Starts order synchronization process when sync button is clicked.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", ".formntr-sync-orders", function (event) {
    var $this = jQuery(this);
    var $type = $this.attr("data-type");
    gsformntr_processAjax($type, 1, 0, 0);
  });

  jQuery(document).on("click", ".gscformntr_checkAll", function (event) {
    jQuery(".li-formntr-header input:checkbox")
      .not(this)
      .prop("checked", this.checked);
  });

  jQuery(document).on("click", ".edit_col_name", function (event) {
    var $parent = jQuery(this).parent();

    $parent.find(".label_text").fadeOut("fast", function () {
      $parent.find("input").fadeIn();
    });

    $parent.find(".edit_col_name").fadeOut("fast", function () {
      $parent.find(".update_col_name").fadeIn();
    });

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

  /**
   * Updates column name label with input value and toggles edit/display modes.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", ".update_col_name", function (event) {
    var $parent = jQuery(this).parent();
    var $input_value = $parent.find("input").val();
    $parent.find(".label_text").text($input_value);

    $parent.find("input").fadeOut("fast", function () {
      $parent.find(".label_text").fadeIn();
    });

    $parent.find(".update_col_name").fadeOut("fast", function () {
      $parent.find(".edit_col_name").fadeIn();
    });
  });

  /**
   * Toggles all header checkboxes based on selected radio button value.
   *
   * @since 1.0.15
   */

  jQuery(document).on("change", ".checkallradio", function (event) {
    var $val = jQuery(this).val();

    if ($val == "yes") {
      jQuery(".li-formntr-header input[value=0]").prop("checked", false);
      jQuery(".li-formntr-header input[value=1]").prop("checked", true);
    } else {
      jQuery(".li-formntr-header input[value=0]").prop("checked", true);
      jQuery(".li-formntr-header input[value=1]").prop("checked", false);
    }
    // jQuery('.li-formntr-header input:checkbox').not(this).prop('checked', this.checked);
  });

  /**
   * Loads and displays Google Sheet panel via AJAX when a form is selected.
   *
   * @since 1.0.15
   */

  jQuery(document).ready(function ($) {
    $("#forminator-form-select").change(function () {
      var form_id = $(this).val();
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: "get_google_sheet_panel", form_id: form_id },
        success: function (response) {
          $("#forminator-google-sheet-panel").html(response);
          $("#forminator-google-sheet-panel").show(); // show the panel
        },
        error: function (xhr, ajaxOptions, thrownError) {
          console.log("Error: " + thrownError);
        },
      });
    });
  });

  /**
   * Deletes a feed via AJAX after confirmation and reloads the page on success.
   *
   * @since 1.0.15
   */

  jQuery(document).ready(function ($) {
    $(".delete-feed").click(function () {
      var feedId = $(this).data("feed-id");
      var confirmDelete = confirm("Are you sure you want to delete this feed?");
      if (confirmDelete) {
        // Submit a POST request to delete the feed
        $.post(
          ajaxurl,
          {
            action: "delete_feed_forminator",
            feed_id: feedId,
            security: jQuery("#frmntr-gs-ajax-nonce").val(),
          },
          function (response) {
            if (response === "success") {
              // Reload the page after successful deletion
              location.reload();
            } else {
              // Handle error here
              console.log("Error deleting feed");
            }
          },
        );
      }
    });
  });

  /**
   * Toggles visibility of form list settings and switches minimize/maximize icons.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", ".gs-formntr-list-set", function (event) {
    var $this = jQuery(this);
    var $id = $this.attr("data-id");

    if (jQuery(".gs-formntr-list-set" + $id).css("display") == "none") {
      //jQuery(".gs-formntr-list-set"+$id).css("display", "block");
      jQuery(".gs-formntr-list-set" + $id).show("slow");
      jQuery(".mini_mize" + $id).show();
      jQuery(".maxi_mize" + $id).hide();
    } else {
      //jQuery(".gs-formntr-list-set"+$id).css("display", "none");
      jQuery(".gs-formntr-list-set" + $id).hide("slow");
      jQuery(".mini_mize" + $id).hide();
      jQuery(".maxi_mize" + $id).show();
    }
  });

  /**
   * Handles dropdown event for Google API.
   *
   * @since 1.0.15
   */

  /* drop down event for Google API */
  if (jQuery("#formntr_manual_setting").val() == "1") {
    jQuery("#gs_frmin_dro_option").val("frmin_manual");
    jQuery(".api_manual_setting_frmin").show();
    jQuery(".api_existing_setting").hide();
  }

  /**
   * Toggles manual and existing API settings based on dropdown selection.
   *
   * @since 1.0.15
   */

  jQuery(document).on("change", "#gs_frmin_dro_option", function () {
    //alert(jQuery('option:selected', jQuery(this)).val());
    var option = jQuery("option:selected", jQuery(this)).val();
    if (option == "frmin_manual") {
      jQuery(".api_manual_setting_frmin").show();
      jQuery(".api_existing_setting").hide();
    } else {
      jQuery(".api_manual_setting_frmin").hide();
      jQuery(".api_existing_setting").show();
    }
  });

  /**
   * Shows or hides Google Sheets settings box when Edit link is clicked.
   *
   * @since 1.0.15
   */

  jQuery(".forminator-forms-list__edit-link").click(function (e) {
    e.preventDefault();

    // Get the form ID
    var formId = jQuery(this).data("form-id");

    // Show/hide the Google Sheets settings box
    jQuery(".forminator-form-settings").hide();
    jQuery(".forminator-form-settings--" + formId).show();
  });

  /**
   * Validates Google Sheets settings form before submission and shows errors if needed.
   *
   * @since 1.0.15
   */

  jQuery(document).on("submit", "#gsformntrSettingForm", function (event) {
    console.log("prevent the subitting the form");
    jQuery("#error_gsformntrTabName").html("");
    var submit = true;
    var gsTabName = jQuery("input.formntr_order_state:checked").length;
    var spreadsheetsName = jQuery("#gs-formntr-sheet-id").val();
    if (spreadsheetsName == "") {
      jQuery("#error_formntr_spread").html(
        "* Please Select Spreadsheet Name !",
      );
      submit = false;
    }
    if (gsTabName <= 0) {
      jQuery("#error_gsformntrTabName").html(
        "* Please select atleast one Tabs !",
      );
      submit = false;
    }
    if (submit == false) {
      event.preventDefault();
      window.scrollTo({ top: 10, behavior: "smooth" });
    }
  });

  /**
   * Toggles add-feed popup visibility when add or close buttons are clicked.
   *
   * @since 1.0.15
   */

  jQuery("#close-feed").hide();
  jQuery(".add-feed-popup").hide();
  jQuery(".connect-form-to-gsheet").hide();
  jQuery(".edit-feed-popup").hide();

  jQuery("#add-new-feed").on("click", function (e) {
    jQuery(".add-feed-popup").show();
    jQuery("#close-feed").show();
  });

  jQuery("#close-add-feed-popup, #close-feed").on("click", function () {
    jQuery(".add-feed-popup").hide();
    jQuery("#close-feed").hide();
  });

  /**
   * Submits the feed form.
   *
   * @since 1.0.15
   */

  jQuery("#feed-form").submit(function (e) {
    e.preventDefault();
    var feedName = jQuery("#feed_name").val();
    if (feedName !== "") {
      jQuery(".add-feed-popup").hide();
      jQuery("#close-feed").hide();
      jQuery(".connect-form-to-gsheet").show();
    }
  });

  /**
   * Validates edit feed form fields before submission and shows inline
   * errors under any required field left empty.
   *
   * @since 1.0.15
   */

  jQuery("#edit-feed-form").submit(function (e) {
    var requiredFields = [
      { id: "edit-sheet-name", label: "Sheet Name is required." },
      { id: "edit-sheet-id", label: "Sheet ID is required." },
      { id: "edit-tab-name", label: "Tab Name is required." },
      { id: "edit-tab-id", label: "Tab ID is required." },
    ];
    var hasError = false;

    requiredFields.forEach(function (field) {
      var value = jQuery("#" + field.id).val();
      var $error = jQuery("#" + field.id + "-error");

      if (value == "") {
        $error.text(field.label);
        hasError = true;
      } else {
        $error.text("");
      }
    });

    if (hasError) {
      e.preventDefault();
    }
  });
});

/**
 * Hides the message element.
 *
 * @since 1.0.15
 */

jQuery(document).ready(function ($) {
  // Check if the message has already been hidden by looking in localStorage
  if (localStorage.getItem("googleDriveMsgHidden") === "true") {
    // jQuery('#google-drive-msg').hide(); // Hide the message if it's already hidden
  }

  // On button click, hide the #google-drive-msg div and store the hidden state in localStorage
  jQuery(".button_formgsc").on("click", function () {
    // jQuery('#google-drive-msg').hide(); // Hide the message
    localStorage.setItem("googleDriveMsgHidden", "true"); // Save the hidden state in localStorage
  });

  // On #deactivate-log click, show the #google-drive-msg div and clear localStorage
  jQuery("#gs-formntr-deactivate-log").on("click", function () {
    // jQuery('#google-drive-msg').show(); // Show the message
    localStorage.removeItem("googleDriveMsgHidden"); // Remove the hidden state from localStorage
  });
});

/** JS for display auth method */
document.addEventListener("DOMContentLoaded", function () {
  var el = document.querySelector(".gsfrm-selected-method");

  if (el && el.dataset.value && el.dataset.value.trim() !== "") {
    var badgeText = el.dataset.value.trim();

    document
      .querySelectorAll(".nav-tab-wrapper .nav-tab")
      .forEach(function (tab) {
        var href = tab.getAttribute("href") || "";

        if (href.indexOf("tab=integration") !== -1) {
          tab.style.position = "relative";

          if (tab.querySelector(".gsfrm-selected-badge")) return;
          var badge = document.createElement("div");

          if (badgeText == "Auth Required") {
            badge.className = "gsfrm-auth-required-selected-badge"; // ✅ IMPORTANT
            badge.textContent = badgeText;
          } else {
            badge.className = "gsfrm-selected-badge"; // ✅ IMPORTANT
            badge.textContent = badgeText;
          }
          tab.appendChild(badge);
        }
      });
  }
});

/** hide notice  */
jQuery(document).on("click", "#gsfrm-pro-dismiss-header-notice", function () {
  var nonce = jQuery("#frmntr-gs-ajax-nonce").val();

  jQuery("#pro-notice-bar").hide();

  jQuery.post(ajaxurl, {
    action: "dismiss_frmntr_pro_notice",
    nonce: nonce,
  });
});

jQuery(document).ready(function ($) {
  function formntrLoadFeedPage(page) {
    var data = {
      action: "formntr_paginate_feed_list",
      paged: page,
      security: $("#formntr-ajax-nonce-pagination").val(),
    };

    $("#formntr-feed-table-body").html(
      '<tr class="formntr-feed-loading-row">' +
        '<td colspan="3">' +
        '<span class="formntr-loader"></span>' +
        '<span class="formntr-loader-text">Loading feeds...</span>' +
        "</td>" +
        "</tr>",
    );

    if ($("#formntr-ajax-nonce-pagination").val()) {
      $.post(ajaxurl, data, function (res) {
        if (res.success) {
          $("#formntr-feed-table-body").html(res.data.rows_html);
          $("#formntr-pagination-wrap").html(res.data.pagination_html);
          $("#formntr-feed-table").attr("data-page", page);

          // Hide the table header when no feed is connected (empty state).
          var dividbNoFeeds =
            $("#formntr-feed-table-body").find(".formntr-feed-empty").length >
            0;
          $("#formntr-feed-table thead").toggle(!dividbNoFeeds);
        } else {
          $("#formntr-feed-table-body").html(
            "<tr><td colspan='3'>" +
              (res.data && res.data.error
                ? res.data.error
                : "Error loading feeds") +
              "</td></tr>",
          );
        }
      });
    }
  }

  formntrLoadFeedPage(1);

  $(document).on("click", ".formntr-page-link", function () {
    var page = $(this).data("page");
    formntrLoadFeedPage(page);
  });
});

/** Notification slider  */
/** notificatin slider arrow button will hide when slider is 1 or 0 */
jQuery(document).ready(function ($) {
  if ($(".notification-formntr-slide").length <= 1) {
    $(".notification-formntr-slider-arrows").hide();
  }

  if ($(".notification-formntr-slide").length == 0) {
    $(".notification-formntr-notice-slider").hide();
  }

  $(".formntr-free-counter").each(function () {
    let $this = $(this);
    let countTo = parseFloat($this.attr("data-count"));

    $({ countNum: 0 }).animate(
      {
        countNum: countTo,
      },
      {
        duration: 2500,
        easing: "swing",

        step: function () {
          if (countTo % 1 !== 0) {
            $this.text(this.countNum.toFixed(1));
          } else {
            $this.text(Math.floor(this.countNum));
          }
        },

        complete: function () {
          if (countTo % 1 !== 0) {
            $this.text(countTo.toFixed(1));
          } else {
            $this.text(countTo);
          }
        },
      },
    );
  });
});

jQuery(document).ready(function ($) {
  let totalSlides = $(
    ".notification-formntr-slider-track .notification-formntr-slide",
  ).length;

  if (totalSlides <= 1) {
  }
});

function showNextSlide(currentSlide) {
  var track = currentSlide.closest(".notification-formntr-slider-track");
  var slides = track.find(".notification-formntr-slide");
  var currentIndex = slides.index(currentSlide);
  var nextIndex = currentIndex + 1;

  currentSlide.remove();

  slides = track.find(".notification-formntr-slide");

  if (slides.length > 0) {
    if (nextIndex >= slides.length) {
      nextIndex = 0;
    }
    slides.hide().eq(nextIndex).show();
  }
  location.reload();
}

/** For JQuery for Dismiss notification */
jQuery(document).on(
  "click",
  ".formntr-review-close, .formntr-review-dismiss-btn  , .formntr-showpro-close, .formntr-addons-close, .formntr-enhance-btn-later, .formntr-enhance-close",
  function () {
    console.log("Dismiss button clicked");
    var key = jQuery(this).data("key");
    var currentSlide = jQuery(this).closest(".notification-formntr-slide");

    jQuery.post(
      ajaxurl,
      {
        action: "formntr_dismiss_notice",
        key: key,
        security: jQuery("#frmntr-gs-ajax-nonce").val(),
      },
      function () {
        showNextSlide(currentSlide);
      },
    );
  },
);

/** For JQuery for snooze notification */
jQuery(document).on(
  "click",
  ".formntr-review-btn-later, .formntr-Showpro-btn-later, .formntr-addons-btn-later",
  function () {
    var key = jQuery(this).data("key");
    var currentSlide = jQuery(this).closest(".notification-formntr-slide");

    jQuery.post(
      ajaxurl,
      {
        action: "formntr_snooze_notice",
        key: key,
        security: jQuery("#frmntr-gs-ajax-nonce").val(),
      },
      function () {
        showNextSlide(currentSlide);
      },
    );
  },
);

/**  */
document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".notification-formntr-slide");
  const prevBtn = document.querySelector(
    ".notification-formntr-slider-btn.prev",
  );
  const nextBtn = document.querySelector(
    ".notification-formntr-slider-btn.next",
  );

  let index = 0;

  function updateSlider() {
    if (!slides || slides.length === 0) return;
    slides.forEach((slide) => slide.classList.remove("active"));
    if (slides[index]) {
      slides[index].classList.add("active");
    }
  }

  nextBtn.addEventListener("click", function () {
    index++;
    if (index >= slides.length) {
      index = 0;
    }
    updateSlider();
  });

  prevBtn.addEventListener("click", function () {
    index--;
    if (index < 0) {
      index = slides.length - 1;
    }
    updateSlider();
  });
  updateSlider();
});

/***new slider for without permission for existing method */
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".gsc-slider-wrapper").forEach(function (wrapper) {
    const slider = wrapper.querySelector(".gsc-slider");
    const slides = wrapper.querySelectorAll(".gsc-slide");
    const prevBtn = wrapper.querySelector(".gsc-nav.prev");
    const nextBtn = wrapper.querySelector(".gsc-nav.next");
    let current = 0;
    function updateSlider() {
      slider.style.transform = "translateX(" + -current * 100 + "%)";
    }
    nextBtn?.addEventListener("click", function () {
      current = (current + 1) % slides.length;
      updateSlider();
    });
    prevBtn?.addEventListener("click", function () {
      current = (current - 1 + slides.length) % slides.length;
      updateSlider();
    });
    /*  attach function */
    wrapper.goToSlide = function (index) {
      current = index;
      updateSlider();
    };
  });

  /*  AUTO MOVE TO STEP 4   */
  setTimeout(function () {
    const errorBox = document.querySelector(".frmntr-permission-error");
    const target = document.querySelector(".frmntr-connection-guide-slider");
    if (!errorBox) {
      return;
    }
    if (!target) {
      return;
    }
    if (target.goToSlide) {
      target.goToSlide(3);
      target.scrollIntoView({
        behavior: "smooth",
        block: "center",
      });
      console.log("Auto moved to Step 4");
    } else {
      console.log("goToSlide not available");
    }
  }, 800);
});

// Open popup instead of alert
jQuery(document).on("click", "#formntr-deactivate-log", function () {
  jQuery("#frmntr-confirm-deactive-popup-free").removeClass("d-none");
});

// Cancel button
jQuery(document).on("click", "#frmntr-deactive-popup-free-cancel", function () {
  jQuery("#frmntr-confirm-deactive-popup-free").addClass("d-none");
});

/** pro Sheets Integration  popup */
jQuery(document).ready(function ($) {
  $("#gs_formntr_dro_option").on("change", function () {
    const value = $(this).val();

    if (value === "1") {
      $("#frmntr-confirm-manual-popup-pro").removeClass("d-none");
    }
  });

  // Close popup (Cancel button)
  $(document).on("click", ".frmntr-popup-close-pro", function () {
    $("#frmntr-confirm-manual-popup-pro").addClass("d-none");
    $("#gs_formntr_dro_option").val("0");
  });

  // Close popup on overlay click
  $(document).on("click", "#frmntr-confirm-manual-popup-pro", function (e) {
    if ($(e.target).is(this)) {
      $(this).addClass("d-none");
      $("#gs_formntr_dro_option").val("0");
    }
  });
});

jQuery(document).ready(function ($) {
  $("#gs_formntr_dro_option").on("change", function () {
    const value = $(this).val();

    if (value === "2") {
      $("#frmntr-confirm-service-popup-pro").removeClass("d-none");
    }
  });

  // Close popup (Cancel button)
  $(document).on("click", ".frmntr-popup-service-close-pro", function () {
    $("#frmntr-confirm-service-popup-pro").addClass("d-none");
    $("#gs_formntr_dro_option").val("0");
  });

  // Close popup on overlay click
  $(document).on("click", "#frmntr-confirm-service-popup-pro", function (e) {
    if ($(e.target).is(this)) {
      $(this).addClass("d-none");
      $("#gs_formntr_dro_option").val("0");
    }
  });
});

/** Update select layout in integration page */
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("select.gsc-select").forEach(function (select) {
    /*  skip if already converted*/
    if (select.classList.contains("auto-processed")) return;
    select.classList.add("auto-processed");

    /* hide original select */
    select.style.display = "none";

    /* wrapper */
    const wrapper = document.createElement("div");
    wrapper.className = "auto-select";

    /* display */
    const display = document.createElement("div");
    display.className = "auto-select-display";

    /* options container */
    const optionsBox = document.createElement("div");
    optionsBox.className = "auto-select-options";

    /* set selected option text */
    function setSelectedText() {
      const selectedOption = select.options[select.selectedIndex];
      display.innerText = selectedOption ? selectedOption.text : "Select";
    }

    /* build options */
    Array.from(select.options).forEach(function (option, index) {
      const item = document.createElement("div");
      item.className = "auto-select-option";
      item.innerText = option.text;

      if (option.selected) {
        item.classList.add("selected");
      }

      item.addEventListener("click", function () {
        /*  update select value */
        select.selectedIndex = index;

        /*  update display */
        display.innerText = option.text;

        /*  update selected class */
        optionsBox
          .querySelectorAll(".auto-select-option")
          .forEach(function (el) {
            el.classList.remove("selected");
          });

        item.classList.add("selected");

        /*  close dropdown */
        optionsBox.style.display = "none";

        /*  trigger change event */
        select.dispatchEvent(new Event("change", { bubbles: true }));
      });

      optionsBox.appendChild(item);
    });

    /*  toggle dropdown */
    display.addEventListener("click", function (e) {
      e.stopPropagation();

      document.querySelectorAll(".auto-select-options").forEach(function (box) {
        if (box !== optionsBox) box.style.display = "none";
      });

      optionsBox.style.display =
        optionsBox.style.display === "block" ? "none" : "block";
    });

    /*  assemble */
    wrapper.appendChild(display);
    wrapper.appendChild(optionsBox);

    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    /*  initialize selected value */
    setSelectedText();

    /*  update if changed externally */
    select.addEventListener("change", setSelectedText);
  });

  /*  close dropdown on outside click */
  document.addEventListener("click", function (e) {
    document.querySelectorAll(".auto-select-options").forEach(function (box) {
      if (!box.parentElement.contains(e.target)) {
        box.style.display = "none";
      }
    });
  });
});

/** Clear Debug Log in integration tab */
jQuery(document).on("click", "#gsfrmnf-clear-logs-btn", function (e) {
  jQuery(".gsfrmnf-copy-logs-loading").addClass("loading");
  e.preventDefault();
  var $btn = jQuery(this);
  jQuery.ajax({
    url: ajaxurl,
    type: "POST",
    data: {
      action: "formntr_clear_error_logs",
      nonce: jQuery("#frmntr-gs-ajax-nonce").val(),
    },
    success: function (response) {
      if (response && response.success) {
        jQuery(".gsfrmnf-copy-logs-loading").removeClass("loading");
        location.reload();
      }
    },
  });
});

/** For Copy Dubug Log */
document.addEventListener("DOMContentLoaded", function () {
  const copyBtn = document.getElementById("gsfrmnf-copy-logs");
  const msgDiv = document.querySelector(".gsc-copy-msg");

  if (!copyBtn || !msgDiv) return;

  copyBtn.addEventListener("click", function (e) {
    e.preventDefault();

    const tbody = document.querySelector(".gsfrmnf-error-log-table tbody");
    if (!tbody) return;

    const rows = tbody.querySelectorAll("tr");
    if (!rows.length) {
      showMessage("No logs found to copy.", "error");
      return;
    }

    let output = "";

    rows.forEach((tr) => {
      const cols = tr.querySelectorAll("td");
      if (cols.length < 5) return;

      const date = (cols[0].innerText || "").trim();
      const errorId = (cols[1].innerText || "").trim();
      const code = (cols[2].innerText || "").trim();
      const message = (cols[3].innerText || "").trim();

      /* Proper details extraction */
      const detailsCell = cols[4];
      const details = detailsCell.querySelector("pre")
        ? detailsCell.querySelector("pre").innerText.trim()
        : (detailsCell.innerText || "").trim();

      output += `Date: ${date}\n`;
      output += `Error ID: ${errorId}\n`;
      output += `Code: ${code}\n`;
      output += `Message: ${message}\n`;
      output += `Details: ${details}\n`;
      output += `\n==========================\n\n`;
    });

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(output)
        .then(function () {
          showMessage("Copied successfully", "success");
        })
        .catch(function () {
          fallbackCopy(output);
        });
    } else {
      fallbackCopy(output);
    }

    function fallbackCopy(text) {
      const ta = document.createElement("textarea");
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand("copy");
        showMessage("Copied successfully", "success");
      } catch (err) {
        showMessage("Copy failed. Please copy manually.", "error");
      }
      document.body.removeChild(ta);
    }

    function showMessage(text, type) {
      msgDiv.innerText = text;

      msgDiv.classList.remove("d-none");
      setTimeout(function () {
        msgDiv.classList.add("d-none");
      }, 2000);
    }
  });
});

/**jQuery for save uninstall settings */
jQuery(document).ready(function ($) {
  const $checkbox = $("#gs_formntr_uninstall_settings");
  const $saveBtn = $(".gs_formntr_save_uninstall_settings");
  const $msg = $("#formntr-uninstall-msg-free");
  const $loader = $(".loading-uninstall-free");
  const $popup = $("#formntr-confirm-uninstall-data-popup-free");

  // Page load → disable button
  $saveBtn.prop("disabled", true).addClass("common-disable");

  // Checkbox change
  $checkbox.on("change", function () {
    // If user tries to enable it → show popup
    if ($(this).is(":checked")) {
      // Uncheck temporarily
      $(this).prop("checked", false);

      // Show popup
      $popup.removeClass("d-none");

      return;
    }

    // Enable save button normally when unchecked
    $saveBtn.prop("disabled", false).removeClass("common-disable");
  });

  /* =========================
     POPUP CONFIRM BUTTON
     ========================= */

  $("#formntr-confirm-enable-uninstall-free").on("click", function () {
    $checkbox.prop("checked", true);

    $popup.addClass("d-none");

    $saveBtn.prop("disabled", false).removeClass("common-disable");
  });

  /* =========================
     POPUP CANCEL BUTTON
     ========================= */

  $("#formntr-cancel-uninstall-free").on("click", function () {
    $checkbox.prop("checked", false);

    $popup.addClass("d-none");
  });

  /* =========================
     SAVE SETTINGS
     ========================= */

  $saveBtn.on("click", function (e) {
    e.preventDefault();

    var isChecked = $checkbox.is(":checked");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "formntr_save_uninstall_settings",
        uninstall_setting: isChecked ? 1 : 0,
        security: $("#gs-formntr-setting-ajax-nonce").val(),
      },

      beforeSend: function () {
        $loader.addClass("loading");
        $saveBtn.prop("disabled", true).addClass("common-disable");
      },

      success: function (response) {
        if (!response.success) return;

        $msg.removeClass("gsc-success gsc-error d-none");

        $msg
          .addClass("gsc-success")
          .text("Plugin preferences updated successfully.");

        setTimeout(function () {
          $msg.addClass("d-none").text("");
        }, 2000);
      },

      error: function () {
        $msg
          .removeClass("d-none")
          .addClass("gsc-error")
          .text("Something went wrong");

        $saveBtn.prop("disabled", false).removeClass("common-disable");
      },

      complete: function () {
        $loader.removeClass("loading");
      },
    });
  });
});

jQuery(document).ready(function ($) {
  // Copy Error Log
  function copyErrorLog() {
    const $textarea = $(".errorlog");
    const $copyMessage = $(".copy-message");

    if ($textarea.length && $copyMessage.length) {
      $textarea.select();
      try {
        document.execCommand("copy");
        $copyMessage.show();
        setTimeout(() => $copyMessage.hide(), 3000);
      } catch (err) {
        console.error("Unable to copy error log:", err);
        alert("Error log copy failed. Please copy it manually.");
      }
      $textarea.blur();
    } else {
      alert("Error log textarea or copy message not found.");
    }
  }

  // Clear Error Log
  function clearErrorLog() {
    $(".errorlog").val("");
  }

  // Bind toggle buttons
  $("#formntr-info-container").show();
  function accordionToggle(button, container) {
    $(button).on("click", function () {
      if ($(container).is(":visible")) {
        // second click → close same section
        $(container).slideUp();
      } else {
        // open clicked, close others
        $(".info-content").slideUp();
        $(container).slideDown();
      }
    });
  }
  accordionToggle("#formntr-show-info-button", "#formntr-info-container");
  accordionToggle(
    "#formntr-show-wordpress-info-button",
    "#formntr-wordpress-info-container",
  );
  accordionToggle(
    "#formntr-show-active-info-button",
    "#formntr-active-info-container",
  );
  accordionToggle(
    "#formntr-show-netplug-info-button",
    "#formntr-netplug-info-container",
  );
  accordionToggle(
    "#formntr-show-acplug-info-button",
    "#formntr-acplug-info-container",
  );
  accordionToggle(
    "#formntr-show-server-info-button",
    "#formntr-server-info-container",
  );
  accordionToggle(
    "#formntr-show-database-info-button",
    "#formntr-database-info-container",
  );
  accordionToggle(
    "#formntr-show-wrcons-info-button",
    "#formntr-wrcons-info-container",
  );
  accordionToggle(
    "#formntr-show-ftps-info-button",
    "#formntr-ftps-info-container",
  );

  // Bind copy and clear buttons

  $(document).on("click", "#formntr-copy-system-info", function () {
    var systemInfoContainer = document.querySelector(
      ".formntr-free-info-container",
    );
    if (!systemInfoContainer) return;

    var textToCopy = "";

    systemInfoContainer
      .querySelectorAll(".info-button")
      .forEach(function (btn) {
        var containerId = btn.id
          .replace("show-", "")
          .replace("-button", "-container");
        var container = document.getElementById(containerId);

        var heading = btn.innerText.trim();
        textToCopy += "\n====================================\n";
        textToCopy += heading + "\n";
        textToCopy += "====================================\n";

        if (container) {
          container.querySelectorAll("tr").forEach(function (row) {
            var cells = row.querySelectorAll("td");
            if (cells.length >= 2) {
              var rowText =
                cells[0].innerText.trim() + ": " + cells[1].innerText.trim();
              for (var i = 2; i < cells.length; i++) {
                rowText += " - " + cells[i].innerText.trim();
              }
              textToCopy += rowText + "\n";
            }
          });
        }

        textToCopy += "\n";
      });

    textToCopy = textToCopy.trim();

    if (!textToCopy) {
      console.error("Nothing to copy");
      return;
    }

    var btn = this;
    var msgDiv = btn.parentNode.querySelector(".gsc-copy-msg-info");
    if (!msgDiv) {
      msgDiv = document.createElement("div");
      msgDiv.className = "gsc-copy-msg";
      msgDiv.style.display = "none";
      btn.parentNode.appendChild(msgDiv);
    }

    function showCopied() {
      msgDiv.innerHTML = "Copied successfully";
      msgDiv.style.display = "block";
      setTimeout(function () {
        msgDiv.style.display = "none";
      }, 1000);
    }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard
        .writeText(textToCopy)
        .then(showCopied)
        .catch(function (err) {
          console.error("Clipboard error:", err);
        });
    } else {
      var textarea = document.createElement("textarea");
      textarea.value = textToCopy;
      textarea.style.position = "fixed";
      textarea.style.left = "-9999px";
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      try {
        document.execCommand("copy");
        showCopied();
      } catch (e) {
        console.error("Copy failed", e);
      }
      document.body.removeChild(textarea);
    }
  });
});

/** Clear debug for system status tab */
jQuery(document).on("click", ".formntr-clear-debug-logs", function () {
  jQuery(".loading-sign-setting").addClass("loading");
  var data = {
    action: "formntrp_clear_debug_logs",
    security: jQuery("#frmntr-gs-ajax-nonce").val(),
  };
  jQuery.post(ajaxurl, data, function (response) {
    if (response == -1) {
      return false; /*  Invalid nonce */
    }

    if (response.success) {
      jQuery(".loading-sign-setting").removeClass("loading");

      /*  Show success message */
      var $msg = jQuery(".gsc-copy-msg");

      $msg.text("Logs are cleared").removeClass("d-none");

      setTimeout(function () {
        $msg.addClass("d-none");
      }, 3000);

      setTimeout(function () {
        location.reload();
      }, 1000);
    }
  });
});

/** download debug log csv file */
jQuery(document).ready(function ($) {
  $("#formntr-download-csv").on("click", function (e) {
    e.preventDefault();
    var rows = $("table.widefat tr");
    var csvContent = "";

    if (rows.length === 0) {
      console.log("No error logs found.");
      return;
    }
    rows.each(function () {
      var cols = $(this).find("th, td");
      var rowData = [];

      cols.each(function () {
        var text = $(this).text().trim();

        /*  Escape quotes */
        text = text.replace(/"/g, '""');

        rowData.push('"' + text + '"');
      });

      csvContent += rowData.join(",") + "\n";
    });

    /*  Create Blob */
    var blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });

    var link = document.createElement("a");
    var url = URL.createObjectURL(blob);

    link.setAttribute("href", url);
    link.setAttribute("download", "debug-log.csv");
    link.style.visibility = "hidden";

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });
});

/** when the return auth with code will scroll down to token save button */
jQuery(document).ready(function ($) {
  /* Check if URL has "code" parameter */
  const code = new URLSearchParams(window.location.search).get("code");

  if (!code) return;

  /*possible targets */
  const selectors = ["#save-gs-formntr-code"];

  let target = null;

  /* find which ID exists  */
  for (let sel of selectors) {
    if (document.querySelector(sel)) {
      target = sel;
      break;
    }
  }

  if (target) {
    window.location.hash = target.replace("#", "");

    document.querySelector(target).scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
});

/**
 * Toggle visibility of the add-feed form and buttons on click.
 *
 * @since 1.0.0
 */
/* Hide the close-feed button initially */
jQuery(document).ready(function () {
  jQuery("#close-feed").hide();
  jQuery(".formntr-pro-feed").hide();
  /*  Show the add-feed form and toggle buttons*/
  jQuery(document).on("click", "#add-new-feed", function () {
    jQuery(".formntr-pro-feed").show();
    jQuery("#add-new-feed").hide();
    jQuery("#close-feed").show();
  });

  // Hide the add-feed form and toggle buttons
  jQuery(document).on("click", "#close-feed", function (event) {
    event.preventDefault(); // Prevent default link behavior
    jQuery("#add-new-feed").show();
    jQuery("#close-feed").hide();
    jQuery(".formntr-pro-feed").hide();
  });
});

/**Feed save error */
jQuery(document).ready(function () {
  jQuery(".gsc-formntr-sub-btn").on("click", function () {
    let isValid = true;

    // Feed name validation
    let feedName = jQuery("#formntr_feed_name").val().trim();
    if (feedName === "") {
      jQuery("#formntr_feed_name").next(".input-msg").removeClass("d-none");
      isValid = false;
    } else {
      jQuery("#formntr_feed_name").next(".input-msg").addClass("d-none");
    }

    // Form select validation
    let selectedForm = jQuery("#formntr_form_select").val();
    if (selectedForm === "") {
      jQuery("#formntr_form_select").next(".input-msg").removeClass("d-none");
      isValid = false;
    } else {
      jQuery("#formntr_form_select").next(".input-msg").addClass("d-none");
    }

    // Stop submit if invalid
    if (!isValid) {
      return false;
    }
  });
});

/**Feed save error */
jQuery(document).ready(function () {
  jQuery(document).on("input", "#formntr_feed_name", function () {
    jQuery(this).next(".input-msg").addClass("d-none");
  });

  jQuery(document).on("change", "#formntr_form_select", function () {
    jQuery(this).next(".input-msg").addClass("d-none");
  });

  jQuery(document).on("click", ".gsc-formntr-sub-btn", function (e) {
    e.preventDefault();

    var feed_name = jQuery(".feedName").val().trim();
    var formntr = jQuery(".formntr_formId").val();

    /*  Hide old messages */
    jQuery(".feed-success-message").hide().html("");
    jQuery(".feed-error-message").hide().html("");

    if (feed_name !== "" && formntr !== "") {
      /*  Show loader */
      jQuery(".formntr-fetch-load").addClass("loading");

      jQuery.post(
        ajaxurl,
        {
          action: "formntr_free_save_feed",
          security: jQuery("#formntr-ajax-nonce").val(),
          feed_name: feed_name,
          formntr: formntr,
        },
        function (response) {
          /*  Hide loader */
          jQuery(".formntr-fetch-load").removeClass("loading");

          if (response.success) {
            /* SUCCESS MESSAGE SHOW */
            jQuery(".feed-success-message").html(response.data).fadeIn(200);

            /* Reset fields */
            jQuery(".feedName").val("");
            jQuery(".gsc-fluentforms").val("");

            /* Optional reload */
            setTimeout(function () {
              location.reload();
            }, 1000);
          } else {
            jQuery(".feed-error-message").html(response.data).fadeIn(200);

            setTimeout(function () {
              jQuery(".feed-error-message").fadeOut(300);
            }, 2000);
          }
        },
        "json",
      );
    } else {
      jQuery(".feed-error-message")
        .html("Please enter Feed Name and select Form.")
        .fadeIn(200);
    }
  });
});

jQuery(document).ready(function ($) {
  /** 1) Click rename icon -> show textbox + cancel/save icons, hide title */
  $(document).on("click", ".rename-feed-link", function (e) {
    e.preventDefault();

    var $wrapper = $(this).closest(".feed-name-wrapper");

    $wrapper.find(".feed-name-text").addClass("d-none");
    $wrapper.find(".rename-feed-link").addClass("d-none");
    $wrapper.find(".feed-name-edit-row").removeClass("d-none");
    $wrapper.find(".feed-name-input").trigger("focus").trigger("select");
  });

  /** 2) Click cancel -> hide edit row, show title again */
  $(document).on("click", ".feed-name-cancel-btn", function (e) {
    e.preventDefault();

    var $wrapper = $(this).closest(".feed-name-wrapper");
    var $input = $wrapper.find(".feed-name-input");
    var original = $wrapper.find(".feed-name-text").text().trim();

    $input.val(original);

    $wrapper.find(".feed-name-edit-row").addClass("d-none");
    $wrapper.find(".feed-name-text").removeClass("d-none");
    $wrapper.find(".rename-feed-link").removeClass("d-none");
  });

  /** 3) Click save icon -> AJAX update feed name */
  $(document).on("click", ".feed-name-save-btn", function (e) {
    e.preventDefault();

    var $saveBtn = $(this);
    var $wrapper = $saveBtn.closest(".feed-name-wrapper");
    var $input = $wrapper.find(".feed-name-input");
    var $spinner = $wrapper.find(".feed-name-spinner");
    var $titleTxt = $wrapper.find(".feed-name-text");

    var feedId = $.trim($wrapper.attr("data-feed-id"));
    var newName = $.trim($input.val());

    if (!feedId) {
      console.warn("No feed-id found for rename.");
      return;
    }

    if (!newName) {
      alert("Feed name cannot be empty.");
      return;
    }

    $saveBtn.prop("disabled", true);
    $wrapper.find(".feed-name-cancel-btn").prop("disabled", true);
    $spinner.addClass("loading");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "formntr_update_feed_name",
        feed_id: feedId,
        feed_name: newName,
        security: jQuery("#frmntr-gs-ajax-nonce").val(),
      },
    })
      .done(function (response) {
        if (response && response.success) {
          $titleTxt.text(response.data.feed_name || newName);

          $wrapper.find(".feed-name-edit-row").addClass("d-none");
          $titleTxt.removeClass("d-none");
          $wrapper.find(".rename-feed-link").removeClass("d-none");
        } else {
          alert(
            (response && response.data && response.data.message) ||
            "Unable to update feed name.",
          );
        }
      })
      .fail(function () {
        alert("Something went wrong. Please try again.");
      })
      .always(function () {
        $saveBtn.prop("disabled", false);
        $wrapper.find(".feed-name-cancel-btn").prop("disabled", false);
        $spinner.removeClass("loading");
      });
  });
});



/** Popup for delete feed  */
jQuery(document).on("click", ".formntr-pro-delete-feed-btn", function (e) {
  e.preventDefault();
  const feedId = jQuery(this).data("feed-id");
  jQuery("#formntr-confirm-delete-popup-" + feedId).removeClass("d-none");
});

/** Popup for cancel feed  */
jQuery(".formntr-delete-popup-cancel").on("click", function (e) {
  e.preventDefault();
  const feedId = jQuery(this).data("feed-id");
  jQuery("#formntr-confirm-delete-popup-" + feedId).addClass("d-none");
});


/** Delete feed  */
jQuery(document).on("click", ".formntr-delete-popup-confirm", function (e) {
  e.preventDefault();
  const feedId = jQuery(this).data("feed-id");
  var $loader = jQuery(".loading-sign-delete-feed-" + feedId);
  $loader.addClass("loading");
  jQuery("#formntr-confirm-delete-popup-" + feedId).addClass("d-none");
  jQuery.ajax({
    url: ajaxurl,
    type: "POST",
    dataType: "json",
    data: {
      action: "formntr_free_feed_delete_ajax",
      feed_id: feedId,
      security: jQuery("#frmntr-gs-ajax-nonce").val(),
    },
    success: function (response) {
      if (!response.success) {
        return;
      }
      jQuery(".gsfrmnp-role").removeClass("d-none");
      $loader.removeClass("loading");
      setTimeout(function () {
        jQuery(".gsfrmnp-role").addClass("d-none");
      }, 3000);
      location.reload();
    },
  });
});


/** Enable / disable a feed */
jQuery(document).on("change", ".feed-status-toggle", function () {
  var $toggle = jQuery(this);
  var feedId = jQuery.trim($toggle.data("feed-id"));
  var newStatus = $toggle.is(":checked") ? 1 : 0;
  var $row = jQuery("#feed-" + feedId);
  var $spinner = $toggle.closest(".custom-check").find(".feed-status-spinner");

  if (!feedId) {
    return;
  }

  $toggle.prop("disabled", true);
  $spinner.addClass("loading");

  jQuery
    .ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "formntr_feed_status_toggle",
        feed_id: feedId,
        status: newStatus,
        security: jQuery("#frmntr-gs-ajax-nonce").val(),
      },
    })
    .done(function (response) {
      if (response && response.success) {
        $row.toggleClass("row-disabled", newStatus === 0);
      } else {
        // revert on failure
        $toggle.prop("checked", newStatus === 0);
        alert(
          (response && response.data && response.data.message) ||
            "Unable to update feed status.",
        );
      }
    })
    .fail(function () {
      $toggle.prop("checked", newStatus === 0);
      alert("Something went wrong. Please try again.");
    })
    .always(function () {
      $toggle.prop("disabled", false);
      $spinner.removeClass("loading");
    });
});