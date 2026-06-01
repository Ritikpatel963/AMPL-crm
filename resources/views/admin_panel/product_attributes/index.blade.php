@extends('admin_panel.layout.app')
@section('title', 'Product Attributes')

@section('main-content')
<div class="card shadow-sm border-0 rounded-3 p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-primary mb-1">Product Attributes</h5>
            <small class="text-muted">Create attributes once and use them on every product.</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin_panel.admin.product_attributes.store') }}" method="POST" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-4">
            <label class="form-label fw-semibold">Attribute Name</label>
            <input type="text" name="name" class="form-control" placeholder="Size, Color, Flavour" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Values</label>
            <textarea name="values" class="form-control" rows="2" placeholder="Small, Medium, Large or Red, Blue, Green" required></textarea>
            <small class="text-muted">Separate values with commas or new lines.</small>
        </div>
        <div class="col-md-2">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="status" checked>
                <label class="form-check-label">Active</label>
            </div>
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-plus-circle me-1"></i>Create
            </button>
        </div>
    </form>
</div>

<div class="card shadow-sm border-0 rounded-3 p-4">
    <h5 class="fw-bold text-primary mb-3">Attribute List</h5>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Values</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attributes as $attribute)
                    <tr>
                        <td><strong>{{ $attribute->name }}</strong></td>
                        <td>
                            @foreach($attribute->values ?? [] as $value)
                                <span class="badge bg-success-subtle text-success me-1 mb-1">{{ $value }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge {{ $attribute->status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $attribute->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#editAttribute{{ $attribute->id }}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form action="{{ route('admin_panel.admin.product_attributes.destroy', $attribute) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete {{ $attribute->name }}?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <tr class="collapse" id="editAttribute{{ $attribute->id }}">
                        <td colspan="4">
                            <form action="{{ route('admin_panel.admin.product_attributes.update', $attribute) }}" method="POST" class="row g-3 align-items-end bg-light rounded p-3">
                                @csrf
                                @method('PUT')
                                <div class="col-md-4">
                                    <label class="form-label">Attribute Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $attribute->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Values</label>
                                    <textarea name="values" class="form-control" rows="2" required>{{ implode(', ', $attribute->values ?? []) }}</textarea>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="status" {{ $attribute->status ? 'checked' : '' }}>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Update</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No attributes created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $attributes->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
