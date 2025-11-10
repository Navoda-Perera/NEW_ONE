@extends('layouts.app')

@section('title', 'Create Customer')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.customers.index') }}">
            <i class="bi bi-people"></i> Customers
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('pm.customers.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="fw-bold text-dark mb-0">Create New Customer</h2>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pm.customers.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nic" class="form-label fw-semibold">NIC Number</label>
                            <input id="nic" type="text" class="form-control @error('nic') is-invalid @enderror"
                                   name="nic" value="{{ old('nic') }}" required autocomplete="username">
                            @error('nic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address (Optional)</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label fw-semibold">Mobile Number</label>
                            <input id="mobile" type="tel" class="form-control @error('mobile') is-invalid @enderror"
                                   name="mobile" value="{{ old('mobile') }}" required autocomplete="tel"
                                   pattern="[0-9]{10}" placeholder="0771234567">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Enter a 10-digit mobile number (e.g., 0771234567)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label fw-semibold">Company Name</label>
                            <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror"
                                   name="company_name" value="{{ old('company_name') }}" required autocomplete="organization">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_br" class="form-label fw-semibold">Company BR Number</label>
                            <input id="company_br" type="text" class="form-control @error('company_br') is-invalid @enderror"
                                   name="company_br" value="{{ old('company_br') }}" required>
                            @error('company_br')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password-confirm" class="form-label fw-semibold">Confirm Password</label>
                            <input id="password-confirm" type="password" class="form-control"
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('pm.customers.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-plus me-2"></i>Create Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
