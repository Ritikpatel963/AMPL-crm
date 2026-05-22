@extends('admin_panel.layout.app')

@section('title', 'Hourly & Login Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@include('admin_panel.callingcrm.partials.ui-polish')

@section('main-content')
<div class="login-report-page">
  <div class="login-title-row">
    <button type="button" class="login-back-btn" onclick="window.location='{{ route('admin_panel.admin.callingcrm.report') }}'" aria-label="Back to reports">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path d="M15 18 9 12l6-6"/><path d="M20 12H9"/></svg>
    </button>
    <h1 class="login-page-title">Hourly &amp; Login Report</h1>
  </div>

  <div class="login-report-grid">
    <section class="login-panel login-panel-wide">
      <div class="login-panel-head">
        <div class="login-panel-title">Hourly Report</div>
        <button type="button" class="login-download-btn">Download Hourly Report</button>
      </div>
      <div class="login-panel-body">
        <div class="login-empty">No data available.</div>
      </div>
    </section>

    <section class="login-panel">
      <div class="login-panel-head">
        <div class="login-panel-title">Login Report</div>
      </div>
      <div class="login-panel-body">
        <div class="login-form-row">
          <div class="login-control">
            <span>User *</span>
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 7.5 10 12l4.5-4.5H5.5z"/></svg>
          </div>
          <div class="login-control login-date-control">
            <span>20/5/2026 - 20/5/2026</span>
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M6 2v2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H8V2H6zm10 7H4v7h12V9z"/></svg>
          </div>
          <button type="button" class="login-go-btn">Go</button>
        </div>
      </div>
    </section>

    <section class="login-panel">
      <div class="login-panel-head">
        <div class="login-panel-title">Day Report</div>
      </div>
      <div class="login-day-body">
        <div class="login-empty">No data available.</div>
      </div>
    </section>
  </div>
</div>
@endsection



@push('scripts')
<script src="{{ asset('js/crm/crm-core.js') }}"></script>
<script type="module" src="{{ asset('js/crm/campaigns.js') }}"></script>
@endpush
