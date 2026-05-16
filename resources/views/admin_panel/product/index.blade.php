@extends('admin_panel.layout.app')
@section('title', 'Product List')

@section('main-content')
<div class="card shadow-sm border-0 rounded-3 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold">All Products</h5>
  </div>

  <!-- Products Table -->
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle admin-prod-table">
      <thead class="table-light">
        <tr>
          <th><input type="checkbox" id="admin-prod-select-all"></th>
          <th>Product</th>
          <th>SKU / Code</th>
          <th>Category</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Status</th>
          <th>Date Added</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
        @php
          $images = json_decode($product->images, true);
          $firstImage = $images[0] ?? null;
        @endphp
        <tr>
          <td><input type="checkbox" class="admin-prod-checkbox"></td>

          <!-- Product Image + Name -->
          <td>
            <div class="d-flex align-items-center">
              @if($firstImage && file_exists(public_path($firstImage)))
                <img src="{{ asset($firstImage) }}" alt="{{ $product->name }}" class="me-2 rounded" width="50" height="50" style="object-fit:cover;">
              @else
                <img src="{{ asset('uploads/products/default.png') }}" alt="No image" class="me-2 rounded" width="50" height="50" style="object-fit:cover;">
              @endif
              <span>{{ $product->name }}</span>
            </div>
          </td>

          <td>{{ $product->sku }}</td>

          <td>
            {{ $product->category?->name }}
            @if($product->subcategory)
              <br><small class="text-muted">→ {{ $product->subcategory->name }}</small>
            @endif
          </td>

          <td>₹{{ $product->sale_price ?? $product->regular_price }}</td>

          <td>{{ $product->stock_quantity }}</td>

          <td>
            @if($product->status)
              <span class="badge bg-success">Active</span>
            @else
              <span class="badge bg-danger">Inactive</span>
            @endif
          </td>

          <td>{{ $product->created_at->format('Y-m-d') }}</td>

          <td class="text-center">
            <a href="#" class="btn btn-info btn-sm">View</a>
            <a href="{{ route('admin_panel.admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm">
    Edit
  </a>
             <form action="{{ route('admin_panel.admin.products.destroy', $product->id) }}" method="POST" class="d-inline"
        onsubmit="return confirm('Are you sure you want to delete this product?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
  </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center text-muted">No products found</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

{{-- Scripts --}}
{{-- @push('scripts') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function(){
  $('.admin-prod-table').DataTable({
    pageLength: 10,
    responsive: true
  });

  // Select/Deselect All
  $('#admin-prod-select-all').on('click', function(){
    $('.admin-prod-checkbox').prop('checked', this.checked);
  });
});
</script>
{{-- @endpush --}}
