<?php echo $__env->make('callingcrm.partials.theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('title', 'Calling CRM Contacts'); ?>

<?php $__env->startSection('main-content'); ?>
    <div class="crm-page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="eyebrow"><i class="bi bi-person-vcard"></i> Contacts</span>
                <h2 class="mt-3 mb-2 fw-bold">Lead database with source-ready organization</h2>
                <p class="text-muted mb-0">Review imported leads, track campaign assignment, and jump into creation and upload workflows.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(route('callingcrm.contacts.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Lead</a>
                <a href="<?php echo e(route('callingcrm.contacts.upload')); ?>" class="btn btn-outline-primary"><i class="bi bi-upload me-2"></i>Upload Excel</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card crm-soft-card h-100 p-4">
                <h3 class="crm-panel-title">Source Filters</h3>
                <div class="d-flex flex-wrap gap-2">
                    <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="crm-chip"><?php echo e($source); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card crm-soft-card h-100 p-4">
                <form method="GET" action="<?php echo e(route('callingcrm.contacts.index')); ?>" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Name, phone or email">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Source</label>
                        <select name="source" class="form-select">
                            <option value="">All Sources</option>
                            <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($source); ?>" <?php if(request('source') === $source): echo 'selected'; endif; ?>><?php echo e($source); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Campaign</label>
                        <select name="campaign_id" class="form-select">
                            <option value="">All Campaigns</option>
                            <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($campaign->id); ?>" <?php if((string) request('campaign_id') === (string) $campaign->id): echo 'selected'; endif; ?>><?php echo e($campaign->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?php echo e(route('callingcrm.contacts.index')); ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
                <hr class="my-4">
                <div class="crm-kpi-grid">
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Total Leads</div>
                        <div class="crm-stat-value"><?php echo e($leads->total()); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">This Page</div>
                        <div class="crm-stat-value"><?php echo e($leads->count()); ?></div>
                    </div>
                    <div class="crm-kpi-box">
                        <div class="crm-section-label">Visible Sources</div>
                        <div class="crm-stat-value"><?php echo e(count($sources)); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card crm-soft-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h3 class="crm-panel-title mb-1">Lead List</h3>
                <div class="text-muted">Structured for search, filters, and campaign-level drilldown.</div>
            </div>
            <span class="badge bg-light text-dark border">Page <?php echo e($leads->currentPage()); ?> of <?php echo e(max($leads->lastPage(), 1)); ?></span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle crm-table mb-0">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Campaign</th>
                    <th>Assigned Agent</th>
                    <th>Source</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo e($lead->name); ?></div>
                            <div class="small text-muted"><?php echo e($lead->email ?: 'No email added'); ?></div>
                            <div class="small text-muted mt-1">Stage: <?php echo e(optional($lead->stage)->name ?: 'Unassigned'); ?></div>
                        </td>
                        <td><span class="fw-semibold"><?php echo e($lead->phone); ?></span></td>
                        <td><?php echo e(optional($lead->campaign)->name ?: 'Unassigned campaign'); ?></td>
                        <td><?php echo e(optional($lead->assignedUser)->name ?: 'Not assigned'); ?></td>
                        <td><span class="crm-chip"><?php echo e($lead->source); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-muted">No leads found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <?php echo e($leads->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/callingcrm/contacts/index.blade.php ENDPATH**/ ?>