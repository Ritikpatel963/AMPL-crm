

<?php $__env->startSection('content'); ?>
<div class="container py-5">
  <h3 class="mb-4 fw-bold">Shop Products</h3>

  <div class="row">
    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="<?php echo e(asset('uploads/products/'.$product->image ?? 'noimg.png')); ?>" class="card-img-top" alt="">
          <div class="card-body">
            <h6 class="fw-bold"><?php echo e($product->name); ?></h6>
            <p class="text-muted mb-1"><?php echo e($product->brand); ?></p>
            <p class="mb-2"><strong>₹<?php echo e($product->sale_price ?? $product->regular_price); ?></strong></p>
            <a href="<?php echo e(route('shop.show', $product->id)); ?>" class="btn btn-primary btn-sm rounded-pill">View</a>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\frontend\shop\index.blade.php ENDPATH**/ ?>