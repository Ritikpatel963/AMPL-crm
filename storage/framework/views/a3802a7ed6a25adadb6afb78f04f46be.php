<?php $__env->startSection('title', 'Product Attributes'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0 rounded-3 p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-primary mb-1">Product Attributes</h5>
            <small class="text-muted">Create attributes once and use them on every product.</small>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin_panel.admin.product_attributes.store')); ?>" method="POST" class="row g-3 align-items-end">
        <?php echo csrf_field(); ?>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Attribute Name</label>
            <input type="text" name="name" class="form-control" placeholder="Size, Color, Flavour" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Values</label>
            <textarea name="values" class="form-control" rows="2" placeholder="Small, Medium, Large or Red, Blue, Green" required></textarea>
            <small class="text-muted">Separate values with commas or new lines.</small>
        </div>
        <div class="col-md-2">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="status" checked>
                <label class="form-check-label">Active</label>
            </div>
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-plus-circle me-1"></i>Create
            </button>
        </div>
    </form>
</div>

<div class="card shadow-sm border-0 rounded-3 p-4">
    <h5 class="fw-bold text-primary mb-3">Attribute List</h5>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Values</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($attribute->name); ?></strong></td>
                        <td>
                            <?php $__currentLoopData = $attribute->values ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-success-subtle text-success me-1 mb-1"><?php echo e($value); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
                        <td>
                            <span class="badge <?php echo e($attribute->status ? 'bg-success' : 'bg-secondary'); ?>">
                                <?php echo e($attribute->status ? 'Active' : 'Inactive'); ?>

                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#editAttribute<?php echo e($attribute->id); ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form action="<?php echo e(route('admin_panel.admin.product_attributes.destroy', $attribute)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete <?php echo e($attribute->name); ?>?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <tr class="collapse" id="editAttribute<?php echo e($attribute->id); ?>">
                        <td colspan="4">
                            <form action="<?php echo e(route('admin_panel.admin.product_attributes.update', $attribute)); ?>" method="POST" class="row g-3 align-items-end bg-light rounded p-3">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div class="col-md-4">
                                    <label class="form-label">Attribute Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($attribute->name); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Values</label>
                                    <textarea name="values" class="form-control" rows="2" required><?php echo e(implode(', ', $attribute->values ?? [])); ?></textarea>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="status" <?php echo e($attribute->status ? 'checked' : ''); ?>>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Update</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No attributes created yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <?php echo e($attributes->links('pagination::bootstrap-4')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\product_attributes\index.blade.php ENDPATH**/ ?>