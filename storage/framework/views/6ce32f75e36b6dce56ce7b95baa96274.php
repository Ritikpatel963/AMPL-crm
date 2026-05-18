
<?php echo $__env->make('callingcrm.partials.theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('title', 'Create Lead'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-person-plus"></i> New Contact</span>
                <h2 class="mt-3 mb-2 fw-bold">Prepare the lead creation workspace</h2>
                <p class="text-muted mb-0">This page defines the fields, campaign targets, and custom CRM properties for new lead entry.</p>
            </div>
            <div class="crm-kpi-box">
                <div class="crm-section-label">Campaign Options</div>
                <div class="crm-stat-value"><?php echo e($campaigns->count()); ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card crm-soft-card p-4 h-100">
                <h3 class="crm-panel-title">Lead Entry Checklist</h3>
                <div class="crm-mini-list">
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Name</div>
                            <div class="small text-muted">Primary contact identity</div>
                        </div>
                        <span class="crm-chip">Text</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Phone</div>
                            <div class="small text-muted">Required unique field for call activity</div>
                        </div>
                        <span class="badge bg-danger">Required</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Email</div>
                            <div class="small text-muted">Optional contact enrichment</div>
                        </div>
                        <span class="crm-chip">Optional</span>
                    </div>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold">Campaign</div>
                            <div class="small text-muted">Required for pipeline placement</div>
                        </div>
                        <span class="badge bg-danger">Required</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card crm-soft-card p-4 mb-4">
                <h3 class="crm-panel-title">Create Lead</h3>
                <form method="POST" action="<?php echo e(route('callingcrm.contacts.store')); ?>" class="row g-3">
                    <?php echo csrf_field(); ?>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone')); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Source</label>
                        <select name="source" class="form-select" required>
                            <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($source); ?>" <?php if(old('source', 'MANUAL') === $source): echo 'selected'; endif; ?>><?php echo e($source); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Campaign</label>
                        <select name="campaign_id" class="form-select" required>
                            <option value="">Select campaign</option>
                            <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($campaign->id); ?>" <?php if((string) old('campaign_id') === (string) $campaign->id): echo 'selected'; endif; ?>><?php echo e($campaign->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assign Agent</label>
                        <select name="user_id" class="form-select">
                            <option value="">Auto / Unassigned</option>
                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($agent->id); ?>" <?php if((string) old('user_id') === (string) $agent->id): echo 'selected'; endif; ?>><?php echo e($agent->name); ?> (<?php echo e($agent->role); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tags</label>
                        <input type="text" name="tags" class="form-control" value="<?php echo e(old('tags')); ?>" placeholder="fresh, follow-up, premium">
                    </div>
                    <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e($property->name); ?></label>
                            <input type="text" name="property_<?php echo e($property->id); ?>" class="form-control" value="<?php echo e(old('property_' . $property->id)); ?>" placeholder="<?php echo e($property->data_type); ?>">
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Lead</button>
                        <a href="<?php echo e(route('callingcrm.contacts.index')); ?>" class="btn btn-outline-secondary">Back to Contacts</a>
                    </div>
                </form>
            </div>

            <div class="card crm-soft-card p-4 mb-4">
                <h3 class="crm-panel-title">Available Campaigns</h3>
                <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="crm-mini-item">
                        <div class="fw-semibold"><?php echo e($campaign->name); ?></div>
                        <span class="badge bg-light text-dark border">Campaign</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="crm-empty">No campaigns available yet.</div>
                <?php endif; ?>
            </div>

            <div class="card crm-soft-card p-4">
                <h3 class="crm-panel-title">Custom Contact Properties</h3>
                <?php $__empty_1 = true; $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="crm-mini-item">
                        <div>
                            <div class="fw-semibold"><?php echo e($property->name); ?></div>
                            <div class="small text-muted">Custom CRM field definition</div>
                        </div>
                        <span class="crm-chip"><?php echo e($property->data_type); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="crm-empty">No custom properties configured yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/callingcrm/contacts/create.blade.php ENDPATH**/ ?>