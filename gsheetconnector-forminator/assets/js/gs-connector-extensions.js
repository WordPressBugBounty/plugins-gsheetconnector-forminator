jQuery(document).ready(function (jQuery) {

  /**
   * Hides empty addon sections and adds a CSS class on page load.
   *
   * @since 1.0.15
   */

  jQuery(".gsheetconnector-addons-list").each(function () {
    if (jQuery(this).html().trim().length === 0) {
      jQuery(this).addClass("blank_div");
      jQuery(this).prev("h2").hide();
    }
  });

  /**
   * Handles plugin install button click via AJAX.
   *
   * Shows loading spinner, sends plugin slug and URL to server, and updates UI based on response.
   *
   * @since 1.0.15
   */

  jQuery(".gs-ff-install-plugin-btn").on("click", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var downloadUrl = button.data("download");
    var loaderSpan = button
      .closest(".button-bar")
      .find(".loading-sign-install");

    loaderSpan.addClass("loading");

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "gs_ff_install_plugin",
        plugin_slug: pluginSlug,
        download_url: downloadUrl,
        security: jQuery("#frmntr-gs-ajax-nonce").val(),
      },
      success: function (response) {
        loaderSpan.removeClass("loading");
        if (response.success) {
          button.hide();
          button.closest(".button-bar").find(".gs-ff-activate-plugin-btn").show();
        } else {
          button.html("Install").prop("disabled", false);
        }
      },
      error: function () {
        loaderSpan.removeClass("loading");
        button.html("Install").prop("disabled", false);
      },
    });
  });

  /**
   * Handles plugin activation button click via AJAX.
   *
   * Shows loading spinner, sends plugin slug for activation, and updates UI or reloads page based on response.
   *
   * @since 1.0.15
   */

  jQuery(document).on("click", ".gs-ff-activate-plugin-btn", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var loaderSpan = button.siblings(".loading-sign-active");
    loaderSpan.addClass("loading");
    // button.prop("disabled", true);
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "gs_ff_activate_plugin",
        plugin_slug: pluginSlug,
        security: jQuery("#frmntr-gs-ajax-nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          button.text("Activated"); // Show "Activated"
          button.prop("disabled", true);
          location.reload();
        } else {
          loaderSpan.removeClass("loading"); // Clear loader
          button.prop("disabled", false);
        }
      },
      error: function () {
        loaderSpan.removeClass("loading").text(""); // Clear loader
        button.prop("disabled", false);
      },
    });
  });


});



/* Extenxion Counter */
jQuery(document).ready(function ($) {
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


/**
     * Handle plugin deactivation button click via AJAX.
     *
     * - Sends plugin slug to server for deactivation
     * - On success, shows alert and reloads the page
     * - On error, shows AJAX error alert
     */
jQuery(document).ready(function (jQuery) {
    let frmntrPluginSlug = null;

    // Open confirmation popup
    jQuery(document).on("click", ".frmntr-deactivate-plugin", function (e) {
        e.preventDefault();
        console.log("Deactivate button clicked");
        frmntrPluginSlug = jQuery(this).data("plugin");
        jQuery("#frmntr-confirm-dective-popup").removeClass("d-none");
    });

    // Cancel popup
    jQuery("#frmntr-dective-popup-cancel").on("click", function () {
        frmntrPluginSlug = null;
        jQuery("#frmntr-confirm-dective-popup").addClass("d-none");
    });

    // Confirm deactivate
    jQuery("#frmntr-deactive-popup-confirm").on("click", function () {
        if (!frmntrPluginSlug) return;
        var $btn = jQuery('.frmntr-deactivate-plugin');


        /*  Remove loading from ALL loaders first */
        jQuery(".loading-sign-deactive").removeClass("loading");

        /*  Add loading only to the current one */
        /*$btn.siblings(".loading-sign-deactive").first().addClass("loading");*/
        jQuery('.frmntr-deactivate-plugin[data-download="' + frmntrPluginSlug + '"]')
            .siblings(".loading-sign-deactive")
            .addClass("loading");


        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            data: {
                action: "formntr_deactivate_plugin",
                plugin_slug: frmntrPluginSlug,
                security: jQuery("#frmntr-gs-ajax-nonce").val(),
            },
            success: function (response) {
                if (response.success) {
                    jQuery(".success-message")
                        .text(response.data || "Integration deactivated successfully!")
                        .fadeIn()
                        .delay(3000)
                        .fadeOut();
                    $btn.removeClass("loading");
                    location.reload();
                }
            },
            error: function () {
                console.log("AJAX error while deactivating plugin");
                $btn.removeClass("loading");
            },
        });

        // Close popup
        jQuery("#frmntr-confirm-dective-popup").addClass("d-none");
        frmntrPluginSlug = null;
    });

    jQuery(document).on("click", "#frmntr-popup-active", function (e) {
        e.preventDefault();
        e.stopPropagation();

        jQuery("#frmntr-confirm-active-popup").addClass("d-none");
    });
});


/**
     * Handle plugin activation button click via AJAX.
     *
     * - Shows loading spinner
     * - Sends plugin slug to server for activation
     * - On success, updates button to "Activated" and reloads page
     * - On error or failure, resets button and removes loading state
     */

    jQuery(document).on("click", ".frmntr-activate-plugin-btn", function () {
        var button = jQuery(this);
        var pluginSlug = button.data("plugin");
        var loaderSpan = button.siblings(".loading-sign-active");

        loaderSpan.addClass("loading");
        button.prop("disabled", true);

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            data: {
                action: "gs_ff_activate_plugin",
                plugin_slug: pluginSlug,
                security: jQuery("#frmntr-gs-ajax-nonce").val(),
            },

            success: function (response) {
                loaderSpan.removeClass("loading");

                if (response.success) {

                    location.reload();
                } else {

                    jQuery(".popup-actions-active-msg").text(
                        response.data.message ||
                        "You do not have permission to activate this plugin.",
                    );

                    jQuery("#frmntr-confirm-active-popup").removeClass("d-none");

                    button.prop("disabled", false);
                }
            },

            error: function () {
                loaderSpan.removeClass("loading");

                jQuery(".popup-actions-active-msg").text(
                    "Something went wrong. Please try again.",
                );

                jQuery("#frmntr-confirm-active-popup").removeClass("d-none");

                button.prop("disabled", false);
            },
        });
    });


     /**
     * Handle plugin install button click via AJAX.
     *
     * - Shows loading spinner
     * - Sends plugin slug and download URL to server
     * - On success, hides install button and shows activate button
     * - On error or failure, resets button state
     */

    jQuery(".frmntr-install-plugin-btn").on("click", function () {
        var button = jQuery(this);
        var pluginSlug = button.data("plugin");
        var downloadUrl = button.data("download");
        var loaderSpan = button
            .closest(".button-bar")
            .find(".loading-sign-install");

        loaderSpan.addClass("loading");
        button.prop("disabled", true);

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            data: {
                action: "gs_ff_install_plugin",
                plugin_slug: pluginSlug,
                download_url: downloadUrl,
                security: jQuery("#frmntr-gs-ajax-nonce").val(),
            },

            success: function (response) {
                loaderSpan.removeClass("loading");

                if (response.success) {

                    button.hide();

                    button
                        .closest(".button-bar")
                        .find(".frmntr-activate-plugin-btn")
                        .show();
                } else {

                    jQuery(".popup-actions-active-msg").text(
                        response.data.message ||
                        "You do not have permission to install this plugin.",
                    );

                    jQuery("#frmntr-confirm-active-popup").removeClass("d-none");

                    button.prop("disabled", false);
                }
            },

            error: function () {
                loaderSpan.removeClass("loading");

                jQuery(".popup-actions-active-msg").text(
                    "Something went wrong. Please try again.",
                );

                jQuery("#frmntr-confirm-active-popup").removeClass("d-none");

                button.prop("disabled", false);
            },
        });
    });




document.querySelectorAll(".market-tab").forEach((tab) => {
    tab.addEventListener("click", function () {
        let filter = this.dataset.filter;

        document
            .querySelectorAll(".market-tab")
            .forEach((t) => t.classList.remove("active"));

        this.classList.add("active");

        document.querySelectorAll(".gsc-market-item").forEach((card) => {
            if (filter === "all") {
                card.style.display = "block";
            } else {
                card.style.display = card.classList.contains(filter) ? "block" : "none";
            }
        });
    });
});