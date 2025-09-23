@extends('layouts.app')

@section('title', 'Manage Postmen')

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
        <a class="nav-link active" href="{{ route('pm.postmen.index') }}">
            <i class="bi bi-person-badge"></i> Postmen
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0">Postmen Management</h2>
        <a href="{{ route('pm.postmen.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus"></i> Create Postman
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>NIC</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($postmen as $postman)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ strtoupper(substr($postman->name, 0, 2)) }}
                                        </div>
                                        {{ $postman->name }}
                                    </div>
                                </td>
                                <td>{{ $postman->nic }}</td>
                                <td>
                                    @if($postman->email)
                                        <a href="mailto:{{ $postman->email }}" class="text-decoration-none">
                                            {{ $postman->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($postman->user_type) }}</span>
                                </td>
                                <td>
                                    @if($postman->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $postman->created_at->format('M d, Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('pm.users.toggle-status', $postman) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $postman->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            @if($postman->is_active)
                                                <i class="bi bi-x-circle"></i> Deactivate
                                            @else
                                                <i class="bi bi-check-circle"></i> Activate
                                            @endif
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-person-badge fs-1"></i>
                                    <p class="mt-2">No postmen found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($postmen->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $postmen->links() }}
        </div>
    @endif
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    background-color: #0d6efd;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection
