@extends('admin_panel.layout.app')
@section('title', 'Vendor Product Categories')

@section('main-content')
<div class="card shadow-sm rounded-3 border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-primary m-0">Vendor Product Categories</h5>
        <a href="{{ route('admin_panel.admin.vendor_categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Category
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Parent Category</th>
                    <th>Icon</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $i => $category)
                <tr>
                    <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $i + 1 }}</td>
                    <td>
                        @if($category->image)
                            <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" 
                                 style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="width:50px; height:50px; border-radius:4px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $category->name }}</strong>
                        @if($category->children()->count() > 0)
                            <br><small class="text-muted">{{ $category->children()->count() }} subcategories</small>
                        @endif
                    </td>
                    <td><code class="text-primary">{{ $category->slug }}</code></td>
                    <td>
                        @if($category->parent)
                            <span class="badge bg-info">{{ $category->parent->name }}</span>
                        @else
                            <span class="badge bg-secondary">Main</span>
                        @endif
                    </td>
                    <td>
                        @if($category->icon)
                            <i class="{{ $category->icon }} fs-5"></i>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $category->sort_order }}</td>
                    <td>
                        @if($category->status)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">{{ $category->vendorProducts()->count() }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin_panel.admin.vendor_categories.edit', $category->id) }}" 
                           class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('admin_panel.admin.vendor_categories.destroy', $category->id) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Delete {{ $category->name }}?')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        No vendor categories found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} categories
        </div>
        {{ $categories->links('pagination::bootstrap-4') }}
    </div>
</div>

@endsection
