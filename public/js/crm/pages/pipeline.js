const campaigns = [
    {name:'Vivek Leads',prio:'medium',paused:true},
    {name:'Ravi UP Data',prio:'medium',paused:true},
    {name:'Raw Data CG',prio:'medium',paused:true},
    {name:'Raw Data RJ',prio:'medium',paused:true},
    {name:'Existing Customers',prio:'high',paused:true},
    {name:'Raw Data MH',prio:'medium',paused:true},
    {name:'Raw Data UP',prio:'medium',paused:true},
    {name:'Nandini RJ Raw Data',prio:'medium',paused:true},
  ];

  let hidePaused = false;

  function renderRightPanel() {
    const list = document.getElementById('right-camp-list');
    const visible = hidePaused ? campaigns.filter(c => !c.paused) : campaigns;
    list.innerHTML = visible.map(c => `
      <div class="right-camp-item">
        <div class="checkbox"></div>
        <div class="prio-dot ${c.prio === 'high' ? 'prio-high' : 'prio-medium'}"></div>
        <span class="right-camp-name">${c.name}</span>
        <div class="right-camp-actions">
          ${c.paused ? '<span class="paused-badge">Paused</span>' : '<span class="active-badge">Active</span>'}
          <div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor" width="14" height="14"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div>
        </div>
      </div>
    `).join('');
  }

  function togglePaused(wrap) {
    hidePaused = !hidePaused;
    const btn = document.getElementById('toggle-btn');
    btn.classList.toggle('on', hidePaused);
    renderRightPanel();
  }

  function showPage(id) {
    document.getElementById('page-all').classList.toggle('active', id === 'all');
    document.getElementById('page-detail').classList.toggle('active', id === 'detail');
    document.querySelectorAll('.nav-item').forEach((el, i) => {
      el.classList.toggle('active', i === 0);
    });
  }

  renderRightPanel();
