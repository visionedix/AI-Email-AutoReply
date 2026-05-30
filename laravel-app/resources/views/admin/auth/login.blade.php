@extends('admin.layouts.auth')

@section('title', 'Admin Login')

@push('styles')
    <style>
        .login-wrap {
            min-height: 100vh;
            width: 100%;
            background:
                radial-gradient(circle at 20% 20%, rgba(88, 166, 255, 0.18), transparent 32%),
                radial-gradient(circle at 80% 10%, rgba(34, 197, 94, 0.14), transparent 28%),
                linear-gradient(180deg, #06101f 0%, #0a1324 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .login-shell {
            width: 100%;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            border-radius: 0;
            overflow: hidden;
            box-shadow: none;
            border: 0;
            background: rgba(8, 16, 30, 0.8);
        }

        .login-hero {
            padding: 2.2rem;
            color: #edf4ff;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(37, 99, 235, 0.82)),
                radial-gradient(circle at top left, rgba(34, 197, 94, 0.18), transparent 30%);
            position: relative;
        }

        .login-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 42px 42px;
            opacity: .35;
            pointer-events: none;
        }

        .login-hero > * {
            position: relative;
            z-index: 1;
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            color: #f8fbff;
            font-size: .84rem;
            letter-spacing: .03em;
        }

        .login-hero h1 {
            margin: 1rem 0 .85rem;
            font-size: clamp(2.4rem, 4vw, 4rem);
            line-height: .98;
            letter-spacing: -0.05em;
            max-width: 12ch;
        }

        .login-hero p {
            color: rgba(237, 244, 255, 0.82);
            max-width: 42ch;
            line-height: 1.7;
            margin-bottom: 1.4rem;
        }

        .login-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .85rem;
            margin-top: 1.4rem;
        }

        .login-metric {
            padding: 1rem;
            border-radius: 1rem;
            background: rgba(7, 14, 28, 0.38);
            border: 1px solid rgba(255,255,255,.12);
        }

        .login-metric strong {
            display: block;
            font-size: 1.6rem;
            margin-bottom: .25rem;
        }

        .login-metric span {
            color: rgba(237, 244, 255, 0.72);
            font-size: .92rem;
        }

        .login-form {
            background: rgba(248, 250, 252, 0.96);
            padding: 2.2rem;
        }

        .login-form .card {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: none;
            background: transparent;
        }

        .login-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.03em;
            margin-bottom: .35rem;
        }

        .login-subtitle {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 1.4rem;
        }

        .input-group-text {
            background: #eff6ff;
            border-color: #dbeafe;
            color: #1d4ed8;
        }

        .form-control {
            border-color: #dbe4f0;
            padding: .82rem 1rem;
        }

        .form-control:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 .2rem rgba(96, 165, 250, 0.18);
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: .2rem;
        }

        .sign-in-btn {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: 0;
            border-radius: .9rem;
            padding: .85rem 1.2rem;
            font-weight: 700;
            box-shadow: 0 16px 28px rgba(37, 99, 235, 0.22);
        }

        .auth-footer {
            margin-top: 1rem;
            color: #64748b;
            font-size: .92rem;
        }

        @media (max-width: 992px) {
            .login-shell {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .login-hero {
                display: none;
            }

            .login-form {
                padding: 1.1rem;
            }

            .login-title {
                font-size: 1.4rem;
            }

            .login-subtitle {
                font-size: .96rem;
            }
        }

        @media (max-width: 640px) {
            .login-wrap {
                padding: 0;
            }

            .login-form {
                padding: 1rem;
            }

            .remember-row {
                flex-direction: column;
                align-items: flex-start;
                gap: .5rem;
            }

            .input-group {
                flex-wrap: nowrap;
            }

            .input-group-text,
            .form-control {
                height: 3.2rem;
            }

            .sign-in-btn {
                border-radius: .8rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-wrap">
        <div class="login-shell">
            <div class="login-hero">
                <div class="login-badge">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    AI Email AutoReply Admin
                </div>

                <h1>Smart quotation automation for your inbox.</h1>
                <p>
                    Sign in to review incoming emails, let AI identify product intent, generate quotation drafts, and manage Gmail drafts from one clean workspace.
                </p>

                <div class="login-metrics">
                    <div class="login-metric">
                        <strong>Read</strong>
                        <span>Scan email headers and body for intent</span>
                    </div>
                    <div class="login-metric">
                        <strong>Draft</strong>
                        <span>Create polished quotation responses</span>
                    </div>
                    <div class="login-metric">
                        <strong>Sync</strong>
                        <span>Save to Gmail drafts automatically</span>
                    </div>
                    <div class="login-metric">
                        <strong>Scale</strong>
                        <span>Swap AI providers later without rewrites</span>
                    </div>
                </div>
            </div>

            <div class="login-form">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center gap-2 mb-3 d-md-none">
                            <div class="brand-mark" style="width: 2.2rem; height: 2.2rem; border-radius: .7rem;">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div class="fw-bold text-dark">{{ config('app.name', 'AI Email AutoReply') }}</div>
                        </div>
                        <div class="login-title">Welcome back</div>
                        <div class="login-subtitle">Sign in to continue managing your email-driven quotation workflow.</div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.login.submit') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="admin@example.com" value="{{ old('email') }}" required autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                                </div>
                            </div>

                            <div class="remember-row mb-4">
                                <div class="form-check">
                                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                                    <label for="remember" class="form-check-label">Remember me</label>
                                </div>
                                <span class="text-muted small">Secure admin access</span>
                            </div>

                            <button type="submit" class="btn btn-primary sign-in-btn w-100">
                                Sign In
                            </button>
                        </form>

                        <div class="auth-footer">
                            AI-driven email replies, product matching, and Gmail draft sync in one workspace.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
