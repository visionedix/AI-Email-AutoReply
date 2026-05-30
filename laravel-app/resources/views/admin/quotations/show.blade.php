@extends('admin.layouts.admin')

@section('title', 'Quotation Details')

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0">Generated Quotation</h3>
                        <div class="text-muted small mt-1">Draft ID: <code>{{ $quotation->gmail_draft_id ?? '-' }}</code></div>
                    </div>
                    <span class="badge rounded-pill {{ $quotation->status === 'draft' ? 'bg-warning text-dark' : ($quotation->status === 'sent' ? 'bg-success' : 'bg-danger') }}">
                        {{ ucfirst($quotation->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Customer Email</dt>
                        <dd class="col-sm-8">{{ $quotation->customer_email ?? '-' }}</dd>

                        <dt class="col-sm-4">Matched Product</dt>
                        <dd class="col-sm-8">{{ $quotation->product?->product_name ?? '-' }}</dd>

                        <dt class="col-sm-4">Matched Template</dt>
                        <dd class="col-sm-8">{{ $quotation->template?->template_name ?? '-' }}</dd>

                        <dt class="col-sm-4">Generated Subject</dt>
                        <dd class="col-sm-8">{{ $quotation->subject }}</dd>

                        <dt class="col-sm-4">Attachment</dt>
                        <dd class="col-sm-8">
                            @if ($quotation->attachment)
                                <a href="{{ asset('storage/'.$quotation->attachment) }}" target="_blank" rel="noopener">Download attachment</a>
                            @else
                                -
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">Generated Body</h3>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 bg-light">
                        @if (\Illuminate\Support\Str::contains($quotation->body, ['<html', '<body', '<div', '<p', '<br', '<span', '<table']))
                            {!! $quotation->body !!}
                        @else
                            <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">{{ $quotation->body }}</pre>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Original Email</h3>
                </div>
                <div class="card-body">
                    @if ($originalMessage)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Subject</dt>
                            <dd class="col-sm-8">{{ $originalMessage['subject'] }}</dd>

                            <dt class="col-sm-4">From</dt>
                            <dd class="col-sm-8">{{ $originalMessage['from'] ?: '-' }}</dd>

                            <dt class="col-sm-4">To</dt>
                            <dd class="col-sm-8">{{ $originalMessage['to'] ?: '-' }}</dd>
                        </dl>
                        <hr>
                        <div class="border rounded p-3 bg-light">
                            @if (\Illuminate\Support\Str::contains($originalMessage['body'], ['<html', '<body', '<div', '<p', '<br', '<span', '<table']))
                                {!! $originalMessage['body'] !!}
                            @else
                                <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">{{ $originalMessage['body'] }}</pre>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            The original Gmail message could not be loaded, but the quotation record is still available.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">Email Template</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8">{{ $quotation->template?->template_name ?? '-' }}</dd>
                        <dt class="col-sm-4">Subject</dt>
                        <dd class="col-sm-8">{{ $quotation->template?->template_subject ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2 flex-wrap">
        <a href="{{ url('/admin/quotations/'.$quotation->id.'/edit') }}" class="btn btn-warning">Edit Draft</a>
        <a href="{{ route('admin.mail.messages.show', $quotation->gmail_message_id) }}" class="btn btn-secondary">Open Original Email</a>
        <a href="{{ url('/admin/quotations') }}" class="btn btn-outline-secondary">Back to Quotations</a>
    </div>
@endsection
