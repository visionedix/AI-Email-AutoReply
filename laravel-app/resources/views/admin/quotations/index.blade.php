@extends('admin.layouts.admin')

@section('title', 'Quotations')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Quotation History</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer Email</th>
                            <th>Product</th>
                            <th>Draft ID</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotations as $quotation)
                            <tr>
                                <td>{{ $quotation->id }}</td>
                                <td>{{ $quotation->customer_email ?? '-' }}</td>
                                <td>{{ $quotation->product?->product_name ?? '-' }}</td>
                                <td><code>{{ $quotation->gmail_draft_id ?? '-' }}</code></td>
                                <td>
                                    <span class="badge rounded-pill {{ $quotation->status === 'draft' ? 'bg-warning text-dark' : ($quotation->status === 'sent' ? 'bg-success' : 'bg-danger') }}">
                                        {{ ucfirst($quotation->status) }}
                                    </span>
                                </td>
                                <td>{{ $quotation->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ url('/admin/quotations/'.$quotation->id) }}" class="btn btn-primary">View</a>
                                        <a href="{{ url('/admin/quotations/'.$quotation->id.'/edit') }}" class="btn btn-warning">Edit Draft</a>
                                        <a href="{{ route('admin.mail.messages.show', $quotation->gmail_message_id) }}" class="btn btn-secondary">Open Email</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No quotations have been generated yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($quotations->hasPages())
            <div class="card-footer">
                {{ $quotations->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
