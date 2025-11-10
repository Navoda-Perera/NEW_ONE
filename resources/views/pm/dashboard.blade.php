@extends('layouts.app')

@section('title', 'Postmaster Dashboard')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('pm.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.customers.index') }}">
            <i class="bi bi-people"></i> Customers
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.single-item.index') }}">
            <i class="bi bi-box-seam"></i> Add Single Item
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.item-management.index') }}">
            <i class="bi bi-search"></i> Item Management
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.bulk-upload') }}">
            <i class="bi bi-cloud-upload"></i> Bulk Upload
        </a>
    </li>
@endsection

@section('styles')
<style>
    .hover-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .text-decoration-none:hover {
        text-decoration: none !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0 bg-light border-end">
            <div class="d-flex flex-column vh-100">
                <div class="p-3">
                    <h6 class="text-muted">Quick Links</h6>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('pm.dashboard') }}" class="list-group-item list-group-item-action active">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                        <a href="{{ route('pm.customers.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-people me-2"></i>Customers
                        </a>
                        <a href="{{ route('pm.single-item.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-box-seam me-2"></i>Add Single Item
                        </a>
                        <a href="{{ route('pm.item-management.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-search me-2"></i>Item Management
                        </a>
                        <a href="{{ route('pm.customer-uploads') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-lines-fill me-2"></i>Customer Uploads
                            @if($pendingItemsCount > 0)
                                <span class="badge bg-info text-dark ms-auto">{{ $pendingItemsCount }}</span>
                            @endif
                        </a>
                    </div>


                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <!-- Sri Lanka Post Office Header -->
            <div class="bg-danger text-white p-3 mb-4" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i class="bi bi-mailbox2 display-6 mb-2"></i>
                        <div>
                            <h3 class="mb-0 fw-bold">ශ්‍රී ලංකා තැපැල් දෙපාර්තමේන්තුව</h3>
                            <h4 class="mb-0 fw-bold">Department of Posts - Sri Lanka</h4>
                            <small class="opacity-75">Postmaster Management System</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 p-3">
                <h2 class="fw-bold text-dark mb-0">
                    <i class="bi bi-briefcase"></i> Postmaster Dashboard
                    <br><small class="text-muted">Welcome, {{ auth('pm')->user()->name }}</small>
                </h2>
                @include('pm.partials.location-info')
            </div>

            <!-- Stats Cards -->
            <div class="row px-3">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold">{{ $customerUsers }}</h3>
                                    <p class="mb-0 opacity-75">Total Customers</p>
                                </div>
                                <div class="text-white-50">
                                    <i class="bi bi-people display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold">{{ $activeCustomers }}</h3>
                                    <p class="mb-0 opacity-75">Active Customers</p>
                                </div>
                                <div class="text-white-50">
                                    <i class="bi bi-person-check display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold">{{ $externalCustomers }}</h3>
                                    <p class="mb-0 opacity-75">External Customers</p>
                                </div>
                                <div class="text-white-50">
                                    <i class="bi bi-globe display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="px-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('pm.customers.index') }}" class="btn btn-outline-primary btn-lg w-100 h-100 d-flex flex-column justify-content-center">
                                    <i class="bi bi-people display-6 mb-2"></i>
                                    <span>View Customers</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
