
<?php $__env->startSection('title', 'Stock History'); ?>

<?php $__env->startSection('main-content'); ?>

<!-- DataTable CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="card shadow-sm border-0 rounded-3 p-4">

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h5 class="fw-bold mb-0">Stock History</h5>
      <small class="text-muted">Track all stock changes for auditing</small>
    </div>
  </div>

  <!-- Stock History Table -->
  <div class="table-responsive">
    <table class="table table-striped table-hover" id="stockHistoryTable">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Product Name</th>
          <th>Action</th>
          <th>Quantity Changed</th>
          <th>User</th>
          <th>Date & Time</th>
        </tr>
      </thead>

      <tbody>

        <!-- Sample Data (Replace with DB Data) -->
        <tr>
          <td>1</td>
          <td>iPhone 14</td>
          <td><span class="badge bg-primary">Added</span></td>
          <td>+10</td>
          <td>Admin</td>
          <td>2025-02-07 14:52</td>
        </tr>

        <tr>
          <td>2</td>
          <td>Samsung S23</td>
          <td><span class="badge bg-warning text-dark">Updated</span></td>
          <td>+3</td>
          <td>Manager</td>
          <td>2025-02-07 12:10</td>
        </tr>

        <tr>
          <td>3</td>
          <td>HP Laptop</td>
          <td><span class="badge bg-danger">Reduced</span></td>
          <td>-2</td>
          <td>Admin</td>
          <td>2025-02-07 09:45</td>
        </tr>

      </tbody>
    </table>
  </div>

</div>


<?php $__env->stopSection(); ?>


<!-- JS CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
  $('#stockHistoryTable').DataTable({
      pageLength: 10,
      ordering: true,
      searching: true
  });
});
</script>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\stock\stock-history.blade.php ENDPATH**/ ?>