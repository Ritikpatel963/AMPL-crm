(function () {
  const root = document.querySelector('.crm-pipeline-app');
  if (!root) return;

  const state = {
    campaigns: [],
    pipelines: [],
    selectedPipelineId: null,
    hidePaused: false,
    search: ''
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

  function moreButton() {
    return '<div class="more-btn" title="Campaign actions"><svg viewBox="0 0 16 16" fill="currentColor" width="14" height="14"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div>';
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
      : '<div class="pipeline-empty-state compact">No matching campaigns.</div>';
  }

  function renderDetailHeader() {
    const pipeline = selectedPipeline();
    const title = root.querySelector('#page-detail .page-title');
    const panelTitle = root.querySelector('.right-panel-title');
    if (title) title.textContent = pipeline ? pipeline.name + ' Pipeline' : 'Pipeline';
    if (panelTitle) panelTitle.textContent = pipeline ? pipeline.name + ' Campaigns' : 'Campaigns';
  }

  function render() {
    renderAllCampaigns();
    renderDetailHeader();
    renderRightPanel();
  }

  function showPage(id, pipelineId) {
    if (pipelineId != null) {
      state.selectedPipelineId = pipelineId;
    }
    root.querySelector('#page-all')?.classList.toggle('active', id === 'all');
    root.querySelector('#page-detail')?.classList.toggle('active', id === 'detail');
    renderDetailHeader();
    renderRightPanel();
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
      const openButton = event.target.closest('[data-open-pipeline]');
      if (openButton) {
        showPage('detail', openButton.dataset.openPipeline);
      }
    });

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
