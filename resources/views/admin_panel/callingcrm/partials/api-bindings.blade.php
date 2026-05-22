@verbatim
<script>
(function () {
  if (!window.callingCrmRequest || !window.CallingCrmApi) {
    return;
  }

  const state = {
    bootstrap: null,
    campaigns: [],
    pipelines: []
  };

  function unwrap(payload) {
    if (!payload) return null;
    return payload.data && payload.data.data ? payload.data.data : payload.data;
  }

  function moneylessNumber(value) {
    return Number(value || 0).toLocaleString('en-IN');
  }

  function toast(message, type) {
    let node = document.querySelector('[data-crm-api-toast]');
    if (!node) {
      node = document.createElement('div');
      node.setAttribute('data-crm-api-toast', '');
      node.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:100500;max-width:340px;padding:12px 14px;border-radius:10px;background:#111827;color:#fff;font:600 13px/1.35 DM Sans,Arial,sans-serif;box-shadow:0 14px 36px rgba(15,23,42,.28);opacity:0;transform:translateY(8px);transition:.18s ease;';
      document.body.appendChild(node);
    }
    node.textContent = message;
    node.style.background = type === 'error' ? '#b91c1c' : '#111827';
    requestAnimationFrame(function () {
      node.style.opacity = '1';
      node.style.transform = 'translateY(0)';
    });
    window.clearTimeout(node._hideTimer);
    node._hideTimer = window.setTimeout(function () {
      node.style.opacity = '0';
      node.style.transform = 'translateY(8px)';
    }, 3200);
  }

  function crm(path, options) {
    return window.callingCrmRequest(path, options).catch(function (error) {
      const validation = error.payload && error.payload.errors
        ? Object.values(error.payload.errors).flat().join(' ')
        : null;
      toast(validation || error.message || 'Calling CRM request failed', 'error');
      throw error;
    });
  }

  function loadBootstrap() {
    if (state.bootstrap) {
      return Promise.resolve(state.bootstrap);
    }
    return crm('settings/bootstrap').then(function (payload) {
      state.bootstrap = unwrap(payload) || {};
      return state.bootstrap;
    });
  }

  function loadCampaigns() {
    return crm('campaigns?per_page=100').then(function (payload) {
      state.campaigns = unwrap(payload) || [];
      return state.campaigns;
    });
  }

  function loadPipelines() {
    if (state.pipelines.length) {
      return Promise.resolve(state.pipelines);
    }
    return crm('pipelines?active_only=1').then(function (payload) {
      state.pipelines = unwrap(payload) || [];
      return state.pipelines;
    });
  }

  function option(text, value, selected) {
    const el = document.createElement('option');
    el.textContent = text;
    el.value = value == null ? '' : value;
    if (selected) el.selected = true;
    return el;
  }

  function escapeHtml(value) {
    const node = document.createElement('span');
    node.textContent = value == null ? '' : String(value);
    return node.innerHTML;
  }

  function hydrateCampaignSelects(campaigns) {
    document.querySelectorAll('.campaigns-body select, [data-crm-campaign-select]').forEach(function (select) {
      const placeholder = select.querySelector('option[disabled]')?.textContent || 'Select Campaign';
      select.innerHTML = '';
      select.appendChild(option(placeholder, '', true));
      campaigns.forEach(function (campaign) {
        select.appendChild(option(campaign.name, campaign.id));
      });
    });

    document.querySelectorAll('.lead-select').forEach(function (wrap) {
      if (wrap.querySelector('select')) return;
      const select = document.createElement('select');
      select.className = 'lead-input';
      select.setAttribute('data-crm-campaign-select', '');
      select.appendChild(option('Campaign *', '', true));
      campaigns.forEach(function (campaign) {
        select.appendChild(option(campaign.name, campaign.id));
      });
      wrap.innerHTML = '';
      wrap.appendChild(select);
    });
  }

  function hydrateSources(bootstrap) {
    const sources = bootstrap.lead_sources || [];
    document.querySelectorAll('.source-list').forEach(function (list) {
      list.innerHTML = sources.map(function (source) {
        return '<label class="checkbox-row"><input type="checkbox" class="cb-input" value="' + source.code + '"><span class="cb-label">' + source.code + '</span></label>';
      }).join('');
      const badge = list.closest('.nd-card')?.querySelector('.head-badge');
      if (badge) {
        badge.textContent = sources.length + ' sources';
      }
    });
  }

  function contactPayloadFromModal(modal) {
    const inputs = modal.querySelectorAll('.lead-input');
    const campaignId = modal.querySelector('[data-crm-campaign-select]')?.value
      || document.querySelector('.campaigns-body select')?.value;

    return {
      campaign_id: campaignId,
      name: inputs[0]?.value || null,
      phone: inputs[1]?.value || '',
      email: inputs[2]?.value || null,
      source: 'MANUAL',
      user_id: window.CallingCrmApi.currentUserId || null
    };
  }

  function bindContactPage() {
    const contactPageTitle = document.querySelector('.calling-crm-canvas .page-title');
    if (!contactPageTitle || contactPageTitle.textContent.trim().toLowerCase() !== 'contact search') {
      return;
    }

    let currentPage = 1;
    let perPage = 50;
    
    // UI Elements
    const tbody = document.querySelector('[data-contact-search-body]');
    const paginationSelect = document.querySelector('[data-contact-per-page]');
    const paginationInfo = document.querySelector('[data-contact-pagination-info]');
    const prevBtn = document.querySelector('[data-contact-prev]');
    const nextBtn = document.querySelector('[data-contact-next]');
    const menuLayer = document.getElementById('contactMenuLayer');
    let currentMenuLeadId = null;

    // Load initial bootstraps
    Promise.all([loadBootstrap(), loadCampaigns()]).then(function (results) {
      // hydrate filters if we want to populate them dynamically later
    });

    function fetchContacts() {
      tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 30px; color: var(--text-muted);">Loading leads...</td></tr>';
      
      const params = new URLSearchParams({ page: currentPage, per_page: perPage });
      // Add other filter values here if necessary

      crm('leads?' + params.toString()).then(function (payload) {
        // The API might return paginated data: { data: [...], total, current_page, ... }
        // Or unwrap might just give the array. Handle both cases depending on how crm() unwrap works.
        const responseData = payload.data || payload; 
        const leads = Array.isArray(responseData.data) ? responseData.data : (Array.isArray(responseData) ? responseData : []);
        
        const total = responseData.total || leads.length;
        const from = responseData.from || ((currentPage - 1) * perPage + 1);
        const to = responseData.to || (from + leads.length - 1);
        
        paginationInfo.textContent = `${from} - ${to} of ${total}`;
        
        if (!leads.length) {
          tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 30px; color: var(--text-muted);">No leads found matching your criteria.</td></tr>';
          return;
        }
        
        tbody.innerHTML = leads.map(function(lead) {
          const creationDate = new Date(lead.created_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
          const updatedDate = new Date(lead.updated_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
          
          return `
            <tr>
              <td style="text-align: center;"><input type="checkbox" value="${lead.id}"></td>
              <td style="font-weight: 600;">${escapeHtml(lead.name)}</td>
              <td>${escapeHtml(lead.phone)}</td>
              <td>${escapeHtml(lead.campaign?.name || '-')}</td>
              <td>${escapeHtml(lead.pipeline?.name || '-')}</td>
              <td>${creationDate}</td>
              <td>${updatedDate}</td>
              <td>${escapeHtml(lead.stage?.name || lead.status || '-')}</td>
              <td>${escapeHtml(lead.assignedUser?.name || '-')}</td>
              <td>
                <button class="action-menu-btn" data-lead-id="${lead.id}" title="Actions">
                  <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 16a2 2 0 0 1 2 2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2m0-6a2 2 0 0 1 2 2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2m0-6a2 2 0 0 1 2 2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2z"/></svg>
                </button>
              </td>
            </tr>
          `;
        }).join('');
      }).catch(function() {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 30px; color: red;">Failed to load leads.</td></tr>';
      });
    }

    // Pagination Listeners
    if (paginationSelect) {
      paginationSelect.addEventListener('change', function(e) {
        perPage = parseInt(e.target.value, 10);
        currentPage = 1;
        fetchContacts();
      });
    }
    if (prevBtn) {
      prevBtn.addEventListener('click', function() {
        if (currentPage > 1) { currentPage--; fetchContacts(); }
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function() {
        currentPage++; fetchContacts();
      });
    }

    // Three-dot menu functionality
    document.addEventListener('click', function(e) {
      const menuBtn = e.target.closest('.action-menu-btn');
      if (menuBtn) {
        e.preventDefault();
        const rect = menuBtn.getBoundingClientRect();
        currentMenuLeadId = menuBtn.getAttribute('data-lead-id');
        menuLayer.style.top = (rect.bottom + window.scrollY) + 'px';
        menuLayer.style.left = (rect.left + window.scrollX - 140) + 'px'; // open to the left
        menuLayer.classList.add('open');
        return;
      }
      
      const menuItem = e.target.closest('.contact-menu-item');
      if (menuItem && currentMenuLeadId) {
        const action = menuItem.getAttribute('data-action');
        if (action === 'delete') {
          if(confirm('Are you sure you want to delete this lead?')) {
            crm('leads/' + currentMenuLeadId, { method: 'DELETE' }).then(function() {
              toast('Lead deleted successfully.');
              fetchContacts();
            });
          }
        } else if (action === 'edit') {
           toast('Edit lead feature coming soon!');
        } else if (action === 'open') {
           toast('Open lead feature coming soon!');
        } else if (action === 'history') {
           toast('View Dispose History coming soon!');
        }
        menuLayer.classList.remove('open');
        currentMenuLeadId = null;
        return;
      }

      // Close menu if clicking outside
      if (menuLayer && menuLayer.classList.contains('open')) {
        menuLayer.classList.remove('open');
        currentMenuLeadId = null;
      }
    });

    // Add Lead Modal functionality (Preserved)
    const leadModal = document.querySelector('[data-lead-modal]');
    const submitButton = leadModal?.querySelector('.lead-submit-btn');
    submitButton?.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();

      const payload = contactPayloadFromModal(leadModal);
      if (!payload.campaign_id) {
        toast('Select a campaign before adding the lead.', 'error');
        return;
      }
      if (!payload.phone) {
        toast('Contact number is required.', 'error');
        return;
      }

      crm('leads', { method: 'POST', body: payload }).then(function () {
        toast('Lead created successfully.');
        leadModal.classList.remove('open');
        leadModal.querySelectorAll('input').forEach(function (input) { input.value = ''; });
        fetchContacts(); // refresh list
      });
    }, true);

    bindUploadButtons();
    
    // Initial fetch
    fetchContacts();
  }

  function bindUploadButtons() {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.csv,.xls,.xlsx';
    fileInput.hidden = true;
    document.body.appendChild(fileInput);

    document.querySelectorAll('.upload-browse-btn').forEach(function (button) {
      button.addEventListener('click', function () {
        fileInput.value = '';
        fileInput.click();
      });
    });

    fileInput.addEventListener('change', function () {
      const file = fileInput.files && fileInput.files[0];
      const campaignId = document.querySelector('[data-crm-campaign-select]')?.value
        || document.querySelector('.campaigns-body select')?.value
        || state.campaigns[0]?.id;
      if (!file) return;
      if (!campaignId) {
        toast('Create or select a campaign before uploading leads.', 'error');
        return;
      }

      const form = new FormData();
      form.append('file', file);
      form.append('name', file.name);
      crm('campaigns/' + campaignId + '/imports', { method: 'POST', body: form }).then(function () {
        toast('Import file queued successfully.');
        document.querySelectorAll('[data-upload-modal]').forEach(function (modal) {
          modal.classList.remove('open');
        });
      });
    });
  }

  function hydrateDashboard() {
    if (!document.querySelector('.dashboard-grid')) {
      return;
    }

    const leadsList = document.querySelector('[data-crm-leads-by-stage]');
    const pinnedCampaigns = document.querySelector('[data-crm-pinned-campaigns]');
    const pipelineOptions = document.querySelector('[data-crm-pipeline-filter-options]');
    const campaignOptions = document.querySelector('[data-crm-campaign-filter-options]');

    function stateMessage(node, message, type) {
      if (!node) return;
      node.innerHTML = '<div class="dashboard-state' + (type === 'error' ? ' error' : '') + '">' + escapeHtml(message) + '</div>';
    }

    function filterQuery(filters) {
      const params = new URLSearchParams();
      Object.entries(filters || {}).forEach(function (entry) {
        if (entry[1]) params.set(entry[0], entry[1]);
      });
      return params.toString() ? '?' + params.toString() : '';
    }

    function localDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return year + '-' + month + '-' + day;
    }

    function dateRange(label) {
      const today = new Date();
      const from = new Date(today);

      if (label === 'Today') {
        return { from: localDate(today), to: localDate(today) };
      }
      if (label === 'Yesterday') {
        from.setDate(today.getDate() - 1);
        return { from: localDate(from), to: localDate(from) };
      }
      if (label === 'This Month') {
        from.setDate(1);
        return { from: localDate(from), to: localDate(today) };
      }
      if (label === 'Last 30 Days') {
        from.setDate(today.getDate() - 29);
        return { from: localDate(from), to: localDate(today) };
      }
      if (label === 'Last 7 Days') {
        from.setDate(today.getDate() - 6);
        return { from: localDate(from), to: localDate(today) };
      }

      return {};
    }

    function loadOverview() {
      const selectedDate = document.querySelector('input[name="dashboard_date"]:checked')?.value || 'Last 7 Days';
      crm('dashboard/overview' + filterQuery(dateRange(selectedDate))).then(function (payload) {
        const data = unwrap(payload) || {};
        const percent = Math.max(0, Math.min(Number(data.connected_percent || 0), 100));
        const circumference = 326.73;

        document.querySelectorAll('[data-crm-connected-percent]').forEach(function (node) {
          node.textContent = percent.toFixed(percent % 1 ? 1 : 0) + '%';
        });
        document.querySelectorAll('[data-crm-connected-calls]').forEach(function (node) {
          node.textContent = moneylessNumber(data.connected_calls);
        });
        document.querySelectorAll('[data-crm-total-calls]').forEach(function (node) {
          node.textContent = moneylessNumber(data.total_calls);
        });
        document.querySelectorAll('[data-crm-connected-ring]').forEach(function (node) {
          const connectedArc = (percent / 100) * circumference;
          node.setAttribute('stroke-dasharray', connectedArc.toFixed(2) + ' ' + Math.max(circumference - connectedArc, 0).toFixed(2));
        });
      }).catch(function () {
        document.querySelectorAll('[data-crm-connected-percent], [data-crm-connected-calls], [data-crm-total-calls]').forEach(function (node) {
          node.textContent = '--';
        });
      });
    }

    function loadAgentActivity() {
      crm('dashboard/agent-activity').then(function (payload) {
        const data = unwrap(payload) || {};
        const total = Number(data.total_agents || 0);
        const active = Number(data.active_agents || 0);
        const onBreak = Number(data.on_break_agents || 0);

        document.querySelectorAll('[data-crm-active-agents]').forEach(function (node) {
          node.textContent = moneylessNumber(active);
        });
        document.querySelectorAll('[data-crm-break-agents]').forEach(function (node) {
          node.textContent = moneylessNumber(onBreak);
        });
        document.querySelectorAll('[data-crm-total-agents]').forEach(function (node) {
          node.textContent = '/ ' + moneylessNumber(total);
        });
        document.querySelectorAll('[data-crm-active-agent-bar]').forEach(function (node) {
          node.style.width = (total ? Math.min((active / total) * 100, 100) : 0).toFixed(1) + '%';
        });
        document.querySelectorAll('[data-crm-break-agent-bar]').forEach(function (node) {
          node.style.width = (total ? Math.min((onBreak / total) * 100, 100) : 0).toFixed(1) + '%';
        });
      }).catch(function () {
        document.querySelectorAll('[data-crm-active-agents], [data-crm-break-agents], [data-crm-total-agents]').forEach(function (node) {
          node.textContent = '--';
        });
      });
    }

    function leadFilters() {
      return {
        pipeline_id: document.querySelector('input[name="dashboard_pipeline"]:checked')?.value || '',
        campaign_id: document.querySelector('input[name="dashboard_campaign"]:checked')?.value || ''
      };
    }

    function renderLeadStages(rows) {
      const total = rows.reduce(function (sum, row) { return sum + Number(row.total || 0); }, 0);
      if (!rows.length) {
        stateMessage(leadsList, 'No leads found for these dashboard filters.');
        return;
      }

      leadsList.innerHTML = rows.map(function (row) {
        const count = Number(row.total || 0);
        const percent = total ? ((count / total) * 100).toFixed(2) : '0.00';
        const stageName = row.stage?.name || 'No stage';
        const stageColor = row.stage?.color || '#763ABB';
        return '<div class="lead-item" style="border-left-color:' + escapeHtml(stageColor) + ';">'
          + '<div class="lead-left"><span class="lead-count">' + moneylessNumber(count) + '</span>'
          + '<span class="lead-name">' + escapeHtml(stageName) + '</span></div>'
          + '<span class="lead-pct">' + percent + '%</span></div>';
      }).join('');
    }

    function loadLeadStages() {
      stateMessage(leadsList, 'Loading lead stages...');
      crm('dashboard/leads-by-stage' + filterQuery(leadFilters())).then(function (payload) {
        renderLeadStages(unwrap(payload) || []);
      }).catch(function () {
        stateMessage(leadsList, 'Lead stage data could not be loaded.', 'error');
      });
    }

    function renderPinnedCampaigns(campaigns) {
      const pinned = campaigns.filter(function (campaign) { return campaign.is_pinned; }).slice(0, 5);
      if (!pinned.length) {
        if (pinnedCampaigns) {
          pinnedCampaigns.innerHTML = '<div class="pinned-empty-icon">📌</div>You currently have no pinned campaigns as of now.';
          pinnedCampaigns.classList.add('pinned-empty');
          pinnedCampaigns.classList.remove('pinned-list');
        }
        return;
      }

      if (pinnedCampaigns) {
        pinnedCampaigns.classList.remove('pinned-empty');
        pinnedCampaigns.classList.add('pinned-list');
        pinnedCampaigns.innerHTML = pinned.map(function (campaign) {
          return '<div class="pinned-campaign"><span class="pinned-campaign-name">' + escapeHtml(campaign.name) + '</span>'
            + '<span class="pinned-campaign-meta">' + escapeHtml(campaign.status || 'active') + '</span></div>';
        }).join('');
      }
    }

    function renderFilterChoices(pipelines, campaigns) {
      const pipelineSearch = pipelineOptions?.querySelector('.crm-search')?.outerHTML || '';
      const campaignSearch = campaignOptions?.querySelector('.crm-search')?.outerHTML || '';

      if (pipelineOptions) {
        pipelineOptions.innerHTML = pipelineSearch + pipelines.map(function (pipeline, index) {
          return '<label class="crm-choice"><input type="radio" name="dashboard_pipeline" value="' + pipeline.id + '"' + (index === 0 ? ' checked' : '') + '>'
            + '<span class="crm-radio"></span><span>' + escapeHtml(pipeline.name) + '</span></label>';
        }).join('');
        if (!pipelines.length) stateMessage(pipelineOptions, 'No active pipelines found.');
        const label = pipelineOptions.closest('[data-filter]')?.querySelector('[data-filter-label]');
        if (label && pipelines[0]) label.textContent = pipelines[0].name;
      }

      if (campaignOptions) {
        campaignOptions.innerHTML = campaignSearch
          + '<label class="crm-choice"><input type="radio" name="dashboard_campaign" value="" checked><span class="crm-radio"></span><span>All campaigns</span></label>'
          + campaigns.map(function (campaign) {
            return '<label class="crm-choice"><input type="radio" name="dashboard_campaign" value="' + campaign.id + '">'
              + '<span class="crm-radio"></span><span>' + escapeHtml(campaign.name) + '</span></label>';
          }).join('');
      }

      [pipelineOptions, campaignOptions].forEach(function (body) {
        const search = body?.querySelector('.crm-search');
        search?.addEventListener('input', function () {
          const query = search.value.trim().toLowerCase();
          body.querySelectorAll('.crm-choice').forEach(function (choice) {
            choice.style.display = choice.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
          });
        });
      });
    }

    document.querySelectorAll('input[name="dashboard_date"]').forEach(function (input) {
      input.closest('[data-filter]')?.querySelector('[data-filter-apply]')?.addEventListener('click', loadOverview);
    });
    document.querySelectorAll('.leads-card [data-filter-apply]').forEach(function (button) {
      button.addEventListener('click', loadLeadStages);
    });

    loadOverview();
    loadAgentActivity();
    Promise.all([loadPipelines(), loadCampaigns(), crm('settings/users')]).then(function (results) {
      const pipelines = results[0] || [];
      const campaigns = results[1] || [];
      const users = unwrap(results[2]) || [];

      renderFilterChoices(pipelines, campaigns);
      renderPinnedCampaigns(campaigns);
      loadLeadStages();
      initCreateCampaignModal(pipelines, users);
    }).catch(function () {
      stateMessage(leadsList, 'Dashboard filters could not be loaded.', 'error');
      stateMessage(pipelineOptions, 'Pipelines could not be loaded.', 'error');
      stateMessage(campaignOptions, 'Campaigns could not be loaded.', 'error');
      stateMessage(pinnedCampaigns, 'Pinned campaigns could not be loaded.', 'error');
    });
  }

  function initCreateCampaignModal(pipelines, users) {
    const modalForm = document.getElementById('campaignCreateForm');
    if (!modalForm) return;

    const nameInput = document.getElementById('campaignNameInput');
    const pipelineSelect = document.getElementById('campaignPipelineSelect');
    const managersChips = document.getElementById('campaignManagersChips');
    const managerSearch = document.getElementById('campaignManagerSearch');
    const managerDropdown = document.getElementById('campaignManagerDropdown');
    const agentsChips = document.getElementById('campaignAgentsChips');
    const agentSearch = document.getElementById('campaignAgentSearch');
    const agentDropdown = document.getElementById('campaignAgentDropdown');
    const addSettingsBtn = document.getElementById('campaignAdditionalSettingsBtn');
    const addSettingsContent = document.getElementById('campaignAdditionalSettingsContent');
    const prioritySelect = document.getElementById('campaignPrioritySelect');
    const duplicacyScope = document.getElementById('campaignDuplicacyScope');
    const duplicacyAction = document.getElementById('campaignDuplicacyAction');

    let selectedDistribution = 'on_demand';
    const selectedManagers = new Set();
    const selectedAgents = new Set();

    // 1. Populate Pipelines select
    if (pipelineSelect) {
      pipelineSelect.innerHTML = '<option value="" disabled selected>Select Pipeline</option>';
      pipelines.forEach(function (pipeline) {
        const option = document.createElement('option');
        option.value = pipeline.id;
        option.textContent = pipeline.name;
        pipelineSelect.appendChild(option);
      });
    }

    // 2. Set default campaign name on open and clear state
    const openBtn = document.querySelector('[data-campaign-open]');
    if (openBtn) {
      openBtn.addEventListener('click', function () {
        selectedDistribution = 'on_demand';
        selectedManagers.clear();
        selectedAgents.clear();

        // Default manager to current logged in user if they are in the list
        const currentUserId = window.CallingCrmApi.currentUserId;
        if (currentUserId && users.some(function (u) { return u.id === currentUserId; })) {
          selectedManagers.add(currentUserId);
        }

        renderManagerChips();
        renderAgentChips();

        // Reset distribution cards visual state
        document.querySelectorAll('.distribution-card').forEach(function (card) {
          card.classList.toggle('selected', card.dataset.strategy === 'on_demand');
        });

        // Toggle additional settings back to collapsed
        if (addSettingsContent) addSettingsContent.style.display = 'none';
        if (addSettingsBtn) addSettingsBtn.classList.remove('expanded');

        if (prioritySelect) prioritySelect.value = 'medium';
        if (duplicacyScope) duplicacyScope.value = 'campaign';
        if (duplicacyAction) duplicacyAction.value = 'ignore';

        const currentUser = users.find(function (u) { return u.id === currentUserId; });
        const currentUserName = currentUser ? currentUser.name : 'Ritik Patel';

        const now = new Date();
        const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        if (nameInput) nameInput.value = "Campaign " + currentUserName + " " + dateStr;
      });
    }

    // 3. Render Manager chips
    function renderManagerChips() {
      if (!managersChips) return;
      managersChips.innerHTML = Array.from(selectedManagers).map(function (id) {
        const user = users.find(function (u) { return u.id === id; });
        if (!user) return '';
        return '<span class="campaign-chip" data-user-id="' + id + '">'
          + escapeHtml(user.name)
          + ' <span class="campaign-chip-x" style="cursor:pointer;" data-remove-manager-chip="' + id + '">&times;</span></span>';
      }).join('');
    }

    // 4. Render Agent chips
    function renderAgentChips() {
      if (!agentsChips) return;
      agentsChips.innerHTML = Array.from(selectedAgents).map(function (id) {
        const user = users.find(function (u) { return u.id === id; });
        if (!user) return '';
        return '<span class="campaign-chip" data-user-id="' + id + '">'
          + escapeHtml(user.name)
          + ' <span class="campaign-chip-x" style="cursor:pointer;" data-remove-agent-chip="' + id + '">&times;</span></span>';
      }).join('');
    }

    // 5. Manager Autocomplete Search
    function renderManagerDropdown(query) {
      if (!managerDropdown) return;
      const q = query.trim().toLowerCase();
      const filtered = users.filter(function (user) {
        return !selectedManagers.has(user.id) &&
          (user.name.toLowerCase().includes(q) || (user.email && user.email.toLowerCase().includes(q)));
      });

      if (!filtered.length) {
        managerDropdown.innerHTML = '<div style="padding: 10px 12px; color: #a4a8b2; font-size: 13px;">No users found</div>';
      } else {
        managerDropdown.innerHTML = filtered.map(function (user) {
          return '<div class="crm-choice" data-user-id="' + user.id + '" style="padding: 8px 12px; cursor: pointer; display: flex; flex-direction: column; gap: 2px;">'
            + '<span style="font-weight: 700; font-size: 13px; color: var(--text);">' + escapeHtml(user.name) + '</span>'
            + '<span style="font-size: 11px; color: #6b7280;">' + escapeHtml(user.email || user.phone_number || '') + ' (' + user.role + ')</span>'
            + '</div>';
        }).join('');
      }
    }

    if (managerSearch) {
      managerSearch.addEventListener('focus', function () {
        if (managerDropdown) {
          managerDropdown.style.display = 'block';
          renderManagerDropdown(managerSearch.value);
        }
      });
      managerSearch.addEventListener('input', function () {
        if (managerDropdown) {
          managerDropdown.style.display = 'block';
          renderManagerDropdown(managerSearch.value);
        }
      });
    }

    if (managerDropdown) {
      managerDropdown.addEventListener('click', function (e) {
        const item = e.target.closest('[data-user-id]');
        if (!item) return;
        const userId = Number(item.dataset.userId);
        selectedManagers.add(userId);
        renderManagerChips();
        if (managerSearch) managerSearch.value = '';
        managerDropdown.style.display = 'none';
      });
    }

    // 6. Agent Autocomplete Search
    function renderAgentDropdown(query) {
      if (!agentDropdown) return;
      const q = query.trim().toLowerCase();
      const filtered = users.filter(function (user) {
        return !selectedAgents.has(user.id) &&
          (user.name.toLowerCase().includes(q) || (user.email && user.email.toLowerCase().includes(q)));
      });

      if (!filtered.length) {
        agentDropdown.innerHTML = '<div style="padding: 10px 12px; color: #a4a8b2; font-size: 13px;">No users found</div>';
      } else {
        agentDropdown.innerHTML = filtered.map(function (user) {
          return '<div class="crm-choice" data-user-id="' + user.id + '" style="padding: 8px 12px; cursor: pointer; display: flex; flex-direction: column; gap: 2px;">'
            + '<span style="font-weight: 700; font-size: 13px; color: var(--text);">' + escapeHtml(user.name) + '</span>'
            + '<span style="font-size: 11px; color: #6b7280;">' + escapeHtml(user.email || user.phone_number || '') + ' (' + user.role + ')</span>'
            + '</div>';
        }).join('');
      }
    }

    if (agentSearch) {
      agentSearch.addEventListener('focus', function () {
        if (agentDropdown) {
          agentDropdown.style.display = 'block';
          renderAgentDropdown(agentSearch.value);
        }
      });
      agentSearch.addEventListener('input', function () {
        if (agentDropdown) {
          agentDropdown.style.display = 'block';
          renderAgentDropdown(agentSearch.value);
        }
      });
    }

    if (agentDropdown) {
      agentDropdown.addEventListener('click', function (e) {
        const item = e.target.closest('[data-user-id]');
        if (!item) return;
        const userId = Number(item.dataset.userId);
        selectedAgents.add(userId);
        renderAgentChips();
        if (agentSearch) agentSearch.value = '';
        agentDropdown.style.display = 'none';
      });
    }

    // 7. Chip Dismiss Actions
    document.addEventListener('click', function (e) {
      const removeMgr = e.target.closest('[data-remove-manager-chip]');
      if (removeMgr) {
        selectedManagers.delete(Number(removeMgr.dataset.removeManagerChip));
        renderManagerChips();
      }
      const removeAgt = e.target.closest('[data-remove-agent-chip]');
      if (removeAgt) {
        selectedAgents.delete(Number(removeAgt.dataset.removeAgentChip));
        renderAgentChips();
      }
    });

    // 8. Close dropdowns clicking outside
    document.addEventListener('click', function (e) {
      if (managerSearch && managerDropdown && !managerSearch.contains(e.target) && !managerDropdown.contains(e.target)) {
        managerDropdown.style.display = 'none';
      }
      if (agentSearch && agentDropdown && !agentSearch.contains(e.target) && !agentDropdown.contains(e.target)) {
        agentDropdown.style.display = 'none';
      }
    });

    // 9. Lead Distribution options click
    const distCards = document.querySelectorAll('.distribution-card');
    distCards.forEach(function (card) {
      card.addEventListener('click', function () {
        distCards.forEach(function (c) { c.classList.remove('selected'); });
        card.classList.add('selected');
        selectedDistribution = card.dataset.strategy;
      });
    });

    // 10. Additional Settings expand panel
    if (addSettingsBtn && addSettingsContent) {
      addSettingsBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const expanded = addSettingsContent.style.display === 'block';
        addSettingsContent.style.display = expanded ? 'none' : 'block';
        addSettingsBtn.classList.toggle('expanded', !expanded);
      });
    }

    // 11. Create Campaign form submit
    modalForm.addEventListener('submit', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();

      const name = nameInput ? nameInput.value.trim() : '';
      const pipelineId = pipelineSelect ? pipelineSelect.value : '';

      if (!name) {
        toast('Campaign name is required.', 'error');
        return;
      }

      if (!pipelineId) {
        toast('Pipeline selection is required.', 'error');
        return;
      }

      const payload = {
        name: name,
        pipeline_id: Number(pipelineId),
        manager_id: selectedManagers.size > 0 ? Array.from(selectedManagers)[0] : null,
        agent_ids: Array.from(selectedAgents),
        distribution: selectedDistribution,
        priority: prioritySelect ? prioritySelect.value : 'medium',
        settings: {
          duplicate_check_scope: duplicacyScope ? duplicacyScope.value : 'campaign',
          duplicate_action: duplicacyAction ? duplicacyAction.value : 'ignore'
        }
      };

      crm('campaigns', { method: 'POST', body: payload }).then(function (res) {
        toast('Campaign created successfully.');
        const backdrop = document.querySelector('[data-campaign-modal]');
        if (backdrop) backdrop.classList.remove('open');

        // Refresh campaigns
        loadCampaigns().then(function (campaigns) {
          renderPinnedCampaigns(campaigns);
        });
      });
    }, true);
  }

  function hydratePipeline() {
    if (!document.querySelector('.crm-pipeline-app')) {
      return;
    }

    loadCampaigns().then(function (campaigns) {
      if (!campaigns.length) {
        document.querySelectorAll('.camp-list').forEach(function (list) {
          list.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--calling-crm-muted); font-size: 13px;">No campaigns found.</div>';
        });
        document.querySelectorAll('.group-count').forEach(function (count) {
          count.textContent = '0';
        });
        return;
      }
      const rows = campaigns.map(function (campaign) {
        const priority = campaign.priority === 'high' || campaign.priority === 'critical' ? 'prio-high' : 'prio-medium';
        const paused = campaign.status === 'paused' || campaign.status === 'draft';
        return '<div class="camp-item" data-crm-campaign-id="' + campaign.id + '"><div class="prio-dot ' + priority + '"></div><span class="camp-name" data-campaign-name>' + campaign.name + '</span><span class="' + (paused ? 'paused-badge' : 'active-badge') + '">' + (paused ? 'Paused' : 'Active') + '</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor" width="14" height="14"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>';
      }).join('');
      document.querySelectorAll('.camp-list').forEach(function (list) {
        list.innerHTML = rows;
      });
      document.querySelectorAll('.group-count').forEach(function (count) {
        count.textContent = campaigns.length;
      });
    });
  }

  function hydrateReportsAndTrends() {
    if (document.title.toLowerCase().includes('report')) {
      crm('reports/catalog').then(function (payload) {
        window.CallingCrmReports = unwrap(payload) || [];
      });
    }

    if (document.title.toLowerCase().includes('trends')) {
      crm('trends/widgets').then(function (payload) {
        window.CallingCrmTrends = unwrap(payload) || {};
      });
      crm('trends/calls-vs-connected').then(function (payload) {
        window.CallingCrmCallsTrend = unwrap(payload) || [];
      });
    }
  }

  function hydrateSettings() {
    const root = document.querySelector('.calling-crm-settings');
    if (!root) return;

    const settingsState = {
      users: [],
      pipelines: [],
      selectedStage: null,
      stageInsertSortOrder: null,
      selectedTag: null,
      retryReason: null,
      propertyId: null
    };
    const profileForm = root.querySelector('[data-profile-form]');
    const usersBody = root.querySelector('[data-users-table-body]');
    const usersForm = document.querySelector('[data-add-user-form]');
    const userRows = document.querySelector('[data-user-rows]');
    const pipelineForm = document.querySelector('[data-pipeline-form]');
    const pipelineSelect = root.querySelector('[data-pipeline-select]');
    const stageFlow = root.querySelector('[data-pipeline-stage-flow]');
    const stageEditorForm = root.querySelector('[data-stage-editor-form]');
    const stageNameInput = root.querySelector('[data-stage-name-input]');
    const stageTagsField = root.querySelector('[data-stage-tags-field]');
    const stageSave = root.querySelector('[data-stage-save]');
    const stageDelete = root.querySelector('[data-stage-delete]');
    const stageCreate = root.querySelector('[data-stage-create]');
    const stageSettingsToggle = root.querySelector('[data-stage-settings-toggle]');
    const stageSettingsBody = root.querySelector('[data-stage-settings-body]');
    const stageTransitionList = root.querySelector('[data-stage-transition-list]');
    const stageForm = document.querySelector('[data-stage-form]');
    const stageModal = document.querySelector('[data-stage-modal]');
    const stageCreateName = document.querySelector('[data-stage-create-name]');
    const tagForm = document.querySelector('[data-tag-form]');
    const tagModal = document.querySelector('[data-tag-modal]');
    const tagNameInput = document.querySelector('[data-tag-name-input]');
    const tagDelete = document.querySelector('[data-tag-delete]');
    const retryTable = root.querySelector('[data-retry-table]');
    const retryLogicForm = document.querySelector('[data-retry-logic-form]');
    const retryReasonForm = document.querySelector('[data-retry-reason-form]');
    const propertyBody = root.querySelector('[data-property-table-body]');
    const propertyForm = document.querySelector('[data-property-form]');
    const propertyCount = root.querySelector('[data-property-count]');
    const priorityList = root.querySelector('[data-priority-list]');

    function roleLabel(role) {
      return role === 'subadmin' ? 'Admin / Team Lead' : 'Executive';
    }

    function field(name) {
      return profileForm?.querySelector('[data-profile-field="' + name + '"]');
    }

    function closeBackdrop(selector) {
      const modal = root.querySelector(selector) || document.querySelector(selector);
      modal?.classList.remove('open');
      modal?.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    function profilePayload() {
      return {
        business_name: field('business_name')?.value.trim() || '',
        phone: field('phone')?.value.trim() || '',
        address: field('address')?.value.trim() || null,
        state: field('state')?.value || null,
        pincode: field('pincode')?.value.trim() || null,
        gst_number: field('gst_number')?.value.trim() || null,
        working_days: field('working_days')?.value || 'mon_sat',
        work_start_time: field('work_start_time')?.value || null,
        work_end_time: field('work_end_time')?.value || null,
        timezone: 'Asia/Kolkata'
      };
    }

    function renderUsers(users) {
      if (!usersBody) return;
      settingsState.users = users;
      if (!users.length) {
        usersBody.innerHTML = '<tr><td colspan="9">No CRM users yet.</td></tr>';
        return;
      }

      usersBody.innerHTML = users.map(function (user, index) {
        return '<tr data-crm-user-id="' + user.id + '"><td>' + (index + 1) + '</td>'
          + '<td>' + escapeHtml(user.name) + '</td><td>' + escapeHtml(user.phone_number || '') + '</td>'
          + '<td>' + escapeHtml(user.reporting_manager?.name || '') + '</td><td>' + escapeHtml(user.email || '') + '</td>'
          + '<td>' + escapeHtml(user.role) + '</td><td>' + escapeHtml(user.expires_at || '') + '</td>'
          + '<td><span class="status-pill">' + escapeHtml(user.crm_status || 'active') + '</span></td>'
          + '<td><button type="button" class="dots-btn" aria-label="User actions" data-user-actions-toggle data-user-name="' + escapeHtml(user.name) + '"><i class="fa-solid fa-ellipsis-vertical"></i></button></td></tr>';
      }).join('');
    }

    function loadUsers() {
      return crm('settings/users').then(function (payload) {
        renderUsers(unwrap(payload) || []);
      });
    }

    function userPayload(row, includePassword) {
      const payload = {
        name: row.querySelector('input[name="name[]"]')?.value.trim(),
        phone_number: row.querySelector('input[name="number[]"]')?.value.trim(),
        role: row.querySelector('select[name="role[]"]')?.value || 'agent',
        email: row.querySelector('input[name="email[]"]')?.value.trim() || null,
        employee_id: row.querySelector('input[name="employee_id[]"]')?.value.trim() || null
      };
      const password = row.querySelector('input[name="password[]"]')?.value;
      if (includePassword || password) payload.password = password;
      return payload;
    }

    function fillProfile(profile) {
      Object.keys(profile || {}).forEach(function (key) {
        const node = field(key);
        if (node && profile[key] != null) node.value = profile[key];
      });
      field('address')?.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function selectedPipeline() {
      return settingsState.pipelines.find(function (pipeline) {
        return String(pipeline.id) === pipelineSelect?.value;
      }) || null;
    }

    function selectedStageIn(pipeline, stageId) {
      return (pipeline?.stages || []).find(function (stage) {
        return String(stage.id) === String(stageId);
      }) || null;
    }

    function renderPipelineOptions(pipelines, pipelineId, stageId) {
      settingsState.pipelines = pipelines;
      if (!pipelineSelect) return;
      pipelineSelect.innerHTML = pipelines.map(function (pipeline) {
        return '<option value="' + pipeline.id + '">' + escapeHtml(pipeline.name) + '</option>';
      }).join('');
      const pipeline = pipelines.find(function (item) {
        return String(item.id) === String(pipelineId);
      }) || pipelines[0];
      if (pipeline) pipelineSelect.value = String(pipeline.id);
      renderStages(pipeline, stageId);
    }

    function reloadPipelines(pipelineId, stageId) {
      state.pipelines = [];
      return loadPipelines().then(function (pipelines) {
        renderPipelineOptions(pipelines, pipelineId, stageId);
        return pipelines;
      });
    }

    function openStageModal(sortOrder) {
      const pipeline = selectedPipeline();
      if (!pipeline) {
        toast('Create or select a saved pipeline first.', 'error');
        return;
      }
      settingsState.stageInsertSortOrder = sortOrder || ((pipeline.stages || []).length + 1);
      if (stageCreateName) stageCreateName.value = '';
      stageModal?.classList.add('open');
      stageModal?.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      stageCreateName?.focus();
    }

    function closeStageModal() {
      settingsState.stageInsertSortOrder = null;
      closeBackdrop('[data-stage-modal]');
    }

    function openTagModal(tag) {
      settingsState.selectedTag = tag || null;
      if (!settingsState.selectedTag) return;
      if (tagNameInput) tagNameInput.value = tag.name || '';
      tagModal?.classList.add('open');
      tagModal?.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      tagNameInput?.focus();
    }

    function closeTagModal() {
      settingsState.selectedTag = null;
      closeBackdrop('[data-tag-modal]');
    }

    function savePendingTag() {
      const input = stageTagsField?.querySelector('.tag-input');
      const stage = settingsState.selectedStage;
      const name = input?.value.trim();
      if (!name) return Promise.resolve();
      if (!stage) {
        toast('Select a saved stage before adding tags.', 'error');
        return Promise.reject(new Error('No stage selected'));
      }
      return crm('stages/' + stage.id + '/tags', { method: 'POST', body: {
        name: name,
        sort_order: (stage.tags || []).length + 1,
        is_active: true
      } }).then(function () {
        input.value = '';
      });
    }

    function renderTags(tags) {
      if (!stageTagsField) return;
      stageTagsField.querySelectorAll('.tag-chip').forEach(function (chip) { chip.remove(); });
      const input = stageTagsField.querySelector('.tag-input');
      (tags || []).forEach(function (tag) {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'tag-chip live';
        chip.dataset.liveTag = tag.id;
        chip.setAttribute('aria-label', 'Edit ' + tag.name);
        chip.innerHTML = '<span>' + escapeHtml(tag.name) + '</span><i class="fa-solid fa-pencil" aria-hidden="true"></i>';
        stageTagsField.insertBefore(chip, input);
      });
    }

    function renderTransitions(stage) {
      if (!stageTransitionList) return;
      const pipeline = selectedPipeline();
      const selectedTransitionIds = new Set((stage?.transitions || []).map(function (transition) {
        return String(transition.id);
      }));
      const candidates = (pipeline?.stages || []).filter(function (candidate) {
        return stage && String(candidate.id) !== String(stage.id);
      });

      if (!candidates.length) {
        stageTransitionList.innerHTML = '<span class="description">Add another stage to configure transitions.</span>';
        return;
      }

      stageTransitionList.innerHTML = candidates.map(function (candidate) {
        return '<label class="stage-transition-choice"><input type="checkbox" data-stage-transition value="' + candidate.id + '"'
          + (selectedTransitionIds.has(String(candidate.id)) ? ' checked' : '') + '><span>' + escapeHtml(candidate.name) + '</span></label>';
      }).join('');
    }

    function selectStage(stage, button) {
      settingsState.selectedStage = stage || null;
      stageFlow?.querySelectorAll('[data-live-stage]').forEach(function (node) {
        node.classList.toggle('active', node === button);
      });
      if (stageNameInput) stageNameInput.value = stage?.name || '';
      renderTags(stage?.tags || []);
      renderTransitions(stage);
      if (stageDelete) stageDelete.style.display = stage ? 'inline-flex' : 'none';
    }

    function renderStages(pipeline, stageId) {
      if (!stageFlow) return;
      const stages = pipeline?.stages || [];
      if (!stages.length) {
        stageFlow.innerHTML = '<div class="description">This pipeline has no saved stages yet. Add a stage to start editing it.</div>';
        selectStage(null);
        return;
      }

      const openStages = stages.filter(function (stage) {
        return stage.category !== 'closed_won' && stage.category !== 'closed_lost';
      });
      const wonStage = stages.find(function (stage) { return stage.category === 'closed_won'; });
      const lostStage = stages.find(function (stage) { return stage.category === 'closed_lost'; });
      const openMarkup = openStages.map(function (stage) {
        return '<div class="stage-step"><button type="button" class="stage-node" data-live-stage="' + stage.id + '">'
          + escapeHtml(stage.name) + '</button><div class="stage-link"><button type="button" class="stage-add" data-stage-add data-stage-sort="'
          + (Number(stage.sort_order || 0) + 1) + '" aria-label="Add stage after ' + escapeHtml(stage.name) + '">+</button></div></div>';
      }).join('');
      const closedMarkup = wonStage || lostStage
        ? '<div class="closed-stage-row">'
          + (wonStage ? '<button type="button" class="stage-node won" data-live-stage="' + wonStage.id + '">' + escapeHtml(wonStage.name) + '</button>' : '<span></span>')
          + '<span class="branch-link" aria-hidden="true"></span>'
          + (lostStage ? '<button type="button" class="stage-node lost" data-live-stage="' + lostStage.id + '">' + escapeHtml(lostStage.name) + '</button>' : '<span></span>')
          + '</div>'
        : '';
      stageFlow.innerHTML = openMarkup + closedMarkup;
      const nextStage = selectedStageIn(pipeline, stageId) || stages[0];
      selectStage(nextStage, stageFlow.querySelector('[data-live-stage="' + nextStage.id + '"]'));
    }

    function renderRetryReasons(reasons) {
      if (!retryTable) return;
      retryTable.querySelectorAll('[data-retry-row]').forEach(function (row) { row.remove(); });
      const note = retryTable.querySelector('.retry-note');
      if (!reasons.length) {
        const row = document.createElement('div');
        row.className = 'retry-row';
        row.dataset.retryRow = '';
        row.innerHTML = '<p class="retry-reason">No retry reasons yet.</p><span></span><span></span><span></span>';
        retryTable.insertBefore(row, note);
        return;
      }
      reasons.forEach(function (reason) {
        const row = document.createElement('div');
        row.className = 'retry-row';
        row.dataset.retryRow = '';
        row.dataset.retryId = reason.id;
        row.dataset.retryReason = reason.name;
        row.dataset.retryRule = JSON.stringify(reason.rule || {});
        row.innerHTML = '<p class="retry-reason">' + escapeHtml(reason.name) + '</p>'
          + '<label class="retry-switch" aria-label="Toggle retry"><input type="checkbox" data-retry-toggle' + (reason.is_active ? ' checked' : '') + '><span></span></label>'
          + '<button type="button" class="setup-btn" data-retry-setup>Setup</button>'
          + '<button type="button" class="retry-action" data-retry-action-toggle aria-label="Retry actions"><i class="fa-solid fa-ellipsis-vertical"></i></button>';
        retryTable.insertBefore(row, note);
      });
    }

    function renderPriorities(rules) {
      if (!priorityList) return;
      priorityList.innerHTML = rules.map(function (rule) {
        if (rule.is_locked) {
          return '<div class="priority-item priority-locked" data-priority-locked data-priority-id="' + rule.id + '">' + escapeHtml(rule.name) + '</div>';
        }
        return '<div class="priority-item draggable" draggable="true" data-priority-item data-priority-id="' + rule.id + '">'
          + '<span class="priority-handle" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></span>'
          + '<span class="priority-name">' + escapeHtml(rule.name) + '</span></div>';
      }).join('');
    }

    function propertyRow(property, index) {
      return '<tr data-property-row data-property-id="' + property.id + '"><td>' + (index + 1) + '</td>'
        + '<td data-property-name>' + escapeHtml(property.name) + '</td><td data-property-type>' + escapeHtml(property.data_type) + '</td>'
        + '<td><span class="property-actions"><label class="retry-switch" aria-label="Toggle property">'
        + '<input type="checkbox" data-property-toggle' + (property.is_active ? ' checked' : '') + '><span></span></label>'
        + '<button type="button" class="property-icon-btn" data-property-edit aria-label="Edit property"><i class="fa-solid fa-pencil"></i></button>'
        + '<button type="button" class="property-icon-btn" data-property-delete aria-label="Delete property"><i class="fa-solid fa-trash"></i></button>'
        + '</span></td></tr>';
    }

    function renderProperties(properties) {
      if (!propertyBody) return;
      propertyBody.innerHTML = properties.map(propertyRow).join('');
      if (!properties.length) propertyBody.innerHTML = '<tr><td colspan="4">No custom contact properties yet.</td></tr>';
      if (propertyCount) propertyCount.textContent = properties.length + '/40';
    }

    function loadProperties() {
      return crm('contact-properties?per_page=40').then(function (payload) {
        renderProperties(unwrap(payload) || []);
      });
    }

    Promise.all([loadBootstrap(), loadUsers(), loadPipelines(), loadProperties(), crm('settings/retry-reasons')]).then(function (results) {
      const bootstrap = results[0] || {};
      fillProfile(bootstrap.business_profile || {});
      renderPipelineOptions(results[2] || []);
      renderPriorities(bootstrap.lead_priority_rules || []);
      renderRetryReasons(unwrap(results[4]) || []);
    });

    profileForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      if (!profileForm.reportValidity()) return;
      crm('settings/profile', { method: 'PUT', body: profilePayload() }).then(function (payload) {
        fillProfile(unwrap(payload) || {});
        toast('CRM profile saved.');
      });
    }, true);

    usersForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      if (!usersForm.reportValidity()) return;
      const formRows = Array.from(userRows?.querySelectorAll('[data-user-form-row]') || []);
      const editRow = usersBody?.querySelector('tr[data-crm-user-id] [data-user-actions-toggle][aria-expanded="true"]')?.closest('tr');
      const currentRow = usersForm?._crmEditRow || editRow;
      const request = currentRow
        ? crm('settings/users/' + currentRow.dataset.crmUserId, { method: 'PUT', body: userPayload(formRows[0], false) })
        : crm('settings/users', { method: 'POST', body: { users: formRows.map(function (row) { return userPayload(row, true); }) } });
      request.then(function () {
        closeBackdrop('[data-add-user-modal]');
        loadUsers();
        toast('CRM users saved.');
      });
    }, true);

    usersBody?.addEventListener('click', function (event) {
      const button = event.target.closest('[data-user-actions-toggle]');
      if (button) usersForm._crmEditRow = button.closest('tr');
    }, true);

    root.querySelector('[data-add-user-open]')?.addEventListener('click', function () {
      if (usersForm) usersForm._crmEditRow = null;
    }, true);

    root.querySelector('[data-user-actions-menu]')?.addEventListener('click', function (event) {
      const action = event.target.closest('[data-user-action]')?.dataset.userAction;
      const row = usersForm?._crmEditRow;
      if (!action || !row) return;
      const userId = row.dataset.crmUserId;
      if (action === 'deactivate') {
        crm('settings/users/' + userId + '/status', { method: 'PATCH', body: { crm_status: row.querySelector('.status-pill')?.textContent.trim() === 'active' ? 'inactive' : 'active' } }).then(loadUsers);
      }
      if (action === 'disable') {
        crm('settings/users/' + userId + '/status', { method: 'PATCH', body: { lead_assignment_enabled: false } }).then(function () { toast('Lead assignment disabled.'); });
      }
      if (action === 'delete') {
        event.stopImmediatePropagation();
        crm('settings/users/' + userId, { method: 'DELETE' }).then(function () {
          loadUsers();
          toast('CRM user deleted.');
        });
      }
    }, true);

    pipelineSelect?.addEventListener('change', function () {
      renderStages(settingsState.pipelines.find(function (pipeline) { return String(pipeline.id) === pipelineSelect.value; }));
    });

    stageCreate?.addEventListener('click', function (event) {
      event.stopImmediatePropagation();
      openStageModal();
    }, true);

    stageSettingsToggle?.addEventListener('click', function () {
      const open = stageSettingsToggle.getAttribute('aria-expanded') !== 'false';
      stageSettingsToggle.setAttribute('aria-expanded', open ? 'false' : 'true');
      if (stageSettingsBody) stageSettingsBody.hidden = open;
      const icon = stageSettingsToggle.querySelector('i');
      if (icon) icon.className = open ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-up';
    });

    stageModal?.addEventListener('click', function (event) {
      if (event.target === stageModal || event.target.closest('[data-stage-modal-close]')) {
        event.preventDefault();
        closeStageModal();
      }
    }, true);

    stageForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      if (!stageForm.reportValidity()) return;
      const pipeline = selectedPipeline();
      const name = stageCreateName?.value.trim();
      if (!pipeline || !name) return;
      crm('pipelines/' + pipeline.id + '/stages', { method: 'POST', body: {
        name: name,
        category: 'in_progress',
        color: '#0f766e',
        sort_order: Number(settingsState.stageInsertSortOrder || ((pipeline.stages || []).length + 1)),
        is_active: true
      } }).then(function (payload) {
        const stage = unwrap(payload);
        closeStageModal();
        reloadPipelines(pipeline.id, stage?.id);
        toast('Stage created.');
      });
    }, true);

    pipelineForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      if (!pipelineForm.reportValidity()) return;
      const name = pipelineForm.querySelector('[data-pipeline-name-input]')?.value.trim();
      const edit = pipelineForm.querySelector('[data-pipeline-modal-title]')?.textContent.includes('Edit');
      const selected = pipelineSelect?.value;
      const path = edit && selected ? 'pipelines/' + selected : 'pipelines';
      crm(path, { method: edit ? 'PUT' : 'POST', body: { name: name, color: '#763abb', is_active: true } }).then(function (payload) {
        const pipeline = unwrap(payload);
        reloadPipelines(pipeline?.id || selected);
        closeBackdrop('[data-pipeline-modal]');
        toast(edit ? 'Pipeline updated.' : 'Pipeline created.');
      });
    }, true);

    stageFlow?.addEventListener('click', function (event) {
      const addButton = event.target.closest('[data-stage-add]');
      if (addButton) {
        event.stopImmediatePropagation();
        openStageModal(Number(addButton.dataset.stageSort || 0));
        return;
      }
      const button = event.target.closest('[data-live-stage]');
      if (!button) return;
      const pipeline = selectedPipeline();
      selectStage((pipeline?.stages || []).find(function (stage) { return String(stage.id) === button.dataset.liveStage; }), button);
    }, true);

    stageNameInput?.addEventListener('input', function () {
      // Save button is always enabled now, we just validate on submit
    }, true);

    function saveSelectedStage(event) {
      event?.preventDefault();
      event.stopImmediatePropagation();
      if (!settingsState.selectedStage || !stageNameInput?.value.trim()) {
        toast('Select a saved stage before saving it.', 'error');
        return;
      }
      const stage = settingsState.selectedStage;
      const pipeline = selectedPipeline();
      const transitionIds = Array.from(stageTransitionList?.querySelectorAll('[data-stage-transition]:checked') || []).map(function (checkbox) {
        return Number(checkbox.value);
      });
      savePendingTag().then(function () {
        return crm('stages/' + stage.id, { method: 'PUT', body: {
          name: stageNameInput.value.trim(),
          code: stage.code,
          category: stage.category,
          color: stage.color,
          is_closed: stage.is_closed,
          is_active: stage.is_active,
          sort_order: stage.sort_order
        } });
      }).then(function () {
        return crm('stages/' + stage.id + '/transitions', { method: 'PUT', body: {
          transition_ids: transitionIds
        } });
      }).then(function () {
        reloadPipelines(pipeline?.id, stage.id);
        toast('Stage updated.');
      });
    }

    stageEditorForm?.addEventListener('submit', saveSelectedStage, true);

    stageDelete?.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      const stage = settingsState.selectedStage;
      const pipeline = selectedPipeline();
      if (!stage) return;
      if (!confirm('Are you sure you want to delete this stage?')) return;
      
      crm('stages/' + stage.id, { method: 'DELETE' }).then(function () {
        reloadPipelines(pipeline?.id);
        toast('Stage deleted.');
      });
    }, true);

    stageTagsField?.querySelector('.tag-input')?.addEventListener('keydown', function (event) {
      if (event.key !== 'Enter') return;
      event.preventDefault();
      event.stopImmediatePropagation();
      const stage = settingsState.selectedStage;
      const pipeline = selectedPipeline();
      savePendingTag().then(function () {
        reloadPipelines(pipeline?.id, stage?.id);
        toast('Stage tag added.');
      });
    }, true);

    stageTagsField?.querySelector('.tag-input')?.addEventListener('input', function () {
      // validation handled on submit
    }, true);

    stageTransitionList?.addEventListener('change', function (event) {
      if (!event.target.closest('[data-stage-transition]')) return;
    }, true);

    stageTagsField?.addEventListener('click', function (event) {
      const button = event.target.closest('[data-live-tag]');
      if (!button) return;
      event.stopImmediatePropagation();
      openTagModal((settingsState.selectedStage?.tags || []).find(function (tag) {
        return String(tag.id) === button.dataset.liveTag;
      }));
    }, true);

    tagModal?.addEventListener('click', function (event) {
      if (event.target === tagModal || event.target.closest('[data-tag-modal-close]')) {
        event.preventDefault();
        closeTagModal();
      }
    }, true);

    tagForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      const tag = settingsState.selectedTag;
      const stage = settingsState.selectedStage;
      const pipeline = selectedPipeline();
      if (!tag || !tagForm.reportValidity() || !tagNameInput?.value.trim()) return;
      crm('stage-tags/' + tag.id, { method: 'PUT', body: {
        name: tagNameInput.value.trim(),
        color: tag.color,
        sort_order: tag.sort_order,
        is_active: tag.is_active
      } }).then(function () {
        closeTagModal();
        reloadPipelines(pipeline?.id, stage?.id);
        toast('Stage tag updated.');
      });
    }, true);

    tagDelete?.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      const tag = settingsState.selectedTag;
      const stage = settingsState.selectedStage;
      const pipeline = selectedPipeline();
      if (!tag) return;
      crm('stage-tags/' + tag.id, { method: 'DELETE' }).then(function () {
        closeTagModal();
        reloadPipelines(pipeline?.id, stage?.id);
        toast('Stage tag deleted.');
      });
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;
      closeStageModal();
      closeTagModal();
    }, true);

    retryTable?.addEventListener('click', function (event) {
      const row = event.target.closest('[data-retry-row]');
      if (row) settingsState.retryReason = row;
    }, true);
    retryTable?.addEventListener('change', function (event) {
      const toggle = event.target.closest('[data-retry-toggle]');
      const row = toggle?.closest('[data-retry-row]');
      if (row) crm('settings/retry-reasons/' + row.dataset.retryId, { method: 'PUT', body: { is_active: toggle.checked } });
    });

    retryLogicForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      const row = settingsState.retryReason;
      if (!row || !retryLogicForm.reportValidity()) return;
      crm('settings/retry-reasons/' + row.dataset.retryId + '/rule', { method: 'PUT', body: {
        logic_type: (retryLogicForm.querySelector('input[name="retry_logic_type"]:checked')?.value || 'fixed').toLowerCase(),
        max_retries: Number(retryLogicForm.querySelector('[data-retry-count]')?.value || 5),
        interval_value: Number(retryLogicForm.querySelector('[data-retry-interval]')?.value || 1),
        interval_unit: (retryLogicForm.querySelector('[data-retry-unit]')?.value || 'hours').toLowerCase(),
        mark_lost_after_exhausted: true,
        is_active: true,
        apply_to_all: Boolean(retryLogicForm.querySelector('[data-retry-apply-all]')?.checked)
      } }).then(function () {
        closeBackdrop('[data-retry-logic-modal]');
        toast('Retry logic saved.');
      });
    }, true);

    retryReasonForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      const row = settingsState.retryReason;
      const name = retryReasonForm.querySelector('[data-retry-reason-input]')?.value.trim();
      if (!name) return;
      crm(row?.dataset.retryId ? 'settings/retry-reasons/' + row.dataset.retryId : 'settings/retry-reasons', {
        method: row?.dataset.retryId ? 'PUT' : 'POST',
        body: { name: name, is_active: true }
      }).then(function () {
        crm('settings/retry-reasons').then(function (payload) {
          renderRetryReasons(unwrap(payload) || []);
        });
        closeBackdrop('[data-retry-reason-modal]');
        toast(row?.dataset.retryId ? 'Retry reason updated.' : 'Retry reason created.');
      });
    }, true);

    root.querySelector('[data-retry-add]')?.addEventListener('click', function () {
      settingsState.retryReason = null;
      const input = document.querySelector('[data-retry-reason-input]');
      if (input) input.value = '';
      document.querySelector('[data-retry-reason-modal]')?.classList.add('open');
      document.querySelector('[data-retry-reason-modal]')?.setAttribute('aria-hidden', 'false');
      input?.focus();
    });

    root.querySelector('[data-retry-menu]')?.addEventListener('click', function (event) {
      if (event.target.closest('[data-retry-menu-action]')?.dataset.retryMenuAction !== 'delete' || !settingsState.retryReason) return;
      event.stopImmediatePropagation();
      crm('settings/retry-reasons/' + settingsState.retryReason.dataset.retryId, { method: 'DELETE' }).then(function () {
        settingsState.retryReason.remove();
        toast('Retry reason deleted.');
      });
    }, true);

    priorityList?.addEventListener('dragend', function () {
      const rules = Array.from(priorityList.querySelectorAll('[data-priority-item]')).map(function (item, index) {
        return { id: item.dataset.priorityId, sort_order: index + 2 };
      });
      if (!rules.length) return;
      crm('settings/lead-priority', { method: 'PUT', body: { rules: rules } }).then(function () {
        toast('Lead priority saved.');
      });
    });

    propertyBody?.addEventListener('click', function (event) {
      const row = event.target.closest('[data-property-row]');
      if (event.target.closest('[data-property-edit]')) settingsState.propertyId = row?.dataset.propertyId || null;
      if (event.target.closest('[data-property-delete]') && row?.dataset.propertyId) {
        event.stopImmediatePropagation();
        crm('contact-properties/' + row.dataset.propertyId, { method: 'DELETE' }).then(function () {
          loadProperties();
          toast('Contact property deleted.');
        });
      }
    }, true);
    propertyBody?.addEventListener('change', function (event) {
      const toggle = event.target.closest('[data-property-toggle]');
      const row = toggle?.closest('[data-property-row]');
      if (row?.dataset.propertyId) crm('contact-properties/' + row.dataset.propertyId + '/toggle', { method: 'PATCH' }).then(loadProperties);
    });
    root.querySelector('[data-property-add]')?.addEventListener('click', function () {
      settingsState.propertyId = null;
    }, true);
    propertyForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      const name = propertyForm.querySelector('[data-property-name-input]')?.value.trim();
      const dataType = propertyForm.querySelector('[data-property-type-input]')?.value;
      if (!name || !dataType) return;
      crm(settingsState.propertyId ? 'contact-properties/' + settingsState.propertyId : 'contact-properties', {
        method: settingsState.propertyId ? 'PUT' : 'POST',
        body: { name: name, data_type: dataType, is_active: true }
      }).then(function () {
        loadProperties();
        closeBackdrop('[data-property-modal]');
        toast('Contact property saved.');
      });
    }, true);
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindContactPage();
    hydrateDashboard();
    hydratePipeline();
    hydrateReportsAndTrends();
    hydrateSettings();
  });

  window.CallingCrmUi = {
    refreshBootstrap: loadBootstrap,
    refreshCampaigns: loadCampaigns,
    toast: toast
  };
})();
</script>
@endverbatim
