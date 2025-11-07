@extends('layouts.app')

@section('title', 'Customer Upload Details')

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

/* Checkbox styling */
.item-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.item-checkbox:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

#selectAllCheckbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Customer Upload #{{ $upload->id }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('pm.customer-uploads') }}">Customer Uploads</a></li>
                            <li class="breadcrumb-item active">Upload #{{ $upload->id }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('pm.customer-uploads') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Customer Uploads
                </a>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items from {{ $upload->user->name }}</h5>
                    @php
                        $pendingItems = $upload->associates->where('status', 'pending');
                        $pendingWithBarcodes = $pendingItems->whereNotNull('barcode')->where('barcode', '!=', '');
                    @endphp

                    @if($pendingWithBarcodes->count() > 0)
                        <div class="btn-group">
                            <button type="button" id="selectAllBtn" class="btn btn-outline-primary" onclick="toggleSelectAll()">
                                <i class="bi bi-check2-square me-2"></i>Select All
                            </button>
                            <button type="button" id="acceptSelectedBtn" class="btn btn-success" onclick="acceptSelected()" disabled>
                                <i class="bi bi-check-circle me-2"></i>Accept Selected (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($upload->associates->count() > 0)
                        <div class="table-responsive">
                            <form id="acceptItemsForm" action="{{ route('pm.accept-selected-upload', $upload->id) }}" method="POST">
                                @csrf
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                @if($pendingWithBarcodes->count() > 0)
                                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input" onchange="toggleSelectAll()">
                                                @endif
                                            </th>
                                            <th>Receiver Details</th>
                                            <th>Service Type</th>
                                            <th>Weight</th>
                                            @php
                                                $hasCodeService = $upload->associates->contains('service_type', 'cod');
                                            @endphp
                                            @if($hasCodeService)
                                                <th>Amount</th>
                                            @endif
                                            <th>Postage</th>
                                            <th>Barcode</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                    @foreach($upload->associates as $item)
                                        <tr>
                                            <td>
                                                @if($item->status === 'pending' && $item->barcode)
                                                    <input type="checkbox" name="selected_items[]" value="{{ $item->id }}"
                                                           class="form-check-input item-checkbox" onchange="updateSelectedCount()">
                                                @else
                                                    {{-- Show disabled checkbox for items without barcode or already processed --}}
                                                    <input type="checkbox" class="form-check-input" disabled>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $item->receiver_name }}</strong>
                                                <br><small class="text-muted">{{ Str::limit($item->receiver_address, 40) }}</small>
                                                @if($item->contact_number)
                                                    <br><small class="text-info">{{ $item->contact_number }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $serviceType = $serviceTypeLabels[$item->service_type] ?? $item->service_type;
                                                @endphp
                                                <span class="badge bg-info">{{ $serviceType }}</span>
                                            </td>
                                            <td>
                                                @if($item->weight)
                                                    {{ number_format($item->weight) }}g
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @if($hasCodeService)
                                                <td>
                                                    @if($item->service_type === 'cod' && $item->amount)
                                                        <strong>LKR {{ number_format($item->amount, 2) }}</strong>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                @if($item->postage)
                                                    <span class="text-primary">LKR {{ number_format($item->postage, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->barcode)
                                                    <span class="badge bg-success">{{ $item->barcode }}</span>
                                                    <br><small class="text-muted">Customer provided</small>
                                                @else
                                                    <span class="badge bg-warning text-dark">No Barcode</span>
                                                    <br><small class="text-danger">PM must add barcode first</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $item->created_at->format('M d, Y') }}</small>
                                                <br><small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                @if($item->status === 'pending')
                                                    <div class="d-flex flex-column gap-1">
                                                        <a href="{{ route('pm.items.edit', $item->id) }}" class="btn btn-primary btn-sm w-100" title="Edit & Add Barcode">
                                                            <i class="bi bi-pencil-square"></i>
                                                            @if($item->barcode)
                                                                Edit & Review
                                                            @else
                                                                Add Barcode & Review
                                                            @endif
                                                        </a>

                                                        <form action="{{ route('pm.items.reject', $item->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm w-100"
                                                                    onclick="return confirm('Are you sure you want to reject this item?')"
                                                                    title="Quick Reject">
                                                                <i class="bi bi-x-circle"></i> Quick Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="badge bg-{{ $item->status === 'accept' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($item->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
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
        selectAllBtn.innerHTML = '<i class="bi bi-check2-square me-2"></i>Deselect All';
    } else {
        selectAllBtn.innerHTML = '<i class="bi bi-check2-square me-2"></i>Select All';
    }

    updateSelectedCount();
}

function updateSelectedCount() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const selectedCount = checkedBoxes.length;
    const acceptBtn = document.getElementById('acceptSelectedBtn');
    const countSpan = document.getElementById('selectedCount');

    countSpan.textContent = selectedCount;
    acceptBtn.disabled = selectedCount === 0;

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

    if (selectAllCheckbox) {
        if (selectedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
            selectAllBtn.innerHTML = '<i class="bi bi-check2-square me-2"></i>Select All';
        } else if (selectedCount === allCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
            selectAllBtn.innerHTML = '<i class="bi bi-check2-square me-2"></i>Deselect All';
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllBtn.innerHTML = '<i class="bi bi-check2-square me-2"></i>Select All';
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

@endsection
