@extends('admin_panel.layout.app')
@section('title', 'Customer Profile')
@section('main-content')

<div class="card shadow-sm rounded-3 border-0 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold">Customer Profile</h5>
    <a href="" class="btn btn-outline-primary btn-sm">← Back to List</a>
  </div>
{{-- {{ route('admin.customers') }} --}}
  <div class="row">
    <div class="col-md-4 text-center">
      <img src="" class="rounded-circle mb-3 shadow-sm" width="150" height="150" alt="">
      <h6 class="fw-bold">Adarsh Kumar</h6>
      <p class="text-muted">+91 9876543210</p>
      <span class="badge bg-success">KYC Approved</span>
    </div>
    <div class="col-md-8">
      <h6 class="fw-bold mb-3">Customer Details</h6>
      <table class="table table-bordered">
        <tr><th>Email</th><td>adarsh@example.com</td></tr>
        <tr><th>Assigned Agent</th><td>Agent Rahul</td></tr>
        <tr><th>Registration Date</th><td>2025-10-20</td></tr>
        <tr><th>Address</th><td>Lucknow, India</td></tr>
      </table>
      <h6 class="fw-bold mt-4">KYC Documents</h6>
      <div class="row">
        <div class="col-md-6 mb-3">
          <div class="border p-2 rounded">
            <h6 class="text-muted">Aadhar Front</h6>
            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded shadow-sm">
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="border p-2 rounded">
            <h6 class="text-muted">Aadhar Back</h6>
            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded shadow-sm">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
