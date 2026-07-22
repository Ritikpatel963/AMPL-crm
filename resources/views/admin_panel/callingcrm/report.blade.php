@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@section('main-content')
<div class="calling-crm-canvas calling-crm-reports">
<main class="crm-page-main"
    data-default-report-url="{{ route('admin_panel.admin.callingcrm.report.user') }}"
    data-activity-report-url="{{ route('admin_panel.admin.callingcrm.report.user_activity') }}"
    data-lead-disposition-report-url="{{ route('admin_panel.admin.callingcrm.report.lead_disposition') }}"
    data-user-stage-report-url="{{ route('admin_panel.admin.callingcrm.report.user_stage') }}"
    data-login-report-url="{{ route('admin_panel.admin.callingcrm.report.login') }}"
    data-follow-up-report-url="{{ route('admin_panel.admin.callingcrm.report.follow_ups') }}"
    data-campaign-report-url="{{ route('admin_panel.admin.callingcrm.report.campaign') }}"
>
    <div class="reports-header">
        <div>
            <div class="page-heading">Reports</div>
        </div>
    </div>

    <div class="top-bar">
        <div class="filter-tabs">
            <button class="filter-tab active" type="button" data-report-tab="all">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>All Reports</span>
                <small data-tab-count="all">0</small>
            </button>
            <button class="filter-tab" type="button" data-report-tab="recent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Recently Viewed</span>
                <small data-tab-count="recent">0</small>
            </button>
            <button class="filter-tab" type="button" data-report-tab="favourites">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>Favourites</span>
                <small data-tab-count="favourites">0</small>
            </button>
        </div>

        <div class="right-actions">
            <button class="download-btn" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Download logs</span>
            </button>
            <div class="search-box">
                <input type="search" placeholder="Search Reports" aria-label="Search reports">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <div class="view-toggle" aria-label="Report view">
                <button class="view-btn" type="button" data-report-view="grid" title="Grid view" aria-label="Grid view">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </button>
                <button class="view-btn active" type="button" data-report-view="list" title="List view" aria-label="List view">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div class="reports-grid" id="reportGrid" hidden></div>

    <div class="table-card reports-table-card" id="reportTableCard">
        <table id="reportsDataTable" class="reports-data-table">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Report Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th class="favourite-col">Favourite</th>
                </tr>
            </thead>
            <tbody id="reportTableBody"></tbody>
        </table>
    </div>

    <div class="reports-empty" id="reportsEmpty" hidden>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6"/><path d="M9 11h2"/></svg>
        <strong>No reports found</strong>
        <span>Try another tab or search keyword.</span>
    </div>
</main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/crm/pages/report.js') }}"></script>
@endpush
