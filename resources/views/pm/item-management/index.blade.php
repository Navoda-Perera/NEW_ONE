@extends('layouts.app')

@section('title', 'Item Management')

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
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.postmen.index') }}">
            <i class="bi bi-person-badge"></i> Postmen
        </a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Item Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Item Management</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Barcode Scanner Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-upc-scan"></i> Barcode Scanner - Item Management
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Instructions:</strong> Enter or scan a barcode to find and update item details instantly.
                    </div>

                    <form id="barcodeSearchForm">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label for="barcode" class="form-label">Enter or Scan Barcode</label>
                                <input type="text" class="form-control form-control-lg" id="barcode" name="barcode"
                                       placeholder="Scan barcode or enter manually..." autofocus>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-search"></i> Search Item
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Search Results -->
                    <div id="searchResults" class="mt-4" style="display: none;">
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Search Results:</strong>
                                    <span id="searchMessage"></span>
                                </div>
                                <button type="button" class="btn-close" onclick="clearSearch()"></button>
                            </div>
                        </div>
                        <div id="itemDetails"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-focus on barcode input
    $('#barcode').focus();

    // Handle barcode form submission
    $('#barcodeSearchForm').on('submit', function(e) {
        e.preventDefault();
        searchByBarcode();
    });

    // Auto-submit on barcode scan (assuming barcode scanner sends Enter)
    $('#barcode').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            setTimeout(function() {
                searchByBarcode();
            }, 100);
        }
    });
});

function searchByBarcode() {
    const barcode = $('#barcode').val().trim();
    console.log('Searching for barcode:', barcode);

    if (!barcode) {
        alert('Please enter a barcode');
        return;
    }

    // Show loading
    $('#searchResults').show();
    $('#searchMessage').html('<i class="spinner-border spinner-border-sm"></i> Searching...');
    $('#itemDetails').html('');

    console.log('Making AJAX request to:', '{{ route("pm.item-management.search-barcode") }}');

    $.ajax({
        url: '{{ route("pm.item-management.search-barcode") }}',
        method: 'POST',
        data: {
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            console.log('AJAX Success Response:', response);
            if (response.success) {
                $('#searchMessage').html(response.message);
                displayItemDetails(response.item, response.type);
            } else {
                $('#searchMessage').html('<span class="text-danger">' + response.message + '</span>');
                $('#itemDetails').html('');
            }
        },
        error: function(xhr) {
            console.log('AJAX Error:', xhr);
            console.log('Response Text:', xhr.responseText);
            console.log('Status:', xhr.status);
            $('#searchMessage').html('<span class="text-danger">Error searching for item. Status: ' + xhr.status + '</span>');
            $('#itemDetails').html('');

            // Show more detailed error information
            if (xhr.responseText) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    console.log('Parsed Error Response:', errorResponse);
                    if (errorResponse.message) {
                        $('#searchMessage').html('<span class="text-danger">Error: ' + errorResponse.message + '</span>');
                    }
                } catch (e) {
                    console.log('Could not parse error response');
                }
            }
        }
    });
}

function displayItemDetails(item, type) {
    let html = '<div class="card">';
    html += '<div class="card-body">';

    if (type === 'processed') {
        // Main item details with inline editing capability
        html += '<div class="row">';
        html += '<div class="col-md-8">';
        html += '<form id="updateItemForm" data-item-id="' + item.id + '">';
        html += '<div class="row">';

        // Barcode row
        html += '<div class="col-12 mb-3">';
        html += '<label class="form-label"><strong>Barcode</strong></label>';
        html += '<input type="text" class="form-control" name="barcode" value="' + item.barcode + '" required>';
        html += '</div>';

        // Receiver Name
        html += '<div class="col-12 mb-3">';
        html += '<label class="form-label"><strong>Receiver Name</strong></label>';
        html += '<input type="text" class="form-control" name="receiver_name" value="' + item.receiver_name + '" required>';
        html += '</div>';

        // Receiver Address
        html += '<div class="col-12 mb-3">';
        html += '<label class="form-label"><strong>Receiver Address</strong></label>';
        html += '<textarea class="form-control" name="receiver_address" rows="3" required>' + item.receiver_address + '</textarea>';
        html += '</div>';

        // Weight and Amount row
        html += '<div class="col-md-6 mb-3">';
        html += '<label class="form-label"><strong>Weight (grams)</strong></label>';
        html += '<input type="number" class="form-control" name="weight" value="' + item.weight + '" min="0" step="0.01" required>';
        html += '</div>';
        html += '<div class="col-md-6 mb-3">';
        html += '<label class="form-label"><strong>Amount (Rs.)</strong></label>';
        html += '<input type="number" class="form-control" name="amount" value="' + item.amount + '" min="0" step="0.01" required>';
        html += '</div>';

        html += '</div>'; // End row
        html += '</form>';
        html += '</div>';

        // Item Info Panel
        html += '<div class="col-md-4">';
        html += '<div class="card bg-light">';
        html += '<div class="card-header"><h6 class="mb-0">Item Information</h6></div>';
        html += '<div class="card-body">';
        html += '<p><strong>Original Barcode:</strong> <code>' + item.barcode + '</code></p>';
        if (item.creator) {
            html += '<p><strong>Customer:</strong> ' + item.creator.name + '</p>';
        }
        html += '<p><strong>Created:</strong> ' + new Date(item.created_at).toLocaleString() + '</p>';
        html += '<p><strong>Updated:</strong> ' + new Date(item.updated_at).toLocaleString() + '</p>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Action buttons
        html += '<div class="mt-4">';
        html += '<div class="d-flex gap-2">';
        html += '<button type="button" class="btn btn-success" onclick="updateItemInline(' + item.id + ')">';
        html += '<i class="bi bi-check-circle"></i> Update Item</button>';

        html += '<a href="{{ route("pm.item-management.edit", ":id") }}" class="btn btn-warning">'.replace(':id', item.id);
        html += '<i class="bi bi-pencil"></i> Full Edit</a>';

        if (!['dispatched', 'delivered'].includes(item.status)) {
            html += '<button type="button" class="btn btn-danger" onclick="deleteItem(' + item.id + ')">';
            html += '<i class="bi bi-trash"></i> Delete Item</button>';
        }

        html += '<button type="button" class="btn btn-secondary" onclick="clearSearch()">';
        html += '<i class="bi bi-arrow-left"></i> Search Another</button>';
        html += '</div>';
        html += '</div>';

    } else if (type === 'temporary') {
        // Temporary item details - read-only with process option
        html += '<div class="alert alert-warning">';
        html += '<i class="bi bi-exclamation-triangle"></i> This item is in temporary status and not yet processed.';
        html += '</div>';
        html += '<div class="row">';
        html += '<div class="col-md-8">';
        html += '<h6>Item Information</h6>';
        html += '<p><strong>Barcode:</strong> <code>' + (item.barcode || 'Not assigned') + '</code></p>';
        html += '<p><strong>Receiver:</strong> ' + item.receiver_name + '</p>';
        html += '<p><strong>Address:</strong> ' + item.receiver_address + '</p>';
        html += '<p><strong>Weight:</strong> ' + item.weight + 'g</p>';
        html += '<p><strong>Amount:</strong> Rs. ' + parseFloat(item.amount || 0).toFixed(2) + '</p>';
        html += '<p><strong>Status:</strong> <span class="badge bg-warning">' + item.status + '</span></p>';
        html += '</div>';
        html += '<div class="col-md-4">';
        html += '<h6>Upload Information</h6>';
        if (item.temporary_upload && item.temporary_upload.user) {
            html += '<p><strong>Customer:</strong> ' + item.temporary_upload.user.name + '</p>';
        }
        html += '<p><strong>Service Type:</strong> ' + (item.service_type || 'N/A') + '</p>';
        html += '<p><strong>Created:</strong> ' + new Date(item.created_at).toLocaleString() + '</p>';
        html += '</div>';
        html += '</div>';

        html += '<div class="mt-3">';
        html += '<button type="button" class="btn btn-primary" onclick="processTemporaryItem(' + item.id + ')">';
        html += '<i class="bi bi-gear"></i> Process Item</button>';
        html += '<button type="button" class="btn btn-secondary" onclick="clearSearch()">';
        html += '<i class="bi bi-arrow-left"></i> Search Another</button>';
        html += '</div>';
    }

    html += '</div>';
    html += '</div>';

    $('#itemDetails').html(html);
}

function clearSearch() {
    $('#searchResults').hide();
    $('#barcode').val('').focus();
}

function updateItemInline(itemId) {
    const form = $('#updateItemForm');
    const formData = {
        barcode: form.find('input[name="barcode"]').val(),
        receiver_name: form.find('input[name="receiver_name"]').val(),
        receiver_address: form.find('textarea[name="receiver_address"]').val(),
        weight: form.find('input[name="weight"]').val(),
        amount: form.find('input[name="amount"]').val(),
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };

    // Show loading
    const updateBtn = $('button[onclick="updateItemInline(' + itemId + ')"]');
    const originalText = updateBtn.html();
    updateBtn.html('<i class="spinner-border spinner-border-sm"></i> Updating...').prop('disabled', true);

    $.ajax({
        url: '{{ route("pm.item-management.update", ":id") }}'.replace(':id', itemId),
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Show success message
                $('#searchMessage').html('<span class="text-success">' + response.message + '</span>');

                // Update the display with new data
                displayItemDetails(response.item, 'processed');

                // Show success alert
                const successAlert = '<div class="alert alert-success alert-dismissible fade show mt-3">' +
                    '<strong>Success!</strong> Item updated successfully.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                $('#itemDetails').prepend(successAlert);

                // Auto-hide success alert after 3 seconds
                setTimeout(function() {
                    $('.alert-success').fadeOut();
                }, 3000);
            } else {
                alert('Error: ' + (response.message || 'Failed to update item'));
                updateBtn.html(originalText).prop('disabled', false);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error updating item';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMessage = errors.join(', ');
            }
            alert(errorMessage);
            updateBtn.html(originalText).prop('disabled', false);
        }
    });
}

function processTemporaryItem(tempItemId) {
    if (!confirm('Do you want to process this temporary item and move it to the main system?')) {
        return;
    }

    // This would need to be implemented based on your existing temporary item processing logic
    alert('Processing temporary items is not yet implemented in this interface. Please use the regular PM workflow.');
}

function deleteItem(itemId) {
    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        return;
    }

    $.ajax({
        url: '{{ route("pm.item-management.delete", ":id") }}'.replace(':id', itemId),
        method: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                clearSearch(); // Clear the search and go back to scanner
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error deleting item');
        }
    });
}
</script>
@endpush
