<?php $__env->startSection('title', 'Vendor Details'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-4 text-primary">Vendor Details</h5>

    <?php $d = $vendor->vendorDetail ?? null; ?>

    <div class="row">
        <div class="col-md-6">
            <h6>Basic Information</h6>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Name:</strong></td>
                    <td><?php echo e($vendor->name); ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo e($vendor->email); ?></td>
                </tr>
                <tr>
                    <td><strong>Firm Name:</strong></td>
                    <td><?php echo e($d->firm_name ?? '—'); ?></td>
                </tr>
                <tr>
                    <td><strong>Phone:</strong></td>
                    <td><?php echo e($d->phone_number ?? '—'); ?></td>
                </tr>
                <tr>
                    <td><strong>License Type:</strong></td>
                    <td><?php echo e(ucfirst($d->license_type ?? '—')); ?></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        <?php if($vendor->approval_status == 'approved'): ?>
                            <span class="badge bg-success">Approved</span>
                        <?php elseif($vendor->approval_status == 'pending'): ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php elseif($vendor->approval_status == 'rejected'): ?>
                            <span class="badge bg-danger">Rejected</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?php echo e(ucfirst($vendor->approval_status ?? 'Unknown')); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Registered On:</strong></td>
                    <td><?php echo e($vendor->created_at->format('d M Y, H:i')); ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Documents</h6>

            <?php if($d && !empty($d->gst_doc)): ?>
                <?php $gstPath = storage_path('app/public/' . $d->gst_doc); ?>
                <?php if(file_exists($gstPath)): ?>
                    <p><strong>GST Document:</strong> <a href="<?php echo e(asset('storage/' . $d->gst_doc)); ?>" target="_blank">View</a></p>
                <?php else: ?>
                    <p><strong>GST Document:</strong> <span class="text-danger">File not found on server</span></p>
                <?php endif; ?>
            <?php else: ?>
                <p><strong>GST Document:</strong> <span class="text-muted">Not uploaded</span></p>
            <?php endif; ?>

            <?php if($d && !empty($d->license_doc)): ?>
                <?php $licensePath = storage_path('app/public/' . $d->license_doc); ?>
                <?php if(file_exists($licensePath)): ?>
                    <p><strong>License Document:</strong> <a href="<?php echo e(asset('storage/' . $d->license_doc)); ?>" target="_blank">View</a></p>
                <?php else: ?>
                    <p><strong>License Document:</strong> <span class="text-danger">File not found on server</span></p>
                <?php endif; ?>
            <?php else: ?>
                <p><strong>License Document:</strong> <span class="text-muted">Not uploaded</span></p>
            <?php endif; ?>

            <?php if($d && !empty($d->aadhar_front_path)): ?>
                <?php $aadharFrontPath = storage_path('app/public/' . $d->aadhar_front_path); ?>
                <?php if(file_exists($aadharFrontPath)): ?>
                    <p><strong>Aadhar Front:</strong> <a href="<?php echo e(asset('storage/' . $d->aadhar_front_path)); ?>" target="_blank">View</a></p>
                <?php else: ?>
                    <p><strong>Aadhar Front:</strong> <span class="text-danger">File not found on server</span></p>
                <?php endif; ?>
            <?php else: ?>
                <p><strong>Aadhar Front:</strong> <span class="text-muted">Not uploaded</span></p>
            <?php endif; ?>

            <?php if($d && !empty($d->aadhar_back_path)): ?>
                <?php $aadharBackPath = storage_path('app/public/' . $d->aadhar_back_path); ?>
                <?php if(file_exists($aadharBackPath)): ?>
                    <p><strong>Aadhar Back:</strong> <a href="<?php echo e(asset('storage/' . $d->aadhar_back_path)); ?>" target="_blank">View</a></p>
                <?php else: ?>
                    <p><strong>Aadhar Back:</strong> <span class="text-danger">File not found on server</span></p>
                <?php endif; ?>
            <?php else: ?>
                <p><strong>Aadhar Back:</strong> <span class="text-muted">Not uploaded</span></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?php echo e(route('admin_panel.admin.vendors.index')); ?>" class="btn btn-secondary">Back to All Vendors</a>
        <a href="<?php echo e(route('admin_panel.admin.vendors.products', $vendor->id)); ?>" class="btn btn-primary ms-2">View Products</a>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\vendors\show.blade.php ENDPATH**/ ?>