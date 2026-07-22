(function () {
  const root = document.querySelector(".calling-crm-user-stage-report");
  const table = document.getElementById("userStageReportDataTable");

  if (!root || !table) return;

  const state = {
    dateRange: "last30",
    from: "",
    to: "",
    pipelines: [],
    campaigns: [],
    users: [],
    pipelineId: "",
    selectedCampaigns: new Set(),
    selectedUsers: new Set(),
    pendingDateRange: "last30",
    table: null,
    stageColumnsKey: "",
  };

  const dateLabel = root.querySelector("[data-date-label]");
  const pipelineLabel = root.querySelector("[data-pipeline-label]");
  const campaignLabel = root.querySelector("[data-campaign-label]");
  const userLabel = root.querySelector("[data-user-label]");
  const pipelineOptions = root.querySelector("[data-pipeline-options]");
  const campaignOptions = root.querySelector("[data-campaign-options]");
  const userOptions = root.querySelector("[data-user-options]");
  const backBtn = root.querySelector("[data-report-back]");
  const customDateRange = root.querySelector("[data-custom-date-range]");
  const customDateFrom = root.querySelector("[data-custom-date-from]");
  const customDateTo = root.querySelector("[data-custom-date-to]");

  const baseColumns = [
    { key: "sno", label: "No." },
    { key: "user_name", label: "User Name" },
    { key: "reporting_manager", label: "Reporting Manager" },
    { key: "date", label: "Date" },
    { key: "conversion_percent", label: "Conversion %" },
    { key: "total_assigned_leads", label: "Total Assigned Leads" },
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
    const date = new Date(`${value}T00:00:00`);
    return date.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
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
    const pipeline = state.pipelines.find((item) => String(item.id) === String(state.pipelineId));
    if (pipelineLabel) pipelineLabel.textContent = pipeline?.name || "Pipeline";
    if (campaignLabel) campaignLabel.textContent = selectedLabel("Campaign", state.selectedCampaigns);
    if (userLabel) userLabel.textContent = selectedLabel("User", state.selectedUsers);
  }

  function optionMarkup(item, type, checked, inputType) {
    return `
      <label class="filter-option" data-option-name="${escapeHtml(String(item.name || "").toLowerCase())}">
        <input type="${inputType}" name="${type}_filter_option" value="${escapeHtml(item.id)}" data-filter-option="${type}" ${checked ? "checked" : ""}>
        <span>${escapeHtml(item.name || "--")}</span>
      </label>
    `;
  }

  function renderFilterOptions() {
    if (pipelineOptions) {
      pipelineOptions.innerHTML = state.pipelines
        .map((pipeline) => optionMarkup(pipeline, "pipelines", String(pipeline.id) === String(state.pipelineId), "radio"))
        .join("");
    }

    if (campaignOptions) {
      campaignOptions.innerHTML = [
        optionMarkup({ id: "__all", name: "Select all" }, "campaigns", state.campaigns.length && state.selectedCampaigns.size === state.campaigns.length, "checkbox"),
        ...state.campaigns.map((campaign) => optionMarkup(campaign, "campaigns", state.selectedCampaigns.has(String(campaign.id)), "checkbox")),
      ].join("");
    }

    if (userOptions) {
      userOptions.innerHTML = [
        optionMarkup({ id: "__all", name: "Select all" }, "users", state.users.length && state.selectedUsers.size === state.users.length, "checkbox"),
        ...state.users.map((user) => optionMarkup({ id: String(user.id), name: user.name || "Unnamed User" }, "users", state.selectedUsers.has(String(user.id)), "checkbox")),
      ].join("");
    }
  }

  function columnsForStages(stages) {
    return baseColumns.concat(stages.map((stage) => ({
      key: `stage_${stage.id}`,
      label: stage.name,
    })));
  }

  function resetDataTable(columns) {
    if (state.table) {
      state.table.destroy();
      state.table = null;
    }

    const head = table.querySelector("thead tr");
    const body = table.querySelector("tbody");
    if (head) {
      head.innerHTML = columns.map((column) => `<th>${escapeHtml(column.label)}</th>`).join("");
    }
    if (body) body.innerHTML = "";
  }

  function initDataTable(columns) {
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
        className: index >= 4 ? "metric-cell" : "",
        orderable: index !== 0,
        searchable: index !== 0,
        render: function (value, type) {
          if (index === 0) return "";
          if (type !== "display") return value;
          if (column.key === "user_name") return `<strong>${escapeHtml(value)}</strong>`;
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
        emptyTable: "No user stage data found",
        loadingRecords: "Loading user stage report...",
        processing: "Loading user stage report...",
        paginate: {
          first: "|<",
          previous: "<",
          next: ">",
          last: ">|",
        },
      },
    });
  }

  function normaliseRows(rows, stages) {
    return rows.map((row) => {
      const item = {
        user_name: row.user_name || "--",
        reporting_manager: row.reporting_manager || "No Manager",
        date: row.date || "--",
        conversion_percent: Number(row.conversion_percent || 0).toFixed(2),
        total_assigned_leads: Number(row.total_assigned_leads || 0),
      };

      stages.forEach((stage) => {
        item[`stage_${stage.id}`] = Number(row.stage_counts?.[stage.id] || 0);
      });

      return item;
    });
  }

  function renderTable(rows, stages) {
    const columns = columnsForStages(stages);
    const stageKey = stages.map((stage) => `${stage.id}:${stage.name}`).join("|");

    if (state.stageColumnsKey !== stageKey) {
      state.stageColumnsKey = stageKey;
      resetDataTable(columns);
    }

    initDataTable(columns);
    if (!state.table) return;

    state.table.clear();
    state.table.rows.add(normaliseRows(rows, stages));
    state.table.draw();
    state.table.columns.adjust();
  }

  function queryParams() {
    const params = new URLSearchParams({
      per_page: "100",
      from: state.from,
      to: state.to,
    });

    if (state.pipelineId) params.set("pipeline_id", state.pipelineId);
    if (state.selectedCampaigns.size) params.set("campaign_ids", Array.from(state.selectedCampaigns).join(","));
    if (state.selectedUsers.size) params.set("user_ids", Array.from(state.selectedUsers).join(","));

    return params;
  }

  function loadPipelines() {
    return crm("pipelines?active_only=1").then((payload) => {
      state.pipelines = unwrap(payload);
      if (!state.pipelineId && state.pipelines.length) {
        const defaultPipeline = state.pipelines.find((pipeline) => pipeline.is_default) || state.pipelines[0];
        state.pipelineId = String(defaultPipeline.id);
      }
      renderFilterOptions();
      updateFilterLabels();
    });
  }

  function loadCampaigns() {
    const params = new URLSearchParams({ per_page: "100" });
    if (state.pipelineId) params.set("pipeline_id", state.pipelineId);

    return crm(`campaigns?${params.toString()}`).then((payload) => {
      state.campaigns = unwrap(payload);
      const availableIds = new Set(state.campaigns.map((campaign) => String(campaign.id)));
      state.selectedCampaigns = new Set(Array.from(state.selectedCampaigns).filter((id) => availableIds.has(id)));
      renderFilterOptions();
      updateFilterLabels();
    });
  }

  function loadUsers() {
    return crm("settings/users?per_page=100").then((payload) => {
      state.users = unwrap(payload);
      renderFilterOptions();
      updateFilterLabels();
    });
  }

  function loadReport() {
    const endpoint = root.dataset.reportEndpoint || "reports/user-stage";

    return crm(`${endpoint}?${queryParams().toString()}`).then((payload) => {
      const data = payload?.data || {};
      const rows = data.rows?.data || data.rows || [];
      const stages = data.stages || [];
      if (data.pipeline_id && !state.pipelineId) state.pipelineId = String(data.pipeline_id);
      renderTable(rows, stages);
      updateFilterLabels();
    }).catch((error) => {
      console.error("Could not load user stage report.", error);
      renderTable([], []);
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
    const source = type === "campaigns" ? state.campaigns : state.users;
    const selected = type === "campaigns" ? state.selectedCampaigns : state.selectedUsers;

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

      if (type === "pipelines") {
        state.pipelineId = input.value;
        state.selectedCampaigns.clear();
        loadCampaigns().then(loadReport);
        return;
      }

      const selected = type === "campaigns" ? state.selectedCampaigns : state.selectedUsers;

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

  setDateRange("last30");
  bindEvents();
  Promise.all([loadPipelines(), loadUsers()])
    .then(loadCampaigns)
    .then(loadReport)
    .catch((error) => {
      console.error("Could not initialise user stage report.", error);
      renderTable([], []);
    });
})();
