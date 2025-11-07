<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SL POST COURIER SYSTEM</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <!-- Main Header -->
                    <div class="text-center mb-5">
                        <h1 class="display-4 fw-bold text-primary mb-3">SRI LANKA POST</h1>
                        <p class="lead text-muted">SL POST COURIER SYSTEM</p>
                        <p class="text-muted">Choose your portal to continue</p>
                    </div>

                    <!-- Portal Cards -->
                    <div class="row g-4">
                        <!-- Admin Portal -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4 class="card-title fw-bold mb-3">Admin Portal</h4>
                                    <p class="card-text text-muted mb-4">
                                        System administration access.
                                        Manage all users, locations, and system settings.
                                    </p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.login') }}" class="btn btn-primary btn-lg">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Admin Login
                                        </a>
                                        <a href="{{ route('admin.register') }}" class="btn btn-outline-primary">
                                            <i class="bi bi-person-plus me-2"></i>Admin Register
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PM Portal -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="bi bi-briefcase text-warning" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4 class="card-title fw-bold mb-3">Postmaster Portal</h4>
                                    <p class="card-text text-muted mb-4">
                                        Post office management access.
                                        Manage customers, postmen, and daily operations.
                                    </p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('pm.login') }}" class="btn btn-warning btn-lg text-white">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>PM Login
                                        </a>
                                        <small class="text-muted mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Contact admin for access
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Portal -->
                        <div class="col-lg-4 col-md-6 mx-auto">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="bi bi-person-circle text-success" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4 class="card-title fw-bold mb-3">Customer Portal</h4>
                                    <p class="card-text text-muted mb-4">
                                        External customer access portal.
                                        View your projects, account information, and services.
                                    </p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('customer.login') }}" class="btn btn-success btn-lg">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Customer Login
                                        </a>
                                        <a href="{{ route('customer.register') }}" class="btn btn-outline-success">
                                            <i class="bi bi-person-plus me-2"></i>Customer Register
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card border-0 bg-primary bg-opacity-10">
                                <div class="card-body text-center py-4">
                                    <h5 class="fw-bold text-primary mb-3">System Features</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <i class="bi bi-shield-lock text-primary mb-2 d-block" style="font-size: 1.5rem;"></i>
                                            <small class="text-muted">Secure Authentication</small>
                                        </div>
                                        <div class="col-md-4">
                                            <i class="bi bi-people text-primary mb-2 d-block" style="font-size: 1.5rem;"></i>
                                            <small class="text-muted">Role-Based Access</small>
                                        </div>
                                        <div class="col-md-4">
                                            <i class="bi bi-speedometer2 text-primary mb-2 d-block" style="font-size: 1.5rem;"></i>
                                            <small class="text-muted">Dedicated Dashboards</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Powered by Laravel {{ app()->version() }} |
                            Built with Bootstrap 5
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
