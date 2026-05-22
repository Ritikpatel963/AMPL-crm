<?php $__env->startSection('title', 'Calling CRM Pipeline'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>


<style>
.calling-crm-canvas *, .calling-crm-canvas *::before, .calling-crm-canvas *::after { box-sizing: border-box; margin: 0; padding: 0; }

  .calling-crm-canvas {
    --bg: #f4f3f0;
    --surface: #ffffff;
    --surface2: #f9f8f6;
    --border: #e8e6e0;
    --border2: #d4d1c8;
    --text: #1a1916;
    --text2: #6b6960;
    --text3: #9b9890;
    --accent: #5b3bb5;
    --accent-light: #ede9fa;
    --blue: #1094f9;
    --blue-light: #e8f3fe;
    --amber: #d97706;
    --red: #dc2626;
    --green: #16a34a;
    --green-light: #dcfce7;
    --font: 'DM Sans', sans-serif;
    --mono: 'DM Mono', monospace;
    --radius: 10px;
    --radius-lg: 14px;
    --shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md: 0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
  }

  .calling-crm-canvas html, .calling-crm-canvas { height: 100%; font-family: var(--font); background: var(--bg); color: var(--text); font-size: 14px; line-height: 1.5; }

  /* ── Layout ── */
  .calling-crm-canvas.crm-pipeline-app { display: block; min-height: calc(100vh - 120px); overflow: visible; background: var(--bg); }

  /* ── Sidebar ── */
  .calling-crm-canvas .sidebar {
    width: 220px; flex-shrink: 0;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    padding: 0;
  }
  .calling-crm-canvas .sidebar-logo {
    padding: 20px 18px 16px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 8px;
  }
  .calling-crm-canvas .logo-mark {
    width: 28px; height: 28px; border-radius: 7px;
    background: var(--accent); display: flex; align-items: center; justify-content: center;
  }
  .calling-crm-canvas .logo-mark svg { width: 14px; height: 14px; fill: #fff; }
  .calling-crm-canvas .logo-text { font-size: 13px; font-weight: 600; letter-spacing: -.01em; }
  .calling-crm-canvas .logo-sub { font-size: 10px; color: var(--text3); font-family: var(--mono); margin-top: 1px; }

  .calling-crm-canvas .sidebar-nav { padding: 10px 8px; flex: 1; }
  .calling-crm-canvas .nav-section { font-size: 10px; font-weight: 500; color: var(--text3); letter-spacing: .08em; text-transform: uppercase; padding: 12px 10px 6px; }
  .calling-crm-canvas .nav-item {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: 8px;
    font-size: 13px; font-weight: 400; color: var(--text2);
    cursor: pointer; transition: all .15s;
    text-decoration: none; margin-bottom: 1px;
    border: 1px solid transparent;
  }
  .calling-crm-canvas .nav-item:hover { background: var(--bg); color: var(--text); }
  .calling-crm-canvas .nav-item.active { background: var(--accent-light); color: var(--accent); font-weight: 500; border-color: rgba(91,59,181,.12); }
  .calling-crm-canvas .nav-item svg { width: 15px; height: 15px; flex-shrink: 0; }

  .calling-crm-canvas .sidebar-footer {
    padding: 12px 8px;
    border-top: 1px solid var(--border);
  }
  .calling-crm-canvas .user-card {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: 8px; cursor: pointer;
  }
  .calling-crm-canvas .user-card:hover { background: var(--bg); }
  .calling-crm-canvas .avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--accent); color: #fff; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; }
  .calling-crm-canvas .user-name { font-size: 12px; font-weight: 500; }
  .calling-crm-canvas .user-role { font-size: 10px; color: var(--text3); }

  /* ── Pages ── */
  .calling-crm-canvas .page { display: none; flex: 1; flex-direction: column; min-width: 0; }
  .calling-crm-canvas .page.active { display: flex; }

  /* ── Top bar ── */
  .calling-crm-canvas .topbar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 24px;
    height: 56px;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
  }
  .calling-crm-canvas .topbar-left { display: flex; align-items: center; gap: 12px; }
  .calling-crm-canvas .topbar-back {
    display: flex; align-items: center; gap: 5px;
    font-size: 12px; color: var(--text3); cursor: pointer;
    padding: 5px 8px; border-radius: 6px; transition: all .15s;
    border: 1px solid transparent;
  }
  .calling-crm-canvas .topbar-back:hover { color: var(--text); background: var(--bg); border-color: var(--border); }
  .calling-crm-canvas .topbar-back svg { width: 13px; height: 13px; }
  .calling-crm-canvas .page-title { font-size: 15px; font-weight: 600; letter-spacing: -.01em; }
  .calling-crm-canvas .topbar-right { display: flex; align-items: center; gap: 8px; }

  .calling-crm-canvas .search-wrap {
    display: flex; align-items: center; gap: 7px;
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 8px; padding: 6px 10px;
  }
  .calling-crm-canvas .search-wrap svg { width: 13px; height: 13px; color: var(--text3); flex-shrink: 0; }
  .calling-crm-canvas .search-wrap input {
    border: none; background: transparent; outline: none;
    font-size: 12px; color: var(--text); font-family: var(--font);
    width: 160px;
  }
  .calling-crm-canvas .search-wrap input::placeholder { color: var(--text3); }

  /* Buttons */
  .calling-crm-canvas .btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: 8px; font-size: 12px;
    font-family: var(--font); font-weight: 500; cursor: pointer;
    transition: all .15s; border: 1px solid var(--border);
    background: var(--surface); color: var(--text);
  }
  .calling-crm-canvas .btn:hover { background: var(--bg); border-color: var(--border2); }
  .calling-crm-canvas .btn svg { width: 13px; height: 13px; }
  .calling-crm-canvas .btn-primary {
    background: var(--accent); color: #fff; border-color: var(--accent);
  }
  .calling-crm-canvas .btn-primary:hover { background: #4a2fa0; border-color: #4a2fa0; }

  /* ── All Campaigns page ── */
  .calling-crm-canvas .page-body { padding: 20px 24px; overflow-y: auto; flex: 1; }

  .calling-crm-canvas .group-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    margin-bottom: 16px;
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: slideUp .3s ease both;
  }
  .calling-crm-canvas .group-card:nth-child(2) { animation-delay: .06s; }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .calling-crm-canvas .group-head {
    padding: 14px 18px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid var(--border);
    background: var(--surface2);
  }
  .calling-crm-canvas .group-head-left { display: flex; align-items: center; gap: 10px; }
  .calling-crm-canvas .group-accent-bar { width: 4px; height: 20px; border-radius: 2px; }
  .calling-crm-canvas .group-title { font-size: 12px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
  .calling-crm-canvas .group-count { font-size: 11px; font-weight: 500; font-family: var(--mono); background: var(--bg); border: 1px solid var(--border); padding: 1px 7px; border-radius: 20px; color: var(--text2); }

  .calling-crm-canvas .arrow-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s; color: var(--text2);
  }
  .calling-crm-canvas .arrow-btn:hover { background: var(--accent); border-color: var(--accent); color: #fff; }
  .calling-crm-canvas .arrow-btn svg { width: 13px; height: 13px; }

  .calling-crm-canvas .camp-list { padding: 0; }
  .calling-crm-canvas .camp-item {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 18px;
    border-bottom: 1px solid var(--border);
    transition: background .12s;
    cursor: pointer;
  }
  .calling-crm-canvas .camp-item:last-child { border-bottom: none; }
  .calling-crm-canvas .camp-item:hover { background: var(--bg); }

  .calling-crm-canvas .prio-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
  .calling-crm-canvas .prio-medium { background: var(--amber); }
  .calling-crm-canvas .prio-high { background: var(--red); }

  .calling-crm-canvas .camp-name { flex: 1; font-size: 13px; font-weight: 400; }

  .calling-crm-canvas .paused-badge {
    font-size: 10px; font-weight: 500; font-family: var(--mono);
    color: var(--text3); background: var(--bg);
    border: 1px solid var(--border); padding: 2px 8px; border-radius: 20px;
  }
  .calling-crm-canvas .active-badge {
    font-size: 10px; font-weight: 500; font-family: var(--mono);
    color: var(--green); background: var(--green-light);
    border: 1px solid #bbf7d0; padding: 2px 8px; border-radius: 20px;
  }

  .calling-crm-canvas .more-btn {
    width: 26px; height: 26px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    color: var(--text3); cursor: pointer; transition: all .12s;
  }
  .calling-crm-canvas .more-btn:hover { background: var(--border); color: var(--text); }
  .calling-crm-canvas .more-btn svg { width: 14px; height: 14px; }

  /* ── Detail page ── */
  .calling-crm-canvas .detail-body {
    display: flex; flex: 1; min-height: 0; overflow: hidden;
  }

  .calling-crm-canvas .detail-left { flex: 1; min-width: 0; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; }
  .calling-crm-canvas .detail-right { width: 280px; flex-shrink: 0; border-left: 1px solid var(--border); overflow-y: auto; background: var(--surface); }

  /* Cards */
  .calling-crm-canvas .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: slideUp .25s ease both;
  }
  .calling-crm-canvas .card:nth-child(2) { animation-delay: .08s; }
  .calling-crm-canvas .card-head {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface2);
    display: flex; align-items: center; justify-content: space-between;
  }
  .calling-crm-canvas .card-head-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--text2); }
  .calling-crm-canvas .card-body { padding: 16px; }

  /* Stats row */
  .calling-crm-canvas .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
  .calling-crm-canvas .stat-tile { background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px 14px; }
  .calling-crm-canvas .stat-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .07em; color: var(--text3); margin-bottom: 5px; }
  .calling-crm-canvas .stat-value { font-size: 22px; font-weight: 600; font-family: var(--mono); letter-spacing: -.02em; }
  .calling-crm-canvas .stat-value.blue { color: var(--blue); }
  .calling-crm-canvas .stat-value.amber { color: var(--amber); }
  .calling-crm-canvas .stat-value.green { color: var(--green); }

  /* Funnel */
  .calling-crm-canvas .funnel-container { display: flex; justify-content: center; padding: 8px 0 4px; }
  .calling-crm-canvas svg.funnel { overflow: visible; }

  /* Pie charts */
  .calling-crm-canvas .pie-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
  .calling-crm-canvas .pie-box { display: flex; flex-direction: column; align-items: center; }
  .calling-crm-canvas .pie-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--text2); margin-bottom: 8px; text-align: center; }
  .calling-crm-canvas .pie-svg-wrap { display: flex; justify-content: center; }
  .calling-crm-canvas .legend { display: flex; flex-direction: column; gap: 4px; margin-top: 8px; width: 100%; }
  .calling-crm-canvas .legend-row { display: flex; align-items: flex-start; gap: 5px; font-size: 10px; color: var(--text2); line-height: 1.3; }
  .calling-crm-canvas .legend-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; margin-top: 2px; }
  .calling-crm-canvas .legend-num { font-family: var(--mono); font-size: 9px; color: var(--text3); }

  /* Right panel */
  .calling-crm-canvas .right-panel-head {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--surface2);
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 1;
  }
  .calling-crm-canvas .right-panel-title { font-size: 12px; font-weight: 600; }
  .calling-crm-canvas .toggle-wrap { display: flex; align-items: center; gap: 6px; font-size: 10px; color: var(--text3); cursor: pointer; }
  .calling-crm-canvas .toggle { width: 30px; height: 16px; background: var(--border2); border-radius: 8px; position: relative; transition: background .2s; }
  .calling-crm-canvas .toggle.on { background: var(--accent); }
  .calling-crm-canvas .toggle-thumb { width: 12px; height: 12px; background: #fff; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: left .2s; box-shadow: 0 1px 2px rgba(0,0,0,.15); }
  .calling-crm-canvas .toggle.on .toggle-thumb { left: 16px; }

  .calling-crm-canvas .right-camp-list { padding: 6px 0; }
  .calling-crm-canvas .right-camp-item {
    padding: 9px 16px; display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid var(--border);
    transition: background .12s; cursor: pointer;
  }
  .calling-crm-canvas .right-camp-item:last-child { border-bottom: none; }
  .calling-crm-canvas .right-camp-item:hover { background: var(--bg); }
  .calling-crm-canvas .checkbox {
    width: 15px; height: 15px; border-radius: 4px;
    border: 1.5px solid var(--border2); flex-shrink: 0;
    transition: all .15s;
  }
  .calling-crm-canvas .right-camp-item:hover .checkbox { border-color: var(--accent); }
  .calling-crm-canvas .right-camp-name { flex: 1; font-size: 12px; }
  .calling-crm-canvas .right-camp-actions { display: flex; align-items: center; gap: 6px; }

  /* Scrollbar */
  .calling-crm-canvas ::-webkit-scrollbar { width: 5px; }
  .calling-crm-canvas ::-webkit-scrollbar-track { background: transparent; }
  .calling-crm-canvas ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 10px; }
  .calling-crm-canvas ::-webkit-scrollbar-thumb:hover { background: var(--text3); }

  /* Divider tag in topbar */
  .calling-crm-canvas .topbar-sep { width: 1px; height: 20px; background: var(--border); }
</style>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas crm-pipeline-app">
<!-- ALL CAMPAIGNS PAGE -->
  <div class="page active" id="page-all">
    <div class="topbar">
      <div class="topbar-left">
        <span class="page-title">All Campaigns</span>
      </div>
      <div class="topbar-right">
        <div class="search-wrap">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6.5" cy="6.5" r="5"/><path d="M11 11l3 3"/></svg>
          <input type="text" placeholder="Search campaign…" />
        </div>
      </div>
    </div>

    <div class="page-body">

      <!-- Indore Sales Group -->
      <div class="group-card">
        <div class="group-head">
          <div class="group-head-left">
            <div class="group-accent-bar" style="background:#763abb;"></div>
            <span class="group-title">Indore Sales</span>
            <span class="group-count">8</span>
          </div>
          <div class="arrow-btn" onclick="showPage('detail')" title="Open pipeline">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
          </div>
        </div>
        <div class="camp-list">
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Vivek Leads</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Ravi UP Data</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data CG</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data RJ</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-high"></div><span class="camp-name">Existing Customers</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data MH</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data UP</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Nandini RJ Raw Data</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
        </div>
      </div>

      <!-- Leads Group -->
      <div class="group-card">
        <div class="group-head">
          <div class="group-head-left">
            <div class="group-accent-bar" style="background:#1094f9;"></div>
            <span class="group-title">Leads</span>
            <span class="group-count">7</span>
          </div>
          <div class="arrow-btn">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
          </div>
        </div>
        <div class="camp-list">
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">SME Data</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">MP Transacted</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-high"></div><span class="camp-name">MP Raw Data</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Trading RAW</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Trading Transacted</span><span class="active-badge">Active</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data Odisha</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
          <div class="camp-item"><div class="prio-dot prio-medium"></div><span class="camp-name">Raw Data BR</span><span class="paused-badge">Paused</span><div class="more-btn"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="3" r="1.2"/><circle cx="8" cy="8" r="1.2"/><circle cx="8" cy="13" r="1.2"/></svg></div></div>
        </div>
      </div>

    </div>
  </div>

  <!-- DETAIL PAGE -->
  <div class="page" id="page-detail">
    <div class="topbar">
      <div class="topbar-left">
        <div class="topbar-back" onclick="showPage('all')">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 8H3M7 4L3 8l4 4"/></svg>
          All Campaigns
        </div>
        <div class="topbar-sep"></div>
        <span class="page-title">Indore Sales Pipeline</span>
      </div>
      <div class="topbar-right">
        <div class="search-wrap">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6.5" cy="6.5" r="5"/><path d="M11 11l3 3"/></svg>
          <input type="text" placeholder="Search campaign…" />
        </div>
        <button class="btn">Lead Summary</button>
        <button class="btn">Call Logs</button>
        <button class="btn">
          Action
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
        </button>
        <button class="btn btn-primary">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v12M2 8h12"/></svg>
          Create Campaign
        </button>
      </div>
    </div>

    <div class="detail-body">
      <!-- Left panel -->
      <div class="detail-left">

        <!-- Funnel card -->
        <div class="card">
          <div class="card-head">
            <span class="card-head-title">Lead funnel by stages</span>
          </div>
          <div class="card-body">
            <div class="stats-row">
              <div class="stat-tile"><div class="stat-label">Total Leads</div><div class="stat-value">45,692</div></div>
              <div class="stat-tile"><div class="stat-label">In-Progress</div><div class="stat-value amber">1,535</div></div>
              <div class="stat-tile"><div class="stat-label">Closed</div><div class="stat-value green">3,218</div></div>
            </div>
            <div class="funnel-container">
              <svg class="funnel" width="420" height="200" viewBox="0 0 420 200" role="img" aria-label="Lead funnel showing stages">
                <title>Lead funnel by stages</title>
                <!-- Fresh Leads -->
                <polygon points="20,0 400,0 270,128 150,128" fill="#3b82f6" opacity=".85" rx="4"/>
                <text x="210" y="50" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="13" font-weight="600" fill="#ffffff">Fresh Leads</text>
                <text x="210" y="68" text-anchor="middle" font-family="DM Mono, monospace" font-size="12" fill="rgba(255,255,255,.8)">40,939</text>
                <text x="210" y="84" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="11" fill="rgba(255,255,255,.7)">89.60%</text>
                <!-- Connector lines to labels -->
                <line x1="290" y1="64" x2="340" y2="64" stroke="#3b82f6" stroke-width="1" stroke-dasharray="3,2" opacity=".5"/>
                <!-- Stage 2: Follow Up -->
                <rect x="150" y="132" width="120" height="16" rx="3" fill="#7c3aed" opacity=".85"/>
                <text x="210" y="144" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9.5" font-weight="500" fill="#ffffff">Follow-up Mandatory — 2.72%</text>
                <!-- Stage 3 -->
                <rect x="155" y="151" width="110" height="13" rx="3" fill="#059669" opacity=".85"/>
                <text x="210" y="161" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9" font-weight="500" fill="#ffffff">Catalog &amp; Price Shared — 0.61%</text>
                <!-- Stage 4 -->
                <rect x="160" y="167" width="100" height="12" rx="3" fill="#d97706" opacity=".85"/>
                <text x="210" y="176.5" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9" font-weight="500" fill="#ffffff">Negotiation / Price Issue — 0.03%</text>
                <!-- Stage 5 -->
                <rect x="165" y="182" width="90" height="12" rx="3" fill="#dc2626" opacity=".85"/>
                <text x="210" y="191.5" text-anchor="middle" font-family="DM Sans, sans-serif" font-size="9" font-weight="500" fill="#ffffff">Closed Won — 0.05%</text>
              </svg>
            </div>
          </div>
        </div>

        <!-- Tags / Pie card -->
        <div class="card">
          <div class="card-head">
            <span class="card-head-title">Leads by tags</span>
          </div>
          <div class="card-body">
            <div class="pie-grid">

              <!-- Fresh Leads Pie -->
              <div class="pie-box">
                <div class="pie-label">Fresh Leads</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="Fresh leads by tag">
                    <title>Fresh leads tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,0,1,0.637,-0.771 Z" fill="#3b82f6"/>
                    <path d="M0,0 L0.637,-0.771 A1,1,0,1,1,-0.980,0.201 Z" fill="#7c3aed"/>
                    <path d="M0,0 L-0.980,0.201 A1,1,0,0,1,0,-1 Z" fill="#f59e0b"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#3b82f6;"></div><div>Out of Station <span class="legend-num">(132)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#7c3aed;"></div><div>Future Req. <span class="legend-num">(1722)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#f59e0b;"></div><div>No Season <span class="legend-num">(212)</span></div></div>
                </div>
              </div>

              <!-- In-Progress Pie -->
              <div class="pie-box">
                <div class="pie-label">In-Progress</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="In-progress by tag">
                    <title>In-progress tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,0,1,0.810,0.587 Z" fill="#ef4444"/>
                    <path d="M0,0 L0.810,0.587 A1,1,0,0,1,-0.556,0.831 Z" fill="#7c3aed"/>
                    <path d="M0,0 L-0.556,0.831 A1,1,0,0,1,-0.773,0.634 Z" fill="#6b7280"/>
                    <path d="M0,0 L-0.773,0.634 A1,1,0,0,1,0,-1 Z" fill="#f97316"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#ef4444;"></div><div>Msg Shared <span class="legend-num">(482)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#7c3aed;"></div><div>Future Req. <span class="legend-num">(227)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#6b7280;"></div><div>No Season <span class="legend-num">(23)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#f97316;"></div><div>Cust. Busy <span class="legend-num">(475)</span></div></div>
                </div>
              </div>

              <!-- Closed Lost Pie -->
              <div class="pie-box">
                <div class="pie-label">Closed Lost</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="Closed lost by tag">
                    <title>Closed lost tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,1,1,-0.317,-0.948 Z" fill="#ef4444"/>
                    <path d="M0,0 L-0.317,-0.948 A1,1,0,0,1,-0.277,-0.961 Z" fill="#3b82f6"/>
                    <path d="M0,0 L-0.277,-0.961 A1,1,0,0,1,-0.031,-1.000 Z" fill="#f59e0b"/>
                    <path d="M0,0 L-0.031,-1.000 A1,1,0,0,1,0,-1 Z" fill="#a78bfa"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#ef4444;"></div><div>No Shop <span class="legend-num">(2860)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#3b82f6;"></div><div>Invalid No. <span class="legend-num">(20)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#f59e0b;"></div><div>No Incoming <span class="legend-num">(120)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#a78bfa;"></div><div>No. N/A <span class="legend-num">(13)</span></div></div>
                </div>
              </div>

              <!-- Closed Won Pie -->
              <div class="pie-box">
                <div class="pie-label">Closed Won</div>
                <div class="pie-svg-wrap">
                  <svg width="80" height="80" viewBox="-1 -1 2 2" role="img" aria-label="Closed won by tag">
                    <title>Closed won tag distribution</title>
                    <path d="M0,0 L0,-1 A1,1,0,0,1,0.663,0.748 Z" fill="#16a34a"/>
                    <path d="M0,0 L0.663,0.748 A1,1,0,0,1,-0.934,0.358 Z" fill="#059669"/>
                    <path d="M0,0 L-0.934,0.358 A1,1,0,0,1,0,-1 Z" fill="#34d399"/>
                  </svg>
                </div>
                <div class="legend">
                  <div class="legend-row"><div class="legend-dot" style="background:#16a34a;"></div><div>Deal Closed <span class="legend-num">(5)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#059669;"></div><div>Full Payment <span class="legend-num">(4)</span></div></div>
                  <div class="legend-row"><div class="legend-dot" style="background:#34d399;"></div><div>Order Recv. <span class="legend-num">(4)</span></div></div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Right panel: Campaigns list -->
      <div class="detail-right">
        <div class="right-panel-head">
          <span class="right-panel-title">Campaigns</span>
          <div class="toggle-wrap" onclick="togglePaused(this)">
            Hide paused
            <div class="toggle" id="toggle-btn"><div class="toggle-thumb"></div></div>
          </div>
        </div>
        <div class="right-camp-list" id="right-camp-list"></div>
      </div>
    </div>
  </div>

</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
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
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/crm-core.js')); ?>"></script>
<script type="module" src="<?php echo e(asset('js/crm/pipeline.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/callingcrm/pipeline.blade.php ENDPATH**/ ?>