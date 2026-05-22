const uploadModal = document.querySelector('[data-upload-modal]');
const openUploadModal = document.querySelector('[data-upload-open]');
const closeUploadButtons = document.querySelectorAll('[data-upload-close]');
const leadModal = document.querySelector('[data-lead-modal]');
const openLeadModal = document.querySelector('[data-lead-open]');
const closeLeadButtons = document.querySelectorAll('[data-lead-close]');

function bindContactModal(modal, openButton, closeButtons) {
  if (modal && openButton) {
    openButton.addEventListener('click', function () {
      modal.classList.add('open');
    });
  }

  closeButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      modal.classList.remove('open');
    });
  });

  if (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === modal) {
        modal.classList.remove('open');
      }
    });
  }
}

bindContactModal(uploadModal, openUploadModal, closeUploadButtons);
bindContactModal(leadModal, openLeadModal, closeLeadButtons);
