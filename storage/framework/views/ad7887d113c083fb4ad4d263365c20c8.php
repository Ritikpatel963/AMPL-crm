

<?php $__env->startSection('title', 'Manage Roles'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="card shadow-sm border-0 rounded-3 p-4">
        <h5 class="fw-bold text-primary mb-4">Manage Roles</h5>

        
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add Role Form -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Add New Role</h6>
                <form id="roleForm" class="row g-3" method="POST" action="<?php echo e(route('admin_panel.admin.roles.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control form-control-lg rounded-3"
                            placeholder="Enter role name" required>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-plus-circle me-2"></i> Add Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role List Table -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h6 class="fw-semibold text-dark mb-3">
                    <i class="bi bi-shield-lock me-2 text-primary"></i> Role List
                </h6>

                <table id="roleTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Role Name</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($role->name); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary me-2 edit-btn"
                                        data-id="<?php echo e($role->id); ?>" data-name="<?php echo e($role->name); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <form action="<?php echo e(route('admin_panel.admin.roles.destroy', $role->id)); ?>" method="POST"
                                        class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this role?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editRoleForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="name" id="editRoleName" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            $('#roleTable').DataTable({
                pageLength: 6,
                language: {
                    searchPlaceholder: "Search role...",
                    search: ""
                }
            });

            // Edit button click
            $('.edit-btn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#editRoleName').val(name);
                $('#editRoleForm').attr('action', `/admin_panel/admin/permission/roles/update/${id}`);
                $('#editRoleModal').modal('show');
            });
        });
    </script>


<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\permission\role.blade.php ENDPATH**/ ?>