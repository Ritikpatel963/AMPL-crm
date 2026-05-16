@extends('admin_panel.layout.app')
@section('title', 'Vendor Details')

@section('main-content')
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-4 text-primary">Vendor Details</h5>

    @php $d = $vendor->vendorDetail ?? null; @endphp

    <div class="row">
        <div class="col-md-6">
            <h6>Basic Information</h6>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{{ $vendor->name }}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $vendor->email }}</td>
                </tr>
                <tr>
                    <td><strong>Firm Name:</strong></td>
                    <td>{{ $d->firm_name ?? '—' }}</td>
                </tr>
                <tr>
                    <td><strong>Phone:</strong></td>
                    <td>{{ $d->phone_number ?? '—' }}</td>
                </tr>
                <tr>
                    <td><strong>License Type:</strong></td>
                    <td>{{ ucfirst($d->license_type ?? '—') }}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
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
                </tr>
                <tr>
                    <td><strong>Registered On:</strong></td>
                    <td>{{ $vendor->created_at->format('d M Y, H:i') }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Documents</h6>

            @if($d && !empty($d->gst_doc))
                @php $gstPath = storage_path('app/public/' . $d->gst_doc); @endphp
                @if(file_exists($gstPath))
                    <p><strong>GST Document:</strong> <a href="{{ asset('storage/' . $d->gst_doc) }}" target="_blank">View</a></p>
                @else
                    <p><strong>GST Document:</strong> <span class="text-danger">File not found on server</span></p>
                @endif
            @else
                <p><strong>GST Document:</strong> <span class="text-muted">Not uploaded</span></p>
            @endif

            @if($d && !empty($d->license_doc))
                @php $licensePath = storage_path('app/public/' . $d->license_doc); @endphp
                @if(file_exists($licensePath))
                    <p><strong>License Document:</strong> <a href="{{ asset('storage/' . $d->license_doc) }}" target="_blank">View</a></p>
                @else
                    <p><strong>License Document:</strong> <span class="text-danger">File not found on server</span></p>
                @endif
            @else
                <p><strong>License Document:</strong> <span class="text-muted">Not uploaded</span></p>
            @endif

            @if($d && !empty($d->aadhar_front_path))
                @php $aadharFrontPath = storage_path('app/public/' . $d->aadhar_front_path); @endphp
                @if(file_exists($aadharFrontPath))
                    <p><strong>Aadhar Front:</strong> <a href="{{ asset('storage/' . $d->aadhar_front_path) }}" target="_blank">View</a></p>
                @else
                    <p><strong>Aadhar Front:</strong> <span class="text-danger">File not found on server</span></p>
                @endif
            @else
                <p><strong>Aadhar Front:</strong> <span class="text-muted">Not uploaded</span></p>
            @endif

            @if($d && !empty($d->aadhar_back_path))
                @php $aadharBackPath = storage_path('app/public/' . $d->aadhar_back_path); @endphp
                @if(file_exists($aadharBackPath))
                    <p><strong>Aadhar Back:</strong> <a href="{{ asset('storage/' . $d->aadhar_back_path) }}" target="_blank">View</a></p>
                @else
                    <p><strong>Aadhar Back:</strong> <span class="text-danger">File not found on server</span></p>
                @endif
            @else
                <p><strong>Aadhar Back:</strong> <span class="text-muted">Not uploaded</span></p>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin_panel.admin.vendors.index') }}" class="btn btn-secondary">Back to All Vendors</a>
        <a href="{{ route('admin_panel.admin.vendors.products', $vendor->id) }}" class="btn btn-primary ms-2">View Products</a>
    </div>
</div>

@endsection