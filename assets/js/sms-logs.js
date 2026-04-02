/**
 * Handle SMS Log Modal
 */
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("response-modal");
  const content = document.getElementById("modal-response-content");

  document.querySelectorAll(".view-response").forEach((button) => {
    button.addEventListener("click", function () {
      if (content && modal) {
        content.textContent = this.dataset.response;
        modal.style.display = "block";
      }
    });
  });

  const closeBtn = document.getElementById("close-modal");
  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      modal.style.display = "none";
    });
  }
});
