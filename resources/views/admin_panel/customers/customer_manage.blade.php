@extends('admin_panel.layout.app')
@section('title', 'Customer Management')

@section('main-content')
<div class="card shadow-sm border-0 rounded-3 p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-primary mb-1">Customer Management</h5>
            <small class="text-muted">Create customers, assign agents, and handle Direct Chat requests.</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
            <i class="bi bi-person-plus"></i> Add Customer
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-sm {{ blank($status) ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin_panel.admin.customers.customer_manage') }}">
            All {{ $counts->sum() }}
        </a>
        <a class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}" href="{{ route('admin_panel.admin.customers.customer_manage', ['status' => 'pending']) }}">
            Pending {{ $counts['pending'] ?? 0 }}
        </a>
        <a class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-success' }}" href="{{ route('admin_panel.admin.customers.customer_manage', ['status' => 'approved']) }}">
            Approved {{ $counts['approved'] ?? 0 }}
        </a>
        <a class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}" href="{{ route('admin_panel.admin.customers.customer_manage', ['status' => 'rejected']) }}">
            Declined {{ $counts['rejected'] ?? 0 }}
        </a>
    </div>

    <table id="customerTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Approval</th>
                <th>Assigned Agent</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                @php
                    $assignment = $customer->assignedAgent;
                    $agent = $assignment?->agent;
                    $approval = $customer->approval_status ?? 'approved';
                    $badge = match ($approval) {
                        'pending' => 'bg-warning text-dark',
                        'rejected' => 'bg-danger',
                        default => 'bg-success',
                    };
                @endphp
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone_number ?? '-' }}</td>
                    <td>{{ $customer->email }}</td>
                    <td><span class="badge {{ $badge }}">{{ ucfirst($approval) }}</span></td>
                    <td>{{ $agent ? $agent->name . ' (#' . $agent->id . ')' : 'Not assigned' }}</td>
                    <td>{{ optional($customer->created_at)->format('d M Y') }}</td>
                    <td class="text-nowrap">
                        <button class="btn btn-primary btn-sm"
                            data-customer-id="{{ $customer->id }}"
                            data-agent-id="{{ $agent?->id }}"
                            data-bs-toggle="modal"
                            data-bs-target="#assignAgentModal"
                            onclick="prepareAssign(this)">
                            <i class="bi bi-person-check"></i>
                        </button>

                        @if ($approval !== 'approved')
                            <form action="{{ route('admin_panel.admin.customers.approve', $customer) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                        @endif

                        @if ($approval !== 'rejected')
                            <form action="{{ route('admin_panel.admin.customers.reject', $customer) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">Decline</button>
                            </form>
                        @endif

                        <form action="{{ route('admin_panel.admin.customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this customer?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="createCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Create Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin_panel.admin.customers.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Customer name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" placeholder="10-digit mobile number (e.g. 9876543210)" required maxlength="15">
                        <div class="form-text text-muted">Customer will use this number to log in via OTP on the app.</div>
                    </div>

                    <select name="agent_id" class="form-select mb-3">
                        <option value="">Assign agent later</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }} - {{ ucfirst($agent->role) }}</option>
                        @endforeach
                    </select>

                    <div class="text-end">
                        <button class="btn btn-success" type="submit">Create Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Assign Agent</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignAgentForm" method="POST" action="">
                    @csrf
                    <label class="form-label">Select Agent</label>
                    <select name="agent_id" id="assign_agent_id" class="form-select mb-3" required>
                        <option value="">Select Agent</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }} - {{ ucfirst($agent->role) }}</option>
                        @endforeach
                    </select>
                    <div class="text-end">
                        <button class="btn btn-success" type="submit">Assign Agent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#customerTable').DataTable({ pageLength: 10 });
});

function prepareAssign(button) {
    var customerId = button.getAttribute('data-customer-id');
    var agentId = button.getAttribute('data-agent-id') || '';
    var action = "{{ route('admin_panel.admin.customers.assign', ['customer' => '__CUSTOMER__']) }}";

    document.getElementById('assignAgentForm').setAttribute('action', action.replace('__CUSTOMER__', customerId));
    document.getElementById('assign_agent_id').value = agentId;
}
</script>
@endsection
