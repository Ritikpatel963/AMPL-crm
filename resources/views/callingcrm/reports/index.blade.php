@extends('admin_panel.layout.app')
@include('callingcrm.partials.theme')

@section('title', 'Calling CRM Reports')

@section('main-content')
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                <h2 class="mt-3 mb-2 fw-bold">Operational reports for calling, login, and lead progress</h2>
                <p class="text-muted mb-0">Use this workspace for all reports, favourites, recently viewed items, and queued CSV exports.</p>
            </div>
            <a href="{{ route('callingcrm.reports.export', request()->query()) }}" class="btn btn-outline-primary"><i class="bi bi-download me-2"></i>Download Logs</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Views</h3>
                <form method="GET" action="{{ route('callingcrm.reports.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Report</label>
                        <select name="report" class="form-select">
                            @foreach ($reports as $report)
                                <option value="{{ $report }}" @selected($selectedReport === $report)>{{ $report }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Filter by Agent</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Agents</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" @selected((string) $userId === (string) $agent->id)>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">From</label>
                        <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">To</label>
                        <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Run Report</button>
                        <a href="{{ route('callingcrm.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card crm-soft-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0">{{ $selectedReport }}</h3>
                    <span class="badge bg-light text-dark border">{{ count($reportData['rows']) }} rows</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle crm-table mb-0">
                        <thead>
                            <tr>
                                @foreach ($reportData['headers'] as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reportData['rows'] as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ max(count($reportData['headers']), 1) }}" class="text-muted">No report data found for the selected range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
