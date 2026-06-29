jQuery(document).ready(function ($) {
  // Build dropdown list items from localized PHP tags
    // Smart Tag Generator: Detects if we are in CF7 or WooCommerce
    function getContextualTagsDropdown() {
        let items = '';
        let isCF7 = $('#cf7si-sms-sortables').length > 0;

        if (isCF7) {
            // We are on CF7: Scrape the available mail-tags dynamically from the DOM
            let addedTags = [];
            $('.mailtag.code').each(function() {
                let tag = $(this).text(); // e.g., [your-name]
                if (!addedTags.includes(tag)) { // Prevent duplicates
                    items += `<li><a class="dropdown-item merge-tag-insert text-primary" href="#" data-tag="${tag}" style="font-size: 13px; font-family: monospace;">${tag}</a></li>`;
                    addedTags.push(tag);
                }
            });
        } else {
            // We are on WooCommerce: Use localized PHP tags
            if (typeof rebuetext_globals !== 'undefined' && rebuetext_globals.mergeTags) {
                rebuetext_globals.mergeTags.forEach(tag => {
                    items += `<li><a class="dropdown-item merge-tag-insert text-primary" href="#" data-tag="{${tag}}" style="font-size: 13px; font-family: monospace;">{${tag}}</a></li>`;
                });
            }
        }
        return items;
    }

    // Populate the SMS text area dropdowns on page load
    $('.sms-tag-dropdown').html(getContextualTagsDropdown());

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
        action: "rebuetext_save_settings",
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

  // Universal Click-to-Insert Handler
    $(document).on("click", ".merge-tag-insert", function (e) {
        e.preventDefault();
        let tag = $(this).attr("data-tag");
        
        let $inputGroup = $(this).closest('.input-group');
        let $targetInput = null;
        
        if ($inputGroup.length > 0) {
            // It's a WhatsApp input group
            $targetInput = $inputGroup.find('input');
        } else {
            // It's an SMS textarea
            $targetInput = $(this).closest('.channel-config').find('textarea');
        }

        if ($targetInput && $targetInput.length > 0) {
            let inputEl = $targetInput[0];
            let startPos = inputEl.selectionStart || 0;
            let endPos = inputEl.selectionEnd || 0;
            let currentText = $(inputEl).val() || '';

            $(inputEl).val(
                currentText.substring(0, startPos) + tag + currentText.substring(endPos, currentText.length)
            );

            inputEl.selectionStart = inputEl.selectionEnd = startPos + tag.length;
            $(inputEl).focus();
            
            showToast("Inserted: " + tag, "bg-success");
        }
    });

  // Merge tags click handler (copy to clipboard)
  // $(".merge-tag").on("click", function () {
  //   let tag = $(this).attr("data-tag");
  //   copyToClipboard(tag);
  //   $(this).addClass("copied");
  //   setTimeout(() => $(this).removeClass("copied"), 1000);
  //   showToast("Copied: " + tag, "bg-success");
  // });

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

  // Toggle Channel Visibility
    function toggleChannels() {
        $('.channel-toggle').each(function() {
            let targetId = $(this).data('target');
            if ($(this).is(':checked')) {
                $('#' + targetId).slideDown(200);
            } else {
                $('#' + targetId).slideUp(200);
            }
        });
    }

    // Run on load
    toggleChannels();

    // Run on click
    $(document).on('change', '.channel-toggle', function() {
        toggleChannels();
    });

    // Parse WhatsApp Templates and Generate Variables
    function renderWaVariables(selectElement) {
        let val = $(selectElement).val();
        let containerId = $(selectElement).data('container');
        let $container = $('#' + containerId);
        
        $container.empty(); // Clear old variables
        
        if (!val) return;

        let parts = val.split('|');
        let tplName = parts[0];
        let tplLang = parts[1];

        let templatesArray = (typeof rebuetext_globals !== 'undefined' && rebuetext_globals.waTemplates) ? rebuetext_globals.waTemplates : [];
        let template = templatesArray.find(t => t.name === tplName && t.language === tplLang);
        if (!template || !template.components) return;

        let components = [];
        try {
            components = typeof template.components === 'string' ? JSON.parse(template.components) : template.components;
        } catch (e) {
            console.error("Could not parse template components", e);
            return;
        }

        let savedVars = [];
        try {
            savedVars = JSON.parse($(selectElement).attr('data-saved-vars') || '[]');
        } catch (e) {}

        let varCount = 0;
        let previewText = ''; // Variable to hold the preview text

        // Scan the components array for {{x}} variables in the BODY
        components.forEach(function(comp) {
            if (comp.type && comp.type.toUpperCase() === 'BODY' && comp.text) {
                previewText = comp.text; // <--- NEW: Capture the text
                
                let matches = comp.text.match(/\{\{(\d+)\}\}/g);
                if (matches) {
                    let uniqueVars = [...new Set(matches)];
                    varCount = uniqueVars.length;
                }
            }
        });

        // --- Render the Preview Box ---
        if (previewText) {
            // Highlight the {{number}} tags in blue so they stand out
            let highlightedText = previewText.replace(/\{\{(\d+)\}\}/g, '<strong class="text-primary">{{$1}}</strong>');
            
            $container.append(`
                <div class="col-12 mb-3">
                    <div class="p-2 border rounded text-muted" style="background-color: #f8f9fa; font-size: 13px; white-space: pre-wrap;">
                        <i class="dashicons dashicons-format-chat" style="font-size: 16px; margin-top: 3px;"></i> <strong>Template Preview:</strong><br>
                        ${highlightedText}
                    </div>
                </div>
            `);
        }

        if (varCount > 0) {
            $container.append('<div class="col-12"><small class="text-muted d-block mb-2">Map Tags to Template Variables:</small></div>');
            
            for (let i = 1; i <= varCount; i++) {
                // If it has a specific input name data attribute (like CF7), use it. Otherwise, use WooCommerce logic.
                let inputName = '';
                if ($(selectElement).attr('data-input-name')) {
                    inputName = $(selectElement).attr('data-input-name');
                } else {
                    let namePrefix = containerId.includes('admin') ? 'admin' : 'customer';
                    let statusKey = containerId.replace('wa-vars-', '').replace('-' + namePrefix, ''); 
                    inputName = `rebuetext_wa_mappings[${statusKey}][${namePrefix}][vars][]`;
                }

                let inputValue = savedVars[i - 1] || '';

                let html = `
                    <div class="col-md-6 mb-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white fw-bold text-primary">{{${i}}}</span>
                            <input type="text" name="${inputName}" class="form-control" value="${inputValue}" placeholder="Static text or tag...">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Insert Tag">
                                <span class="dashicons dashicons-tag" style="line-height: 1.5; font-size: 14px; width: 14px; height: 14px;"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 250px; overflow-y: auto;">
                                ${getContextualTagsDropdown()}
                            </ul>
                        </div>
                    </div>
                `;
                
                $container.append(html);
            }
        } else {
            $container.append('<div class="col-12"><small class="text-success"><i class="dashicons dashicons-yes-alt"></i> This template requires no variables.</small></div>');
        }
    }

    // Run on load for all selectors
    $('.wa-template-selector').each(function() {
        renderWaVariables(this);
    });

    // Run on change
    $(document).on('change', '.wa-template-selector', function() {
        // Clear saved vars attr so it doesn't try to fill a new template with old data
        $(this).attr('data-saved-vars', '[]');
        renderWaVariables(this);
    });

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
