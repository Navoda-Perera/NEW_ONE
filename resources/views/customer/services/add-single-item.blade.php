@extends('layouts.app')

@section('title', 'Add Single Item')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        border-radius: 0 0 20px 20px;
    }

    .form-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        padding: 30px;
    }

    .form-select, .form-control {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        background: #fff;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 15px 30px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        color: white;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .field-group {
        background: rgba(246, 248, 251, 0.8);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #667eea;
    }

    .calculation-display {
        background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
        border: 2px solid #28a745;
        border-radius: 10px;
        padding: 15px;
        font-weight: 600;
        color: #155724;
    }

    .register-post-display {
        background: linear-gradient(135deg, #ffebee 0%, #fff5f5 100%);
        border: 2px solid #f44336;
        border-radius: 10px;
        padding: 15px;
        font-weight: 600;
        color: #b71c1c;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-light me-3">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
            </div>
            <div class="col">
                <h2 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-3"></i>Add Single Item</h2>
                <p class="mb-0 opacity-75">Submit your postal service items quickly and easily</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="form-container">
                <form method="POST" action="{{ route('customer.services.store-single-item') }}" id="itemForm">
                    @csrf

                    <!-- Service Type Selection -->
                    <div class="field-group">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-gear me-2"></i>Service Selection
                        </h5>

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

                        <div class="mb-3">
                            <label for="service_type" class="form-label fw-semibold">Choose Service Type</label>
                            <select id="service_type" class="form-select @error('service_type') is-invalid @enderror"
                                    name="service_type" required>
                                <option value="">Select Your Service</option>
                                @foreach($serviceTypes as $value => $details)
                                    <option value="{{ $value }}"
                                            data-type="{{ $details['label'] }}"
                                            data-has-weight="{{ $details['has_weight'] ? 'true' : 'false' }}"
                                            data-base-price="{{ $details['base_price'] }}">
                                        {{ $details['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Dynamic Fields Container -->
                    <div id="dynamicFields"></div>

                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-submit btn-lg px-5">
                            <i class="bi bi-check-circle me-2"></i>Submit Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, starting JavaScript initialization...');

    const serviceSelect = document.getElementById('service_type');
    const dynamicFields = document.getElementById('dynamicFields');
    const locations = @json($locations);

    if (!serviceSelect) {
        console.error('Service select element not found!');
        return;
    }

    if (!dynamicFields) {
        console.error('Dynamic fields container not found!');
        return;
    }

    console.log('Elements found successfully. Adding event listener...');

    serviceSelect.addEventListener('change', function() {
        console.log('Service type changed!');

        const selectedOption = this.options[this.selectedIndex];
        const serviceType = this.value; // Use the option value instead of data-type
        const serviceLabel = selectedOption.dataset.type; // This is the label for display

        console.log('Selected service type:', serviceType, 'Label:', serviceLabel);

        // Clear existing fields
        dynamicFields.innerHTML = '';

        if (!serviceType) {
            console.log('No service type selected, returning');
            return;
        }

        // Start building form fields
        let formHTML = `
            <div class="field-group">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-person-lines-fill me-2"></i>Recipient Information
                </h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="receiver_name" class="form-label fw-semibold">
                            <i class="bi bi-person me-1"></i>Receiver Name
                        </label>
                        <input id="receiver_name" type="text" class="form-control" name="receiver_name" placeholder="Enter receiver's full name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="receiver_mobile" class="form-label fw-semibold">
                            <i class="bi bi-telephone me-1"></i>Receiver Mobile
                        </label>
                        <input id="receiver_mobile" type="text" class="form-control" name="receiver_mobile" placeholder="07XXXXXXXX" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label fw-semibold">
                        <i class="bi bi-geo-alt me-1"></i>Receiver Address
                    </label>
                    <textarea id="address" class="form-control" name="address" rows="3" placeholder="Enter complete delivery address" required></textarea>
                </div>
            </div>
        `;

        // Add service-specific fields
        if (serviceType === 'cod') {
            console.log('Adding COD specific fields');
            formHTML += `
                <div class="field-group">
                    <h5 class="fw-bold text-warning mb-3">
                        <i class="bi bi-currency-dollar me-2"></i>Collection Details
                    </h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-semibold">
                                <i class="bi bi-cash-stack me-1"></i>Collection Amount (LKR)
                            </label>
                            <input id="amount" type="number" step="0.01" min="0" class="form-control cod-amount-input" name="amount" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="item_value" class="form-label fw-semibold">
                                <i class="bi bi-gem me-1"></i>Item Value (LKR)
                            </label>
                            <input id="item_value" type="number" step="0.01" min="0" class="form-control" name="item_value" placeholder="0.00" required>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-box-seam me-2"></i>Package Details
                    </h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="weight_cod" class="form-label fw-semibold">
                                <i class="bi bi-speedometer2 me-1"></i>Weight (grams)
                            </label>
                            <input id="weight_cod" type="number" step="0.01" min="1" class="form-control weight-input" name="weight" placeholder="Enter weight in grams" required>
                        </div>
                        <div class="col-md-6">
                            <div class="postage-calculator">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calculator me-1"></i>Total Charges (Base + COD)
                                </label>
                                <div id="postage-display-cod" class="calculation-display">
                                    <i class="bi bi-hourglass-split me-2"></i>Enter weight and amount to calculate
                                </div>
                                <button type="button" onclick="calculatePostage()" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="bi bi-calculator"></i> Calculate Now
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="destination_post_office_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Destination Post Office
                            </label>
                            <select id="destination_post_office_id" class="form-select" name="destination_post_office_id" required>
                                <option value="">Select Destination Post Office</option>`;

            locations.forEach(location => {
                formHTML += `<option value="${location.id}">${location.name}</option>`;
            });

            formHTML += `
                            </select>
                        </div>
                    </div>
                </div>
            `;
        } else if (serviceType === 'slp_courier') {
            console.log('Adding SLP Courier specific fields');
            formHTML += `
                <div class="field-group">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-box-seam me-2"></i>Package Details
                    </h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="weight" class="form-label fw-semibold">
                                <i class="bi bi-speedometer2 me-1"></i>Weight (grams)
                            </label>
                            <input id="weight" type="number" step="0.01" min="1" class="form-control weight-input" name="weight" placeholder="Enter weight in grams" required>
                        </div>
                        <div class="col-md-6">
                            <div class="postage-calculator">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calculator me-1"></i>Estimated Postage
                                </label>
                                <div id="postage-display-slp" class="calculation-display">
                                    <i class="bi bi-hourglass-split me-2"></i>Enter weight to calculate
                                </div>
                                <button type="button" onclick="calculatePostage()" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="bi bi-calculator"></i> Calculate Now
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="destination_post_office_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Destination Post Office
                            </label>
                            <select id="destination_post_office_id" class="form-select" name="destination_post_office_id" required>
                                <option value="">Select Destination</option>`;

            locations.forEach(location => {
                formHTML += `<option value="${location.id}">${location.name}</option>`;
            });

            formHTML += `
                            </select>
                        </div>
                    </div>
                </div>
            `;
        } else if (serviceType === 'register_post') {
            console.log('Adding Register Post specific fields');
            formHTML += `
                <div class="field-group">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-box-seam me-2"></i>Package Details
                    </h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="weight" class="form-label fw-semibold">
                                <i class="bi bi-speedometer2 me-1"></i>Weight (grams)
                            </label>
                            <input id="weight" type="number" step="0.01" min="1" class="form-control weight-input" name="weight" placeholder="Enter weight in grams" required>
                        </div>
                        <div class="col-md-6">
                            <div class="postage-calculator">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calculator me-1"></i>Postage Cost
                                </label>
                                <div id="postage-display-register" class="register-post-display">
                                    <i class="bi bi-hourglass-split me-2"></i>Enter weight to calculate
                                </div>
                                <button type="button" onclick="calculatePostage()" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="bi bi-calculator"></i> Calculate Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Add barcode field for all services
        formHTML += `
            <div class="field-group">
                <h5 class="fw-bold text-info mb-3">
                    <i class="bi bi-upc-scan me-2"></i>Barcode Information
                </h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="barcode" class="form-label fw-semibold">
                            <i class="bi bi-upc-scan me-1"></i>Barcode (Optional)
                        </label>
                        <input id="barcode" type="text" class="form-control" name="barcode" placeholder="Enter your item barcode if available">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>If you don't have a barcode, PM will assign one after accepting your item
                        </div>
                    </div>
                </div>
            </div>
        `;

        console.log('Setting form HTML...');
        dynamicFields.innerHTML = formHTML;
        console.log('Form HTML set successfully!');

        // Initialize postage calculator after form is loaded
        initializePostageCalculator();
    });
});

// Postage Auto Calculator Functions
function initializePostageCalculator() {
    console.log('Initializing postage calculator...');

    // Remove existing event listeners to prevent duplicates
    const allInputs = document.querySelectorAll('input');
    allInputs.forEach(input => {
        input.removeEventListener('input', calculatePostage);
        input.removeEventListener('blur', calculatePostage);
        input.removeEventListener('change', calculatePostage);
    });

    // Add event listeners for all weight inputs (different forms may have different IDs)
    const weightSelectors = [
        '.weight-input',
        'input[name="weight"]',
        'input[id*="weight"]',
        'input[id="weight"]',
        'input[id="weight_cod"]'
    ];

    weightSelectors.forEach(selector => {
        const inputs = document.querySelectorAll(selector);
        inputs.forEach(input => {
            input.addEventListener('input', calculatePostage);
            input.addEventListener('blur', calculatePostage);
            console.log('Added weight listener to:', input.id || input.name || 'unnamed input');
        });
    });

    // Add event listeners for service type changes (both radio and select)
    const serviceTypeInputs = document.querySelectorAll('input[name="service_type"]');
    serviceTypeInputs.forEach(input => {
        input.addEventListener('change', calculatePostage);
    });

    // Add event listener for service type select dropdown
    const serviceTypeSelect = document.getElementById('service_type');
    if (serviceTypeSelect) {
        serviceTypeSelect.addEventListener('change', calculatePostage);
        console.log('Added event listener to service type select');
    }

    // Add event listeners for COD amount
    const codAmountInputs = document.querySelectorAll('.cod-amount-input, input[name="cod_amount"]');
    codAmountInputs.forEach(input => {
        input.addEventListener('input', calculatePostage);
        input.addEventListener('blur', calculatePostage);
    });

    console.log('Added event listeners to:', {
        weightInputs: document.querySelectorAll(weightSelectors.join(', ')).length,
        serviceTypeInputs: serviceTypeInputs.length,
        codInputs: codAmountInputs.length
    });

    // Trigger initial calculation if there's already data
    setTimeout(() => {
        console.log('Triggering initial calculation...');
        calculatePostage();
    }, 500);
}

function normalizeServiceType(serviceType) {
    // Service types are already in the correct backend format
    // but keep mapping for backwards compatibility
    const mapping = {
        'COD': 'cod',
        'SLP Courier': 'slp_courier',
        'Register Post': 'register_post'
    };

    // Return mapped value if exists, otherwise return as-is (already normalized)
    return mapping[serviceType] || serviceType;
}function calculatePostage() {
    console.log('Calculating postage...');

    // Try to get service type from radio buttons first
    let selectedServiceType = document.querySelector('input[name="service_type"]:checked');
    let serviceType = null;

    if (selectedServiceType) {
        serviceType = selectedServiceType.value;
        console.log('Found service type from radio button:', serviceType);
    } else {
        // Fallback: try to get from select dropdown
        const serviceSelect = document.getElementById('service_type');
        if (serviceSelect && serviceSelect.value) {
            serviceType = serviceSelect.value;
            console.log('Found service type from select dropdown:', serviceType);
        }
    }

    if (!serviceType || serviceType === '') {
        console.log('No service type selected');
        return;
    }

    console.log('Selected service type:', serviceType);

    // Get the correct postage display element based on service type
    let postageDisplayId = 'postage-display-register'; // default
    if (serviceType === 'cod' || serviceType === 'COD') {
        postageDisplayId = 'postage-display-cod';
    } else if (serviceType === 'slp_courier' || serviceType === 'SLP Courier') {
        postageDisplayId = 'postage-display-slp';
    } else if (serviceType === 'register_post' || serviceType === 'Register Post') {
        postageDisplayId = 'postage-display-register';
    }

    console.log('Service type:', serviceType, 'Using display ID:', postageDisplayId);    // Get weight from the current visible form section
    let weight = 0;

    // Try multiple approaches to find the weight input
    let weightInput = null;

    // 1. Try to find visible weight input in active form
    const activeFormContainer = document.querySelector('.dynamic-form-content:not([style*="display: none"])');
    if (activeFormContainer) {
        weightInput = activeFormContainer.querySelector('input[name="weight"], .weight-input, input[id*="weight"]');
    }

    // 2. Fallback: find any visible weight input
    if (!weightInput) {
        const allWeightInputs = document.querySelectorAll('input[name="weight"], .weight-input, input[id*="weight"]');
        for (let input of allWeightInputs) {
            if (input.offsetParent !== null) { // Check if visible
                weightInput = input;
                break;
            }
        }
    }

    // 3. Get weight value
    if (weightInput && weightInput.value.trim() !== '') {
        weight = parseFloat(weightInput.value) || 0;
        console.log('Found weight input:', weightInput.id || weightInput.name, 'with value:', weight);
    } else {
        console.log('No weight input found or weight is empty');
    }    const codAmount = parseFloat(document.querySelector('.cod-amount-input')?.value) || 0;

    console.log('Calculation details:', {
        serviceType: serviceType,
        weight: weight,
        codAmount: codAmount,
        postageDisplayId: postageDisplayId
    });

    const postageDisplay = document.getElementById(postageDisplayId);
    if (!postageDisplay) {
        console.error('Postage display element not found:', postageDisplayId);
        return;
    }

    // Only calculate if we have weight for non-remittance services
    if (weight > 0) {

        postageDisplay.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Calculating...';

        // Make AJAX request to calculate postage
        fetch('{{ route("customer.services.calculate-postage") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                service_type: normalizeServiceType(serviceType), // Normalize service type for backend
                weight: weight,
                cod_amount: codAmount
            })
        })
        .then(response => {
            console.log('Raw response:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Postage calculation response:', data);
            updatePostageDisplay(data, postageDisplayId);
        })
        .catch(error => {
            console.error('Error calculating postage:', error);
            postageDisplay.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Error calculating postage';
            postageDisplay.className = 'calculation-display text-danger';
        });
    } else {
        // Reset display if requirements not met
        postageDisplay.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Enter weight to calculate';
        postageDisplay.className = 'calculation-display';
    }
}

function updatePostageDisplay(data, postageDisplayId) {
    const postageDisplay = document.getElementById(postageDisplayId);
    if (!postageDisplay) return;

    if (data.success) {
        let displayText = '';
        let iconClass = 'bi-check-circle';

        switch(data.service_type) {
            case 'cod':
                displayText = `Base: LKR ${data.price} (includes COD charges)`;
                break;
            case 'register_post':
                displayText = `Postage: LKR ${data.price}`;
                break;
            case 'slp_courier':
                displayText = `Courier Fee: LKR ${data.price}`;
                break;
            default:
                displayText = `Total: LKR ${data.price}`;
        }

        postageDisplay.innerHTML = `<i class="${iconClass} me-2"></i>${displayText}`;

        // Set appropriate styling based on service type
        if (data.service_type === 'register_post') {
            postageDisplay.className = 'register-post-display';
        } else {
            postageDisplay.className = 'calculation-display';
        }
    } else {
        postageDisplay.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${data.message}`;
        postageDisplay.className = 'calculation-display text-warning';
    }
}
</script>
@endpush
@endsection
