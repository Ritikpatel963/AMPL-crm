<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo e(route('shop.index')); ?>">MyShop</a>
  </div>
</nav>

<?php echo $__env->yieldContent('content'); ?>

</body>
</html>
<?php /**PATH E:\website-project\Amplchat\CMS\resources\views\frontend\layout\app.blade.php ENDPATH**/ ?>