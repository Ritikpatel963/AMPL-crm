@extends('admin_panel.layout.app')
@section('title', 'Shipping info')
@section('main-content')


<div class="modal fade" id="editShippingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <form id="updateForm">
        @csrf
        <input type="hidden" id="edit_id">

        <div class="modal-body">
          <input type="text" name="method_name" id="edit_method_name" class="form-control mb-3" required>
          <input type="number" name="cost" id="edit_cost" class="form-control mb-3" required>
          <input type="text" name="delivery_time" id="edit_delivery_time" class="form-control mb-3" required>

          <select name="status" id="edit_status" class="form-control mb-3" required>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>

    </div>
  </div>
</div>



<div class="card shadow-sm rounded-3 border-0 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold">Shipping Management</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShippingModal">
      <i class="bi bi-plus-lg"></i> Add Shipping Method
    </button>
  </div>

  <table class="table table-striped table-hover" id="shippingTable">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Method Name</th>
        <th>Cost</th>
        <th>Delivery Time</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
   <tbody>
@foreach ($shipping_methods as $ship)
  <tr>
    <td>{{ $ship->id }}</td>
    <td>{{ $ship->method_name }}</td>
    <td>${{ $ship->cost }}</td>
    <td>{{ $ship->delivery_time }}</td>
    <td>
      <span class="badge {{ $ship->status == 'Active' ? 'bg-success' : 'bg-secondary' }}">
        {{ $ship->status }}
      </span>
    </td>
    <td>
       <button class="btn btn-warning btn-sm edit-btn"
    data-id="{{ $ship->id }}"
    data-method="{{ $ship->method_name }}"
    data-cost="{{ $ship->cost }}"
    data-delivery="{{ $ship->delivery_time }}"
    data-status="{{ $ship->status }}">
    Edit
</button>
        <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $ship->id }}">
    Delete
</button>
    </td>
  </tr>
@endforeach
</tbody>

  </table>
</div>
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif



<!-- Add Shipping Modal -->
<div class="modal fade" id="addShippingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-sm rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add Shipping Method</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
      <form action="{{ route('admin_panel.admin.shipping.store') }}" method="POST">
    @csrf


  <input type="text" name="method_name" class="form-control mb-3" placeholder="Method Name" required>

  <input type="number" name="cost" class="form-control mb-3" placeholder="Cost" required>

  <input type="text" name="delivery_time" class="form-control mb-3" placeholder="Delivery Time" required>

  <select name="status" class="form-control mb-3" required>
      <option value="Active">Active</option>
      <option value="Inactive">Inactive</option>
  </select>

  <div class="text-end">
    <button type="submit" class="btn btn-primary">Add</button>
  </div>
</form>

      </div>
    </div>
  </div>
</div>

{{-- @push('scripts') --}}
<script>
$(document).ready(function() {
  $('#shippingTable').DataTable({ pageLength: 5 });
});




</script>
<!-- add model -->
<script>
$(document).ready(function () {

    // Handle Edit Button Click
    $('.edit-btn').on('click', function () {
        let id = $(this).data('id');
        let method = $(this).data('method');
        let cost = $(this).data('cost');
        let delivery = $(this).data('delivery');
        let status = $(this).data('status');

        $('#edit_id').val(id);
        $('#edit_method_name').val(method);
        $('#edit_cost').val(cost);
        $('#edit_delivery_time').val(delivery);
        $('#edit_status').val(status);
        $('#editShippingModal').modal('show');
    });

    // Update Ajax
    $('#updateForm').submit(function(e) {
        e.preventDefault();
        let id = $('#edit_id').val();

        $.ajax({
            url: "{{ url('admin_panel/admin/shipping/update') }}/" + id,
            type: "POST",
            data: $('#updateForm').serialize(),
            success: function () {
                location.reload();
            }
        });
    });

// Delete
    $('.delete-btn').on('click', function () {
        if (!confirm("Are you sure you want to delete this shipping method?")) return;
        let id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin_panel/admin/shipping/delete') }}/" + id,
            type: "DELETE",
            data: {_token: "{{ csrf_token() }}"},
            success: function () {
                location.reload();
            }
        });
    });

});
</script>



{{-- @endpush --}}
@endsection






