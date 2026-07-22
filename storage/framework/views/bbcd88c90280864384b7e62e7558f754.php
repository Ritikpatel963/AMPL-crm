
<?php $__env->startSection('title', 'Edit Product'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card p-4 shadow-sm border-0">
    <h5 class="fw-bold mb-3">Edit Product</h5>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin_panel.admin.products.update', $product->id)); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Product Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo e($product->name); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>SKU <span class="text-muted">(optional)</span></label>
                <input type="text" name="sku" class="form-control" value="<?php echo e($product->sku); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat->id); ?>" <?php echo e($cat->id == $product->category_id ? 'selected' : ''); ?>>
                            <?php echo e($cat->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Subcategory</label>
                <select name="subcategory_id" class="form-control">
                    <option value="">-- Select Subcategory --</option>
                    <?php $__currentLoopData = $subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($sub->id); ?>" <?php echo e($sub->id == $product->subcategory_id ? 'selected' : ''); ?>>
                            <?php echo e($sub->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Brand</label>
                <input type="text" name="brand" class="form-control" value="<?php echo e($product->brand); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Regular Price</label>
                <input type="number" step="0.01" name="regular_price" class="form-control" value="<?php echo e($product->regular_price); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Sale Price</label>
                <input type="number" step="0.01" name="sale_price" class="form-control" value="<?php echo e($product->sale_price); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Tax (%)</label>
                <input type="number" step="0.01" name="tax" class="form-control" value="<?php echo e($product->tax); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control" value="<?php echo e($product->stock_quantity); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Low Stock Alert</label>
                <input type="number" name="low_stock_alert" class="form-control" value="<?php echo e($product->low_stock_alert); ?>">
            </div>

            <div class="col-md-12 mb-3">
                <h6 class="fw-semibold mb-3 text-primary">Attributes & Variations</h6>
                <?php echo $__env->make('admin_panel.product.partials.product_variations_builder', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            <div class="col-md-12 mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control summernote" rows="3"><?php echo e($product->description); ?></textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label>Video URL</label>
                <input type="text" name="video_url" class="form-control" value="<?php echo e($product->video_url); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Upload New Images (Optional)</label>
                <input type="file" name="images[]" class="form-control" multiple>
            </div>

            <div class="col-md-12 mb-3">
                <label>Current Images:</label><br>
                <?php if($product->images): ?>
                    <?php $__currentLoopData = json_decode($product->images); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <img src="<?php echo e(asset($img)); ?>" width="60" height="60" class="rounded m-1 border">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>

            <div class="col-md-6 mb-3 form-check">
                <input type="checkbox" name="status" class="form-check-input" id="statusCheck" <?php echo e($product->status ? 'checked' : ''); ?>>
                <label class="form-check-label" for="statusCheck">Active</label>
            </div>

            <div class="col-md-6 mb-3 form-check">
                <input type="checkbox" name="featured" class="form-check-input" id="featuredCheck" <?php echo e($product->featured ? 'checked' : ''); ?>>
                <label class="form-check-label" for="featuredCheck">Trending Product</label>
            </div>

            <div class="col-md-6 mb-3 form-check">
                <input type="checkbox" name="is_offer" class="form-check-input" id="offerCheck" <?php echo e($product->is_offer ? 'checked' : ''); ?>>
                <label class="form-check-label" for="offerCheck">Offer Product</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update Product</button>
        <a href="<?php echo e(route('admin_panel.admin.products.index')); ?>" class="btn btn-secondary mt-3">Back</a>
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
  window.initialProductAttributes = <?php echo json_encode($product->attributes_json ?? [], 15, 512) ?>;
  window.initialProductVariations = <?php echo json_encode($product->variations_json ?? [], 15, 512) ?>;
  window.availableProductAttributes = <?php echo json_encode($productAttributes, 15, 512) ?>;
</script>
<?php echo $__env->make('admin_panel.product.partials.product_variations_script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\product\edit_product.blade.php ENDPATH**/ ?>