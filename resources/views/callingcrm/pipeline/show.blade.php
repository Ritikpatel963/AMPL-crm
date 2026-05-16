@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Campaign Detail')

@section('main-content')
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-megaphone"></i> Campaign Detail</span>
                <h2 class="mt-3 mb-2 fw-bold">{{ $campaign->name }}</h2>
                <p class="text-muted mb-1">Pipeline: {{ optional($campaign->pipeline)->name }}</p>
                <p class="text-muted mb-0">Manager: {{ optional($campaign->manager)->name ?? 'Not assigned' }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-dark border">Status: {{ $campaign->status }}</span>
                <span class="badge bg-light text-dark border">Distribution: {{ $campaign->distribution }}</span>
                <span class="badge bg-light text-dark border">Priority: {{ $campaign->priority }}</span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Lead Funnel by Stage</h3>
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total</div>
                        <div class="crm-stat-value">{{ $leadCounts['total'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">In Progress</div>
                        <div class="crm-stat-value">{{ $leadCounts['in_progress'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Closed</div>
                        <div class="crm-stat-value">{{ $leadCounts['closed'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Connected Calls</div>
                        <div class="crm-stat-value">{{ $callSummary['connected_calls'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
            <h3 class="crm-panel-title">Lead Funnel Stages</h3>
            @forelse ($campaign->pipeline?->stages ?? [] as $stage)
                <div class="crm-mini-item mb-3">
                    <div>
                        <div class="fw-semibold">{{ $stage->name }}</div>
                        <div class="small text-muted">Stage configured for this pipeline</div>
                    </div>
                    <span class="badge text-bg-light border">{{ $stage->color }}</span>
                </div>
            @empty
                <div class="crm-empty">No stages found for this pipeline.</div>
            @endforelse
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Campaign Statistics</h3>
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total Calls</div>
                        <div class="crm-stat-value">{{ $callSummary['total_calls'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Duration</div>
                        <div class="crm-stat-value">{{ $callSummary['duration'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Follow Ups Due</div>
                        <div class="crm-stat-value">{{ $callSummary['follow_ups_due'] }}</div>
                    </div>
                </div>
                <hr class="my-4">
                <h4 class="crm-panel-title">Stage Breakdown</h4>
                @forelse ($stageBreakdown as $stage)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="fw-semibold">{{ $stage['name'] }}</div>
                            <div class="text-muted">{{ $stage['count'] }}</div>
                        </div>
                        <div class="crm-progress">
                            <div class="crm-progress-bar" style="width: {{ $leadCounts['total'] > 0 ? round(($stage['count'] / $leadCounts['total']) * 100) : 0 }}%; background: {{ $stage['color'] }};"></div>
                        </div>
                    </div>
                @empty
                    <div class="crm-empty">No stage breakdown available.</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card crm-soft-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0">Lead Distribution by Agent</h3>
                    <span class="crm-chip">Uncontacted / Follow-Up / Closed</span>
                </div>
                @forelse ($leadDistribution as $row)
                    @php
                        $share = $leadCounts['total'] > 0 ? round(($row->total / $leadCounts['total']) * 100) : 0;
                    @endphp
                    <div class="crm-chart-row">
                        <div class="crm-chart-label">{{ optional($row->assignedUser)->name ?? 'Unassigned' }}</div>
                        <div class="crm-chart-track">
                            <div class="crm-chart-fill" style="width: {{ $share }}%;"></div>
                        </div>
                        <div class="crm-chart-value">{{ $row->total }}</div>
                    </div>
                @empty
                    <div class="crm-empty">No distribution data available.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
