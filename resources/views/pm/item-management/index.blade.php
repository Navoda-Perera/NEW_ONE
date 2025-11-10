@extends('layouts.modern-pm')

@section('title', 'Item Management')

@section('content')
<div class="container-fluid">
    <!-- Modern Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-info text-white p-4 rounded-top">
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h2 class="mb-1">
                            <i class="bi bi-search-heart"></i>
                            Item Management System
                        </h2>
                        <p class="mb-0 opacity-75">Search, view, and manage postal items using barcode scanner</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barcode Scanner Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <div class="row align-items-center">
                        <div class="col-12 text-center">
                            <h4 class="mb-0">
                                <i class="bi bi-upc-scan fs-3 me-2"></i>
                                Barcode Scanner
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Search Form -->
                    <form id="barcodeSearchForm" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-9">
                                <label for="barcode" class="form-label fw-bold text-dark">
                                    <i class="bi bi-qr-code-scan"></i> Enter or Scan Barcode
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-info text-white">
                                        <i class="bi bi-upc-scan"></i>
                                    </span>
                                    <input type="text" class="form-control" id="barcode" name="barcode"
                                           placeholder="Scan barcode or type manually..." autofocus>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-lightbulb"></i> Tip: Focus on this field and scan directly, or type the barcode manually
                                </small>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-info btn-lg w-100 shadow">
                                    <i class="bi bi-search"></i> Search Item
                                </button>
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="button" class="btn btn-warning btn-sm" onclick="testSearch()">
                                    🔧 Test Search (Debug)
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Search Results -->
                    <div id="searchResults" class="mt-4" style="display: none;">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-search"></i>
                                        <strong>Search Results</strong>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" onclick="clearSearch()"></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="searchMessage" class="mb-3"></div>
                                <div id="itemDetails"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Document ready - jQuery loaded successfully');
    console.log('Search route URL:', '{{ route("pm.item-management.search-barcode") }}');
    console.log('CSRF Token:', '{{ csrf_token() }}');
    
    // Auto-focus on barcode input
    $('#barcode').focus();

    // Handle barcode form submission
    $('#barcodeSearchForm').on('submit', function(e) {
        console.log('Form submitted');
        e.preventDefault();
        searchByBarcode();
    });

    // Auto-submit on barcode scan (assuming barcode scanner sends Enter)
    $('#barcode').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            console.log('Enter key pressed');
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
        // Enhanced error display
        $('#searchResults').show();
        $('#searchMessage').html(`
            <div class="alert alert-warning border-0 mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Please enter a barcode</strong> to search for items.
            </div>
        `);
        $('#itemDetails').html('');
        $('#barcode').focus();
        return;
    }

    // Show modern loading
    $('#searchResults').show();
    $('#searchMessage').html(`
        <div class="d-flex align-items-center text-info">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <strong>Searching for item with barcode: ${barcode}</strong>
        </div>
    `);
    $('#itemDetails').html('');

    console.log('Making AJAX request to:', '{{ route("pm.item-management.search-barcode") }}');

    $.ajax({
        url: '{{ route("pm.item-management.search-barcode") }}',
        method: 'POST',
        data: {
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        beforeSend: function() {
            console.log('AJAX request starting...');
            console.log('URL:', '{{ route("pm.item-management.search-barcode") }}');
            console.log('Data:', {
                barcode: barcode,
                _token: '{{ csrf_token() }}'
            });
        },
        success: function(response) {
            console.log('AJAX Success Response:', response);
            console.log('Response type:', typeof response);
            console.log('Response success:', response.success);
            
            if (response.success) {
                $('#searchMessage').html(`
                    <div class="alert alert-success border-0 mb-0">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>${response.message}</strong>
                    </div>
                `);
                console.log('About to call displayItemDetails with:', response.item, response.type);
                displayItemDetails(response.item, response.type);
            } else {
                $('#searchMessage').html(`
                    <div class="alert alert-warning border-0 mb-0">
                        <i class="bi bi-search"></i>
                        <strong>${response.message}</strong>
                    </div>
                `);
                $('#itemDetails').html('');
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr);
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response Text:', xhr.responseText);
            console.log('Status Code:', xhr.status);
            
            let errorMessage = 'Error searching for item. Please try again.';
            
            if (xhr.status === 419) {
                errorMessage = 'CSRF token mismatch. Please refresh the page.';
            } else if (xhr.status === 422) {
                errorMessage = 'Validation error. Please check your input.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error. Please contact support.';
            }
            
            $('#searchMessage').html(`
                <div class="alert alert-danger border-0 mb-0">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>${errorMessage}</strong> (Status: ${xhr.status})
                </div>
            `);
            $('#itemDetails').html('');
        }
    });
}

function displayItemDetails(item, type) {
    console.log('displayItemDetails called with:', {item: item, type: type});
    
    if (!item) {
        console.error('Item is null or undefined');
        return;
    }
    
    let html = '<div class="card border-0 shadow-sm">';
    html += '<div class="card-header bg-light border-bottom">';
    html += '<h5 class="mb-0 text-dark">';
    html += '<i class="bi bi-box-seam-fill text-info"></i> Item Details';
    html += '</h5>';
    html += '</div>';
    html += '<div class="card-body p-4">';

    console.log('Processing item type:', type);

    if (type === 'processed') {
        console.log('Displaying processed item details');
        // Main item details with modern styling
        html += '<div class="row">';
        html += '<div class="col-md-8">';
        html += '<form id="updateItemForm" data-item-id="' + item.id + '" class="needs-validation" novalidate>';

        // Modern form sections
        html += '<div class="mb-4">';
        html += '<h6 class="text-info border-bottom pb-2 mb-3">';
        html += '<i class="bi bi-upc-scan"></i> Barcode Information</h6>';
        html += '<input type="text" class="form-control form-control-lg" name="barcode" value="' + (item.barcode || '') + '" required>';
        html += '</div>';

        html += '<div class="mb-4">';
        html += '<h6 class="text-info border-bottom pb-2 mb-3">';
        html += '<i class="bi bi-person-fill"></i> Receiver Information</h6>';
        html += '<div class="row">';
        html += '<div class="col-12 mb-3">';
        html += '<label class="form-label fw-bold">Receiver Name</label>';
        html += '<input type="text" class="form-control" name="receiver_name" value="' + (item.receiver_name || '') + '" required>';
        html += '</div>';
        html += '<div class="col-12 mb-3">';
        html += '<label class="form-label fw-bold">Receiver Address</label>';
        html += '<textarea class="form-control" name="receiver_address" rows="3" required>' + (item.receiver_address || '') + '</textarea>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '<div class="mb-4">';
        html += '<h6 class="text-info border-bottom pb-2 mb-3">';
        html += '<i class="bi bi-box"></i> Item Details</h6>';
        html += '<div class="row">';
        html += '<div class="col-md-6 mb-3">';
        html += '<label class="form-label fw-bold">Weight (grams)</label>';
        html += '<div class="input-group">';
        html += '<input type="number" class="form-control" name="weight" value="' + (item.weight || '') + '" min="0" step="0.01" required>';
        html += '<span class="input-group-text">g</span>';
        html += '</div>';
        html += '</div>';
        html += '<div class="col-md-6 mb-3">';
        html += '<label class="form-label fw-bold">Amount</label>';
        html += '<div class="input-group">';
        html += '<span class="input-group-text">Rs.</span>';
        html += '<input type="number" class="form-control" name="amount" value="' + (item.amount || '') + '" min="0" step="0.01" required>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '</form>';
        html += '</div>';

        // Modern info panel
        html += '<div class="col-md-4">';
        html += '<div class="card bg-light border-0">';
        html += '<div class="card-header bg-info text-white">';
        html += '<h6 class="mb-0"><i class="bi bi-info-circle"></i> Item Status</h6>';
        html += '</div>';
        html += '<div class="card-body">';
        html += '<div class="mb-3">';
        html += '<small class="text-muted d-block">Barcode</small>';
        html += '<code class="fs-6">' + (item.barcode || '') + '</code>';
        html += '</div>';

        if (item.creator) {
            html += '<div class="mb-3">';
            html += '<small class="text-muted d-block">Customer</small>';
            html += '<strong>' + (item.creator.name || 'N/A') + '</strong>';
            html += '</div>';
        }

        html += '<div class="mb-3">';
        html += '<small class="text-muted d-block">Created</small>';
        html += '<span>' + (item.created_at ? new Date(item.created_at).toLocaleString() : 'N/A') + '</span>';
        html += '</div>';
        html += '<div class="mb-0">';
        html += '<small class="text-muted d-block">Last Updated</small>';
        html += '<span>' + (item.updated_at ? new Date(item.updated_at).toLocaleString() : 'N/A') + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Modern action buttons
        html += '<div class="mt-4 pt-3 border-top">';
        html += '<div class="d-flex gap-2 flex-wrap">';
        html += '<button type="button" class="btn btn-success btn-lg shadow-sm" onclick="updateItemInline(' + item.id + ')">';
        html += '<i class="bi bi-check-circle-fill"></i> Update Item</button>';
        if (!['dispatched', 'delivered'].includes(item.status)) {
            html += '<button type="button" class="btn btn-danger btn-lg shadow-sm" onclick="deleteItem(' + item.id + ')">';
            html += '<i class="bi bi-trash-fill"></i> Delete</button>';
        }
        html += '<button type="button" class="btn btn-secondary btn-lg shadow-sm" onclick="clearSearch()">';
        html += '<i class="bi bi-arrow-left"></i> Search Another</button>';
        html += '</div>';
        html += '</div>';

    } else if (type === 'temporary') {
        console.log('Displaying temporary item details');
        // Modern temporary item display
        html += '<div class="alert alert-warning border-0 mb-4">';
        html += '<i class="bi bi-exclamation-triangle-fill"></i> ';
        html += '<strong>Temporary Item</strong> - This item needs to be processed.';
        html += '</div>';

        html += '<div class="row">';
        html += '<div class="col-md-8">';
        html += '<h6 class="text-warning border-bottom pb-2 mb-3">';
        html += '<i class="bi bi-clock-history"></i> Item Information</h6>';

        const detailItems = [
            { label: 'Barcode', value: item.barcode || 'Not assigned', icon: 'upc-scan' },
            { label: 'Receiver', value: item.receiver_name || 'N/A', icon: 'person-fill' },
            { label: 'Address', value: item.receiver_address || 'N/A', icon: 'geo-alt-fill' },
            { label: 'Weight', value: (item.weight || '0') + 'g', icon: 'box' },
            { label: 'Amount', value: 'Rs. ' + parseFloat(item.amount || 0).toFixed(2), icon: 'currency-dollar' }
        ];

        detailItems.forEach(detail => {
            html += '<div class="mb-3 p-3 bg-light rounded">';
            html += '<div class="d-flex align-items-center">';
            html += '<i class="bi bi-' + detail.icon + ' text-warning me-2"></i>';
            html += '<small class="text-muted me-2">' + detail.label + ':</small>';
            html += '<strong>' + detail.value + '</strong>';
            html += '</div>';
            html += '</div>';
        });

        html += '</div>';
        html += '<div class="col-md-4">';
        html += '<div class="card bg-warning bg-opacity-10 border-warning">';
        html += '<div class="card-header border-warning">';
        html += '<h6 class="mb-0 text-warning"><i class="bi bi-upload"></i> Upload Info</h6>';
        html += '</div>';
        html += '<div class="card-body">';

        if (item.temporary_upload && item.temporary_upload.user) {
            html += '<p><strong>Customer:</strong><br>' + (item.temporary_upload.user.name || 'N/A') + '</p>';
        }
        html += '<p><strong>Service:</strong><br>' + (item.service_type || 'Standard') + '</p>';
        html += '<p><strong>Status:</strong><br><span class="badge bg-warning">' + (item.status || 'pending') + '</span></p>';
        html += '<p class="mb-0"><strong>Created:</strong><br>' + (item.created_at ? new Date(item.created_at).toLocaleString() : 'N/A') + '</p>';

        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '<div class="mt-4 pt-3 border-top">';
        html += '<div class="d-flex gap-2">';
        html += '<button type="button" class="btn btn-primary btn-lg shadow-sm" onclick="processTemporaryItem(' + item.id + ')">';
        html += '<i class="bi bi-gear-fill"></i> Process Item</button>';
        html += '<button type="button" class="btn btn-secondary btn-lg shadow-sm" onclick="clearSearch()">';
        html += '<i class="bi bi-arrow-left"></i> Search Another</button>';
        html += '</div>';
        html += '</div>';
    }

    html += '</div>';
    html += '</div>';

    console.log('Generated HTML length:', html.length);
    console.log('Setting HTML to #itemDetails');
    $('#itemDetails').html(html);
    console.log('displayItemDetails completed');
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

    // Modern loading state
    const updateBtn = $('button[onclick="updateItemInline(' + itemId + ')"]');
    const originalText = updateBtn.html();
    updateBtn.html('<i class="spinner-border spinner-border-sm"></i> Updating...').prop('disabled', true);

    $.ajax({
        url: '{{ route("pm.item-management.update", ":id") }}'.replace(':id', itemId),
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Update search message with success
                $('#searchMessage').html(`
                    <div class="alert alert-success border-0 mb-0">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>${response.message}</strong>
                    </div>
                `);

                // Refresh the display
                displayItemDetails(response.item, 'processed');

                // Show floating success notification
                showNotification('Item updated successfully!', 'success');
            } else {
                showNotification('Error: ' + (response.message || 'Failed to update item'), 'error');
                updateBtn.html(originalText).prop('disabled', false);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error updating item';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMessage = errors.join(', ');
            }
            showNotification(errorMessage, 'error');
            updateBtn.html(originalText).prop('disabled', false);
        }
    });
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
                showNotification('Item deleted successfully!', 'success');
                clearSearch();
            } else {
                showNotification('Error: ' + (response.message || 'Failed to delete item'), 'error');
            }
        },
        error: function(xhr) {
            showNotification('Error deleting item. Please try again.', 'error');
        }
    });
}

function processTemporaryItem(tempItemId) {
    if (!confirm('Do you want to process this temporary item and move it to the main system?')) {
        return;
    }
    showNotification('Processing temporary items is not yet implemented in this interface.', 'info');
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill';

    const notification = `
        <div class="alert ${alertClass} border-0 shadow position-fixed"
             style="top: 20px; right: 20px; z-index: 1050; min-width: 300px;"
             id="notification-${Date.now()}">
            <div class="d-flex align-items-center">
                <i class="bi bi-${icon} me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        </div>
    `;

    $('body').append(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        $(`#notification-${Date.now()}`).fadeOut(() => {
            $(this).remove();
        });
    }, 5000);
}

// Test function to debug search issues
function testSearch() {
    console.log('=== TEST SEARCH FUNCTION ===');
    
    // Check if jQuery is working
    if (typeof $ === 'undefined') {
        alert('jQuery is not loaded!');
        return;
    }
    
    // Set test barcode
    $('#barcode').val('bn675111');
    
    // Test if we can show the search results div
    $('#searchResults').show();
    $('#searchMessage').html('<div class="alert alert-info">🔧 Testing AJAX request...</div>');
    
    // Test AJAX call
    console.log('Testing AJAX call...');
    
    $.ajax({
        url: '{{ route("pm.item-management.search-barcode") }}',
        method: 'POST',
        data: {
            barcode: 'bn675111',
            _token: '{{ csrf_token() }}'
        },
        beforeSend: function(xhr) {
            console.log('AJAX beforeSend triggered');
            console.log('Headers:', xhr.getAllResponseHeaders());
        },
        success: function(data) {
            console.log('✅ AJAX Success:', data);
            $('#searchMessage').html('<div class="alert alert-success">✅ AJAX Success! Check console for response.</div>');
            
            if (data.success) {
                displayItemDetails(data.item, data.type);
            }
        },
        error: function(xhr, status, error) {
            console.log('❌ AJAX Error:', {
                status: status,
                error: error,
                statusCode: xhr.status,
                responseText: xhr.responseText,
                readyState: xhr.readyState
            });
            
            $('#searchMessage').html(`
                <div class="alert alert-danger">
                    ❌ AJAX Error: ${status} (${xhr.status})<br>
                    Error: ${error}<br>
                    Response: ${xhr.responseText}
                </div>
            `);
        },
        complete: function() {
            console.log('AJAX request completed');
        }
    });
}
</script>
@endsection
