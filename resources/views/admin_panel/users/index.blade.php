@extends('admin_panel.layout.app')
@section('title', 'Create Users')

@section('main-content')
<!-- ✅ DataTables CSS -->
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- ✅ jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}
{{-- //update model code --}}
<div class="modal fade" id="editUserModal">
  <div class="modal-dialog">
    <form id="editUserForm" method="POST" action="{{ route('admin_panel.admin.users.update') }}">
    @csrf
      <input type="hidden" name="id" id="edit_user_id">

      <div class="modal-content">
        <div class="modal-header">
          <h5>Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label>Name</label>
          <input type="text" id="edit_name" name="name" class="form-control">

          <label>Email</label>
          <input type="email" id="edit_email" name="email" class="form-control">

          <label>Username</label>
          <input type="text" id="edit_username" name="username" class="form-control">

          <label>Role</label>
        <select name="role" class="form-select mb-3" id="edit_role">
    <option value="agent">Agent</option>
    <option value="subadmin">Subadmin</option>
</select>


        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </div>

    </form>
  </div>
</div>



<div class="card shadow-sm rounded-3 border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold">Users Management</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus-lg"></i> Add User
        </button>
    </div>

    <table id="usersTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
       <tbody>
@foreach ($users as $user)
<tr>
    <td>{{ $user->id }}</td>
    <td>{{ $user->name }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ ucfirst($user->role) }}</td>
    <td>
        <span class="badge {{ $user->status ? 'bg-success' : 'bg-danger' }}">
            {{ $user->status ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td>
      <button class="btn btn-sm btn-warning"
    data-id="{{ $user->id }}"
    data-name="{{ $user->name }}"
    data-email="{{ $user->email }}"
    data-username="{{ $user->username }}"
    data-role="{{ $user->role }}"
    data-password=""
    data-bs-toggle="modal"
    data-bs-target="#editUserModal"
    onclick="editUser(this)">
    Edit
</button>

        <form action="{{ route('admin_panel.admin.users.destroy') }}" method="POST" class="d-inline">
    @csrf
    <input type="hidden" name="id" value="{{ $user->id }}">
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
        Delete
    </button>
</form>
    </td>
</tr>
@endforeach
</tbody>

    </table>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Create User (Agent/Subadmin)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
           <form action="{{ route('admin_panel.admin.users.store') }}" method="POST">
    @csrf


    <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
    <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

    <select name="role" class="form-select mb-3" required>
        <option value="">Select Role</option>
        <option value="agent">Agent</option>
        <option value="subadmin">Subadmin</option>
    </select>

    <div class="text-end">
        <button type="submit" class="btn btn-primary">Create User</button>
    </div>
</form>

            </div>
        </div>
    </div>
</div>

<!-- ✅ Initialize DataTable -->
<script>
$(document).ready(function () {
    $('#usersTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        language: {
            search: "Search:",
            lengthMenu: "Show MENU entries",
            info: "Showing START to END of TOTAL users",
        }
    });
});
</script>
<!-- user -->
<script>
function editUser(button) {

    var id = button.getAttribute('data-id');
    var name = button.getAttribute('data-name');
    var email = button.getAttribute('data-email');
    var username = button.getAttribute('data-username');
    var role = button.getAttribute('data-role');

    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_username').value = username;

    // ✅ THIS FIXES ROLE SELECT ISSUE
    document.getElementById('edit_role').value = role;
}
</script>


@endsection