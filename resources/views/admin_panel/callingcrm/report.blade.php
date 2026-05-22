@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@section('main-content')
<div class="calling-crm-canvas"><main class="crm-page-main">
  <div class="page-heading">Reports</div>

  <div class="top-bar">
    <div class="filter-tabs">
      <button class="filter-tab active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        All Reports
      </button>
      <button class="filter-tab">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Recently Viewed
      </button>
      <button class="filter-tab">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Favourites
      </button>
    </div>

    <div class="right-actions">
      <button class="download-btn">Download logs</button>
      <div class="search-box">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" placeholder="Search Reports">
      </div>
      <div class="view-toggle">
        <button class="view-btn">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        </button>
        <button class="view-btn active">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </button>
      </div>
    </div>
  </div>

  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>S.No.</th>
          <th>Report Name</th>
          <th>Description</th>
          <th>Category</th>
          <th>★</th>
        </tr>
      </thead>
      <tbody id="reportTableBody"></tbody>
    </table>
  </div>
</main></div>
@endsection

@push('scripts')
<script src="{{ asset('js/crm/pages/report.js') }}"></script>
@endpush
