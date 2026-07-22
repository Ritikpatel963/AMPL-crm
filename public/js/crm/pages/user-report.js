(function () {
  const root = document.querySelector(".calling-crm-user-report");
  const table = document.getElementById("userReportDataTable");

  if (!root || !table) return;

  const state = {
    dateRange: "today",
    from: "",
    to: "",
    users: [],
    rows: [],
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
  const downloadBtn = root.querySelector("[data-report-download]");
  const backBtn = root.querySelector("[data-report-back]");
  const customDateRange = root.querySelector("[data-custom-date-range]");
  const customDateFrom = root.querySelector("[data-custom-date-from]");
  const customDateTo = root.querySelector("[data-custom-date-to]");

  const allColumns = [
    { key: "sno", label: "No." },
    { key: "name", label: "User Name" },
    { key: "manager", label: "Reporting Manager" },
    { key: "phone", label: "Mobile Number" },
    { key: "date", label: "Date" },
    { key: "total_calls", label: "Total Calls" },
    { key: "connected_calls", label: "Total Calls Connected" },
    { key: "unconnected_calls", label: "Total Unconnected Calls" },
    { key: "outgoing_calls", label: "Total Outgoing Calls" },
    { key: "outgoing_connected_calls", label: "Outgoing Connected Calls" },
    { key: "outgoing_unanswered_calls", label: "Outgoing Unanswered Calls" },
    { key: "avg_outgoing_call_duration", label: "Avg. Outgoing Call Duration" },
    { key: "incoming_calls", label: "Total Incoming Calls" },
    { key: "incoming_connected_calls", label: "Incoming Connected Calls" },
    { key: "incoming_unanswered_calls", label: "Incoming Unanswered Calls" },
    { key: "avg_incoming_call_duration", label: "Avg. Incoming Call Duration" },
    { key: "disposed_count", label: "Total Disposed Count" },
    { key: "disposed_connected_count", label: "Disposed Yes Connected Count" },
    { key: "disposed_not_connected_count", label: "Disposed Not Connected Count" },
    { key: "in_progress_leads", label: "Total In-Progress Leads" },
    { key: "converted_leads", label: "Total Converted Leads" },
    { key: "lost_leads", label: "Total Lost Leads" },
    { key: "follow_ups_due_today", label: "Follow-Ups Due Today" },
    { key: "avg_start_calling_time", label: "Avg. Start Calling Time" },
    { key: "avg_call_duration", label: "Avg. Call Duration" },
    { key: "avg_form_filling_time", label: "Avg. Form Filling Time" },
    { key: "total_call_duration", label: "Total Call Duration" },
    { key: "total_breaks", label: "Total Number of Breaks" },
    { key: "total_break_duration", label: "Total Break Duration" },
    { key: "whatsapp_sent", label: "Total Whatsapp Sent" },
    { key: "emails_sent", label: "Total Emails Sent" },
    { key: "sms_sent", label: "Total SMS Sent" },
  ];
  const activityColumnKeys = new Set([
    "sno",
    "name",
    "manager",
    "phone",
    "date",
    "avg_start_calling_time",
    "total_breaks",
    "total_break_duration",
    "avg_call_duration",
    "avg_form_filling_time",
  ]);
  const columns = root.dataset.reportColumns === "activity"
    ? allColumns.filter((column) => activityColumnKeys.has(column.key))
    : allColumns;

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
    const date = new Date(`${value}T00:00:00`);
    return date.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
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

  function secondsToClock(value) {
    const total = Math.max(0, Math.round(Number(value || 0)));
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const seconds = total % 60;
    return [hours, minutes, seconds].map((part) => String(part).padStart(2, "0")).join(":");
  }

  function num(value) {
    return Number(value || 0);
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function normaliseRow(user) {
    return {
      id: user.id,
      name: user.name || "--",
      manager: user.reporting_manager?.name || "No Manager",
      phone: user.phone_number || "--",
      date: displayDate(state.to),
      total_calls: num(user.total_calls),
      connected_calls: num(user.connected_calls),
      unconnected_calls: num(user.unconnected_calls),
      outgoing_calls: num(user.outgoing_calls),
      outgoing_connected_calls: num(user.outgoing_connected_calls),
      outgoing_unanswered_calls: num(user.outgoing_unanswered_calls),
      avg_outgoing_call_duration: secondsToClock(user.avg_outgoing_call_duration_seconds),
      incoming_calls: num(user.incoming_calls),
      incoming_connected_calls: num(user.incoming_connected_calls),
      incoming_unanswered_calls: num(user.incoming_unanswered_calls),
      avg_incoming_call_duration: secondsToClock(user.avg_incoming_call_duration_seconds),
      disposed_count: num(user.disposed_count),
      disposed_connected_count: num(user.disposed_connected_count),
      disposed_not_connected_count: num(user.disposed_not_connected_count),
      in_progress_leads: num(user.in_progress_leads),
      converted_leads: num(user.converted_leads),
      lost_leads: num(user.lost_leads),
      follow_ups_due_today: num(user.follow_ups_due_today),
      avg_start_calling_time: user.avg_start_calling_time || "--",
      avg_call_duration: secondsToClock(user.avg_call_duration_seconds),
      avg_form_filling_time: secondsToClock(user.avg_form_filling_time_seconds),
      total_call_duration: secondsToClock(user.total_call_duration_seconds),
      total_breaks: num(user.total_breaks),
      total_break_duration: secondsToClock(user.total_break_duration_seconds),
      whatsapp_sent: num(user.whatsapp_sent),
      emails_sent: num(user.emails_sent),
      sms_sent: num(user.sms_sent),
    };
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
        ...state.users.map((user) => optionMarkup({ id: String(user.id), name: user.name }, "users", state.selectedUsers.has(String(user.id)))),
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
      order: [[1, "asc"]],
      columns: columns.map((column, index) => ({
        data: column.key,
        title: column.label,
        width: index === 0 ? "64px" : (index === 1 ? "190px" : null),
        className: index > 4 ? "metric-cell" : "",
        orderable: index !== 0,
        searchable: index !== 0,
        render: function (value, type, row) {
          if (index === 0) return "";
          if (type !== "display") return value;
          if (column.key === "name") return `<strong>${escapeHtml(value)}</strong>`;
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
        emptyTable: "No user report data found",
        loadingRecords: "Loading user report...",
        processing: "Loading user report...",
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
    const endpoint = root.dataset.reportEndpoint || "reports/user-call";

    return crm(`${endpoint}?${queryParams().toString()}`).then((payload) => {
      const users = unwrap(payload);
      state.rows = users.map(normaliseRow);
      renderTable(state.rows);
    }).catch((error) => {
      console.error("Could not load user report.", error);
      state.rows = [];
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

    downloadBtn?.addEventListener("click", exportExcel);
  }

  function exportExcel() {
    const params = queryParams();
    const baseUrl = window.CallingCrmApi?.baseUrl || "";
    const anchor = document.createElement("a");

    params.delete("per_page");

    const url = baseUrl
      ? `${baseUrl.replace(/\/$/, "")}/reports/user-call/export?${params.toString()}`
      : `reports/user-call/export?${params.toString()}`;

    if (downloadBtn) {
      downloadBtn.disabled = true;
      downloadBtn.classList.add("is-loading");
    }

    anchor.href = url;
    anchor.download = "";
    anchor.hidden = true;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();

    window.setTimeout(() => {
      if (downloadBtn) {
        downloadBtn.disabled = false;
        downloadBtn.classList.remove("is-loading");
      }
    }, 1200);
  }

  setDateRange("today");
  bindEvents();
  initDataTable();
  loadReport();
  loadUsers();
})();
