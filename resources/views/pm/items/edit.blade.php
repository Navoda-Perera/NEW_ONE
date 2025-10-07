@extends('layouts.app')

@section('title', 'Review & Edit Item')

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
                    <a href="{{ route('pm.items.pending') }}" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i> Back to Pending Items
                    </a>
                    <h2 class="fw-bold text-dark mb-0 d-inline">Review & Edit Item Details</h2>
                </div>
                <div>
                    <span class="badge bg-info fs-6">PM Review Required</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Item Details - Review & Edit</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm.items.accept', $temporaryAssociate->id) }}">
                        @csrf

                        <div class="row">
                            <!-- Customer Information (Read-only) -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-person me-1"></i>Customer Information</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Name</label>
                                    <input type="text" class="form-control" value="{{ $temporaryAssociate->temporaryUpload->user->name }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Email</label>
                                    <input type="text" class="form-control" value="{{ $temporaryAssociate->temporaryUpload->user->email ?? 'N/A' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Service Type</label>
                                    <input type="text" class="form-control" value="{{ $serviceTypeLabels[$temporaryAssociate->service_type] ?? $temporaryAssociate->service_type }}" readonly>
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
                                           value="{{ old('weight', $temporaryAssociate->weight) }}"
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
                                           value="{{ old('receiver_name', $temporaryAssociate->receiver_name) }}"
                                           required>
                                    @error('receiver_name')
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
                                              required>{{ old('receiver_address', $temporaryAssociate->receiver_address) }}</textarea>
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
                                           value="{{ old('amount', $temporaryAssociate->amount) }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="item_value" class="form-label fw-semibold">
                                        <i class="bi bi-tag me-1"></i>Item Value (LKR) *
                                    </label>
                                    <input type="number"
                                           id="item_value"
                                           name="item_value"
                                           class="form-control @error('item_value') is-invalid @enderror"
                                           value="{{ old('item_value', $temporaryAssociate->item_value) }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('item_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                           value="{{ old('barcode', $temporaryAssociate->barcode) }}"
                                           required
                                           placeholder="Scan or enter barcode manually">
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">Use barcode scanner or enter manually. Must be unique.</small>
                                    </div>
                                </div>

                                @if($temporaryAssociate->barcode)
                                    <div class="alert alert-info">
                                        <small><i class="bi bi-info-circle me-1"></i>Customer provided barcode: {{ $temporaryAssociate->barcode }}</small>
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
                                        onclick="rejectItem({{ $temporaryAssociate->id }})">
                                    <i class="bi bi-x-circle me-1"></i>Reject Item
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Accept & Process Item
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
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>PM Review Checklist</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-check-square text-primary me-2"></i>
                            <strong>Verify Weight:</strong> Check actual weight against customer entry
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-check-square text-primary me-2"></i>
                            <strong>Check Details:</strong> Verify receiver name and address
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-check-square text-primary me-2"></i>
                            <strong>Confirm Amounts:</strong> Verify amount and item value
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-check-square text-primary me-2"></i>
                            <strong>Assign Barcode:</strong> Scan or manually enter unique barcode
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="bi bi-check-square text-primary me-2"></i>
                            <strong>Process:</strong> Accept to move to items table or reject if issues
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
                    <p><strong>Name:</strong> {{ $temporaryAssociate->temporaryUpload->user->name }}</p>
                    <p><strong>NIC:</strong> {{ $temporaryAssociate->temporaryUpload->user->nic }}</p>
                    <p><strong>Mobile:</strong> {{ $temporaryAssociate->temporaryUpload->user->mobile }}</p>
                    <p class="mb-0"><strong>Submitted:</strong> {{ $temporaryAssociate->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
