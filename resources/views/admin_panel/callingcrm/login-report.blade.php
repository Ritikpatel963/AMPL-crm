@extends('admin_panel.layout.app')

@section('title', 'Hourly & Login Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@section('main-content')
<div class="calling-crm-canvas login-report-page" data-report-login-endpoint="reports/login" data-report-hourly-endpoint="reports/hourly" data-report-day-endpoint="reports/day">
  <div class="login-title-row">
    <button type="button" class="login-back-btn" onclick="window.location='{{ route('admin_panel.admin.callingcrm.report') }}'" aria-label="Back to reports">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path d="M15 18 9 12l6-6"/><path d="M20 12H9"/></svg>
    </button>
    <h1 class="login-page-title">Hourly &amp; Login Report</h1>
  </div>

  <div class="user-report-toolbar" style="margin-bottom: 20px;">
    <div class="user-report-filters">
        <div class="report-filter" data-filter-menu="date">
            <button class="report-filter-btn active" type="button" data-filter-toggle>
                <span data-date-label>Today</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="report-filter-menu date-menu">
                <div class="filter-menu-title">Choose Date</div>
                <div class="date-range-options">
                    <label class="date-range-option">
                        <input type="radio" name="login_report_date_range" value="today" data-date-range-option checked>
                        <span>Today</span>
                    </label>
                    <label class="date-range-option">
                        <input type="radio" name="login_report_date_range" value="yesterday" data-date-range-option>
                        <span>Yesterday</span>
                    </label>
                    <label class="date-range-option">
                        <input type="radio" name="login_report_date_range" value="last7" data-date-range-option>
                        <span>Last 7 days</span>
                    </label>
                    <label class="date-range-option">
                        <input type="radio" name="login_report_date_range" value="last30" data-date-range-option>
                        <span>Last 30 days</span>
                    </label>
                    <label class="date-range-option">
                        <input type="radio" name="login_report_date_range" value="month" data-date-range-option>
                        <span>This Month</span>
                    </label>
                    <label class="date-range-option">
                        <input type="radio" name="login_report_date_range" value="custom" data-date-range-option>
                        <span>Custom Range</span>
                    </label>
                </div>
                <div class="custom-date-range" data-custom-date-range hidden>
                    <input type="date" data-custom-date-from aria-label="From date">
                    <input type="date" data-custom-date-to aria-label="To date">
                </div>
                <div class="filter-menu-footer">
                    <button class="filter-apply-btn" type="button" data-date-apply>Apply</button>
                </div>
            </div>
        </div>

        <div class="report-filter" data-filter-menu="users">
            <button class="report-filter-btn" type="button" data-filter-toggle>
                <span data-user-label>Users</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="report-filter-menu multi-menu">
                <div class="filter-menu-title">Choose Users</div>
                <input class="filter-search" type="search" placeholder="Search" data-filter-search="users">
                <div class="filter-options" data-user-options></div>
                <div class="filter-menu-footer">
                    <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                </div>
            </div>
        </div>
    </div>
  </div>

  <div class="login-report-grid">
    <section class="login-panel login-panel-wide">
      <div class="login-panel-head">
        <div class="login-panel-title">Hourly Report</div>
        <button type="button" class="login-download-btn">Download Hourly Report</button>
      </div>
      <div class="login-panel-body" style="height: 300px;">
         <canvas id="hourlyChart"></canvas>
      </div>
    </section>

    <section class="login-panel login-panel-wide">
      <div class="login-panel-head">
        <div class="login-panel-title">Day Report</div>
      </div>
      <div class="login-day-body" style="height: 300px; padding: 20px;">
         <canvas id="dayChart"></canvas>
      </div>
    </section>

    <section class="login-panel login-panel-wide">
      <div class="login-panel-head">
        <div class="login-panel-title">Login Report</div>
      </div>
      <div class="login-panel-body" style="padding: 0;">
        <table id="loginReportTable" class="user-report-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>IP Address</th>
                    <th>Device</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
      </div>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/crm/pages/login-report.js') }}"></script>
@endpush
