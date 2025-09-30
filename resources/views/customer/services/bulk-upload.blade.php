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
                <h2 class="fw-bold text-dark mb-0">Bulk Upload</h2>
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
                    <p class="text-muted">Download the template to see the required format for bulk uploads</p>
                    <button class="btn btn-success" onclick="downloadTemplate()">
                        <i class="bi bi-download me-2"></i>Download Template
                    </button>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('customer.services.store-bulk-upload') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <!-- Service Type Selection -->
                        <div class="mb-3">
                            <label for="service_type" class="form-label fw-semibold">Service Type for All Items</label>
                            <select id="service_type" class="form-select @error('service_type') is-invalid @enderror"
                                    name="service_type" required>
                                <option value="">Select Service Type</option>
                                @foreach($serviceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('service_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">All items in the uploaded file will use this service type</small>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="bulk_file" class="form-label fw-semibold">Upload CSV File</label>
                            <input id="bulk_file" type="file" class="form-control @error('bulk_file') is-invalid @enderror"
                                   name="bulk_file" accept=".csv,.xlsx,.xls" required>
                            @error('bulk_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Supported formats: CSV, Excel (.xlsx, .xls). Maximum file size: 2MB</small>
                            </div>
                        </div>

                        <!-- File Format Requirements -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Required CSV Columns:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><strong>receiver_name</strong> - Receiver's full name</li>
                                                <li><strong>receiver_address</strong> - Complete address</li>
                                                <li><strong>amount</strong> - Item value in LKR</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><strong>weight</strong> - Weight in grams (for SLP Courier)</li>
                                                <li><strong>item_value</strong> - Declared value (optional)</li>
                                                <li><strong>notes</strong> - Additional notes (optional)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('customer.services.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload me-2"></i>Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sample Data Preview -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Sample CSV Format</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>receiver_name</th>
                                    <th>receiver_address</th>
                                    <th>amount</th>
                                    <th>weight</th>
                                    <th>item_value</th>
                                    <th>notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>John Doe</td>
                                    <td>123 Main St, Colombo 07</td>
                                    <td>1500.00</td>
                                    <td>250</td>
                                    <td>1500.00</td>
                                    <td>Handle with care</td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>456 Galle Road, Dehiwala</td>
                                    <td>2500.00</td>
                                    <td>500</td>
                                    <td>2500.00</td>
                                    <td>Fragile item</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function downloadTemplate() {
    // Create CSV content
    const csvContent = `receiver_name,receiver_address,amount,weight,item_value,notes
John Doe,"123 Main St, Colombo 07",1500.00,250,1500.00,Handle with care
Jane Smith,"456 Galle Road, Dehiwala",2500.00,500,2500.00,Fragile item`;

    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'bulk_upload_template.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
