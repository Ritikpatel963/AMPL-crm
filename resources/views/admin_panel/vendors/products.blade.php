@extends('admin_panel.layout.app')
@section('title', 'Vendor Products')

@section('main-content')
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-4 text-primary">Products for {{ $vendor->name }}</h5>

    <div class="mb-3">
        <a href="{{ route('admin_panel.admin.vendors.index') }}" class="btn btn-secondary">Back to Vendor List</a>
        <a href="{{ route('admin_panel.admin.vendors.show', $vendor->id) }}" class="btn btn-info ms-2">Vendor Profile</a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                    <i class="bi bi-funnel-fill me-2"></i>Filter Products
                </button>
            </h6>
        </div>
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form method="GET" action="{{ route('admin_panel.admin.vendors.products', $vendor->id) }}" id="filterForm">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Product</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by product name...">
                        </div>

                        <!-- Category -->
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($filterOptions['categories'] ?? [] as $category)
                                    <option value="{{ $category['id'] }}" {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                                        {{ $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="col-md-3">
                            <label for="brand_name" class="form-label">Brand</label>
                            <select class="form-select" id="brand_name" name="brand_name">
                                <option value="">All Brands</option>
                                @foreach($filterOptions['brands'] ?? [] as $brand)
                                    <option value="{{ $brand }}" {{ request('brand_name') == $brand ? 'selected' : '' }}>
                                        {{ $brand }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Unit Type -->
                        <div class="col-md-3">
                            <label for="unit_type" class="form-label">Unit Type</label>
                            <select class="form-select" id="unit_type" name="unit_type">
                                <option value="">All Unit Types</option>
                                @foreach($filterOptions['unit_types'] ?? [] as $unitType)
                                    <option value="{{ $unitType }}" {{ request('unit_type') == $unitType ? 'selected' : '' }}>
                                        {{ strtoupper($unitType) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Unit Size -->
                        <div class="col-md-3">
                            <label for="unit_size" class="form-label">Unit Size</label>
                            <select class="form-select" id="unit_size" name="unit_size">
                                <option value="">All Unit Sizes</option>
                                @foreach($filterOptions['unit_sizes'] ?? [] as $unitSize)
                                    <option value="{{ $unitSize }}" {{ request('unit_size') == $unitSize ? 'selected' : '' }}>
                                        {{ $unitSize }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Status -->
                        <div class="col-md-3">
                            <label for="stock_status" class="form-label">Stock Status</label>
                            <select class="form-select" id="stock_status" name="stock_status">
                                <option value="">All Stock Status</option>
                                @foreach($filterOptions['stock_status_options'] ?? [] as $option)
                                    <option value="{{ $option['value'] }}" {{ request('stock_status') == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Expiry Status -->
                        <div class="col-md-3">
                            <label for="expiry_status" class="form-label">Expiry Status</label>
                            <select class="form-select" id="expiry_status" name="expiry_status">
                                <option value="">All Expiry Status</option>
                                @foreach($filterOptions['expiry_status_options'] ?? [] as $option)
                                    <option value="{{ $option['value'] }}" {{ request('expiry_status') == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="col-md-3">
                            <label for="min_price" class="form-label">Min Price</label>
                            <input type="number" class="form-control" id="min_price" name="min_price" value="{{ request('min_price') }}" placeholder="0">
                        </div>
                        <div class="col-md-3">
                            <label for="max_price" class="form-label">Max Price</label>
                            <input type="number" class="form-control" id="max_price" name="max_price" value="{{ request('max_price') }}" placeholder="10000">
                        </div>

                        <!-- Quantity Range -->
                        <div class="col-md-3">
                            <label for="min_quantity" class="form-label">Min Quantity</label>
                            <input type="number" class="form-control" id="min_quantity" name="min_quantity" value="{{ request('min_quantity') }}" placeholder="0">
                        </div>
                        <div class="col-md-3">
                            <label for="max_quantity" class="form-label">Max Quantity</label>
                            <input type="number" class="form-control" id="max_quantity" name="max_quantity" value="{{ request('max_quantity') }}" placeholder="1000">
                        </div>

                        <!-- Sort Options -->
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                @foreach($filterOptions['sort_options'] ?? [] as $option)
                                    <option value="{{ $option['value'] }}" {{ request('sort_by', 'created_at') == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ request('sort_order', 'desc') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin_panel.admin.vendors.products', $vendor->id) }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <table id="vendorProductsTable" class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Expiry</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $i => $product)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    @if(!empty($product->images[0]))
                        <img src="{{ asset($product->images[0]) }}" alt="product" class="rounded" style="width:80px; height:80px; object-fit:cover; border: 2px solid #dee2e6;" />
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width:80px; height:80px; border: 2px solid #dee2e6;">
                            <i class="bi bi-image text-muted fs-4"></i>
                        </div>
                    @endif
                </td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->category_name }}</td>
                <td>{{ $product->brand_name ?? '—' }}</td>
                <td>
                    @if($product->unit_size && $product->unit_type)
                        {{ $product->unit_size }} {{ strtoupper($product->unit_type) }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    <span class="badge {{ $product->quantity > 10 ? 'bg-success' : ($product->quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                        {{ $product->formatted_quantity }}
                    </span>
                </td>
                <td>{{ $product->product_rate ? '₹' . number_format($product->product_rate, 2) : '—' }}</td>
                <td>
                    @if($product->product_expiry)
                        @php
                            $daysUntilExpiry = now()->diffInDays($product->product_expiry, false);
                        @endphp
                        @if($daysUntilExpiry < 0)
                            <span class="badge bg-danger">Expired</span>
                        @elseif($daysUntilExpiry <= 7)
                            <span class="badge bg-warning">{{ $product->product_expiry_formatted }}</span>
                        @elseif($daysUntilExpiry <= 30)
                            <span class="badge bg-info">{{ $product->product_expiry_formatted }}</span>
                        @else
                            <span class="text-muted">{{ $product->product_expiry_formatted }}</span>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-search fs-1 text-muted mb-2"></i>
                    <br>No products found matching your filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit filter form on select change for better UX
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });

    // Add loading state to filter form
    $('#filterForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Filtering...');
    });

    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#vendorProductsTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            columnDefs: [
                { orderable: false, targets: [1] } // Disable sorting on image column
            ],
            language: {
                search: "Search products:",
                lengthMenu: "Show _MENU_ products per page",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "No products available",
                infoFiltered: "(filtered from _MAX_ total products)",
                zeroRecords: "No matching products found",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    } else {
        console.log('DataTables not available');
    }
});
</script>
@endpush