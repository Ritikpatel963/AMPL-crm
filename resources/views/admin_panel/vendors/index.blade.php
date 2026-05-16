@extends('admin_panel.layout.app')
@section('title', 'All Vendors')

@section('main-content')
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-4 text-primary">All Vendors</h5>

    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Vendor Name</th>
                <th>Firm Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>License Type</th>
                <th>Status</th>
                <th>Registered On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $i => $vendor)
            @php $d = $vendor->vendorDetail; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $vendor->name }}</td>
                <td>{{ $d->firm_name ?? '—' }}</td>
                <td>{{ $d->phone_number ?? '—' }}</td>
                <td>{{ $vendor->email }}</td>
                <td>
                    <span class="badge bg-secondary">
                        {{ ucfirst($d->license_type ?? '—') }}
                    </span>
                </td>
                <td>
                    @if($vendor->approval_status == 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($vendor->approval_status == 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif($vendor->approval_status == 'rejected')
                        <span class="badge bg-danger">Rejected</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($vendor->approval_status ?? 'Unknown') }}</span>
                    @endif
                </td>
                <td>{{ $vendor->created_at->format('d M Y') }}</td>
                <td>
                    {{-- View Details --}}
                    <a href="{{ route('admin_panel.admin.vendors.show', $vendor->id) }}" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <a href="{{ route('admin_panel.admin.vendors.products', $vendor->id) }}" class="btn btn-secondary btn-sm ms-1">
                        <i class="bi bi-list-ul"></i> Products
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    No vendors found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection