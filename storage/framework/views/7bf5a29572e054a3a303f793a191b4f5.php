
<?php echo $__env->make('callingcrm.partials.theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('title', 'Calling CRM Reports'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-file-earmark-bar-graph"></i> Reports</span>
                <h2 class="mt-3 mb-2 fw-bold">Operational reports for calling, login, and lead progress</h2>
                <p class="text-muted mb-0">Use this workspace for all reports, favourites, recently viewed items, and queued CSV exports.</p>
            </div>
            <a href="<?php echo e(route('callingcrm.reports.export', request()->query())); ?>" class="btn btn-outline-primary"><i class="bi bi-download me-2"></i>Download Logs</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Views</h3>
                <form method="GET" action="<?php echo e(route('callingcrm.reports.index')); ?>" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Report</label>
                        <select name="report" class="form-select">
                            <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($report); ?>" <?php if($selectedReport === $report): echo 'selected'; endif; ?>><?php echo e($report); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Filter by Agent</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Agents</option>
                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($agent->id); ?>" <?php if((string) $userId === (string) $agent->id): echo 'selected'; endif; ?>><?php echo e($agent->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">From</label>
                        <input type="date" name="from" class="form-control" value="<?php echo e($from->toDateString()); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">To</label>
                        <input type="date" name="to" class="form-control" value="<?php echo e($to->toDateString()); ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Run Report</button>
                        <a href="<?php echo e(route('callingcrm.reports.index')); ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card crm-soft-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0"><?php echo e($selectedReport); ?></h3>
                    <span class="badge bg-light text-dark border"><?php echo e(count($reportData['rows'])); ?> rows</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle crm-table mb-0">
                        <thead>
                            <tr>
                                <?php $__currentLoopData = $reportData['headers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th><?php echo e($header); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $reportData['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td><?php echo e($cell); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e(max(count($reportData['headers']), 1)); ?>" class="text-muted">No report data found for the selected range.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/callingcrm/reports/index.blade.php ENDPATH**/ ?>