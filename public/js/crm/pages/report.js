(function () {
  const reports = [
    { id: "user", name: "User Report", desc: "All in one user report", cat: "User Reports", tone: "violet" },
    { id: "activity", name: "User Activity Report", desc: "Insights into breaks information and calling metrics.", cat: "User Reports", tone: "blue" },
    { id: "lead-disposition", name: "Lead Disposition Report", desc: "Analysis of connected and not connected calls with status wise lead counts.", cat: "User Reports", tone: "green" },
    { id: "stage", name: "User Stage Report", desc: "Shows lead stage distribution by user across campaigns.", cat: "User Reports", tone: "orange" },
    { id: "user-call", name: "User Call Report", desc: "Insights into call related activities of the users.", cat: "User Reports", tone: "teal" },
    { id: "follow-up", name: "Follow-Up Report", desc: "Details like missed and due follow ups by the user are present.", cat: "User Reports", tone: "pink" },
    { id: "login", name: "Login Report", desc: "Hourly report and other login activities of the users are present.", cat: "User Reports", tone: "indigo" },
    { id: "campaign", name: "Campaign Report", desc: "Calls related data along with lost and converted count of leads.", cat: "Campaign Reports", tone: "amber" },
    { id: "campaign-lead", name: "Campaign Lead Report", desc: "Tracks campaign lead progress through statuses.", cat: "Campaign Reports", tone: "cyan" },
    { id: "campaign-stage", name: "Campaign Stage Report", desc: "Tracks campaign lead progress through stages.", cat: "Campaign Reports", tone: "slate" },
    { id: "message-activity", name: "Message Activity Report", desc: "Details of messaging activities across WhatsApp, SMS, and email.", cat: "User Reports", tone: "red" },
    { id: "user-deal", name: "User Deal Report", desc: "Track deals in-progress, won, and lost by each user.", cat: "User Reports", tone: "emerald" },
    { id: "campaign-deal", name: "Campaign Deal Report", desc: "Tracks deals in-progress, won, and lost for each campaign.", cat: "Campaign Reports", tone: "purple" },
    { id: "user-task", name: "User Task Report", desc: "Tracks user task completion, pending, and overdue tasks.", cat: "User Reports", tone: "lime" },
    { id: "campaign-source", name: "Campaign Source Report", desc: "Shows lead count by campaign and their respective sources.", cat: "Campaign Reports", tone: "rose" },
  ];

  const root = document.querySelector(".calling-crm-reports .crm-page-main");
  const table = document.getElementById("reportsDataTable");
  const tbody = document.getElementById("reportTableBody");
  const grid = document.getElementById("reportGrid");
  const tableCard = document.getElementById("reportTableCard");
  const emptyState = document.getElementById("reportsEmpty");
  const searchInput = document.querySelector(".calling-crm-reports .search-box input");
  const tabs = document.querySelectorAll(".calling-crm-reports .filter-tab");
  const viewButtons = document.querySelectorAll(".calling-crm-reports .view-btn");

  if (!root || !table || !tbody || !grid || !tableCard || !emptyState) return;

  const defaultReportRoute = root.dataset.defaultReportUrl || "#";
  const reportRoutes = {
    "User Activity Report": root.dataset.activityReportUrl || defaultReportRoute,
    "Lead Disposition Report": root.dataset.leadDispositionReportUrl || defaultReportRoute,
    "User Stage Report": root.dataset.userStageReportUrl || defaultReportRoute,
    "Login Report": root.dataset.loginReportUrl || defaultReportRoute,
    "Follow-Up Report": root.dataset.followUpReportUrl || defaultReportRoute,
    "Campaign Report": root.dataset.campaignReportUrl || defaultReportRoute,
  };
  const storageKeys = {
    favourites: "callingCrmReportFavourites",
    recent: "callingCrmReportRecent",
  };

  let activeTab = "all";
  let activeView = "list";
  let query = "";
  let favourites = readStoredSet(storageKeys.favourites);
  let recent = readStoredList(storageKeys.recent);
  let reportsDataTable = null;

  function readStoredSet(key) {
    try {
      return new Set(JSON.parse(localStorage.getItem(key) || "[]"));
    } catch (error) {
      return new Set();
    }
  }

  function readStoredList(key) {
    try {
      return JSON.parse(localStorage.getItem(key) || "[]");
    } catch (error) {
      return [];
    }
  }

  function saveStoredSet(key, value) {
    localStorage.setItem(key, JSON.stringify(Array.from(value)));
  }

  function saveStoredList(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function reportUrl(report) {
    return reportRoutes[report.name] || defaultReportRoute;
  }

  function starIcon(isStarred) {
    return `
      <svg viewBox="0 0 24 24" fill="${isStarred ? "currentColor" : "none"}" stroke="currentColor" stroke-width="2">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>
    `;
  }

  function categoryBadge(report) {
    const badgeClass = report.cat === "User Reports" ? "badge-user" : "badge-campaign";
    return `<span class="category-badge ${badgeClass}">${escapeHtml(report.cat)}</span>`;
  }

  function favouriteButton(report) {
    const starred = favourites.has(report.id);
    return `
      <button class="star-btn ${starred ? "starred" : ""}" type="button" data-report-star="${report.id}" aria-label="Toggle favourite">
        ${starIcon(starred)}
      </button>
    `;
  }

  function filteredReports() {
    let list = reports.slice();

    if (activeTab === "recent") {
      list = recent.map((id) => reports.find((report) => report.id === id)).filter(Boolean);
    }

    if (activeTab === "favourites") {
      list = list.filter((report) => favourites.has(report.id));
    }

    if (query) {
      const q = query.toLowerCase();
      list = list.filter((report) => {
        return [report.name, report.desc, report.cat].some((field) => field.toLowerCase().includes(q));
      });
    }

    return list;
  }

  function renderCounts() {
    const counts = {
      all: reports.length,
      recent: recent.length,
      favourites: favourites.size,
    };

    Object.entries(counts).forEach(([key, value]) => {
      const node = document.querySelector(`[data-tab-count="${key}"]`);
      if (node) node.textContent = value;
    });
  }

  function initDataTable() {
    if (!window.jQuery || !jQuery.fn.DataTable || reportsDataTable) return;

    reportsDataTable = jQuery(table).DataTable({
      data: [],
      autoWidth: false,
      deferRender: true,
      pageLength: -1,
      lengthMenu: [[-1, 10, 25, 50], ["All", 10, 25, 50]],
      order: [[1, "asc"]],
      dom: "<'reports-dt-top'<'reports-dt-length'l><'reports-dt-info'i>>t<'reports-dt-bottom'p>",
      columns: [
        {
          data: null,
          className: "td-sno",
          orderable: false,
          searchable: false,
          render: function () {
            return "";
          },
        },
        {
          data: "name",
          render: function (value, type, report) {
            if (type !== "display") return value;
            return `
              <div class="report-title-cell">
                <span class="report-mark report-mark-${report.tone}">${escapeHtml(value.charAt(0))}</span>
                <div>
                  <div class="report-name">${escapeHtml(value)}</div>
                  <div class="report-meta">Open report</div>
                </div>
              </div>
            `;
          },
        },
        {
          data: "desc",
          render: function (value, type) {
            return type === "display" ? `<div class="report-desc">${escapeHtml(value)}</div>` : value;
          },
        },
        {
          data: "cat",
          render: function (value, type, report) {
            return type === "display" ? categoryBadge(report) : value;
          },
        },
        {
          data: null,
          className: "favourite-col",
          orderable: false,
          searchable: false,
          render: function (_value, type, report) {
            return type === "display" ? favouriteButton(report) : "";
          },
        },
      ],
      createdRow: function (row, report) {
        row.dataset.reportId = report.id;
        row.dataset.reportOpen = "";
      },
      drawCallback: function () {
        const api = this.api();
        const pageInfo = api.page.info();

        api.column(0, { page: "current" }).nodes().each(function (cell, index) {
          cell.textContent = pageInfo.start + index + 1;
        });
      },
      language: {
        lengthMenu: "Rows _MENU_",
        info: "Showing _START_ to _END_ of _TOTAL_ reports",
        infoEmpty: "No reports available",
        emptyTable: "No reports found",
        paginate: {
          previous: "Prev",
          next: "Next",
        },
      },
    });
  }

  function renderTable(list) {
    initDataTable();

    if (reportsDataTable) {
      reportsDataTable.clear();
      reportsDataTable.rows.add(list);
      reportsDataTable.draw();
      reportsDataTable.columns.adjust();
      return;
    }

    tbody.innerHTML = list.map((report, index) => `
      <tr data-report-id="${report.id}" data-report-open>
        <td class="td-sno">${index + 1}</td>
        <td>
          <div class="report-title-cell">
            <span class="report-mark report-mark-${report.tone}">${escapeHtml(report.name.charAt(0))}</span>
            <div>
              <div class="report-name">${escapeHtml(report.name)}</div>
              <div class="report-meta">Open report</div>
            </div>
          </div>
        </td>
        <td><div class="report-desc">${escapeHtml(report.desc)}</div></td>
        <td>${categoryBadge(report)}</td>
        <td class="favourite-col">${favouriteButton(report)}</td>
      </tr>
    `).join("");
  }

  function renderGrid(list) {
    grid.innerHTML = list.map((report) => `
      <article class="report-card" data-report-id="${report.id}" data-report-open>
        ${favouriteButton(report)}
        <span class="report-mark report-mark-${report.tone}">${escapeHtml(report.name.charAt(0))}</span>
        <div class="report-card-body">
          ${categoryBadge(report)}
          <h3>${escapeHtml(report.name)}</h3>
          <p>${escapeHtml(report.desc)}</p>
        </div>
        <div class="report-card-action">
          <span>Open report</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </div>
      </article>
    `).join("");
  }

  function render() {
    const list = filteredReports();
    const hasResults = list.length > 0;

    renderCounts();
    renderTable(list);
    renderGrid(list);

    emptyState.hidden = hasResults;
    tableCard.hidden = !hasResults || activeView !== "list";
    grid.hidden = !hasResults || activeView !== "grid";

    if (reportsDataTable && activeView === "list" && hasResults) {
      reportsDataTable.columns.adjust();
    }
  }

  function openReport(id) {
    const report = reports.find((item) => item.id === id);
    if (!report) return;

    recent = [id].concat(recent.filter((recentId) => recentId !== id)).slice(0, 8);
    saveStoredList(storageKeys.recent, recent);
    window.location.href = reportUrl(report);
  }

  function toggleFavourite(id) {
    if (favourites.has(id)) {
      favourites.delete(id);
    } else {
      favourites.add(id);
    }

    saveStoredSet(storageKeys.favourites, favourites);
    render();
  }

  searchInput?.addEventListener("input", (event) => {
    query = event.target.value.trim();
    render();
  });

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      activeTab = tab.dataset.reportTab || "all";
      tabs.forEach((item) => item.classList.toggle("active", item === tab));
      render();
    });
  });

  viewButtons.forEach((button) => {
    button.addEventListener("click", () => {
      activeView = button.dataset.reportView || "list";
      viewButtons.forEach((item) => item.classList.toggle("active", item === button));
      render();
    });
  });

  document.addEventListener("click", (event) => {
    const starButton = event.target.closest("[data-report-star]");
    if (starButton) {
      event.preventDefault();
      event.stopPropagation();
      toggleFavourite(starButton.dataset.reportStar);
      return;
    }

    const reportNode = event.target.closest("[data-report-open]");
    if (reportNode) {
      openReport(reportNode.dataset.reportId);
    }
  });

  render();
})();
