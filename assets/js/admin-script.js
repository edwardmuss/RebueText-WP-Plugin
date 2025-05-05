jQuery(document).ready(function ($) {
  // Initialize Bootstrap components
  if (typeof bootstrap !== "undefined") {
    var toastElList = [].slice.call(document.querySelectorAll(".toast"));
    var toastList = toastElList.map(function (toastEl) {
      return new bootstrap.Toast(toastEl);
    });
  }

  // Save settings form submission
  $("#rebuetext-settings-form").on("submit", function (e) {
    e.preventDefault();

    let formData = $(this).serialize();

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "save_rebuetext_settings",
        data: formData,
        security: $("#rebuetext_ajax_nonce").val(),
      },
      beforeSend: function () {
        $("#save-settings-btn").text("Saving...").prop("disabled", true);
      },
      success: function (response) {
        if (response.success) {
          $(".status-message").html(
            '<div class="notice notice-success is-dismissible"><p>' +
              response.data +
              "</p></div>"
          );
          showToast(response.data, "bg-success");
        } else {
          $(".status-message").html(
            '<div class="notice notice-error is-dismissible"><p>' +
              response.data +
              "</p></div>"
          );
          showToast(response.data, "bg-danger");
        }
      },
      complete: function () {
        $("#save-settings-btn").text("Save Settings").prop("disabled", false);
      },
    });
  });

  // Merge tags click handler (copy to clipboard)
  $(".merge-tag").on("click", function () {
    let tag = $(this).attr("data-tag");
    copyToClipboard(tag);
    $(this).addClass("copied");
    setTimeout(() => $(this).removeClass("copied"), 1000);
    showToast("Copied: " + tag, "bg-success");
  });

  // Copy to clipboard function
  function copyToClipboard(text) {
    let tempInput = $("<input>");
    $("body").append(tempInput);
    tempInput.val(text).select();
    document.execCommand("copy");
    tempInput.remove();
  }

  // Toast notification function
  function showToast(message, bgClass = "bg-success") {
    let toastContainer = $(".toast-container");
    let toastId = "toast-" + Date.now();

    let toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

    toastContainer.append(toastHtml);

    // Initialize and show the toast
    if (typeof bootstrap !== "undefined") {
      var toastEl = document.getElementById(toastId);
      var toast = new bootstrap.Toast(toastEl);
      toast.show();

      // Remove toast after it's hidden
      $(toastEl).on("hidden.bs.toast", function () {
        $(this).remove();
      });
    } else {
      // Fallback if Bootstrap JS isn't loaded
      setTimeout(() => $("#" + toastId).remove(), 3000);
    }
  }

  document.querySelectorAll(".status-switch").forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      const iconSpan =
        this.closest(".form-check").querySelector(".status-icon");
      if (this.checked) {
        iconSpan.textContent = "✅";
      } else {
        iconSpan.textContent = "❌";
      }
    });
  });

  // Enable Bootstrap tooltips
  document.addEventListener("DOMContentLoaded", function () {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
  // Toggle All Switches
  // Check if the toggle button exists
  const toggleAllButton = document.getElementById("toggle-all-statuses");

  if (toggleAllButton) {
    // Toggle All Switches
    toggleAllButton.addEventListener("click", function () {
      const allSwitches = document.querySelectorAll(".status-switch");
      const isAnyUnchecked = Array.from(allSwitches).some(
        (input) => !input.checked
      );

      allSwitches.forEach((input) => {
        input.checked = isAnyUnchecked;
        const icon = input.closest(".form-check").querySelector(".status-icon");
        // Check if the icon element exists before trying to set textContent
        if (icon) {
          icon.textContent = isAnyUnchecked ? "✅" : "❌";
        }
      });

      this.textContent = isAnyUnchecked ? "Deselect All" : "Select All";
    });
  } else {
    console.warn("Element with id 'toggle-all-statuses' not found.");
  }

  function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }

  if (getQueryParam("rebuetext_sms_tab") === "1") {
    setTimeout(function () {
      const tabLink = document.querySelector(
        '#contact-form-editor-tabs a[href="#rebuetext-sms-panel"]'
      );
      if (tabLink) {
        tabLink.click();
      }
    }, 300);
  }
});
