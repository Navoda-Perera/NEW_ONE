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
                    @switch($temporaryUpload->status)
                        @case('pending')
                            <span class="badge bg-warning">Pending</span>
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
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>File Name:</strong> {{ $temporaryUpload->original_filename }}</p>
                            <p><strong>Upload Date:</strong> {{ $temporaryUpload->created_at->format('M d, Y H:i:s') }}</p>
                            <p><strong>Total Items:</strong> {{ $temporaryUpload->total_items }}</p>
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
                    <div class="card-header">
                        <h5 class="mb-0">Items from Upload ({{ $temporaryUpload->associates->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Receiver</th>
                                        <th>Address</th>
                                        <th>Service Type</th>
                                        <th>Amount</th>
                                        <th>Weight</th>
                                        <th>Postage</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($temporaryUpload->associates as $index => $associate)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $associate->receiver_name }}</td>
                                            <td>{{ Str::limit($associate->receiver_address, 50) }}</td>
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
                                                <button class="btn btn-sm btn-outline-primary" onclick="editItem({{ $associate->id }}, '{{ $associate->receiver_name }}', '{{ $associate->receiver_address }}', '{{ $associate->item_value }}', '{{ $associate->service_type }}', '{{ $associate->weight }}', '{{ $associate->amount }}')" data-bs-toggle="modal" data-bs-target="#editItemModal">
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

function editItem(id, receiverName, receiverAddress, itemValue, serviceType, weight, amount) {
    currentEditId = id;
    document.getElementById('edit_receiver_name').value = receiverName;
    document.getElementById('edit_receiver_address').value = receiverAddress;
    document.getElementById('edit_item_value').value = itemValue;
    document.getElementById('edit_service_type').value = serviceType;
    document.getElementById('edit_weight').value = weight;
    document.getElementById('edit_amount').value = amount;
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
</script>
@endsection
