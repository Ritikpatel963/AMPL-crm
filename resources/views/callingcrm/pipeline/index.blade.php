@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Calling CRM Pipeline')

@section('main-content')
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-diagram-3"></i> Pipeline</span>
                <h2 class="mt-3 mb-2 fw-bold">Campaigns grouped by pipeline</h2>
                <p class="text-muted mb-0">Use this area for active campaign monitoring, hide-paused behavior, and distribution control.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPipelineModal">Create Pipeline</button>
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createCampaignModal">Create Campaign</button>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse ($pipelines as $pipeline)
        <div class="card crm-soft-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h3 class="crm-panel-title mb-1">{{ $pipeline->name }}</h3>
                    <div class="text-muted">{{ $pipeline->campaigns->count() }} campaigns in this pipeline</div>
                </div>
                <span class="crm-chip">Pipeline Group</span>
            </div>
            @forelse ($pipeline->campaigns as $campaign)
                <div class="crm-mini-item mb-3">
                    <div>
                        <div class="fw-semibold">{{ $campaign->name }}</div>
                        <div class="small text-muted">
                            Manager: {{ optional($campaign->manager)->name ?: 'Not assigned' }}
                            · Agents: {{ $campaign->agents->count() }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="mb-2">
                            <span class="badge {{ $campaign->status === 'paused' ? 'bg-warning text-dark' : 'bg-success' }}">{{ $campaign->status }}</span>
                        </div>
                        <a href="{{ route('callingcrm.pipeline.show', $campaign) }}" class="btn btn-sm btn-outline-primary">View Campaign</a>
                    </div>
                </div>
            @empty
                <div class="crm-empty">No campaigns in this pipeline yet.</div>
            @endforelse
        </div>
    @empty
        <div class="card crm-soft-card p-4">
            <div class="crm-empty">No pipelines created yet.</div>
        </div>
    @endforelse

    <div class="modal fade" id="createPipelineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('callingcrm.pipeline.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create Pipeline</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Pipeline Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Stages</label>
                                <input type="text" name="stages" class="form-control" placeholder="Fresh Lead, Follow Up, Closed Won, Closed Lost">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Dispositions</label>
                                <input type="text" name="dispositions" class="form-control" placeholder="Follow Up:in_progress, Closed Won:closed_won, Closed Lost:closed_lost">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create Pipeline</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createCampaignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('callingcrm.campaigns.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create Campaign</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Campaign Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pipeline</label>
                                <select name="pipeline_id" class="form-select" required>
                                    <option value="">Select pipeline</option>
                                    @foreach ($pipelines as $pipeline)
                                        <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Manager</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">Select manager</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Agents</label>
                                <select name="agent_ids[]" class="form-select" multiple size="5">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="paused">Paused</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Distribution</label>
                                <select name="distribution" class="form-select">
                                    <option value="on_demand">On Demand</option>
                                    <option value="auto_assign">Auto Assign</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
