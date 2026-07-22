<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 40px; }
        .card { background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .btn { background: #00c3ff; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Admin Settings</h2>
        
        <?php if(session('success')): ?>
            <div class="success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin_panel.admin.settings.update')); ?>">
            <?php echo csrf_field(); ?>
            <label>
                <input type="checkbox" name="google_login_enabled" value="1" <?php echo e($googleLoginEnabled ? 'checked' : ''); ?>>
                Enable Google Login
            </label>
            <br><br>
            <button type="submit" class="btn">Save Settings</button>
        </form>
        <br>
        <a href="<?php echo e(route('admin_panel.admin.index')); ?>">Back to Dashboard</a>
    </div>
</body>
</html>
<?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/admin/settings.blade.php ENDPATH**/ ?>