let activeFilter = null;
const popoverLayer = document.querySelector('[data-popover-layer]');

document.addEventListener('click', function (event) {
  document.querySelectorAll('[data-filter].open').forEach(function (filter) {
    const popover = filter._activePopover;
    if (!filter.contains(event.target) && !(popover && popover.contains(event.target))) {
      closeFilter(filter);
    }
  });
});

function closeFilter(filter) {
  const popover = filter._activePopover || filter.querySelector('.crm-popover');
  filter.classList.remove('open');
  if (popover) {
    popover.style.removeProperty('top');
    popover.style.removeProperty('left');
    popover.style.removeProperty('width');
    popover.style.removeProperty('display');
    if (filter._popoverParent && popover.parentElement !== filter._popoverParent) {
      filter._popoverParent.appendChild(popover);
    }
  }
  filter._activePopover = null;
  filter._popoverParent = null;
  const card = filter.closest('.card');
  if (card) {
    card.classList.remove('filter-active');
  }
  if (activeFilter === filter) {
    activeFilter = null;
  }
  if (popoverLayer && !document.querySelector('[data-filter].open')) {
    popoverLayer.classList.remove('active');
  }
}

function positionFilterPopover(filter) {
  const button = filter.querySelector('[data-filter-toggle]');
  const popover = filter._activePopover || filter.querySelector('.crm-popover');
  if (!button || !popover) {
    return;
  }

  const gap = 10;
  const viewportPadding = 12;
  const rect = button.getBoundingClientRect();
  const popoverWidth = Math.min(326, window.innerWidth - viewportPadding * 2);
  popover.style.width = popoverWidth + 'px';

  let left = rect.right - popoverWidth;
  if (popover.classList.contains('popover-left')) {
    left = rect.left;
  }
  left = Math.max(viewportPadding, Math.min(left, window.innerWidth - popoverWidth - viewportPadding));

  let top = rect.bottom + gap;
  const availableBelow = window.innerHeight - top - viewportPadding;
  const naturalHeight = Math.min(popover.scrollHeight || 0, 420);
  if (availableBelow < 180 && rect.top > availableBelow) {
    top = Math.max(viewportPadding, rect.top - naturalHeight - gap);
  }

  popover.style.left = left + 'px';
  popover.style.top = top + 'px';
}

document.querySelectorAll('[data-filter-toggle]').forEach(function (button) {
  button.addEventListener('click', function (event) {
    event.stopPropagation();
    const filter = button.closest('[data-filter]');

    if (filter.classList.contains('open')) {
      closeFilter(filter);
      return;
    }

    document.querySelectorAll('[data-filter].open').forEach(closeFilter);

    const popover = filter.querySelector('.crm-popover');
    if (popover && popoverLayer) {
      filter._popoverParent = popover.parentElement;
      filter._activePopover = popover;
      popoverLayer.appendChild(popover);
      popoverLayer.classList.add('active');
      popover.style.display = 'block';
    }

    filter.classList.add('open');
    activeFilter = filter;
    const card = filter.closest('.card');
    if (card) {
      card.classList.add('filter-active');
    }
    positionFilterPopover(filter);
  });
});

document.querySelectorAll('[data-filter-apply]').forEach(function (button) {
  button.addEventListener('click', function () {
    var filter = activeFilter;
    if (!filter) {
      return;
    }
    var label = filter.querySelector('[data-filter-label]');
    var popover = filter._activePopover || filter.querySelector('.crm-popover');
    var checkedBoxes = Array.from(popover.querySelectorAll('input[type="checkbox"]:checked'))
      .map(function (input) { return input.value; })
      .filter(function (value) { return value !== 'all'; });
    var checkedRadio = popover.querySelector('input[type="radio"]:checked');
    var hasCheckboxes = popover.querySelectorAll('input[type="checkbox"]').length > 0;

    if (hasCheckboxes) {
      if (checkedBoxes.length === 0) {
        label.textContent = 'Campaign';
        filter.querySelector('[data-filter-toggle]').classList.remove('applied');
      } else if (checkedBoxes.length === 1) {
        var checkedInput = popover.querySelector('input[type="checkbox"][value="' + checkedBoxes[0] + '"]');
        var choiceLabel = checkedInput?.closest('.crm-choice')?.querySelector('span:last-child');
        label.textContent = choiceLabel ? choiceLabel.textContent.trim() : checkedBoxes[0];
        filter.querySelector('[data-filter-toggle]').classList.add('applied');
      } else {
        label.textContent = checkedBoxes.length + ' selected';
        filter.querySelector('[data-filter-toggle]').classList.add('applied');
      }
    } else if (checkedRadio) {
      label.textContent = checkedRadio.closest('.crm-choice')?.textContent.trim() || checkedRadio.value;
      filter.querySelector('[data-filter-toggle]').classList.add('applied');
    }

    closeFilter(filter);
  });
});

window.addEventListener('resize', function () {
  document.querySelectorAll('[data-filter].open').forEach(positionFilterPopover);
});

window.addEventListener('scroll', function () {
  document.querySelectorAll('[data-filter].open').forEach(positionFilterPopover);
}, true);

document.querySelectorAll('.crm-popover').forEach(function (popover) {
  popover.addEventListener('click', function (event) {
    event.stopPropagation();
  });
});

document.querySelectorAll('.crm-search').forEach(function (input) {
  input.addEventListener('input', function () {
    const query = input.value.trim().toLowerCase();
    const body = input.closest('.crm-popover-body');
    body.querySelectorAll('.crm-choice').forEach(function (choice) {
      choice.style.display = choice.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
    });
  });
});

// Note: Campaign "Select all" checkbox is handled in crm-core.js renderFilterChoices()

const pinModal = document.querySelector('[data-pin-modal]');
const openPinModal = document.querySelector('[data-pin-open]');
const closePinButtons = document.querySelectorAll('[data-pin-close]');
const uploadModal = document.querySelector('[data-upload-modal]');
const openUploadModal = document.querySelector('[data-upload-open]');
const closeUploadButtons = document.querySelectorAll('[data-upload-close]');
const campaignModal = document.querySelector('[data-campaign-modal]');
const openCampaignModal = document.querySelector('[data-campaign-open]');
const closeCampaignButtons = document.querySelectorAll('[data-campaign-close]');
const conditionsModal = document.querySelector('[data-conditions-modal]');
const closeConditionsButtons = document.querySelectorAll('[data-conditions-close]');

function bindModal(modal, openButton, closeButtons) {
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

bindModal(uploadModal, openUploadModal, closeUploadButtons);
bindModal(campaignModal, openCampaignModal, closeCampaignButtons);
bindModal(conditionsModal, null, closeConditionsButtons);

if (pinModal && openPinModal) {
  openPinModal.addEventListener('click', function () {
    pinModal.classList.add('open');
  });
}

closePinButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    pinModal.classList.remove('open');
  });
});

if (pinModal) {
  pinModal.addEventListener('click', function (event) {
    if (event.target === pinModal) {
      pinModal.classList.remove('open');
    }
  });
}

document.querySelectorAll('.pin-option').forEach(function (option) {
  option.addEventListener('click', function () {
    document.querySelectorAll('.pin-option').forEach(function (item) {
      item.classList.remove('selected');
    });
    option.classList.add('selected');
  });
});

const pinSearch = document.querySelector('[data-pin-search]');
if (pinSearch) {
  pinSearch.addEventListener('input', function () {
    const query = pinSearch.value.trim().toLowerCase();
    document.querySelectorAll('.pin-option').forEach(function (option) {
      option.style.display = option.textContent.toLowerCase().includes(query) ? 'block' : 'none';
    });
  });
}
