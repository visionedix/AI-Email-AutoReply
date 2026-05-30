@extends('admin.layouts.admin')

@section('title', 'Edit Email Template')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Email Template</h3>
        </div>
        <form action="{{ route('admin.email-templates.update', $emailTemplate) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <label for="template_name">Template Name</label>
                    <input type="text" name="template_name" id="template_name" class="form-control" value="{{ old('template_name', $emailTemplate->template_name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="template_subject">Template Subject</label>
                    <input type="text" name="template_subject" id="template_subject" class="form-control" value="{{ old('template_subject', $emailTemplate->template_subject) }}" required>
                </div>

                <div class="mb-3">
                    <label for="template_body">Template Body</label>
                    <textarea name="template_body" id="template_body" class="form-control" rows="8" required>{{ old('template_body', $emailTemplate->template_body) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="template_other_details">Template Other Details</label>
                    <textarea name="template_other_details" id="template_other_details" class="form-control" rows="4">{{ old('template_other_details', $emailTemplate->template_other_details) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="template_document">Template Document</label>
                    <input type="file" name="template_document" id="template_document" class="form-control">
                    @if($emailTemplate->template_document)
                        <small class="form-text text-muted">
                            Current document:
                            <a href="{{ asset('storage/' . $emailTemplate->template_document) }}" target="_blank">View Document</a>
                        </small>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="keyword_search">Keyword Search</label>
                    <textarea name="keyword_search" id="keyword_search" class="form-control" rows="3" placeholder="keyword one, keyword two, keyword three">{{ old('keyword_search', $emailTemplate->keyword_search) }}</textarea>
                    <small class="form-text text-muted">Add multiple keywords separated by commas.</small>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
