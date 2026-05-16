@extends('admin_panel.layout.app')
@section('title', 'Pending Kyc')

@section('main-content')
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-4 text-primary">KYC Pending Approval</h5>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <table id="kycTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Vendor Name</th>
                <th>Firm Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>License Type</th>
                <th>Submitted On</th>
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
                <td>{{ $vendor->created_at->format('d M Y') }}</td>
                <td>
                    {{-- Approve --}}
                    <form action="{{ route('admin_panel.admin.kyc.approve', $vendor->id) }}"
                          method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Approve {{ $vendor->name }}?')">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                    </form>

                    {{-- Reject --}}
                    <button class="btn btn-danger btn-sm"
                            onclick="openRejectModal({{ $vendor->id }}, '{{ $vendor->name }}')">
                        <i class="bi bi-x-lg"></i> Reject
                    </button>

                    {{-- View Documents --}}
                    <button class="btn btn-info btn-sm"
                            onclick="openViewModal(
                                '{{ $vendor->name }}',
                                '{{ $d->firm_name ?? '' }}',
                                '{{ $d->gst_number ?? '' }}',
                                '{{ $d->license_type ?? '' }}',
                                '{{ $d->phone_number ?? '' }}',
                                '{{ $d->address ?? '' }}',
                                '{{ $d->gst_doc && file_exists(storage_path('app/public/' . $d->gst_doc)) ? asset('storage/' . $d->gst_doc) : '' }}',
                                '{{ $d->license_doc && file_exists(storage_path('app/public/' . $d->license_doc)) ? asset('storage/' . $d->license_doc) : '' }}',
                                '{{ $d->aadhar_front_path && file_exists(storage_path('app/public/' . $d->aadhar_front_path)) ? asset('storage/' . $d->aadhar_front_path) : '' }}',
                                '{{ $d->aadhar_back_path && file_exists(storage_path('app/public/' . $d->aadhar_back_path)) ? asset('storage/' . $d->aadhar_back_path) : '' }}'
                            )">
                        <i class="bi bi-eye"></i> View
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    No pending vendor registrations.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- View KYC Modal --}}
<div class="modal fade" id="viewKycModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">KYC Documents</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Vendor Info --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Vendor Name:</strong> <span id="modal_name"></span></p>
                        <p class="mb-1"><strong>Firm Name:</strong> <span id="modal_firm"></span></p>
                        <p class="mb-1"><strong>GST Number:</strong> <span id="modal_gst"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>License Type:</strong> <span id="modal_license"></span></p>
                        <p class="mb-1"><strong>Phone:</strong> <span id="modal_phone"></span></p>
                        <p class="mb-1"><strong>Address:</strong> <span id="modal_address"></span></p>
                    </div>
                </div>

                <hr>

                {{-- Documents --}}
                <div class="row g-3">
                    <div class="col-md-6 text-center">
                        <p class="fw-bold mb-1">GST Document</p>
                        <div id="modal_gst_doc_wrap"></div>
                    </div>
                    <div class="col-md-6 text-center">
                        <p class="fw-bold mb-1">License Document</p>
                        <div id="modal_license_doc_wrap"></div>
                    </div>
                    <div class="col-md-6 text-center">
                        <p class="fw-bold mb-1">Aadhaar Front</p>
                        <div id="modal_aadhar_front_wrap"></div>
                    </div>
                    <div class="col-md-6 text-center">
                        <p class="fw-bold mb-1">Aadhaar Back</p>
                        <div id="modal_aadhar_back_wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Confirmation Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Vendor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject <strong id="rejectVendorName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <form id="rejectForm" method="POST">
                    @csrf
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#kycTable').DataTable({ pageLength: 5 });
});

// Populate View modal
function openViewModal(name, firm, gst, license, phone, address,
                       gstDocUrl, licenseDocUrl, aadharFrontUrl, aadharBackUrl) {
    $('#modal_name').text(name);
    $('#modal_firm').text(firm || '—');
    $('#modal_gst').text(gst || '—');
    $('#modal_license').text(license || '—');
    $('#modal_phone').text(phone || '—');
    $('#modal_address').text(address || '—');

    // Helper: render image or PDF link or placeholder
    function renderDoc(wrapperId, url) {
        let wrap = $(wrapperId);
        wrap.html('');
        if (!url) {
            wrap.html('<span class="text-muted">Not uploaded</span>');
            return;
        }
        let ext = url.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            wrap.html('<a href="' + url + '" target="_blank">' +
                '<img src="' + url + '" class="img-fluid rounded shadow-sm" style="max-height:160px;">' +
                '</a>');
        } else {
            wrap.html('<a href="' + url + '" target="_blank" class="btn btn-outline-secondary btn-sm">' +
                '<i class="bi bi-file-earmark-pdf"></i> View PDF</a>');
        }
    }

    renderDoc('#modal_gst_doc_wrap', gstDocUrl);
    renderDoc('#modal_license_doc_wrap', licenseDocUrl);
    renderDoc('#modal_aadhar_front_wrap', aadharFrontUrl);
    renderDoc('#modal_aadhar_back_wrap', aadharBackUrl);

    new bootstrap.Modal(document.getElementById('viewKycModal')).show();
}

// Populate Reject modal
function openRejectModal(vendorId, vendorName) {
    $('#rejectVendorName').text(vendorName);
    $('#rejectForm').attr('action', '/admin_panel/admin/kyc/' + vendorId + '/reject');
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endsection