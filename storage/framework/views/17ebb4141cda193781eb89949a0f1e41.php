<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="container-fluid p-0" style="background-color: #eef3fc; min-height: 100vh;">
        <!-- 🔹 Top Bar -->
        <div class="bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center shadow-sm"
             style="height: 60px;">
            <h5 class="mb-0 fw-semibold">Chat</h5>
            <i class="bi bi-three-dots-vertical fs-5"></i>
        </div>

        <!-- 🔹 Chat List -->
        <div class="mt-2">
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('chat', $user->id)); ?>"
                   class="d-flex align-items-center p-3 text-decoration-none text-dark border-bottom"
                   style="background-color: #fff; transition: background 0.2s;"
                   onmouseover="this.style.background='#e7f0ff'"
                   onmouseout="this.style.background='#fff'">
                   
                    <!-- Profile Picture -->
                    <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($user->name)); ?>&background=0d6efd&color=fff"
                         alt="Profile"
                         class="rounded-circle me-3"
                         width="55" height="55">
                         
                    <!-- User Info -->
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-semibold"><?php echo e($user->name); ?></h6>
                        <small class="text-muted">Tap to chat</small>
                    </div>

                    <!-- Three Dots Icon -->
                    <i class="bi bi-three-dots-vertical text-muted"></i>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- ✅ Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\website-project\Amplchat\CMS\resources\views\dashboard.blade.php ENDPATH**/ ?>