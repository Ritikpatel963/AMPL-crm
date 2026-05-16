@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Calling CRM Contacts')

@section('main-content')
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-person-vcard"></i> Contacts</span>
                <h2 class="mt-3 mb-2 fw-bold">Lead database with source-ready organization</h2>
                <p class="text-muted mb-0">Review imported leads, track campaign assignment, and jump into creation and upload workflows.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('callingcrm.contacts.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Lead</a>
                <a href="{{ route('callingcrm.contacts.upload') }}" class="btn btn-outline-primary"><i class="bi bi-upload me-2"></i>Upload Excel</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Source Filters</h3>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($sources as $source)
                        <span class="crm-chip">{{ $source }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card crm-soft-card h-100 p-4">
                <form method="GET" action="{{ route('callingcrm.contacts.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, phone or email">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Source</label>
                        <select name="source" class="form-select">
                            <option value="">All Sources</option>
                            @foreach ($sources as $source)
                                <option value="{{ $source }}" @selected(request('source') === $source)>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Campaign</label>
                        <select name="campaign_id" class="form-select">
                            <option value="">All Campaigns</option>
                            @foreach ($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" @selected((string) request('campaign_id') === (string) $campaign->id)>{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('callingcrm.contacts.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
                <hr class="my-4">
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total Leads</div>
                        <div class="crm-stat-value">{{ $leads->total() }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">This Page</div>
                        <div class="crm-stat-value">{{ $leads->count() }}</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Visible Sources</div>
                        <div class="crm-stat-value">{{ count($sources) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card crm-soft-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h3 class="crm-panel-title mb-1">Lead List</h3>
                <div class="text-muted">Structured for search, filters, and campaign-level drilldown.</div>
            </div>
            <span class="badge bg-light text-dark border">Page {{ $leads->currentPage() }} of {{ max($leads->lastPage(), 1) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle crm-table mb-0">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Campaign</th>
                    <th>Assigned Agent</th>
                    <th>Source</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($leads as $lead)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $lead->name }}</div>
                            <div class="small text-muted">{{ $lead->email ?: 'No email added' }}</div>
                            <div class="small text-muted mt-1">Stage: {{ optional($lead->stage)->name ?: 'Unassigned' }}</div>
                        </td>
                        <td><span class="fw-semibold">{{ $lead->phone }}</span></td>
                        <td>{{ optional($lead->campaign)->name ?: 'Unassigned campaign' }}</td>
                        <td>{{ optional($lead->assignedUser)->name ?: 'Not assigned' }}</td>
                        <td><span class="crm-chip">{{ $lead->source }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted">No leads found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $leads->links() }}
        </div>
    </div>
@endsection
