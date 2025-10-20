@extends('layouts.app')

@section('title', 'My Items')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.services.index') }}">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('customer.services.index') }}" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h2 class="fw-bold text-dark mb-0">My Items</h2>
                </div>
                <div>
                    <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Add New Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <a href="{{ route('customer.services.items') }}"
                   class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                    All Items
                    <span class="badge bg-light text-dark ms-1">{{ $statusCounts['total'] }}</span>
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'pending']) }}"
                   class="btn {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                    Pending
                    <span class="badge bg-light text-dark ms-1">{{ $statusCounts['pending'] }}</span>
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'accept']) }}"
                   class="btn {{ request('status') === 'accept' ? 'btn-success' : 'btn-outline-success' }}">
                    Accepted
                    <span class="badge bg-light text-dark ms-1">{{ $statusCounts['accepted'] }}</span>
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'reject']) }}"
                   class="btn {{ request('status') === 'reject' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Rejected
                    <span class="badge bg-light text-dark ms-1">{{ $statusCounts['rejected'] }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if($uploads->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Upload ID</th>
                                        <th>Service Type</th>
                                        <th>Item Quantity</th>
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
                                                <span class="badge bg-primary fs-6">{{ $upload->total_items ?? $upload->associates_count }}</span>
                                                @if($upload->total_items == 1)
                                                    <br><small class="text-muted">Single Item</small>
                                                @else
                                                    <br><small class="text-muted">List Items</small>
                                                @endif
                                            </td>
                                            <td>{{ $upload->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <button class="btn btn-outline-primary btn-sm"
                                                        onclick="viewUploadDetails({{ $upload->id }})"
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
                            {{ $uploads->links() }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-3">No uploads found.</p>
                            <a href="{{ route('customer.services.bulk-upload') }}" class="btn btn-success me-2">
                                <i class="bi bi-upload me-2"></i>Bulk Upload
                            </a>
                            <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-outline-success">
                                <i class="bi bi-plus-circle me-2"></i>Add Single Item
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="itemDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewUploadDetails(uploadId) {
    // Redirect to a dedicated page to view upload details
    window.location.href = `/customer/services/view-upload/${uploadId}`;
}

function showItemDetails(itemId) {
    // For now, just show a placeholder
    const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
    document.getElementById('itemDetailsContent').innerHTML = '<p>Item details will be loaded here...</p>';
    modal.show();
}

function editItem(itemId) {
    // Redirect to edit form or show edit modal
    alert('Edit functionality will be implemented');
}
</script>
@endsection
