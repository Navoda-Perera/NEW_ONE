@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('customer.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.services.index') }}">
            <i class="bi bi-box-seam"></i> Postal Services
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.profile') }}">
            <i class="bi bi-person"></i> Profile
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">
                <i class="bi bi-speedometer2"></i> Customer Dashboard
                <small class="text-muted">Welcome, {{ $user->name }}</small>
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Account Type:</strong>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $user->user_type)) }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Member Since:</strong> {{ $user->created_at->format('M d, Y') }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-success">Active</span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('customer.profile') }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('customer.services.index') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-box-seam"></i> Postal Services
                        </a>
                        <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-plus-circle"></i> Add Single Item
                        </a>
                        <a href="{{ route('customer.services.bulk-upload') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-cloud-upload"></i> Bulk Upload
                        </a>
                        <a href="{{ route('customer.services.items') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-list-ul"></i> View Items
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No recent activity to display.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
