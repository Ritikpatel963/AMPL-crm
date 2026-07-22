@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Campaign Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@section('main-content')
<div class="calling-crm-canvas calling-crm-user-report" data-report-endpoint="reports/campaign">
<main class="crm-page-main" data-report-list-url="{{ route('admin_panel.admin.callingcrm.report') }}">
    <div class="user-report-heading">
        <button class="user-report-back" type="button" onclick="window.location='{{ route('admin_panel.admin.callingcrm.report') }}'" aria-label="Back to reports">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <h3>Campaign Report</h3>
    </div>

    <div class="user-report-toolbar">
        <div class="user-report-filters">
            <!-- Pipeline Filter -->
            <div class="report-filter" data-filter-menu="pipelines">
                <button class="report-filter-btn active" type="button" data-filter-toggle>
                    <span data-pipeline-label>All Pipelines</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Pipeline</div>
                    <div class="filter-options" data-pipeline-options>
                        <!-- Options will be populated by JS if needed or a simple text input can be used -->
                        <div style="padding: 10px;">
                            <input type="number" name="campaign_pipeline_id" placeholder="Pipeline ID" class="form-control" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
                        </div>
                    </div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-pipeline-apply>Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="user-report-table-card">
        <table id="campaignReportTable" class="user-report-table">
            <thead>
                <tr>
                    <th>Campaign Name</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Total Leads</th>
                    <th>Converted Leads</th>
                    <th>Lost Leads</th>
                    <th>Total Calls</th>
                    <th>Connected Calls</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        
        <div class="pagination-controls" style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div class="pagination-info" data-pagination-info></div>
            <div class="pagination-buttons" data-pagination-buttons></div>
        </div>
    </div>
</main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/crm/pages/campaign-report.js') }}"></script>
@endpush
