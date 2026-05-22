<?php $__env->startSection('title', 'Calling CRM Contact'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.callingcrm.partials.ui-polish', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas"><main class="main-wrapper">

  <!-- PAGE HEADER -->
  <div class="page-header" style="justify-content: space-between; align-items: center; border-bottom: none; padding-bottom: 0;">
    <div style="display: flex; align-items: center; gap: 12px;">
      <button class="btn-outline" style="padding: 6px; border-radius: 50%; border: none;" onclick="history.back()">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      </button>
      <h1 class="page-title" style="margin: 0; font-family: 'DM Sans', sans-serif;">Contact Search</h1>
    </div>
    <div class="header-actions">
      <!-- Custom Contact Properties -->
      <button class="btn-outline" type="button" onclick="window.location='<?php echo e(route('admin_panel.admin.callingcrm.contact.properties')); ?>'">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Custom Contact Properties
      </button>

      <!-- Upload Excel Sheet -->
      <button class="btn-outline" type="button" data-upload-open>
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload Contacts
      </button>
    </div>
  </div>

  <div style="padding: 0 28px;">
    <!-- FILTER BAR -->
    <div class="filter-bar">
      <button class="filter-btn">
        Integration <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <button class="filter-btn">
        FileUpload <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <button class="filter-btn">
        Others <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <button class="filter-btn">
        Workflows <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <button class="filter-export-btn" title="Export">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      </button>
    </div>

    <!-- TABLE -->
    <div class="table-card">
      <div class="table-responsive">
        <table class="results-table">
          <thead>
            <tr>
              <th style="width: 40px; text-align: center;"><input type="checkbox"></th>
              <th>Name</th>
              <th>Number</th>
              <th>Campaign</th>
              <th>Pipeline</th>
              <th>Creation Date</th>
              <th>Updated at</th>
              <th>Stage</th>
              <th>User Assigned</th>
              <th style="width: 50px;">Action</th>
            </tr>
          </thead>
          <tbody data-contact-search-body>
            <tr>
              <td colspan="10" style="text-align: center; padding: 30px; color: var(--text-muted);">
                Loading leads...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- PAGINATION -->
      <div class="pagination-footer">
        <div>
          Items per page: 
          <select class="pagination-select" data-contact-per-page>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50" selected>50</option>
            <option value="100">100</option>
          </select>
        </div>
        <div data-contact-pagination-info>
          1 - 50 of 0
        </div>
        <div class="pagination-arrows">
          <button data-contact-prev>&lt;</button>
          <button data-contact-next>&gt;</button>
        </div>
      </div>
    </div>
  </div>

  <div id="contactMenuLayer" class="contact-menu-layer">
    <button class="contact-menu-item" data-action="open">OPEN</button>
    <button class="contact-menu-item" data-action="history">VIEW DISPOSE HISTORY</button>
    <button class="contact-menu-item" data-action="edit">EDIT</button>
    <button class="contact-menu-item" data-action="delete">DELETE</button>
  </div>

  <div class="crm-modal-backdrop" data-upload-modal>
    <div class="crm-modal" role="dialog" aria-modal="true" aria-labelledby="contactUploadExcelTitle">
      <div class="crm-modal-head">
        <div class="modal-title-wrap">
          <div class="crm-modal-title" id="contactUploadExcelTitle">Upload Excel Sheet</div>
          <div class="learn-more-pill">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3V9z"/></svg>
            Learn More
          </div>
        </div>
        <button type="button" class="crm-modal-close" data-upload-close aria-label="Close">&times;</button>
      </div>
      <div class="upload-dropzone">
        <div>
          <div class="upload-icon">↑</div>
          <div class="upload-drop-text">Drag and drop file</div>
          <button type="button" class="upload-browse-btn">Browse</button>
          <div class="upload-format">Supported formats are .csv, .xls, .xlsx</div>
        </div>
      </div>
      <div class="upload-meta-row">
        <div>Max leads: 25,000 at a time, file size limit: 3MB.</div>
        <a href="#" class="sample-link">Download Sample file</a>
      </div>
      <div class="upload-note">No specific column order needed! Just include crucial details like name and number in the file.</div>
    </div>
  </div>

  <div class="crm-modal-backdrop" data-lead-modal>
    <div class="crm-modal lead-modal" role="dialog" aria-modal="true" aria-labelledby="addLeadTitle">
      <div class="crm-modal-head">
        <div class="modal-title-wrap">
          <div class="crm-modal-title" id="addLeadTitle">Add Lead</div>
          <div class="learn-more-pill">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3V9z"/></svg>
            Learn More
          </div>
        </div>
        <button type="button" class="crm-modal-close" data-lead-close aria-label="Close">&times;</button>
      </div>
      <div class="lead-form">
        <input class="lead-input" type="text" placeholder="Contact Name">
        <input class="lead-input" type="tel" placeholder="Contact Number *">
        <input class="lead-input" type="email" placeholder="Email">
        <div class="lead-select">
          <span>Campaign *</span>
          <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 7.5 10 12l4.5-4.5H5.5z"/></svg>
        </div>
        <div class="lead-modal-actions">
          <button type="button" class="lead-submit-btn" data-lead-close>Submit</button>
        </div>
      </div>
    </div>
  </div>

</main></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
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
</script>
<?php $__env->stopPush(); ?>



<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/crm-core.js')); ?>"></script>
<script type="module" src="<?php echo e(asset('js/crm/leads.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/callingcrm/contact.blade.php ENDPATH**/ ?>