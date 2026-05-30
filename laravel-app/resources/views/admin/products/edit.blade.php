@extends('admin.layouts.admin')

@section('title', 'Edit Product')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Product</h3>
        </div>
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="product_name">Product Name</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" value="{{ old('product_name', $product->product_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sku">SKU</label>
                            <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="unit">Unit</label>
                            <input type="text" name="unit" id="unit" class="form-control" value="{{ old('unit', $product->unit) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="per_unit_price">Per Unit Price</label>
                            <input type="number" name="per_unit_price" id="per_unit_price" class="form-control" value="{{ old('per_unit_price', $product->per_unit_price) }}" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="product_details">Product Details</label>
                    <textarea name="product_details" id="product_details" class="form-control" rows="4">{{ old('product_details', $product->product_details) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $product->notes) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="keyword_search">Keyword Search</label>
                    <textarea name="keyword_search" id="keyword_search" class="form-control" rows="3" placeholder="keyword one, keyword two, keyword three">{{ old('keyword_search', $product->keyword_search) }}</textarea>
                    <small class="form-text text-muted">Add multiple keywords separated by commas.</small>
                </div>

                <div class="mb-3">
                    <label for="image">Product Image</label>
                    <input type="file" name="image" id="image" class="dropify dropify-event" data-default-file="{{ $product->image ? asset('storage/' . $product->image) : '' }}">
                </div>

                <div class="row">
                    @for($i = 0; $i < 4; $i++)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="drow_image_{{ $i }}">Drow Image {{ $i + 1 }}</label>
                                <input type="file" name="drow_images[{{ $i }}]" id="drow_image_{{ $i }}" class="dropify dropify-event" data-default-file="{{ isset($product->drow_images[$i]) ? asset('storage/' . $product->drow_images[$i]) : '' }}">
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
    <script>
        $('.dropify').dropify();

        var drEvent = $('.dropify-event').dropify();
        drEvent.on('dropify.beforeClear', function(event, element) {
            return confirm('Do you really want to delete "' + element.file.name + '" ?');
        });
        drEvent.on('dropify.afterClear', function(event, element) {
            alert('File deleted');
        });
    </script>
@endpush
