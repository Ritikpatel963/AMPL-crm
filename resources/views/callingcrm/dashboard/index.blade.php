@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Calling CRM Dashboard')

@section('main-content')
    <div class="crm-page-header">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="eyebrow"><i class="bi bi-telephone-forward"></i> Calling CRM</span>
                <h2 class="mt-3 mb-2 fw-bold">Team calling performance at a glance</h2>
                <p class="text-muted mb-0">Track connection quality, active agents, stage movement, and campaign shortcuts from one place.</p>
            </div>
            <div class="col-lg-4">
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Connected Rate</div>
                        <div class="crm-stat-value">{{ $connectedPercent }}%</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Pinned Campaigns</div>
                        <div class="crm-stat-value">{{ $pinnedCampaigns->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Call Overview</div>
                        <div class="crm-stat-value mt-2">{{ $totalCalls }}</div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-telephone-outbound"></i></span>
                </div>
                <div class="text-muted">Total calls recorded</div>
                <div class="mt-3 small fw-semibold text-success">{{ $connectedCalls }} connected successfully</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Agent Activity</div>
                        <div class="crm-stat-value mt-2">{{ $activeAgents }}</div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-people"></i></span>
                </div>
                <div class="text-muted">Agents tagged for Calling CRM</div>
                <div class="mt-3 small fw-semibold text-warning">{{ $agentsOnBreak }} currently on break</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Follow Through</div>
                        <div class="crm-stat-value mt-2">{{ $leadsByStage->sum('total') }}</div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-kanban"></i></span>
                </div>
                <div class="text-muted">Leads mapped into visible stages</div>
                <div class="mt-3 small fw-semibold text-primary">{{ $leadsByStage->count() }} stage buckets active</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Campaign Health</div>
                        <div class="crm-stat-value mt-2">{{ $pinnedCampaigns->count() }}</div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-broadcast-pin"></i></span>
                </div>
                <div class="text-muted">Pinned campaigns ready for fast access</div>
                <div class="mt-3 small fw-semibold text-info">Use pipeline to manage distribution</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0">Leads by Stage</h3>
                    <span class="crm-chip">Live mix</span>
                </div>
                @forelse ($leadsByStage as $stage)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="fw-semibold">{{ $stage['name'] }}</div>
                            <div class="text-muted">{{ $stage['total'] }} leads</div>
                        </div>
                        <div class="crm-progress">
                            <div class="crm-progress-bar" style="width: {{ $stage['percentage'] }}%; background: {{ $stage['color'] }};"></div>
                        </div>
                        <div class="small text-muted mt-2">{{ $stage['percentage'] }}% of visible stage distribution</div>
                    </div>
                @empty
                    <div class="crm-empty">No lead stage data yet.</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0">Quick Access</h3>
                    <a href="{{ route('callingcrm.reports.index') }}" class="btn btn-outline-primary btn-sm">Campaigns Report</a>
                </div>
                <div class="crm-mini-list mb-4">
                    @foreach ($quickAccess as $item)
                        <a href="{{ $item['href'] }}" class="crm-action-link">
                            <span>{{ $item['label'] }}</span>
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    @endforeach
                </div>

                <h4 class="crm-panel-title">Pinned Campaigns</h4>
                @forelse ($pinnedCampaigns as $campaign)
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">{{ $campaign->name }}</div>
                            <div class="small text-muted">Shortcut into campaign detail</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary text-uppercase">{{ $campaign->status }}</span>
                            <div class="mt-2">
                                <a href="{{ route('callingcrm.pipeline.show', $campaign) }}" class="btn btn-sm btn-light">Open</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="crm-empty">No campaigns created yet.</div>
                @endforelse

                <hr class="my-4">

                <h4 class="crm-panel-title">Tools Panel</h4>
                <div class="crm-mini-list">
                    @foreach ($toolsPanel as $item)
                        <a href="{{ $item['href'] }}" class="crm-action-link">
                            <span>{{ $item['label'] }}</span>
                            <i class="bi bi-graph-up-arrow"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
