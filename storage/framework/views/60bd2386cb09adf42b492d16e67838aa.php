
<?php $__env->startSection('title', 'Product List'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0 rounded-3 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold">All Products</h5>
  </div>

  <!-- Products Table -->
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle admin-prod-table">
      <thead class="table-light">
        <tr>
          <th><input type="checkbox" id="admin-prod-select-all"></th>
          <th>Product</th>
          <th>SKU / Code</th>
          <th>Category</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Status</th>
          <th>Date Added</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
          $images = json_decode($product->images, true);
          $firstImage = $images[0] ?? null;
        ?>
        <tr>
          <td><input type="checkbox" class="admin-prod-checkbox"></td>

          <!-- Product Image + Name -->
          <td>
            <div class="d-flex align-items-center">
              <?php if($firstImage && file_exists(public_path($firstImage))): ?>
                <img src="<?php echo e(asset($firstImage)); ?>" alt="<?php echo e($product->name); ?>" class="me-2 rounded" width="50" height="50" style="object-fit:cover;">
              <?php else: ?>
                <img src="<?php echo e(asset('uploads/products/default.png')); ?>" alt="No image" class="me-2 rounded" width="50" height="50" style="object-fit:cover;">
              <?php endif; ?>
              <span><?php echo e($product->name); ?></span>
            </div>
          </td>

          <td><?php echo e($product->sku); ?></td>

          <td>
            <?php echo e($product->category?->name); ?>

            <?php if($product->subcategory): ?>
              <br><small class="text-muted">→ <?php echo e($product->subcategory->name); ?></small>
            <?php endif; ?>
          </td>

          <td>₹<?php echo e($product->sale_price ?? $product->regular_price); ?></td>

          <td><?php echo e($product->stock_quantity); ?></td>

          <td>
            <?php if($product->status): ?>
              <span class="badge bg-success">Active</span>
            <?php else: ?>
              <span class="badge bg-danger">Inactive</span>
            <?php endif; ?>
          </td>

          <td><?php echo e($product->created_at->format('Y-m-d')); ?></td>

          <td class="text-center">
            <a href="#" class="btn btn-info btn-sm">View</a>
            <a href="<?php echo e(route('admin_panel.admin.products.edit', $product->id)); ?>" class="btn btn-warning btn-sm">
    Edit
  </a>
             <form action="<?php echo e(route('admin_panel.admin.products.destroy', $product->id)); ?>" method="POST" class="d-inline"
        onsubmit="return confirm('Are you sure you want to delete this product?');">
      <?php echo csrf_field(); ?>
      <?php echo method_field('DELETE'); ?>
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
  </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
          <td colspan="9" class="text-center text-muted">No products found</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $__env->stopSection(); ?>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function(){
  $('.admin-prod-table').DataTable({
    pageLength: 10,
    responsive: true
  });

  // Select/Deselect All
  $('#admin-prod-select-all').on('click', function(){
    $('.admin-prod-checkbox').prop('checked', this.checked);
  });
});
</script>


<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\product\index.blade.php ENDPATH**/ ?>