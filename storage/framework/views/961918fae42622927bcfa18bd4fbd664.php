<?php $__env->startSection('title', 'Calling CRM Dashboard'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>?v=<?php echo e(filemtime(public_path('css/crm/calling-crm.css'))); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas calling-crm-dashboard"><main class="main-wrapper">

  <!-- HEADER -->
  <div class="page-header dashboard-page-header">
    <h1 class="page-title">Dashboard</h1>
  </div>

  <!-- DASHBOARD GRID -->
  <div class="dashboard-grid">

    <!-- CALL OVERVIEW -->
    <div class="card call-overview-card">
      <div class="card-header">
        <span class="card-title">Call Overview</span>
        <div class="card-actions">
          <div class="crm-filter" data-filter>
            <button type="button" class="date-filter applied" data-filter-toggle>
              <span class="crm-filter-label" data-filter-label>Last 7 Days</span>
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="crm-popover crm-filter-menu">
              <div class="filter-wrapper">
                <div class="sticky-container">
                  <div class="crm-popover-title heading-title">Choose Date</div>
                  <div class="line"></div>
                  <div class="crm-popover-body content">
                    <label class="crm-choice"><input type="radio" name="dashboard_date" value="This Month"><span class="crm-radio"></span><span>This Month</span></label>
                    <label class="crm-choice"><input type="radio" name="dashboard_date" value="Yesterday"><span class="crm-radio"></span><span>Yesterday</span></label>
                    <label class="crm-choice"><input type="radio" name="dashboard_date" value="Today"><span class="crm-radio"></span><span>Today</span></label>
                    <label class="crm-choice"><input type="radio" name="dashboard_date" value="Last 7 Days" checked><span class="crm-radio"></span><span>Last 7 days</span></label>
                    <label class="crm-choice"><input type="radio" name="dashboard_date" value="Last 30 Days"><span class="crm-radio"></span><span>Last 30 days</span></label>
                    <label class="crm-choice"><input type="radio" name="dashboard_date" value="Custom Range"><span class="crm-radio"></span><span>Custom Range</span></label>
                  </div>
                  <div class="line mt-10"></div>
                </div>
                <div class="crm-popover-footer footer">
                  <button type="button" class="crm-apply-btn primary-btn-popup" data-filter-apply>Apply</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="call-overview-surface">
        <a href="<?php echo e(route('admin_panel.admin.callingcrm.report')); ?>" class="view-report-link">View Report</a>
      <div class="donut-container">
        <div class="donut-svg-wrap">
          <svg class="donut" viewBox="0 0 330 190" role="img" aria-label="Connected call percentage">
            <path d="M61 150 A104 104 0 0 1 269 150" fill="none" stroke="#D8C9F2" stroke-width="48" stroke-linecap="round"/>
            <path d="M61 150 A104 104 0 0 1 269 150" fill="none" stroke="#763ABB" stroke-width="48"
              stroke-dasharray="0 326.73" stroke-dashoffset="0" data-crm-connected-ring
              stroke-linecap="round"
              style="transition: stroke-dasharray 1.2s cubic-bezier(.4,0,.2,1);"/>
          </svg>
          <div class="donut-center">
            <div class="donut-pct" data-crm-connected-percent><span class="skeleton-loader" style="width: 45px; height: 28px; border-radius: 4px;"></span></div>
            <div class="donut-label">Connected</div>
          </div>
        </div>

        <div class="donut-legend">
          <div class="legend-item">
            <div class="legend-dot" style="background:#763ABB;"></div>
            <div class="legend-info">
              <span class="legend-name">Connected</span>
              <span class="legend-count" data-crm-connected-calls><span class="skeleton-loader" style="width: 32px; height: 16px;"></span></span>
            </div>
          </div>
          <div class="legend-item">
            <div class="legend-dot" style="background:#DDCEF5;"></div>
            <div class="legend-info">
              <span class="legend-name">Total</span>
              <span class="legend-count" data-crm-total-calls><span class="skeleton-loader" style="width: 38px; height: 16px;"></span></span>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>

    <!-- AGENT ACTIVITY -->
    <div class="card agent-activity-card">
      <div class="card-header">
        <span class="card-title">Agent Activity</span>
      </div>
      <div class="agent-grid">
        <div class="agent-stat">
          <div class="agent-icon active-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path d="M13 5.5a2.5 2.5 0 1 0-5 0 2.5 2.5 0 0 0 5 0Z"/><path d="m10.5 8 1.5 4 3 1"/><path d="m8.5 9-1 5-3 2"/><path d="m12 12-2 3 1 5"/><path d="m7.5 14 4 1"/></svg>
          </div>
          <div class="agent-label">Active Agents <span class="agent-help" aria-hidden="true">i</span></div>
          <div class="agent-numbers">
            <span class="agent-num" data-crm-active-agents><span class="skeleton-loader" style="width: 25px; height: 20px;"></span></span>
            <span class="agent-total" data-crm-total-agents>/ <span class="skeleton-loader" style="width: 25px; height: 14px;"></span></span>
          </div>
          <div class="agent-bar">
            <div class="agent-bar-fill active" style="width:0%;" data-crm-active-agent-bar></div>
          </div>
        </div>

        <div class="agent-stat">
          <div class="agent-icon break-icon">
            <svg width="20" height="20" fill="none" stroke="#F59E0B" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
          </div>
          <div class="agent-label">On-break Agents <span class="agent-help" aria-hidden="true">i</span></div>
          <div class="agent-numbers">
            <span class="agent-num break" data-crm-break-agents><span class="skeleton-loader" style="width: 25px; height: 20px;"></span></span>
            <span class="agent-total" data-crm-total-agents>/ <span class="skeleton-loader" style="width: 25px; height: 14px;"></span></span>
          </div>
          <div class="agent-bar">
            <div class="agent-bar-fill break" style="width:0%;" data-crm-break-agent-bar></div>
          </div>
        </div>
      </div>
    </div>

    <!-- LEADS BY STAGE -->
    <div class="card leads-card">
      <div class="leads-card-header">
        <span class="card-title">Leads by Stage</span>
        <div class="leads-filters">
          <div class="crm-filter" data-filter>
            <button type="button" class="date-filter applied" data-filter-toggle>
              <span class="crm-filter-label" data-filter-label><span class="skeleton-loader" style="width: 70px; height: 12px; border-radius: 3px;"></span></span>
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="crm-popover crm-filter-menu popover-left">
              <div class="filter-wrapper">
                <div class="sticky-container">
                  <div class="crm-popover-title heading-title">Choose Pipeline</div>
                  <div class="line"></div>
                  <div class="crm-popover-body content" data-crm-pipeline-filter-options>
                    <input type="search" class="crm-search search-input" placeholder="Search">
                    <div class="dashboard-state">Loading pipelines...</div>
                  </div>
                  <div class="line mt-10"></div>
                </div>
                <div class="crm-popover-footer footer">
                  <button type="button" class="crm-apply-btn primary-btn-popup" data-filter-apply>Apply</button>
                </div>
              </div>
            </div>
          </div>
          <div class="crm-filter" data-filter>
            <button type="button" class="date-filter" data-filter-toggle>
              <span class="crm-filter-label" data-filter-label><span class="skeleton-loader" style="width: 60px; height: 12px; border-radius: 3px;"></span></span>
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="crm-popover crm-filter-menu">
              <div class="filter-wrapper">
                <div class="sticky-container">
                  <div class="crm-popover-title heading-title">Choose Campaign</div>
                  <div class="line"></div>
                  <div class="crm-popover-body filter-list-wrapper content" data-crm-campaign-filter-options>
                    <input type="search" class="crm-search search-input" placeholder="Search">
                    <div class="dashboard-state">Loading campaigns...</div>
                  </div>
                  <div class="line mt-10"></div>
                </div>
                <div class="crm-popover-footer footer">
                  <button type="button" class="crm-apply-btn primary-btn-popup" data-filter-apply>Apply</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="leads-list" data-crm-leads-by-stage>
        <div class="lead-item" style="border-left-color: rgba(228, 231, 239, 0.6); pointer-events: none;">
          <div class="lead-left">
            <span class="lead-count"><span class="skeleton-loader" style="width: 25px; height: 16px;"></span></span>
            <span class="lead-name"><span class="skeleton-loader" style="width: 100px; height: 14px;"></span></span>
          </div>
          <span class="lead-pct"><span class="skeleton-loader" style="width: 40px; height: 14px;"></span></span>
        </div>
        <div class="lead-item" style="border-left-color: rgba(228, 231, 239, 0.6); pointer-events: none;">
          <div class="lead-left">
            <span class="lead-count"><span class="skeleton-loader" style="width: 25px; height: 16px;"></span></span>
            <span class="lead-name"><span class="skeleton-loader" style="width: 80px; height: 14px;"></span></span>
          </div>
          <span class="lead-pct"><span class="skeleton-loader" style="width: 40px; height: 14px;"></span></span>
        </div>
        <div class="lead-item" style="border-left-color: rgba(228, 231, 239, 0.6); pointer-events: none;">
          <div class="lead-left">
            <span class="lead-count"><span class="skeleton-loader" style="width: 25px; height: 16px;"></span></span>
            <span class="lead-name"><span class="skeleton-loader" style="width: 120px; height: 14px;"></span></span>
          </div>
          <span class="lead-pct"><span class="skeleton-loader" style="width: 40px; height: 14px;"></span></span>
        </div>
      </div>
    </div>

    <!-- TOOLS TO IMPROVE EFFICIENCY -->
    <div class="card tools-card">
      <div class="card-header">
        <span class="card-title">Tools to Improve Efficiency &amp; Outcomes</span>
      </div>
      <div class="tools-grid">
        <a class="tool-item" href="<?php echo e(route('admin_panel.admin.callingcrm.trends')); ?>">
          <div class="tool-icon-wrap">📈</div>
          <div class="tool-info">
            <span class="tool-name">User Trends</span>
            <span class="tool-desc">Discover how your calls, conversions, and breaks have evolved over time.</span>
          </div>
        </a>
        <a class="tool-item" href="<?php echo e(route('admin_panel.admin.callingcrm.trends')); ?>">
          <div class="tool-icon-wrap">📊</div>
          <div class="tool-info">
            <span class="tool-name">Business Trend</span>
            <span class="tool-desc">Get business insights on conversions, calls &amp; lead sources driving results.</span>
          </div>
        </a>
        <a class="tool-item" href="<?php echo e(route('admin_panel.admin.callingcrm.pipeline')); ?>">
          <div class="tool-icon-wrap">⚡</div>
          <div class="tool-info">
            <span class="tool-name">Workflow</span>
            <span class="tool-desc">Create workflows to handle actions like sending WhatsApp messages and more.</span>
          </div>
        </a>
      </div>
    </div>

    <!-- QUICK ACCESS -->
    <div class="card quick-card">
      <div class="card-header">
        <span class="card-title">Quick Access</span>
      </div>
      <div class="quick-grid">
        <a class="quick-item" href="<?php echo e(route('admin_panel.admin.callingcrm.report.user')); ?>">
          <div class="quick-icon">📞</div>
          <span class="quick-label">User Call Report</span>
          <span class="quick-arrow">›</span>
        </a>
        <a class="quick-item" href="<?php echo e(route('admin_panel.admin.callingcrm.report.login')); ?>">
          <div class="quick-icon">🔐</div>
          <span class="quick-label">User Login Report</span>
          <span class="quick-arrow">›</span>
        </a>
        <button type="button" class="quick-item" data-upload-open>
          <div class="quick-icon">📤</div>
          <span class="quick-label">Upload Excel Sheet</span>
          <span class="quick-arrow">›</span>
        </button>
        <button type="button" class="quick-item" data-campaign-open>
          <div class="quick-icon">🚀</div>
          <span class="quick-label">Create Campaign</span>
          <span class="quick-arrow">›</span>
        </button>
      </div>
    </div>

    <!-- PINNED CAMPAIGNS -->
    <div class="card pinned-section">
      <div class="pinned-header">
        <span class="card-title">Pinned Campaigns</span>
        <div class="pinned-actions">
          <a href="<?php echo e(route('admin_panel.admin.callingcrm.report')); ?>" class="report-link">Campaigns Report</a>
          <button type="button" class="add-btn" data-pin-open>+</button>
        </div>
      </div>
      <div class="pinned-list" data-crm-pinned-campaigns>
        <div class="pinned-campaign" style="pointer-events: none;">
          <span class="pinned-campaign-name"><span class="skeleton-loader" style="width: 120px; height: 14px;"></span></span>
          <span class="pinned-campaign-meta"><span class="skeleton-loader" style="width: 50px; height: 14px;"></span></span>
        </div>
        <div class="pinned-campaign" style="pointer-events: none;">
          <span class="pinned-campaign-name"><span class="skeleton-loader" style="width: 150px; height: 14px;"></span></span>
          <span class="pinned-campaign-meta"><span class="skeleton-loader" style="width: 50px; height: 14px;"></span></span>
        </div>
        <div class="pinned-campaign" style="pointer-events: none;">
          <span class="pinned-campaign-name"><span class="skeleton-loader" style="width: 95px; height: 14px;"></span></span>
          <span class="pinned-campaign-meta"><span class="skeleton-loader" style="width: 50px; height: 14px;"></span></span>
        </div>
      </div>
      <div class="view-all-link">
        <a href="<?php echo e(route('admin_panel.admin.callingcrm.pipeline')); ?>">View All Campaigns &rsaquo;</a>
      </div>
    </div>

  </div><!-- end grid -->

  <div class="crm-popover-layer" data-popover-layer></div>

  <div class="crm-modal-backdrop" data-upload-modal>
    <div class="crm-modal crm-large-modal" role="dialog" aria-modal="true" aria-labelledby="uploadExcelTitle">
      <div class="crm-modal-head">
        <div class="modal-title-wrap">
          <div class="crm-modal-title" id="uploadExcelTitle">Upload Excel Sheet</div>
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

  <div class="crm-modal-backdrop" data-campaign-modal>
    <div class="crm-modal crm-campaign-modal" role="dialog" aria-modal="true" aria-labelledby="createCampaignTitle">
      <div class="crm-modal-head">
        <div class="modal-title-wrap">
          <div class="crm-modal-title" id="createCampaignTitle">Create Campaign</div>
        </div>
        <button type="button" class="crm-modal-close" data-campaign-close aria-label="Close">&times;</button>
      </div>
      <form id="campaignCreateForm" class="campaign-form" novalidate>
        <div class="campaign-grid">
          <div class="campaign-field">
            <label>Name</label>
            <input class="campaign-input" id="campaignNameInput" required placeholder="Campaign Name">
          </div>
          <div class="campaign-field">
            <label>Pipeline</label>
            <div style="position: relative;">
              <select class="campaign-input" id="campaignPipelineSelect" required style="width: 100%; padding-right: 32px; appearance: none; -webkit-appearance: none;">
                <option value="" disabled selected>Select Pipeline</option>
              </select>
              <div class="campaign-select" style="position: absolute; right: 0; top: 0; bottom: 0; border: none; padding: 0 12px; display: flex; align-items: center; pointer-events: none; background: transparent;">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 7.5 10 12l4.5-4.5H5.5z"/></svg>
              </div>
            </div>
          </div>
          <div class="campaign-field campaign-field-wide">
            <label>Who will be managing this campaign?</label>
            <div class="campaign-box" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; min-height: 44px; padding: 6px 12px; position: relative;">
              <div id="campaignManagersChips" style="display: flex; flex-wrap: wrap; gap: 6px;"></div>
              <input type="text" id="campaignManagerSearch" placeholder="Type to search managers..." autocomplete="off" style="border: none; outline: none; flex: 1; min-width: 150px; font-size: 13px; color: var(--text); background: transparent;">
              <div id="campaignManagerDropdown" class="crm-popover" style="display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; background: #fff; border: 1px solid var(--border); border-radius: 8px; max-height: 180px; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-top: 4px;">
              </div>
            </div>
          </div>
          <div class="campaign-field campaign-field-wide">
            <label>Select Agents</label>
            <div class="campaign-box" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; min-height: 44px; padding: 6px 12px; position: relative;">
              <div id="campaignAgentsChips" style="display: flex; flex-wrap: wrap; gap: 6px;"></div>
              <input type="text" id="campaignAgentSearch" placeholder="Type to search agents..." autocomplete="off" style="border: none; outline: none; flex: 1; min-width: 150px; font-size: 13px; color: var(--text); background: transparent;">
              <div id="campaignAgentDropdown" class="crm-popover" style="display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; background: #fff; border: 1px solid var(--border); border-radius: 8px; max-height: 180px; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-top: 4px;">
              </div>
            </div>
          </div>
        </div>
        <div class="campaign-section-title">Lead Distribution</div>
        <div class="distribution-options">
          <div class="distribution-card selected" data-strategy="on_demand">
            <div class="distribution-title"><span class="distribution-radio"></span>On Demand</div>
            <div>Leads stay unassigned until a user assigns it to themself or clicks Start Calling, then the system assigns ten lead at a time.</div>
          </div>
          <div class="distribution-card" data-strategy="equal">
            <div class="distribution-title"><span class="distribution-radio"></span>Equal</div>
            <div>Distributes leads equally among all agents in the campaign, ensuring fair allocation.</div>
          </div>
          <div class="distribution-card" data-strategy="conditional">
            <div class="distribution-title"><span class="distribution-radio"></span>Conditional</div>
            <div>Assigns leads based on set conditions, ensuring the right leads go to the right agents.</div>
          </div>
        </div>
        <button type="button" class="additional-settings" id="campaignAdditionalSettingsBtn" aria-expanded="false" aria-controls="campaignAdditionalSettingsContent">
          <span class="additional-settings-title">Additional Settings <span class="additional-info">i</span></span>
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="additional-settings-content" id="campaignAdditionalSettingsContent" hidden style="display: none; padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: #fbf7ff; margin-bottom: 16px;">
          <div class="campaign-grid">
            <div class="campaign-field">
              <label>Priority</label>
              <div style="position: relative;">
                <select class="campaign-input" id="campaignPrioritySelect" style="width: 100%; padding-right: 32px; appearance: none; -webkit-appearance: none;">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                  <option value="critical">Critical</option>
                </select>
                <div style="position: absolute; right: 12px; top: 11px; pointer-events: none; color: #a4a8b2; display: flex; align-items: center;">
                  <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 7.5 10 12l4.5-4.5H5.5z"/></svg>
                </div>
              </div>
            </div>
            <div></div>
            <div class="campaign-field campaign-field-wide" style="margin-top: 6px;">
              <span class="additional-settings-title" style="font-size: 13px; font-weight: 800; color: var(--text);">Lead Duplicacy</span>
            </div>
            <div class="campaign-field">
              <label>Check for Duplicates</label>
              <div style="position: relative;">
                <select class="campaign-input" id="campaignDuplicacyScope" style="width: 100%; padding-right: 32px; appearance: none; -webkit-appearance: none;">
                  <option value="none">Don't Check</option>
                  <option value="campaign" selected>Within This Campaign</option>
                  <option value="pipeline">Within This Pipeline</option>
                  <option value="org">Within Organization</option>
                </select>
                <div style="position: absolute; right: 12px; top: 11px; pointer-events: none; color: #a4a8b2; display: flex; align-items: center;">
                  <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 7.5 10 12l4.5-4.5H5.5z"/></svg>
                </div>
              </div>
            </div>
            <div class="campaign-field">
              <label>If Duplicate Found</label>
              <div style="position: relative;">
                <select class="campaign-input" id="campaignDuplicacyAction" style="width: 100%; padding-right: 32px; appearance: none; -webkit-appearance: none;">
                  <option value="ignore" selected>Ignore Duplicate</option>
                  <option value="block">Block Duplicate</option>
                  <option value="merge">Merge Duplicate</option>
                  <option value="reassign">Reassign Duplicate</option>
                </select>
                <div style="position: absolute; right: 12px; top: 11px; pointer-events: none; color: #a4a8b2; display: flex; align-items: center;">
                  <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 7.5 10 12l4.5-4.5H5.5z"/></svg>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="campaign-create-row">
          <button type="submit" class="campaign-create-btn" id="campaignSubmitBtn">Create</button>
        </div>
      </form>
    </div>
  </div>

  <div class="crm-modal-backdrop" data-conditions-modal>
    <div class="crm-modal crm-conditions-modal" role="dialog" aria-modal="true" aria-labelledby="setConditionsTitle">
      <div class="crm-modal-head">
        <div class="modal-title-wrap">
          <div class="crm-modal-title" id="setConditionsTitle">Set Conditions</div>
        </div>
        <button type="button" class="crm-modal-close" data-conditions-close aria-label="Close">&times;</button>
      </div>

      <form class="conditions-form" id="campaignConditionsForm" novalidate>
        <div class="conditions-body">
          <div class="conditions-row conditions-row-head">
            <label>If lead has data:</label>
            <input type="text" class="conditions-input" id="conditionFieldSeed" placeholder="Ex. Name,City.">
            <button type="button" class="conditions-add-circle" id="conditionSeedAdd" aria-label="Add condition">+</button>
          </div>

          <div class="conditions-rule-list" id="conditionsRuleList">
            <div class="conditions-row conditions-rule" data-condition-rule>
              <label>Option 1</label>
              <input type="text" class="conditions-input" data-condition-field placeholder="Ex. Name,City.">
              <span class="conditions-then">then assign lead to<br>User:</span>
              <div class="conditions-select-wrap">
                <select class="conditions-input" data-condition-user>
                  <option value="">Choose User</option>
                </select>
              </div>
              <button type="button" class="conditions-delete" data-condition-remove aria-label="Remove condition">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>

          <button type="button" class="conditions-add-btn" id="conditionsAddRule">Add another</button>
        </div>

        <div class="conditions-footer">
          <label>Otherwise assign lead to</label>
          <div class="conditions-select-wrap">
            <select class="conditions-input" id="conditionsFallbackUser">
              <option value="">Choose User</option>
            </select>
          </div>
          <div class="conditions-actions">
            <button type="button" class="conditions-secondary" data-conditions-close>Cancel</button>
            <button type="submit" class="conditions-primary">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="crm-modal-backdrop" data-pin-modal>
    <div class="crm-modal" role="dialog" aria-modal="true" aria-labelledby="pinCampaignTitle">
      <div class="crm-modal-head">
        <div class="crm-modal-title" id="pinCampaignTitle">Pin Campaign</div>
        <button type="button" class="crm-modal-close" data-pin-close aria-label="Close">&times;</button>
      </div>
      <div class="crm-modal-search">
        <input type="search" placeholder="Search Campaigns" data-pin-search>
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M16.5 16.5 21 21"/></svg>
      </div>
      <div class="pin-list" data-pin-list>
        <div class="pin-option">MP Raw Data</div>
        <div class="pin-option selected">MP Transacted</div>
        <div class="pin-option">SME Data</div>
        <div class="pin-option">Trading RAW</div>
        <div class="pin-option">Trading Transacted</div>
      </div>
      <div class="crm-modal-footer">
        <button type="button" class="crm-modal-btn" data-pin-close>Cancel</button>
        <button type="button" class="crm-modal-btn" data-pin-confirm>Confirm</button>
      </div>
    </div>
  </div>

</main></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/dashboard.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/callingcrm/dashboard.blade.php ENDPATH**/ ?>