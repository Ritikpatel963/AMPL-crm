
<?php $__env->startSection('title', 'All Vendors'); ?>

<?php $__env->startSection('main-content'); ?>
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
                <td><?php echo e($vendor->created_at->format('d M Y')); ?></td>
                <td>
                    
                    <a href="<?php echo e(route('admin_panel.admin.vendors.show', $vendor->id)); ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <a href="<?php echo e(route('admin_panel.admin.vendors.products', $vendor->id)); ?>" class="btn btn-secondary btn-sm ms-1">
                        <i class="bi bi-list-ul"></i> Products
                    </a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    No vendors found.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/admin_panel/vendors/index.blade.php ENDPATH**/ ?>