<?php $__env->startSection('title', 'Vendor Products'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-4 text-primary">Products for <?php echo e($vendor->name); ?></h5>

    <div class="mb-3">
        <a href="<?php echo e(route('admin_panel.admin.vendors.index')); ?>" class="btn btn-secondary">Back to Vendor List</a>
        <a href="<?php echo e(route('admin_panel.admin.vendors.show', $vendor->id)); ?>" class="btn btn-info ms-2">Vendor Profile</a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                    <i class="bi bi-funnel-fill me-2"></i>Filter Products
                </button>
            </h6>
        </div>
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('admin_panel.admin.vendors.products', $vendor->id)); ?>" id="filterForm">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Product</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by product name...">
                        </div>

                        <!-- Category -->
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                <?php $__currentLoopData = $filterOptions['categories'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category['id']); ?>" <?php echo e(request('category_id') == $category['id'] ? 'selected' : ''); ?>>
                                        <?php echo e($category['name']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="col-md-3">
                            <label for="brand_name" class="form-label">Brand</label>
                            <select class="form-select" id="brand_name" name="brand_name">
                                <option value="">All Brands</option>
                                <?php $__currentLoopData = $filterOptions['brands'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($brand); ?>" <?php echo e(request('brand_name') == $brand ? 'selected' : ''); ?>>
                                        <?php echo e($brand); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Unit Type -->
                        <div class="col-md-3">
                            <label for="unit_type" class="form-label">Unit Type</label>
                            <select class="form-select" id="unit_type" name="unit_type">
                                <option value="">All Unit Types</option>
                                <?php $__currentLoopData = $filterOptions['unit_types'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unitType); ?>" <?php echo e(request('unit_type') == $unitType ? 'selected' : ''); ?>>
                                        <?php echo e(strtoupper($unitType)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Unit Size -->
                        <div class="col-md-3">
                            <label for="unit_size" class="form-label">Unit Size</label>
                            <select class="form-select" id="unit_size" name="unit_size">
                                <option value="">All Unit Sizes</option>
                                <?php $__currentLoopData = $filterOptions['unit_sizes'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitSize): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unitSize); ?>" <?php echo e(request('unit_size') == $unitSize ? 'selected' : ''); ?>>
                                        <?php echo e($unitSize); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Stock Status -->
                        <div class="col-md-3">
                            <label for="stock_status" class="form-label">Stock Status</label>
                            <select class="form-select" id="stock_status" name="stock_status">
                                <option value="">All Stock Status</option>
                                <?php $__currentLoopData = $filterOptions['stock_status_options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option['value']); ?>" <?php echo e(request('stock_status') == $option['value'] ? 'selected' : ''); ?>>
                                        <?php echo e($option['label']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Expiry Status -->
                        <div class="col-md-3">
                            <label for="expiry_status" class="form-label">Expiry Status</label>
                            <select class="form-select" id="expiry_status" name="expiry_status">
                                <option value="">All Expiry Status</option>
                                <?php $__currentLoopData = $filterOptions['expiry_status_options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option['value']); ?>" <?php echo e(request('expiry_status') == $option['value'] ? 'selected' : ''); ?>>
                                        <?php echo e($option['label']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="col-md-3">
                            <label for="min_price" class="form-label">Min Price</label>
                            <input type="number" class="form-control" id="min_price" name="min_price" value="<?php echo e(request('min_price')); ?>" placeholder="0">
                        </div>
                        <div class="col-md-3">
                            <label for="max_price" class="form-label">Max Price</label>
                            <input type="number" class="form-control" id="max_price" name="max_price" value="<?php echo e(request('max_price')); ?>" placeholder="10000">
                        </div>

                        <!-- Quantity Range -->
                        <div class="col-md-3">
                            <label for="min_quantity" class="form-label">Min Quantity</label>
                            <input type="number" class="form-control" id="min_quantity" name="min_quantity" value="<?php echo e(request('min_quantity')); ?>" placeholder="0">
                        </div>
                        <div class="col-md-3">
                            <label for="max_quantity" class="form-label">Max Quantity</label>
                            <input type="number" class="form-control" id="max_quantity" name="max_quantity" value="<?php echo e(request('max_quantity')); ?>" placeholder="1000">
                        </div>

                        <!-- Sort Options -->
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <?php $__currentLoopData = $filterOptions['sort_options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option['value']); ?>" <?php echo e(request('sort_by', 'created_at') == $option['value'] ? 'selected' : ''); ?>>
                                        <?php echo e($option['label']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="desc" <?php echo e(request('sort_order', 'desc') == 'desc' ? 'selected' : ''); ?>>Descending</option>
                                <option value="asc" <?php echo e(request('sort_order', 'desc') == 'asc' ? 'selected' : ''); ?>>Ascending</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Apply Filters
                        </button>
                        <a href="<?php echo e(route('admin_panel.admin.vendors.products', $vendor->id)); ?>" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <table id="vendorProductsTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Expiry</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td>
                    <?php if(!empty($product->images[0])): ?>
                        <img src="<?php echo e(asset($product->images[0])); ?>" alt="product" class="rounded" style="width:80px; height:80px; object-fit:cover; border: 2px solid #dee2e6;" />
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width:80px; height:80px; border: 2px solid #dee2e6;">
                            <i class="bi bi-image text-muted fs-4"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?php echo e($product->product_name); ?></td>
                <td><?php echo e($product->category_name); ?></td>
                <td><?php echo e($product->brand_name ?? '—'); ?></td>
                <td>
                    <?php if($product->unit_size && $product->unit_type): ?>
                        <?php echo e($product->unit_size); ?> <?php echo e(strtoupper($product->unit_type)); ?>

                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge <?php echo e($product->quantity > 10 ? 'bg-success' : ($product->quantity > 0 ? 'bg-warning' : 'bg-danger')); ?>">
                        <?php echo e($product->formatted_quantity); ?>

                    </span>
                </td>
                <td><?php echo e($product->product_rate ? '₹' . number_format($product->product_rate, 2) : '—'); ?></td>
                <td>
                    <?php if($product->product_expiry): ?>
                        <?php
                            $daysUntilExpiry = now()->diffInDays($product->product_expiry, false);
                        ?>
                        <?php if($daysUntilExpiry < 0): ?>
                            <span class="badge bg-danger">Expired</span>
                        <?php elseif($daysUntilExpiry <= 7): ?>
                            <span class="badge bg-warning"><?php echo e($product->product_expiry_formatted); ?></span>
                        <?php elseif($daysUntilExpiry <= 30): ?>
                            <span class="badge bg-info"><?php echo e($product->product_expiry_formatted); ?></span>
                        <?php else: ?>
                            <span class="text-muted"><?php echo e($product->product_expiry_formatted); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-search fs-1 text-muted mb-2"></i>
                    <br>No products found matching your filters.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Auto-submit filter form on select change for better UX
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });

    // Add loading state to filter form
    $('#filterForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Filtering...');
    });

    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#vendorProductsTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            columnDefs: [
                { orderable: false, targets: [1] } // Disable sorting on image column
            ],
            language: {
                search: "Search products:",
                lengthMenu: "Show _MENU_ products per page",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "No products available",
                infoFiltered: "(filtered from _MAX_ total products)",
                zeroRecords: "No matching products found",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    } else {
        console.log('DataTables not available');
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\vendors\products.blade.php ENDPATH**/ ?>