@extends('layouts.app')

@section('title', 'Bulk Upload')

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
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('customer.services.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="fw-bold text-dark mb-0">Bulk Upload <span class="badge bg-info">temporary_list</span></h2>
            </div>

            <!-- Instructions Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Instructions</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Download the CSV template below</li>
                        <li>Fill in your item details following the format</li>
                        <li>Select the service type for all items in the file</li>
                        <li>Upload your completed CSV file</li>
                        <li>Review and confirm the items before final submission</li>
                    </ol>
                </div>
            </div>

            <!-- Template Download -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="bi bi-download fs-1 text-success mb-3"></i>
                    <h5>Download CSV Template</h5>
                    <p class="text-muted">Select a service type above, then download the customized template</p>
                    <button class="btn btn-success" id="download-template-btn" onclick="downloadTemplate()" disabled>
                        <i class="bi bi-download me-2"></i>Download Template
                    </button>
                    <div id="template-info" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <strong id="selected-service-label"></strong> template will include specific columns for this service type.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('customer.services.store-bulk-upload') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <!-- Origin Post Office Selection -->
                        <div class="mb-3">
                            <label for="origin_post_office_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Origin Post Office
                            </label>
                            <select id="origin_post_office_id" class="form-select @error('origin_post_office_id') is-invalid @enderror"
                                    name="origin_post_office_id" required>
                                <option value="">Select Origin Post Office</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('origin_post_office_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} ({{ $location->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_post_office_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                        </div>


                        <!-- Service Type Instructions -->
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <strong>Note:</strong> Select the service type above that will apply to all items in your CSV file.
                                You can also specify individual <code>service_type</code> for each item in your CSV file.
                                Supported values: <code>register_post</code>, <code>slp_courier</code>, <code>cod</code>, <code>remittance</code>.
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

                        <!-- CSV Format Preview -->
                        <div class="mb-4" id="csv-format-section" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-table me-2"></i>CSV Format for <span id="csv-service-type"></span></h6>
                                    <div class="table-responsive">
                                        <div id="csv-format-table">
                                            <!-- Dynamic CSV format table will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('customer.services.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="upload-btn">
                                <i class="bi bi-cloud-upload me-2"></i>Upload File & Process
                            </button>
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
                    <div class="table-responsive">
                        <div id="sample-table-container">
                            <!-- Dynamic table will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Service type specific templates and configurations
const serviceTypeConfigs = {
    'register_post': {
        label: 'Register Post',
        columns: [
            { name: 'receiver_name', label: 'Receiver Name', required: true, example: 'John Doe' },
            { name: 'receiver_address', label: 'Complete Receiver Address', required: true, example: '123 Main St, Colombo 07' },
            { name: 'contact_number', label: 'Receiver Contact Number', required: false, example: '0771234567' },
            { name: 'weight', label: 'Weight in Grams', required: true, example: '250' },
            { name: 'postage', label: 'Postage (LKR)', required: false, example: '75.00' },
            { name: 'barcode', label: 'Barcode (Optional)', required: false, example: 'BC001234567' },
            { name: 'reg_number', label: 'Registration Number', required: false, example: 'REG001' },
            { name: 'priority_level', label: 'Priority (normal/express)', required: false, example: 'normal' },
            { name: 'notes', label: 'Additional Notes', required: false, example: 'Handle with care' }
        ],
        sampleData: [
            {
                receiver_name: 'John Doe',
                receiver_address: '123 Main St, Colombo 07',
                contact_number: '0771234567',
                weight: '250',
                postage: '75.00',
                barcode: 'BC001234567',
                reg_number: 'REG001',
                priority_level: 'normal',
                notes: 'Handle with care'
            },
            {
                receiver_name: 'Jane Smith',
                receiver_address: '456 Galle Road, Dehiwala',
                contact_number: '0779876543',
                weight: '150',
                postage: '65.00',
                barcode: 'BC001234568',
                reg_number: 'REG002',
                priority_level: 'express',
                notes: 'Urgent document'
            }
        ]
    },
    'slp_courier': {
        label: 'SLP Courier',
        columns: [
            { name: 'receiver_name', label: 'Receiver Name', required: true, example: 'Bob Johnson' },
            { name: 'receiver_address', label: 'Complete Receiver Address', required: true, example: '789 Kandy Road, Peradeniya' },
            { name: 'contact_number', label: 'Receiver Contact Number', required: false, example: '0771234567' },
            { name: 'weight', label: 'Weight in Grams', required: true, example: '500' },
            { name: 'postage', label: 'Postage (LKR)', required: false, example: '120.00' },
            { name: 'barcode', label: 'Barcode (Optional)', required: false, example: 'SLP001234567' },
            { name: 'notes', label: 'Additional Notes', required: false, example: 'Express package' }
        ],
        sampleData: [
            {
                receiver_name: 'Bob Johnson',
                receiver_address: '789 Kandy Road, Peradeniya',
                contact_number: '0771234567',
                weight: '500',
                postage: '120.00',
                barcode: 'SLP001234567',
                notes: 'Express package'
            },
            {
                receiver_name: 'Alice Brown',
                receiver_address: '321 Negombo Road, Gampaha',
                contact_number: '0779876543',
                weight: '300',
                postage: '95.00',
                barcode: 'SLP001234568',
                notes: 'Standard delivery'
            }
        ]
    },
    'cod': {
        label: 'Cash on Delivery (COD)',
        columns: [
            { name: 'receiver_name', label: 'Receiver Name', required: true, example: 'Mary Wilson' },
            { name: 'receiver_address', label: 'Complete Receiver Address', required: true, example: '555 Matara Road, Galle' },
            { name: 'item_value', label: 'Item Value (LKR)', required: true, example: '5000.00' },
            { name: 'weight', label: 'Weight in Grams', required: true, example: '1000' },
            { name: 'postage', label: 'Postage (LKR)', required: false, example: '150.00' },
            { name: 'barcode', label: 'Barcode (Optional)', required: false, example: 'COD001234567' },
            { name: 'cod_amount', label: 'COD Collection Amount (LKR)', required: true, example: '5000.00' },
            { name: 'contact_number', label: 'Receiver Contact Number', required: true, example: '0751234567' },
            { name: 'payment_method', label: 'Payment Method (cash/card)', required: false, example: 'cash' },
            { name: 'delivery_instructions', label: 'Special Delivery Instructions', required: false, example: 'Call before delivery' },
            { name: 'notes', label: 'Additional Notes', required: false, example: 'COD payment required' }
        ],
        sampleData: [
            {
                receiver_name: 'Mary Wilson',
                receiver_address: '555 Matara Road, Galle',
                item_value: '5000.00',
                weight: '1000',
                postage: '150.00',
                barcode: 'COD001234567',
                cod_amount: '5000.00',
                contact_number: '0751234567',
                payment_method: 'cash',
                delivery_instructions: 'Call before delivery',
                notes: 'COD payment required'
            },
            {
                receiver_name: 'David Lee',
                receiver_address: '777 Kurunegala Road, Kuliyapitiya',
                item_value: '3500.00',
                weight: '750',
                postage: '130.00',
                barcode: 'COD001234568',
                cod_amount: '3500.00',
                contact_number: '0783456789',
                payment_method: 'card',
                delivery_instructions: 'Deliver to office',
                notes: 'Business delivery'
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
        document.getElementById('template-info').style.display = 'none';
        document.getElementById('csv-format-section').style.display = 'none';
        document.getElementById('sample-data-card').style.display = 'none';
        return;
    }

    // Enable download button and show info
    document.getElementById('download-template-btn').disabled = false;
    document.getElementById('selected-service-label').textContent = config.label;
    document.getElementById('template-info').style.display = 'block';

    // Show CSV format preview
    updateCSVFormatPreview(serviceType, config);


}
// Function to update CSV format preview
function updateCSVFormatPreview(serviceType, config) {
    const columns = config.columns;
    const sampleData = config.sampleData[0]; // Use first sample row

    document.getElementById('csv-service-type').textContent = config.label;

    let tableHtml = '<table class="table table-sm table-bordered table-striped"><thead class="table-primary"><tr>';

    // Table headers
    columns.forEach(col => {
        const required = col.required ? ' <span class="text-danger">*</span>' : '';
        tableHtml += `<th class="text-nowrap small">${col.name}${required}</th>`;
    });
    tableHtml += '</tr></thead><tbody><tr>';

    // Sample data row
    columns.forEach(col => {
        const value = sampleData[col.name] || '';
        tableHtml += `<td class="small text-muted">${value}</td>`;
    });
    tableHtml += '</tr></tbody></table>';

    document.getElementById('csv-format-table').innerHTML = tableHtml;
    document.getElementById('csv-format-section').style.display = 'block';
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
    a.setAttribute('download', `${serviceType}_bulk_upload_template.csv`);
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
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
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
