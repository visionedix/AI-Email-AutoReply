@extends('admin.layouts.admin')

@section('title', 'Email Viewer')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0">{{ $message['subject'] }}</h3>
                    <div class="text-muted small mt-1">
                        {{ $message['from'] ?: 'Unknown sender' }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge {{ $message['isRead'] ? 'badge-secondary' : 'badge-warning' }}">
                        {{ $message['isRead'] ? 'Read' : 'Unread' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <dl class="row mb-4">
                <dt class="col-sm-2">From</dt>
                <dd class="col-sm-10">{{ $message['from'] ?: '-' }}</dd>

                <dt class="col-sm-2">To</dt>
                <dd class="col-sm-10">{{ $message['to'] ?: '-' }}</dd>

                <dt class="col-sm-2">CC</dt>
                <dd class="col-sm-10">{{ $message['cc'] ?: '-' }}</dd>

                <dt class="col-sm-2">Date</dt>
                <dd class="col-sm-10">
                    {{ $message['date'] ? \Illuminate\Support\Carbon::parse($message['date'])->format('Y-m-d H:i') : '-' }}
                </dd>
            </dl>

            <div class="border rounded p-3 bg-light" style="white-space: normal;">
                @if (\Illuminate\Support\Str::contains($message['body'], ['<html', '<body', '<div', '<p', '<br', '<span', '<table']))
                    {!! $message['body'] !!}
                @else
                    <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">{{ $message['body'] }}</pre>
                @endif
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.mail.messages.index') }}" class="btn btn-secondary">
                Back
            </a>
            <form method="POST" action="{{ route('admin.mail.messages.read', $message['id']) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary">Mark as Read</button>
            </form>
        </div>
    </div>
@endsection
