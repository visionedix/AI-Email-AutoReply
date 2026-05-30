@extends('admin.layouts.admin')

@section('title', 'Create Email Template')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Email Template</h3>
        </div>
        <form action="{{ route('admin.email-templates.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="product_id">Product</label>
                    <select name="product_id" id="product_id" class="form-select">
                        <option value="">Select a product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->product_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="template_name">Template Name</label>
                    <input type="text" name="template_name" id="template_name" class="form-control" value="{{ old('template_name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="template_subject">Template Subject</label>
                    <input type="text" name="template_subject" id="template_subject" class="form-control" value="{{ old('template_subject') }}" required>
                </div>

                <div class="mb-3">
                    <label for="template_body">Template Body</label>
                    <textarea name="template_body" id="template_body" class="form-control" rows="8" required>{{ old('template_body') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="template_other_details">Template Other Details</label>
                    <textarea name="template_other_details" id="template_other_details" class="form-control" rows="4">{{ old('template_other_details') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="template_document">Template Document</label>
                    <input type="file" name="template_document" id="template_document" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="keyword_search">Keyword Search</label>
                    <textarea name="keyword_search" id="keyword_search" class="form-control" rows="3" placeholder="keyword one, keyword two, keyword three">{{ old('keyword_search') }}</textarea>
                    <small class="form-text text-muted">Add multiple keywords separated by commas.</small>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
@endsection
