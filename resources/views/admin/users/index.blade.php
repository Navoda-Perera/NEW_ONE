@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary me-3" title="Back to Dashboard">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h2 class="fw-bold text-dark mb-0">User Management</h2>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Create New User
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>NIC</th>
                                    <th>Email</th>
                                    <th>User Type</th>
                                    <th>Role</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users ?? [] as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->nic }}</td>
                                    <td>{{ $user->email ?? 'N/A' }}</td>
                                    <td>
                                        @if($user->user_type === 'internal')
                                            <span class="badge bg-primary">Internal</span>
                                        @else
                                            <span class="badge bg-secondary">External</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">Admin</span>
                                        @elseif($user->role === 'pm')
                                            <span class="badge bg-warning">Postmaster</span>
                                        @elseif($user->role === 'postman')
                                            <span class="badge bg-success">Postman</span>
                                        @else
                                            <span class="badge bg-info">Customer</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->location)
                                            <small>
                                                <strong>{{ $user->location->name }}</strong><br>
                                                <span class="text-muted">{{ $user->location->code }} - {{ $user->location->city }}</span>
                                            </small>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}"
                                                            onclick="return confirm('Are you sure?')">
                                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No users found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
