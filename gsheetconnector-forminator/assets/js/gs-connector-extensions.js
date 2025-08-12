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

  /**
   * Handles plugin deactivation button click via AJAX.
   *
   * Sends plugin slug for deactivation and reloads page or shows error based on response.
   *
   * @since 1.0.15
   */

  jQuery(".gs-ff-deactivate-plugin").on("click", function () {
    var pluginSlug = jQuery(this).data("plugin");
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json", // Ensure JSON response
      data: {
        action: "gs_ff_deactivate_plugin",
        plugin_slug: pluginSlug,
        security: jQuery("#frmntr-gs-ajax-nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          alert(response.data); // Display success message
          location.reload();
        }
      },
      error: function (xhr, status, error) {
        alert("AJAX error: " + error);
      },
    });
  });
});
