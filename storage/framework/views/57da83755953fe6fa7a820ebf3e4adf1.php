<?php echo $__env->make('callingcrm.partials.theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('title', 'Campaign Detail'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-megaphone"></i> Campaign Detail</span>
                <h2 class="mt-3 mb-2 fw-bold"><?php echo e($campaign->name); ?></h2>
                <p class="text-muted mb-1">Pipeline: <?php echo e(optional($campaign->pipeline)->name); ?></p>
                <p class="text-muted mb-0">Manager: <?php echo e(optional($campaign->manager)->name ?? 'Not assigned'); ?></p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-dark border">Status: <?php echo e($campaign->status); ?></span>
                <span class="badge bg-light text-dark border">Distribution: <?php echo e($campaign->distribution); ?></span>
                <span class="badge bg-light text-dark border">Priority: <?php echo e($campaign->priority); ?></span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Lead Funnel by Stage</h3>
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total</div>
                        <div class="crm-stat-value"><?php echo e($leadCounts['total']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">In Progress</div>
                        <div class="crm-stat-value"><?php echo e($leadCounts['in_progress']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Closed</div>
                        <div class="crm-stat-value"><?php echo e($leadCounts['closed']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Connected Calls</div>
                        <div class="crm-stat-value"><?php echo e($callSummary['connected_calls']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
            <h3 class="crm-panel-title">Lead Funnel Stages</h3>
            <?php $__empty_1 = true; $__currentLoopData = $campaign->pipeline?->stages ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="crm-mini-item mb-3">
                    <div>
                        <div class="fw-semibold"><?php echo e($stage->name); ?></div>
                        <div class="small text-muted">Stage configured for this pipeline</div>
                    </div>
                    <span class="badge text-bg-light border"><?php echo e($stage->color); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="crm-empty">No stages found for this pipeline.</div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Campaign Statistics</h3>
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total Calls</div>
                        <div class="crm-stat-value"><?php echo e($callSummary['total_calls']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Duration</div>
                        <div class="crm-stat-value"><?php echo e($callSummary['duration']); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Follow Ups Due</div>
                        <div class="crm-stat-value"><?php echo e($callSummary['follow_ups_due']); ?></div>
                    </div>
                </div>
                <hr class="my-4">
                <h4 class="crm-panel-title">Stage Breakdown</h4>
                <?php $__empty_1 = true; $__currentLoopData = $stageBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="fw-semibold"><?php echo e($stage['name']); ?></div>
                            <div class="text-muted"><?php echo e($stage['count']); ?></div>
                        </div>
                        <div class="crm-progress">
                            <div class="crm-progress-bar" style="width: <?php echo e($leadCounts['total'] > 0 ? round(($stage['count'] / $leadCounts['total']) * 100) : 0); ?>%; background: <?php echo e($stage['color']); ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="crm-empty">No stage breakdown available.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card crm-soft-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0">Lead Distribution by Agent</h3>
                    <span class="crm-chip">Uncontacted / Follow-Up / Closed</span>
                </div>
                <?php $__empty_1 = true; $__currentLoopData = $leadDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $share = $leadCounts['total'] > 0 ? round(($row->total / $leadCounts['total']) * 100) : 0;
                    ?>
                    <div class="crm-chart-row">
                        <div class="crm-chart-label"><?php echo e(optional($row->assignedUser)->name ?? 'Unassigned'); ?></div>
                        <div class="crm-chart-track">
                            <div class="crm-chart-fill" style="width: <?php echo e($share); ?>%;"></div>
                        </div>
                        <div class="crm-chart-value"><?php echo e($row->total); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="crm-empty">No distribution data available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/callingcrm/pipeline/show.blade.php ENDPATH**/ ?>