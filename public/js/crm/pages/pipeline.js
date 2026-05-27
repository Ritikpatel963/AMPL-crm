(function () {
  const root = document.querySelector('.crm-pipeline-app');
  if (!root) return;

  const state = {
    campaigns: [],
    pipelines: [],
    pipelineSummaries: {},
    selectedPipelineId: null,
    hidePaused: false,
    search: '',
    menuCampaignId: null
  };

  function escapeHtml(value) {
    const node = document.createElement('span');
    node.textContent = value == null ? '' : String(value);
    return node.innerHTML;
  }

  function unwrap(payload) {
    if (!payload) return null;
    return payload.data && payload.data.data ? payload.data.data : payload.data;
  }

  function formatNumber(value) {
    return new Intl.NumberFormat('en-IN').format(Number(value || 0));
  }

  function categoryLabel(category) {
    const labels = {
      fresh: 'Fresh Leads',
      in_progress: 'IN-PROGRESS',
      closed_lost: 'Closed Lost',
      closed_won: 'Closed Won',
      uncategorized: 'Other'
    };
    return labels[category] || String(category || 'Other').replace(/_/g, ' ');
  }

  function colorAt(index) {
    const colors = ['#ff4545', '#2ab7ca', '#fed766', '#c3ab33', '#3d1e6d', '#051e3e', '#851e3e', '#4a4e4d', '#fe8a71', '#eb6841', '#4ca3dd'];
    return colors[index % colors.length];
  }

  function crm(path) {
    if (!window.callingCrmRequest) {
      return Promise.reject(new Error('Calling CRM API is not configured.'));
    }
    return window.callingCrmRequest(path);
  }

  function isPaused(campaign) {
    return campaign.status === 'paused' || campaign.status === 'draft';
  }

  function priorityClass(campaign) {
    return campaign.priority === 'high' || campaign.priority === 'critical' ? 'prio-high' : 'prio-medium';
  }

  function statusBadge(campaign) {
    return isPaused(campaign)
      ? '<span class="paused-badge">Paused</span>'
      : '<span class="active-badge">Active</span>';
  }

  function campaignDetailUrl(campaignId) {
    return window.location.pathname.replace(/\/+$/, '') + '/campaign/' + encodeURIComponent(campaignId);
  }

  function moreButton() {
    return '<button type="button" class="more-btn" data-campaign-actions-toggle title="Campaign actions" aria-label="Campaign actions" aria-expanded="false"><svg viewBox="0 0 16 16" fill="currentColor" width="14" height="14"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></button>';
  }

  function campaignRow(campaign) {
    return '<div class="camp-item" data-crm-campaign-id="' + campaign.id + '">'
      + '<div class="prio-dot ' + priorityClass(campaign) + '"></div>'
      + '<span class="camp-name" data-campaign-name>' + escapeHtml(campaign.name) + '</span>'
      + statusBadge(campaign)
      + moreButton()
      + '</div>';
  }

  function rightCampaignRow(campaign) {
    return '<div class="right-camp-item" data-crm-campaign-id="' + campaign.id + '">'
      + '<div class="checkbox"></div>'
      + '<div class="prio-dot ' + priorityClass(campaign) + '"></div>'
      + '<span class="right-camp-name">' + escapeHtml(campaign.name) + '</span>'
      + '<div class="right-camp-actions">' + statusBadge(campaign) + moreButton() + '</div>'
      + '</div>';
  }

  function visibleCampaigns(campaigns) {
    const search = state.search.trim().toLowerCase();
    return campaigns.filter(function (campaign) {
      const matchesSearch = !search || String(campaign.name || '').toLowerCase().includes(search);
      const matchesPaused = !state.hidePaused || !isPaused(campaign);
      return matchesSearch && matchesPaused;
    });
  }

  function campaignsForPipeline(pipelineId) {
    return state.campaigns.filter(function (campaign) {
      return String(campaign.pipeline_id || campaign.pipeline?.id || '') === String(pipelineId);
    });
  }

  function fallbackPipelineName(campaign) {
    return campaign.pipeline?.name || 'No Pipeline';
  }

  function groupedPipelines() {
    const known = state.pipelines.map(function (pipeline) {
      return {
        id: pipeline.id,
        name: pipeline.name,
        color: pipeline.color || '#763abb',
        campaigns: campaignsForPipeline(pipeline.id)
      };
    });

    const knownIds = new Set(known.map(function (pipeline) { return String(pipeline.id); }));
    const missingGroups = {};

    state.campaigns.forEach(function (campaign) {
      const pipelineId = campaign.pipeline_id || campaign.pipeline?.id || 'none';
      if (knownIds.has(String(pipelineId))) return;
      if (!missingGroups[pipelineId]) {
        missingGroups[pipelineId] = {
          id: pipelineId,
          name: fallbackPipelineName(campaign),
          color: '#763abb',
          campaigns: []
        };
      }
      missingGroups[pipelineId].campaigns.push(campaign);
    });

    return known.concat(Object.values(missingGroups)).filter(function (pipeline) {
      return pipeline.campaigns.length || state.pipelines.length;
    });
  }

  function renderAllCampaigns() {
    const body = root.querySelector('#page-all .page-body');
    if (!body) return;

    const groups = groupedPipelines();
    if (!groups.length) {
      body.innerHTML = '<div class="pipeline-empty-state">No campaigns found.</div>';
      return;
    }

    body.innerHTML = groups.map(function (pipeline) {
      const campaigns = visibleCampaigns(pipeline.campaigns);
      return '<div class="group-card" data-crm-pipeline-id="' + escapeHtml(pipeline.id) + '">'
        + '<div class="group-head">'
        + '<div class="group-head-left">'
        + '<div class="group-accent-bar" style="background:' + escapeHtml(pipeline.color || '#763abb') + ';"></div>'
        + '<span class="group-title">' + escapeHtml(pipeline.name) + '</span>'
        + '<span class="group-count">' + campaigns.length + '</span>'
        + '</div>'
        + '<button type="button" class="arrow-btn" data-open-pipeline="' + escapeHtml(pipeline.id) + '" title="Open pipeline">'
        + '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>'
        + '</button>'
        + '</div>'
        + '<div class="camp-list">'
        + (campaigns.length ? campaigns.map(campaignRow).join('') : '<div class="pipeline-empty-state compact">No matching campaigns.</div>')
        + '</div>'
        + '</div>';
    }).join('');
  }

  function selectedPipeline() {
    return groupedPipelines().find(function (pipeline) {
      return String(pipeline.id) === String(state.selectedPipelineId);
    }) || groupedPipelines()[0] || null;
  }

  function renderRightPanel() {
    const pipeline = selectedPipeline();
    const list = document.getElementById('right-camp-list');
    if (!list) return;

    if (!pipeline) {
      list.innerHTML = '<div class="pipeline-empty-state compact">No campaigns found.</div>';
      return;
    }

    const campaigns = visibleCampaigns(pipeline.campaigns);
    list.innerHTML = campaigns.length
      ? campaigns.map(rightCampaignRow).join('')
      : '<div class="pipeline-empty-state compact">No campaign found.</div>';
  }

  function renderDetailHeader() {
    const pipeline = selectedPipeline();
    const title = root.querySelector('#page-detail .page-title');
    const panelTitle = root.querySelector('.right-panel-title');
    if (title) title.textContent = pipeline ? pipeline.name + ' Pipeline' : 'Pipeline';
    if (panelTitle) panelTitle.textContent = pipeline ? pipeline.name + ' Campaigns' : 'Campaigns';
  }

  function renderPipelineStats(summary) {
    const stats = root.querySelector('#page-detail .stats-row');
    if (!stats || !summary) return;

    stats.innerHTML = [
      ['Total Leads', summary.total_leads],
      ['Total In-Progress', summary.in_progress_leads],
      ['Total Closed', summary.closed_leads]
    ].map(function (item) {
      return '<div class="stat-tile"><div class="stat-label">' + item[0] + '</div><div class="stat-value">' + formatNumber(item[1]) + '</div></div>';
    }).join('');
  }

  function renderPipelineFunnel(summary) {
    const container = root.querySelector('#page-detail .funnel-container');
    if (!container || !summary) return;

    const stages = (summary.stages || []).filter(function (stage) { return Number(stage.leads_count || 0) > 0; });
    const total = Number(summary.total_leads || 0);
    if (!stages.length || !total) {
      container.innerHTML = '<div class="pipeline-empty-state compact">No stage data found.</div>';
      return;
    }

    const max = Math.max.apply(null, stages.map(function (stage) { return Number(stage.leads_count || 0); }));
    const chartWidth = 520;
    const left = 42;
    const top = 12;
    const maxWidth = 360;
    const minWidth = 110;
    const totalHeight = 230;
    const segmentHeight = Math.max(10, Math.floor(totalHeight / stages.length));
    let y = top;
    let previousWidth = maxWidth;
    const shapes = [];
    const labels = [];

    stages.forEach(function (stage, index) {
      const ratio = max ? Number(stage.leads_count || 0) / max : 0;
      const width = Math.max(minWidth, maxWidth * ratio);
      const nextStage = stages[index + 1];
      const nextRatio = nextStage && max ? Number(nextStage.leads_count || 0) / max : ratio;
      const nextWidth = index === stages.length - 1 ? width : Math.max(minWidth, maxWidth * nextRatio);
      const x1 = left + (maxWidth - previousWidth) / 2;
      const x2 = left + (maxWidth + previousWidth) / 2;
      const x3 = left + (maxWidth + nextWidth) / 2;
      const x4 = left + (maxWidth - nextWidth) / 2;
      const color = stage.color || colorAt(index + 5);
      const labelX = Math.min(chartWidth - 120, x3 + 52);
      const midY = y + segmentHeight / 2;
      const pct = total ? ((Number(stage.leads_count || 0) / total) * 100).toFixed(2) : '0.00';

      shapes.push('<polygon points="' + [x1, y, x2, y, x3, y + segmentHeight, x4, y + segmentHeight].join(' ') + '" fill="' + escapeHtml(color) + '" stroke="#fff" stroke-width="1"></polygon>');
      labels.push('<line x1="' + x3 + '" y1="' + midY + '" x2="' + (labelX - 12) + '" y2="' + midY + '" stroke="' + escapeHtml(color) + '" stroke-width="1"></line>');
      labels.push('<text x="' + labelX + '" y="' + (midY + 4) + '" fill="#05070d" font-size="14" font-weight="800">' + escapeHtml(stage.name) + '(' + pct + ' %)</text>');

      y += segmentHeight;
      previousWidth = nextWidth;
    });

    container.innerHTML = '<svg class="funnel" viewBox="0 0 ' + chartWidth + ' 260" role="img" aria-label="Lead funnel by stages">' + shapes.join('') + labels.join('') + '</svg>';
  }

  function conicGradient(items) {
    const total = items.reduce(function (sum, item) { return sum + Number(item.leads_count || 0); }, 0);
    if (!total) return '#e5e7eb';
    let cursor = 0;
    return 'conic-gradient(' + items.map(function (item, index) {
      const start = cursor;
      cursor += (Number(item.leads_count || 0) / total) * 100;
      return (item.color || colorAt(index)) + ' ' + start + '% ' + cursor + '%';
    }).join(', ') + ')';
  }

  function renderPipelineTags(summary) {
    const grid = root.querySelector('#page-detail .pie-grid');
    if (!grid || !summary) return;

    const tags = summary.tags || {};
    const categories = ['fresh', 'in_progress', 'closed_lost', 'closed_won'];
    const cards = categories.map(function (category) {
      const items = tags[category] || [];
      if (!items.length) {
        return '<div class="pie-box"><div class="pie-svg-wrap"><div class="pipeline-tag-donut empty"></div></div><div class="pie-label">' + categoryLabel(category) + '</div><div class="legend"><div class="legend-row">No tag data</div></div></div>';
      }

      const legend = items.map(function (item, index) {
        return '<div class="legend-row"><span class="legend-dot" style="background:' + escapeHtml(item.color || colorAt(index)) + ';"></span><div>' + escapeHtml(item.name) + ' <span class="legend-num">(' + formatNumber(item.leads_count) + ')</span></div></div>';
      }).join('');

      return '<div class="pie-box"><div class="pie-svg-wrap"><div class="pipeline-tag-donut" style="background:' + conicGradient(items) + ';"></div></div><div class="pie-label">' + categoryLabel(category) + '</div><div class="legend">' + legend + '</div></div>';
    });

    grid.innerHTML = cards.join('');
  }

  function renderPipelineSummary(summary) {
    renderPipelineStats(summary);
    renderPipelineFunnel(summary);
    renderPipelineTags(summary);
  }

  function loadPipelineSummary(pipelineId) {
    if (!pipelineId) return;
    if (state.pipelineSummaries[pipelineId]) {
      renderPipelineSummary(state.pipelineSummaries[pipelineId]);
      return;
    }

    const stats = root.querySelector('#page-detail .stats-row');
    const funnel = root.querySelector('#page-detail .funnel-container');
    const tags = root.querySelector('#page-detail .pie-grid');
    if (stats) stats.innerHTML = '<div class="pipeline-empty-state compact">Loading summary...</div>';
    if (funnel) funnel.innerHTML = '<div class="pipeline-empty-state compact">Loading funnel...</div>';
    if (tags) tags.innerHTML = '<div class="pipeline-empty-state compact">Loading tags...</div>';

    crm('pipelines/' + encodeURIComponent(pipelineId) + '/summary').then(function (payload) {
      const summary = unwrap(payload);
      state.pipelineSummaries[pipelineId] = summary;
      renderPipelineSummary(summary);
    }).catch(function () {
      if (stats) stats.innerHTML = '<div class="pipeline-empty-state compact error">Unable to load summary.</div>';
      if (funnel) funnel.innerHTML = '';
      if (tags) tags.innerHTML = '';
    });
  }

  function render() {
    closeCampaignMenu();
    renderAllCampaigns();
    renderDetailHeader();
    renderRightPanel();
  }

  function showPage(id, pipelineId) {
    if (pipelineId != null) {
      state.selectedPipelineId = pipelineId;
    }
    if (id === 'detail') {
      state.hidePaused = true;
      document.getElementById('toggle-btn')?.classList.add('on');
    }
    root.querySelector('#page-all')?.classList.toggle('active', id === 'all');
    root.querySelector('#page-detail')?.classList.toggle('active', id === 'detail');
    renderDetailHeader();
    renderRightPanel();
    if (id === 'detail') {
      loadPipelineSummary(state.selectedPipelineId);
    }
  }

  function ensureCampaignMenu() {
    let menu = root.querySelector('[data-campaign-actions-menu]');
    if (menu) return menu;

    menu = document.createElement('div');
    menu.className = 'campaign-actions-menu';
    menu.setAttribute('data-campaign-actions-menu', '');
    menu.innerHTML = ''
      + '<button type="button" data-campaign-menu-action="copy">COPY</button>'
      + '<button type="button" data-campaign-menu-action="logs">VIEW LOGS</button>'
      + '<button type="button" data-campaign-menu-action="delete">DELETE</button>';
    root.appendChild(menu);
    return menu;
  }

  function closeCampaignMenu() {
    const menu = root.querySelector('[data-campaign-actions-menu]');
    menu?.classList.remove('open');
    root.querySelectorAll('[data-campaign-actions-toggle][aria-expanded="true"]').forEach(function (button) {
      button.setAttribute('aria-expanded', 'false');
    });
    state.menuCampaignId = null;
  }

  function openCampaignMenu(button) {
    const row = button.closest('[data-crm-campaign-id], .camp-item, .right-camp-item');
    if (!row) return;

    const menu = ensureCampaignMenu();
    const rect = button.getBoundingClientRect();
    const menuWidth = 144;
    const menuHeight = 162;
    const gap = 8;
    const left = Math.min(Math.max(10, rect.right - menuWidth), window.innerWidth - menuWidth - 10);
    let top = rect.bottom + gap;

    if (top + menuHeight > window.innerHeight - 10) {
      top = Math.max(10, rect.top - menuHeight - gap);
    }

    closeCampaignMenu();
    state.menuCampaignId = row.dataset.crmCampaignId || row.querySelector('.camp-name, .right-camp-name')?.textContent.trim() || null;
    button.setAttribute('aria-expanded', 'true');
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    menu.classList.add('open');
  }

  function togglePaused(wrap) {
    state.hidePaused = !state.hidePaused;
    const btn = document.getElementById('toggle-btn');
    btn?.classList.toggle('on', state.hidePaused);
    wrap?.setAttribute('aria-pressed', state.hidePaused ? 'true' : 'false');
    render();
  }

  function bindEvents() {
    root.addEventListener('click', function (event) {
      const menuToggle = event.target.closest('[data-campaign-actions-toggle], .more-btn');
      if (menuToggle) {
        event.preventDefault();
        event.stopPropagation();
        if (menuToggle.getAttribute('aria-expanded') === 'true') {
          closeCampaignMenu();
        } else {
          openCampaignMenu(menuToggle);
        }
        return;
      }

      const menuAction = event.target.closest('[data-campaign-menu-action]');
      if (menuAction) {
        event.preventDefault();
        closeCampaignMenu();
        return;
      }

      const pipelineActionToggle = event.target.closest('[data-pipeline-action-toggle]');
      if (pipelineActionToggle) {
        event.preventDefault();
        event.stopPropagation();
        root.querySelector('[data-pipeline-action-menu]')?.classList.toggle('open');
        return;
      }

      const campaignRow = event.target.closest('[data-crm-campaign-id]');
      if (campaignRow) {
        window.location.href = campaignDetailUrl(campaignRow.dataset.crmCampaignId);
        return;
      }

      const openButton = event.target.closest('[data-open-pipeline]');
      if (openButton) {
        showPage('detail', openButton.dataset.openPipeline);
      }
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('[data-campaign-actions-toggle]') && !event.target.closest('[data-campaign-actions-menu]')) {
        closeCampaignMenu();
      }
      if (!event.target.closest('[data-pipeline-action-toggle]') && !event.target.closest('[data-pipeline-action-menu]')) {
        root.querySelector('[data-pipeline-action-menu]')?.classList.remove('open');
      }
    });

    window.addEventListener('resize', closeCampaignMenu);
    window.addEventListener('scroll', closeCampaignMenu, true);

    root.querySelectorAll('.search-wrap input').forEach(function (input) {
      input.addEventListener('input', function () {
        state.search = input.value;
        root.querySelectorAll('.search-wrap input').forEach(function (otherInput) {
          if (otherInput !== input) otherInput.value = input.value;
        });
        render();
      });
    });
  }

  function load() {
    const allBody = root.querySelector('#page-all .page-body');
    if (allBody) {
      allBody.innerHTML = '<div class="pipeline-empty-state">Loading campaigns...</div>';
    }

    Promise.all([
      crm('pipelines?active_only=1'),
      crm('campaigns?per_page=500')
    ]).then(function (results) {
      state.pipelines = unwrap(results[0]) || [];
      state.campaigns = unwrap(results[1]) || [];
      state.selectedPipelineId = state.pipelines[0]?.id || state.campaigns[0]?.pipeline_id || state.campaigns[0]?.pipeline?.id || null;
      render();
    }).catch(function () {
      if (allBody) {
        allBody.innerHTML = '<div class="pipeline-empty-state error">Unable to load campaigns from the backend.</div>';
      }
      const rightList = document.getElementById('right-camp-list');
      if (rightList) {
        rightList.innerHTML = '<div class="pipeline-empty-state compact error">Unable to load campaigns.</div>';
      }
    });
  }

  window.showPage = showPage;
  window.togglePaused = togglePaused;
  window.renderRightPanel = renderRightPanel;

  bindEvents();
  load();
})();
