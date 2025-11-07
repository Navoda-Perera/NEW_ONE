@extends('layouts.app')

@section('title', 'PM Bulk Upload')

@section('styles')
<style>
    /* Custom styles for scrollable tables */
    .scrollable-table-container {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .scrollable-table-container table {
        margin-bottom: 0;
    }

    .scrollable-table-container thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        border-bottom: 2px solid #dee2e6;
    }

    /* Custom scrollbar styling */
    .scrollable-table-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .scrollable-table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scrollable-table-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scrollable-table-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure table cells don't break on narrow screens */
    .scrollable-table-container table th,
    .scrollable-table-container table td {
        white-space: nowrap;
        min-width: 120px;
    }

    /* Columns info container with scroll */
    .columns-info-container {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 15px;
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.items.pending') }}">
            <i class="bi bi-clock-history"></i> Pending Items
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('pm.bulk-upload') }}">
            <i class="bi bi-cloud-upload"></i> Bulk Upload
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('pm.dashboard') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="fw-bold text-dark mb-0">PM Bulk Upload <span class="badge bg-success">Direct Processing</span></h2>
            </div>

            <!-- Instructions Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>PM Direct Upload Instructions</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <strong>Note:</strong> PM uploads go directly to the final system (Items & ItemBulk tables) and are automatically accepted.
                    </div>
                    <ol class="mb-0">
                        <li>Select the service type for all items in the file</li>
                        <li>Download the CSV template for the selected service type</li>
                        <li>Fill in your item details following the format</li>
                        <li>Upload your completed CSV file</li>
                        <li>Items will be immediately processed and available in the system</li>
                    </ol>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pm.store-bulk-upload') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <!-- PM's Post Office (Auto-selected) -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Origin Post Office
                            </label>
                            <div class="form-control-plaintext bg-light border rounded p-2">
                                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                <strong>{{ $user->location->name }}</strong> ({{ $user->location->code }})
                                <small class="text-muted ms-2">- Your assigned post office</small>
                            </div>
                            <div class="form-text">
                                <small class="text-muted">All uploads will be processed from your assigned post office.</small>
                            </div>
                        </div>

                        <!-- Service Type Selection -->
                        <div class="mb-4">
                            <label for="service_type" class="form-label fw-semibold">
                                <i class="bi bi-box-seam me-1"></i>Service Type for All Items
                            </label>
                            <select id="service_type" class="form-select @error('service_type') is-invalid @enderror"
                                    name="service_type" required>
                                <option value="">Select Service Type</option>
                                @foreach($serviceTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('service_type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">This service type will be applied to all items in the uploaded CSV file.</small>
                            </div>

                            <!-- Template Download Button -->
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-success" id="download-template-btn" onclick="downloadTemplate()" disabled>
                                    <i class="bi bi-download me-2"></i>Download CSV Template
                                </button>
                                <small class="text-muted ms-2" id="template-help">Select a service type first to download the template</small>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="bulk_file" class="form-label fw-semibold">
                                <i class="bi bi-cloud-upload me-1"></i>Upload CSV File
                            </label>
                            <input id="bulk_file" type="file" class="form-control @error('bulk_file') is-invalid @enderror"
                                   name="bulk_file" accept=".csv" required>
                            @error('bulk_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">
                                    <strong>Only CSV files are supported.</strong> Maximum file size: 2MB<br>
                                    📝 <strong>To convert Excel to CSV:</strong> In Excel, go to File > Save As > Choose "CSV (Comma delimited)" format
                                </small>
                            </div>

                            <!-- File Preview Section -->
                            <div id="file-preview" class="mt-3" style="display: none;">
                                <div class="alert alert-success">
                                    <i class="bi bi-file-earmark-check me-2"></i>
                                    <span id="file-name"></span> selected successfully!
                                    <div class="mt-2">
                                        <small>File size: <span id="file-size"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- File Format Requirements -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Required CSV Columns:</h6>
                                    <div class="alert alert-success mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Note:</strong> PM uploads go directly to final tables (no approval needed).
                                    </div>

                                    <!-- Dynamic columns based on service type -->
                                    <div id="columns-info" class="columns-info-container">
                                        <div class="alert alert-warning">
                                            <i class="bi bi-arrow-up me-2"></i>
                                            Please select a service type above to see the required columns for your template.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('pm.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success" id="upload-btn">
                                <i class="bi bi-cloud-upload me-2"></i>Process Upload Directly
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sample Data Preview -->
                                    </div>
                    </form>
                </div>
            </div>

            <!-- Sample Data Preview -->
            <div class="card mt-4" id="sample-data-card" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0">Sample CSV Format for <span id="sample-service-type"></span></h6>
                </div>
                <div class="card-body">
                    <div class="scrollable-table-container">
                        <div id="sample-table-container">
                            <!-- Dynamic table will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<script>
// Service type configurations for PM bulk upload (matches customer format but auto-processes)
const serviceTypeConfigs = {
    'register_post': {
        label: 'Register Post',
        columns: [
            { name: 'receiver_name', label: 'Receiver Name', required: true, example: 'John Doe' },
            { name: 'receiver_address', label: 'Complete Receiver Address', required: true, example: '123 Main St, Colombo 07' },
            { name: 'item_value', label: 'Item Value (LKR)', required: true, example: '2500.00' },
            { name: 'weight', label: 'Weight in Grams', required: true, example: '500' },
            { name: 'postage', label: 'Postage (LKR) - Optional', required: false, example: '250.00' },
            { name: 'barcode', label: 'Barcode - Optional', required: false, example: 'REG1234567' },
            { name: 'contact_number', label: 'Receiver Contact Number', required: true, example: '0771234567' },
            { name: 'sender_name', label: 'Sender Name - Optional', required: false, example: 'ABC Company' },
        ],
        sampleData: [
            {
                receiver_name: 'John Doe',
                receiver_address: '123 Main St, Colombo 07',
                item_value: '2500.00',
                weight: '500',
                postage: '250.00',
                barcode: 'REG1234567',
                contact_number: '0771234567',
                sender_name: 'ABC Company',
            },
            {
                receiver_name: 'Jane Smith',
                receiver_address: '456 Park Road, Kandy',
                item_value: '1800.00',
                weight: '300',
                postage: '250.00',
                barcode: 'REG1234568',
                contact_number: '0753456789',
                sender_name: 'XYZ Store',
            }
        ]
    },
    'slp_courier': {
        label: 'SLP Courier',
        columns: [
            { name: 'receiver_name', label: 'Receiver Name', required: true, example: 'Alice Johnson' },
            { name: 'receiver_address', label: 'Complete Receiver Address', required: true, example: '789 Beach Road, Galle' },
            { name: 'item_value', label: 'Item Value (LKR)', required: true, example: '3500.00' },
            { name: 'weight', label: 'Weight in Grams', required: true, example: '750' },
            { name: 'postage', label: 'Postage (LKR) - Optional', required: false, example: '200.00' },
            { name: 'barcode', label: 'Barcode - Optional', required: false, example: 'SLP1234567' },
            { name: 'contact_number', label: 'Receiver Contact Number', required: true, example: '0912234567' },
            { name: 'sender_name', label: 'Sender Name - Optional', required: false, example: 'Online Shop' },
        ],
        sampleData: [
            {
                receiver_name: 'Alice Johnson',
                receiver_address: '789 Beach Road, Galle',
                item_value: '3500.00',
                weight: '750',
                postage: '200.00',
                barcode: 'SLP1234567',
                contact_number: '0912234567',
                sender_name: 'Online Shop',
            },
            {
                receiver_name: 'Bob Wilson',
                receiver_address: '321 Hill View, Nuwara Eliya',
                item_value: '2200.00',
                weight: '400',
                postage: '200.00',
                barcode: 'SLP1234568',
                contact_number: '0522345678',
                sender_name: 'Tech Store',
            }
        ]
    },
    'cod': {
        label: 'COD (Cash on Delivery)',
        columns: [
            { name: 'receiver_name', label: 'Receiver Name', required: true, example: 'Mary Wilson' },
            { name: 'receiver_address', label: 'Complete Receiver Address', required: true, example: '555 Matara Road, Galle' },
            { name: 'item_value', label: 'Item Value (LKR)', required: true, example: '5000.00' },
            { name: 'weight', label: 'Weight in Grams', required: true, example: '1000' },
            { name: 'postage', label: 'Postage (LKR) - Optional', required: false, example: '290.00' },
            { name: 'barcode', label: 'Barcode - Optional', required: false, example: 'COD1234567' },
            { name: 'contact_number', label: 'Receiver Contact Number', required: true, example: '0751234567' },
            { name: 'sender_name', label: 'Sender Name - Optional', required: false, example: 'E-commerce Store' },
            { name: 'cod_amount', label: 'COD Collection Amount (LKR)', required: false, example: '5000.00' },
            { name: 'payment_method', label: 'Payment Method (cash/card)', required: false, example: 'cash' },
        ],
        sampleData: [
            {
                receiver_name: 'Mary Wilson',
                receiver_address: '555 Matara Road, Galle',
                item_value: '5000.00',
                weight: '1000',
                postage: '290.00',
                barcode: 'COD1234567',
                contact_number: '0751234567',
                sender_name: 'E-commerce Store',
                cod_amount: '5000.00',
                payment_method: 'cash',
            },
            {
                receiver_name: 'David Lee',
                receiver_address: '777 Kurunegala Road, Kuliyapitiya',
                item_value: '3500.00',
                weight: '750',
                postage: '290.00',
                barcode: 'COD1234568',
                contact_number: '0783456789',
                sender_name: 'Fashion Store',
                cod_amount: '3500.00',
                payment_method: 'card',
            }
        ]
    }
};

// Function to update UI based on selected service type
function updateServiceTypeUI(serviceType) {
    const config = serviceTypeConfigs[serviceType];
    if (!config) {
        // Hide everything if no service type selected
        document.getElementById('download-template-btn').disabled = true;
        const templateHelp = document.getElementById('template-help');
        if (templateHelp) templateHelp.textContent = 'Select a service type first to download the template';

        document.getElementById('columns-info').innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-arrow-up me-2"></i>
                Please select a service type above to see the required columns for your template.
            </div>
        `;
        document.getElementById('sample-data-card').style.display = 'none';
        return;
    }

    // Enable download button and update help text
    document.getElementById('download-template-btn').disabled = false;
    const templateHelp = document.getElementById('template-help');
    if (templateHelp) templateHelp.textContent = `Download ${config.label} template with required columns`;

    // Update columns info
    const requiredColumns = config.columns.filter(col => col.required);
    const optionalColumns = config.columns.filter(col => !col.required);

    let columnsHtml = '<div class="row">';

    // Required columns
    if (requiredColumns.length > 0) {
        columnsHtml += '<div class="col-md-6"><h6 class="text-danger mb-3"><i class="bi bi-asterisk me-1"></i>Required Columns:</h6><div class="list-group list-group-flush">';
        requiredColumns.forEach(col => {
            columnsHtml += `
                <div class="list-group-item border-0 px-0 py-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="text-primary">${col.name}</strong>
                            <div class="text-muted small">${col.label}</div>
                        </div>
                        <span class="badge bg-light text-dark small">${col.example}</span>
                    </div>
                </div>
            `;
        });
        columnsHtml += '</div></div>';
    }

    // Optional columns
    if (optionalColumns.length > 0) {
        columnsHtml += '<div class="col-md-6"><h6 class="text-info mb-3"><i class="bi bi-plus-circle me-1"></i>Optional Columns:</h6><div class="list-group list-group-flush">';
        optionalColumns.forEach(col => {
            columnsHtml += `
                <div class="list-group-item border-0 px-0 py-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="text-secondary">${col.name}</strong>
                            <div class="text-muted small">${col.label}</div>
                        </div>
                        <span class="badge bg-light text-dark small">${col.example}</span>
                    </div>
                </div>
            `;
        });
        columnsHtml += '</div></div>';
    }

    columnsHtml += '</div>';

    const columnsInfoElement = document.getElementById('columns-info');
    columnsInfoElement.innerHTML = columnsHtml;
    // Reset scroll position when content changes
    columnsInfoElement.scrollTop = 0;

    // Update sample data table
    updateSampleTable(serviceType, config);
}

// Function to update sample table
function updateSampleTable(serviceType, config) {
    const columns = config.columns;
    const sampleData = config.sampleData;

    let tableHtml = '<table class="table table-sm table-bordered table-hover mb-0"><thead class="table-success"><tr>';

    // Table headers
    columns.forEach(col => {
        const required = col.required ? ' <span class="text-danger">*</span>' : '';
        tableHtml += `<th class="text-nowrap">${col.name}${required}</th>`;
    });
    tableHtml += '</tr></thead><tbody>';

    // Sample data rows
    sampleData.forEach((row, index) => {
        const rowClass = index % 2 === 0 ? '' : 'table-light';
        tableHtml += `<tr class="${rowClass}">`;
        columns.forEach(col => {
            const cellValue = row[col.name] || '';
            tableHtml += `<td class="text-nowrap" title="${cellValue}">${cellValue}</td>`;
        });
        tableHtml += '</tr>';
    });

    tableHtml += '</tbody></table>';

    document.getElementById('sample-table-container').innerHTML = tableHtml;
    document.getElementById('sample-service-type').textContent = config.label;
    document.getElementById('sample-data-card').style.display = 'block';
}

// Function to generate and download template
function downloadTemplate() {
    const serviceType = document.getElementById('service_type').value;
    const config = serviceTypeConfigs[serviceType];

    if (!config) {
        alert('Please select a service type first.');
        return;
    }

    // Generate CSV headers
    const headers = config.columns.map(col => col.name);
    const csvHeaders = headers.join(',');

    // Generate sample data
    const csvRows = config.sampleData.map(row => {
        return headers.map(header => {
            const value = row[header] || '';
            // Escape values that contain commas
            return value.includes(',') ? `"${value}"` : value;
        }).join(',');
    });

    const csvContent = [csvHeaders, ...csvRows].join('\n');

    // Create download
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', `pm_${serviceType}_bulk_upload_template.csv`);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Service type change handler
document.getElementById('service_type').addEventListener('change', function(e) {
    updateServiceTypeUI(e.target.value);
});

// File upload preview
document.getElementById('bulk_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');

    if (file) {
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        preview.style.display = 'block';

        // Enable upload button
        document.getElementById('upload-btn').disabled = false;
    } else {
        preview.style.display = 'none';
        document.getElementById('upload-btn').disabled = true;
    }
});

// Form validation before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const serviceType = document.getElementById('service_type').value;
    const file = document.getElementById('bulk_file').files[0];
    const postOffice = document.getElementById('origin_post_office_id').value;

    if (!postOffice) {
        e.preventDefault();
        alert('Please select an origin post office.');
        return;
    }

    if (!serviceType) {
        e.preventDefault();
        alert('Please select a service type.');
        return;
    }

    if (!file) {
        e.preventDefault();
        alert('Please select a file to upload.');
        return;
    }

    // Check file size (2MB limit)
    if (file.size > 2 * 1024 * 1024) {
        e.preventDefault();
        alert('File size must be less than 2MB.');
        return;
    }

    // Show loading state
    const btn = document.getElementById('upload-btn');
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing Items...';
    btn.disabled = true;
});

// Initialize upload button state
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('upload-btn').disabled = true;

    // Initialize with any pre-selected service type
    const currentServiceType = document.getElementById('service_type').value;
    if (currentServiceType) {
        updateServiceTypeUI(currentServiceType);
    }
});
</script>
@endsection
