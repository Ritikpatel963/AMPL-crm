
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

    <!-- Assignment Rules Section -->
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card crm-soft-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="crm-panel-title mb-1">Condition-Based Assignment Rules</h3>
                        <div class="text-muted">If a lead matches these conditions, they will be assigned to the specific agent instead of round-robin.</div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle crm-table mb-0">
                        <thead>
                            <tr>
                                <th>Priority</th>
                                <th>Condition</th>
                                <th>Value</th>
                                <th>Assign To</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $campaign->assignmentRules()->orderBy('sort_order')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($rule->sort_order); ?></td>
                                    <td><span class="badge bg-light text-dark border">Field: <?php echo e($rule->condition_field); ?></span> <?php echo e($rule->condition_operator); ?></td>
                                    <td><span class="fw-semibold"><?php echo e($rule->condition_value); ?></span></td>
                                    <td><?php echo e(optional($rule->user)->name ?? 'Unknown Agent'); ?></td>
                                    <td>
                                        <form action="<?php echo e(route('callingcrm.campaigns.rules.destroy', [$campaign->id, $rule->id])); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this rule?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-muted text-center py-3">No condition rules found. Leads are currently distributed equally (Round Robin).</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add New Rule Form -->
                <div class="card bg-light border-0 p-3 mt-2">
                    <h5 class="fw-bold mb-3 fs-6"><i class="bi bi-plus-circle me-2"></i>Add New Rule</h5>
                    <form action="<?php echo e(route('callingcrm.campaigns.rules.store', $campaign->id)); ?>" method="POST" class="row g-3 align-items-end">
                        <?php echo csrf_field(); ?>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Field</label>
                            <select name="condition_field" class="form-select form-select-sm" required>
                                <option value="source">Source</option>
                                <option value="tags">Tags</option>
                                <!-- Can add more custom fields here -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Operator</label>
                            <select name="condition_operator" class="form-select form-select-sm" required>
                                <option value="equals">Equals</option>
                                <option value="contains">Contains</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Target Value</label>
                            <input type="text" name="condition_value" class="form-control form-control-sm" placeholder="e.g. Facebook, Mumbai" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Assign To Agent</label>
                            <select name="user_id" class="form-select form-select-sm" required>
                                <?php $__currentLoopData = $campaign->agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($agent->id); ?>"><?php echo e($agent->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-semibold">Order</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Pipeline Lead List -->
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card crm-soft-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="crm-panel-title mb-1">Campaign Leads</h3>
                        <div class="text-muted">Simple pipeline list view instead of Kanban.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle crm-table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Stage</th>
                                <th>Assigned Agent</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $campaign->leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($lead->name); ?></div>
                                        <div class="small text-muted"><?php echo e($lead->email ?: 'No email'); ?></div>
                                    </td>
                                    <td><span class="fw-semibold"><?php echo e($lead->phone); ?></span></td>
                                    <td>
                                        <span class="badge" style="background-color: <?php echo e(optional($lead->stage)->color ?? '#6c757d'); ?>">
                                            <?php echo e(optional($lead->stage)->name ?: 'Unassigned'); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e(optional($lead->assignedUser)->name ?: 'Not assigned'); ?></td>
                                    <td><span class="crm-chip"><?php echo e($lead->source); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-muted text-center py-4">No leads found in this campaign.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/callingcrm/pipeline/show.blade.php ENDPATH**/ ?>