const propertyModal = document.querySelector('[data-property-modal]');
const openPropertyModal = document.querySelector('[data-property-open]');
const closePropertyButtons = document.querySelectorAll('[data-property-close]');
const propertyNameInput = document.querySelector('.ccp-input');
const propertyCounter = document.querySelector('.ccp-counter');

if (openPropertyModal && propertyModal) {
  openPropertyModal.addEventListener('click', function () {
    propertyModal.classList.add('open');
  });
}

closePropertyButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    propertyModal.classList.remove('open');
  });
});

if (propertyModal) {
  propertyModal.addEventListener('click', function (event) {
    if (event.target === propertyModal) {
      propertyModal.classList.remove('open');
    }
  });
}

if (propertyNameInput && propertyCounter) {
  propertyNameInput.addEventListener('input', function () {
    propertyCounter.textContent = propertyNameInput.value.length + '/60';
  });
}
