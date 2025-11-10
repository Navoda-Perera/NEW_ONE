@extends('layouts.app')

@section('title', 'Receipt')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-receipt text-success"></i> Receipt Generated
                </h1>
                <div>
                    <a href="{{ route('pm.single-item.print-receipt', $receipt->id) }}"
                       class="btn btn-primary" target="_blank">
                        <i class="bi bi-printer"></i> Print Receipt
                    </a>
                    <a href="{{ route('pm.single-item.index') }}" class="btn btn-secondary">
                        <i class="bi bi-plus"></i> Add Another Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-receipt"></i> POSTAL SERVICE RECEIPT
                    </h4>
                    <small>Receipt #{{ $receipt->id }}</small>
                </div>
                <div class="card-body">
                    <!-- Receipt Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Office Information</h6>
                            <p class="mb-1"><strong>Location:</strong> {{ $receipt->location->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>PM:</strong> {{ $receipt->itemBulk->creator->name ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Date:</strong> {{ $receipt->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-success">Service Type</h6>
                            <p class="mb-1">
                                <span class="badge bg-{{ $receipt->itemBulk->service_type === 'slp_courier' ? 'primary' : ($receipt->itemBulk->service_type === 'cod' ? 'warning' : 'success') }} fs-6">
                                    {{ strtoupper(str_replace('_', ' ', $receipt->itemBulk->service_type)) }}
                                </span>
                            </p>
                            <p class="mb-0"><strong>Passcode:</strong> {{ $receipt->passcode }}</p>
                        </div>
                    </div>

                    <hr>

                    <!-- Item Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Item Details</h6>
                            @foreach($receipt->itemBulk->items as $item)
                            <div class="row mb-3 p-3 bg-light rounded">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Barcode:</strong>
                                        <span class="font-monospace bg-white p-1 rounded border">{{ $item->barcode }}</span>
                                    </p>
                                    <p class="mb-1"><strong>Sender:</strong> {{ $receipt->itemBulk->sender_name }}</p>
                                    <p class="mb-0"><strong>Weight:</strong> {{ $item->weight }}g</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Receiver:</strong> {{ $item->receiver_name }}</p>
                                    <p class="mb-1"><strong>Mobile:</strong> {{ $item->smsSents->first()->receiver_mobile ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Address:</strong> {{ $item->receiver_address }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <!-- Pricing Details -->
                    <div class="row mb-4">
                        <div class="col-md-6 offset-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-primary mb-3">Payment Summary</h6>

                                @if($receipt->itemBulk->service_type === 'cod')
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>COD Amount:</span>
                                        <span>LKR {{ number_format($receipt->amount, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Postage:</span>
                                        <span>LKR {{ number_format($receipt->postage, 2) }}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><strong>Total Amount:</strong></span>
                                        <span><strong>LKR {{ number_format($receipt->total_amount, 2) }}</strong></span>
                                    </div>
                                @else
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><strong>Total Postage:</strong></span>
                                        <span><strong>LKR {{ number_format($receipt->postage, 2) }}</strong></span>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between mb-2">
                                    <span>No. of Items:</span>
                                    <span>{{ $receipt->item_quantity }}</span>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>Payment Type:</span>
                                    <span class="badge bg-secondary">{{ strtoupper($receipt->payment_type) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="border-top pt-3">
                                <h6 class="text-muted">Terms & Conditions</h6>
                                <small class="text-muted">
                                    <ul class="mb-0">
                                        <li>This receipt is valid for tracking and delivery claims</li>
                                        <li>Please keep this receipt safe until delivery is completed</li>
                                        <li>For inquiries, contact the issuing post office with receipt number</li>
                                        @if($receipt->itemBulk->service_type === 'cod')
                                        <li>COD amount will be collected from receiver upon delivery</li>
                                        @endif
                                        @if($receipt->itemBulk->service_type === 'register_post')
                                        <li>Registered post includes basic insurance coverage</li>
                                        @endif
                                    </ul>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center text-muted">
                    <small>
                        Generated on {{ $receipt->created_at->format('Y-m-d H:i:s') }} |
                        Receipt ID: {{ $receipt->id }} |
                        System Generated Receipt
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.font-monospace {
    font-family: 'Courier New', monospace;
}

@media print {
    .btn {
        display: none !important;
    }

    .breadcrumb,
    .navbar {
        display: none !important;
    }
}
</style>
@endsection
