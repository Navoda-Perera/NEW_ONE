<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title'); ?> - SL Post System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --pm-primary: #4682B4;        /* Stormy Sky - main primary color */
            --pm-primary-dark: #2F5F8A;   /* Darker Stormy Sky */
            --pm-secondary: #9CAF88;      /* Sage Green - secondary actions */
            --pm-accent: #A0522D;         /* Burnt Sienna - accent color */
            --pm-light: #F5F5DC;          /* Ivory Sand - light backgrounds */
            --pm-success: #9CAF88;        /* Sage Green for success states */
            --pm-info: #4682B4;           /* Stormy Sky for info */
            --pm-warning: #A0522D;        /* Burnt Sienna for warnings */
            --pm-dark: #2c3e50;           /* Dark text color */
            --sidebar-width: 260px;
            --card-shadow: 0 4px 20px rgba(70, 130, 180, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--pm-light) 0%, #f8f9fb 100%);
            color: var(--pm-dark);
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
            box-shadow: 4px 0 15px rgba(70, 130, 180, 0.15);
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
            background: var(--pm-accent);
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 12px;
            margin-left: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
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
            background: linear-gradient(135deg, white 0%, var(--pm-light) 100%);
            padding: 1rem 2rem;
            box-shadow: var(--card-shadow);
            border-left: 4px solid var(--pm-accent);
            margin-bottom: 0;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--pm-dark);
            margin: 0;
            background: linear-gradient(135deg, var(--pm-primary), var(--pm-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-date {
            background: linear-gradient(135deg, rgba(70, 130, 180, 0.1), rgba(156, 175, 136, 0.1));
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 1px solid rgba(70, 130, 180, 0.2);
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
            background: linear-gradient(135deg, white 0%, rgba(245, 245, 220, 0.3) 100%);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(70, 130, 180, 0.1);
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
            background: linear-gradient(90deg, var(--pm-primary), var(--pm-accent));
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(70, 130, 180, 0.2);
            background: linear-gradient(135deg, white 0%, rgba(245, 245, 220, 0.5) 100%);
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
        .stat-primary .stat-icon { background: rgba(70, 130, 180, 0.15); color: var(--pm-primary); }

        .stat-success { color: var(--pm-secondary); }
        .stat-success .stat-icon { background: rgba(156, 175, 136, 0.15); color: var(--pm-secondary); }

        .stat-info { color: var(--pm-primary); }
        .stat-info .stat-icon { background: rgba(70, 130, 180, 0.15); color: var(--pm-primary); }

        .stat-warning { color: var(--pm-accent); }
        .stat-warning .stat-icon { background: rgba(160, 82, 45, 0.15); color: var(--pm-accent); }

        .stat-secondary { color: #6c757d; }
        .stat-secondary .stat-icon { background: rgba(108, 117, 125, 0.1); color: #6c757d; }

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
            border-bottom: 2px solid var(--pm-accent);
            display: inline-block;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, white 0%, rgba(245, 245, 220, 0.3) 100%);
            border: 2px solid rgba(70, 130, 180, 0.15);
            border-radius: 12px;
            text-decoration: none;
            color: var(--pm-dark);
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
            min-height: 140px;
        }

        .action-btn:hover {
            border-color: var(--pm-accent);
            background: linear-gradient(135deg, white 0%, rgba(156, 175, 136, 0.1) 100%);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(70, 130, 180, 0.2);
            color: var(--pm-primary);
            text-decoration: none;
        }

        .action-btn i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--pm-accent);
            transition: color 0.3s ease;
        }

        .action-btn:hover i {
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
            background: linear-gradient(135deg, var(--pm-secondary), var(--pm-accent));
            color: white;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        /* Custom Button Styles */
        .btn-pm-primary {
            background: linear-gradient(135deg, var(--pm-primary), var(--pm-primary-dark));
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-pm-primary:hover {
            background: linear-gradient(135deg, var(--pm-primary-dark), var(--pm-primary));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(70, 130, 180, 0.3);
        }

        .btn-pm-accent {
            background: linear-gradient(135deg, var(--pm-accent), #8B4513);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-pm-accent:hover {
            background: linear-gradient(135deg, #8B4513, var(--pm-accent));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(160, 82, 45, 0.3);
        }

        .btn-pm-secondary {
            background: linear-gradient(135deg, var(--pm-secondary), #7A8F6D);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-pm-secondary:hover {
            background: linear-gradient(135deg, #7A8F6D, var(--pm-secondary));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(156, 175, 136, 0.3);
        }

        /* Custom Background Colors */
        .bg-pm-primary { background: linear-gradient(135deg, var(--pm-primary), var(--pm-primary-dark)) !important; }
        .bg-pm-accent { background: linear-gradient(135deg, var(--pm-accent), #8B4513) !important; }
        .bg-pm-secondary { background: linear-gradient(135deg, var(--pm-secondary), #7A8F6D) !important; }

        /* Custom Badge Colors */
        .badge-pm-accent {
            background: var(--pm-accent) !important;
            color: white !important;
        }

        .badge-pm-primary {
            background: var(--pm-primary) !important;
            color: white !important;
        }

        .badge-pm-secondary {
            background: var(--pm-secondary) !important;
            color: white !important;
        }

        .badge-pm-success {
            background: var(--pm-success-color) !important;
            color: white !important;
        }

        /* Alert Colors */
        .alert-pm-primary {
            background: rgba(70, 130, 180, 0.1);
            border: 1px solid rgba(70, 130, 180, 0.2);
            color: var(--pm-primary);
        }

        .alert-pm-accent {
            background: rgba(160, 82, 45, 0.1);
            border: 1px solid rgba(160, 82, 45, 0.2);
            color: var(--pm-accent);
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
            <a href="<?php echo e(route('pm.dashboard')); ?>" class="logo">
                <i class="bi bi-mailbox"></i>
                <span>SL Post PM</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="<?php echo e(route('pm.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('pm.dashboard') ? 'active' : ''); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="<?php echo e(route('pm.customers.index')); ?>" class="nav-link <?php echo e(request()->routeIs('pm.customers.*') ? 'active' : ''); ?>">
                    <i class="bi bi-people"></i>
                    <span>Customers</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="<?php echo e(route('pm.single-item.index')); ?>" class="nav-link <?php echo e(request()->routeIs('pm.single-item.*') ? 'active' : ''); ?>">
                    <i class="bi bi-box-seam"></i>
                    <span>Add Single Item</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="<?php echo e(route('pm.item-management.index')); ?>" class="nav-link <?php echo e(request()->routeIs('pm.item-management.*') ? 'active' : ''); ?>">
                    <i class="bi bi-search"></i>
                    <span>Item Management</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="<?php echo e(route('pm.bulk-upload')); ?>" class="nav-link <?php echo e(request()->routeIs('pm.bulk-upload') ? 'active' : ''); ?>">
                    <i class="bi bi-cloud-upload"></i>
                    <span>Bulk Upload</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="<?php echo e(route('pm.customer-uploads')); ?>" class="nav-link <?php echo e(request()->routeIs('pm.customer-uploads') ? 'active' : ''); ?>">
                    <i class="bi bi-inbox"></i>
                    <span>Customer Uploads</span>
                    <?php if(isset($pendingItemsCount) && $pendingItemsCount > 0): ?>
                        <span class="notification-badge"><?php echo e($pendingItemsCount); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </nav>

        <!-- User Profile at Bottom -->
        <div class="sidebar-user">
            <a href="#" class="sidebar-user-info">
                <div class="sidebar-user-avatar">
                    <?php echo e(strtoupper(substr(auth('pm')->user()->name, 0, 1))); ?>

                </div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 600;"><?php echo e(auth('pm')->user()->name); ?></div>
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
                    <h1 class="page-title"><?php echo $__env->yieldContent('title', 'PM Dashboard'); ?></h1>
                </div>
                <div class="header-date">
                    <i class="bi bi-calendar3 text-primary"></i>
                    <?php echo e(now()->format('M d, Y')); ?>

                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-wrapper">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts Section -->
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\User\Desktop\NEW_ONE\resources\views/layouts/modern-pm.blade.php ENDPATH**/ ?>