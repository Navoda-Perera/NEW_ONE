@extends('layouts.modern-pm')

@section('title', 'PM Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, {{ auth('pm')->user()->name }}!</h2>
                <p class="text-muted mb-0">Here's what's happening in your post office today.</p>
            </div>
            <div class="text-muted">
                <i class="bi bi-calendar3"></i> {{ now()->format('M d, Y') }}
            </div>
        </div>
    </div>
</div>

<!-- Location Info Card -->
@if(auth('pm')->user()->location)
<div class="location-card mb-4">
    <div class="d-flex align-items-center">
        <i class="bi bi-geo-alt-fill fs-3 me-3"></i>
        <div>
            <h5 class="mb-1">{{ auth('pm')->user()->location->name }}</h5>
            <p class="mb-0 opacity-75">{{ auth('pm')->user()->location->code }} - {{ auth('pm')->user()->location->city }}</p>
            @if(auth('pm')->user()->location->phone)
                <small class="opacity-75">📞 {{ auth('pm')->user()->location->phone }}</small>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number stat-primary">{{ $customerUsers }}</div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-icon stat-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number stat-success">{{ $activeCustomers }}</div>
                    <div class="stat-label">Active Customers</div>
                </div>
                <div class="stat-icon stat-success">
                    <i class="bi bi-person-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number stat-info">{{ $externalCustomers }}</div>
                    <div class="stat-label">External Customers</div>
                </div>
                <div class="stat-icon stat-info">
                    <i class="bi bi-globe"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number stat-warning">{{ $pendingItemsCount ?? 0 }}</div>
                    <div class="stat-label">Pending Items</div>
                </div>
                <div class="stat-icon stat-warning">
                    <i class="bi bi-clock"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <h3 class="section-title">Quick Actions</h3>
    <div class="row g-3">
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('pm.customers.index') }}" class="action-btn">
                <i class="bi bi-people"></i>
                <span>Manage Customers</span>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('pm.single-item.index') }}" class="action-btn">
                <i class="bi bi-box-seam"></i>
                <span>Add Single Item</span>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('pm.bulk-upload') }}" class="action-btn">
                <i class="bi bi-cloud-upload"></i>
                <span>Bulk Upload</span>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="{{ route('pm.item-management.index') }}" class="action-btn">
                <i class="bi bi-search"></i>
                <span>Item Management</span>
            </a>
        </div>
    </div>
</div>

<!-- Additional Quick Actions Row -->
<div class="row g-3 mt-2">
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('pm.customer-uploads') }}" class="action-btn">
            <i class="bi bi-inbox"></i>
            <span>Customer Uploads</span>
            @if($pendingItemsCount > 0)
                <div class="notification-badge mt-2">{{ $pendingItemsCount }} pending</div>
            @endif
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <a href="#" class="action-btn">
            <i class="bi bi-graph-up"></i>
            <span>View Reports</span>
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <a href="#" class="action-btn">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
    </div>
</div>

@endsection
