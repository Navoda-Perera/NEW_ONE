@extends('layouts.app')

@section('title', 'Customers Management')

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

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Customers</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Customers Management</h2>
    <a href="{{ route('pm.customers.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Add New Customer
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Search Bar -->
<div class="row mb-4">
    <div class="col-md-8">
        <form method="GET" action="{{ route('pm.customers.index') }}" class="d-flex">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       class="form-control"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by name, email, NIC, or mobile..."
                       autocomplete="off">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search me-1"></i>Search
                </button>
                @if(request('search'))
                    <a href="{{ route('pm.customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
    <div class="col-md-4 text-end">
        @if($customers->total() > 0)
            <small class="text-muted">
                Showing {{ $customers->firstItem() }}-{{ $customers->lastItem() }} of {{ $customers->total() }} customers
            </small>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list-ul me-2"></i>All Customers
            <span class="badge bg-primary ms-2">{{ $customers->total() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>NIC</th>
                            <th>Mobile</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $customer->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->nic }}</td>
                            <td>{{ $customer->mobile }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $customer->location ? $customer->location->name : 'Not Assigned' }}
                                </span>
                            </td>
                            <td>
                                @if($customer->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('pm.users.toggle-status', $customer) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $customer->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                title="{{ $customer->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $customer->is_active ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $customers->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-people display-1 text-muted"></i>
                <h5 class="text-muted mt-3">No customers found</h5>
                <p class="text-muted">No customers are assigned to your location yet.</p>
                <a href="{{ route('pm.customers.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Add First Customer
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
