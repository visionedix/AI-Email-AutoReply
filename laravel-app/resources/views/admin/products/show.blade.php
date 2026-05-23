@extends('admin.layouts.admin')

@section('title', 'Product Details')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Product Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <dl class="row">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">{{ $product->id }}</dd>

                        <dt class="col-sm-4">Product Name</dt>
                        <dd class="col-sm-8">{{ $product->product_name }}</dd>

                        <dt class="col-sm-4">SKU</dt>
                        <dd class="col-sm-8">{{ $product->sku }}</dd>

                        <dt class="col-sm-4">Unit</dt>
                        <dd class="col-sm-8">{{ $product->unit }}</dd>

                        <dt class="col-sm-4">Per Unit Price</dt>
                        <dd class="col-sm-8">{{ number_format((float) $product->per_unit_price, 2) }}</dd>

                        <dt class="col-sm-4">Product Details</dt>
                        <dd class="col-sm-8">{{ $product->product_details ?: '-' }}</dd>

                        <dt class="col-sm-4">Notes</dt>
                        <dd class="col-sm-8">{{ $product->notes ?: '-' }}</dd>

                        <dt class="col-sm-4">Keyword Search</dt>
                        <dd class="col-sm-8">{{ $product->keyword_search ?: '-' }}</dd>

                        <dt class="col-sm-4">Created At</dt>
                        <dd class="col-sm-8">{{ $product->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="col-md-4">
                    <h5>Product Image</h5>
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->product_name }}" class="img-fluid img-thumbnail mb-3">
                    @else
                        <p class="text-muted">No product image uploaded.</p>
                    @endif
                </div>
            </div>

            <h5>Drow Images</h5>
            <div class="row">
                @forelse($product->drow_images ?? [] as $image)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <img src="{{ asset('storage/' . $image) }}" alt="Drow image" class="img-fluid img-thumbnail">
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted">No drow images uploaded.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">Edit Product</a>
        </div>
    </div>
@endsection
