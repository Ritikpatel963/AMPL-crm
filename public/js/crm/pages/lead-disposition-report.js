(function () {
  const root = document.querySelector(".calling-crm-lead-disposition-report");
  const table = document.getElementById("leadDispositionReportDataTable");

  if (!root || !table) return;

  const state = {
    dateRange: "today",
    from: "",
    to: "",
    users: [],
    selectedManagers: new Set(),
    selectedUsers: new Set(),
    pendingDateRange: "today",
    table: null,
  };

  const dateLabel = root.querySelector("[data-date-label]");
  const managerLabel = root.querySelector("[data-manager-label]");
  const userLabel = root.querySelector("[data-user-label]");
  const managerOptions = root.querySelector("[data-manager-options]");
  const userOptions = root.querySelector("[data-user-options]");
  const backBtn = root.querySelector("[data-report-back]");
  const customDateRange = root.querySelector("[data-custom-date-range]");
  const customDateFrom = root.querySelector("[data-custom-date-from]");
  const customDateTo = root.querySelector("[data-custom-date-to]");

  const columns = [
    { key: "sno", label: "No." },
    { key: "disposed_at", label: "Disposed At" },
    { key: "user_name", label: "User Name" },
    { key: "manager", label: "Reporting Manager" },
    { key: "user_phone", label: "Mobile Number" },
    { key: "lead_name", label: "Lead Name" },
    { key: "lead_phone", label: "Lead Phone" },
    { key: "campaign", label: "Campaign" },
    { key: "call_status", label: "Call Status" },
    { key: "recording", label: "Recording" },
    { key: "disposition", label: "Disposition" },
    { key: "stage", label: "Stage" },
    { key: "tag", label: "Tag" },
    { key: "remark", label: "Remark" },
  ];

  function crm(path) {
    if (typeof window.callingCrmRequest === "function") {
      return window.callingCrmRequest(path);
    }

    return fetch(path, { headers: { Accept: "application/json" } }).then((response) => response.json());
  }

  function unwrap(payload) {
    return payload?.data?.data || payload?.data || [];
  }

  function todayParts() {
    const now = new Date();
    return {
      year: now.getFullYear(),
      month: now.getMonth(),
      date: now.getDate(),
    };
  }

  function isoDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  function displayDate(value) {
    if (!value) return "--";
    const date = new Date(`${String(value).slice(0, 10)}T00:00:00`);
    return date.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
  }

  function displayDateTime(value) {
    if (!value) return "--";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "--";

    return date.toLocaleString("en-GB", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function titleCase(value) {
    return String(value || "--")
      .replace(/_/g, " ")
      .replace(/\b\w/g, (letter) => letter.toUpperCase());
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function dateRangeSettings(range) {
    const current = todayParts();
    let from = new Date(current.year, current.month, current.date);
    let to = new Date(current.year, current.month, current.date);
    let label = "Today";

    if (range === "yesterday") {
      from.setDate(from.getDate() - 1);
      to.setDate(to.getDate() - 1);
      label = "Yesterday";
    } else if (range === "last7") {
      from.setDate(from.getDate() - 6);
      label = "Last 7 days";
    } else if (range === "last30") {
      from.setDate(from.getDate() - 29);
      label = "Last 30 days";
    } else if (range === "month") {
      from = new Date(current.year, current.month, 1);
      label = "This Month";
    }

    return { from: isoDate(from), to: isoDate(to), label };
  }

  function setDateRange(range, customFrom, customTo) {
    const settings = dateRangeSettings(range);

    state.dateRange = range;
    state.from = range === "custom" && customFrom ? customFrom : settings.from;
    state.to = range === "custom" && customTo ? customTo : settings.to;
    if (dateLabel) dateLabel.textContent = range === "custom" ? `${displayDate(state.from)} - ${displayDate(state.to)}` : settings.label;
  }

  function syncDateMenu() {
    state.pendingDateRange = state.dateRange;

    root.querySelectorAll("[data-date-range-option]").forEach((input) => {
      input.checked = input.value === state.dateRange;
    });

    if (customDateFrom) customDateFrom.value = state.from;
    if (customDateTo) customDateTo.value = state.to;
    if (customDateRange) customDateRange.hidden = state.dateRange !== "custom";
  }

  function applyPendingDateRange() {
    const range = state.pendingDateRange;

    if (range === "custom") {
      let from = customDateFrom?.value || state.from;
      let to = customDateTo?.value || from;

      if (from > to) {
        [from, to] = [to, from];
      }

      setDateRange("custom", from, to);
    } else {
      setDateRange(range);
    }

    closeMenus();
    loadReport();
  }

  function selectedLabel(base, selectedSet) {
    if (!selectedSet.size) return base;
    if (selectedSet.size === 1) return "1 selected";
    return `${selectedSet.size} selected`;
  }

  function updateFilterLabels() {
    if (managerLabel) managerLabel.textContent = selectedLabel("Reporting manager", state.selectedManagers);
    if (userLabel) userLabel.textContent = selectedLabel("Users", state.selectedUsers);
  }

  function uniqueManagers() {
    const managers = new Map();
    managers.set("none", { id: "none", name: "No Manager" });

    state.users.forEach((user) => {
      if (user.reporting_manager?.id) {
        managers.set(String(user.reporting_manager.id), {
          id: String(user.reporting_manager.id),
          name: user.reporting_manager.name,
        });
      }
    });

    return Array.from(managers.values()).sort((a, b) => a.name.localeCompare(b.name));
  }

  function optionMarkup(item, type, checked) {
    return `
      <label class="filter-option" data-option-name="${escapeHtml(item.name.toLowerCase())}">
        <input type="checkbox" value="${escapeHtml(item.id)}" data-filter-option="${type}" ${checked ? "checked" : ""}>
        <span>${escapeHtml(item.name)}</span>
      </label>
    `;
  }

  function renderFilterOptions() {
    const managers = uniqueManagers();

    if (managerOptions) {
      managerOptions.innerHTML = [
        optionMarkup({ id: "__all", name: "Select all" }, "managers", managers.length && state.selectedManagers.size === managers.length),
        ...managers.map((manager) => optionMarkup(manager, "managers", state.selectedManagers.has(String(manager.id)))),
      ].join("");
    }

    if (userOptions) {
      userOptions.innerHTML = [
        optionMarkup({ id: "__all", name: "Select all" }, "users", state.users.length && state.selectedUsers.size === state.users.length),
        ...state.users.map((user) => optionMarkup({ id: String(user.id), name: user.name || "Unnamed User" }, "users", state.selectedUsers.has(String(user.id)))),
      ].join("");
    }
  }

  function initDataTable() {
    if (!window.jQuery || !jQuery.fn.DataTable || state.table) return;

    state.table = jQuery(table).DataTable({
      data: [],
      autoWidth: false,
      deferRender: true,
      processing: true,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      scrollX: true,
      scrollCollapse: true,
      dom: "rt<'user-report-dt-footer'lip>",
      order: [[1, "desc"]],
      columns: columns.map((column, index) => ({
        data: column.key,
        title: column.label,
        width: column.key === "sno" ? "64px" : (column.key === "remark" ? "280px" : (column.key === "recording" ? "180px" : null)),
        className: ["call_status", "recording", "disposition", "stage", "tag", "remark"].includes(column.key) ? "metric-cell" : "",
        orderable: column.key !== "sno" && column.key !== "recording",
        searchable: column.key !== "sno" && column.key !== "recording",
        render: function (value, type, row) {
          if (column.key === "sno") return "";
          if (type !== "display") return value;
          if (column.key === "user_name" || column.key === "lead_name") return `<strong>${escapeHtml(value)}</strong>`;
          if (column.key === "remark") return `<div class="report-desc">${escapeHtml(value)}</div>`;
          if (column.key === "recording") {
            if (!value) return '<span class="text-muted">--</span>';
            var safeUrl = String(value).replace(/&/g,'&amp;').replace(/"/g,'&quot;');
            return '<button class="crm-play-btn" data-audio-url="' + safeUrl + '" type="button">'
              + '<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>'
              + ' Play</button>';
          }
          return escapeHtml(value);
        },
      })),
      drawCallback: function () {
        const api = this.api();
        const pageInfo = api.page.info();

        api.column(0, { page: "current" }).nodes().each(function (cell, index) {
          cell.textContent = pageInfo.start + index + 1;
        });
      },
      language: {
        lengthMenu: "Items per page: _MENU_",
        info: "_START_ - _END_ of _TOTAL_",
        infoEmpty: "0 - 0 of 0",
        emptyTable: "No lead disposition data found",
        loadingRecords: "Loading lead dispositions...",
        processing: "Loading lead dispositions...",
        paginate: {
          first: "|<",
          previous: "<",
          next: ">",
          last: ">|",
        },
      },
    });
  }

  function renderTable(rows) {
    initDataTable();

    if (!state.table) return;

    state.table.clear();
    state.table.rows.add(rows);
    state.table.draw();
    state.table.columns.adjust();
  }

  function normaliseRow(row) {
    return {
      id: row.id,
      disposed_at: displayDateTime(row.disposed_at),
      user_name: row.user?.name || "--",
      manager: row.user?.reporting_manager?.name || "No Manager",
      user_phone: row.user?.phone_number || "--",
      lead_name: row.lead?.name || "--",
      lead_phone: row.lead?.phone || "--",
      campaign: row.campaign?.name || "--",
      call_status: titleCase(row.call_status),
      recording: row.call_log?.recording_url || row.callLog?.recording_url || "",
      disposition: row.disposition?.name || "--",
      stage: row.to_stage?.name || "--",
      tag: row.tag?.name || "--",
      remark: row.remark || "--",
    };
  }

  function queryParams() {
    const params = new URLSearchParams({
      per_page: "100",
      from: state.from,
      to: state.to,
    });

    if (state.selectedManagers.size) {
      params.set("manager_ids", Array.from(state.selectedManagers).join(","));
    }

    if (state.selectedUsers.size) {
      params.set("user_ids", Array.from(state.selectedUsers).join(","));
    }

    return params;
  }

  function loadUsers() {
    return crm("settings/users?per_page=100").then((payload) => {
      state.users = unwrap(payload);
      renderFilterOptions();
      updateFilterLabels();
    });
  }

  function loadReport() {
    const endpoint = root.dataset.reportEndpoint || "reports/lead-disposition";

    return crm(`${endpoint}?${queryParams().toString()}`).then((payload) => {
      renderTable(unwrap(payload).map(normaliseRow));
    }).catch((error) => {
      console.error("Could not load lead disposition report.", error);
      renderTable([]);
    });
  }

  function closeMenus(except) {
    root.querySelectorAll(".report-filter.open").forEach((menu) => {
      if (menu !== except) menu.classList.remove("open");
    });
  }

  function applySearch(input) {
    const menu = input.closest(".report-filter-menu");
    const term = input.value.trim().toLowerCase();
    menu?.querySelectorAll(".filter-option").forEach((option) => {
      const name = option.dataset.optionName || "";
      option.hidden = term && !name.includes(term);
    });
  }

  function handleSelectAll(type, checked) {
    const source = type === "managers" ? uniqueManagers() : state.users.map((user) => ({ id: String(user.id) }));
    const selected = type === "managers" ? state.selectedManagers : state.selectedUsers;

    selected.clear();
    if (checked) {
      source.forEach((item) => selected.add(String(item.id)));
    }
  }

  function bindEvents() {
    backBtn?.addEventListener("click", () => {
      window.location.href = root.querySelector(".crm-page-main")?.dataset.reportListUrl || document.referrer || "#";
    });

    root.addEventListener("click", (event) => {
      const toggle = event.target.closest("[data-filter-toggle]");
      if (toggle) {
        const filter = toggle.closest(".report-filter");
        const willOpen = !filter.classList.contains("open");
        closeMenus(filter);
        filter.classList.toggle("open", willOpen);
        if (willOpen && filter.dataset.filterMenu === "date") syncDateMenu();
        return;
      }

      if (event.target.closest("[data-date-apply]")) {
        applyPendingDateRange();
        return;
      }

      if (event.target.closest("[data-filter-apply]")) {
        closeMenus();
        updateFilterLabels();
        loadReport();
      }
    });

    document.addEventListener("click", (event) => {
      if (!event.target.closest(".report-filter")) closeMenus();
    });

    root.addEventListener("change", (event) => {
      const dateInput = event.target.closest("[data-date-range-option]");
      if (dateInput) {
        state.pendingDateRange = dateInput.value;
        if (customDateRange) customDateRange.hidden = dateInput.value !== "custom";
        return;
      }

      const input = event.target.closest("[data-filter-option]");
      if (!input) return;

      const type = input.dataset.filterOption;
      const selected = type === "managers" ? state.selectedManagers : state.selectedUsers;

      if (input.value === "__all") {
        handleSelectAll(type, input.checked);
      } else if (input.checked) {
        selected.add(input.value);
      } else {
        selected.delete(input.value);
      }

      renderFilterOptions();
    });

    root.querySelectorAll("[data-filter-search]").forEach((input) => {
      input.addEventListener("input", () => applySearch(input));
    });
  }

  setDateRange("today");
  bindEvents();
  initDataTable();
  loadReport();
  loadUsers();
})();
