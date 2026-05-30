<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="brand-link">
                <span class="brand-text fw-light">AI Email Admin</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                    @can('view-admin-dashboard')
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon bi bi-box-seam-fill"></i>
                            <p>
                                Configuration
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview" role="navigation" aria-label="Configuration navigation" style="display: none; box-sizing: border-box;">
                            @can('manage-users')
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-users"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                            @endcan
                            @can('manage-products')
                                <li class="nav-item">
                                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-box"></i>
                                        <p>Products</p>
                                    </a>
                                </li>
                            @endcan
                            @can('manage-email-templates')
                                <li class="nav-item">
                                    <a href="{{ route('admin.email-templates.index') }}" class="nav-link {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-pencil-square"></i>
                                        <p>Email Templates</p>
                                    </a>
                                </li>
                            @endcan
                            @can('manage-quotations')
                                <li class="nav-item">
                                    <a href="{{ route('admin.quotations.index') }}" class="nav-link {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                        <p>Quotations</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    @can('manage-mail')
                        <li class="nav-item">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon bi bi-envelope"></i>
                                <p>
                                    G-box
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" role="navigation" aria-label="Mail navigation" style="display: none; box-sizing: border-box;">
                                <li class="nav-item">
                                    <a href="{{ route('admin.mail.messages.index') }}" class="nav-link {{ request()->routeIs('admin.mail.messages.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-envelope-open-text"></i>
                                        <p>Inbox</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.quotations.index') }}" class="nav-link {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                        <p>Quotations</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    @can('manage-roles-permissions')
                        <li class="nav-item">
                            <a href="{{ url('/admin/access-control') }}" class="nav-link {{ request()->is('admin/access-control*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Roles & Permissions</p>
                            </a>
                        </li>
                    @endcan
                    
                     
                </ul>
            </nav>
        </div>
    </aside>
