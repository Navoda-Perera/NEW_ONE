<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Multi-Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .admin-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .btn-admin {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .form-floating .form-control,
        .form-floating .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-floating .form-control:focus,
        .form-floating .form-select:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card register-card border-0">
                    <div class="card-header admin-header text-center py-4">
                        <i class="bi bi-person-plus-fill fs-1 mb-3"></i>
                        <h3 class="mb-0">Admin Registration</h3>
                        <p class="mb-0 opacity-75">Create Administrator Account</p>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.register') }}">
                            @csrf
                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Enter your full name"
                                    required
                                    autofocus
                                >
                                <label for="name">
                                    <i class="bi bi-person me-2"></i>Full Name
                                </label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    class="form-control @error('nic') is-invalid @enderror"
                                    id="nic"
                                    name="nic"
                                    value="{{ old('nic') }}"
                                    placeholder="Enter your NIC"
                                    required
                                >
                                <label for="nic">
                                    <i class="bi bi-card-text me-2"></i>NIC Number
                                </label>
                                @error('nic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Enter your email (optional)"
                                >
                                <label for="email">
                                    <i class="bi bi-envelope me-2"></i>Email Address (Optional)
                                </label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="tel"
                                    class="form-control @error('mobile') is-invalid @enderror"
                                    id="mobile"
                                    name="mobile"
                                    value="{{ old('mobile') }}"
                                    placeholder="Enter your mobile number"
                                    pattern="[0-9+\-\s]+"
                                    required
                                >
                                <label for="mobile">
                                    <i class="bi bi-phone me-2"></i>Mobile Number
                                </label>
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <select
                                    class="form-select @error('role') is-invalid @enderror"
                                    id="role"
                                    name="role"
                                    required
                                >
                                    <option value="">Select Role</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <label for="role">
                                    <i class="bi bi-shield-check me-2"></i>Role
                                </label>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Note: Postmaster accounts can only be created by administrators through the user management panel.
                                </small>
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >
                                <label for="password">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-4">
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="Confirm your password"
                                    required
                                >
                                <label for="password_confirmation">
                                    <i class="bi bi-lock-fill me-2"></i>Confirm Password
                                </label>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-admin btn-lg text-white">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Create Account
                                </button>
                            </div>
                        </form>

                        <div class="text-center">
                            <div class="border-top pt-3">
                                <small class="text-muted">
                                    Already have an account?
                                    <a href="{{ route('admin.login') }}" class="text-decoration-none">Login here</a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="text-center mt-4">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.login') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-shield-lock"></i> Admin Login
                        </a>
                        <a href="{{ route('pm.login') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-briefcase"></i> PM Login
                        </a>
                        <a href="{{ route('customer.login') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-person"></i> Customer Login
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
