<?php $__env->startSection('title', 'Vendor Product Categories'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm rounded-3 border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-primary m-0">Vendor Product Categories</h5>
        <a href="<?php echo e(route('admin_panel.admin.vendor_categories.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Category
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Parent Category</th>
                    <th>Icon</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e(($categories->currentPage() - 1) * $categories->perPage() + $i + 1); ?></td>
                    <td>
                        <?php if($category->image): ?>
                            <img src="<?php echo e(asset($category->image)); ?>" alt="<?php echo e($category->name); ?>" 
                                 style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="width:50px; height:50px; border-radius:4px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo e($category->name); ?></strong>
                        <?php if($category->children()->count() > 0): ?>
                            <br><small class="text-muted"><?php echo e($category->children()->count()); ?> subcategories</small>
                        <?php endif; ?>
                    </td>
                    <td><code class="text-primary"><?php echo e($category->slug); ?></code></td>
                    <td>
                        <?php if($category->parent): ?>
                            <span class="badge bg-info"><?php echo e($category->parent->name); ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Main</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($category->icon): ?>
                            <i class="<?php echo e($category->icon); ?> fs-5"></i>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($category->sort_order); ?></td>
                    <td>
                        <?php if($category->status): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark"><?php echo e($category->vendorProducts()->count()); ?></span>
                    </td>
                    <td>
                        <a href="<?php echo e(route('admin_panel.admin.vendor_categories.edit', $category->id)); ?>" 
                           class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="<?php echo e(route('admin_panel.admin.vendor_categories.destroy', $category->id)); ?>" 
                              method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Delete <?php echo e($category->name); ?>?')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        No vendor categories found.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            Showing <?php echo e($categories->firstItem() ?? 0); ?> to <?php echo e($categories->lastItem() ?? 0); ?> of <?php echo e($categories->total()); ?> categories
        </div>
        <?php echo e($categories->links('pagination::bootstrap-4')); ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\vendor_categories\index.blade.php ENDPATH**/ ?>