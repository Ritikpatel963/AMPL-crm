

<?php $__env->startSection('main-content'); ?>
<div class="container mt-4">
    <h2>Update Profile</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin_panel.admin.update.profile')); ?>">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="<?php echo e(old('name', $admin->name)); ?>" class="form-control" required>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" value="<?php echo e(old('phone', $admin->phone)); ?>" class="form-control" <?php echo e($admin->isMainAdmin() ? 'readonly' : 'required'); ?>>
            <?php if($admin->isMainAdmin()): ?>
                <small class="text-muted">Main admin phone number cannot be changed.</small>
            <?php endif; ?>
            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\admin\edit-profile.blade.php ENDPATH**/ ?>