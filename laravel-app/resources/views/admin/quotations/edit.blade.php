@extends('admin.layouts.admin')

@section('title', 'Edit Quotation Draft')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Edit Gmail Draft</h3>
        </div>
        <form action="{{ url('/admin/quotations/'.$quotation->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <label for="subject">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject', $quotation->subject) }}" required>
                </div>

                <div class="mb-3">
                    <label for="body">Body</label>
                    <textarea name="body" id="body" class="form-control" rows="12" required>{{ old('body', $quotation->body) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="attachment">Attachment</label>
                    <input type="file" name="attachment" id="attachment" class="form-control">
                    @if ($quotation->attachment)
                        <small class="text-muted d-block mt-1">
                            Current attachment:
                            <a href="{{ asset('storage/'.$quotation->attachment) }}" target="_blank" rel="noopener">{{ basename($quotation->attachment) }}</a>
                        </small>
                    @endif
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Draft</button>
                <a href="{{ url('/admin/quotations/'.$quotation->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
