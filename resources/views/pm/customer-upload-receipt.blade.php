@extends('layouts.modern-pm')

@section('title', 'Customer Upload Receipt')

@section('content')
<!-- Page Header -->
<div class="row mb-4 no-print">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-receipt-cutoff text-success me-2"></i>
                    Customer Upload Receipt
                </h2>
                <p class="text-muted mb-0">Receipt for customer upload #{{ $upload->id }} - {{ $upload->user->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-pm-primary">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
                <a href="{{ route('pm.view-customer-upload', $upload->id) }}" class="btn btn-pm-accent">
                    <i class="bi bi-arrow-left"></i> Back to Upload
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Success Alert -->
<div class="alert alert-success shadow-sm no-print" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-3 fs-4"></i>
        <div>
            <strong>Receipt Generated Successfully!</strong><br>
            <span class="text-muted">Customer upload items have been processed and receipt is ready for printing.</span>
        </div>
    </div>
</div>

<!-- Receipt Card -->
<div class="card shadow-lg border-0" style="max-width: 800px; margin: 0 auto;">
    <!-- Receipt Header -->
    <div class="card-header text-white py-4" style="background: linear-gradient(135deg, #28a745, #20c997);">
        <div class="text-center">
            <h3 class="mb-2">
                <i class="bi bi-building me-2"></i>
                Sri Lanka Post
            </h3>
            <p class="mb-1">Official Service Receipt</p>
            <small>
                <strong>{{ $receipt->location ? $receipt->location->name : 'Main Office' }}</strong>
            </small>
        </div>
    </div>

    <!-- Receipt Body -->
    <div class="card-body p-4">
        <!-- Receipt Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-section bg-light p-4 rounded">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-info-circle me-2"></i>Receipt Information
                    </h6>
                    <p class="mb-2"><strong>Receipt ID:</strong> #{{ $receipt->id }}</p>
                    <p class="mb-2"><strong>Passcode:</strong> <code class="bg-warning text-dark px-2 py-1 rounded">{{ $receipt->passcode }}</code></p>
                    <p class="mb-2"><strong>Date:</strong> {{ $receipt->created_at->format('d/m/Y') }}</p>
                    <p class="mb-0"><strong>Time:</strong> {{ $receipt->created_at->format('h:i A') }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-section bg-light p-4 rounded">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-person-circle me-2"></i>Customer Information
                    </h6>
                    <p class="mb-2"><strong>Name:</strong> {{ $upload->user->name }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $upload->user->email }}</p>
                    @if($upload->user->nic)
                        <p class="mb-2"><strong>NIC:</strong> {{ $upload->user->nic }}</p>
                    @endif
                    @if($upload->user->mobile)
                        <p class="mb-0"><strong>Mobile:</strong> {{ $upload->user->mobile }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Service Details -->
        <div class="mb-4">
            <h6 class="fw-bold text-primary mb-3">
                <i class="bi bi-box-seam me-2"></i>Service Details
            </h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center bg-light p-3 rounded">
                        <h5 class="text-success mb-1">{{ $receipt->item_quantity }}</h5>
                        <small class="text-muted">Total Items</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center bg-light p-3 rounded">
                        <h5 class="text-info mb-1">
                            @if($receipt->itemBulk && $receipt->itemBulk->items->first())
                                @php
                                    $serviceType = $receipt->itemBulk->items->first()->service_type;
                                    $serviceLabels = [
                                        'register_post' => 'Register Post',
                                        'slp_courier' => 'SLP Courier',
                                        'cod' => 'COD'
                                    ];
                                @endphp
                                {{ $serviceLabels[$serviceType] ?? ucfirst($serviceType) }}
                            @else
                                Service
                            @endif
                        </h5>
                        <small class="text-muted">Service Type</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center bg-light p-3 rounded">
                        <h5 class="text-primary mb-1">{{ $receipt->payment_type === 'cash' ? 'Cash' : ucfirst($receipt->payment_type) }}</h5>
                        <small class="text-muted">Payment Method</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items List -->
        @if($receipt->itemBulk && $receipt->itemBulk->items->count() > 0)
            <div class="mb-4">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="bi bi-list-ul me-2"></i>Items Details
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Barcode</th>
                                <th>Receiver Name</th>
                                <th>Weight (g)</th>
                                <th>Amount (LKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipt->itemBulk->items as $item)
                                <tr>
                                    <td><code>{{ $item->barcode }}</code></td>
                                    <td>{{ $item->receiver_name }}</td>
                                    <td class="text-end">{{ number_format($item->weight, 0) }}</td>
                                    <td class="text-end">{{ number_format($item->amount ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Payment Summary -->
        <div class="mb-4">
            <div class="payment-summary border-2 border-dark bg-light p-4 rounded">
                <h5 class="fw-bold text-center mb-3 text-dark">
                    <i class="bi bi-calculator me-2"></i>Payment Summary
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">COD Amount:</span>
                            <span class="fw-bold">LKR {{ number_format($receipt->amount ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Postage:</span>
                            <span class="fw-bold">LKR {{ number_format($receipt->postage ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 fw-bold text-success mb-0">Total Amount:</span>
                            <span class="h4 fw-bold text-success mb-0">LKR {{ number_format($receipt->total_amount ?? ($receipt->amount + $receipt->postage), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="terms-section border-1 border-secondary bg-light p-3 rounded">
            <h6 class="fw-bold mb-2">
                <i class="bi bi-shield-check me-2"></i>Terms & Conditions
            </h6>
            <ul class="small mb-0">
                <li>This receipt must be presented for item collection</li>
                <li>Items must be collected within 30 days of receipt date</li>
                <li>Lost receipts require identity verification for collection</li>
                <li>Sri Lanka Post is not liable for items not collected within specified time</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4 border-top pt-3">
            <small>
                <strong>Thank you for using Sri Lanka Post services</strong><br>
                Generated: {{ $receipt->created_at->format('Y-m-d H:i:s') }} |
                Receipt ID: {{ $receipt->id }} |
                Upload ID: {{ $upload->id }} |
                System Generated
            </small>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
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
        page-break-after: avoid !important;
    }

    /* Optimize receipt for single page printing */
    body {
        font-size: 9pt !important;
        line-height: 1.1 !important;
        margin: 0 !important;
        padding: 5px !important;
    }

    /* Very compact header */
    .card-header {
        background: #28a745 !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        padding: 8px !important;
        font-size: 12pt !important;
    }

    .card-header h3 {
        font-size: 12pt !important;
        margin-bottom: 2px !important;
    }

    .card-header p {
        font-size: 9pt !important;
        margin-bottom: 1px !important;
    }

    /* Very compact content */
    .card-body {
        padding: 8px !important;
    }

    /* Minimal spacing */
    .mb-4 {
        margin-bottom: 0.5rem !important;
    }

    .mb-3 {
        margin-bottom: 0.4rem !important;
    }

    .mb-2 {
        margin-bottom: 0.25rem !important;
    }

    .mb-1 {
        margin-bottom: 0.15rem !important;
    }

    .mt-4 {
        margin-top: 0.5rem !important;
    }

    .py-4, .py-3 {
        padding-top: 0.3rem !important;
        padding-bottom: 0.3rem !important;
    }

    .p-4, .p-3 {
        padding: 0.5rem !important;
    }

    /* Compact sections */
    .info-section {
        padding: 0.4rem !important;
        font-size: 8pt !important;
    }

    .info-section h6 {
        font-size: 9pt !important;
        margin-bottom: 0.25rem !important;
    }

    .info-section p {
        margin-bottom: 0.1rem !important;
        font-size: 8pt !important;
    }

    /* Compact service details */
    .text-center h5 {
        font-size: 10pt !important;
        margin-bottom: 0.1rem !important;
    }

    .text-center small {
        font-size: 7pt !important;
    }

    /* Very compact payment summary */
    .payment-summary {
        border: 1px solid #333 !important;
        padding: 0.4rem !important;
        margin-bottom: 0.3rem !important;
    }

    .payment-summary h5 {
        font-size: 9pt !important;
        margin-bottom: 0.2rem !important;
    }

    .payment-summary .h5,
    .payment-summary .h4 {
        font-size: 9pt !important;
        margin-bottom: 0 !important;
    }

    /* Compact terms */
    .terms-section {
        border: 1px solid #ccc !important;
        padding: 0.3rem !important;
        margin-bottom: 0.3rem !important;
    }

    .terms-section h6 {
        font-size: 8pt !important;
        margin-bottom: 0.1rem !important;
    }

    .terms-section ul {
        margin-bottom: 0 !important;
        font-size: 7pt !important;
        line-height: 1.0 !important;
        padding-left: 15px !important;
    }

    .terms-section li {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    /* Extra compact table */
    .table-responsive {
        margin-bottom: 0.3rem !important;
    }

    .table-sm {
        font-size: 7pt !important;
        margin-bottom: 0.3rem !important;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.15rem !important;
        line-height: 1.0 !important;
        border-width: 1px !important;
    }

    .table-sm thead th {
        font-size: 7pt !important;
        font-weight: bold !important;
    }

    /* Compact footer */
    .border-top {
        border-top: 1px solid #ccc !important;
        padding-top: 0.2rem !important;
    }

    .text-center small {
        font-size: 7pt !important;
        line-height: 1.0 !important;
    }

    /* Hide less essential elements on small space */
    @page {
        margin: 0.3in !important;
        size: A4 !important;
    }

    /* Force single page */
    * {
        page-break-inside: avoid !important;
    }

    .card-body > div {
        page-break-inside: avoid !important;
    }
}

/* PM Color Scheme Variables */
:root {
    --pm-primary: #17a2b8;
    --pm-primary-dark: #138496;
    --pm-accent: #28a745;
    --pm-accent-dark: #20c997;
}

.btn-pm-primary {
    background-color: var(--pm-primary);
    border-color: var(--pm-primary);
    color: white;
}

.btn-pm-primary:hover {
    background-color: var(--pm-primary-dark);
    border-color: var(--pm-primary-dark);
    color: white;
}

.btn-pm-accent {
    background-color: var(--pm-accent);
    border-color: var(--pm-accent);
    color: white;
}

.btn-pm-accent:hover {
    background-color: var(--pm-accent-dark);
    border-color: var(--pm-accent-dark);
    color: white;
}
</style>
@endsection
