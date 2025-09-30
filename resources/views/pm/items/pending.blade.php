@extends('layouts.app')

@section('title', 'Pending Items - PM Dashboard')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('pm.items.pending') }}">
            <i class="bi bi-clock-history"></i> Pending Items
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.customers.index') }}">
            <i class="bi bi-people"></i> Customers
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-0">Pending Items for Approval</h2>
                    <p class="text-muted mb-0">Review and approve customer submitted items</p>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-warning fs-6 me-2">{{ $pendingItems->total() }} Pending</span>
                    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Items List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if($pendingItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Receiver Details</th>
                                        <th>Service Type</th>
                                        <th>Weight</th>
                                        <th>Amount</th>
                                        <th>Postage</th>
                                        <th>Barcode</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingItems as $item)
                                        <tr id="item-{{ $item->id }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $item->temporaryUpload->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $item->temporaryUpload->user->email }}</small>
                                                    @if($item->temporaryUpload->user->company_name)
                                                        <br><small class="text-info">{{ $item->temporaryUpload->user->company_name }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $item->receiver_name }}</strong><br>
                                                    <small class="text-muted">{{ Str::limit($item->receiver_address, 60) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $serviceType = $serviceTypeLabels[$item->temporaryUpload->service_type] ?? $item->temporaryUpload->service_type;
                                                @endphp
                                                <span class="badge bg-info">{{ $serviceType }}</span>
                                            </td>
                                            <td>
                                                @if($item->weight)
                                                    <span class="fw-semibold">{{ number_format($item->weight) }}g</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-success">LKR {{ number_format($item->amount, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-primary">LKR {{ number_format($item->postage, 2) }}</span>
                                            </td>
                                            <td>
                                                @if($item->barcode)
                                                    <span class="badge bg-success">{{ $item->barcode }}</span>
                                                    <br><small class="text-muted">Customer provided</small>
                                                @else
                                                    <span class="badge bg-secondary">Will auto-generate</span>
                                                    <br><small class="text-muted">ITM-{{ str_pad($item->id, 8, '0', STR_PAD_LEFT) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $item->created_at->format('M d, Y') }}</span>
                                                <br><small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical" role="group">
                                                    <button class="btn btn-success btn-sm mb-1"
                                                            onclick="acceptItem({{ $item->id }})"
                                                            title="Accept Item">
                                                        <i class="bi bi-check-circle"></i> Accept
                                                    </button>
                                                    <button class="btn btn-danger btn-sm"
                                                            onclick="rejectItem({{ $item->id }})"
                                                            title="Reject Item">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $pendingItems->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No Pending Items</h4>
                            <p class="text-muted">All customer submissions have been processed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Actions -->
<script>
function acceptItem(itemId) {
    if (confirm('Are you sure you want to accept this item? This will assign a barcode and move it to the tracking system.')) {
        fetch(`/pm/items/${itemId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row or update status
                document.getElementById(`item-${itemId}`).style.opacity = '0.5';
                document.getElementById(`item-${itemId}`).innerHTML = '<td colspan="9" class="text-center text-success"><i class="bi bi-check-circle"></i> Item Accepted Successfully</td>';

                // Show success message
                showAlert('success', data.message || 'Item accepted successfully!');

                // Reload page after 2 seconds
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', data.message || 'Error accepting item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Network error occurred');
        });
    }
}

function rejectItem(itemId) {
    if (confirm('Are you sure you want to reject this item? The customer will be notified.')) {
        fetch(`/pm/items/${itemId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row or update status
                document.getElementById(`item-${itemId}`).style.opacity = '0.5';
                document.getElementById(`item-${itemId}`).innerHTML = '<td colspan="9" class="text-center text-danger"><i class="bi bi-x-circle"></i> Item Rejected</td>';

                // Show success message
                showAlert('warning', data.message || 'Item rejected successfully');

                // Reload page after 2 seconds
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', data.message || 'Error rejecting item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Network error occurred');
        });
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endsection
