@extends('admin_panel.layout.app')
@section('title', 'CRM Users')

@section('main-content')
<div class="modal fade" id="editUserModal">
  <div class="modal-dialog">
    <form id="editUserForm" method="POST" action="{{ route('admin_panel.admin.users.update') }}">
      @csrf
      <input type="hidden" name="id" id="edit_user_id">

      <div class="modal-content">
        <div class="modal-header">
          <h5>Edit CRM User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label>Name</label>
          <input type="text" id="edit_name" name="name" class="form-control mb-3" required>

          <label>Email</label>
          <input type="email" id="edit_email" name="email" class="form-control mb-3" required>

          <label>Phone Number</label>
          <input type="text" id="edit_phone_number" name="phone_number" class="form-control mb-3" required>

          <label>Password</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Leave blank to keep current password">

          <label>Role</label>
          <select name="role" class="form-select mb-3" id="edit_role" required>
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
    <h5 class="fw-bold">Calling CRM Users</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-plus-lg"></i> Add User
    </button>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      {{ $errors->first() }}
    </div>
  @endif

  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  <table id="usersTable" class="display" style="width:100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
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
          <td>{{ $user->phone_number }}</td>
          <td>{{ ucfirst($user->role) }}</td>
          <td>
            <span class="badge {{ ($user->crm_status ?? 'active') === 'active' ? 'bg-success' : 'bg-danger' }}">
              {{ ucfirst($user->crm_status ?? 'active') }}
            </span>
          </td>
          <td>
            <button class="btn btn-sm btn-warning"
              data-id="{{ $user->id }}"
              data-name="{{ $user->name }}"
              data-email="{{ $user->email }}"
              data-phone-number="{{ $user->phone_number }}"
              data-role="{{ $user->role }}"
              data-bs-toggle="modal"
              data-bs-target="#editUserModal"
              onclick="editUser(this)">
              Edit
            </button>

            <form action="{{ route('admin_panel.admin.users.destroy') }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="id" value="{{ $user->id }}">
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this CRM user?')">
                Delete
              </button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-sm rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Create Calling CRM User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('admin_panel.admin.users.store') }}" method="POST">
          @csrf

          <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
          <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
          <input type="text" name="phone_number" class="form-control mb-3" placeholder="Phone Number" required>
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

<script>
function editUser(button) {
  document.getElementById('edit_user_id').value = button.getAttribute('data-id');
  document.getElementById('edit_name').value = button.getAttribute('data-name');
  document.getElementById('edit_email').value = button.getAttribute('data-email');
  document.getElementById('edit_phone_number').value = button.getAttribute('data-phone-number');
  document.getElementById('edit_role').value = button.getAttribute('data-role');
}
</script>
@endsection
