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
        <a class="nav-link" href="{{ route('customer.receipts.index') }}">
            <i class="bi bi-receipt"></i> My Receipts
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.tracking.index') }}">
            <i class="bi bi-search"></i> Tracking
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.profile') }}">
            <i class="bi bi-person"></i> Profile
        </a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Sri Lanka Post Office Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="bg-danger text-white p-3 rounded shadow-sm" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i class="bi bi-mailbox2 display-6 mb-2"></i>
                        <div>
                            <h4 class="mb-0 fw-bold">ශ්‍රී ලංකා තැපැල් දෙපාර්තමේන්තුව</h4>
                            <h5 class="mb-0 fw-bold">Department of Posts - Sri Lanka</h5>
                            <small class="opacity-75">Customer Portal</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2 fw-bold text-dark" style="font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;">
                                <i class="bi bi-speedometer2 me-2 text-primary"></i>
                                Welcome back, {{ $user->name }}!
                            </h1>
                            <p class="mb-1 text-muted fs-5" style="font-weight: 500;">
                                <i class="bi bi-calendar-event me-2"></i>
                                {{ now()->format('l, F j, Y') }}
                            </p>
                            @if($user->location)
                                <p class="mb-0 text-muted" style="font-weight: 500;">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <strong>Post Office:</strong> {{ $user->location->location_name }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-inline-flex align-items-center bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2" style="border: 1px solid rgba(13, 110, 253, 0.2);">
                                <i class="bi bi-shield-check me-2"></i>
                                <span class="fw-bold">{{ ucfirst(str_replace('_', ' ', $user->user_type)) }} Account</span>
                            </div>

            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1 text-primary" style="font-family: 'Segoe UI', system-ui;">{{ $totalItems }}</h3>
                            <p class="mb-0 text-muted" style="font-weight: 500;">Total Items</p>
                        </div>
                        <div class="text-primary opacity-50">
                            <i class="bi bi-box display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #fff3cd 100%); border-left: 4px solid #ffc107 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1 text-warning" style="font-family: 'Segoe UI', system-ui;">{{ $pendingItems }}</h3>
                            <p class="mb-0 text-muted" style="font-weight: 500;">Pending</p>
                        </div>
                        <div class="text-warning opacity-50">
                            <i class="bi bi-clock display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #d1e7dd 100%); border-left: 4px solid #198754 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1 text-success" style="font-family: 'Segoe UI', system-ui;">{{ $acceptedItems }}</h3>
                            <p class="mb-0 text-muted" style="font-weight: 500;">Accepted</p>
                        </div>
                        <div class="text-success opacity-50">
                            <i class="bi bi-check-circle display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8d7da 100%); border-left: 4px solid #dc3545 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1 text-danger" style="font-family: 'Segoe UI', system-ui;">{{ $rejectedItems }}</h3>
                            <p class="mb-0 text-muted" style="font-weight: 500;">Rejected</p>
                        </div>
                        <div class="text-danger opacity-50">
                            <i class="bi bi-x-circle display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="font-family: 'Segoe UI', system-ui;">
                        <i class="bi bi-lightning-charge text-warning me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="{{ route('customer.services.index') }}" class="btn btn-primary btn-lg shadow-sm" style="font-weight: 600; border-radius: 8px;">
                            <i class="bi bi-box-seam me-2"></i>
                            <span>Postal Services</span>
                        </a>
                        <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-success btn-lg shadow-sm" style="font-weight: 600; border-radius: 8px;">
                            <i class="bi bi-plus-circle me-2"></i>
                            <span>Add Single Item</span>
                        </a>
                        <a href="{{ route('customer.services.bulk-upload') }}" class="btn btn-info btn-lg shadow-sm" style="font-weight: 600; border-radius: 8px;">
                            <i class="bi bi-cloud-upload me-2"></i>
                            <span>Bulk Upload</span>
                        </a>
                        <a href="{{ route('customer.receipts.index') }}" class="btn btn-warning btn-lg shadow-sm" style="font-weight: 600; border-radius: 8px;">
                            <i class="bi bi-receipt me-2"></i>
                            <span>My Receipts</span>
                        </a>
                        <a href="{{ route('customer.services.items') }}" class="btn btn-outline-secondary btn-lg shadow-sm" style="font-weight: 600; border-radius: 8px; border-width: 2px;">
                            <i class="bi bi-list-ul me-2"></i>
                            <span>View All Items</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Breakdown -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="font-family: 'Segoe UI', system-ui;">
                        <i class="bi bi-pie-chart text-primary me-2"></i>
                        Service Overview
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if(!empty($serviceBreakdown))
                        <div class="row g-3">
                            @foreach($serviceBreakdown as $service => $count)
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border: 1px solid #e9ecef; border-radius: 8px;">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark" style="font-family: 'Segoe UI', system-ui; font-weight: 600;">
                                                @switch($service)
                                                    @case('cod')
                                                        <i class="bi bi-cash text-danger me-2"></i>COD
                                                    @break
                                                    @case('register_post')
                                                        <i class="bi bi-envelope text-primary me-2"></i>Register Post
                                                    @break
                                                    @case('slp_courier')
                                                        <i class="bi bi-truck text-success me-2"></i>SLP Courier
                                                    @break
                                                    @case('remittance')
                                                        <i class="bi bi-currency-exchange text-warning me-2"></i>Remittance
                                                    @break
                                                    @default {{ ucfirst($service) }}
                                                @endswitch
                                            </h6>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: 600;">
                                            <span>{{ $count }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-3 mb-0" style="font-weight: 500;">No services used yet</p>
                            <small>Start by adding your first item!</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="font-family: 'Segoe UI', system-ui;">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Recent Uploads
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($recentUploads->count() > 0)
                        <div class="timeline">
                            @foreach($recentUploads->take(4) as $upload)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                                                 style="width: 40px; height: 40px; border: 2px solid rgba(13, 110, 253, 0.2);">
                                                <i class="bi bi-box text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-0 fw-bold" style="font-family: 'Segoe UI', system-ui; font-weight: 600;">
                                                        @if($upload->associates->count() > 0)
                                                            {{ ucfirst(str_replace('_', ' ', $upload->associates->first()->service_type)) }}
                                                        @else
                                                            Upload
                                                        @endif
                                                    </h6>
                                                    <small class="text-muted" style="font-weight: 500;">
                                                        {{ $upload->associates->count() }} items • {{ $upload->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <span class="badge {{ $upload->status === 'accept' ? 'bg-success' : ($upload->status === 'reject' ? 'bg-danger' : 'bg-warning') }}" style="font-weight: 500;">
                                                    {{ ucfirst($upload->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('customer.services.items') }}" class="btn btn-outline-primary btn-sm" style="font-weight: 600; border-radius: 6px;">
                                <i class="bi bi-arrow-right me-1"></i>
                                View All Items
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-3 mb-0" style="font-weight: 500;">No recent activity</p>
                            <small>Your recent uploads will appear here</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information (Simplified) -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold" style="font-family: 'Segoe UI', system-ui;">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Account Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-primary bg-opacity-10"
                                     style="width: 80px; height: 80px; border: 3px solid rgba(13, 110, 253, 0.2);">
                                    <i class="bi bi-person-fill text-primary fs-1"></i>
                                </div>
                                <h6 class="fw-bold" style="font-family: 'Segoe UI', system-ui; font-weight: 600;">{{ $user->name }}</h6>
                                <small class="text-muted" style="font-weight: 500;">{{ $user->email ?: 'No email provided' }}</small>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="border-start border-primary border-3 ps-3" style="border-radius: 0 4px 4px 0; background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%);">
                                        <h6 class="text-muted mb-1" style="font-weight: 600;">Account Type</h6>
                                        <p class="mb-0 fw-bold">
                                            <span class="badge bg-primary" style="font-weight: 600;">{{ ucfirst(str_replace('_', ' ', $user->user_type)) }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border-start border-primary border-3 ps-3" style="border-radius: 0 4px 4px 0; background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%);">
                                        <h6 class="text-muted mb-1" style="font-weight: 600;">Member Since</h6>
                                        <p class="mb-0 fw-bold" style="font-family: 'Segoe UI', system-ui;">{{ $user->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border-start border-primary border-3 ps-3" style="border-radius: 0 4px 4px 0; background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%);">
                                        <h6 class="text-muted mb-1" style="font-weight: 600;">Status</h6>
                                        <p class="mb-0">
                                            <span class="badge bg-success" style="font-weight: 600;">
                                                <i class="bi bi-check-circle me-1"></i>Active
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('customer.profile') }}" class="btn btn-primary" style="font-weight: 600; border-radius: 8px; padding: 10px 20px;">
                                    <i class="bi bi-pencil me-2"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 50px;
    width: 2px;
    height: calc(100% - 10px);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0.3;
}

.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 25px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endsection
