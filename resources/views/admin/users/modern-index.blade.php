@extends('layouts.modern-admin')

@section('title', 'User Management')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary me-3" title="Back to Dashboard">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <div>
                    <p class="text-muted mb-0">Manage all system users and their permissions</p>
                </div>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Create New User
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Search and Filter Section -->
<div class="stat-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-semibold mb-0 text-muted">Search & Filter Users</h6>
        <small class="text-muted">
            @if(isset($users))
                Showing {{ $users->count() }} of {{ $users->total() }} users
                @if(request()->hasAny(['search', 'role', 'type']))
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-1">Filtered</span>
                @endif
            @endif
        </small>
    </div>
    
    <div class="row g-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" id="searchNic" 
                       placeholder="Search by NIC, name, or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="filterRole">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="pm" {{ request('role') === 'pm' ? 'selected' : '' }}>Postmaster</option>
                <option value="postman" {{ request('role') === 'postman' ? 'selected' : '' }}>Postman</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="filterType">
                <option value="">All Types</option>
                <option value="internal" {{ request('type') === 'internal' ? 'selected' : '' }}>Internal</option>
                <option value="external" {{ request('type') === 'external' ? 'selected' : '' }}>External</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                <i class="bi bi-arrow-clockwise me-1"></i>Reset
            </button>
        </div>
        <div class="col-md-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary flex-fill" onclick="exportUsers()">
                    <i class="bi bi-download"></i>
                </button>
                <button type="button" class="btn btn-outline-info flex-fill" onclick="refreshUsers()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold text-muted">ID</th>
                    <th class="fw-semibold text-muted">User</th>
                    <th class="fw-semibold text-muted">Contact</th>
                    <th class="fw-semibold text-muted">Type & Role</th>
                    <th class="fw-semibold text-muted">Location</th>
                    <th class="fw-semibold text-muted">Status</th>
                    <th class="fw-semibold text-muted">Created</th>
                    <th class="fw-semibold text-muted text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                <tr>
                    <td class="text-muted">#{{ $user->id }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px; font-size: 0.9rem; font-weight: 600;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-medium">{{ $user->name }}</div>
                                <div class="text-muted small">{{ $user->nic }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-muted small">
                            @if($user->email)
                                <div><i class="bi bi-envelope me-1"></i>{{ $user->email }}</div>
                            @endif
                            @if($user->mobile)
                                <div><i class="bi bi-phone me-1"></i>{{ $user->mobile }}</div>
                            @endif
                            @if(!$user->email && !$user->mobile)
                                <span class="text-muted">No contact info</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div>
                            @if($user->user_type === 'internal')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20">Internal</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20">External</span>
                            @endif
                        </div>
                        <div class="mt-1">
                            @if($user->role === 'admin')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20">Admin</span>
                            @elseif($user->role === 'pm')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20">Postmaster</span>
                            @elseif($user->role === 'postman')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20">Postman</span>
                            @else
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20">Customer</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($user->location)
                            <div class="fw-medium">{{ $user->location->name }}</div>
                            <div class="text-muted small">{{ $user->location->code }} - {{ $user->location->city }}</div>
                        @else
                            <span class="text-muted">Not assigned</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20">
                                <i class="bi bi-check-circle me-1"></i>Active
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20">
                                <i class="bi bi-x-circle me-1"></i>Inactive
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.users.edit', $user) }}" 
                               class="btn btn-outline-primary btn-sm"
                               title="Edit User">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }} btn-sm"
                                            onclick="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')"
                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} User">
                                        <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-3 opacity-25"></i>
                            <h5>No users found</h5>
                            <p class="mb-0">Get started by creating your first user.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
</div>

<script>
// Real-time search functionality
let searchTimeout;

document.getElementById('searchNic').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterUsers();
    }, 500);
});

document.getElementById('filterRole').addEventListener('change', filterUsers);
document.getElementById('filterType').addEventListener('change', filterUsers);

function filterUsers() {
    const search = document.getElementById('searchNic').value;
    const role = document.getElementById('filterRole').value;
    const type = document.getElementById('filterType').value;
    
    let url = new URL(window.location.href);
    url.searchParams.delete('page'); // Reset pagination
    
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    
    if (role) {
        url.searchParams.set('role', role);
    } else {
        url.searchParams.delete('role');
    }
    
    if (type) {
        url.searchParams.set('type', type);
    } else {
        url.searchParams.delete('type');
    }
    
    window.location.href = url.toString();
}

function clearFilters() {
    document.getElementById('searchNic').value = '';
    document.getElementById('filterRole').value = '';
    document.getElementById('filterType').value = '';
    
    // Redirect to clean URL
    window.location.href = window.location.pathname;
}

function exportUsers() {
    // Add export functionality
    alert('Export functionality coming soon!');
}

function refreshUsers() {
    window.location.reload();
}

// Highlight search terms in table
document.addEventListener('DOMContentLoaded', function() {
    const searchTerm = '{{ request("search") }}';
    if (searchTerm) {
        highlightSearchTerm(searchTerm);
    }
});

function highlightSearchTerm(term) {
    if (!term) return;
    
    const tableBody = document.querySelector('tbody');
    if (!tableBody) return;
    
    const regex = new RegExp(`(${term})`, 'gi');
    
    tableBody.querySelectorAll('td').forEach(cell => {
        if (cell.textContent.includes(term)) {
            cell.innerHTML = cell.innerHTML.replace(regex, '<mark class="bg-warning bg-opacity-25">$1</mark>');
        }
    });
}
</script>
@endsection