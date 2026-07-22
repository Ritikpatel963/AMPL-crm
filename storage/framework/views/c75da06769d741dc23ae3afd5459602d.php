
<?php $__env->startSection('title', 'Approved KYC'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0 rounded-3 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold">KYC Approved Vendors</h5>
        <span class="badge bg-success fs-6"><?php echo e($vendors->count()); ?> Approved</span>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover align-middle" id="kycApprovedTable">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Vendor Name</th>
                <th>Firm Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>License Type</th>
                <th>Approved On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $d = $vendor->vendorDetail; ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($vendor->name); ?></td>
                <td><?php echo e($d->firm_name ?? '—'); ?></td>
                <td><?php echo e($d->phone_number ?? '—'); ?></td>
                <td><?php echo e($vendor->email); ?></td>
                <td>
                    <span class="badge bg-secondary">
                        <?php echo e(ucfirst($d->license_type ?? '—')); ?>

                    </span>
                </td>
                <td><?php echo e($vendor->updated_at->format('d M Y')); ?></td>
                <td>
                    
                    <button class="btn btn-info btn-sm"
                            onclick="openViewModal(
                                '<?php echo e($vendor->name); ?>',
                                '<?php echo e($d->firm_name ?? ''); ?>',
                                '<?php echo e($d->gst_number ?? ''); ?>',
                                '<?php echo e($d->license_type ?? ''); ?>',
                                '<?php echo e($d->phone_number ?? ''); ?>',
                                '<?php echo e($d->address ?? ''); ?>',
                                '<?php echo e($d->gst_doc && file_exists(storage_path('app/public/' . $d->gst_doc)) ? asset('storage/' . $d->gst_doc) : ''); ?>',
                                '<?php echo e($d->license_doc && file_exists(storage_path('app/public/' . $d->license_doc)) ? asset('storage/' . $d->license_doc) : ''); ?>',
                                '<?php echo e($d->aadhar_front_path && file_exists(storage_path('app/public/' . $d->aadhar_front_path)) ? asset('storage/' . $d->aadhar_front_path) : ''); ?>',
                                '<?php echo e($d->aadhar_back_path && file_exists(storage_path('app/public/' . $d->aadhar_back_path)) ? asset('storage/' . $d->aadhar_back_path) : ''); ?>'
                            )">
                        <i class="bi bi-eye"></i> View
                    </button>

                    
                    <button class="btn btn-danger btn-sm"
                            onclick="openRevokeModal(<?php echo e($vendor->id); ?>, '<?php echo e($vendor->name); ?>')">
                        <i class="bi bi-x-lg"></i> Revoke
                    </button>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No approved vendors yet.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="modal fade" id="viewKycModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">KYC Documents</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
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


<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Revoke Approval</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to revoke approval for <strong id="revokeVendorName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <form id="revokeForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Revoke</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#kycApprovedTable').DataTable({ pageLength: 5 });
});

function openViewModal(name, firm, gst, license, phone, address,
                       gstDocUrl, licenseDocUrl, aadharFrontUrl, aadharBackUrl) {
    $('#modal_name').text(name);
    $('#modal_firm').text(firm || '—');
    $('#modal_gst').text(gst || '—');
    $('#modal_license').text(license || '—');
    $('#modal_phone').text(phone || '—');
    $('#modal_address').text(address || '—');

    function renderDoc(wrapperId, url) {
        let wrap = $(wrapperId);
        wrap.html('');
        if (!url) { wrap.html('<span class="text-muted">Not uploaded</span>'); return; }
        let ext = url.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            wrap.html('<a href="' + url + '" target="_blank"><img src="' + url + '" class="img-fluid rounded shadow-sm" style="max-height:160px;"></a>');
        } else {
            wrap.html('<a href="' + url + '" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-pdf"></i> View PDF</a>');
        }
    }

    renderDoc('#modal_gst_doc_wrap', gstDocUrl);
    renderDoc('#modal_license_doc_wrap', licenseDocUrl);
    renderDoc('#modal_aadhar_front_wrap', aadharFrontUrl);
    renderDoc('#modal_aadhar_back_wrap', aadharBackUrl);

    new bootstrap.Modal(document.getElementById('viewKycModal')).show();
}

function openRevokeModal(vendorId, vendorName) {
    $('#revokeVendorName').text(vendorName);
    $('#revokeForm').attr('action', '/admin_panel/admin/kyc/' + vendorId + '/reject');
    new bootstrap.Modal(document.getElementById('revokeModal')).show();
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\kyc\approved.blade.php ENDPATH**/ ?>