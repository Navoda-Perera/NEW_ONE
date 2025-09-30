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
        <a class="nav-link" href="{{ route('pm.postmen.index') }}">
            <i class="bi bi-person-badge"></i> Postmen
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-briefcase"></i> Postmaster Dashboard
                    <small class="text-muted">Welcome, {{ auth()->user()->name }}</small>
                </h2>
                @include('pm.partials.location-info')
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $customerUsers }}</h4>
                            <p class="mb-0">Total Customers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $activeCustomers }}</h4>
                            <p class="mb-0">Active Customers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $pendingItemsCount }}</h4>
                            <p class="mb-0">Pending Items</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock-history fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('pm.customers.index') }}" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-people"></i><br>
                                View Customers
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('pm.customers.create') }}" class="btn btn-success btn-lg w-100 mb-3">
                                <i class="bi bi-person-plus"></i><br>
                                Create Customer
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('pm.items.pending') }}" class="btn btn-warning btn-lg w-100 mb-3">
                                <i class="bi bi-clock-history"></i><br>
                                Pending Items
                                @if($pendingItemsCount > 0)
                                    <span class="badge bg-danger">{{ $pendingItemsCount }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('pm.postmen.index') }}" class="btn btn-info btn-lg w-100 mb-3">
                                <i class="bi bi-person-badge"></i><br>
                                View Postmen
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('pm.postmen.create') }}" class="btn btn-secondary btn-lg w-100 mb-3">
                                <i class="bi bi-person-plus-fill"></i><br>
                                Create Postman
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Projects</h5>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-briefcase fs-1"></i>
                        <p class="mt-2">No projects to display.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
