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
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
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
                        <div class="mb-3">
                            <label for="service_type_id" class="form-label fw-semibold">Choose Service Type</label>
                            <select id="service_type_id" class="form-select @error('service_type_id') is-invalid @enderror"
                                    name="service_type_id" required>
                                <option value="">Select Your Service</option>
                                @foreach($serviceTypes as $serviceType)
                                    <option value="{{ $serviceType->id }}"
                                            data-type="{{ $serviceType->name }}"
                                            data-has-weight="{{ $serviceType->has_weight_pricing ? 'true' : 'false' }}"
                                            data-base-price="{{ $serviceType->base_price }}">
                                        {{ $serviceType->name }}
                                        @if(!$serviceType->has_weight_pricing && $serviceType->base_price)
                                            - LKR {{ number_format($serviceType->base_price, 2) }}
                                        @endif
                                    </option>
                                @endforeach
                                <option value="remittance" data-type="Remittance" data-has-weight="false">
                                    Remittance Service
                                </option>
                                <option value="insured" data-type="Insured" data-has-weight="false">
                                    Insured Service
                                </option>
                            </select>
                            @error('service_type_id')
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
    const serviceSelect = document.getElementById('service_type_id');
    const dynamicFields = document.getElementById('dynamicFields');

    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const serviceType = selectedOption.dataset.type;
        const hasWeight = selectedOption.dataset.hasWeight === 'true';
        const basePrice = selectedOption.dataset.basePrice;

        dynamicFields.innerHTML = '';

        if (!serviceType) return;

        // Common fields for all services
        const commonFields = `
            <div class="field-group">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-person-lines-fill me-2"></i>Recipient Information
                </h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="receiver_name" class="form-label fw-semibold">
                            <i class="bi bi-person me-1"></i>Receiver Name
                        </label>
                        <input id="receiver_name" type="text" class="form-control" name="receiver_name"
                               placeholder="Enter receiver's full name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label fw-semibold">
                            <i class="bi bi-currency-dollar me-1"></i>Amount (LKR)
                        </label>
                        <input id="amount" type="number" step="0.01" min="0" class="form-control"
                               name="amount" placeholder="0.00" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label fw-semibold">
                        <i class="bi bi-geo-alt me-1"></i>Receiver Address
                    </label>
                    <textarea id="address" class="form-control" name="address" rows="3"
                              placeholder="Enter complete delivery address" required></textarea>
                </div>
            </div>
        `;

        let specificFields = '';

        if (serviceType === 'SLP Courier' || serviceType === 'COD') {
            specificFields += `
                <div class="field-group">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-box-seam me-2"></i>Package Details
                    </h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="weight" class="form-label fw-semibold">
                                <i class="bi bi-speedometer2 me-1"></i>Weight (grams)
                            </label>
                            <input id="weight" type="number" step="0.01" min="1" class="form-control"
                                   name="weight" placeholder="Enter weight in grams" required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Weight determines postage cost
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="destination_post_office_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>Destination Post Office
                            </label>
                            <select id="destination_post_office_id" class="form-select" name="destination_post_office_id" required>
                                <option value="">Select Destination</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            `;

            if (serviceType === 'SLP Courier') {
                specificFields += `
                    <div class="field-group">
                        <h5 class="fw-bold text-success mb-3">
                            <i class="bi bi-calculator me-2"></i>Postage Calculation
                        </h5>
                        <div class="calculation-display">
                            <label class="form-label mb-2 fw-semibold">
                                <i class="bi bi-cash-coin me-1"></i>Calculated Postage
                            </label>
                            <input type="text" class="form-control" id="postage_display" readonly
                                   placeholder="Enter weight to calculate pricing">
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-lightbulb me-1"></i>Pricing calculated automatically based on weight
                            </small>
                        </div>
                    </div>
                `;
            }

            if (serviceType === 'COD') {
                specificFields += `
                    <div class="field-group">
                        <h5 class="fw-bold text-success mb-3">
                            <i class="bi bi-percent me-2"></i>Commission Calculation
                        </h5>
                        <div class="calculation-display">
                            <label class="form-label mb-2 fw-semibold">
                                <i class="bi bi-cash-stack me-1"></i>Commission (2%)
                            </label>
                            <input type="number" class="form-control" id="commission_display" readonly>
                            <input type="hidden" name="commission" id="commission">
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-calculator me-1"></i>Automatically calculated at 2% of amount
                            </small>
                        </div>
                    </div>
                `;
            }
        } else if (serviceType === 'Remittance' || serviceType === 'Insured') {
            specificFields += `
                <div class="field-group">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="bi bi-percent me-2"></i>Commission Calculation
                    </h5>
                    <div class="calculation-display">
                        <label class="form-label mb-2 fw-semibold">
                            <i class="bi bi-cash-stack me-1"></i>Commission (2%)
                        </label>
                        <input type="number" class="form-control" id="commission_display" readonly>
                        <input type="hidden" name="commission" id="commission">
                        <input type="hidden" name="service_type" value="${serviceType.toLowerCase()}">
                        <small class="text-muted mt-2 d-block">
                            <i class="bi bi-shield-check me-1"></i>Secure ${serviceType} service with 2% commission
                        </small>
                    </div>
                </div>
            `;
        }

        dynamicFields.innerHTML = commonFields + specificFields;

        // Add event listeners for auto-calculations
        const amountField = document.getElementById('amount');
        if (amountField && (serviceType === 'COD' || serviceType === 'Remittance' || serviceType === 'Insured')) {
            amountField.addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                const commission = amount * 0.02;
                const commissionField = document.getElementById('commission');
                const commissionDisplay = document.getElementById('commission_display');

                if (commissionField && commissionDisplay) {
                    commissionField.value = commission.toFixed(2);
                    commissionDisplay.value = commission.toFixed(2);
                }
            });
        }

        // Add weight-based pricing for SLP Courier
        const weightField = document.getElementById('weight');
        if (weightField && serviceType === 'SLP Courier') {
            weightField.addEventListener('input', function() {
                const weight = parseFloat(this.value) || 0;

                if (weight > 0) {
                    fetch('{{ route("customer.services.get-slp-price") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ weight: weight })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const postageDisplay = document.getElementById('postage_display');
                        if (postageDisplay) {
                            postageDisplay.value = data.formatted_price || 'No pricing available';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching price:', error);
                        const postageDisplay = document.getElementById('postage_display');
                        if (postageDisplay) {
                            postageDisplay.value = 'Error calculating price';
                        }
                    });
                } else {
                    const postageDisplay = document.getElementById('postage_display');
                    if (postageDisplay) {
                        postageDisplay.value = '';
                    }
                }
            });
        }
    });
});
</script>
@endpush
@endsection
