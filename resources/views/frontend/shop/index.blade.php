@extends('frontend.layout.app')

@section('content')
<div class="container py-5">
  <h3 class="mb-4 fw-bold">Shop Products</h3>

  <div class="row">
    @foreach($products as $product)
      <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="{{ asset('uploads/products/'.$product->image ?? 'noimg.png') }}" class="card-img-top" alt="">
          <div class="card-body">
            <h6 class="fw-bold">{{ $product->name }}</h6>
            <p class="text-muted mb-1">{{ $product->brand }}</p>
            <p class="mb-2"><strong>₹{{ $product->sale_price ?? $product->regular_price }}</strong></p>
            <a href="{{ route('shop.show', $product->id) }}" class="btn btn-primary btn-sm rounded-pill">View</a>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
