<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - SL POST COURIER SYSTEM</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        
        /* Modern Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            box-shadow: 0 0 40px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid #374151;
        }
        
        .sidebar-brand {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar-brand i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            color: #60a5fa;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 0;
            position: relative;
        }
        
        .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            padding-left: 1.5rem;
        }
        
        .nav-link.active {
            color: white;
            background-color: rgba(96,165,250,0.2);
            border-right: 3px solid #60a5fa;
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .notification-badge {
            background: #ef4444;
            color: white;
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            border-radius: 10px;
            margin-left: auto;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            background-color: #f8fafc;
        }
        
        .top-header {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .page-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            border-radius: 2px;
            margin-right: 1rem;
        }
        
        .header-date {
            display: flex;
            align-items: center;
            color: #64748b;
            font-weight: 500;
            background: #f8fafc;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .header-date i {
            margin-right: 0.5rem;
            color: #3b82f6;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            color: #64748b;
            margin-left: auto;
        }
        
        .user-profile i {
            font-size: 1.25rem;
            margin-right: 0.5rem;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        /* Modern Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-left: auto;
        }
        
        /* Color Themes */
        .stat-primary { color: #3b82f6; }
        .stat-primary .stat-icon { background: rgba(59,130,246,0.1); color: #3b82f6; }
        
        .stat-success { color: #10b981; }
        .stat-success .stat-icon { background: rgba(16,185,129,0.1); color: #10b981; }
        
        .stat-warning { color: #f59e0b; }
        .stat-warning .stat-icon { background: rgba(245,158,11,0.1); color: #f59e0b; }
        
        .stat-info { color: #06b6d4; }
        .stat-info .stat-icon { background: rgba(6,182,212,0.1); color: #06b6d4; }
        
        .stat-secondary { color: #6b7280; }
        .stat-secondary .stat-icon { background: rgba(107,114,128,0.1); color: #6b7280; }
        
        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-top: 2rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #64748b;
            transition: all 0.2s ease;
            height: 120px;
        }
        
        .action-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.15);
        }
        
        .action-btn i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .action-btn span {
            font-weight: 500;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-brand span,
            .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .content-area {
                padding: 1rem;
            }
            
            .top-header {
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .page-title::before {
                width: 3px;
                height: 20px;
                margin-right: 0.75rem;
            }
            
            .header-date {
                align-self: flex-end;
                font-size: 0.875rem;
            }
        }
        
        @media (max-width: 480px) {
            .page-title {
                font-size: 1.25rem;
            }
            
            .top-header {
                gap: 0.5rem;
            }
        }
        
        /* User Dropdown */
        .user-dropdown {
            position: relative;
            margin-left: auto;
        }
        
        .user-toggle {
            display: flex;
            align-items: center;
            color: #64748b;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .user-toggle:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 0.5rem;
            font-weight: 600;
        }
        
        .admin-badge {
            background: #3b82f6;
            color: white;
            font-size: 0.75rem;
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
            border-top: 1px solid #374151;
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
        }
        
        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .sidebar-user-details span {
            display: block;
        }
        
        .sidebar-user-name {
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .sidebar-user-role {
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <i class="bi bi-shield-check"></i>
                <span>SL Post Admin</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Manage Users</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-graph-up"></i>
                    <span>Reports</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-bell"></i>
                    <span>Notifications</span>
                    <span class="notification-badge">3</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
        
        <!-- Sidebar User Profile -->
        @php
            $currentUser = auth('admin')->user();
        @endphp
        
        @if($currentUser)
        <div class="sidebar-user">
            <div class="dropdown">
                <a href="#" class="sidebar-user-info" data-bs-toggle="dropdown">
                    <div class="sidebar-user-avatar">
                        {{ substr($currentUser->name, 0, 1) }}
                    </div>
                    <div class="sidebar-user-details">
                        <span class="sidebar-user-name">{{ $currentUser->name }}</span>
                        <span class="sidebar-user-role">{{ ucfirst($currentUser->role) }}</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            <div class="header-date">
                <i class="bi bi-calendar3"></i>
                <span>{{ now()->format('F d, Y') }}</span>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>