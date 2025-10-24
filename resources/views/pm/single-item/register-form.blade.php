@extends('layouts.app')

@section('title', 'Add Register Post Item')

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
        <a class="nav-link active" href="{{ route('pm.single-item.index') }}">
            <i class="bi bi-box-seam"></i> Add Single Item
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
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-envelope-check text-success"></i> Add Register Post Item
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pm.single-item.index') }}">Single Item</a></li>
                        <li class="breadcrumb-item active">Register Post</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope-check"></i> Register Post Item Details
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pm.single-item.store-register') }}" method="POST">
                        @csrf

                        <!-- Sender Information -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-person-fill"></i> Sender Information
                        </h6>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="sender_name" class="form-label">Sender Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sender_name" name="sender_name"
                                       value="{{ old('sender_name') }}" required>
                            </div>
                        </div>

                        <!-- Receiver Information -->
                        <h6 class="text-success border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-person-check"></i> Receiver Information
                        </h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="receiver_name" class="form-label">Receiver Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="receiver_name" name="receiver_name"
                                       value="{{ old('receiver_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="receiver_mobile" class="form-label">Receiver Mobile <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="receiver_mobile" name="receiver_mobile"
                                       value="{{ old('receiver_mobile') }}" placeholder="07XXXXXXXX" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="receiver_address" class="form-label">Receiver Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="receiver_address" name="receiver_address"
                                          rows="3" required>{{ old('receiver_address') }}</textarea>
                            </div>
                        </div>

                        <!-- Item Information -->
                        <h6 class="text-warning border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-box"></i> Item Information
                        </h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="weight" class="form-label">Weight (grams) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="weight" name="weight"
                                       value="{{ old('weight') }}" step="0.01" min="0.01" required>
                                <small class="text-muted">Enter weight in grams (e.g., 250 for 250g)</small>
                            </div>
                            <div class="col-md-6">
                                <label for="postage_display" class="form-label">Postage Amount (LKR)</label>
                                <input type="text" class="form-control" id="postage_display" readonly
                                       placeholder="Enter weight to calculate">
                                <small class="text-muted">Automatically calculated based on weight</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="barcode" name="barcode"
                                           value="{{ old('barcode') }}" required>
                                    <button type="button" class="btn btn-outline-secondary" id="generateBarcode">
                                        <i class="bi bi-arrow-clockwise"></i> Generate
                                    </button>
                                </div>
                                <small class="text-muted">Unique barcode for tracking this registered item</small>
                            </div>
                        </div>

                        <!-- Register Post Features -->
                        <div class="alert alert-success">
                            <h6 class="alert-heading"><i class="bi bi-shield-check"></i> Register Post Features</h6>
                            <ul class="mb-0">
                                <li><i class="bi bi-check text-success"></i> <strong>Tracking:</strong> Full tracking from sender to receiver</li>
                                <li><i class="bi bi-check text-success"></i> <strong>Delivery Confirmation:</strong> Signature required upon delivery</li>
                                <li><i class="bi bi-check text-success"></i> <strong>Insurance:</strong> Basic coverage included</li>
                                <li><i class="bi bi-check text-success"></i> <strong>Priority Handling:</strong> Faster processing and delivery</li>
                            </ul>
                        </div>

                        <!-- Postage Calculation Alert -->
                        <div class="alert alert-info d-none" id="postageAlert">
                            <i class="bi bi-info-circle"></i>
                            <span id="postageMessage">Calculating postage...</span>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('pm.single-item.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                        <i class="bi bi-check-circle"></i> Create Register Post Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
    // Generate random barcode
    $('#generateBarcode').click(function() {
        const barcode = 'REG' + Date.now() + Math.floor(Math.random() * 1000);
        $('#barcode').val(barcode);
    });

    // Calculate postage when weight changes
    $('#weight').on('input', function() {
        const weight = parseFloat($(this).val());

        if (weight && weight > 0) {
            calculatePostage(weight);
        } else {
            $('#postage_display').val('');
            $('#postageAlert').addClass('d-none');
        }
    });

    function calculatePostage(weight) {
        $.ajax({
            url: '{{ route("pm.single-item.calculate-postage") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                weight: weight,
                service_type: 'register_post'
            },
            beforeSend: function() {
                $('#postage_display').val('Calculating...');
                $('#postageAlert').removeClass('d-none alert-danger alert-success')
                    .addClass('alert-info');
                $('#postageMessage').text('Calculating postage...');
            },
            success: function(response) {
                if (response.success) {
                    $('#postage_display').val('LKR ' + response.postage);
                    $('#postageAlert').removeClass('alert-info alert-danger')
                        .addClass('alert-success');
                    $('#postageMessage').text(`Postage calculated: LKR ${response.postage} for ${weight}g`);
                } else {
                    $('#postage_display').val('Error');
                    showError(response.message || 'Failed to calculate postage');
                }
            },
            error: function(xhr) {
                $('#postage_display').val('Error');
                showError('Failed to calculate postage. Please try again.');
            }
        });
    }

    function showError(message) {
        $('#postageAlert').removeClass('alert-info alert-success')
            .addClass('alert-danger');
        $('#postageMessage').text(message);
    }

    // PM must enter or generate barcode manually
    // No automatic barcode generation on page load
});
</script>
@endsection
@endsection
