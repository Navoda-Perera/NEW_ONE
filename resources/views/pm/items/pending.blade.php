@extends('layouts.app')

@section('title', 'Pending Items')

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
                    <h2 class="fw-bold text-dark mb-0">
                        @if(isset($serviceType))
                            {{ $serviceTypeLabel }} - Pending Items
                        @else
                            Pending Items for Approval
                        @endif
                    </h2>
                    <p class="text-muted mb-0">
                        @if(isset($serviceType))
                            Review and approve {{ strtolower($serviceTypeLabel) }} items
                            <a href="{{ route('pm.items.pending') }}" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-left"></i> View All Types
                            </a>
                        @else
                            Review and approve customer submitted items
                        @endif
                    </p>
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

    <!-- Search Bar -->
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" action="{{ isset($serviceType) ? route('pm.items.pending.by-service-type', $serviceType) : route('pm.items.pending') }}" id="searchForm">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text"
                           class="form-control"
                           id="nicSearch"
                           name="search"
                           placeholder="Search by Customer NIC, Name, or Email..."
                           value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                    @if(request('search'))
                        <a href="{{ isset($serviceType) ? route('pm.items.pending.by-service-type', $serviceType) : route('pm.items.pending') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <div class="text-muted small">
                <i class="bi bi-info-circle"></i>
                @if(request('search'))
                    <strong>{{ $pendingItems->total() }}</strong> results found for "<strong>{{ request('search') }}</strong>"
                @else
                    Search by NIC, customer name, or email address
                @endif
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
                            <table class="table table-hover" id="pendingItemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Customer NIC</th>
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
                                        <tr id="item-{{ $item->id }}" class="item-row" data-customer-nic="{{ $item->temporaryUpload->user->nic }}">
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
                                                <span class="badge bg-secondary customer-nic">{{ $item->temporaryUpload->user->nic }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $item->receiver_name }}</strong><br>
                                                    <small class="text-muted">{{ Str::limit($item->receiver_address, 60) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    // Get service type directly from TemporaryUploadAssociate
                                                    $serviceTypeValue = $item->service_type ?? 'register_post';
                                                    $serviceTypeLabels = [
                                                        'register_post' => 'Register Post',
                                                        'slp_courier' => 'SLP Courier',
                                                        'cod' => 'COD',
                                                        'remittance' => 'Remittance'
                                                    ];
                                                    $serviceType = $serviceTypeLabels[$serviceTypeValue] ?? $serviceTypeValue;
                                                @endphp
                                                <span class="badge bg-info">{{ $serviceType }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="input-group input-group-sm" style="width: 100px;">
                                                        <input type="number"
                                                               class="form-control form-control-sm weight-input"
                                                               id="weight-{{ $item->id }}"
                                                               value="{{ $item->weight ?? '' }}"
                                                               placeholder="Weight"
                                                               min="1"
                                                               step="1"
                                                               style="font-size: 12px;"
                                                               onchange="validateWeight({{ $item->id }})">
                                                        <span class="input-group-text" style="font-size: 10px;">g</span>
                                                    </div>
                                                    @if($item->weight)
                                                        <small class="text-muted mt-1">Original: {{ number_format($item->weight) }}g</small>
                                                    @else
                                                        <small class="text-warning mt-1">
                                                            <i class="bi bi-exclamation-triangle"></i> Required
                                                        </small>
                                                    @endif
                                                </div>
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
                                                    <div class="d-flex flex-column align-items-start">
                                                        <input type="text"
                                                               class="form-control form-control-sm mb-1 barcode-input"
                                                               id="barcode-{{ $item->id }}"
                                                               placeholder="Type or scan barcode"
                                                               style="width: 140px; font-size: 12px;"
                                                               onkeypress="handleBarcodeKeypress(event, {{ $item->id }})"
                                                               autocomplete="off">
                                                        <button class="btn btn-outline-primary btn-xs"
                                                                onclick="updateBarcode({{ $item->id }})"
                                                                style="font-size: 10px; padding: 2px 6px;">
                                                            <i class="bi bi-check"></i> Set
                                                        </button>
                                                        <small class="text-muted mt-1">
                                                            <i class="bi bi-upc-scan"></i> Scanner ready
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $item->created_at->format('M d, Y') }}</span>
                                                <br><small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="btn-group-vertical" role="group">
                                                        <button class="btn btn-success btn-sm mb-1"
                                                                onclick="acceptItemWithValidation({{ $item->id }})"
                                                                title="Accept after Weight & Barcode Check"
                                                                id="accept-btn-{{ $item->id }}">
                                                            <i class="bi bi-check-circle"></i> Accept
                                                        </button>
                                                        <a href="{{ route('pm.items.edit', $item->id) }}"
                                                           class="btn btn-primary btn-sm mb-1"
                                                           title="Review & Edit Item Details">
                                                            <i class="bi bi-pencil-square"></i> Review & Edit
                                                        </a>
                                                        <button class="btn btn-outline-danger btn-sm"
                                                                onclick="quickRejectItem({{ $item->id }})"
                                                                title="Quick Reject">
                                                            <i class="bi bi-x-circle"></i> Quick Reject
                                                        </button>
                                                    </div>
                                                    <!-- Progress indicators -->
                                                    <div class="mt-2">
                                                        <small class="text-muted d-block">
                                                            <i class="bi bi-list-check"></i> Checklist:
                                                        </small>
                                                        <div class="checklist-items" style="font-size: 10px;">
                                                            <div class="checklist-item" id="weight-check-{{ $item->id }}">
                                                                @if($item->weight)
                                                                    <i class="bi bi-check-circle text-success"></i>
                                                                @else
                                                                    <i class="bi bi-circle text-warning"></i>
                                                                @endif
                                                                Weight
                                                            </div>
                                                            <div class="checklist-item" id="barcode-check-{{ $item->id }}">
                                                                @if($item->barcode)
                                                                    <i class="bi bi-check-circle text-success"></i>
                                                                @else
                                                                    <i class="bi bi-circle text-warning"></i>
                                                                @endif
                                                                Barcode
                                                            </div>
                                                        </div>
                                                    </div>
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
function quickAcceptItem(itemId) {
    if (confirm('Are you sure you want to accept this item? It will be processed and moved to the system.')) {
        fetch(`/pm/items/${itemId}/quick-accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the row to show accepted status
                document.getElementById(`item-${itemId}`).style.opacity = '0.5';
                document.getElementById(`item-${itemId}`).innerHTML = '<td colspan="10" class="text-center text-success"><i class="bi bi-check-circle"></i> Item Accepted Successfully</td>';

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
            showAlert('danger', 'Error accepting item');
        });
    }
}

function quickRejectItem(itemId) {
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
                document.getElementById(`item-${itemId}`).innerHTML = '<td colspan="10" class="text-center text-danger"><i class="bi bi-x-circle"></i> Item Rejected</td>';

                // Show success message
                showAlert('success', data.message || 'Item rejected successfully!');

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
                document.getElementById(`item-${itemId}`).innerHTML = '<td colspan="10" class="text-center text-danger"><i class="bi bi-x-circle"></i> Item Rejected</td>';

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

function updateBarcode(itemId) {
    const barcodeInput = document.getElementById(`barcode-${itemId}`);
    const barcode = barcodeInput.value.trim();

    if (!barcode) {
        showAlert('warning', 'Please enter a barcode');
        return;
    }

    // Disable the button during request
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass"></i> Setting...';

    fetch('{{ route("pm.items.update-barcode") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            item_id: itemId,
            barcode: barcode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Replace the input with success badge
            const td = barcodeInput.closest('td');
            td.innerHTML = `
                if (data.success) {
            const td = barcodeInput.closest('td');
            td.innerHTML = `
                <span class="badge bg-success">${barcode}</span>
                <br><small class="text-muted">PM assigned</small>
            `;

            // Update checklist
            const checkIcon = document.querySelector(`#barcode-check-${itemId} i`);
            if (checkIcon) {
                checkIcon.className = 'bi bi-check-circle text-success';
            }

            updateAcceptButton(itemId);
            showAlert('success', 'Barcode updated successfully');
        } else {
            `;
            showAlert('success', 'Barcode updated successfully');
        } else {
            showAlert('danger', data.message || 'Failed to update barcode');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error occurred');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function acceptItemWithValidation(itemId) {
    // Get weight and barcode values
    const weightInput = document.getElementById(`weight-${itemId}`);
    const barcodeInput = document.getElementById(`barcode-${itemId}`);

    const weight = weightInput ? weightInput.value.trim() : '';
    const barcode = barcodeInput ? barcodeInput.value.trim() : '';

    // Check if item has barcode (either customer provided or PM entered)
    const existingBarcode = document.querySelector(`#item-${itemId} .badge.bg-success`);

    // Validate requirements
    let validationErrors = [];

    if (!weight || weight <= 0) {
        validationErrors.push('Weight is required and must be greater than 0');
        weightInput?.focus();
    }

    if (!existingBarcode && !barcode) {
        validationErrors.push('Barcode is required (enter barcode and click Set first)');
        barcodeInput?.focus();
    }

    if (validationErrors.length > 0) {
        showAlert('warning', validationErrors.join('<br>'));
        return;
    }

    // If barcode needs to be set first
    if (!existingBarcode && barcode) {
        showAlert('info', 'Please click "Set" to assign the barcode first, then click Accept');
        return;
    }

    // Confirmation dialog with details
    const confirmMessage = `Accept this item with the following details?

Weight: ${weight}g
Barcode: ${existingBarcode ? existingBarcode.textContent : barcode}

The item will be processed and moved to the system.`;

    if (confirm(confirmMessage)) {
        // Send acceptance request with updated weight
        fetch(`/pm/items/${itemId}/accept-with-updates`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                weight: parseInt(weight),
                barcode: existingBarcode ? existingBarcode.textContent : barcode
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the row to show accepted status
                document.getElementById(`item-${itemId}`).style.opacity = '0.8';
                document.getElementById(`item-${itemId}`).innerHTML = '<td colspan="8" class="text-center text-success"><i class="bi bi-check-circle"></i> Item Accepted Successfully</td>';

                showAlert('success', data.message || 'Item accepted successfully!');
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

function handleBarcodeKeypress(event, itemId) {
    // Auto-submit on Enter key (common for barcode scanners)
    if (event.key === 'Enter') {
        event.preventDefault();
        updateBarcode(itemId);
    }
}

function validateWeight(itemId) {
    const weightInput = document.getElementById(`weight-${itemId}`);
    const weight = parseInt(weightInput.value);
    const checkIcon = document.querySelector(`#weight-check-${itemId} i`);

    if (weight && weight > 0) {
        weightInput.style.borderColor = '#28a745';
        weightInput.style.backgroundColor = '#f8fff9';

        // Update checklist
        if (checkIcon) {
            checkIcon.className = 'bi bi-check-circle text-success';
        }
    } else {
        weightInput.style.borderColor = '#dc3545';
        weightInput.style.backgroundColor = '#fff5f5';

        // Update checklist
        if (checkIcon) {
            checkIcon.className = 'bi bi-circle text-warning';
        }
    }

    updateAcceptButton(itemId);
}

function updateAcceptButton(itemId) {
    const weightInput = document.getElementById(`weight-${itemId}`);
    const barcodeExists = document.querySelector(`#item-${itemId} .badge.bg-success`);
    const acceptBtn = document.getElementById(`accept-btn-${itemId}`);

    const weight = weightInput ? parseInt(weightInput.value) : 0;
    const hasValidWeight = weight && weight > 0;
    const hasBarcode = !!barcodeExists;

    if (acceptBtn) {
        if (hasValidWeight && hasBarcode) {
            acceptBtn.className = 'btn btn-success btn-sm mb-1';
            acceptBtn.disabled = false;
        } else {
            acceptBtn.className = 'btn btn-outline-success btn-sm mb-1';
            acceptBtn.disabled = false; // Keep enabled for validation messages
        }
    }
}

// Auto-focus on first barcode input for scanner efficiency
document.addEventListener('DOMContentLoaded', function() {
    const firstBarcodeInput = document.querySelector('.barcode-input');
    if (firstBarcodeInput) {
        setTimeout(() => {
            firstBarcodeInput.focus();
        }, 500);
    }

    // Add scanner-friendly behavior
    document.querySelectorAll('.barcode-input').forEach(input => {
        input.addEventListener('input', function() {
            // Many barcode scanners input very quickly
            // If more than 8 characters are entered quickly, likely a scanner
            if (this.value.length > 8) {
                setTimeout(() => {
                    this.style.backgroundColor = '#e8f5e8';
                }, 100);
            }
        });
    });
});

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

// Search functionality
function clearSearch() {
    document.getElementById('nicSearch').value = '';
    document.getElementById('searchForm').submit();
}

// Initialize search functionality when page loads
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('nicSearch');
    if (searchInput) {
        // Focus on search input
        searchInput.focus();

        // Select text if there's a search term
        if (searchInput.value.trim() !== '') {
            searchInput.select();
        }
    }
});
</script>
@endsection
