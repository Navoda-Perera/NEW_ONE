@extends('layouts.app')

@section('title', 'Customer Uploads')

@section('styles')
<style>
.table-responsive {
    max-height: 70vh;
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('pm.dashboard') }}" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h2 class="fw-bold text-dark mb-0">Customer Uploads</h2>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('pm.customer-uploads') }}" class="d-flex">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by sender name, email, NIC, or upload ID..."
                                   autocomplete="off">
                            @if(request('service_type'))
                                <input type="hidden" name="service_type" value="{{ request('service_type') }}">
                            @endif
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                            @if(request('search') || request('service_type'))
                                <a href="{{ route('pm.customer-uploads') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    @if($uploads->total() > 0)
                        <small class="text-muted">
                            Showing {{ $uploads->firstItem() }}-{{ $uploads->lastItem() }} of {{ $uploads->total() }} uploads
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Service Type Filter -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <a href="{{ route('pm.customer-uploads', ['search' => request('search')]) }}"
                   class="btn {{ !request('service_type') ? 'btn-primary' : 'btn-outline-primary' }}">
                    All Service Types
                </a>
                <a href="{{ route('pm.customer-uploads', ['service_type' => 'slp_courier', 'search' => request('search')]) }}"
                   class="btn {{ request('service_type') === 'slp_courier' ? 'btn-success' : 'btn-outline-success' }}">
                    SLP Courier
                </a>
                <a href="{{ route('pm.customer-uploads', ['service_type' => 'cod', 'search' => request('search')]) }}"
                   class="btn {{ request('service_type') === 'cod' ? 'btn-warning' : 'btn-outline-warning' }}">
                    COD
                </a>
                <a href="{{ route('pm.customer-uploads', ['service_type' => 'register_post', 'search' => request('search')]) }}"
                   class="btn {{ request('service_type') === 'register_post' ? 'btn-info' : 'btn-outline-info' }}">
                    Register Post
                </a>
            </div>
        </div>
    </div>

    <!-- Customer Uploads Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if($uploads->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Sender Name</th>
                                        <th>NIC</th>
                                        <th>Service Type</th>
                                        <th>Items Count</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($uploads as $upload)
                                        <tr>
                                            <td>
                                                <strong>#{{ $upload->id }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $upload->user->name }}</strong>
                                                <br><small class="text-muted">{{ $upload->user->email }}</small>
                                            </td>
                                            <td>
                                                {{ $upload->user->nic ?? 'Not provided' }}
                                            </td>
                                            <td>
                                                @php
                                                    // Get service type from the first associate since all items in an upload have the same service type
                                                    $firstAssociate = $upload->associates->first();
                                                    $serviceType = $firstAssociate ? ($serviceTypeLabels[$firstAssociate->service_type] ?? $firstAssociate->service_type) : 'Not specified';
                                                @endphp
                                                @if($firstAssociate && $firstAssociate->service_type)
                                                    <span class="badge bg-info">{{ $serviceType }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Not specified</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $upload->total_items }}</span>
                                                <small class="text-muted">({{ $upload->pending_items }} pending)</small>
                                            </td>
                                            <td>{{ $upload->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <button class="btn btn-outline-primary btn-sm"
                                                        onclick="viewCustomerUploadDetails({{ $upload->id }})"
                                                        title="View Items">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $uploads->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-3">No customer uploads found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewCustomerUploadDetails(uploadId) {
    // Redirect to view customer upload details
    window.location.href = `/pm/view-customer-upload/${uploadId}`;
}
</script>
@endsection
