@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Lead Disposition Report')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
<style>
    /* ── Play button inside the table ─────────────────────────── */
    .crm-play-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        background: #f53003;
        color: #fff;
        border: none;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: background .2s;
    }
    .crm-play-btn:hover { background: #c42800; }

    /* ── Overlay backdrop ──────────────────────────────────────── */
    #crm-player-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.55);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    #crm-player-overlay.show { display: flex; }

    /* ── Modal card ────────────────────────────────────────────── */
    #crm-player-modal {
        background: #fff;
        border-radius: 16px;
        padding: 28px 32px;
        width: min(480px, 92vw);
        box-shadow: 0 24px 64px rgba(0,0,0,.25);
        text-align: center;
        position: relative;
    }
    #crm-player-modal h4 {
        margin: 0 0 18px;
        font-size: 15px;
        font-weight: 600;
        color: #1a1a2e;
    }
    #crm-player-modal audio {
        width: 100%;
        margin-bottom: 14px;
    }
    #crm-player-modal .crm-modal-close {
        position: absolute;
        top: 14px; right: 16px;
        background: none;
        border: none;
        font-size: 22px;
        color: #888;
        cursor: pointer;
        line-height: 1;
    }
    #crm-player-modal .crm-modal-close:hover { color: #333; }
    #crm-player-modal .crm-dl-link {
        font-size: 12px;
        color: #f53003;
        text-decoration: underline;
    }
    #crm-player-error {
        display: none;
        color: #c00;
        font-size: 13px;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('main-content')
<div class="calling-crm-canvas calling-crm-user-report calling-crm-lead-disposition-report" data-report-endpoint="reports/lead-disposition">
<main class="crm-page-main" data-report-list-url="{{ route('admin_panel.admin.callingcrm.report') }}">
    <div class="user-report-heading">
        <button class="user-report-back" type="button" data-report-back aria-label="Back to reports">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <h3>Lead Disposition Report</h3>
    </div>

    <div class="user-report-toolbar">
        <div class="user-report-filters">
            <div class="report-filter" data-filter-menu="date">
                <button class="report-filter-btn active" type="button" data-filter-toggle>
                    <span data-date-label>Today</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu date-menu">
                    <div class="filter-menu-title">Choose Disposition Date</div>
                    <div class="date-range-options">
                        <label class="date-range-option">
                            <input type="radio" name="lead_disposition_report_date_range" value="today" data-date-range-option checked>
                            <span>Today</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="lead_disposition_report_date_range" value="yesterday" data-date-range-option>
                            <span>Yesterday</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="lead_disposition_report_date_range" value="last7" data-date-range-option>
                            <span>Last 7 days</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="lead_disposition_report_date_range" value="last30" data-date-range-option>
                            <span>Last 30 days</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="lead_disposition_report_date_range" value="month" data-date-range-option>
                            <span>This Month</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="lead_disposition_report_date_range" value="custom" data-date-range-option>
                            <span>Custom Range</span>
                        </label>
                    </div>
                    <div class="custom-date-range" data-custom-date-range hidden>
                        <input type="date" data-custom-date-from aria-label="From date">
                        <input type="date" data-custom-date-to aria-label="To date">
                    </div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-date-apply>Apply</button>
                    </div>
                </div>
            </div>

            <div class="report-filter" data-filter-menu="managers">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-manager-label>Reporting manager</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Reporting manager</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="managers">
                    <div class="filter-options" data-manager-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                    </div>
                </div>
            </div>

            <div class="report-filter" data-filter-menu="users">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-user-label>Users</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Users</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="users">
                    <div class="filter-options" data-user-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="user-report-table-card">
        <table id="leadDispositionReportDataTable" class="user-report-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Disposed At</th>
                    <th>User Name</th>
                    <th>Reporting Manager</th>
                    <th>Mobile Number</th>
                    <th>Lead Name</th>
                    <th>Lead Phone</th>
                    <th>Campaign</th>
                    <th>Call Status</th>
                    <th>Recording</th>
                    <th>Disposition</th>
                    <th>Stage</th>
                    <th>Tag</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</main>
</div>

{{-- Audio Player Modal (lives outside DataTable so it never gets destroyed) --}}
<div id="crm-player-overlay">
    <div id="crm-player-modal">
        <button class="crm-modal-close" id="crm-modal-close-btn" type="button">&times;</button>
        <h4>&#9654;&nbsp; Call Recording</h4>
        <div id="crm-player-error">
            ⚠ This audio format cannot be played in your browser.<br>
            Please use the download link below.
        </div>
        <audio id="crm-modal-audio" controls></audio>
        <br>
        <a id="crm-modal-dl" class="crm-dl-link" href="#" download target="_blank">⬇ Download Recording</a>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/crm/pages/lead-disposition-report.js') }}"></script>
<script>
(function () {
    var overlay  = document.getElementById('crm-player-overlay');
    var audio    = document.getElementById('crm-modal-audio');
    var dlLink   = document.getElementById('crm-modal-dl');
    var errBox   = document.getElementById('crm-player-error');
    var closeBtn = document.getElementById('crm-modal-close-btn');

    function openPlayer(url) {
        // Upgrade http → https if page is https (prevents mixed-content block)
        if (window.location.protocol === 'https:') {
            url = url.replace(/^http:\/\//i, 'https://');
        }
        errBox.style.display = 'none';
        audio.src = url;
        dlLink.href = url;
        overlay.classList.add('show');
        audio.play().catch(function () {
            errBox.style.display = 'block';
        });
    }

    function closePlayer() {
        audio.pause();
        audio.src = '';
        overlay.classList.remove('show');
    }

    // Click inside table → find play button
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.crm-play-btn[data-audio-url]');
        if (btn) { openPlayer(btn.getAttribute('data-audio-url')); return; }

        // Click backdrop to close
        if (e.target === overlay) closePlayer();
    });

    closeBtn.addEventListener('click', closePlayer);
})();
</script>
@endpush
