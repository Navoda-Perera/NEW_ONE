@extends('layouts.app')

@section('title', 'Edit Item')

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
        <a class="nav-link active" href="{{ route('pm.item-management.index') }}">
            <i class="bi bi-search"></i> Item Management
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.bulk-upload') }}">
            <i class="bi bi-cloud-upload"></i> Bulk Upload
        </a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Item</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pm.item-management.index') }}">Item Management</a></li>
                        <li class="breadcrumb-item active">Edit Item</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil"></i> Edit Item Details
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pm.item-management.update', $item->id) }}" method="POST" id="editItemForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror"
                                           id="barcode" name="barcode" value="{{ old('barcode', $item->barcode) }}" required>
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="receiver_name" class="form-label">Receiver Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('receiver_name') is-invalid @enderror"
                                   id="receiver_name" name="receiver_name" value="{{ old('receiver_name', $item->receiver_name) }}" required>
                            @error('receiver_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="receiver_address" class="form-label">Receiver Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('receiver_address') is-invalid @enderror"
                                      id="receiver_address" name="receiver_address" rows="3" required>{{ old('receiver_address', $item->receiver_address) }}</textarea>
                            @error('receiver_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Weight (grams) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                           id="weight" name="weight" value="{{ old('weight', $item->weight) }}"
                                           min="0" step="0.01" required>
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount" value="{{ old('amount', $item->amount) }}"
                                           min="0" step="0.01" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('pm.item-management.index') }}" class="btn btn-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Item Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Item Information
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Current Barcode:</strong></td>
                            <td><code>{{ $item->barcode }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $item->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $item->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($item->creator)
                            <tr>
                                <td><strong>Created By:</strong></td>
                                <td>{{ $item->creator->name }}</td>
                            </tr>
                        @endif
                        @if($item->updater)
                            <tr>
                                <td><strong>Updated By:</strong></td>
                                <td>{{ $item->updater->name }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Customer Information Card -->
            @if($item->creator)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-person"></i> Customer Information
                        </h6>
                    </div>
                    <div class="card-body">
                        @php $customer = $item->creator; @endphp
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $customer->email }}</td>
                            </tr>
                            @if($customer->mobile)
                                <tr>
                                    <td><strong>Mobile:</strong></td>
                                    <td>{{ $customer->mobile }}</td>
                                </tr>
                            @endif
                            @if($customer->nic)
                                <tr>
                                    <td><strong>NIC:</strong></td>
                                    <td>{{ $customer->nic }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            @endif

            <!-- Actions Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-pm-primary text-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    @if(!in_array($item->status, ['dispatched', 'delivered']))
                        <p class="text-muted small mb-3">
                            Deleting this item will permanently remove it from the system. This action cannot be undone.
                        </p>
                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="deleteItem()">
                            <i class="bi bi-trash"></i> Delete Item
                        </button>
                    @else
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle"></i>
                            This item cannot be deleted as it has been {{ $item->status }}.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function deleteItem() {
    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        return;
    }

    $.ajax({
        url: '{{ route("pm.item-management.delete", $item->id) }}',
        method: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                window.location.href = '{{ route("pm.item-management.index") }}';
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error deleting item');
        }
    });
}

// Form validation and enhancement
$(document).ready(function() {
    // Auto-format barcode input
    $('#barcode').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Weight input validation
    $('#weight').on('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });

    // Amount input validation
    $('#amount').on('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });

    // Form submission enhancement
    $('#editItemForm').on('submit', function(e) {
        // Add loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="spinner-border spinner-border-sm"></i> Updating...').prop('disabled', true);

        // Reset on completion (this will be overridden by page redirect on success)
        setTimeout(function() {
            submitBtn.html(originalText).prop('disabled', false);
        }, 3000);
    });
});
</script>
@endsection
