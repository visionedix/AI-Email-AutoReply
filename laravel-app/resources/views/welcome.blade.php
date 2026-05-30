<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AI Email AutoReply') }}</title>

    @fonts

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --bg: #07111f;
            --bg-soft: #0c1729;
            --panel: rgba(11, 20, 37, 0.72);
            --panel-border: rgba(148, 163, 184, 0.16);
            --text: #e5eefc;
            --muted: #9eb0cb;
            --accent: #58a6ff;
            --accent-2: #22c55e;
            --accent-3: #f59e0b;
            --shadow: 0 32px 80px rgba(2, 8, 23, 0.46);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: "Source Sans 3", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 15% 15%, rgba(88, 166, 255, 0.24), transparent 30%),
                radial-gradient(circle at 80% 10%, rgba(34, 197, 94, 0.16), transparent 26%),
                radial-gradient(circle at 70% 80%, rgba(245, 158, 11, 0.12), transparent 24%),
                linear-gradient(180deg, #050c17 0%, #0b1220 100%);
            color: var(--text);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            position: relative;
            overflow: hidden;
        }

        .noise {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,.9), transparent 92%);
            opacity: .45;
        }

        .container {
            width: min(1180px, calc(100% - 2rem));
            margin: 0 auto;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 0;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: .8rem;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .brand-mark {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: .9rem;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #60a5fa, #22c55e);
            box-shadow: 0 12px 26px rgba(34, 197, 94, 0.22);
            color: white;
        }

        .nav-links {
            display: flex;
            gap: .9rem;
            align-items: center;
        }

        .nav-links .ghost {
            padding: .8rem 1.1rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.45);
            color: var(--text);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 1.5rem;
            padding: 3.5rem 0 2rem;
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .45rem .9rem;
            border-radius: 999px;
            background: rgba(88, 166, 255, 0.14);
            color: #cbe2ff;
            border: 1px solid rgba(88, 166, 255, 0.2);
            font-size: .85rem;
            letter-spacing: .03em;
        }

        h1 {
            margin: 1rem 0;
            font-size: clamp(3rem, 5vw, 5.8rem);
            line-height: .96;
            letter-spacing: -0.05em;
            max-width: 12ch;
        }

        .lead {
            font-size: 1.08rem;
            line-height: 1.7;
            color: var(--muted);
            max-width: 60ch;
        }

        .cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: .9rem;
            margin-top: 1.7rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            border-radius: 999px;
            padding: .95rem 1.25rem;
            font-weight: 700;
            border: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: white;
            box-shadow: 0 18px 30px rgba(37, 99, 235, 0.28);
        }

        .btn-secondary {
            background: rgba(15, 23, 42, 0.55);
            color: var(--text);
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: .9rem;
            margin-top: 1.4rem;
            color: var(--muted);
            font-size: .95rem;
        }

        .meta-row span {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem .8rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.46);
            border: 1px solid rgba(148, 163, 184, 0.14);
        }

        .preview-card {
            position: relative;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.92), rgba(11, 20, 37, 0.82));
            border: 1px solid var(--panel-border);
            border-radius: 1.4rem;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .preview-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            color: var(--muted);
            font-size: .92rem;
        }

        .preview-body {
            padding: 1.2rem;
        }

        .message-shell {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .message-label {
            color: var(--muted);
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: .45rem;
        }

        .message-value {
            font-size: .98rem;
            color: #f8fbff;
            line-height: 1.6;
        }

        .pipeline {
            display: grid;
            gap: .8rem;
            margin-top: 1rem;
        }

        .step {
            display: flex;
            gap: .9rem;
            align-items: flex-start;
            background: rgba(15, 23, 42, 0.58);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 1rem;
            padding: .9rem 1rem;
        }

        .step-dot {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: rgba(88, 166, 255, 0.18);
            color: #cbe2ff;
        }

        .section {
            padding: 2rem 0 4rem;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #8eb6e7;
            margin-bottom: .4rem;
        }

        .section-head h2 {
            margin: 0;
            font-size: clamp(1.7rem, 3vw, 2.4rem);
            letter-spacing: -0.04em;
        }

        .section-head p {
            margin: 0;
            color: var(--muted);
            max-width: 54ch;
            line-height: 1.6;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .feature {
            padding: 1.2rem;
            border-radius: 1.1rem;
            background: rgba(15, 23, 42, 0.56);
            border: 1px solid rgba(148, 163, 184, 0.14);
            min-height: 100%;
        }

        .feature-icon {
            width: 2.8rem;
            height: 2.8rem;
            border-radius: .9rem;
            display: grid;
            place-items: center;
            margin-bottom: 1rem;
            color: white;
        }

        .feature h3 {
            margin: 0 0 .4rem;
            font-size: 1.08rem;
        }

        .feature p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
        }

        .stats-band {
            margin-top: 1.2rem;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .stat {
            padding: 1rem 1.1rem;
            border-radius: 1rem;
            background: rgba(7, 17, 31, 0.72);
            border: 1px solid rgba(148, 163, 184, 0.14);
        }

        .stat strong {
            display: block;
            font-size: 1.7rem;
            margin-bottom: .2rem;
        }

        .stat span {
            color: var(--muted);
        }

        .footer {
            padding: 1.8rem 0 2.8rem;
            color: var(--muted);
            font-size: .92rem;
        }

        @media (max-width: 991px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .feature-grid,
            .stats-band {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .container {
                width: min(100% - 1.2rem, 1180px);
            }

            .nav {
                gap: 1rem;
                align-items: flex-start;
                flex-direction: column;
            }

            h1 {
                max-width: 100%;
            }

            .feature-grid,
            .stats-band {
                grid-template-columns: 1fr;
            }

            .cta-row {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="noise"></div>
    <div class="page">
        <div class="container">
            <header class="nav">
                <a href="{{ url('/') }}" class="brand">
                    <span class="brand-mark">
                        <i class="fas fa-brain"></i>
                    </span>
                    <span>{{ config('app.name', 'AI Email AutoReply') }}</span>
                </a>
                <div class="nav-links">
                    <a href="{{ route('admin.login') }}" class="ghost">Admin Login</a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Open Dashboard</a>
                </div>
            </header>

            <section class="hero">
                <div>
                    <div class="eyebrow">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        AI-powered quotation drafting for email workflows
                    </div>
                    <h1>Turn inbound emails into polished quotes in minutes.</h1>
                    <p class="lead">
                        AI Email AutoReply reads customer emails, detects product intent, matches the right catalog item,
                        drafts a quotation, and prepares a Gmail draft for review or sending.
                    </p>
                    <div class="cta-row">
                        <a href="{{ route('admin.login') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Start in Admin
                        </a>
                        <a href="#workflow" class="btn btn-secondary">
                            <i class="fas fa-diagram-project"></i>
                            See Workflow
                        </a>
                    </div>
                    <div class="meta-row">
                        <span><i class="fas fa-inbox"></i> Read email time matching</span>
                        <span><i class="fas fa-file-pen"></i> AI quotation drafts</span>
                        <span><i class="fas fa-envelope-open-text"></i> Gmail draft sync</span>
                    </div>
                    <div class="stats-band">
                        <div class="stat">
                            <strong>03</strong>
                            <span>Core steps from email to draft</span>
                        </div>
                        <div class="stat">
                            <strong>AI</strong>
                            <span>Swappable provider design</span>
                        </div>
                        <div class="stat">
                            <strong>PDF</strong>
                            <span>Quotation attachments supported</span>
                        </div>
                        <div class="stat">
                            <strong>Gmail</strong>
                            <span>Draft-based workflow today</span>
                        </div>
                    </div>
                </div>

                <div class="preview-card">
                    <div class="preview-top">
                        <span>Live workflow preview</span>
                        <span>AI email assistant</span>
                    </div>
                    <div class="preview-body">
                        <div class="message-shell">
                            <div class="message-label">Incoming Email</div>
                            <div class="message-value">
                                Subject: Need a bearing quotation<br>
                                Body: Please send the price and delivery details for SKF Bearing. This is an urgent inquiry.
                            </div>
                        </div>

                        <div class="pipeline">
                            <div class="step">
                                <div class="step-dot"><i class="fas fa-magnifying-glass"></i></div>
                                <div>
                                    <div class="fw-semibold">Keyword and intent detection</div>
                                    <div class="text-white-50 small">The system detects quotation intent and extracts the product keyword.</div>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-dot"><i class="fas fa-wand-magic-sparkles"></i></div>
                                <div>
                                    <div class="fw-semibold">AI quotation drafting</div>
                                    <div class="text-white-50 small">A clean subject and body are generated from product and template data.</div>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-dot"><i class="fas fa-envelope-open-text"></i></div>
                                <div>
                                    <div class="fw-semibold">Gmail draft saved</div>
                                    <div class="text-white-50 small">The draft is stored in Gmail for review now, and can auto-send later.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section" id="workflow">
                <div class="section-head">
                    <div>
                        <div class="section-title">Workflow</div>
                        <h2>Built for a real sales inbox, not a demo screen.</h2>
                    </div>
                    <p>
                        The landing page explains how the app processes inbound email, identifies products, and produces a quotation draft that your team can review before sending.
                    </p>
                </div>

                <div class="feature-grid">
                    <div class="feature">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #2563eb, #60a5fa);">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h3>Read-time email analysis</h3>
                        <p>Header and body data are scanned as the email is read, so the app can infer intent and find matching products.</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #16a34a, #22c55e);">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>Provider-swappable AI layer</h3>
                        <p>The AI service is built as a contract, so you can switch from ChatGPT to another provider later without changing the product flow.</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                            <i class="fas fa-envelope-circle-check"></i>
                        </div>
                        <h3>Draft-first delivery</h3>
                        <p>Quotations are saved as Gmail drafts today, giving your team control before you switch to auto-send.</p>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <span>&copy; {{ date('Y') }} {{ config('app.name', 'AI Email AutoReply') }}.</span>
                    <span>Designed for AI-assisted email sales workflows.</span>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
