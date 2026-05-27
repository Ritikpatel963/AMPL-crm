(function () {
  const root = document.querySelector('.campaign-detail-app');
  if (!root) return;

  const campaignId = root.dataset.campaignId;
  const state = {
    campaign: null,
    summary: null
  };

  function crm(path, options) {
    if (!window.callingCrmRequest) {
      return Promise.reject(new Error('Calling CRM API is not configured.'));
    }
    return window.callingCrmRequest(path, options);
  }

  function unwrap(payload) {
    if (!payload) return null;
    return payload.data && payload.data.data ? payload.data.data : payload.data;
  }

  function setText(selector, value) {
    const node = root.querySelector(selector);
    if (node) node.textContent = value == null ? '0' : value;
  }

  function escapeHtml(value) {
    const node = document.createElement('span');
    node.textContent = value == null ? '' : String(value);
    return node.innerHTML;
  }

  function number(value) {
    return Number(value || 0);
  }

  function percent(value, total) {
    if (!total) return 0;
    return Math.max(0, Math.min(100, (value / total) * 100));
  }

  function paintDonut(selector, parts, fallbackColor) {
    const node = root.querySelector(selector);
    if (!node) return;

    const total = parts.reduce(function (sum, part) { return sum + number(part.value); }, 0);
    if (!total) {
      node.style.background = 'conic-gradient(' + fallbackColor + ' 0 100%)';
      return;
    }

    let cursor = 0;
    const stops = parts.map(function (part) {
      const start = cursor;
      cursor += percent(part.value, total);
      return part.color + ' ' + start + '% ' + cursor + '%';
    });
    node.style.background = 'conic-gradient(' + stops.join(', ') + ')';
  }

  function renderSummary() {
    const summary = state.summary || {};
    const campaign = state.campaign || summary.campaign || {};
    const total = number(summary.total_leads);
    const uncontacted = number(summary.uncontacted_leads);
    const inProgress = number(summary.in_progress_leads);
    const converted = number(summary.converted_leads);
    const lost = number(summary.lost_leads);
    const followUp = number(summary.follow_up_leads);
    const noFollowUp = number(summary.no_follow_up_leads);
    const closedBySystem = number(summary.closed_by_system);
    const closed = converted + lost + closedBySystem;

    setText('[data-campaign-title]', campaign.name || 'Campaign');
    setText('[data-campaign-priority]', (campaign.priority || 'medium').replace(/^./, function (char) { return char.toUpperCase(); }));
    setText('[data-manager-name]', campaign.manager?.name || campaign.name || 'Campaign');
    setText('[data-stat-total]', total);
    setText('[data-stat-uncontacted]', uncontacted);
    setText('[data-stat-progress]', inProgress);
    setText('[data-stat-closed]', closed);
    setText('[data-progress-total]', inProgress);
    setText('[data-progress-no-follow]', noFollowUp);
    setText('[data-progress-follow]', followUp);
    setText('[data-closed-total]', closed);
    setText('[data-closed-converted]', converted);
    setText('[data-closed-lost]', lost);
    setText('[data-closed-system]', closedBySystem);
    setText('[data-distribution-count]', total);

    const alert = root.querySelector('[data-paused-alert]');
    if (alert) alert.hidden = campaign.status !== 'paused' && campaign.status !== 'draft';

    const bar = root.querySelector('[data-distribution-bar]');
    if (bar) {
      const segments = [
        { value: uncontacted, color: '#16b335' },
        { value: noFollowUp, color: '#a99517' },
        { value: followUp, color: '#ffd747' },
        { value: closed, color: '#ff1111' }
      ];
      let cursor = 0;
      const gradient = segments
        .filter(function (segment) { return segment.value > 0; })
        .map(function (segment) {
          const start = cursor;
          cursor += percent(segment.value, total);
          return segment.color + ' ' + start + '% ' + cursor + '%';
        })
        .join(', ');
      bar.style.width = total ? '78%' : '0%';
      bar.style.background = gradient ? 'linear-gradient(90deg, ' + gradient + ')' : '#e5e7eb';
    }

    paintDonut('[data-donut-main]', [
      { value: uncontacted, color: '#16b335' },
      { value: inProgress, color: '#ffaf00' },
      { value: closed, color: '#ef4444' }
    ], '#e5e7eb');

    paintDonut('[data-donut-progress]', [
      { value: noFollowUp, color: '#ffd747' },
      { value: followUp, color: '#ff7b7b' }
    ], '#e5e7eb');

    paintDonut('[data-donut-closed]', [
      { value: converted, color: '#d861dc' },
      { value: lost, color: '#8c8c8c' }
    ], '#e5e7eb');
  }

  function renderImports(payload) {
    const imports = unwrap(payload) || [];
    const body = root.querySelector('[data-imports-body]');
    const count = root.querySelector('[data-imports-count]');
    if (count) count.textContent = '0 of ' + imports.length;
    if (!body) return;

    if (!imports.length) {
      body.innerHTML = '<tr><td colspan="8">No uploaded files found.</td></tr>';
      return;
    }

    body.innerHTML = imports.map(function (item, index) {
      const date = item.created_at ? new Date(item.created_at).toLocaleDateString() : '-';
      return '<tr>'
        + '<td>' + (index + 1) + '</td>'
        + '<td>' + escapeHtml(item.file_name || '-') + '</td>'
        + '<td>' + escapeHtml(date) + '</td>'
        + '<td><span class="campaign-file-status">' + escapeHtml(item.status || '-') + '</span></td>'
        + '<td>' + number(item.created_rows || item.successful_records) + '</td>'
        + '<td>' + number(item.merged_rows || item.merged_records) + '</td>'
        + '<td>' + number(item.merged_and_reopened_rows || item.merged_and_reopened_records) + '</td>'
        + '<td><button type="button" class="campaign-row-menu">⋮</button></td>'
        + '</tr>';
    }).join('');
  }

  function bindActions() {
    root.querySelector('[data-campaign-action-toggle]')?.addEventListener('click', function () {
      root.querySelector('[data-campaign-action-menu]')?.classList.toggle('open');
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('[data-campaign-action-toggle]') && !event.target.closest('[data-campaign-action-menu]')) {
        root.querySelector('[data-campaign-action-menu]')?.classList.remove('open');
      }
    });

    root.querySelector('[data-campaign-resume]')?.addEventListener('click', function () {
      crm('campaigns/' + campaignId + '/status', { method: 'PATCH', body: { status: 'active' } })
        .then(load);
    });

    root.querySelector('[data-campaign-refresh]')?.addEventListener('click', load);
  }

  function load() {
    Promise.all([
      crm('campaigns/' + campaignId),
      crm('campaigns/' + campaignId + '/summary'),
      crm('imports?campaign_id=' + encodeURIComponent(campaignId) + '&per_page=10')
    ]).then(function (results) {
      state.campaign = unwrap(results[0]);
      state.summary = unwrap(results[1]);
      if (state.summary && !state.summary.campaign) state.summary.campaign = state.campaign;
      renderSummary();
      renderImports(results[2]);
    }).catch(function () {
      setText('[data-campaign-title]', 'Unable to load campaign');
      const body = root.querySelector('[data-imports-body]');
      if (body) body.innerHTML = '<tr><td colspan="8">Unable to load uploaded files.</td></tr>';
    });
  }

  bindActions();
  load();
})();
