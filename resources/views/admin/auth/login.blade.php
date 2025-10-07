<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Multi-Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .admin-header {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .btn-admin {
            background: linear-gradient(135deg, #007bff, #6610f2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        .form-floating .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-floating .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card border-0">
                    <div class="card-header admin-header text-center py-4">
                        <i class="bi bi-shield-lock-fill fs-1 mb-3"></i>
                        <h3 class="mb-0">Admin Login</h3>
                        <p class="mb-0 opacity-75">System Administration</p>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                @foreach ($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.login.post') }}">
                            @csrf
                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    class="form-control @error('nic') is-invalid @enderror"
                                    id="nic"
                                    name="nic"
                                    value="{{ old('nic') }}"
                                    placeholder="Enter your NIC"
                                    required
                                    autofocus
                                >
                                <label for="nic">
                                    <i class="bi bi-card-text me-2"></i>NIC Number
                                </label>
                                @error('nic')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
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
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">
                                    <small>Remember Me</small>
                                </label>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-admin btn-lg text-white">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Sign In
                                </button>
                            </div>
                        </form>

                        <div class="text-center">
                            <div class="border-top pt-3">
                                <small class="text-muted">
                                    Don't have an account?
                                    <a href="{{ route('admin.register') }}" class="text-decoration-none">Register here</a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="text-center mt-4">
                    <div class="btn-group" role="group">
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
