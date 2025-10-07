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


                        <!-- Service Type Instructions -->
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <strong>New!</strong> You can now specify the <code>service_type</code> for each item in your CSV file. Supported values: <code>register_post</code>, <code>slp_courier</code>, <code>cod</code>, <code>remittance</code>.
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
                                                <li><strong>item_value</strong> - Declared item value in LKR</li>
                                                <li><strong>service_type</strong> - <span class="text-primary">Service type for this item</span></li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><strong>weight</strong> - Weight in grams</li>
                                                <li><strong>amount</strong> - Collection amount (COD only)</li>
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
                                    <th>item_value</th>
                                    <th>service_type</th>
                                    <th>weight</th>
                                    <th>amount</th>
                                    <th>notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>John Doe</td>
                                    <td>123 Main St, Colombo 07</td>
                                    <td>1500.00</td>
                                    <td>register_post</td>
                                    <td>250</td>
                                    <td></td>
                                    <td>Handle with care</td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>456 Galle Road, Dehiwala</td>
                                    <td>2500.00</td>
                                    <td>cod</td>
                                    <td>500</td>
                                    <td>2500.00</td>
                                    <td>COD - Fragile item</td>
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
    const csvContent = `receiver_name,receiver_address,item_value,service_type,weight,amount,notes\nJohn Doe,"123 Main St, Colombo 07",1500.00,register_post,250,,Handle with care\nJane Smith,"456 Galle Road, Dehiwala",2500.00,cod,500,2500.00,COD - Fragile item`;

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
