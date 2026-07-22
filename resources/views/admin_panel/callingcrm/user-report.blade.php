@extends('admin_panel.layout.app')

@section('title', 'Calling CRM User Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@section('main-content')
<div class="calling-crm-canvas calling-crm-user-report">
<main class="crm-page-main" data-report-list-url="{{ route('admin_panel.admin.callingcrm.report') }}">
    <div class="user-report-heading">
        <button class="user-report-back" type="button" data-report-back aria-label="Back to reports">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <h3>User Report</h3>
    </div>

    <div class="user-report-toolbar">
        <div class="user-report-filters">
            <div class="report-filter" data-filter-menu="date">
                <button class="report-filter-btn active" type="button" data-filter-toggle>
                    <span data-date-label>Today</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu date-menu">
                    <div class="filter-menu-title">Choose Disposition Date</div>
                    <div class="date-range-options">
                        <label class="date-range-option">
                            <input type="radio" name="user_report_date_range" value="today" data-date-range-option checked>
                            <span>Today</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_report_date_range" value="yesterday" data-date-range-option>
                            <span>Yesterday</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_report_date_range" value="last7" data-date-range-option>
                            <span>Last 7 days</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_report_date_range" value="last30" data-date-range-option>
                            <span>Last 30 days</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_report_date_range" value="month" data-date-range-option>
                            <span>This Month</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_report_date_range" value="custom" data-date-range-option>
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

            <div class="report-filter" data-filter-menu="managers">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-manager-label>Reporting manager</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Reporting manager</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="managers">
                    <div class="filter-options" data-manager-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
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

        <button class="user-report-download" type="button" data-report-download title="Download Excel Report" aria-label="Download Excel Report">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </button>
    </div>

    <div class="user-report-table-card">
        <table id="userReportDataTable" class="user-report-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>User Name</th>
                    <th>Reporting Manager</th>
                    <th>Mobile Number</th>
                    <th>Date</th>
                    <th>Total Calls</th>
                    <th>Total Calls Connected</th>
                    <th>Total Unconnected Calls</th>
                    <th>Total Outgoing Calls</th>
                    <th>Outgoing Connected Calls</th>
                    <th>Outgoing Unanswered Calls</th>
                    <th>Avg. Outgoing Call Duration</th>
                    <th>Total Incoming Calls</th>
                    <th>Incoming Connected Calls</th>
                    <th>Incoming Unanswered Calls</th>
                    <th>Avg. Incoming Call Duration</th>
                    <th>Total Disposed Count</th>
                    <th>Disposed Yes Connected Count</th>
                    <th>Disposed Not Connected Count</th>
                    <th>Total In-Progress Leads</th>
                    <th>Total Converted Leads</th>
                    <th>Total Lost Leads</th>
                    <th>Follow-Ups Due Today</th>
                    <th>Avg. Start Calling Time</th>
                    <th>Avg. Call Duration</th>
                    <th>Avg. Form Filling Time</th>
                    <th>Total Call Duration</th>
                    <th>Total Number of Breaks</th>
                    <th>Total Break Duration</th>
                    <th>Total Whatsapp Sent</th>
                    <th>Total Emails Sent</th>
                    <th>Total SMS Sent</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/crm/pages/user-report.js') }}"></script>
@endpush
