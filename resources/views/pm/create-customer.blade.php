@extends('layouts.app')

@section('title', 'Create Customer')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('pm.customers.index') }}">
            <i class="bi bi-people"></i> Customers
        </a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-box-seam"></i> Items
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('pm.items.pending') }}">Pending Items</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('pm.items.pending') }}">All Pending Items</a></li>
        </ul>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.bulk-upload') }}">
            <i class="bi bi-cloud-upload"></i> Bulk Upload
        </a>
    </li>
@endsection

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pm.customers.index') }}">Customers</a></li>
        <li class="breadcrumb-item active">Create Customer</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-plus me-2"></i>Create New Customer
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('pm.customers.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nic" class="form-label">NIC Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nic') is-invalid @enderror"
                                   id="nic" name="nic" value="{{ old('nic') }}" maxlength="12" required>
                            @error('nic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                   id="mobile" name="mobile" value="{{ old('mobile') }}" maxlength="15" required>
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Assigned Post Office</label>
                        <input type="text" class="form-control"
                               value="{{ $locations->first()->name ?? 'No location assigned' }}" readonly>
                        <small class="text-muted">Customer will be assigned to your post office location.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" minlength="8" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" minlength="8" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pm.customers.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Customers
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i>Create Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
