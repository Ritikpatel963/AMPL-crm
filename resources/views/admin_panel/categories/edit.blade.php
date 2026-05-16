@extends('admin_panel.layout.app')
@section('title', 'Edit Category')

@section('main-content')
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-4 text-primary">Edit Category</h5>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin_panel.admin.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $category->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="slug" class="form-label fw-bold">Slug</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                           id="slug" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="Auto-generated">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Leave blank to auto-generate from name</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="parent_id" class="form-label fw-bold">Parent Category</label>
                    <select class="form-select @error('parent_id') is-invalid @enderror" 
                            id="parent_id" name="parent_id">
                        <option value="">-- None (Main Category) --</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" 
                                {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="icon" class="form-label fw-bold">Icon Class</label>
                    <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                           id="icon" name="icon" value="{{ old('icon', $category->icon) }}" 
                           placeholder="e.g., bi bi-box or fa fa-apple">
                    @error('icon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Bootstrap Icons: bi bi-*, Font Awesome: fa fa-*</small>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" 
                       {{ old('status', $category->status) ? 'checked' : '' }}>
                <label class="form-check-label" for="status">
                    <strong>Active Status</strong>
                </label>
            </div>
            <small class="text-muted">Check to make this category active/visible</small>
        </div>

        <div class="alert alert-info mt-3">
            <strong>Info:</strong>
            <ul class="mb-0 mt-2">
                <li>Subcategories: <strong>{{ $category->children()->count() }}</strong></li>
                <li>Associated Products: <strong>{{ $category->vendorProducts()->count() }}</strong></li>
            </ul>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Update Category
            </button>
            <a href="{{ route('admin_panel.admin.categories.index') }}" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </form>
</div>

@endsection
