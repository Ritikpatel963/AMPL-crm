@extends('admin_panel.layout.app')

@section('title', 'Calling CRM User Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@section('main-content')
<div class="calling-crm-canvas"><main class="crm-page-main">

  <!-- Title row -->
  <div class="top-content">
    <button class="back-btn" onclick="window.location='{{ route('admin_panel.admin.callingcrm.report') }}'" title="Go back">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <h3 class="page-title">User Report</h3>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="toolbar-left">
      <button class="filter-btn applied">
        Today
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <button class="filter-btn">
        Reporting Manager
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <button class="filter-btn">
        Users
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
    </div>
    <button class="download-icon-btn" title="Download CSV Report">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    </button>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-wrapper">
      <table>
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
        <tbody id="reportBody"></tbody>
      </table>
    </div>
  </div>
</main></div>
@endsection

@push('scripts')
<script src="{{ asset('js/crm/pages/user-report.js') }}"></script>
@endpush
