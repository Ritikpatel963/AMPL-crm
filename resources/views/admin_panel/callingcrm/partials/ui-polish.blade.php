@once
    @push('styles')
        <style>
            :root {
                --calling-crm-primary: #763abb;
                --calling-crm-primary-hover: #6230a0;
                --calling-crm-primary-soft: #ede5fa;
                --calling-crm-bg: #f4f6fb;
                --calling-crm-surface: #ffffff;
                --calling-crm-surface-soft: #f8fafc;
                --calling-crm-border: #e4e7ef;
                --calling-crm-border-strong: #cfd5e3;
                --calling-crm-text: #182033;
                --calling-crm-muted: #667085;
                --calling-crm-shadow: 0 1px 2px rgba(16, 24, 40, .05), 0 10px 24px rgba(16, 24, 40, .06);
            }

            .calling-crm-canvas,
            .calling-crm-settings,
            .ccp-page,
            .login-report-page {
                background: var(--calling-crm-bg) !important;
                color: var(--calling-crm-text);
                width: 100%;
                min-width: 0;
                overflow-x: hidden;
                -webkit-font-smoothing: antialiased;
                text-rendering: optimizeLegibility;
            }

            .calling-crm-canvas .main-wrapper,
            .calling-crm-canvas .crm-page-main,
            .calling-crm-settings,
            .ccp-page,
            .login-report-page {
                padding: clamp(18px, 2vw, 28px) !important;
            }

            .calling-crm-canvas .page-title,
            .calling-crm-canvas .page-heading,
            .calling-crm-settings .settings-title,
            .calling-crm-settings .users-title,
            .ccp-heading,
            .ccp-title,
            .login-page-title,
            .login-panel-title {
                color: var(--calling-crm-text) !important;
                letter-spacing: 0 !important;
            }

            .calling-crm-canvas .card,
            .calling-crm-canvas .chart-card,
            .calling-crm-canvas .widget-card,
            .calling-crm-canvas .table-card,
            .calling-crm-canvas .toolbar,
            .calling-crm-canvas .summary-card,
            .calling-crm-canvas .group-card,
            .calling-crm-canvas .crm-modal,
            .calling-crm-settings .content-wrap,
            .calling-crm-settings .table-card,
            .calling-crm-settings .settings-tabs-row,
            .calling-crm-settings .crm-modal,
            .ccp-wrapper,
            .ccp-tabs,
            .ccp-table-wrap,
            .login-panel {
                border-color: var(--calling-crm-border) !important;
                border-radius: 8px !important;
                box-shadow: var(--calling-crm-shadow) !important;
            }

            .calling-crm-canvas .card:hover,
            .calling-crm-canvas .chart-card:hover,
            .calling-crm-canvas .widget-card:hover,
            .calling-crm-settings .content-wrap:hover,
            .ccp-wrapper:hover,
            .login-panel:hover {
                box-shadow: 0 1px 2px rgba(16, 24, 40, .06), 0 14px 30px rgba(16, 24, 40, .08) !important;
            }

            .calling-crm-canvas button,
            .calling-crm-canvas a,
            .calling-crm-settings button,
            .calling-crm-settings a,
            .ccp-page button,
            .ccp-page a,
            .login-report-page button,
            .login-report-page a {
                text-decoration: none;
            }

            .calling-crm-canvas button:focus-visible,
            .calling-crm-canvas a:focus-visible,
            .calling-crm-canvas input:focus-visible,
            .calling-crm-canvas select:focus-visible,
            .calling-crm-settings button:focus-visible,
            .calling-crm-settings a:focus-visible,
            .calling-crm-settings input:focus-visible,
            .calling-crm-settings select:focus-visible,
            .ccp-page button:focus-visible,
            .ccp-page a:focus-visible,
            .login-report-page button:focus-visible,
            .login-report-page a:focus-visible {
                outline: 3px solid rgba(118, 58, 187, .18) !important;
                outline-offset: 2px;
            }

            .calling-crm-canvas .btn-primary,
            .calling-crm-canvas .lead-submit-btn,
            .calling-crm-canvas .btn-search,
            .calling-crm-canvas .crm-apply-btn,
            .calling-crm-settings .action-btn.primary,
            .calling-crm-settings .save-btn,
            .ccp-add-btn,
            .login-go-btn {
                background: var(--calling-crm-primary) !important;
                border-color: var(--calling-crm-primary) !important;
                color: #fff !important;
            }

            .calling-crm-canvas .btn-primary:hover,
            .calling-crm-canvas .lead-submit-btn:hover,
            .calling-crm-canvas .btn-search:hover,
            .calling-crm-canvas .crm-apply-btn:hover,
            .calling-crm-settings .action-btn.primary:hover,
            .calling-crm-settings .save-btn:hover,
            .ccp-add-btn:hover,
            .login-go-btn:hover {
                background: var(--calling-crm-primary-hover) !important;
                border-color: var(--calling-crm-primary-hover) !important;
            }

            .calling-crm-canvas .filter-tab.active,
            .calling-crm-canvas .filter-btn.applied,
            .calling-crm-canvas .switch-btn.selected,
            .calling-crm-canvas .nav-item.active,
            .calling-crm-settings .tab-item.active,
            .ccp-tab.active {
                color: var(--calling-crm-primary) !important;
                border-color: rgba(118, 58, 187, .28) !important;
                background: var(--calling-crm-primary-soft) !important;
            }

            .calling-crm-canvas input,
            .calling-crm-canvas select,
            .calling-crm-canvas textarea,
            .calling-crm-settings input,
            .calling-crm-settings select,
            .calling-crm-settings textarea,
            .login-report-page input,
            .login-report-page select {
                max-width: 100%;
            }

            .calling-crm-canvas input:focus,
            .calling-crm-canvas select:focus,
            .calling-crm-canvas textarea:focus,
            .calling-crm-settings input:focus,
            .calling-crm-settings select:focus,
            .calling-crm-settings textarea:focus,
            .login-report-page input:focus,
            .login-report-page select:focus {
                border-color: var(--calling-crm-primary) !important;
                box-shadow: 0 0 0 3px rgba(118, 58, 187, .12) !important;
            }

            .calling-crm-canvas table,
            .calling-crm-settings table,
            .ccp-table {
                border-collapse: separate !important;
                border-spacing: 0;
            }

            .calling-crm-canvas thead th,
            .calling-crm-settings th,
            .ccp-table th {
                background: var(--calling-crm-surface-soft) !important;
                color: var(--calling-crm-muted) !important;
                font-weight: 800 !important;
            }

            .calling-crm-canvas tbody tr:hover,
            .calling-crm-settings tbody tr:hover td,
            .ccp-table tbody tr:hover td {
                background: #f7f3fd !important;
            }

            .calling-crm-canvas .table-wrapper,
            .calling-crm-canvas .table-scroll,
            .calling-crm-settings .table-scroll,
            .ccp-table-wrap {
                overflow-x: auto;
                scrollbar-width: thin;
                scrollbar-color: rgba(118, 58, 187, .28) transparent;
            }

            @media (max-width: 991px) {
                .calling-crm-canvas .dashboard-grid,
                .calling-crm-canvas #page-all .page-body,
                .calling-crm-canvas .charts-grid,
                .calling-crm-settings .users-toolbar,
                .login-report-grid {
                    grid-template-columns: minmax(0, 1fr) !important;
                }

                .calling-crm-settings .toolbar-actions,
                .calling-crm-canvas .header-actions,
                .calling-crm-canvas .right-actions,
                .calling-crm-canvas .toolbar {
                    justify-content: flex-start !important;
                }
            }

            @media (max-width: 640px) {
                .calling-crm-canvas .main-wrapper,
                .calling-crm-canvas .crm-page-main,
                .calling-crm-settings,
                .ccp-page,
                .login-report-page {
                    padding: 16px 12px !important;
                }

                .calling-crm-canvas .page-header,
                .calling-crm-canvas .top-bar,
                .calling-crm-canvas .card-header,
                .calling-crm-settings .users-title-row,
                .ccp-top,
                .login-panel-head {
                    align-items: flex-start !important;
                    flex-direction: column !important;
                }

                .calling-crm-canvas button,
                .calling-crm-canvas .btn,
                .calling-crm-canvas .btn-outline,
                .calling-crm-canvas .btn-primary,
                .calling-crm-settings .action-btn,
                .calling-crm-settings .learn-btn,
                .ccp-add-btn,
                .login-download-btn,
                .login-go-btn {
                    min-width: 0;
                    white-space: normal;
                }

                .calling-crm-settings .search-box,
                .calling-crm-canvas .search-box,
                .calling-crm-canvas .search-wrap,
                .login-control,
                .login-date-control {
                    width: 100% !important;
                    min-width: 0 !important;
                }
            }

            /* PREMIUM SKELETON SHIMMER LOADERS */
            @keyframes skeleton-shimmer {
                0% {
                    background-position: -200% 0;
                }
                100% {
                    background-position: 200% 0;
                }
            }

            .skeleton-loader {
                display: inline-block;
                height: 1em;
                width: 100%;
                background: linear-gradient(90deg, rgba(228, 231, 239, 0.6) 25%, rgba(244, 246, 251, 0.9) 50%, rgba(228, 231, 239, 0.6) 75%);
                background-size: 200% 100%;
                animation: skeleton-shimmer 1.6s infinite linear;
                border-radius: 4px;
                vertical-align: middle;
            }
        </style>
    @endpush
@endonce
