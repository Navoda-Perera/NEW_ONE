@extends('layouts.modern-pm')

@section('title', 'Customer Upload Details')

@section('content')
<div class="container-fluid">
    <!-- Modern Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-info text-white p-4 rounded-top">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1">
                            <i class="bi bi-file-earmark-text"></i>
                            Customer Upload #{{ $upload->id }}
                        </h2>
                        <p class="mb-0 opacity-75">Review and manage items from {{ $upload->user->name }}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('pm.customer-uploads') }}" class="btn btn-light btn-lg shadow">
                            <i class="bi bi-arrow-left"></i> Back to Uploads
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <div class="row align-items-center">
                        <div class="col-12 text-center">
                            <h4 class="mb-0">
                                <i class="bi bi-person-circle fs-3 me-2"></i>
                                Customer Information
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ strtoupper(substr($upload->user->name, 0, 1)) }}
                            </div>
                            <h5 class="text-dark">{{ $upload->user->name }}</h5>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-success border-bottom pb-2 mb-3">
                                        <i class="bi bi-envelope"></i> Contact Information
                                    </h6>
                                    <p class="mb-2">
                                        <strong>Email:</strong> {{ $upload->user->email }}
                                    </p>
                                    @if($upload->user->nic)
                                        <p class="mb-2">
                                            <strong>NIC:</strong> <code>{{ $upload->user->nic }}</code>
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success border-bottom pb-2 mb-3">
                                        <i class="bi bi-info-circle"></i> Upload Information
                                    </h6>
                                    <p class="mb-2">
                                        <strong>Upload Date:</strong> {{ $upload->created_at->format('M d, Y H:i') }}
                                    </p>
                                    <p class="mb-2">
                                        <strong>Total Items:</strong>
                                        <span class="badge bg-primary fs-6">{{ $upload->associates->count() }}</span>
                                    </p>
                                    @php
                                        $pendingItems = $upload->associates->where('status', 'pending');
                                    @endphp
                                    @if($pendingItems->count() > 0)
                                        <p class="mb-2">
                                            <strong>Pending:</strong>
                                            <span class="badge bg-warning fs-6">{{ $pendingItems->count() }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Management Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">
                                <i class="bi bi-list-check fs-3 me-2"></i>
                                Items Management
                            </h4>
                        </div>
                        <div class="col-md-6 text-end">
                            @php
                                $pendingItems = $upload->associates->where('status', 'pending');
                                $pendingWithBarcodes = $pendingItems->whereNotNull('barcode')->where('barcode', '!=', '');
                                $acceptedItems = $upload->associates->where('status', 'accept');
                            @endphp

                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                @if($acceptedItems->count() > 0)
                                    <!-- Print Receipt Button for accepted items -->
                                    <a href="{{ route('pm.view-customer-upload-receipt', $upload->id) }}"
                                       class="btn btn-warning btn-lg shadow-sm"
                                       title="View & Print Receipt">
                                        <i class="bi bi-receipt"></i> Print Receipt ({{ $acceptedItems->count() }} items)
                                    </a>
                                @endif

                                @if($pendingWithBarcodes->count() > 0)
                                    <button type="button" id="selectAllBtn" class="btn btn-light btn-lg shadow-sm" onclick="toggleSelectAll()">
                                        <i class="bi bi-check2-square"></i> Select All
                                    </button>
                                    <button type="button" id="acceptSelectedBtn" class="btn btn-success btn-lg shadow-sm" onclick="acceptSelected()" disabled>
                                        <i class="bi bi-check-circle"></i> Accept Selected (<span id="selectedCount">0</span>)
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($upload->associates->count() > 0)
                        <form id="acceptItemsForm" action="{{ route('pm.accept-selected-upload', $upload->id) }}" method="POST">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50" class="text-center">
                                                @if($pendingWithBarcodes->count() > 0)
                                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input" style="width: 20px; height: 20px;" onchange="toggleSelectAll()">
                                                @endif
                                            </th>
                                            <th class="fw-bold text-dark py-3">
                                                Receiver Details
                                            </th>
                                            <th class="fw-bold text-dark py-3">
                                                Service Type
                                            </th>
                                            <th class="fw-bold text-dark py-3">
                                                Weight
                                            </th>
                                            @php
                                                $hasCodeService = $upload->associates->contains('service_type', 'cod');
                                            @endphp
                                            @if($hasCodeService)
                                                <th class="fw-bold text-dark py-3">
                                                    Amount
                                                </th>
                                            @endif
                                            <th class="fw-bold text-dark py-3">
                                                Postage
                                            </th>
                                            <th class="fw-bold text-dark py-3">
                                                Barcode
                                            </th>
                                            <th class="fw-bold text-dark py-3">
                                                Submitted
                                            </th>
                                            <th class="fw-bold text-dark py-3 text-center">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upload->associates as $item)
                                            <tr class="border-bottom">
                                                <td class="text-center py-3">
                                                    @if($item->status === 'pending' && $item->barcode)
                                                        <input type="checkbox" name="selected_items[]" value="{{ $item->id }}"
                                                               class="form-check-input item-checkbox" style="width: 20px; height: 20px;" onchange="updateSelectedCount()">
                                                    @else
                                                        <input type="checkbox" class="form-check-input" style="width: 20px; height: 20px;" disabled>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                                            {{ strtoupper(substr($item->receiver_name, 0, 1)) }}
                                                        </div>
                                                        <div class="ms-3">
                                                            <strong class="text-dark">{{ $item->receiver_name }}</strong>
                                                            <br><small class="text-muted">{{ Str::limit($item->receiver_address, 50) }}</small>
                                                            @if($item->contact_number)
                                                                <br><small class="text-info">
                                                                    {{ $item->contact_number }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    @php
                                                        $serviceType = $serviceTypeLabels[$item->service_type] ?? $item->service_type;
                                                    @endphp
                                                    @switch($item->service_type)
                                                        @case('slp_courier')
                                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                                {{ $serviceType }}
                                                            </span>
                                                            @break
                                                        @case('cod')
                                                            <span class="badge bg-warning fs-6 px-3 py-2">
                                                                {{ $serviceType }}
                                                            </span>
                                                            @break
                                                        @case('register_post')
                                                            <span class="badge bg-info fs-6 px-3 py-2">
                                                                {{ $serviceType }}
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary fs-6 px-3 py-2">
                                                                {{ $serviceType }}
                                                            </span>
                                                    @endswitch
                                                </td>
                                                <td class="py-3">
                                                    @if($item->weight)
                                                        <span class="badge bg-light text-dark fs-6">
                                                            {{ number_format($item->weight) }}g
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                @if($hasCodeService)
                                                    <td>
                                                        @if($item->service_type === 'cod' && $item->amount)
                                                            <span class="badge bg-warning text-dark fs-6">
                                                                LKR {{ number_format($item->amount, 2) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="py-3">
                                                    @if($item->postage)
                                                        <span class="badge bg-primary fs-6">
                                                            LKR {{ number_format($item->postage, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    @if($item->barcode)
                                                        <span class="badge bg-success fs-6 px-3 py-2">
                                                            {{ $item->barcode }}
                                                        </span>
                                                        <br><small class="text-success">Customer provided</small>
                                                    @else
                                                        <span class="badge badge-pm-accent fs-6 px-3 py-2">
                                                            No Barcode
                                                        </span>
                                                        <br><small class="text-muted">PM must add barcode first</small>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    <div class="text-muted">
                                                        {{ $item->created_at->format('M d, Y') }}
                                                        <br><small class="text-muted">
                                                            {{ $item->created_at->format('H:i') }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-center">
                                                    @if($item->status === 'pending')
                                                        <div class="d-flex flex-column gap-2">
                                                            <a href="{{ route('pm.items.edit', $item->id) }}"
                                                               class="btn btn-primary btn-sm shadow-sm"
                                                               title="Edit & Add Barcode">
                                                                <i class="bi bi-pencil-square"></i>
                                                                @if($item->barcode)
                                                                    Edit & Review
                                                                @else
                                                                    Add Barcode
                                                                @endif
                                                            </a>

                                                            <form action="{{ route('pm.items.reject', $item->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-danger btn-sm shadow-sm w-100"
                                                                        onclick="return confirm('Are you sure you want to reject this item?')"
                                                                        title="Quick Reject">
                                                                    <i class="bi bi-x-circle"></i> Reject
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        @if($item->status === 'accept')
                                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                                <i class="bi bi-check-circle"></i> Accepted
                                                            </span>
                                                        @else
                                                            <span class="badge badge-pm-accent fs-6 px-3 py-2">
                                                                <i class="bi bi-x-circle"></i> {{ ucfirst($item->status) }}
                                                            </span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="text-muted mb-2">No Items Found</h4>
                            <p class="text-muted">This upload doesn't contain any items.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
/* Custom styles for better checkbox and selection */
.item-checkbox {
    cursor: pointer;
}

.item-checkbox:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

/* Row highlighting for selected items */
tr.selected {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

/* Button styling */
#acceptSelectedBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Custom table responsive styling */
.table-responsive {
    max-height: 70vh;
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

<script>
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const selectAllBtn = document.getElementById('selectAllBtn');

    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    // Update button text
    if (selectAllCheckbox.checked) {
        selectAllBtn.innerHTML = '<i class="bi bi-check2-square"></i> Deselect All';
    } else {
        selectAllBtn.innerHTML = '<i class="bi bi-check2-square"></i> Select All';
    }

    updateSelectedCount();
}

function updateSelectedCount() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const selectedCount = checkedBoxes.length;
    const acceptBtn = document.getElementById('acceptSelectedBtn');
    const countSpan = document.getElementById('selectedCount');

    if (countSpan) countSpan.textContent = selectedCount;
    if (acceptBtn) acceptBtn.disabled = selectedCount === 0;

    // Update row highlighting
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('selected');
        } else {
            row.classList.remove('selected');
        }
    });

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectAllBtn = document.getElementById('selectAllBtn');

    if (selectAllCheckbox && selectAllBtn) {
        if (selectedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
            selectAllBtn.innerHTML = '<i class="bi bi-check2-square"></i> Select All';
        } else if (selectedCount === allCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
            selectAllBtn.innerHTML = '<i class="bi bi-check2-square"></i> Deselect All';
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllBtn.innerHTML = '<i class="bi bi-check2-square"></i> Select All';
        }
    }
}

function acceptSelected() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one item to accept.');
        return;
    }

    if (confirm(`Accept ${checkedBoxes.length} selected item(s)?`)) {
        document.getElementById('acceptItemsForm').submit();
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>
