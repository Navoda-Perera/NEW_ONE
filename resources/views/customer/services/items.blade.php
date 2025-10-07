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
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'pending']) }}"
                   class="btn {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                    Pending
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'accept']) }}"
                   class="btn {{ request('status') === 'accept' ? 'btn-success' : 'btn-outline-success' }}">
                    Accepted
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'reject']) }}"
                   class="btn {{ request('status') === 'reject' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Rejected
                </a>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if($items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Receiver</th>
                                        <th>Service Type</th>
                                        <th>Barcode</th>
                                        <th>Weight</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $item->receiver_name }}</strong><br>
                                                    <small class="text-muted">{{ Str::limit($item->receiver_address, 50) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $serviceType = $serviceTypeLabels[$item->service_type] ?? $item->service_type;
                                                @endphp
                                                <span class="badge bg-info">{{ $serviceType }}</span>
                                            </td>
                                            <td>
                                                @if($item->barcode)
                                                    <span class="badge bg-success">{{ $item->barcode }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                    <br><small class="text-muted">PM will assign</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->weight)
                                                    {{ number_format($item->weight) }}g
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>LKR {{ number_format($item->amount, 2) }}</td>
                                            <td>
                                                @switch($item->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pending PM Approval</span>
                                                        @break
                                                    @case('accept')
                                                        <span class="badge bg-success">Accepted</span>
                                                        @break
                                                    @case('reject')
                                                        <span class="badge bg-danger">Rejected</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $item->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm"
                                                            onclick="showItemDetails({{ $item->id }})"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    @if($item->status === 'accept')
                                                        <button class="btn btn-outline-warning btn-sm"
                                                                onclick="editItem({{ $item->id }})"
                                                                title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $items->links() }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-3">No items found.</p>
                            <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>Add Your First Item
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
