@extends('admin_panel.layout.app')
@section('title', 'stock list')

@section('main-content')

<!-- Bootstrap + DataTable CSS -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="card shadow-sm border-0 rounded-3 p-4">

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h5 class="fw-bold mb-0">Stock Management</h5>
      <small class="text-muted">Check stock levels and availability</small>
    </div>
  </div>

  <!-- Stock Table -->
  <div class="table-responsive">
    <table class="table table-striped table-hover" id="adminStockTable">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Product</th>
          <th>Category</th>
          <th>Current Stock</th>
          <th>Minimum Stock</th>
          <th>Status</th>
          
        </tr>
      </thead>

      <tbody>
        <tr>
          <td>1</td>
          <td>iPhone 14</td>
          <td>Mobiles</td>
          <td>12</td>
          <td>5</td>
          <td><span class="badge bg-success">In Stock</span></td>
         
        </tr>

        <tr>
          <td>2</td>
          <td>Samsung S23</td>
          <td>Mobiles</td>
          <td>4</td>
          <td>6</td>
          <td><span class="badge bg-warning text-dark">Low Stock</span></td>

        </tr>

        <tr>
          <td>3</td>
          <td>HP Laptop</td>
          <td>Computers</td>
          <td>0</td>
          <td>3</td>
          <td><span class="badge bg-danger">Out of Stock</span></td>

        </tr>

      </tbody>
    </table>
  </div>

</div>


<!-- View Modal -->
<div class="modal fade" id="stockViewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Stock Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <table class="table table-borderless mb-0">
          <tr><th>Product:</th><td id="m_product"></td></tr>
          <tr><th>Category:</th><td id="m_category"></td></tr>
          <tr><th>Current Stock:</th><td id="m_qty"></td></tr>
          <tr><th>Minimum Stock:</th><td id="m_min"></td></tr>
          <tr><th>Status:</th><td id="m_status"></td></tr>
        </table>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

@endsection


<!-- JS CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    // ✅ DataTable
    $('#adminStockTable').DataTable({
        pageLength: 5,
        lengthChange: true,
        ordering: true,
        searching: true
    });

    // ✅ Open Modal and Fill Data
    $('.viewStockBtn').on('click', function () {
        $('#m_product').text($(this).data('product'));
        $('#m_category').text($(this).data('category'));
        $('#m_qty').text($(this).data('qty'));
        $('#m_min').text($(this).data('min'));
        $('#m_status').text($(this).data('status'));

        new bootstrap.Modal(document.getElementById('stockViewModal')).show();
    });

});
</script>
