
<?php echo $__env->make('callingcrm.partials.theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('title', 'Calling CRM Trends'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-graph-up"></i> Trends</span>
                <h2 class="mt-3 mb-2 fw-bold">Business and user trend snapshots</h2>
                <p class="text-muted mb-0">This area is ready for period comparison, grouped bar charts, saved graph presets, and custom date ranges.</p>
            </div>
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Request Graph</button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Business Trend KPIs</h3>
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total SMS Sent</div>
                        <div class="crm-stat-value"><?php echo e($metrics['total_sms_sent']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total Calls</div>
                        <div class="crm-stat-value"><?php echo e($metrics['total_calls']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Converted Leads</div>
                        <div class="crm-stat-value"><?php echo e($metrics['converted_leads']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Call Duration</div>
                        <div class="crm-stat-value"><?php echo e($metrics['call_duration']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Period Filters</h3>
                <form method="GET" action="<?php echo e(route('callingcrm.trends.index')); ?>" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Range</label>
                        <select name="range" class="form-select">
                            <?php $__currentLoopData = $periodFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php if($activeFilter === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">From</label>
                        <input type="date" name="from" class="form-control" value="<?php echo e($from->toDateString()); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">To</label>
                        <input type="date" name="to" class="form-control" value="<?php echo e($to->toDateString()); ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Apply</button>
                        <a href="<?php echo e(route('callingcrm.trends.index')); ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card crm-soft-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="crm-panel-title mb-0">Total Calls vs Calls Connected</h3>
            <span class="badge bg-light text-dark border"><?php echo e($from->format('d M Y')); ?> - <?php echo e($to->format('d M Y')); ?></span>
        </div>
        <?php
            $maxCalls = max(1, collect($callVsConnected)->max('calls'));
        ?>
        <?php $__empty_1 = true; $__currentLoopData = $callVsConnected; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="crm-chart-row">
                <div class="crm-chart-label"><?php echo e($point['label']); ?></div>
                <div class="w-100">
                    <div class="crm-chart-track mb-2">
                        <div class="crm-chart-fill" style="width: <?php echo e(round(($point['calls'] / $maxCalls) * 100)); ?>%;"></div>
                    </div>
                    <div class="crm-chart-track">
                        <div class="crm-chart-fill" style="width: <?php echo e(round(($point['connected'] / $maxCalls) * 100)); ?>%; background: linear-gradient(90deg, #10b981, #22c55e);"></div>
                    </div>
                </div>
                <div class="crm-chart-value"><?php echo e($point['calls']); ?>/<?php echo e($point['connected']); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="crm-empty">No trend data available.</div>
        <?php endif; ?>
    </div>

    <div class="card crm-soft-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="crm-panel-title mb-0">Users Trend</h3>
            <span class="badge bg-light text-dark border">Agent breakdown</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle crm-table mb-0">
                <thead>
                    <tr>
                        <th>Agent Name</th>
                        <th class="text-center">Total Calls</th>
                        <th class="text-center">Connected Calls</th>
                        <th class="text-center">Conversion (%)</th>
                        <th class="text-end">Total Talk Time</th>
                        <th class="text-end">Avg. Talk Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $userTrend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $conversion = $item['calls'] > 0 ? round(($item['connected'] / $item['calls']) * 100) : 0;
                        ?>
                        <tr>
                            <td><div class="fw-semibold"><?php echo e($item['name']); ?></div></td>
                            <td class="text-center"><?php echo e($item['calls']); ?></td>
                            <td class="text-center"><?php echo e($item['connected']); ?></td>
                            <td class="text-center">
                                <span class="badge <?php echo e($conversion > 50 ? 'bg-success' : 'bg-secondary'); ?>"><?php echo e($conversion); ?>%</span>
                            </td>
                            <td class="text-end"><?php echo e(gmdate("H:i:s", $item['total_duration'])); ?></td>
                            <td class="text-end"><?php echo e(gmdate("i:s", $item['average_duration'])); ?> min</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-muted text-center py-4">No agent performance data available for this period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/callingcrm/trends/index.blade.php ENDPATH**/ ?>