

<?php $__env->startSection('content'); ?>
<div class="container py-5">
  <div class="row">
    <div class="col-md-5">
      <img src="<?php echo e(asset('uploads/products/'.$product->image ?? 'noimg.png')); ?>" class="img-fluid rounded">
    </div>

    <div class="col-md-7">
      <h4 class="fw-bold"><?php echo e($product->name); ?></h4>
      <p><?php echo e($product->description); ?></p>
      <h5 class="text-success mb-3">₹<?php echo e($product->sale_price ?? $product->regular_price); ?></h5>

      <?php if($product->stock_quantity > 0): ?>
      <form method="POST" action="<?php echo e(route('shop.order')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
        <div class="mb-3">
          <label>Quantity</label>
          <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo e($product->stock_quantity); ?>">
        </div>

        <h6 class="mt-4 fw-bold">Enter Your Details</h6>
        <div class="mb-2"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
        <div class="mb-2"><input type="email" name="email" class="form-control" placeholder="Email (optional)"></div>
        <div class="mb-2"><input type="text" name="phone" class="form-control" placeholder="Phone" required></div>
        <div class="mb-2"><textarea name="address" class="form-control" placeholder="Address" required></textarea></div>

        <button type="submit" class="btn btn-success rounded-pill px-4 mt-2">Place Order</button>
      </form>
      <?php else: ?>
        <span class="badge bg-danger">Out of Stock</span>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\frontend\shop\show.blade.php ENDPATH**/ ?>