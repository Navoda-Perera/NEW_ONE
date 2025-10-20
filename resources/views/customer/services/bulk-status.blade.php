@extends('layouts.app')

@section('title', 'Bulk Upload Status')

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

@section('styles')
<style>
    .form-check-input:indeterminate {
        background-color: #0d6efd;
        border-color: #0d6efd;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10h8'/%3e%3c/svg%3e");
    }

    .table-responsive {
        border-radius: 0.375rem;
    }

    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-left: 0.25rem;
    }

    .btn-group .btn:first-child {
        margin-left: 0;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('customer.services.bulk-upload') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="fw-bold text-dark mb-0">Bulk Upload Status</h2>
            </div>

            <!-- Upload Info Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upload Information</h5>
                    <div class="d-flex align-items-center">
                        @switch($temporaryUpload->status)
                            @case('pending')
                                <span class="badge bg-warning me-2">Pending</span>
                                <form action="{{ route('customer.services.delete-bulk-upload', $temporaryUpload->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this upload? This action cannot be undone.')"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Upload">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @break
                            @case('processing')
                                <span class="badge bg-info">Processing</span>
                                @break
                            @case('completed')
                                <span class="badge bg-success">Completed</span>
                                @break
                            @case('failed')
                                <span class="badge bg-danger">Failed</span>
                                @break
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>File Name:</strong> {{ $temporaryUpload->original_filename }}</p>
                            <p><strong>Upload Date:</strong> {{ $temporaryUpload->created_at->format('M d, Y H:i:s') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong>
                                @switch($temporaryUpload->status)
                                    @case('pending')
                                        <span class="text-warning">Waiting for processing</span>
                                        @break
                                    @case('processing')
                                        <span class="text-info">Currently being processed</span>
                                        @break
                                    @case('completed')
                                        <span class="text-success">Processing completed</span>
                                        @break
                                    @case('failed')
                                        <span class="text-danger">Processing failed</span>
                                        @break
                                @endswitch
                            </p>
                            @if($temporaryUpload->notes)
                                <p><strong>Notes:</strong> {{ $temporaryUpload->notes }}</p>
                            @endif
                        </div>
                    </div>

                    @if($temporaryUpload->status === 'pending')
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Your file has been uploaded successfully and is waiting to be processed. You will be notified once processing begins.
                        </div>
                    @elseif($temporaryUpload->status === 'processing')
                        <div class="alert alert-warning">
                            <i class="bi bi-clock me-2"></i>
                            Your file is currently being processed. Please wait...
                        </div>
                    @elseif($temporaryUpload->status === 'failed')
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Processing failed. Please check the file format and try again.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Processing Progress -->
            @if($temporaryUpload->status === 'processing')
                <div class="card mb-4">
                    <div class="card-body">
                        <h6>Processing Progress</h6>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 45%">
                                Processing...
                            </div>
                        </div>
                        <small class="text-muted">This page will automatically refresh to show updates.</small>
                    </div>
                </div>
            @endif

            <!-- Items from Upload -->
            @if($temporaryUpload->associates && $temporaryUpload->associates->count() > 0)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Items from Upload ({{ $temporaryUpload->associates->count() }})</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()">
                                <i class="bi bi-check-all"></i> Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectNone()">
                                <i class="bi bi-square"></i> Select None
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSelected()" id="deleteSelectedBtn" disabled>
                                <i class="bi bi-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>
            @else
                <!-- No Items Found -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Upload Processing Issue</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No items were processed from your file.</strong> This could be due to:
                            <ul class="mt-2 mb-0">
                                <li>Incorrect file format (make sure it's CSV or Excel)</li>
                                <li>Missing or incorrectly named column headers</li>
                                <li>Empty data rows or missing receiver information</li>
                                <li>File encoding issues</li>
                            </ul>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('customer.services.bulk-upload') }}" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Try Again with New File
                            </a>
                            <button class="btn btn-outline-info" onclick="showDebugInfo()">
                                <i class="bi bi-info-circle"></i> Show Debug Info
                            </button>
                        </div>

                        <div id="debugInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Expected CSV Headers:</strong><br>
                                <code>receiver_name, receiver_address, item_value, weight, postage, contact_number, notes</code>
                                <br><br>
                                <strong>Upload Details:</strong><br>
                                File: {{ $temporaryUpload->original_filename ?? 'Unknown' }}<br>
                                @php
                                    $firstAssociate = $temporaryUpload->associates->first();
                                    $serviceType = $firstAssociate ? $firstAssociate->service_type : 'register_post';
                                @endphp
                                Service Type: {{ ucfirst(str_replace('_', ' ', $serviceType)) }}<br>
                                Upload Time: {{ $temporaryUpload->created_at->format('M d, Y H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($temporaryUpload->associates && $temporaryUpload->associates->count() > 0)
                    <div class="card-body">
                        @php
                            $missingDataCount = $temporaryUpload->associates->filter(function($associate) {
                                return empty($associate->receiver_name) || empty($associate->receiver_address);
                            })->count();
                        @endphp

                        @if($missingDataCount > 0)
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Data Issue Detected:</strong> {{ $missingDataCount }} item(s) are missing receiver name or address information.
                                This might be due to incorrect CSV column headers. Please ensure your CSV file has columns named
                                <code>receiver_name</code> and <code>receiver_address</code> or download a fresh template.
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleAll()">
                                                <label class="form-check-label" for="selectAllCheckbox"></label>
                                            </div>
                                        </th>
                                        <th>#</th>
                                        <th>Receiver</th>
                                        <th>Address</th>
                                        <th>Service Type</th>
                                        <th>Amount</th>
                                        <th>Weight</th>
                                        <th>Postage</th>
                                        <th>Barcode</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($temporaryUpload->associates as $index => $associate)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox" type="checkbox" value="{{ $associate->id }}" onchange="updateDeleteButton()">
                                                </div>
                                            </td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($associate->receiver_name)
                                                    {{ $associate->receiver_name }}
                                                @else
                                                    <span class="text-muted fst-italic">Not provided</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($associate->receiver_address)
                                                    {{ Str::limit($associate->receiver_address, 50) }}
                                                @else
                                                    <span class="text-muted fst-italic">Not provided</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $typeLabels = [
                                                        'register_post' => 'Register Post',
                                                        'slp_courier' => 'SLP Courier',
                                                        'cod' => 'COD',
                                                        'remittance' => 'Remittance'
                                                    ];
                                                @endphp
                                                <span class="badge bg-primary">{{ $typeLabels[$associate->service_type] ?? $associate->service_type }}</span>
                                            </td>
                                            <td>LKR {{ number_format($associate->amount, 2) }}</td>
                                            <td>
                                                @if($associate->weight)
                                                    {{ number_format($associate->weight) }}g
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>LKR {{ number_format($associate->postage, 2) }}</td>
                                            <td>
                                                @if($associate->barcode)
                                                    <code class="text-primary">{{ $associate->barcode }}</code>
                                                @else
                                                    <span class="text-muted fst-italic">Not provided</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($associate->status)
                                                    @case('accept')
                                                        <span class="badge bg-success">Accepted</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                        @break
                                                    @case('reject')
                                                        <span class="badge bg-danger">Rejected</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editItem({{ $associate->id }}, '{{ $associate->receiver_name }}', '{{ $associate->receiver_address }}', '{{ $associate->item_value }}', '{{ $associate->service_type }}', '{{ $associate->weight }}', '{{ $associate->amount }}', '{{ $associate->barcode }}')" data-bs-toggle="modal" data-bs-target="#editItemModal">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteItem({{ $associate->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Summary</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Total Items:</strong> {{ $temporaryUpload->associates->count() }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Amount:</strong> LKR {{ number_format($temporaryUpload->associates->sum('amount'), 2) }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Postage:</strong> LKR {{ number_format($temporaryUpload->associates->sum('postage'), 2) }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Commission:</strong> LKR {{ number_format($temporaryUpload->associates->sum('commission'), 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    @if($temporaryUpload->associates && $temporaryUpload->associates->count() > 0 && $temporaryUpload->status !== 'submitted')
                        <form method="POST" action="{{ route('customer.services.submit-bulk-to-pm', $temporaryUpload->id) }}" style="display: inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-success me-2" onclick="return confirm('Are you sure you want to submit these items to PM for review?')">
                                <i class="bi bi-send me-2"></i>Submit to PM
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('customer.services.bulk-upload') }}" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Another File
                    </a>
                    <a href="{{ route('customer.services.items') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul me-2"></i>View All Items
                    </a>
                    @if($temporaryUpload->status === 'completed')
                        <button class="btn btn-success" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>Print Summary
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editItemForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_receiver_name" class="form-label">Receiver Name</label>
                        <input type="text" class="form-control" id="edit_receiver_name" name="receiver_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_receiver_address" class="form-label">Receiver Address</label>
                        <textarea class="form-control" id="edit_receiver_address" name="receiver_address" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_item_value" class="form-label">Item Value (LKR)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_item_value" name="item_value" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_service_type" class="form-label">Service Type</label>
                                <select class="form-select" id="edit_service_type" name="service_type" required>
                                    <option value="register_post">Register Post</option>
                                    <option value="slp_courier">SLP Courier</option>
                                    <option value="cod">COD</option>
                                    <option value="remittance">Remittance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_weight" class="form-label">Weight (g)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_weight" name="weight" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_amount" class="form-label">Amount (LKR)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_barcode" class="form-label">Barcode (Optional)</label>
                        <input type="text" class="form-control" id="edit_barcode" name="barcode" placeholder="Enter barcode if available">
                        <div class="form-text">Leave empty if barcode is not available - PM will add it after approval.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($temporaryUpload->status === 'processing')
<script>
// Auto-refresh page every 30 seconds if still processing
setTimeout(function() {
    location.reload();
}, 30000);
</script>
@endif

<script>
let currentEditId = null;

function editItem(id, receiverName, receiverAddress, itemValue, serviceType, weight, amount, barcode) {
    currentEditId = id;
    document.getElementById('edit_receiver_name').value = receiverName;
    document.getElementById('edit_receiver_address').value = receiverAddress;
    document.getElementById('edit_item_value').value = itemValue;
    document.getElementById('edit_service_type').value = serviceType;
    document.getElementById('edit_weight').value = weight;
    document.getElementById('edit_amount').value = amount;
    document.getElementById('edit_barcode').value = barcode || '';
}

function deleteItem(id) {
    if (confirm('Are you sure you want to delete this item?')) {
        fetch(`{{ route('customer.services.delete-bulk-item', ':id') }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting item');
        });
    }
}

document.getElementById('editItemForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`{{ route('customer.services.update-bulk-item', ':id') }}`.replace(':id', currentEditId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating item');
    });
});

// Bulk selection functions
function selectAll() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
    updateDeleteButton();
}

function selectNone() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectAllCheckbox.checked = false;
    updateDeleteButton();
}

function toggleAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.item-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateDeleteButton();
}

function updateDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const deleteBtn = document.getElementById('deleteSelectedBtn');

    if (checkedBoxes.length > 0) {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = `<i class="bi bi-trash"></i> Delete Selected (${checkedBoxes.length})`;
    } else {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="bi bi-trash"></i> Delete Selected';
    }

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    if (checkedBoxes.length === allCheckboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedBoxes.length > 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
}

function deleteSelected() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');

    if (checkedBoxes.length === 0) {
        alert('Please select items to delete');
        return;
    }

    if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected item(s)?`)) {
        const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

        // Show loading state
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Deleting...';
        deleteBtn.disabled = true;

        // Delete each item
        const deletePromises = selectedIds.map(id => {
            return fetch(`{{ route('customer.services.delete-bulk-item', ':id') }}`.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            });
        });

        Promise.all(deletePromises)
            .then(responses => {
                // Check if all requests were successful
                const allSuccessful = responses.every(response => response.ok);
                if (allSuccessful) {
                    location.reload();
                } else {
                    alert('Some items could not be deleted. Please try again.');
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting items');
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            });
    }
}

function showDebugInfo() {
    const debugDiv = document.getElementById('debugInfo');
    if (debugDiv.style.display === 'none') {
        debugDiv.style.display = 'block';
    } else {
        debugDiv.style.display = 'none';
    }
}
</script>
@endsection
