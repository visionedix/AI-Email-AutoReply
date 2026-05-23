@extends('admin.layouts.admin')

@section('title', 'Email Templates')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Email Templates</h3>
            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary btn-sm">Create Email Template</a>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Template Name</th>
                        <th>Subject</th>
                        <th>Keyword Search</th>
                        <th>Document</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emailTemplates as $emailTemplate)
                        <tr>
                            <td>{{ $emailTemplate->id }}</td>
                            <td>{{ $emailTemplate->template_name }}</td>
                            <td>{{ $emailTemplate->template_subject }}</td>
                            <td>{{ $emailTemplate->keyword_search ?: '-' }}</td>
                            <td>
                                @if($emailTemplate->template_document)
                                    <a href="{{ asset('storage/' . $emailTemplate->template_document) }}" target="_blank">View Document</a>
                                @else
                                    <span class="text-muted">No document</span>
                                @endif
                            </td>
                            <td>{{ $emailTemplate->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.email-templates.show', $emailTemplate) }}" class="btn btn-sm btn-secondary">View</a>
                                <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.email-templates.destroy', $emailTemplate) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this email template?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No email templates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $emailTemplates->links() }}
        </div>
    </div>
@endsection
