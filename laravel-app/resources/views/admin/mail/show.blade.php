@extends('admin.layouts.admin')

@section('title', 'Email Viewer')

@section('content')

@php

$body = $message['body'] ?? '';

$isHtml = preg_match('/<(html|body|table|div|p|span|img|a)[^>]*>/i', $body);

preg_match('/https?:\/\/[^\s<>"\']+/i', strip_tags($body), $urlMatch);

$webViewUrl = $urlMatch[0] ?? null;

@endphp

<div class="container-fluid">

    <div class="card shadow-sm">

        {{-- HEADER --}}
        <div class="card-header bg-white">

            <div class="d-flex justify-content-between">

                <div>

                    <h3 class="mb-1 fw-bold">
                        {{ $message['subject'] ?: '(No Subject)' }}
                    </h3>

                    <div class="text-muted">
                        {{ $message['from'] ?: 'Unknown Sender' }}
                    </div>

                </div>

                <div class="text-end">

                    @if($message['isRead'])
                        <span class="badge bg-success">
                            Read
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            Unread
                        </span>
                    @endif

                    <div class="small text-muted mt-1">

                        @if(!empty($message['date']))
                            {{ \Carbon\Carbon::parse($message['date'])->format('d M Y h:i A') }}
                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- EMAIL DETAILS --}}
        <div class="card-body border-bottom">

            <div class="row">

                <div class="col-md-2 fw-bold">
                    From
                </div>

                <div class="col-md-10">
                    {{ $message['from'] ?: '-' }}
                </div>

            </div>

            <div class="row mt-2">

                <div class="col-md-2 fw-bold">
                    To
                </div>

                <div class="col-md-10">
                    {{ $message['to'] ?: '-' }}
                </div>

            </div>

            @if(!empty($message['cc']))

                <div class="row mt-2">

                    <div class="col-md-2 fw-bold">
                        CC
                    </div>

                    <div class="col-md-10">
                        {{ $message['cc'] }}
                    </div>

                </div>

            @endif

        </div>

        {{-- QUOTATION EXISTS --}}
        @if($quotation)

            <div class="alert alert-success rounded-0 mb-0">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        Quotation already generated.

                        <a href="{{ url('/admin/quotations/'.$quotation->id) }}">
                            View Quotation
                        </a>
                    </div>

                    <span class="badge bg-success">
                        {{ ucfirst($quotation->status) }}
                    </span>

                </div>

            </div>

        @endif

        {{-- VIEW IN BROWSER BUTTON --}}
        @if(!$isHtml && $webViewUrl)

            <div class="card-body border-bottom">

                <div class="alert alert-info mb-0">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            This email contains a web version.
                        </div>

                        <a href="{{ $webViewUrl }}"
                           target="_blank"
                           class="btn btn-primary">

                            View Full Email
                        </a>

                    </div>

                </div>

            </div>

        @endif

        {{-- EMAIL BODY --}}
        <div class="card-body bg-light">

            <div class="bg-white border rounded shadow-sm">

                @if($isHtml)

                    <iframe
                        id="emailFrame"
                        style="width:100%;height:800px;border:none;">
                    </iframe>

                    <script>

                        document.addEventListener('DOMContentLoaded', function () {

                            let iframe = document.getElementById('emailFrame');

                            let doc = iframe.contentWindow.document;

                            doc.open();

                            doc.write(@json($body));

                            doc.close();

                        });

                    </script>

                @else

                    @php

                        $formattedBody = preg_replace_callback(
                            '/(https?:\/\/[^\s<]+)/i',
                            function ($matches) {

                                return '<br><a href="' . $matches[1] . '" target="_blank" class="btn btn-sm btn-primary my-2">Open Link</a><br>';

                            },
                            e($body)
                        );

                    @endphp

                    <div class="p-4">

                        <div style="
                            white-space: pre-wrap;
                            line-height: 1.8;
                            font-size: 14px;
                            font-family: Arial, sans-serif;
                        ">
                            {!! nl2br($formattedBody) !!}
                        </div>

                    </div>

                @endif

            </div>

        </div>

        {{-- MATCHED PRODUCTS --}}
        @if($matchedProducts->isNotEmpty())

            <div class="card-body border-top">

                <h4 class="mb-3">
                    Matched Products
                </h4>

                <div class="row">

                    <div class="col-md-7">

                        @foreach($matchedProducts as $match)

                            <div class="card mb-2">

                                <div class="card-body">

                                    <h6 class="mb-1">
                                        {{ $match['product']->product_name }}
                                    </h6>

                                    <small class="text-muted">
                                        Matched Keyword:
                                        {{ $match['matched_keyword'] }}
                                    </small>

                                </div>

                            </div>

                        @endforeach

                    </div>

                    <div class="col-md-5">

                        @if(!empty($aiRecommendation))

                            <div class="card border-info mb-3">

                                <div class="card-body">

                                    <h5 class="mb-2">
                                        AI Recommendation
                                    </h5>

                                    <div class="small text-muted mb-2">
                                        Confidence: {{ number_format((float) ($aiRecommendation['confidence'] ?? 0), 2) }}
                                    </div>

                                    <div class="fw-semibold">
                                        {{ $aiRecommendation['product']->product_name ?? 'No selection' }}
                                    </div>

                                    <div class="small text-muted mt-2">
                                        Keyword: {{ $aiRecommendation['matched_keyword'] ?? '-' }}
                                    </div>

                                    @if(!empty($aiRecommendation['reason']))
                                        <div class="small mt-2">
                                            {{ $aiRecommendation['reason'] }}
                                        </div>
                                    @endif

                                </div>

                            </div>

                        @endif

                        <div class="card border-success">

                            <div class="card-body">

                                <h5>
                                    Generate Quotation
                                </h5>

                                <p class="text-muted small">
                                    Create quotation from matched products.
                                </p>

                                <form method="POST"
                                      action="{{ url('/admin/quotations') }}">

                                    @csrf

                                    <input type="hidden"
                                           name="message_id"
                                           value="{{ $message['id'] }}">

                                    <button class="btn btn-success w-100">

                                        Generate Quotation

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        @endif

        {{-- FOOTER --}}
        <div class="card-footer bg-white d-flex justify-content-between">

            <a href="{{ route('admin.mail.messages.index') }}"
               class="btn btn-secondary">

                Back

            </a>

            <form method="POST"
                  action="{{ route('admin.mail.messages.read', $message['id']) }}">

                @csrf
                @method('PATCH')

                <button type="submit"
                        class="btn btn-primary">

                    Mark as Read

                </button>

            </form>

        </div>

    </div>

</div>

@endsection
