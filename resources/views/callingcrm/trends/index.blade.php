@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Calling CRM Trends')

@section('main-content')
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-graph-up"></i> Trends</span>
                <h2 class="mt-3 mb-2 fw-bold">Business and user trend snapshots</h2>
                <p class="text-muted mb-0">This area is ready for period comparison, grouped bar charts, saved graph presets, and custom date ranges.</p>
            </div>
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Request Graph</button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Business Trend KPIs</h3>
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total SMS Sent</div>
                        <div class="crm-stat-value">{{ $metrics['total_sms_sent'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total Calls</div>
                        <div class="crm-stat-value">{{ $metrics['total_calls'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Converted Leads</div>
                        <div class="crm-stat-value">{{ $metrics['converted_leads'] }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Call Duration</div>
                        <div class="crm-stat-value">{{ $metrics['call_duration'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Period Filters</h3>
                <form method="GET" action="{{ route('callingcrm.trends.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Range</label>
                        <select name="range" class="form-select">
                            <option value="30_days" @selected($activeFilter === '30_days')>Last 30 Days</option>
                            <option value="7_days" @selected($activeFilter === '7_days')>Last 7 Days</option>
                            <option value="custom" @selected($activeFilter === 'custom')>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">From</label>
                        <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">To</label>
                        <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Apply</button>
                        <a href="{{ route('callingcrm.trends.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card crm-soft-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="crm-panel-title mb-0">Total Calls vs Calls Connected</h3>
            <span class="badge bg-light text-dark border">{{ $from->format('d M Y') }} - {{ $to->format('d M Y') }}</span>
        </div>
        @php
            $maxCalls = max(1, collect($callVsConnected)->max('calls'));
        @endphp
        @forelse ($callVsConnected as $point)
            <div class="crm-chart-row">
                <div class="crm-chart-label">{{ $point['label'] }}</div>
                <div class="w-100">
                    <div class="crm-chart-track mb-2">
                        <div class="crm-chart-fill" style="width: {{ round(($point['calls'] / $maxCalls) * 100) }}%;"></div>
                    </div>
                    <div class="crm-chart-track">
                        <div class="crm-chart-fill" style="width: {{ round(($point['connected'] / $maxCalls) * 100) }}%; background: linear-gradient(90deg, #10b981, #22c55e);"></div>
                    </div>
                </div>
                <div class="crm-chart-value">{{ $point['calls'] }}/{{ $point['connected'] }}</div>
            </div>
        @empty
            <div class="crm-empty">No trend data available.</div>
        @endforelse
    </div>

    <div class="card crm-soft-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="crm-panel-title mb-0">Users Trend</h3>
            <span class="badge bg-light text-dark border">Agent breakdown</span>
        </div>
        @forelse ($userTrend as $item)
            @php
                $maxUserCalls = max(1, $userTrend->max('calls'));
            @endphp
            <div class="crm-chart-row">
                <div class="crm-chart-label">{{ $item['name'] }}</div>
                <div class="crm-chart-track">
                    <div class="crm-chart-fill" style="width: {{ round(($item['calls'] / $maxUserCalls) * 100) }}%;"></div>
                </div>
                <div class="crm-chart-value">{{ $item['calls'] }}</div>
            </div>
        @empty
            <div class="crm-empty">No user trend data available.</div>
        @endforelse
    </div>
@endsection
