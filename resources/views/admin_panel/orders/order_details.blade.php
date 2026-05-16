@extends('admin_panel.layout.app')

@section('title', 'Order Details')

@section('main-content')
<div class="card shadow-sm border-0 rounded-3 p-4">
    <h5 class="fw-bold text-primary mb-4">Order #1 Details</h5>

    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="fw-semibold">Customer Info</h6>
            <p><strong>Name:</strong> Adarsh Kumar</p>
            <p><strong>Email:</strong> adarsh@example.com</p>
            <p><strong>Phone:</strong> +91 9876543210</p>
        </div>
        <div class="col-md-6">
            <h6 class="fw-semibold">Order Info</h6>
            <p><strong>Total:</strong> ₹1,200.00</p>
            <p><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending</span></p>
            <p><strong>Date:</strong> 05 Nov 2025, 11:30 AM</p>
        </div>
    </div>

    <h6 class="fw-semibold mb-3">Items</h6>
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Protein Powder</td>
                <td>2</td>
                <td>₹600</td>
                <td>₹1,200</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4">
        <a href="/admin/orders" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>
@endsection
