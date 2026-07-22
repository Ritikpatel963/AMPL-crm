<?php $__env->startSection('title', 'Calling CRM Campaign'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>?v=<?php echo e(filemtime(public_path('css/crm/calling-crm.css'))); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas campaign-detail-app" data-campaign-id="<?php echo e($campaignId); ?>">
  <div class="campaign-detail-topbar">
    <div class="campaign-detail-title-row">
      <button type="button" class="campaign-back-btn" onclick="window.location='<?php echo e(route('admin_panel.admin.callingcrm.pipeline')); ?>'" aria-label="Back to campaigns">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      </button>
      <h1 data-campaign-title>Campaign</h1>
    </div>
    <div class="campaign-detail-actions">
      <span class="campaign-priority">Priority: <span data-campaign-priority-icon>↑</span> <span data-campaign-priority>Medium</span></span>
      <button type="button" class="campaign-toolbar-btn">Lead Summary</button>
      <button type="button" class="campaign-toolbar-btn" data-campaign-call-logs>Call Logs</button>
      <div class="campaign-action-wrap">
        <button type="button" class="campaign-toolbar-btn" data-campaign-action-toggle>
          Action
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
        </button>
        <div class="campaign-action-menu" data-campaign-action-menu>
          <button type="button">Dispositions</button>
          <button type="button" data-detail-upload-open>Upload Excel Sheet</button>
          <button type="button">Add Lead</button>
          <button type="button">Engagement Form</button>
          <button type="button">Tasks</button>
          <button type="button" data-campaign-resume>Resume campaign</button>
          <button type="button">Campaign Settings</button>
        </div>
      </div>
    </div>
  </div>

  <div class="campaign-paused-alert" data-paused-alert hidden>Campaign is paused.</div>

  <div class="campaign-detail-grid">
    <article class="campaign-detail-card">
      <div class="campaign-card-head">Leads Statistics</div>
      <div class="campaign-stats-row">
        <div><span>Total</span><strong data-stat-total>0</strong></div>
        <div><span>Uncontacted</span><strong data-stat-uncontacted>0</strong></div>
        <div><span>In-Progress</span><strong data-stat-progress>0</strong></div>
        <div><span>Closed</span><strong data-stat-closed>0</strong></div>
      </div>
      <div class="campaign-donut-wrap">
        <div class="campaign-donut" data-donut-main></div>
        <div class="campaign-donut-legend">
          <span><i style="background:#16b335"></i>Uncontacted</span>
          <span><i style="background:#ffaf00"></i>In-Progress</span>
          <span><i style="background:#ef4444"></i>Closed</span>
        </div>
      </div>
    </article>

    <article class="campaign-detail-card">
      <div class="campaign-card-head">In-Progress Leads</div>
      <div class="campaign-stats-row three">
        <div><span>Total</span><strong data-progress-total>0</strong></div>
        <div><span>No Follow-Up</span><strong data-progress-no-follow>0</strong></div>
        <div><span>Follow-Up</span><strong data-progress-follow>0</strong></div>
      </div>
      <div class="campaign-donut-wrap">
        <div class="campaign-donut small" data-donut-progress></div>
        <div class="campaign-donut-legend">
          <span><i style="background:#ffd747"></i>No Follow-Up</span>
          <span><i style="background:#ff7b7b"></i>Follow-Up</span>
        </div>
      </div>
    </article>

    <article class="campaign-detail-card">
      <div class="campaign-card-head">Closed Leads</div>
      <div class="campaign-stats-row four">
        <div><span>Total</span><strong data-closed-total>0</strong></div>
        <div><span>Converted</span><strong data-closed-converted>0</strong></div>
        <div><span>Lost</span><strong data-closed-lost>0</strong></div>
        <div><span>Closed By System</span><strong data-closed-system>0</strong></div>
      </div>
      <div class="campaign-donut-wrap">
        <div class="campaign-donut small" data-donut-closed></div>
        <div class="campaign-donut-legend">
          <span><i style="background:#d861dc"></i>Converted</span>
          <span><i style="background:#8c8c8c"></i>Lost</span>
        </div>
      </div>
    </article>
  </div>

  <article class="campaign-detail-card campaign-wide-card">
    <div class="campaign-card-head with-meta">
      <span>Lead Distribution <small>(Last updated 1 month ago)</small></span>
      <button type="button" class="campaign-refresh-btn" data-campaign-refresh aria-label="Refresh">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg>
      </button>
    </div>
    <div class="campaign-distribution">
      <div class="campaign-bar-label" data-manager-name>Campaign</div>
      <div class="campaign-bar-track"><div class="campaign-bar-fill" data-distribution-bar><span data-distribution-count>0</span></div></div>
      <div class="campaign-bar-legend">
        <span><i style="background:#16b335"></i>Uncontacted</span>
        <span><i style="background:#a99517"></i>No Follow-Up</span>
        <span><i style="background:#ffd747"></i>Follow-Up</span>
        <span><i style="background:#f28d93"></i>Not Connected</span>
        <span><i style="background:#ff1111"></i>Closed</span>
      </div>
    </div>
  </article>

  <article class="campaign-detail-card campaign-wide-card">
    <div class="campaign-card-head">Uploaded Files</div>
    <div class="campaign-table-wrap">
      <table class="campaign-files-table">
        <thead>
          <tr>
            <th>Sr.No.</th>
            <th>File Name</th>
            <th>Date ↓</th>
            <th>Status</th>
            <th>Created</th>
            <th>Merged</th>
            <th>Merged &amp; Reopened</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody data-imports-body>
          <tr><td colspan="8">Loading uploaded files...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="campaign-files-footer">
      <div class="campaign-note"><span>☼</span> Note: Only logs from the last 30 days are available.</div>
      <div class="campaign-pager">Items per page: <strong>10</strong> <span data-imports-count>0 of 0</span> ‹ ›</div>
    </div>
  </article>

  <!-- Upload Modal (auto-selects current campaign) -->
  <div class="crm-modal-backdrop" data-upload-modal data-upload-campaign-id="<?php echo e($campaignId); ?>">
    <div class="crm-modal crm-large-modal" role="dialog" aria-modal="true" aria-labelledby="detailUploadExcelTitle">
      <div class="crm-modal-head">
        <div class="modal-title-wrap">
          <div class="crm-modal-title" id="detailUploadExcelTitle">Upload Excel Sheet</div>
          <a href="https://docs.neodove.com/" target="_blank" class="learn-more-pill" rel="noopener">
            <i class="fa-regular fa-circle-play"></i>
            Learn More
          </a>
        </div>
        <button type="button" class="crm-modal-close" data-upload-close aria-label="Close">&times;</button>
      </div>

      <div class="upload-steps">
        <div class="upload-step active" data-upload-step-indicator="1"><span class="upload-step-num">1</span> Select File</div>
        <div class="upload-step-divider"></div>
        <div class="upload-step" data-upload-step-indicator="2"><span class="upload-step-num">2</span> Map Columns</div>
        <div class="upload-step-divider"></div>
        <div class="upload-step" data-upload-step-indicator="3"><span class="upload-step-num">3</span> Upload</div>
      </div>

      <div data-upload-step="1">
        <div class="upload-campaign-row">
          <label>Select Campaign *</label>
          <select class="upload-campaign-select" data-upload-campaign-select>
            <option value="">Choose a campaign...</option>
          </select>
        </div>
        <div class="upload-dropzone" data-upload-dropzone>
          <div>
            <div class="upload-icon">↑</div>
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

      <div class="upload-action-row" data-upload-actions>
        <button type="button" class="upload-btn-back" data-upload-back style="display:none;">Back</button>
        <button type="button" class="upload-btn-next" data-upload-next disabled>Next</button>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/campaign-detail.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\callingcrm\campaign-detail.blade.php ENDPATH**/ ?>