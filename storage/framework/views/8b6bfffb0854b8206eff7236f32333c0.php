<?php $__env->startSection('title', 'Manage Admins'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm rounded-3 border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Admin Management</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-plus-lg"></i> Add Admin
        </button>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Type</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($admin->name); ?></td>
                        <td><?php echo e($admin->phone); ?></td>
                        <td>
                            <span class="badge <?php echo e($admin->isMainAdmin() ? 'bg-primary' : 'bg-secondary'); ?>">
                                <?php echo e($admin->isMainAdmin() ? 'Main Admin' : 'Admin'); ?>

                            </span>
                        </td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editAdminModal"
                                data-id="<?php echo e($admin->id); ?>"
                                data-name="<?php echo e($admin->name); ?>"
                                data-phone="<?php echo e($admin->phone); ?>"
                                data-is-main="<?php echo e($admin->isMainAdmin() ? 1 : 0); ?>"
                                onclick="editAdmin(this)"
                            >
                                Edit
                            </button>

                            <form action="<?php echo e(route('admin_panel.admin.admins.destroy', $admin)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button
                                    type="submit"
                                    class="btn btn-sm btn-danger"
                                    <?php echo e($admin->isMainAdmin() ? 'disabled' : ''); ?>

                                    onclick="return confirm('Delete this admin?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Create Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo e(route('admin_panel.admin.admins.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
                    <input type="tel" name="phone" class="form-control mb-3" placeholder="Phone Number" required>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editAdminForm" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="modal-content shadow-sm rounded-3">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="edit_admin_name" name="name" class="form-control mb-3" placeholder="Full Name" required>
                    <input type="tel" id="edit_admin_phone" name="phone" class="form-control mb-2" placeholder="Phone Number" required>
                    <small id="main_admin_note" class="text-muted d-none">Main admin phone number cannot be changed.</small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update Admin</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function editAdmin(button) {
    const id = button.getAttribute('data-id');
    const isMain = button.getAttribute('data-is-main') === '1';
    const phoneInput = document.getElementById('edit_admin_phone');

    document.getElementById('editAdminForm').action = "<?php echo e(url('/admin_panel/admin/admins')); ?>/" + id;
    document.getElementById('edit_admin_name').value = button.getAttribute('data-name');
    phoneInput.value = button.getAttribute('data-phone');
    phoneInput.readOnly = isMain;
    document.getElementById('main_admin_note').classList.toggle('d-none', !isMain);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\admin\admins.blade.php ENDPATH**/ ?>