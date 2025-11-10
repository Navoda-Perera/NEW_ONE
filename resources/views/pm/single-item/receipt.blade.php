@extends('layouts.modern-pm')

@section('title', 'Receipt Generated')

@section('content')
<!-- Page Header -->
<div class="row mb-4 no-print">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-receipt-cutoff text-success me-2"></i>
                    Receipt Generated
                </h2>
                <p class="text-muted mb-0">SLP item created successfully with official receipt</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-pm-primary">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
                <a href="{{ route('pm.single-item.index') }}" class="btn btn-pm-accent">
                    <i class="bi bi-plus-circle"></i> Add Another Item
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Success Alert -->
<div class="row mb-4 no-print">
    <div class="col-12">
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
            <div>
                <h6 class="alert-heading mb-1">SLP item created successfully!</h6>
                <p class="mb-0">Receipt #{{ str_pad($receipt->id, 3, '0', STR_PAD_LEFT) }} has been generated with passcode <strong>{{ $receipt->passcode }}</strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Card -->
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card shadow-lg border-0">
            <!-- Receipt Header -->
            <div class="card-header bg-gradient text-white text-center py-4" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="receipt-icon me-3">
                        <i class="bi bi-receipt fs-2"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">POSTAL SERVICE RECEIPT</h3>
                        <small class="opacity-75">Receipt #{{ str_pad($receipt->id, 3, '0', STR_PAD_LEFT) }}</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Office & Service Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-section">
                            <h6 class="section-title text-pm-primary mb-3">
                                <i class="bi bi-building me-2"></i>Office Information
                            </h6>
                            <div class="info-item mb-2">
                                <strong>Location:</strong> {{ $receipt->location->name ?? 'N/A' }}
                            </div>
                            <div class="info-item mb-2">
                                <strong>PM:</strong> {{ $receipt->itemBulk->creator->name ?? 'N/A' }}
                            </div>
                            <div class="info-item">
                                <strong>Date:</strong> {{ $receipt->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="info-section">
                            <h6 class="section-title text-pm-accent mb-3">
                                <i class="bi bi-tag me-2"></i>Service Type
                            </h6>
                            <div class="mb-3">
                                <span class="service-badge badge fs-6 px-3 py-2
                                    {{ $receipt->itemBulk->service_type === 'slp_courier' ? 'bg-pm-primary' :
                                       ($receipt->itemBulk->service_type === 'cod' ? 'bg-warning text-dark' : 'bg-success') }}">
                                    <i class="bi bi-truck me-1"></i>
                                    {{ strtoupper(str_replace('_', ' ', $receipt->itemBulk->service_type)) }}
                                </span>
                            </div>
                            <div class="passcode-display p-3 bg-light rounded">
                                <strong>Passcode:</strong>
                                <span class="badge bg-dark fs-6 ms-2">{{ $receipt->passcode }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Item Details Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="section-title text-pm-primary mb-3">
                            <i class="bi bi-box-seam me-2"></i>Item Details
                        </h6>

                        @foreach($receipt->itemBulk->items as $item)
                        <div class="item-card mb-3 p-4 border rounded-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="item-info">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">BARCODE</label>
                                            <div class="barcode-display p-2 bg-white rounded border">
                                                <span class="font-monospace fw-bold fs-5">{{ $item->barcode }}</span>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <label class="form-label text-muted small">WEIGHT</label>
                                                <div class="fw-bold">{{ $item->weight }}g</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="recipient-info">
                                        <h6 class="text-pm-accent mb-2">
                                            <i class="bi bi-person-check me-1"></i>Recipient Details
                                        </h6>
                                        <div class="mb-2">
                                            <strong>Name:</strong> {{ $item->receiver_name }}
                                        </div>
                                        <div class="mb-2">
                                            <strong>Mobile:</strong> {{ $item->smsSents->first()->receiver_mobile ?? 'N/A' }}
                                        </div>
                                        <div class="address-text">
                                            <strong>Address:</strong> {{ $item->receiver_address }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="row mb-4">
                    <div class="col-md-8 mx-auto">
                        <div class="payment-summary p-4 bg-gradient rounded-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <h6 class="section-title text-center text-pm-primary mb-4">
                                <i class="bi bi-credit-card me-2"></i>Payment Summary
                            </h6>

                            <div class="summary-details">
                                @if($receipt->itemBulk->service_type === 'cod')
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted">COD Amount:</span>
                                        <span class="fw-bold">LKR {{ number_format($receipt->amount, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted">Postage Fee:</span>
                                        <span class="fw-bold">LKR {{ number_format($receipt->postage ?? 0, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center py-3 bg-white rounded mt-3 px-3">
                                        <span class="fw-bold fs-5 text-pm-primary">Total Amount:</span>
                                        <span class="fw-bold fs-4 text-success">LKR {{ number_format($receipt->total_amount ?? ($receipt->amount + ($receipt->postage ?? 0)), 2) }}</span>
                                    </div>
                                @else
                                    @php
                                        $displayPostage = $receipt->postage ?? $receipt->total_amount ?? 0;
                                        // For existing receipts where postage might not be set, try to calculate from item weight
                                        if ($displayPostage == 0 && $receipt->itemBulk->items->count() > 0) {
                                            $item = $receipt->itemBulk->items->first();
                                            if ($receipt->itemBulk->service_type === 'slp_courier') {
                                                $displayPostage = \App\Models\SlpPricing::calculatePrice($item->weight);
                                            } elseif ($receipt->itemBulk->service_type === 'register_post') {
                                                $displayPostage = \App\Models\PostPricing::calculatePrice($item->weight, 'register');
                                            }
                                        }
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center py-3 bg-white rounded px-3">
                                        <span class="fw-bold fs-5 text-pm-primary">Total Postage:</span>
                                        <span class="fw-bold fs-4 text-success">LKR {{ number_format($displayPostage, 2) }}</span>
                                    </div>
                                @endif

                                <div class="row mt-3 pt-3 border-top">
                                    <div class="col-6">
                                        <span class="text-muted small">No. of Items:</span>
                                        <div class="fw-bold">{{ $receipt->item_quantity }}</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="text-muted small">Payment Type:</span>
                                        <div>
                                            <span class="badge bg-pm-accent text-white">{{ strtoupper($receipt->payment_type) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="row">
                    <div class="col-12">
                        <div class="terms-section p-4 bg-light rounded-3">
                            <h6 class="section-title text-pm-primary mb-3">
                                <i class="bi bi-shield-check me-2"></i>Terms & Conditions
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="terms-list mb-0 small text-muted">
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            This receipt is valid for tracking and delivery claims
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-shield-fill text-primary me-2"></i>
                                            Please keep this receipt safe until delivery is completed
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-telephone-fill text-info me-2"></i>
                                            For inquiries, contact the issuing post office with receipt number
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="terms-list mb-0 small text-muted">
                                        @if($receipt->itemBulk->service_type === 'cod')
                                        <li class="mb-2">
                                            <i class="bi bi-cash-coin text-warning me-2"></i>
                                            COD amount will be collected from receiver upon delivery
                                        </li>
                                        @endif
                                        @if($receipt->itemBulk->service_type === 'register_post')
                                        <li class="mb-2">
                                            <i class="bi bi-award-fill text-success me-2"></i>
                                            Registered post includes basic insurance coverage
                                        </li>
                                        @endif
                                        <li class="mb-2">
                                            <i class="bi bi-truck text-pm-accent me-2"></i>
                                            Tracking is available throughout delivery process
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-clock-fill text-secondary me-2"></i>
                                            Delivery time varies based on destination and service type
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Footer -->
            <div class="card-footer bg-light text-center py-3">
                <div class="d-flex align-items-center justify-content-center text-muted">
                    <i class="bi bi-clock me-2"></i>
                    <small class="me-3">Generated on {{ $receipt->created_at->format('Y-m-d H:i:s') }}</small>
                    <span class="mx-2">|</span>
                    <i class="bi bi-hash me-1"></i>
                    <small class="me-3">Receipt ID: {{ $receipt->id }}</small>
                    <span class="mx-2">|</span>
                    <i class="bi bi-shield-check me-1"></i>
                    <small>System Generated</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Styling for Modern Receipt */
.receipt-icon {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.section-title {
    font-weight: 700;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--pm-accent);
    display: inline-block;
    padding-bottom: 2px;
}

.info-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border-left: 4px solid var(--pm-primary);
}

.info-item {
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.service-badge {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none !important;
}

.passcode-display {
    border: 2px dashed var(--pm-accent);
}

.item-card {
    transition: all 0.3s ease;
    border: 2px solid #e9ecef !important;
}

.item-card:hover {
    border-color: var(--pm-primary) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.barcode-display {
    background: #fff !important;
    border: 2px solid #28a745;
    font-family: 'Courier New', monospace;
}

.payment-summary {
    border: 2px solid var(--pm-primary);
    position: relative;
}

.payment-summary::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: var(--pm-accent);
    border-radius: 2px;
}

.summary-details .border-bottom {
    border-color: #dee2e6 !important;
}

.terms-section {
    border: 1px solid #dee2e6;
}

.terms-list {
    list-style: none;
    padding-left: 0;
}

.terms-list li {
    display: flex;
    align-items: flex-start;
}

.address-text {
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Print Styles */
@media print {
    /* Hide non-essential elements */
    .no-print,
    .btn,
    .alert,
    button,
    .navbar,
    .sidebar,
    .breadcrumb {
        display: none !important;
    }

    /* Hide the entire page layout elements */
    body * {
        visibility: hidden;
    }

    /* Show only the receipt card */
    .card,
    .card * {
        visibility: visible;
    }

    /* Position receipt at top of page and ensure single page */
    .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        max-height: 100vh !important;
        overflow: hidden !important;
    }

    /* Optimize receipt for single page printing */
    body {
        font-size: 10pt !important;
        line-height: 1.2 !important;
    }

    /* Compact header */
    .card-header {
        background: #28a745 !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        padding: 15px !important;
        font-size: 14pt !important;
    }

    /* Compact content */
    .card-body {
        padding: 15px !important;
    }

    /* Smaller spacing */
    .mb-4 {
        margin-bottom: 1rem !important;
    }

    .mb-3 {
        margin-bottom: 0.75rem !important;
    }

    .mb-2 {
        margin-bottom: 0.5rem !important;
    }

    .py-4 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }

    .p-4 {
        padding: 1rem !important;
    }

    .info-section {
        padding: 1rem !important;
    }

    .payment-summary {
        border: 2px solid #333 !important;
        padding: 1rem !important;
    }

    .terms-section {
        border: 1px solid #ccc !important;
        padding: 1rem !important;
    }

    .section-title {
        color: #333 !important;
        font-size: 11pt;
    }

    .service-badge {
        background: #007bff !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .payment-summary {
        border: 2px solid #333 !important;
    }

    .terms-section {
        border: 1px solid #ccc !important;
    }

    /* Ensure barcode is readable */
    .barcode-display {
        border: 2px solid #000 !important;
        font-weight: bold !important;
    }

    /* Page breaks */
    .card {
        page-break-inside: avoid;
    }

    /* Hide all margins and padding from page */
    @page {
        margin: 0.5in;
    }
}
</style>
@endsection
