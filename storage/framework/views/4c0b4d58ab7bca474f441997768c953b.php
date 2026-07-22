<?php $__env->startSection('title', 'Calling CRM Contact'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>?v=<?php echo e(filemtime(public_path('css/crm/calling-crm.css'))); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas contact-search-app">
  <main class="main-wrapper contact-search-wrapper">

    <section data-contact-form-page>
      <div class="contact-search-header">
        <h1 class="page-title">Contacts</h1>
        <div class="header-actions">
          <button class="btn-outline" type="button" onclick="window.location='<?php echo e(route('admin_panel.admin.callingcrm.settings')); ?>#properties'">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Custom Contact Properties
          </button>
          <button class="btn-outline" type="button" data-upload-open>
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload Excel Sheet
          </button>
          <button class="btn-primary contact-add-btn" type="button" data-lead-open>
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
            Add Lead
          </button>
        </div>
      </div>

      <div class="contact-search-grid">
        <form class="contact-search-card contact-lead-card" data-contact-search-form>
          <h2>Lead Details</h2>

          <div class="contact-section-title">Basic Details</div>
          <div class="contact-form-grid">
            <input class="contact-field" type="text" name="name" placeholder="Contact Name">
            <input class="contact-field" type="tel" name="phone" placeholder="Contact Number">
            <input class="contact-field" type="email" name="email" placeholder="Email">
          </div>

          <div class="contact-section-title">Custom Contact Property</div>
          <div class="contact-form-grid">
            <input class="contact-field" type="text" name="company_name" placeholder="Company Name">
            <input class="contact-field" type="text" name="address_line_1" placeholder="Address Line 1">
            <input class="contact-field" type="text" name="address_line_2" placeholder="Address Line 2">
            <input class="contact-field" type="text" name="city" placeholder="Town/City">
            <input class="contact-field" type="text" name="state" placeholder="State">
            <input class="contact-field" type="text" name="pincode" placeholder="Pincode">
            <input class="contact-field" type="text" name="gst" placeholder="GST">
          </div>

          <div class="contact-form-actions">
            <button type="reset" class="btn-outline">Reset</button>
            <button type="submit" class="btn-primary contact-search-submit">Search Contacts</button>
          </div>
        </form>

        <aside class="contact-side-stack">
          <div class="contact-search-card">
            <h2>Campaigns</h2>
            <select class="contact-select" name="campaign_id" data-contact-campaign-select>
              <option value="">Select Campaigns</option>
            </select>
          </div>

          <div class="contact-search-card">
            <h2>Contact Source</h2>
            <div class="contact-source-list" data-contact-source-list>
              <label><input type="checkbox" name="source" value="FILE_UPLOAD"> FILE_UPLOAD</label>
              <label><input type="checkbox" name="source" value="WALK_IN_LEAD"> WALK_IN_LEAD</label>
              <label><input type="checkbox" name="source" value="INCOMING_IVR"> INCOMING_IVR</label>
              <label><input type="checkbox" name="source" value="WORKFLOW"> WORKFLOW</label>
              <label><input type="checkbox" name="source" value="GOOGLE_SHEET"> GOOGLE_SHEET</label>
              <label class="contact-source-extra"><input type="checkbox" name="source" value="MANUAL"> MANUAL</label>
              <label class="contact-source-extra"><input type="checkbox" name="source" value="API"> API</label>
              <button type="button" class="contact-view-more" data-source-more>View More...</button>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <section data-contact-results-page hidden>
      <div class="contact-search-header">
        <div class="contact-title-row">
          <button class="contact-back-btn" type="button" data-contact-back title="Go back">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          </button>
          <h1 class="page-title">Contact Search</h1>
        </div>
        <div class="header-actions">
          <button class="btn-outline" type="button" onclick="window.location='<?php echo e(route('admin_panel.admin.callingcrm.settings')); ?>#properties'">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Custom Contact Properties
          </button>
          <button class="btn-outline" type="button" data-upload-open>
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload Contacts
          </button>
        </div>
      </div>

      <div class="filter-bar contact-results-filters">
        <button class="filter-btn applied" type="button" data-filter-chip="source">Integration <span data-source-count>0</span> <span data-clear-source>&times;</span></button>
        <button class="filter-btn" type="button">FileUpload <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></button>
        <button class="filter-btn" type="button">Others <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></button>
        <button class="filter-btn" type="button">Workflows <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></button>
        <button class="filter-export-btn" type="button" title="Download CSV" data-contact-export>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </button>
      </div>

      <div class="table-card contact-results-card">
        <div class="table-responsive">
          <table id="contactResultsTable" class="results-table contact-results-table">
            <thead>
              <tr>
                <th style="width: 44px; text-align: center;"><input type="checkbox" data-contact-select-all></th>
                <th>Name</th>
                <th>Number</th>
                <th>Campaign</th>
                <th>Pipeline</th>
                <th>Creation Date</th>
                <th>Updated at</th>
                <th>Stage</th>
                <th>User Assigned</th>
                <th style="width: 70px;">Action</th>
              </tr>
            </thead>
            <tbody data-contact-search-body>
              <tr>
                <td colspan="10" style="text-align: center; padding: 30px; color: var(--calling-crm-muted);">Search contacts to view results.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="pagination-footer contact-results-pagination">
        <div>
          Items per page:
          <select class="pagination-select" data-contact-per-page>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50" selected>50</option>
            <option value="100">100</option>
          </select>
        </div>
        <div data-contact-pagination-info>0 - 0 of 0</div>
        <div class="pagination-arrows">
          <button type="button" data-contact-prev aria-label="Previous page">&lt;</button>
          <button type="button" data-contact-next aria-label="Next page">&gt;</button>
        </div>
      </div>
    </section>

    <div id="contactMenuLayer" class="contact-menu-layer">
      <button class="contact-menu-item" data-action="open">OPEN</button>
      <button class="contact-menu-item" data-action="history">VIEW DISPOSE HISTORY</button>
      <button class="contact-menu-item" data-action="edit">EDIT</button>
      <button class="contact-menu-item" data-action="delete">DELETE</button>
    </div>

    <div class="crm-modal-backdrop" data-upload-modal>
      <div class="crm-modal crm-large-modal contact-upload-modal" role="dialog" aria-modal="true" aria-labelledby="contactUploadExcelTitle">
        <div class="crm-modal-head">
          <div class="modal-title-wrap">
            <div class="crm-modal-title" id="contactUploadExcelTitle">Upload Excel Sheet</div>
            <a href="https://docs.neodove.com/" target="_blank" class="learn-more-pill" rel="noopener">
              <i class="fa-regular fa-circle-play"></i>
              Learn More
            </a>
          </div>
          <button type="button" class="crm-modal-close" data-upload-close aria-label="Close">&times;</button>
        </div>

        <!-- Step indicator -->
        <div class="upload-steps">
          <div class="upload-step active" data-upload-step-indicator="1"><span class="upload-step-num">1</span> Select File</div>
          <div class="upload-step-divider"></div>
          <div class="upload-step" data-upload-step-indicator="2"><span class="upload-step-num">2</span> Map Columns</div>
          <div class="upload-step-divider"></div>
          <div class="upload-step" data-upload-step-indicator="3"><span class="upload-step-num">3</span> Upload</div>
        </div>

        <!-- Step 1: File selection -->
        <div data-upload-step="1">
          <div class="upload-campaign-row">
            <label>Select Campaign *</label>
            <select class="upload-campaign-select" data-upload-campaign-select>
              <option value="">Choose a campaign...</option>
            </select>
          </div>
          <div class="upload-dropzone" data-upload-dropzone>
            <div>
              <div class="upload-icon"><i class="fa-solid fa-arrow-up"></i></div>
              <div class="upload-drop-text">Drag and drop file</div>
              <button type="button" class="upload-browse-btn">Browse</button>
              <div class="upload-format">Supported formats are .csv, .xls, .xlsx</div>
            </div>
          </div>
          <div class="upload-file-preview" data-upload-file-preview>
            <div class="upload-file-icon"><i class="fa-solid fa-file-excel"></i></div>
            <div class="upload-file-info">
              <div class="upload-file-name" data-upload-file-name></div>
              <div class="upload-file-size" data-upload-file-size></div>
            </div>
            <button type="button" class="upload-file-remove" data-upload-file-remove aria-label="Remove file">&times;</button>
          </div>
          <div class="upload-error-msg" data-upload-error></div>
          <div class="upload-meta-row">
            <div>Max leads: 25,000 at a time, file size limit: 3MB.</div>
            <a href="#" class="sample-link" data-import-sample-link>Download Sample file</a>
          </div>
          <div class="upload-note"><i class="fa-regular fa-sun"></i> No specific column order needed! Just include crucial details like name and number in the file.</div>
        </div>

        <!-- Step 2: Column mapping -->
        <div data-upload-step="2" style="display:none;">
          <div class="upload-mapping-area visible">
            <div class="upload-mapping-title">Map Your Columns</div>
            <div class="upload-mapping-sub">We detected <strong data-upload-row-count>0</strong> rows. Match each column to a lead field below.</div>
            <div class="upload-mapping-scroll">
              <table class="upload-mapping-table">
                <thead><tr><th>File Column</th><th>Map To</th><th>Preview</th></tr></thead>
                <tbody data-upload-mapping-body></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Step 3: Progress + Result -->
        <div data-upload-step="3" style="display:none;">
          <div class="upload-progress-area visible" data-upload-progress>
            <div class="upload-spinner"></div>
            <div class="upload-progress-text">Uploading your file...</div>
            <div class="upload-progress-sub" data-upload-progress-detail>Please wait</div>
            <div class="upload-progress-bar-wrap"><div class="upload-progress-bar" data-upload-progress-bar></div></div>
          </div>
          <div class="upload-result-area" data-upload-result>
            <div class="upload-result-icon" data-upload-result-icon>✅</div>
            <div class="upload-result-title" data-upload-result-title>Import Queued Successfully</div>
            <div class="upload-result-desc" data-upload-result-desc>Your file has been queued for processing.</div>
            <div class="upload-result-stats" data-upload-result-stats></div>
          </div>
        </div>

        <!-- Action row -->
        <div class="upload-action-row" data-upload-actions>
          <button type="button" class="upload-btn-back" data-upload-back style="display:none;">Back</button>
          <button type="button" class="upload-btn-next" data-upload-next disabled>Next</button>
        </div>
      </div>
    </div>

    <div class="crm-modal-backdrop" data-lead-modal>
      <div class="crm-modal lead-modal" role="dialog" aria-modal="true" aria-labelledby="addLeadTitle">
        <div class="crm-modal-head">
          <div class="modal-title-wrap">
            <div class="crm-modal-title" id="addLeadTitle">Add Lead</div>
            <a href="https://docs.neodove.com/" target="_blank" class="learn-more-pill" rel="noopener">
              <i class="fa-regular fa-circle-play"></i>
              Learn More
            </a>
          </div>
          <button type="button" class="crm-modal-close" data-lead-close aria-label="Close">&times;</button>
        </div>
        <form class="lead-form" data-add-lead-form>
          <input class="lead-input" type="text" name="name" placeholder="Contact Name" autocomplete="name">
          <input class="lead-input" type="tel" name="phone" placeholder="Contact Number *" autocomplete="tel" required>
          <input class="lead-input" type="email" name="email" placeholder="Email" autocomplete="email">
          <div class="lead-select">
            <select class="lead-input" data-crm-campaign-select required>
              <option value="">Campaign *</option>
            </select>
          </div>
          <div class="lead-select">
            <select class="lead-input" name="state" data-state-select>
              <option value="">State</option>
            </select>
          </div>
          <div class="lead-select">
            <select class="lead-input" name="city" data-city-select disabled>
              <option value="">City</option>
            </select>
          </div>
          <div class="lead-modal-actions">
            <button type="submit" class="lead-submit-btn">Submit</button>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/contact.js')); ?>"></script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\callingcrm\contact.blade.php ENDPATH**/ ?>