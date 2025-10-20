@extends('layouts.app')

@section('title', 'Single Item Management')

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
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('pm.single-item.index') }}">
            <i class="bi bi-box-seam"></i> Add Single Item
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.bulk-upload') }}">
            <i class="bi bi-cloud-upload"></i> Bulk Upload
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.postmen.index') }}">
            <i class="bi bi-person-badge"></i> Postmen
        </a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Single Item Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Single Item</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Single Item Services:</strong> Add individual postal items for SLP Courier, COD, and Register Post services.
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- SLP Courier Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-truck display-4 text-primary"></i>
                    </div>
                    <h5 class="card-title text-primary">SLP Courier</h5>
                    <p class="card-text text-muted">
                        Add single courier item with weight-based pricing calculation.
                    </p>
                    <ul class="list-unstyled text-start small mb-3">
                        <li><i class="bi bi-check text-success"></i> Sender Details</li>
                        <li><i class="bi bi-check text-success"></i> Receiver Details</li>
                        <li><i class="bi bi-check text-success"></i> Weight & Postage</li>
                        <li><i class="bi bi-check text-success"></i> Barcode Tracking</li>
                    </ul>
                    <a href="{{ route('pm.single-item.slp-form') }}" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-plus-circle"></i> Add SLP Item
                    </a>
                </div>
            </div>
        </div>

        <!-- COD Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-cash-coin display-4 text-warning"></i>
                    </div>
                    <h5 class="card-title text-warning">Cash on Delivery (COD)</h5>
                    <p class="card-text text-muted">
                        Add COD item with amount collection and postage calculation.
                    </p>
                    <ul class="list-unstyled text-start small mb-3">
                        <li><i class="bi bi-check text-success"></i> COD Amount</li>
                        <li><i class="bi bi-check text-success"></i> Postage Calculation</li>
                        <li><i class="bi bi-check text-success"></i> Payment Collection</li>
                        <li><i class="bi bi-check text-success"></i> Receipt Generation</li>
                    </ul>
                    <a href="{{ route('pm.single-item.cod-form') }}" class="btn btn-warning btn-lg w-100">
                        <i class="bi bi-plus-circle"></i> Add COD Item
                    </a>
                </div>
            </div>
        </div>

        <!-- Register Post Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-envelope-check display-4 text-success"></i>
                    </div>
                    <h5 class="card-title text-success">Register Post</h5>
                    <p class="card-text text-muted">
                        Add registered postal item with tracking and delivery confirmation.
                    </p>
                    <ul class="list-unstyled text-start small mb-3">
                        <li><i class="bi bi-check text-success"></i> Registered Tracking</li>
                        <li><i class="bi bi-check text-success"></i> Delivery Confirmation</li>
                        <li><i class="bi bi-check text-success"></i> Weight-based Pricing</li>
                        <li><i class="bi bi-check text-success"></i> Official Receipt</li>
                    </ul>
                    <a href="{{ route('pm.single-item.register-form') }}" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-plus-circle"></i> Add Register Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-geo-alt text-info"></i> Current Location
                            </h6>
                            <p class="card-text text-muted mb-0">
                                {{ $location ? $location->name : 'No location assigned' }} -
                                All items will be created under this location
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <small class="text-muted">
                                PM: {{ $user->name }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.display-4 {
    font-size: 3rem;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>
@endsection
