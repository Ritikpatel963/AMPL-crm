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
