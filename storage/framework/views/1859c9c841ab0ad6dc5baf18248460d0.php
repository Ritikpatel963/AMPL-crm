
<?php $__env->startSection('title', 'Create agent and subadmin'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0">
  <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
    <h5 class="fw-bold text-primary">Manage Agents & Subadmins</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">+ Add New</button>
  </div>
  <div class="card-body">
    <table id="agentTable" class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Aditya Verma</td>
          <td>aditya@domain.com</td>
          <td><span class="badge bg-info">Agent</span></td>
          <td><span class="badge bg-success">Active</span></td>
          <td>2025-10-10</td>
          <td>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add Agent / Subadmin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" placeholder="Enter full name">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" placeholder="Enter email">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" class="form-control" placeholder="Enter phone number">
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <select class="form-select">
                <option>Agent</option>
                <option>Subadmin</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" placeholder="Create password">
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirm Password</label>
              <input type="password" class="form-control" placeholder="Confirm password">
            </div>
          </div>
          <div class="text-end mt-4">
            <button class="btn btn-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>


<script>
  $(document).ready(function() {
    $('#agentTable').DataTable({
      responsive: true
    });
  });
</script>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\users\manage_agent_subadmin.blade.php ENDPATH**/ ?>