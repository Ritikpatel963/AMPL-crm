

<?php $__env->startSection('title', 'Assign Role Permissions'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="card shadow-sm border-0 rounded-3 p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0">Role Permissions</h4>
                <small class="text-muted">Assign permissions to specific roles below</small>
            </div>
        </div>

        <!-- Alerts -->
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Select Role -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form id="permissionsForm" method="POST" action="<?php echo e(route('admin_panel.admin.permissions.assign')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Select Role</label>
                            <select name="role_id" id="roleSelect" class="form-select form-select-lg rounded-pill" required>
                                <option selected disabled value="">Choose Role...</option>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role->id); ?>"><?php echo e(ucfirst($role->name)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-light text-black fw-semibold fs-6">
                                <?php echo e($group); ?>

                            </div>
                            <div class="card-body d-flex flex-wrap gap-3">
                                <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="role-perm-box">
                                        <input type="checkbox" name="permissions[]" value="<?php echo e($permission); ?>">
                                        <div class="role-perm-content"><?php echo e($permission); ?></div>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                            <i class="bi bi-save me-2"></i> Save Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<style>
    .role-perm-box {
        position: relative;
        cursor: pointer;
        user-select: none;
        width: 180px;
        height: 50px;
        border-radius: 12px;
        border: 2px solid #adb5bd;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        transition: all 0.3s;
    }

    .role-perm-box input {
        display: none;
    }

    .role-perm-box .role-perm-content {
        pointer-events: none;
        text-align: center;
        padding: 0 10px;
    }

    .role-perm-box input:checked+.role-perm-content {
        background-color: #0d6efd;
        color: white;
        width: 100%;
        height: 100%;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
</style>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(function() {

        // Fetch assigned permissions when a role is selected
        $('#roleSelect').on('change', function() {
            var roleId = $(this).val();
            if (!roleId) return;

            $('input[type="checkbox"]').prop('checked', false);

            $.get(`/admin_panel/admin/permission/get-role-permissions/${roleId}`, function(res) {
                res.forEach(function(p) {
                    $(`input[value="${p}"]`).prop('checked', true);
                });
            });
        });
    });
</script>


<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\resources\views/admin_panel/permission/role_permission.blade.php ENDPATH**/ ?>