@extends('admin_panel.layout.app')
@section('title', 'Edit Product')

@section('main-content')
<div class="card p-4 shadow-sm border-0">
    <h5 class="fw-bold mb-3">Edit Product</h5>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin_panel.admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Product Name</label>
                <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>SKU</label>
                <input type="text" name="sku" class="form-control" value="{{ $product->sku }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $cat->id == $product->category_id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Subcategory</label>
                <select name="subcategory_id" class="form-control">
                    <option value="">-- Select Subcategory --</option>
                    @foreach($subcategories as $sub)
                        <option value="{{ $sub->id }}" {{ $sub->id == $product->subcategory_id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Brand</label>
                <input type="text" name="brand" class="form-control" value="{{ $product->brand }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Regular Price</label>
                <input type="number" step="0.01" name="regular_price" class="form-control" value="{{ $product->regular_price }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Sale Price</label>
                <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ $product->sale_price }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Tax (%)</label>
                <input type="number" step="0.01" name="tax" class="form-control" value="{{ $product->tax }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control" value="{{ $product->stock_quantity }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Low Stock Alert</label>
                <input type="number" name="low_stock_alert" class="form-control" value="{{ $product->low_stock_alert }}">
            </div>

            <div class="col-md-12 mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label>Video URL</label>
                <input type="text" name="video_url" class="form-control" value="{{ $product->video_url }}">
            </div>

            <div class="col-md-6 mb-3">
                <label>Upload New Images (Optional)</label>
                <input type="file" name="images[]" class="form-control" multiple>
            </div>

            <div class="col-md-12 mb-3">
                <label>Current Images:</label><br>
                @if($product->images)
                    @foreach(json_decode($product->images) as $img)
                        <img src="{{ asset($img) }}" width="60" height="60" class="rounded m-1 border">
                    @endforeach
                @endif
            </div>

            <div class="col-md-6 mb-3 form-check">
                <input type="checkbox" name="status" class="form-check-input" id="statusCheck" {{ $product->status ? 'checked' : '' }}>
                <label class="form-check-label" for="statusCheck">Active</label>
            </div>

            <div class="col-md-6 mb-3 form-check">
                <input type="checkbox" name="featured" class="form-check-input" id="featuredCheck" {{ $product->featured ? 'checked' : '' }}>
                <label class="form-check-label" for="featuredCheck">Featured</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update Product</button>
        <a href="{{ route('admin_panel.admin.products.index') }}" class="btn btn-secondary mt-3">Back</a>
    </form>
</div>
@endsection
