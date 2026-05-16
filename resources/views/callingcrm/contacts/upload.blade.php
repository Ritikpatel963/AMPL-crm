@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Upload Contacts')

@section('main-content')
    <div class="crm-page-header">
        <span class="eyebrow"><i class="bi bi-file-earmark-arrow-up"></i> Bulk Import</span>
        <h2 class="mt-3 mb-2 fw-bold">Upload Excel Sheet</h2>
        <p class="text-muted mb-0">Keep imports controlled with clear file rules, row limits, and a dedicated upload workflow.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Import Rules</h3>
                <div class="crm-mini-list">
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Accepted Formats</div>
                            <div class="small text-muted">CSV supported now</div>
                        </div>
                        <span class="crm-chip">Spreadsheet</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Row Limit</div>
                            <div class="small text-muted">Up to 25,000 contacts per file</div>
                        </div>
                        <span class="crm-chip">25k</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">File Size</div>
                            <div class="small text-muted">Recommended maximum 3MB</div>
                        </div>
                        <span class="crm-chip">3MB</span>
                    </div>
                </div>
                <hr class="my-4">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('callingcrm.contacts.import') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf
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
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Source</label>
                        <select name="source" class="form-select" required>
                            @foreach (\App\Models\callingcrm\Lead::SOURCE_OPTIONS as $source)
                                <option value="{{ $source }}" @selected(old('source', 'FILE_UPLOAD') === $source)>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CSV File</label>
                        <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Import CSV Leads</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Workflow Notes</h3>
                <div class="crm-empty h-100 d-flex flex-column justify-content-center">
                    <div class="mb-2"><i class="bi bi-cloud-upload fs-1 text-primary"></i></div>
                    <div class="fw-semibold mb-2">CSV import is live</div>
                    <div>Use headers like <strong>name</strong>, <strong>phone</strong>, optional <strong>email</strong>, and optional <strong>tags</strong>.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
