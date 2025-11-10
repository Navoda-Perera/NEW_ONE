<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - SL Post System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --pm-primary: #dc3545;
            --pm-primary-dark: #c82333;
            --pm-secondary: #6c757d;
            --pm-success: #198754;
            --pm-info: #0dcaf0;
            --pm-warning: #ffc107;
            --pm-light: #f8f9fa;
            --pm-dark: #212529;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #2d3436;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--pm-primary) 0%, var(--pm-primary-dark) 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-header .logo {
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .sidebar-header .logo:hover {
            color: rgba(255,255,255,0.9);
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .notification-badge {
            background: var(--pm-info);
            color: var(--pm-dark);
            padding: 0.125rem 0.5rem;
            border-radius: 12px;
            margin-left: 0.5rem;
        }

        /* Sidebar User Profile */
        .sidebar-user {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .sidebar-user-info:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .top-header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid var(--pm-primary);
            margin-bottom: 0;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--pm-dark);
            margin: 0;
            background: linear-gradient(135deg, var(--pm-primary), var(--pm-primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-date {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: var(--pm-primary);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content Area */
        .content-wrapper {
            padding: 2rem;
        }

        /* Statistics Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--pm-primary), var(--pm-primary-dark));
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-primary { color: var(--pm-primary); }
        .stat-primary .stat-icon { background: rgba(220, 53, 69, 0.1); color: var(--pm-primary); }

        .stat-success { color: var(--pm-success); }
        .stat-success .stat-icon { background: rgba(25, 135, 84, 0.1); color: var(--pm-success); }

        .stat-info { color: var(--pm-info); }
        .stat-info .stat-icon { background: rgba(13, 202, 240, 0.1); color: var(--pm-info); }

        .stat-warning { color: var(--pm-warning); }
        .stat-warning .stat-icon { background: rgba(255, 193, 7, 0.1); color: var(--pm-warning); }

        .stat-secondary { color: var(--pm-secondary); }
        .stat-secondary .stat-icon { background: rgba(108, 117, 125, 0.1); color: var(--pm-secondary); }

        /* Quick Actions */
        .quick-actions {
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--pm-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--pm-primary);
            display: inline-block;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: white;
            border: 2px solid rgba(220, 53, 69, 0.1);
            border-radius: 12px;
            text-decoration: none;
            color: var(--pm-dark);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            min-height: 140px;
        }

        .action-btn:hover {
            border-color: var(--pm-primary);
            color: var(--pm-primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.2);
            text-decoration: none;
        }

        .action-btn i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--pm-primary);
        }

        .action-btn span {
            font-weight: 600;
            font-size: 1rem;
        }

        /* Sidebar User Profile */
        .sidebar-user {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.15);
            background: rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-user-info:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .sidebar-user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--pm-dark);
            margin-right: 1rem;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }

        .sidebar-user-details {
            flex: 1;
        }

        .sidebar-user-name {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .sidebar-user-role {
            font-size: 0.8rem;
            opacity: 0.8;
            color: #ffd700;
            font-weight: 600;
        }

        /* Main Content */
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            background: #fff;
            color: var(--pm-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-weight: 700;
        }

        /* Location Info Card */
        .location-card {
            background: linear-gradient(135deg, var(--pm-primary), var(--pm-primary-dark));
            color: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .content-wrapper {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('pm.dashboard') }}" class="logo">
                <i class="bi bi-mailbox"></i>
                <span>SL Post PM</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('pm.dashboard') }}" class="nav-link {{ request()->routeIs('pm.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('pm.customers.index') }}" class="nav-link {{ request()->routeIs('pm.customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Customers</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('pm.single-item.index') }}" class="nav-link {{ request()->routeIs('pm.single-item.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Add Single Item</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('pm.item-management.index') }}" class="nav-link {{ request()->routeIs('pm.item-management.*') ? 'active' : '' }}">
                    <i class="bi bi-search"></i>
                    <span>Item Management</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('pm.bulk-upload') }}" class="nav-link {{ request()->routeIs('pm.bulk-upload') ? 'active' : '' }}">
                    <i class="bi bi-cloud-upload"></i>
                    <span>Bulk Upload</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('pm.customer-uploads') }}" class="nav-link {{ request()->routeIs('pm.customer-uploads') ? 'active' : '' }}">
                    <i class="bi bi-inbox"></i>
                    <span>Customer Uploads</span>
                    @if(isset($pendingItemsCount) && $pendingItemsCount > 0)
                        <span class="notification-badge">{{ $pendingItemsCount }}</span>
                    @endif
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('pm.postmen.index') }}" class="nav-link {{ request()->routeIs('pm.postmen.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Postmen</span>
                </a>
            </div>
        </nav>

        <!-- User Profile at Bottom -->
        <div class="sidebar-user">
            <a href="#" class="sidebar-user-info">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(auth('pm')->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 600;">{{ auth('pm')->user()->name }}</div>
                    <div style="font-size: 0.75rem; opacity: 0.8;">Postmaster</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">@yield('title', 'PM Dashboard')</h1>
                </div>
                <div class="header-date">
                    <i class="bi bi-calendar3 text-primary"></i>
                    {{ now()->format('M d, Y') }}
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts Section -->
    @yield('scripts')
</body>
</html>
