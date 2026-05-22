@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Trends')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@push('styles')
@verbatim
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endverbatim
@endpush

@include('admin_panel.callingcrm.partials.ui-polish')

@section('main-content')
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
@endsection

@push('scripts')
<script>
const labels = ['Apr 20 - Apr 26', 'Apr 27 - May 3', 'May 4 - May 10', 'May 11 - May 17', 'May 18 - May 24'];

const commonOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        font: { family: 'DM Sans', size: 11 },
        padding: 16,
        usePointStyle: true,
        pointStyleWidth: 10,
        boxHeight: 8,
      }
    },
    tooltip: {
      backgroundColor: '#1a1d2e',
      titleFont: { family: 'DM Sans', size: 12 },
      bodyFont:  { family: 'DM Sans', size: 12 },
      padding: 10,
      cornerRadius: 8,
    }
  },
  scales: {
    x: {
      ticks: {
        font: { family: 'DM Sans', size: 10 },
        maxRotation: 45,
        minRotation: 45,
        color: '#6b7280',
      },
      grid: { display: false },
      border: { color: '#e3e6ef' }
    },
    y: {
      ticks: { font: { family: 'DM Sans', size: 10 }, color: '#6b7280' },
      grid: { color: '#f0f2f7' },
      border: { display: false }
    }
  }
};

// Chart 1 — Total Calls vs Calls Connected (grouped bar)
new Chart(document.getElementById('chart1'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Total Calls',
        data: [11200, 12800, 14600, 15300, 8871],
        backgroundColor: '#294FCF',
        borderRadius: 4,
        barPercentage: 0.45,
        categoryPercentage: 0.75,
      },
      {
        label: 'Total Calls Connected',
        data: [4800, 4950, 5200, 5300, 992],
        backgroundColor: '#8EFA50',
        borderRadius: 4,
        barPercentage: 0.45,
        categoryPercentage: 0.75,
      }
    ]
  },
  options: { ...commonOptions }
});

// Chart 2 — Total Call Duration (single bar)
new Chart(document.getElementById('chart2'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Total Call Time',
        data: [4700, 5100, 5800, 6100, 2592],
        backgroundColor: '#294FCF',
        borderRadius: 4,
        barPercentage: 0.5,
        categoryPercentage: 0.7,
      }
    ]
  },
  options: { ...commonOptions }
});

// Chart 4 — Leads Added (single bar)
new Chart(document.getElementById('chart4'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Total Leads Added',
        data: [0, 38000, 80, 50, 30],
        backgroundColor: '#294FCF',
        borderRadius: 4,
        barPercentage: 0.5,
        categoryPercentage: 0.7,
      }
    ]
  },
  options: {
    ...commonOptions,
    scales: {
      ...commonOptions.scales,
      y: {
        ...commonOptions.scales.y,
        ticks: {
          ...commonOptions.scales.y.ticks,
          callback: v => v >= 1000 ? (v/1000) + 'k' : v
        }
      }
    }
  }
});

// Chart 5 — Lead Sources (spline/line)
new Chart(document.getElementById('chart5'), {
  type: 'line',
  data: {
    labels,
    datasets: [
      {
        label: 'File Upload',
        data: [3200, 47800, 0, 0, 0],
        borderColor: '#294FCF',
        backgroundColor: 'rgba(41,79,207,0.08)',
        pointBackgroundColor: '#294FCF',
        pointRadius: 4,
        tension: 0.4,
        fill: false,
      },
      {
        label: 'Walk in Lead',
        data: [120, 115, 130, 125, 118],
        borderColor: '#8EFA50',
        backgroundColor: 'rgba(142,250,80,0.08)',
        pointBackgroundColor: '#8EFA50',
        pointStyle: 'rectRot',
        pointRadius: 5,
        tension: 0.4,
        fill: false,
      }
    ]
  },
  options: {
    ...commonOptions,
    scales: {
      ...commonOptions.scales,
      y: {
        ...commonOptions.scales.y,
        ticks: {
          ...commonOptions.scales.y.ticks,
          callback: v => v >= 1000 ? (v/1000) + 'k' : v
        }
      }
    }
  }
});

// Switch button toggle (visual only)
document.querySelectorAll('.switch-wrapper').forEach(wrapper => {
  wrapper.querySelectorAll('.switch-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      wrapper.querySelectorAll('.switch-btn').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
    });
  });
});
</script>
@endpush



@push('scripts')
<script src="{{ asset('js/crm/crm-core.js') }}"></script>
<script type="module" src="{{ asset('js/crm/campaigns.js') }}"></script>
@endpush
