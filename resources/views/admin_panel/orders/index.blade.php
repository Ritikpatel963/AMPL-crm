@extends('admin_panel.layout.app')
@section('title', 'My Orders')

@section('main-content')
<div class="card shadow-sm border-0 rounded-3 p-4">
    <h5 class="fw-bold text-primary mb-3">My Orders</h5>
    <small class="text-muted mb-3 d-block">View, update or delete your orders</small>

    <table id="ordersTable" class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Status</th>
                <th>Placed On</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
    @forelse($orders as $index => $order)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>#ORD{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
            <td>
                @if(isset($order->product_name))
                    {{ $order->product_name }}
                @elseif(isset($order->items) && count($order->items))
                    {{ $order->items[0]->product->name }}
                @else
                    —
                @endif
            </td>
            <td>{{ $order->quantity ?? '-' }}</td>
            <td>₹{{ number_format($order->total_amount, 2) }}</td>
            <td>
                @if($order->status == 'Pending')
                    <span class="badge bg-warning text-dark">{{ $order->status }}</span>
                @elseif($order->status == 'Completed')
                    <span class="badge bg-success">{{ $order->status }}</span>
                @elseif($order->status == 'Cancelled')
                    <span class="badge bg-danger">{{ $order->status }}</span>
                @else
                    <span class="badge bg-secondary">{{ $order->status }}</span>
                @endif
            </td>
            <td>{{ $order->created_at->format('Y-m-d') }}</td>
            <td class="text-center">
                <button class="btn btn-outline-primary btn-sm edit-btn" data-id="{{ $order->id }}" data-status="{{ $order->status }}">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $order->id }}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center text-muted">No orders found.</td>
        </tr>
    @endforelse
</tbody>

    </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editOrderModalLabel">Edit Order Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    <input type="hidden" id="editOrderId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Order Status</label>
                        <select id="orderStatus" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    // Initialize DataTable
    $('#ordersTable').DataTable({
        pageLength: 5,
        lengthChange: true,
        language: { searchPlaceholder: "Search orders...", search: "" }
    });

    // Edit order status
    $('.edit-btn').on('click', function() {
        const orderId = $(this).data('id');
        const status = $(this).data('status');

        $('#editOrderId').val(orderId);
        $('#orderStatus').val(status);

        const modal = new bootstrap.Modal(document.getElementById('editOrderModal'));
        modal.show();
    });

    $('#editOrderForm').on('submit', function(e) {
    e.preventDefault();

    const id = $('#editOrderId').val();
    const status = $('#orderStatus').val();

    $.ajax({
        url: `admin_panel/admin/orders/${id}/status`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: status
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#editOrderModal').modal('hide');
                location.reload(); // reload to show updated status
            }
        },
        error: function(xhr) {
            alert('Something went wrong while updating order status.');
            console.error(xhr.responseText);
        }
    });
});


    // Delete order
    $('.delete-btn').on('click', function() {
        const orderId = $(this).data('id');
        if(confirm('Are you sure you want to delete this order?')) {
            // Backend delete AJAX call here
            alert('Order #' + orderId + ' deleted successfully.');
        }
    });

});
</script>
@endsection
