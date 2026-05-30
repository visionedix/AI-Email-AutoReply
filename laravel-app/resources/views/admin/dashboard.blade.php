@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@push('styles')
    <style>
        .dashboard-shell {
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(16, 185, 129, 0.12), transparent 26%),
                linear-gradient(180deg, #f8fafc 0%, #f3f6fb 100%);
            border-radius: 1.25rem;
            padding: 1.5rem;
        }

        .hero-card {
            border: 0;
            border-radius: 1.25rem;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #2563eb 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        }

        .hero-card .card-body {
            padding: 2rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .92);
            font-size: .82rem;
            letter-spacing: .02em;
        }

        .hero-stat {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 1rem;
            padding: 1rem 1.1rem;
            min-height: 100%;
        }

        .metric-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            height: 100%;
        }

        .metric-card .card-body {
            padding: 1.1rem;
        }

        .metric-label {
            color: #64748b;
            font-size: .85rem;
            margin-bottom: .35rem;
        }

        .metric-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.1;
            color: #0f172a;
        }

        .metric-icon {
            width: 3rem;
            height: 3rem;
            border-radius: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
        }

        .soft-panel {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        }

        .activity-table thead th {
            border-top: 0;
            color: #64748b;
            font-weight: 600;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .section-title {
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: .02em;
        }

        .shortcut-link {
            text-decoration: none;
        }

        .shortcut-link:hover .shortcut-card {
            transform: translateY(-2px);
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.12);
        }

        .shortcut-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            transition: all .2s ease;
            background: #fff;
            height: 100%;
        }

        .shortcut-card .card-body {
            padding: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-shell">
        <div class="hero-card mb-4">
            <div class="card-body">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="hero-badge mb-3">
                            <i class="fas fa-sparkles"></i>
                            Smart Email Quotation Workspace
                        </div>
                        <h1 class="display-6 fw-bold mb-3">Professional dashboard for sales, drafts, and AI-assisted quotations.</h1>
                        <p class="mb-4 text-white-50" style="max-width: 56rem;">
                            Track your catalogue, quotation pipeline, and team activity from one clean overview.
                            The dashboard is designed to help you move from email intake to draft creation faster.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.mail.messages.index') }}" class="btn btn-light btn-lg">
                                <i class="fas fa-inbox me-2"></i>Open Inbox
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-box me-2"></i>Manage Products
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="text-white-50 small text-uppercase fw-semibold">Today's quotations</div>
                                    <div class="display-5 fw-bold mb-0">{{ $stats['todayQuotationsCount'] }}</div>
                                </div>
                                <div class="metric-icon" style="background: rgba(255,255,255,.16);">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                            <div class="small text-white-50">
                                Drafts, edits, and Gmail sync activity are updated live across the admin workflow.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Products</div>
                            <div class="metric-value">{{ $stats['productsCount'] }}</div>
                        </div>
                        <div class="metric-icon bg-primary">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Quotations</div>
                            <div class="metric-value">{{ $stats['quotationsCount'] }}</div>
                        </div>
                        <div class="metric-icon bg-success">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Draft Quotations</div>
                            <div class="metric-value">{{ $stats['draftQuotationsCount'] }}</div>
                        </div>
                        <div class="metric-icon bg-warning text-dark">
                            <i class="fas fa-pen-to-square"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Users</div>
                            <div class="metric-value">{{ $stats['usersCount'] }}</div>
                        </div>
                        <div class="metric-icon bg-info">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card soft-panel h-100">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="section-title">Recent Quotations</div>
                            <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle activity-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentQuotations as $quotation)
                                        <tr>
                                            <td>
                                                <a href="{{ url('/admin/quotations/'.$quotation->id) }}" class="fw-semibold text-decoration-none">#{{ $quotation->id }}</a>
                                            </td>
                                            <td>{{ $quotation->customer_email ?: '-' }}</td>
                                            <td>{{ $quotation->product?->product_name ?: '-' }}</td>
                                            <td>
                                                <span class="badge rounded-pill {{ $quotation->status === 'draft' ? 'bg-warning text-dark' : ($quotation->status === 'sent' ? 'bg-success' : 'bg-danger') }}">
                                                    {{ ucfirst($quotation->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $quotation->created_at?->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No quotations yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card soft-panel mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <div class="section-title">Workflow Summary</div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Sent quotations</span>
                            <strong>{{ $stats['sentQuotationsCount'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Failed drafts</span>
                            <strong>{{ $stats['failedQuotationsCount'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted">Created today</span>
                            <strong>{{ $stats['todayQuotationsCount'] }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card soft-panel">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <div class="section-title">Quick Actions</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <a href="{{ route('admin.mail.messages.index') }}" class="shortcut-link">
                                    <div class="shortcut-card">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold text-dark">Inbox</div>
                                                <div class="text-muted small">Review new customer emails</div>
                                            </div>
                                            <i class="fas fa-inbox text-primary fs-4"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-12">
                                <a href="{{ route('admin.products.index') }}" class="shortcut-link">
                                    <div class="shortcut-card">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold text-dark">Products</div>
                                                <div class="text-muted small">Update catalog and documents</div>
                                            </div>
                                            <i class="fas fa-box text-success fs-4"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-12">
                                <a href="{{ route('admin.quotations.index') }}" class="shortcut-link">
                                    <div class="shortcut-card">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold text-dark">Quotations</div>
                                                <div class="text-muted small">Track drafts and sent items</div>
                                            </div>
                                            <i class="fas fa-file-invoice-dollar text-warning fs-4"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
