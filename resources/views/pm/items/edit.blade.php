@extends('layouts.app')

@section('title', 'Update Item Details')

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
        <a class="nav-link" href="{{ route('pm.single-item.index') }}">
            <i class="bi bi-box-seam"></i> Add Single Item
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.bulk-upload') }}">
            <i class="bi bi-cloud-upload"></i> Bulk Upload
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('pm.dashboard') }}" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <h2 class="fw-bold text-dark mb-0 d-inline">Update Item Details</h2>
                </div>
                <div>
                    <span class="badge bg-warning fs-6">Update Only - Accept Later</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Item Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm.items.update-only', $item->id) }}" id="itemEditForm">
                        @csrf

                        @if(!$item->barcode)
                            <div class="alert alert-warning border-0 mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">Barcode Required</h6>
                                        <p class="mb-0">This customer did not provide a barcode. You must enter or scan a barcode before accepting this item.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Customer Information (Read-only) -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-person me-1"></i>Customer Information</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Name</label>
                                    <input type="text" class="form-control" value="{{ $item->temporaryUpload->user->name }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Email</label>
                                    <input type="text" class="form-control" value="{{ $item->temporaryUpload->user->email ?? 'N/A' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Service Type</label>
                                    <input type="text" class="form-control" value="{{ $serviceTypeLabels[$item->service_type] ?? $item->service_type }}" readonly>
                                </div>
                            </div>

                            <!-- Item Details (Editable) -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-package me-1"></i>Item Details - Edit as Needed</h6>

                                <div class="mb-3">
                                    <label for="weight" class="form-label fw-semibold">
                                        <i class="bi bi-speedometer2 me-1"></i>Weight (grams) *
                                    </label>
                                    <input type="number"
                                           id="weight"
                                           name="weight"
                                           class="form-control @error('weight') is-invalid @enderror"
                                           value="{{ old('weight', $item->weight) }}"
                                           step="0.01"
                                           min="0"
                                           required
                                           placeholder="Verify and enter actual weight">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">Please verify the actual weight and update if necessary</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="receiver_name" class="form-label fw-semibold">
                                        <i class="bi bi-person-check me-1"></i>Receiver Name *
                                    </label>
                                    <input type="text"
                                           id="receiver_name"
                                           name="receiver_name"
                                           class="form-control @error('receiver_name') is-invalid @enderror"
                                           value="{{ old('receiver_name', $item->receiver_name) }}"
                                           required>
                                    @error('receiver_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_number" class="form-label fw-semibold">
                                        <i class="bi bi-telephone me-1"></i>Receiver Contact Number
                                    </label>
                                    <input type="text"
                                           id="contact_number"
                                           name="contact_number"
                                           class="form-control @error('contact_number') is-invalid @enderror"
                                           value="{{ old('contact_number', $item->contact_number) }}"
                                           placeholder="07XXXXXXXX">
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="receiver_address" class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt me-1"></i>Receiver Address *
                                    </label>
                                    <textarea id="receiver_address"
                                              name="receiver_address"
                                              class="form-control @error('receiver_address') is-invalid @enderror"
                                              rows="3"
                                              required>{{ old('receiver_address', $item->receiver_address) }}</textarea>
                                    @error('receiver_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Financial Details -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-currency-dollar me-1"></i>Financial Details</h6>

                                <div class="mb-3">
                                    <label for="amount" class="form-label fw-semibold">
                                        <i class="bi bi-cash me-1"></i>Amount (LKR) *
                                    </label>
                                    <input type="number"
                                           id="amount"
                                           name="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount', $item->amount) }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if($item->service_type === 'cod')
                                    <div class="mb-3">
                                        <label for="item_value" class="form-label fw-semibold">
                                            <i class="bi bi-tag me-1"></i>Item Value (LKR) *
                                        </label>
                                        <input type="number"
                                               id="item_value"
                                               name="item_value"
                                               class="form-control @error('item_value') is-invalid @enderror"
                                               value="{{ old('item_value', $item->item_value) }}"
                                               step="0.01"
                                               min="0"
                                               required>
                                        @error('item_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <small class="text-muted">Required for COD items - value to collect from receiver</small>
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" name="item_value" value="0">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Note:</strong> Item value is not required for {{ $serviceTypeLabels[$item->service_type] ?? $item->service_type }} services.
                                    </div>
                                @endif
                            </div>

                            <!-- Barcode Entry -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-upc me-1"></i>Barcode Assignment</h6>

                                <div class="mb-3">
                                    <label for="barcode" class="form-label fw-semibold">
                                        <i class="bi bi-upc-scan me-1"></i>Enter Barcode Manually *
                                    </label>
                                    <input type="text"
                                           id="barcode"
                                           name="barcode"
                                           class="form-control @error('barcode') is-invalid @enderror"
                                           value="{{ old('barcode', $item->barcode) }}"
                                           required
                                           placeholder="Scan or enter barcode manually">
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">Use barcode scanner or enter manually. Must be unique.</small>
                                    </div>
                                </div>

                                @if($item->barcode)
                                    <div class="alert alert-info">
                                        <small><i class="bi bi-info-circle me-1"></i>Customer provided barcode: {{ $item->barcode }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('pm.items.pending') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Cancel
                                </a>
                            </div>
                            <div>
                                <button type="button"
                                        class="btn btn-danger me-2"
                                        onclick="rejectItem({{ $item->id }})">
                                    <i class="bi bi-x-circle me-1"></i>Reject Item
                                </button>
                                <button type="submit" class="btn btn-primary" id="updateBtn"
                                        @if(!$item->barcode) disabled @endif>
                                    <i class="bi bi-pencil-square me-1"></i>Update Item Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Update Workflow</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-1-circle text-primary me-2"></i>
                            <strong>Step 1:</strong> Update item details and assign barcode
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-2-circle text-warning me-2"></i>
                            <strong>Step 2:</strong> Click "Update Item Details" to save changes
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-3-circle text-success me-2"></i>
                            <strong>Step 3:</strong> Use Accept buttons in list view to process to database
                        </div>
                        <div class="list-group-item border-0 px-0 mt-2">
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Note:</strong> This page only updates temporary data. 
                                Final acceptance happens in the upload list view.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Details</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $item->temporaryUpload->user->name }}</p>
                    <p><strong>NIC:</strong> {{ $item->temporaryUpload->user->nic }}</p>
                    <p><strong>Mobile:</strong> {{ $item->temporaryUpload->user->mobile }}</p>
                    <p class="mb-0"><strong>Submitted:</strong> {{ $item->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enable/disable accept button based on barcode presence
function checkBarcodeRequirement() {
    const barcodeInput = document.getElementById('barcode');
    const acceptBtn = document.getElementById('acceptBtn');
    const alertSection = document.querySelector('.alert-warning');

    if (barcodeInput && acceptBtn) {
        const hasBarcode = barcodeInput.value.trim().length > 0;

        if (hasBarcode) {
            acceptBtn.disabled = false;
            acceptBtn.classList.remove('btn-secondary');
            acceptBtn.classList.add('btn-success');
            if (alertSection) {
                alertSection.style.display = 'none';
            }
        } else {
            acceptBtn.disabled = true;
            acceptBtn.classList.remove('btn-success');
            acceptBtn.classList.add('btn-secondary');
            if (alertSection) {
                alertSection.style.display = 'block';
            }
        }
    }
}

// Initialize barcode checking when page loads
document.addEventListener('DOMContentLoaded', function() {
    const barcodeInput = document.getElementById('barcode');
    if (barcodeInput) {
        // Check initially
        checkBarcodeRequirement();

        // Check whenever barcode input changes
        barcodeInput.addEventListener('input', checkBarcodeRequirement);
        barcodeInput.addEventListener('change', checkBarcodeRequirement);
    }
});

function rejectItem(itemId) {
    if (confirm('Are you sure you want to reject this item? The customer will be notified.')) {
        // Create a form to submit the rejection
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/pm/items/${itemId}/reject`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
