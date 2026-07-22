
<?php $__env->startSection('title', 'Add New Product'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0 rounded-3 p-4">

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h5 class="fw-bold mb-0">Add New Product</h5>
      <small class="text-muted">Fill in the details to add a new product</small>
    </div>
    <a href="<?php echo e(route('admin_panel.admin.products.index')); ?>" class="btn btn-secondary btn-sm rounded-pill">
      <i class="bi bi-arrow-left me-2"></i>Back to Products
    </a>
  </div>

  <!-- Product Form -->
  <form action="<?php echo e(route('admin_panel.admin.products.store')); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <!-- Basic Info -->
    <h6 class="fw-semibold mb-3 text-primary">Product Basic Info</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label">Product Name</label>
        <input type="text" class="form-control" name="name" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">SKU / Product Code <span class="text-muted">(optional)</span></label>
        <input type="text" class="form-control" name="sku">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control summernote" name="description" rows="4"></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id" required>
          <option selected disabled>Select Category</option>
          <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Subcategory</label>
        <select class="form-select" name="subcategory_id">
          <option selected disabled>Select Subcategory</option>
          <?php $__currentLoopData = $subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($sub->id); ?>"><?php echo e($sub->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Brand</label>
        <input type="text" class="form-control" name="brand">
      </div>
    </div>

    <!-- Pricing -->
    <h6 class="fw-semibold mb-3 text-primary">Pricing</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Regular Price</label>
        <input type="number" class="form-control" name="regular_price" step="0.01">
      </div>
      <div class="col-md-4">
        <label class="form-label">Sale Price</label>
        <input type="number" class="form-control" name="sale_price" step="0.01">
      </div>
      <div class="col-md-4">
        <label class="form-label">Tax (VAT/GST %)</label>
        <input type="number" class="form-control" name="tax" step="0.01">
      </div>
    </div>

    <!-- Inventory -->
    <h6 class="fw-semibold mb-3 text-primary">Inventory</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Stock Quantity</label>
        <input type="number" class="form-control" name="stock_quantity">
      </div>
      <div class="col-md-4">
        <label class="form-label">Stock Status</label>
        <select class="form-select" name="stock_status">
          <option value="in_stock">In Stock</option>
          <option value="out_of_stock">Out of Stock</option>
          <option value="preorder">Preorder</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Low Stock Alert</label>
        <input type="number" class="form-control" name="low_stock_alert">
      </div>
    </div>

    <!-- Attributes / Variations -->
    <h6 class="fw-semibold mb-3 text-primary">Attributes & Variations</h6>
    <?php echo $__env->make('admin_panel.product.partials.product_variations_builder', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Images -->
    <h6 class="fw-semibold mb-3 text-primary">Images / Media</h6>
    <div class="row g-3 mb-4">
      <div class="col-12">
        <label class="form-label">Product Images</label>
        <input type="file" class="form-control" name="images[]" multiple>
      </div>
      <div class="col-12">
        <label class="form-label">Video URL (Optional)</label>
        <input type="url" class="form-control" name="video_url" placeholder="https://example.com/video">
      </div>
    </div>

    <!-- Status -->
    <h6 class="fw-semibold mb-3 text-primary">Status</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="status" checked>
          <label class="form-check-label">Active</label>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="featured">
          <label class="form-check-label">Trending Product</label>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_offer">
          <label class="form-check-label">Offer Product</label>
        </div>
      </div>
    </div>

    <div class="mt-3">
      <button type="submit" class="btn btn-success btn-sm rounded-pill px-4">
        <i class="bi bi-save me-2"></i>Save Product
      </button>
    </div>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
  $('.summernote').summernote({
    height: 220,
    placeholder: 'Write product description...'
  });
  window.availableProductAttributes = <?php echo json_encode($productAttributes, 15, 512) ?>;
  window.initialProductAttributes = [];
  window.initialProductVariations = [];
</script>
<?php echo $__env->make('admin_panel.product.partials.product_variations_script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\product\add_product.blade.php ENDPATH**/ ?>