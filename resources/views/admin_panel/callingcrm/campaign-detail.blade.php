@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Campaign')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@section('main-content')
<div class="calling-crm-canvas campaign-detail-app" data-campaign-id="{{ $campaignId }}">
  <div class="campaign-detail-topbar">
    <div class="campaign-detail-title-row">
      <button type="button" class="campaign-back-btn" onclick="window.location='{{ route('admin_panel.admin.callingcrm.pipeline') }}'" aria-label="Back to campaigns">
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/crm/pages/campaign-detail.js') }}"></script>
@endpush
