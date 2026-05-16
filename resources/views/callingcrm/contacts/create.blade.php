@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Create Lead')

@section('main-content')
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-person-plus"></i> New Contact</span>
                <h2 class="mt-3 mb-2 fw-bold">Prepare the lead creation workspace</h2>
                <p class="text-muted mb-0">This page defines the fields, campaign targets, and custom CRM properties for new lead entry.</p>
            </div>
            <div class="crm-kpi-box">
                <div class="crm-section-label">Campaign Options</div>
                <div class="crm-stat-value">{{ $campaigns->count() }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Lead Entry Checklist</h3>
                <div class="crm-mini-list">
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Name</div>
                            <div class="small text-muted">Primary contact identity</div>
                        </div>
                        <span class="crm-chip">Text</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Phone</div>
                            <div class="small text-muted">Required unique field for call activity</div>
                        </div>
                        <span class="badge bg-danger">Required</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Email</div>
                            <div class="small text-muted">Optional contact enrichment</div>
                        </div>
                        <span class="crm-chip">Optional</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Campaign</div>
                            <div class="small text-muted">Required for pipeline placement</div>
                        </div>
                        <span class="badge bg-danger">Required</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card crm-soft-card p-4 mb-4">
                <h3 class="crm-panel-title">Create Lead</h3>
                <form method="POST" action="{{ route('callingcrm.contacts.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Source</label>
                        <select name="source" class="form-select" required>
                            @foreach ($sources as $source)
                                <option value="{{ $source }}" @selected(old('source', 'MANUAL') === $source)>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Campaign</label>
                        <select name="campaign_id" class="form-select" required>
                            <option value="">Select campaign</option>
                            @foreach ($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" @selected((string) old('campaign_id') === (string) $campaign->id)>{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assign Agent</label>
                        <select name="user_id" class="form-select">
                            <option value="">Auto / Unassigned</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" @selected((string) old('user_id') === (string) $agent->id)>{{ $agent->name }} ({{ $agent->role }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tags</label>
                        <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="fresh, follow-up, premium">
                    </div>
                    @foreach ($properties as $property)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ $property->name }}</label>
                            <input type="text" name="property_{{ $property->id }}" class="form-control" value="{{ old('property_' . $property->id) }}" placeholder="{{ $property->data_type }}">
                        </div>
                    @endforeach
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Lead</button>
                        <a href="{{ route('callingcrm.contacts.index') }}" class="btn btn-outline-secondary">Back to Contacts</a>
                    </div>
                </form>
            </div>

            <div class="card crm-soft-card p-4 mb-4">
                <h3 class="crm-panel-title">Available Campaigns</h3>
                @forelse ($campaigns as $campaign)
                    <div class="crm-mini-item">
                        <div class="fw-semibold">{{ $campaign->name }}</div>
                        <span class="badge bg-light text-dark border">Campaign</span>
                    </div>
                @empty
                    <div class="crm-empty">No campaigns available yet.</div>
                @endforelse
            </div>

            <div class="card crm-soft-card p-4">
                <h3 class="crm-panel-title">Custom Contact Properties</h3>
                @forelse ($properties as $property)
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">{{ $property->name }}</div>
                            <div class="small text-muted">Custom CRM field definition</div>
                        </div>
                        <span class="crm-chip">{{ $property->data_type }}</span>
                    </div>
                @empty
                    <div class="crm-empty">No custom properties configured yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
