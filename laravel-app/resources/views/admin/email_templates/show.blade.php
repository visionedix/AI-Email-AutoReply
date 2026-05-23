@extends('admin.layouts.admin')

@section('title', 'Email Template Details')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Email Template Details</h3>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $emailTemplate->id }}</dd>

                <dt class="col-sm-3">Template Name</dt>
                <dd class="col-sm-9">{{ $emailTemplate->template_name }}</dd>

                <dt class="col-sm-3">Template Subject</dt>
                <dd class="col-sm-9">{{ $emailTemplate->template_subject }}</dd>

                <dt class="col-sm-3">Template Body</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $emailTemplate->template_body }}</dd>

                <dt class="col-sm-3">Template Other Details</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $emailTemplate->template_other_details ?: '-' }}</dd>

                <dt class="col-sm-3">Template Document</dt>
                <dd class="col-sm-9">
                    @if($emailTemplate->template_document)
                        <a href="{{ asset('storage/' . $emailTemplate->template_document) }}" target="_blank">View Document</a>
                    @else
                        -
                    @endif
                </dd>

                <dt class="col-sm-3">Keyword Search</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $emailTemplate->keyword_search ?: '-' }}</dd>

                <dt class="col-sm-3">Created At</dt>
                <dd class="col-sm-9">{{ $emailTemplate->created_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}" class="btn btn-warning">Edit Email Template</a>
        </div>
    </div>
@endsection
