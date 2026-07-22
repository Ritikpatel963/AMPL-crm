
<?php $__env->startSection('title', 'Categories Management'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0 rounded-3 p-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-black mb-0">Categories Management</h5>
            <small class="text-muted">Manage your main and sub categories easily</small>
        </div>
        <button class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Category
        </button>
    </div>

    <!-- Category Table -->
    <div class="table-responsive">
        <table id="categoryTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Category Name</th>
                    <th>Parent Category</th>
                    <th>Icon</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($key + 1); ?></td>
                        <td><?php echo e($cat->name); ?></td>
                        <td><?php echo e($cat->parent?->name ?? '-'); ?></td>
                        <td><i class="<?php echo e($cat->icon ?? 'bi bi-tag'); ?> fs-5 text-primary"></i></td>
                        <td>
                            <?php if($cat->status == 'Active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo e($cat->id); ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <form action="<?php echo e(route('admin_panel.admin.categories.destroy', $cat->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" onclick="return confirm('Delete this category?')" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCategoryModal<?php echo e($cat->id); ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-3">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title"><i class="bi bi-tags me-2"></i> Edit Category</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <form action="<?php echo e(route('admin_panel.admin.categories.update', $cat->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('POST'); ?>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Parent Category</label>
                                                <select class="form-select" name="parent_id">
                                                    <option value="">-- None --</option>
                                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($parent->id); ?>" <?php echo e($cat->parent_id == $parent->id ? 'selected' : ''); ?>>
                                                            <?php echo e($parent->name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Category Name</label>
                                                <input type="text" class="form-control" name="name" value="<?php echo e($cat->name); ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Icon</label>
                                                <input type="text" class="form-control" name="icon" value="<?php echo e($cat->icon); ?>">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Status</label>
                                                <select class="form-select" name="status">
                                                    <option value="Active" <?php echo e($cat->status == 'Active' ? 'selected' : ''); ?>>Active</option>
                                                    <option value="Inactive" <?php echo e($cat->status == 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
                                                </select>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Slug</label>
                                                <input type="text" class="form-control" name="slug" value="<?php echo e($cat->slug); ?>">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-3">Update Category</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-tags me-2"></i> Add Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo e(route('admin_panel.admin.categories.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Category</label>
                                <select class="form-select" name="parent_id">
                                    <option value="">-- None --</option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Icon</label>
                                <input type="text" class="form-control" name="icon" placeholder="e.g., bi bi-laptop">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select" name="status">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Slug / URL</label>
                                <input type="text" class="form-control" name="slug" placeholder="auto-generated or custom slug">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-3">Save Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<!-- DataTables Bootstrap 5 -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
.dataTables_wrapper .dataTables_filter input {
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 5px 10px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
}
.btn-outline-primary:hover {
    background-color: #0d6efd;
    color: #fff;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}
</style>
<?php $__env->stopPush(); ?>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#categoryTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search categories..."
        }
    });
});
</script>


<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\product\product_category.blade.php ENDPATH**/ ?>