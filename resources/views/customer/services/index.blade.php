@extends('layouts.app')

@section('title', 'Postal Services')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('customer.services.index') }}">
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
                <i class="bi bi-box-seam"></i> Postal Services
                <small class="text-muted">Manage your shipments and items</small>
            </h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Items</h6>
                            <h3 class="mb-0">{{ $totalItems }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-box fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending</h6>
                            <h3 class="mb-0">{{ $pendingItems }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Accepted</h6>
                            <h3 class="mb-0">{{ $acceptedItems }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Rejected</h6>
                            <h3 class="mb-0">{{ $rejectedItems }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-x-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Options -->
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-plus-circle fs-1 text-success mb-3"></i>
                    <h5 class="card-title">Add Single Item</h5>
                    <p class="card-text">Add individual items (<span class="fw-bold">single_item</span> category) with specific service types like Register Post, SLP Courier, COD, or Remittance.</p>
                    <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Add Single Item
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cloud-upload fs-1 text-primary mb-3"></i>
                    <h5 class="card-title">Bulk Upload</h5>
                    <p class="card-text">Upload multiple items at once using CSV or Excel files (<span class="fw-bold">temporary_list</span> category) for efficient bulk processing.</p>
                    <a href="{{ route('customer.services.bulk-upload') }}" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Bulk Upload
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('customer.services.items') }}" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="bi bi-list-ul me-2"></i>View All Items
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('customer.services.items') }}?status=pending" class="btn btn-outline-warning w-100 mb-2">
                                <i class="bi bi-clock me-2"></i>Pending Items
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('customer.services.items') }}?status=accept" class="btn btn-outline-success w-100 mb-2">
                                <i class="bi bi-check-circle me-2"></i>Accepted Items
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('customer.services.items') }}?status=reject" class="btn btn-outline-danger w-100 mb-2">
                                <i class="bi bi-x-circle me-2"></i>Rejected Items
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info w-100 mb-2" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
