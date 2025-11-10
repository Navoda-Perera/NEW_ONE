@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.users.index') }}">
            <i class="bi bi-people"></i> Manage Users
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">
                <i class="bi bi-speedometer2"></i> Admin Dashboard
                <small class="text-muted">Welcome, {{ auth('admin')->user()->name }}</small>
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $totalUsers }}</h4>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
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
                            <h4>{{ $adminUsers }}</h4>
                            <p class="mb-0">Admin Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-shield-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $pmUsers }}</h4>
                            <p class="mb-0">Postmasters</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-briefcase fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $postmanUsers }}</h4>
                            <p class="mb-0">Postmen</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-truck fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $customerUsers }}</h4>
                            <p class="mb-0">Customers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-badge fs-1"></i>
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
                        <div class="col-md-4">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-person-plus"></i><br>
                                Create New User
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-info btn-lg w-100 mb-3">
                                <i class="bi bi-people"></i><br>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-4">
                            <div class="btn btn-secondary btn-lg w-100 mb-3">
                                <i class="bi bi-gear"></i><br>
                                System Settings
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
