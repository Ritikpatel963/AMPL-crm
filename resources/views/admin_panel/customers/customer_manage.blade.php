@extends('admin_panel.layout.app')
@section('title', 'Customer Management')

@section('main-content')
<div class="card shadow-sm border-0 rounded-3 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary">Customer Management</h5>
    </div>

    <table id="customerTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>KYC Status</th>
                <th>Assigned Agent</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Adarsh Kumar</td>
                <td>+91 9876543210</td>
                <td>adarsh@example.com</td>
                <td><span class="badge bg-success">Approved</span></td>
                <td>Agent Rahul</td>
                <td>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewCustomerModal"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignAgentModal"><i class="bi bi-person-plus"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Assign Agent Modal -->
<div class="modal fade" id="assignAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Assign Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Select Agent</label>
                        <select class="form-select">
                            <option>Agent Rahul</option>
                            <option>Agent Ankit</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-success">Assign Agent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Customer Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold mb-3">Profile</h6>
                <p><b>Name:</b> Adarsh Kumar</p>
                <p><b>Email:</b> adarsh@example.com</p>
                <p><b>Phone:</b> +91 9876543210</p>
                <p><b>KYC Status:</b> Approved</p>
                <p><b>Assigned Agent:</b> Agent Rahul</p>
                <h6 class="fw-bold mt-4">KYC Documents</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <img src="https://via.placeholder.com/300x200" class="img-fluid rounded shadow-sm">
                        <p class="text-center mt-1">Aadhar Front</p>
                    </div>
                    <div class="col-md-6">
                        <img src="https://via.placeholder.com/300x200" class="img-fluid rounded shadow-sm">
                        <p class="text-center mt-1">Aadhar Back</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- @push('scripts') --}}
<script>
$(document).ready(function() {
    $('#customerTable').DataTable({ pageLength: 5 });
});
</script>
{{-- @endpush --}}
@endsection
