@extends('layouts.app')

@section('title', 'Upload Details')

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
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Upload Details #{{ $upload->id }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('customer.services.items') }}">My Items</a></li>
                            <li class="breadcrumb-item active">Upload #{{ $upload->id }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('customer.services.items') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Items
                </a>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Items in this Upload</h5>
                </div>
                <div class="card-body">
                    @if($upload->associates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Receiver</th>
                                        <th>Address</th>
                                        <th>Barcode</th>
                                        <th>Weight</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upload->associates as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->receiver_name }}</strong>
                                                @if($item->receiver_mobile)
                                                    <br><small class="text-muted">{{ $item->receiver_mobile }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ Str::limit($item->receiver_address, 80) }}</small>
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
                                            <td>
                                                @if($item->amount)
                                                    LKR {{ number_format($item->amount, 2) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($item->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pending</span>
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
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm"
                                                            onclick="showItemDetails({{ $item->id }})"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    @if($item->status === 'pending')
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
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-3">No items found in this upload.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
