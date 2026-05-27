<?php $__env->startSection('title', 'Calling CRM Pipeline'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>">
<?php $__env->stopPush(); ?>


<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas crm-pipeline-app">
<!-- ALL CAMPAIGNS PAGE -->
  <div class="page active" id="page-all">
    <div class="topbar">
      <div class="topbar-left">
        <span class="page-title">All Campaigns</span>
      </div>
      <div class="topbar-right">
        <div class="search-wrap">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6.5" cy="6.5" r="5"/><path d="M11 11l3 3"/></svg>
          <input type="text" placeholder="Search campaign…" />
        </div>
      </div>
    </div>

    <div class="page-body">

      <!-- Indore Sales Group -->
      <div class="group-card">
        <div class="group-head">
          <div class="group-head-left">
            <div class="group-accent-bar" style="background:#763abb;"></div>
            <span class="group-title">Indore Sales</span>
            <span class="group-count">8</span>
          </div>
          <div class="arrow-btn" onclick="showPage('detail')" title="Open pipeline">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
          </div>
        </div>
        <div class="camp-list">
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Vivek Leads</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Ravi UP Data</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data CG</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data RJ</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-high"></div><span class="camp-name">Existing Customers</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data MH</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data UP</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Nandini RJ Raw Data</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
        </div>
      </div>

      <!-- Leads Group -->
      <div class="group-card">
        <div class="group-head">
          <div class="group-head-left">
            <div class="group-accent-bar" style="background:#1094f9;"></div>
            <span class="group-title">Leads</span>
            <span class="group-count">7</span>
          </div>
          <div class="arrow-btn">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
          </div>
        </div>
        <div class="camp-list">
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">SME Data</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">MP Transacted</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-high"></div><span class="camp-name">MP Raw Data</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Trading RAW</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Trading Transacted</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data Odisha</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data BR</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
        </div>
      </div>

    </div>
  </div>

  <!-- DETAIL PAGE -->
  <div class="page" id="page-detail">
    <div class="topbar">
      <div class="topbar-left">
        <button type="button" class="topbar-back" onclick="showPage('all')" aria-label="Back to all campaigns">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 8H3M7 4L3 8l4 4"/></svg>
        </button>
        <span class="page-title">Indore Sales Pipeline</span>
      </div>
      <div class="topbar-right">
        <button class="btn">Lead Summary</button>
        <button class="btn">Call Logs</button>
        <div class="pipeline-action-wrap">
          <button type="button" class="btn" data-pipeline-action-toggle>
            Action
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
          </button>
          <div class="pipeline-action-menu" data-pipeline-action-menu>
            <button type="button">Dispositions</button>
            <button type="button">Upload Excel Sheet</button>
            <button type="button">Add Lead</button>
            <button type="button">Manage Pipelines</button>
          </div>
        </div>
        <button class="btn btn-primary">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v12M2 8h12"/></svg>
          Create Campaign
        </button>
        <div class="search-wrap">
          <input type="text" placeholder="Search Campaign" />
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="6.5" cy="6.5" r="5"/><path d="M11 11l3 3"/></svg>
        </div>
      </div>
    </div>

    <div class="detail-body">
      <!-- Left panel -->
      <div class="detail-left">

        <!-- Funnel card -->
        <div class="card">
          <div class="card-head">
            <span class="card-head-title">Lead funnel by stages</span>
          </div>
          <div class="card-body">
            <div class="stats-row">
              <div class="stat-tile"><div class="stat-label">Total Leads</div><div class="stat-value">45,692</div></div>
              <div class="stat-tile"><div class="stat-label">In-Progress</div><div class="stat-value amber">1,535</div></div>
              <div class="stat-tile"><div class="stat-label">Closed</div><div class="stat-value green">3,218</div></div>
            </div>
            <div class="funnel-container">
              <svg class="funnel" width="420" height="200" viewBox="0 0 420 200" role="img" aria-label="Lead funnel showing stages">
                <title>Lead funnel by stages</title>
                <!-- Fresh Leads -->
                <polygon points="20,0 400,0 270,128 150,128" fill="#3b82f6" opacity=".85" rx="4"/>
                <text x="210" y="50" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="13" font-weight="600" fill="#ffffff">Fresh Leads</text>
                <text x="210" y="68" text-anchor="middle" font-family="DM Mono, monospace" font-size="12" fill="rgba(255,255,255,.8)">40,939</text>
                <text x="210" y="84" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="11" fill="rgba(255,255,255,.7)">89.60%</text>
                <!-- Connector lines to labels -->
                <line x1="290" y1="64" x2="340" y2="64" stroke="#3b82f6" stroke-width="1" stroke-dasharray="3,2" opacity=".5"/>
                <!-- Stage 2: Follow Up -->
                <rect x="150" y="132" width="120" height="16" rx="3" fill="#7c3aed" opacity=".85"/>
                <text x="210" y="144" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9.5" font-weight="500" fill="#ffffff">Follow-up Mandatory — 2.72%</text>
                <!-- Stage 3 -->
                <rect x="155" y="151" width="110" height="13" rx="3" fill="#059669" opacity=".85"/>
                <text x="210" y="161" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9" font-weight="500" fill="#ffffff">Catalog &amp; Price Shared — 0.61%</text>
                <!-- Stage 4 -->
                <rect x="160" y="167" width="100" height="12" rx="3" fill="#d97706" opacity=".85"/>
                <text x="210" y="176.5" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9" font-weight="500" fill="#ffffff">Negotiation / Price Issue — 0.03%</text>
                <!-- Stage 5 -->
                <rect x="165" y="182" width="90" height="12" rx="3" fill="#dc2626" opacity=".85"/>
                <text x="210" y="191.5" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9" font-weight="500" fill="#ffffff">Closed Won — 0.05%</text>
              </svg>
            </div>
          </div>
        </div>

        <!-- Tags / Pie card -->
        <div class="card">
          <div class="card-head">
            <span class="card-head-title">Leads by tags</span>
          </div>
          <div class="card-body">
            <div class="pie-grid">

              <!-- Fresh Leads Pie -->
              <div class="pie-box">
                <div class="pie-label">Fresh Leads</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="Fresh leads by tag">
                    <title>Fresh leads tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,0,1,0.637,-0.771 Z" fill="#3b82f6"/>
                    <path d="M0,0 L0.637,-0.771 A1,1,0,1,1,-0.980,0.201 Z" fill="#7c3aed"/>
                    <path d="M0,0 L-0.980,0.201 A1,1,0,0,1,0,-1 Z" fill="#f59e0b"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#3b82f6;"></div><div>Out of Station <span class="legend-num">(132)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#7c3aed;"></div><div>Future Req. <span class="legend-num">(1722)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#f59e0b;"></div><div>No Season <span class="legend-num">(212)</span></div></div>
                </div>
              </div>

              <!-- In-Progress Pie -->
              <div class="pie-box">
                <div class="pie-label">In-Progress</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="In-progress by tag">
                    <title>In-progress tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,0,1,0.810,0.587 Z" fill="#ef4444"/>
                    <path d="M0,0 L0.810,0.587 A1,1,0,0,1,-0.556,0.831 Z" fill="#7c3aed"/>
                    <path d="M0,0 L-0.556,0.831 A1,1,0,0,1,-0.773,0.634 Z" fill="#6b7280"/>
                    <path d="M0,0 L-0.773,0.634 A1,1,0,0,1,0,-1 Z" fill="#f97316"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#ef4444;"></div><div>Msg Shared <span class="legend-num">(482)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#7c3aed;"></div><div>Future Req. <span class="legend-num">(227)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#6b7280;"></div><div>No Season <span class="legend-num">(23)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#f97316;"></div><div>Cust. Busy <span class="legend-num">(475)</span></div></div>
                </div>
              </div>

              <!-- Closed Lost Pie -->
              <div class="pie-box">
                <div class="pie-label">Closed Lost</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="Closed lost by tag">
                    <title>Closed lost tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,1,1,-0.317,-0.948 Z" fill="#ef4444"/>
                    <path d="M0,0 L-0.317,-0.948 A1,1,0,0,1,-0.277,-0.961 Z" fill="#3b82f6"/>
                    <path d="M0,0 L-0.277,-0.961 A1,1,0,0,1,-0.031,-1.000 Z" fill="#f59e0b"/>
                    <path d="M0,0 L-0.031,-1.000 A1,1,0,0,1,0,-1 Z" fill="#a78bfa"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#ef4444;"></div><div>No Shop <span class="legend-num">(2860)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#3b82f6;"></div><div>Invalid No. <span class="legend-num">(20)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#f59e0b;"></div><div>No Incoming <span class="legend-num">(120)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#a78bfa;"></div><div>No. N/A <span class="legend-num">(13)</span></div></div>
                </div>
              </div>

              <!-- Closed Won Pie -->
              <div class="pie-box">
                <div class="pie-label">Closed Won</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="Closed won by tag">
                    <title>Closed won tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,0,1,0.663,0.748 Z" fill="#16a34a"/>
                    <path d="M0,0 L0.663,0.748 A1,1,0,0,1,-0.934,0.358 Z" fill="#059669"/>
                    <path d="M0,0 L-0.934,0.358 A1,1,0,0,1,0,-1 Z" fill="#34d399"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#16a34a;"></div><div>Deal Closed <span class="legend-num">(5)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#059669;"></div><div>Full Payment <span class="legend-num">(4)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#34d399;"></div><div>Order Recv. <span class="legend-num">(4)</span></div></div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Right panel: Campaigns list -->
      <div class="detail-right">
        <div class="right-panel-head">
          <span class="right-panel-title">Campaigns</span>
          <div class="toggle-wrap" onclick="togglePaused(this)">
            Hide paused
            <div class="toggle" id="toggle-btn"><div class="toggle-thumb"></div></div>
          </div>
        </div>
        <div class="right-camp-list" id="right-camp-list"></div>
      </div>
    </div>
  </div>

</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/pipeline.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/callingcrm/pipeline.blade.php ENDPATH**/ ?>