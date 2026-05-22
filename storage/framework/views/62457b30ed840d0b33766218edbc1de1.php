
<?php $__env->startSection('title', 'Stock Management'); ?>

<?php $__env->startSection('main-content'); ?>

<!-- ✅ DataTable CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="card shadow-sm border-0 rounded-3 p-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h5 class="fw-bold mb-0">Stock Management</h5>
      <small class="text-muted">Check and update product stock levels</small>
    </div>

    <button class="btn btn-primary rounded-pill" id="addStockBtn">+ Add Stock</button>
  </div>

  <!-- ✅ Stock Table -->
  <div class="table-responsive">
    <table class="table table-striped table-hover" id="adminStockTable">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Product</th>
          <th>Category</th>
          <th>Current Stock</th>
          <th>Minimum Stock</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $current = $product->stock_quantity ?? 0;
            $min = $product->low_stock_alert ?? 0;
            $status = '';
            $badge = '';

            if ($current <= 0) {
                $status = 'Out of Stock';
                $badge = 'danger';
            } elseif ($current < $min) {
                $status = 'Low Stock';
                $badge = 'warning text-dark';
            } else {
                $status = 'In Stock';
                $badge = 'success';
            }
          ?>

          <tr>
            <td><?php echo e($index + 1); ?></td>
            <td><?php echo e($product->name); ?></td>
            <td><?php echo e($product->category->name ?? 'N/A'); ?></td>
            <td><?php echo e($current); ?></td>
            <td><?php echo e($min); ?></td>
            <td><span class="badge bg-<?php echo e($badge); ?>"><?php echo e($status); ?></span></td>
            <td>
              <button class="btn btn-sm btn-info viewStockBtn rounded-pill"
                      data-product="<?php echo e($product->name); ?>"
                      data-category="<?php echo e($product->category->name ?? 'N/A'); ?>"
                      data-qty="<?php echo e($current); ?>"
                      data-min="<?php echo e($min); ?>"
                      data-status="<?php echo e($status); ?>">
                View
              </button>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

</div>


<!-- ✅ Add / Update Stock Modal -->
<div class="modal fade" id="stockAddModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add / Update Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="stockForm" method="POST" action="<?php echo e(route('admin_panel.admin.stocks.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Select Product</label>
            <select name="product_id" class="form-select" required>
              <option value="" disabled selected>Select Product</option>
              <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Quantity to Add</label>
            <input type="number" name="quantity" class="form-control" placeholder="Enter quantity" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Note (optional)</label>
            <input type="text" name="note" class="form-control" placeholder="Optional note">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ✅ View Modal -->
<div class="modal fade" id="stockViewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Stock Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <table class="table table-borderless mb-0">
          <tr><th>Product:</th><td id="m_product"></td></tr>
          <tr><th>Category:</th><td id="m_category"></td></tr>
          <tr><th>Current Stock:</th><td id="m_qty"></td></tr>
          <tr><th>Minimum Stock:</th><td id="m_min"></td></tr>
          <tr><th>Status:</th><td id="m_status"></td></tr>
        </table>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>


<!-- ✅ Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    // ✅ DataTable
    $('#adminStockTable').DataTable({
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        searching: true
    });

    // ✅ Open Add Stock Modal
    $('#addStockBtn').on('click', function () {
        $('#stockForm')[0].reset();
        new bootstrap.Modal(document.getElementById('stockAddModal')).show();
    });

    // ✅ Open View Modal
    $('.viewStockBtn').on('click', function () {
        $('#m_product').text($(this).data('product'));
        $('#m_category').text($(this).data('category'));
        $('#m_qty').text($(this).data('qty'));
        $('#m_min').text($(this).data('min'));
        $('#m_status').text($(this).data('status'));
        new bootstrap.Modal(document.getElementById('stockViewModal')).show();
    });
});
</script>


<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\stock\add-update-stock.blade.php ENDPATH**/ ?>