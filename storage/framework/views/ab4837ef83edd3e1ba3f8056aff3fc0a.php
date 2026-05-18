
<?php echo $__env->make('callingcrm.partials.theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('title', 'Calling CRM Pipeline'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-diagram-3"></i> Pipeline</span>
                <h2 class="mt-3 mb-2 fw-bold">Campaigns grouped by pipeline</h2>
                <p class="text-muted mb-0">Use this area for active campaign monitoring, hide-paused behavior, and distribution control.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPipelineModal">Create Pipeline</button>
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createCampaignModal">Create Campaign</button>
            </div>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $__empty_1 = true; $__currentLoopData = $pipelines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pipeline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="card crm-soft-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h3 class="crm-panel-title mb-1"><?php echo e($pipeline->name); ?></h3>
                    <div class="text-muted"><?php echo e($pipeline->campaigns->count()); ?> campaigns in this pipeline</div>
                </div>
                <span class="crm-chip">Pipeline Group</span>
            </div>
            <?php $__empty_2 = true; $__currentLoopData = $pipeline->campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                <div class="crm-mini-item mb-3">
                    <div>
                        <div class="fw-semibold"><?php echo e($campaign->name); ?></div>
                        <div class="small text-muted">
                            Manager: <?php echo e(optional($campaign->manager)->name ?: 'Not assigned'); ?>

                            · Agents: <?php echo e($campaign->agents->count()); ?>

                        </div>
                    </div>
                    <div class="text-end">
                        <div class="mb-2">
                            <span class="badge <?php echo e($campaign->status === 'paused' ? 'bg-warning text-dark' : 'bg-success'); ?>"><?php echo e($campaign->status); ?></span>
                        </div>
                        <a href="<?php echo e(route('callingcrm.pipeline.show', $campaign)); ?>" class="btn btn-sm btn-outline-primary">View Campaign</a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                <div class="crm-empty">No campaigns in this pipeline yet.</div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card crm-soft-card p-4">
            <div class="crm-empty">No pipelines created yet.</div>
        </div>
    <?php endif; ?>

    <div class="modal fade" id="createPipelineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="<?php echo e(route('callingcrm.pipeline.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Create Pipeline</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Pipeline Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Stages</label>
                                <input type="text" name="stages" class="form-control" placeholder="Fresh Lead, Follow Up, Closed Won, Closed Lost">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Dispositions</label>
                                <input type="text" name="dispositions" class="form-control" placeholder="Follow Up:in_progress, Closed Won:closed_won, Closed Lost:closed_lost">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create Pipeline</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createCampaignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="<?php echo e(route('callingcrm.campaigns.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Create Campaign</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Campaign Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pipeline</label>
                                <select name="pipeline_id" class="form-select" required>
                                    <option value="">Select pipeline</option>
                                    <?php $__currentLoopData = $pipelines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pipeline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($pipeline->id); ?>"><?php echo e($pipeline->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Manager</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">Select manager</option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> (<?php echo e($user->role); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Agents</label>
                                <select name="agent_ids[]" class="form-select" multiple size="5">
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> (<?php echo e($user->role); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="paused">Paused</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Distribution</label>
                                <select name="distribution" class="form-select">
                                    <option value="on_demand">On Demand</option>
                                    <option value="auto_assign">Auto Assign</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/callingcrm/pipeline/index.blade.php ENDPATH**/ ?>