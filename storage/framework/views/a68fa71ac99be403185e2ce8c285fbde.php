
<?php echo $__env->make('callingcrm.partials.theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('title', 'Calling CRM Dashboard'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="crm-page-header">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="eyebrow"><i class="bi bi-telephone-forward"></i> Calling CRM</span>
                <h2 class="mt-3 mb-2 fw-bold">Team calling performance at a glance</h2>
                <p class="text-muted mb-0">Track connection quality, active agents, stage movement, and campaign shortcuts from one place.</p>
            </div>
            <div class="col-lg-4">
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Connected Rate</div>
                        <div class="crm-stat-value"><?php echo e($connectedPercent); ?>%</div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Pinned Campaigns</div>
                        <div class="crm-stat-value"><?php echo e($pinnedCampaigns->count()); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Call Overview</div>
                        <div class="crm-stat-value mt-2"><?php echo e($totalCalls); ?></div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-telephone-outbound"></i></span>
                </div>
                <div class="text-muted">Total calls recorded</div>
                <div class="mt-3 small fw-semibold text-success"><?php echo e($connectedCalls); ?> connected successfully</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Agent Activity</div>
                        <div class="crm-stat-value mt-2"><?php echo e($activeAgents); ?></div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-people"></i></span>
                </div>
                <div class="text-muted">Agents tagged for Calling CRM</div>
                <div class="mt-3 small fw-semibold text-warning"><?php echo e($agentsOnBreak); ?> currently on break</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Follow Through</div>
                        <div class="crm-stat-value mt-2"><?php echo e($leadsByStage->sum('total')); ?></div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-kanban"></i></span>
                </div>
                <div class="text-muted">Leads mapped into visible stages</div>
                <div class="mt-3 small fw-semibold text-primary"><?php echo e($leadsByStage->count()); ?> stage buckets active</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card crm-stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Campaign Health</div>
                        <div class="crm-stat-value mt-2"><?php echo e($pinnedCampaigns->count()); ?></div>
                    </div>
                    <span class="crm-icon"><i class="bi bi-broadcast-pin"></i></span>
                </div>
                <div class="text-muted">Pinned campaigns ready for fast access</div>
                <div class="mt-3 small fw-semibold text-info">Use pipeline to manage distribution</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0">Leads by Stage</h3>
                    <span class="crm-chip">Live mix</span>
                </div>
                <?php $__empty_1 = true; $__currentLoopData = $leadsByStage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="fw-semibold"><?php echo e($stage['name']); ?></div>
                            <div class="text-muted"><?php echo e($stage['total']); ?> leads</div>
                        </div>
                        <div class="crm-progress">
                            <div class="crm-progress-bar" style="width: <?php echo e($stage['percentage']); ?>%; background: <?php echo e($stage['color']); ?>;"></div>
                        </div>
                        <div class="small text-muted mt-2"><?php echo e($stage['percentage']); ?>% of visible stage distribution</div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="crm-empty">No lead stage data yet.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card crm-soft-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="crm-panel-title mb-0">Quick Access</h3>
                    <a href="<?php echo e(route('callingcrm.reports.index')); ?>" class="btn btn-outline-primary btn-sm">Campaigns Report</a>
                </div>
                <div class="crm-mini-list mb-4">
                    <?php $__currentLoopData = $quickAccess; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($item['href']); ?>" class="crm-action-link">
                            <span><?php echo e($item['label']); ?></span>
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <h4 class="crm-panel-title">Pinned Campaigns</h4>
                <?php $__empty_1 = true; $__currentLoopData = $pinnedCampaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold"><?php echo e($campaign->name); ?></div>
                            <div class="small text-muted">Shortcut into campaign detail</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary text-uppercase"><?php echo e($campaign->status); ?></span>
                            <div class="mt-2">
                                <a href="<?php echo e(route('callingcrm.pipeline.show', $campaign)); ?>" class="btn btn-sm btn-light">Open</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="crm-empty">No campaigns created yet.</div>
                <?php endif; ?>

                <hr class="my-4">

                <h4 class="crm-panel-title">Tools Panel</h4>
                <div class="crm-mini-list">
                    <?php $__currentLoopData = $toolsPanel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($item['href']); ?>" class="crm-action-link">
                            <span><?php echo e($item['label']); ?></span>
                            <i class="bi bi-graph-up-arrow"></i>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/callingcrm/dashboard/index.blade.php ENDPATH**/ ?>