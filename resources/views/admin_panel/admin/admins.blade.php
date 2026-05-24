@extends('admin_panel.layout.app')

@section('title', 'Manage Admins')

@section('main-content')
<div class="card shadow-sm rounded-3 border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Admin Management</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-plus-lg"></i> Add Admin
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Type</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                    <tr>
                        <td>{{ $admin->name }}</td>
                        <td>{{ $admin->phone }}</td>
                        <td>
                            <span class="badge {{ $admin->isMainAdmin() ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $admin->isMainAdmin() ? 'Main Admin' : 'Admin' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editAdminModal"
                                data-id="{{ $admin->id }}"
                                data-name="{{ $admin->name }}"
                                data-phone="{{ $admin->phone }}"
                                data-is-main="{{ $admin->isMainAdmin() ? 1 : 0 }}"
                                onclick="editAdmin(this)"
                            >
                                Edit
                            </button>

                            <form action="{{ route('admin_panel.admin.admins.destroy', $admin) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="btn btn-sm btn-danger"
                                    {{ $admin->isMainAdmin() ? 'disabled' : '' }}
                                    onclick="return confirm('Delete this admin?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Create Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin_panel.admin.admins.store') }}" method="POST">
                    @csrf
                    <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
                    <input type="tel" name="phone" class="form-control mb-3" placeholder="Phone Number" required>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editAdminForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content shadow-sm rounded-3">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="edit_admin_name" name="name" class="form-control mb-3" placeholder="Full Name" required>
                    <input type="tel" id="edit_admin_phone" name="phone" class="form-control mb-2" placeholder="Phone Number" required>
                    <small id="main_admin_note" class="text-muted d-none">Main admin phone number cannot be changed.</small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update Admin</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function editAdmin(button) {
    const id = button.getAttribute('data-id');
    const isMain = button.getAttribute('data-is-main') === '1';
    const phoneInput = document.getElementById('edit_admin_phone');

    document.getElementById('editAdminForm').action = "{{ url('/admin_panel/admin/admins') }}/" + id;
    document.getElementById('edit_admin_name').value = button.getAttribute('data-name');
    phoneInput.value = button.getAttribute('data-phone');
    phoneInput.readOnly = isMain;
    document.getElementById('main_admin_note').classList.toggle('d-none', !isMain);
}
</script>
@endsection
