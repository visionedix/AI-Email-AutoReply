@extends('admin.layouts.admin')

@section('title', 'Create Product')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Product</h3>
        </div>
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="product_name">Product Name</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" value="{{ old('product_name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sku">SKU</label>
                            <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku') }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="unit">Unit</label>
                            <input type="text" name="unit" id="unit" class="form-control" value="{{ old('unit') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="per_unit_price">Per Unit Price</label>
                            <input type="number" name="per_unit_price" id="per_unit_price" class="form-control" value="{{ old('per_unit_price') }}" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="product_details">Product Details</label>
                    <textarea name="product_details" id="product_details" class="form-control" rows="4">{{ old('product_details') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="other_details">Other Details</label>
                    <textarea name="other_details" id="other_details" class="form-control" rows="3">{{ old('other_details') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="specification">Specification</label>
                    <textarea name="specification" id="specification" class="form-control" rows="4">{{ old('specification') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="keyword_search">Keyword Search</label>
                    <textarea name="keyword_search" id="keyword_search" class="form-control" rows="3" placeholder="keyword one, keyword two, keyword three">{{ old('keyword_search') }}</textarea>
                    <small class="form-text text-muted">Add multiple keywords separated by commas.</small>
                </div>

                <div class="mb-3">
                    <label for="image">Product Image</label>
                    <input type="file" name="image" id="image" class="dropify dropify-event" data-default-file="">
                </div>

                <div class="mb-3">
                    <label for="quotation_documents">Quotation Documents (PDF)</label>
                    <input type="file" name="quotation_documents" id="quotation_documents" class="dropify dropify-event" data-default-file="" accept="application/pdf">
                    <small class="form-text text-muted">Upload a PDF file that will be attached to quotations.</small>
                </div>

                <div class="row">
                    @for($i = 1; $i <= 3; $i++)
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="drow_image_{{ $i }}">Drow Image {{ $i }}</label>
                                <input type="file" name="drow_image_{{ $i }}" id="drow_image_{{ $i }}" class="dropify dropify-event" data-default-file="">
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create</button>
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
