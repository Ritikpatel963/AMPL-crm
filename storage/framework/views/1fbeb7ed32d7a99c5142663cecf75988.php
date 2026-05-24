<?php $__env->startSection('title', 'Customer Management'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0 rounded-3 p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-primary mb-1">Customer Management</h5>
            <small class="text-muted">Create customers, assign agents, and handle Direct Chat requests.</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
            <i class="bi bi-person-plus"></i> Add Customer
        </button>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-sm <?php echo e(blank($status) ? 'btn-primary' : 'btn-outline-primary'); ?>" href="<?php echo e(route('admin_panel.admin.customers.customer_manage')); ?>">
            All <?php echo e($counts->sum()); ?>

        </a>
        <a class="btn btn-sm <?php echo e($status === 'pending' ? 'btn-warning' : 'btn-outline-warning'); ?>" href="<?php echo e(route('admin_panel.admin.customers.customer_manage', ['status' => 'pending'])); ?>">
            Pending <?php echo e($counts['pending'] ?? 0); ?>

        </a>
        <a class="btn btn-sm <?php echo e($status === 'approved' ? 'btn-success' : 'btn-outline-success'); ?>" href="<?php echo e(route('admin_panel.admin.customers.customer_manage', ['status' => 'approved'])); ?>">
            Approved <?php echo e($counts['approved'] ?? 0); ?>

        </a>
        <a class="btn btn-sm <?php echo e($status === 'rejected' ? 'btn-danger' : 'btn-outline-danger'); ?>" href="<?php echo e(route('admin_panel.admin.customers.customer_manage', ['status' => 'rejected'])); ?>">
            Declined <?php echo e($counts['rejected'] ?? 0); ?>

        </a>
    </div>

    <table id="customerTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Approval</th>
                <th>Assigned Agent</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $assignment = $customer->assignedAgent;
                    $agent = $assignment?->agent;
                    $approval = $customer->approval_status ?? 'approved';
                    $badge = match ($approval) {
                        'pending' => 'bg-warning text-dark',
                        'rejected' => 'bg-danger',
                        default => 'bg-success',
                    };
                ?>
                <tr>
                    <td><?php echo e($customer->id); ?></td>
                    <td><?php echo e($customer->name); ?></td>
                    <td><?php echo e($customer->phone_number ?? '-'); ?></td>
                    <td><?php echo e($customer->email); ?></td>
                    <td><span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst($approval)); ?></span></td>
                    <td><?php echo e($agent ? $agent->name . ' (#' . $agent->id . ')' : 'Not assigned'); ?></td>
                    <td><?php echo e(optional($customer->created_at)->format('d M Y')); ?></td>
                    <td class="text-nowrap">
                        <button class="btn btn-primary btn-sm"
                            data-customer-id="<?php echo e($customer->id); ?>"
                            data-agent-id="<?php echo e($agent?->id); ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#assignAgentModal"
                            onclick="prepareAssign(this)">
                            <i class="bi bi-person-check"></i>
                        </button>

                        <?php if($approval !== 'approved'): ?>
                            <form action="<?php echo e(route('admin_panel.admin.customers.approve', $customer)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                        <?php endif; ?>

                        <?php if($approval !== 'rejected'): ?>
                            <form action="<?php echo e(route('admin_panel.admin.customers.reject', $customer)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-warning btn-sm">Decline</button>
                            </form>
                        <?php endif; ?>

                        <form action="<?php echo e(route('admin_panel.admin.customers.destroy', $customer)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this customer?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="createCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Create Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?php echo e(route('admin_panel.admin.customers.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
                    <input type="text" name="phone_number" class="form-control mb-3" placeholder="Phone Number">
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

                    <select name="agent_id" class="form-select mb-3">
                        <option value="">Assign agent later</option>
                        <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($agent->id); ?>"><?php echo e($agent->name); ?> - <?php echo e(ucfirst($agent->role)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <div class="text-end">
                        <button class="btn btn-success" type="submit">Create Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Assign Agent</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignAgentForm" method="POST" action="">
                    <?php echo csrf_field(); ?>
                    <label class="form-label">Select Agent</label>
                    <select name="agent_id" id="assign_agent_id" class="form-select mb-3" required>
                        <option value="">Select Agent</option>
                        <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($agent->id); ?>"><?php echo e($agent->name); ?> - <?php echo e(ucfirst($agent->role)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="text-end">
                        <button class="btn btn-success" type="submit">Assign Agent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#customerTable').DataTable({ pageLength: 10 });
});

function prepareAssign(button) {
    var customerId = button.getAttribute('data-customer-id');
    var agentId = button.getAttribute('data-agent-id') || '';
    var action = "<?php echo e(route('admin_panel.admin.customers.assign', ['customer' => '__CUSTOMER__'])); ?>";

    document.getElementById('assignAgentForm').setAttribute('action', action.replace('__CUSTOMER__', customerId));
    document.getElementById('assign_agent_id').value = agentId;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/customers/customer_manage.blade.php ENDPATH**/ ?>