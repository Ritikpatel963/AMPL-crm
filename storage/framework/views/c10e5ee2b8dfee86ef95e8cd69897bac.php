<?php $__env->startSection('title', 'Calling CRM Trends'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas"><main class="crm-page-main">
  <div class="page-heading">Business Trends</div>

  <!-- Actions -->
  <div class="actions-header">
    <button class="filter-btn applied">
      Last 30 Days
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <button class="request-btn">
      Request Graph ⭐
    </button>
  </div>

  <!-- Widgets -->
  <div class="widgets-strip">

    <div class="widget-card">
      <div class="widget-title">Total SMS Sent</div>
      <div class="widget-date">Apr 20 to May 20</div>
      <div class="widget-main">
        <div class="widget-total">5</div>
        <span class="widget-pct neutral">0%</span>
      </div>
      <div class="widget-compare">
        <span class="widget-compare-label">Compared To: Mar 21 to Apr 19</span>
        <span class="widget-compare-val">0</span>
      </div>
    </div>

    <div class="widget-card">
      <div class="widget-title">Total Calls</div>
      <div class="widget-date">Apr 20 to May 20</div>
      <div class="widget-main">
        <div class="widget-total">52,771</div>
        <span class="widget-pct positive">31.27% ↑</span>
      </div>
      <div class="widget-compare">
        <span class="widget-compare-label">Compared To: Mar 21 to Apr 19</span>
        <span class="widget-compare-val">40,201</span>
      </div>
    </div>

    <div class="widget-card">
      <div class="widget-title">Total Converted Leads</div>
      <div class="widget-date">Apr 20 to May 20</div>
      <div class="widget-main">
        <div class="widget-total">157</div>
        <span class="widget-pct negative">28.64% ↓</span>
      </div>
      <div class="widget-compare">
        <span class="widget-compare-label">Compared To: Mar 21 to Apr 19</span>
        <span class="widget-compare-val">220</span>
      </div>
    </div>

    <div class="widget-card">
      <div class="widget-title">Total Call Time</div>
      <div class="widget-date">Apr 20 to May 20</div>
      <div class="widget-main">
        <div class="widget-total" style="font-size:17px;">20,292.33 Mins</div>
        <span class="widget-pct positive">39.01% ↑</span>
      </div>
      <div class="widget-compare">
        <span class="widget-compare-label">Compared To: Mar 21 to Apr 19</span>
        <span class="widget-compare-val">14,598.12 Mins</span>
      </div>
    </div>

    <div class="widget-card">
      <div class="widget-title">Total Calls Connected</div>
      <div class="widget-date">Apr 20 to May 20</div>
      <div class="widget-main">
        <div class="widget-total">21,242</div>
        <span class="widget-pct positive">27.74% ↑</span>
      </div>
      <div class="widget-compare">
        <span class="widget-compare-label">Compared To: Mar 21 to Apr 19</span>
        <span class="widget-compare-val">16,629</span>
      </div>
    </div>

    <div class="widget-card">
      <div class="widget-title">Total Lost Leads</div>
      <div class="widget-date">Apr 20 to May 20</div>
      <div class="widget-main">
        <div class="widget-total">5,024</div>
        <span class="widget-pct positive">23.8% ↑</span>
      </div>
      <div class="widget-compare">
        <span class="widget-compare-label">Compared To: Mar 21 to Apr 19</span>
        <span class="widget-compare-val">4,058</span>
      </div>
    </div>

  </div>

  <!-- Charts -->
  <div class="charts-grid">

    <!-- Chart 1: Total Calls vs Calls Connected -->
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title-row">
          <span class="chart-title">Total Calls Vs Calls Connected</span>
          <span class="info-icon">ℹ</span>
        </div>
        <div class="chart-controls">
          <div class="switch-wrapper">
            <button class="switch-btn selected">📈</button>
            <button class="switch-btn">⊞</button>
          </div>
          <button class="filter-btn applied" style="padding:5px 10px;font-size:12px;">
            Last 30 Days <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="chart1"></canvas>
      </div>
    </div>

    <!-- Chart 2: Total Call Duration -->
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title-row">
          <span class="chart-title">Total Call Duration</span>
          <span class="info-icon">ℹ</span>
        </div>
        <div class="chart-controls">
          <div class="switch-wrapper">
            <button class="switch-btn selected">📈</button>
            <button class="switch-btn">⊞</button>
          </div>
          <button class="filter-btn applied" style="padding:5px 10px;font-size:12px;">
            Last 30 Days <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="chart2"></canvas>
      </div>
    </div>

    <!-- Chart 3: Conversion Ratio — no data -->
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title-row">
          <span class="chart-title">Conversion Ratio</span>
          <span class="info-icon">ℹ</span>
        </div>
        <div class="chart-controls">
          <div class="switch-wrapper">
            <button class="switch-btn selected">📈</button>
            <button class="switch-btn">⊞</button>
          </div>
          <button class="filter-btn applied" style="padding:5px 10px;font-size:12px;">
            Last 30 Days <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-body">
        <div class="no-data">No data available for the selected date range</div>
      </div>
    </div>

    <!-- Chart 4: Leads Added -->
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title-row">
          <span class="chart-title">Leads Added</span>
          <span class="info-icon">ℹ</span>
        </div>
        <div class="chart-controls">
          <div class="switch-wrapper">
            <button class="switch-btn selected">📈</button>
            <button class="switch-btn">⊞</button>
          </div>
          <button class="filter-btn applied" style="padding:5px 10px;font-size:12px;">
            Last 30 Days <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="chart4"></canvas>
      </div>
    </div>

    <!-- Chart 5: Lead Sources -->
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title-row">
          <span class="chart-title">Lead Sources</span>
          <span class="info-icon">ℹ</span>
        </div>
        <div class="chart-controls">
          <div class="switch-wrapper">
            <button class="switch-btn selected">📈</button>
            <button class="switch-btn">⊞</button>
          </div>
          <button class="filter-btn applied" style="padding:5px 10px;font-size:12px;">
            Last 30 Days <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="chart5"></canvas>
      </div>
    </div>

    <!-- Chart 6: Lost Leads — no data -->
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title-row">
          <span class="chart-title">Lost Leads</span>
          <span class="info-icon">ℹ</span>
        </div>
        <div class="chart-controls">
          <div class="switch-wrapper">
            <button class="switch-btn selected">📈</button>
            <button class="switch-btn">⊞</button>
          </div>
          <button class="filter-btn applied" style="padding:5px 10px;font-size:12px;">
            Last 30 Days <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-body">
        <div class="no-data">No data available for the selected date range</div>
      </div>
    </div>

  </div>
</main></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/trends.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/callingcrm/trends.blade.php ENDPATH**/ ?>