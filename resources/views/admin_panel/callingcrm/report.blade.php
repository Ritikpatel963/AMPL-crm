@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@include('admin_panel.callingcrm.partials.ui-polish')

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
<script>
const reports = [
  { name: "User Report",            desc: "All in one user report",                                               cat: "User Reports" },
  { name: "User Activity Report",   desc: "Insights into Breaks information and calling metrics.",                cat: "User Reports" },
  { name: "Lead Disposition Report",desc: "Analysis of calls connected, not connected along with status wise count of leads.", cat: "User Reports" },
  { name: "User Stage Report",      desc: "Shows lead stage distribution by user across campaigns.",              cat: "User Reports" },
  { name: "User Call Report",       desc: "Insights into call related activities of the users.",                  cat: "User Reports" },
  { name: "Follow-Up Report",       desc: "Details like missed and due follow ups by the user are present.",      cat: "User Reports" },
  { name: "Login Report",           desc: "Hourly report and other login activities of the users are present.",   cat: "User Reports" },
  { name: "Campaign Report",        desc: "Calls related data along with lost and converted count of leads.",     cat: "Campaign Reports" },
  { name: "Campaign Lead Report",   desc: "Tracks campaign lead progress through statuses.",                     cat: "Campaign Reports" },
  { name: "Campaign Stage Report",  desc: "Tracks campaign lead progress through stages.",                       cat: "Campaign Reports" },
  { name: "Message Activity Report",desc: "Details of messaging activities across channels like WhatsApp, SMS, and email.", cat: "User Reports" },
  { name: "User Deal Report",       desc: "Track deals in-progress, won, and lost by each user.",                cat: "User Reports" },
  { name: "Campaign Deal Report",   desc: "Tracks deals in-progress, won, and lost for each campaign.",         cat: "Campaign Reports" },
  { name: "User Task Report",       desc: "Tracks user task completion, pending, and overdue tasks.",            cat: "User Reports" },
  { name: "Campaign Source Report", desc: "Shows lead count by campaign and their respective sources.",          cat: "Campaign Reports" },
];

const tbody = document.getElementById('reportTableBody');
const starred = new Set();
const defaultReportRoute = "{{ route('admin_panel.admin.callingcrm.report.user') }}";
const reportRoutes = {
  "Login Report": "{{ route('admin_panel.admin.callingcrm.report.login') }}",
};

function render(list) {
  tbody.innerHTML = list.map((r, i) => `
    <tr onclick="window.location='${reportRoutes[r.name] || defaultReportRoute}'">
      <td class="td-sno">${i + 1}</td>
      <td><div class="report-name">${r.name}</div></td>
      <td><div class="report-desc">${r.desc}</div></td>
      <td><span class="category-badge ${r.cat === 'User Reports' ? 'badge-user' : 'badge-campaign'}">${r.cat}</span></td>
      <td>
        <button class="star-btn ${starred.has(i) ? 'starred' : ''}" onclick="event.stopPropagation(); toggleStar(${i}, this)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="${starred.has(i) ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </button>
      </td>
    </tr>
  `).join('');
}

function toggleStar(idx, btn) {
  if (starred.has(idx)) starred.delete(idx); else starred.add(idx);
  btn.classList.toggle('starred');
  const svg = btn.querySelector('svg');
  svg.setAttribute('fill', starred.has(idx) ? 'currentColor' : 'none');
}

// Search
document.querySelector('.search-box input').addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  render(reports.filter(r => r.name.toLowerCase().includes(q) || r.desc.toLowerCase().includes(q)));
});

// Filter tabs
document.querySelectorAll('.filter-tab').forEach((tab, i) => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    if (i === 0) render(reports);
    else if (i === 2) render(reports.filter((_, idx) => starred.has(idx)));
    else render(reports);
  });
});

render(reports);
</script>
@endpush



@push('scripts')
<script src="{{ asset('js/crm/crm-core.js') }}"></script>
<script type="module" src="{{ asset('js/crm/campaigns.js') }}"></script>
@endpush
