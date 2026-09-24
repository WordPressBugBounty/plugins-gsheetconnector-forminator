/**
 * Copies formatted system information from the `.info-container` element to the clipboard.
 *
 * - Extracts section titles (h3) and table data (td) as key-value pairs.
 * - Formats output with bold section headers and line breaks.
 * - Shows a success message for 3 seconds after copying.
 */

function copySystemInfo() {
  const systemInfoContainer = document.querySelector(".info-container");
  const systemInfoElements = systemInfoContainer.querySelectorAll(
    ".info-content h3, .info-content td"
  );
  let systemInfoText = "";
  let currentRow = "";

  systemInfoElements.forEach((element) => {
    if (element.innerText) {
      const tagName = element.tagName.toLowerCase();

      // Handle section headers (h3 tags)
      if (tagName === "h3") {
        if (currentRow !== "") {
          systemInfoText += currentRow.trim() + "\n\n"; // Add two newlines between sections
        }
        systemInfoText += `**${element.innerText}**\n\n`; // Make h3 bold and add extra space after it
        currentRow = "";
      }

      // Handle table data (td tags)
      else if (tagName === "td") {
        const labelElement = element.previousElementSibling;

        // Check if label element exists and has text
        if (labelElement && labelElement.innerText) {
          let label = labelElement.innerText.trim(); // Keep the label as is (no underscores)
          currentRow += `${label}: ${element.innerText.trim()}\n`; // Format the row as key-value pair
        }
      }
    }
  });

  // Add the last row to the final text
  systemInfoText += currentRow.trim();

  // Copy the formatted text to the clipboard
  navigator.clipboard
    .writeText(systemInfoText.trim())
    .then(() => {
      const messageElement = document.createElement("div");
      messageElement.textContent = "System info copied!";
      messageElement.classList.add("copy-success-message");
      document.body.appendChild(messageElement);

      setTimeout(() => {
        messageElement.remove();
      }, 3000);
    })
    .catch((error) => {
      console.error("Unable to copy system info:", error);
    });
}

/**
 * Toggles visibility of different system info sections on button click.
 *
 * Each button (e.g., #show-info-button) toggles its corresponding container
 * (e.g., #info-container) using jQuery's slideToggle().
 */

jQuery(document).ready(function ($) {
  $("#show-info-button").click(function () {
    $("#info-container").slideToggle();
  });
  $("#show-wordpress-info-button").click(function () {
    $("#wordpress-info-container").slideToggle();
  });
  $("#show-Drop-info-button").click(function () {
    $("#Drop-info-container").slideToggle();
  });
  $("#show-active-info-button").click(function () {
    $("#active-info-container").slideToggle();
  });
  $("#show-netplug-info-button").click(function () {
    $("#netplug-info-container").slideToggle();
  });
  $("#show-acplug-info-button").click(function () {
    $("#acplug-info-container").slideToggle();
  });
  $("#show-server-info-button").click(function () {
    $("#server-info-container").slideToggle();
  });
  $("#show-database-info-button").click(function () {
    $("#database-info-container").slideToggle();
  });
  $("#show-wrcons-info-button").click(function () {
    $("#wrcons-info-container").slideToggle();
  });
  $("#show-ftps-info-button").click(function () {
    $("#ftps-info-container").slideToggle();
  });
});

/**
 * Copies the error log text from the textarea to the clipboard.
 * Shows a temporary "Copied" message on success, otherwise alerts an error.
 */

function copyErrorLog() {
  // Select the textarea containing the error log
  var textarea = document.querySelector(".errorlog");
  // Select the message div
  var copyMessage = document.querySelector(".copy-message");

  // Check if the textarea and message div exist
  if (textarea && copyMessage) {
    // Select the text within the textarea
    textarea.select();

    try {
      // Attempt to copy the selected text to the clipboard
      document.execCommand("copy");
      // Display the "Copied" message
      copyMessage.style.display = "block";

      // Hide the message after a few seconds (e.g., 3 seconds)
      setTimeout(function () {
        copyMessage.style.display = "none";
      }, 3000);
    } catch (err) {
      console.error("Unable to copy error log: " + err);
      alert("Error log copy failed. Please copy it manually.");
    }

    // Deselect the text
    textarea.blur();
  } else {
    alert("Error log textarea or copy message not found.");
  }
}

/**
 * Attaches click event to the copy button to trigger the error log copy function.
 */

document.addEventListener("DOMContentLoaded", function () {
  var copyButton = document.querySelector(".copy");

  if (copyButton) {
    copyButton.addEventListener("click", function (event) {
      event.preventDefault();
      copyErrorLog();
    });
  }
});

/**
 * Clears the content of the error log textarea.
 */

function clearErrorLog() {
  var textarea = document.querySelector(".errorlog");

  if (textarea) {
    // Clear the textarea content
    textarea.value = "";
  }
}

/**
 * Adds click event listener to the "Clear" button to clear the error log.
 */

document.addEventListener("DOMContentLoaded", function () {
  var clearButton = document.querySelector(".clear");

  if (clearButton) {
    clearButton.addEventListener("click", function (event) {
      event.preventDefault();
      clearErrorLog();
    });
  }
});

/**
 * JS for Integration page - Handles copying logs content to clipboard.
 */

jQuery(document).ready(function ($) {
  $("#copy-logs-btn").on("click", function () {
    // Get the text inside the logs container
    var logsContent = $("#logs-content").text();

    // Create a temporary textarea element to hold the text
    var tempTextarea = $("<textarea>");
    $("body").append(tempTextarea);
    tempTextarea.val(logsContent).select();

    // Execute the copy command
    document.execCommand("copy");

    // Remove the temporary element after copying
    tempTextarea.remove();

    // Notify the user that the logs have been copied
    alert("Logs have been copied to the clipboard.");
  });
});

/**
 * JS for Feed page (gs-formntr-google-sheet.php) - Search filter for form list.
 */

document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("form-search");
  const formList = document.getElementById("form-list");

  // Bail out quietly if either element isn't on this page
  if (!searchInput || !formList) {
    return;
  }

  const forms = formList.getElementsByClassName("add-feed-row");

  searchInput.addEventListener("input", function () {
    const filter = searchInput.value.toLowerCase();
    Array.from(forms).forEach(function (form) {
      const nameEl = form.querySelector(".form-name");
      if (!nameEl) return; // guard in case a row is missing .form-name too

      const formName = nameEl.textContent.toLowerCase();
      form.style.display = formName.includes(filter) ? "" : "none";
    });
  });
});


/** Copy debug log  */
jQuery(document).ready(function ($) {
  $("#formntr-copy-logs").on("click", function (e) {
    e.preventDefault();

    var rows = $("table tbody tr");
    var copyText = "";

    if (!rows.length) {
      console.log("No error logs found.");
      return;
    }

    rows.each(function () {
      var cols = $(this).find("td");

      if (cols.length >= 4) {
        copyText += $(cols[0]).text().trim() + "\n"; /* Date */
        copyText += $(cols[1]).text().trim() + "\n"; /* Type */
        copyText += $(cols[2]).text().trim() + "\n"; /* Message */
        copyText += $(cols[3]).text().trim() + "\n"; /* File */
        copyText += "----------------------------------------\n\n";
      }
    });

    /*  Temporary textarea copy */
    var tempTextarea = $("<textarea>");
    $("body").append(tempTextarea);
    tempTextarea.val(copyText).select();
    document.execCommand("copy");
    tempTextarea.remove();

    /*  Show success message */
    var $msg = $(".gsc-copy-msg");

    $msg.text("Copied successfully").removeClass("d-none");

    setTimeout(function () {
      $msg.addClass("d-none");
    }, 3000);
  });
});