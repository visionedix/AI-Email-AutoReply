@extends('admin.layouts.admin')

@section('title', 'Email Inbox')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Messages</h3>

                <form method="GET" action="{{ route('admin.mail.messages.index') }}" class="d-flex align-items-center" style="gap: 0.5rem;">
                    <input type="hidden" name="limit" value="{{ $filters['limit'] }}">
                    <div class="custom-control custom-switch">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="unread_only"
                            name="unread_only"
                            value="1"
                            {{ $filters['unread_only'] ? 'checked' : '' }}
                            onchange="this.form.submit()"
                        >
                        <label class="custom-control-label" for="unread_only">Unread only</label>
                    </div>
                    <noscript>
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </noscript>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 90px;">Status</th>
                            <th>From</th>
                            <th>Subject</th>
                            <th style="width: 180px;">Date</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $message)
                            <tr class="{{ $message['isRead'] ? '' : 'table-warning' }}">
                                <td>
                                    <span class="badge {{ $message['isRead'] ? 'badge-secondary' : 'badge-warning' }}">
                                        {{ $message['isRead'] ? 'Read' : 'Unread' }}
                                    </span>
                                </td>
                                <td>{{ $message['from'] ?: 'Unknown' }}</td>
                                <td>
                                    <strong>{{ $message['subject'] }}</strong>
                                    @if (! empty($message['preview']))
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($message['preview']), 90) }}</div>
                                    @endif
                                </td>
                                <td>{{ $message['date'] ? \Illuminate\Support\Carbon::parse($message['date'])->format('Y-m-d H:i') : '-' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.mail.messages.show', $message['id']) }}" class="btn btn-sm btn-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No messages found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
