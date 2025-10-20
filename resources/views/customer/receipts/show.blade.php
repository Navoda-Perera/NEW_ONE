@extends('layouts.app')

@section('title', 'Receipt Details')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.receipts.index') }}">
            <i class="bi bi-receipt"></i> Receipts
        </a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-receipt text-success"></i> Receipt Details
                </h1>
                <div>
                    <a href="{{ route('customer.receipts.download', $receipt->id) }}"
                       class="btn btn-success" target="_blank">
                        <i class="bi bi-download"></i> Download/Print
                    </a>
                    <a href="{{ route('customer.receipts.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Receipts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-receipt"></i> POSTAL SERVICE RECEIPT
                    </h4>
                    <small>Receipt #{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}</small>
                </div>
                <div class="card-body">
                    <!-- Receipt Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Office Information</h6>
                            <p class="mb-1"><strong>Location:</strong> {{ $receipt->location->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Postmaster:</strong> {{ $receipt->itemBulk->creator->name ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Created:</strong> {{ $receipt->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-success">Service Information</h6>
                            <p class="mb-1">
                                <span class="badge bg-{{ $receipt->itemBulk->service_type === 'slp_courier' ? 'primary' : ($receipt->itemBulk->service_type === 'cod' ? 'warning' : 'success') }} fs-6">
                                    {{ strtoupper(str_replace('_', ' ', $receipt->itemBulk->service_type)) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Passcode:</strong> {{ $receipt->passcode }}</p>
                            <p class="mb-0"><strong>Payment:</strong> {{ ucfirst($receipt->payment_type) }}</p>
                        </div>
                    </div>

                    <hr>

                    <!-- Item Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-box"></i> Item Details ({{ $receipt->item_quantity }} item{{ $receipt->item_quantity > 1 ? 's' : '' }})
                            </h6>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Sender:</strong> {{ $receipt->itemBulk->sender_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Service Category:</strong> {{ ucfirst($receipt->itemBulk->category) }}</p>
                                </div>
                            </div>

                            @foreach($receipt->itemBulk->items as $index => $item)
                            <div class="card mb-3 border-light">
                                <div class="card-body bg-light">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Barcode:</strong>
                                                <span class="font-monospace bg-white p-2 rounded border">{{ $item->barcode }}</span>
                                            </p>
                                            <p class="mb-2"><strong>Weight:</strong> {{ $item->weight }}g</p>
                                            @if($receipt->itemBulk->service_type === 'cod' && $item->cod_amount)
                                                <p class="mb-0"><strong>COD Amount:</strong> LKR {{ number_format($item->cod_amount, 2) }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Receiver:</strong> {{ $item->receiver_name }}</p>
                                            <p class="mb-2"><strong>Mobile:</strong> {{ $item->smsSents->first()->receiver_mobile ?? 'N/A' }}</p>
                                            <p class="mb-0"><strong>Address:</strong> {{ $item->receiver_address }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <!-- Pricing Details -->
                    <div class="row mb-4">
                        <div class="col-md-6 offset-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Payment Summary</h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        // Calculate totals from actual items
                                        $totalCodAmount = $receipt->itemBulk->items->sum('amount');
                                    @endphp

                                    @if($receipt->itemBulk->service_type === 'cod')
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total COD Amount:</span>
                                            <span>LKR {{ number_format($totalCodAmount, 2) }}</span>
                                        </div>
                                        <hr class="my-2">
                                    @endif

                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Number of Items:</span>
                                        <span>{{ $receipt->itemBulk->items->count() }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span><strong>Total Amount:</strong></span>
                                        <span><strong>LKR {{ number_format($totalCodAmount, 2) }}</strong></span>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span>Payment Method:</span>
                                        <span class="badge bg-secondary">{{ strtoupper($receipt->payment_type) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Information -->
                    @if($receipt->itemBulk->service_type === 'cod')
                    <div class="alert alert-warning">
                        <h6 class="alert-heading"><i class="bi bi-cash-coin"></i> Cash on Delivery (COD) Information</h6>
                        <p class="mb-0">The receiver will pay LKR {{ number_format($totalCodAmount, 2) }} upon delivery. This amount will be transferred to you after successful delivery.</p>
                    </div>
                    @endif

                    @if($receipt->itemBulk->service_type === 'register_post')
                    <div class="alert alert-success">
                        <h6 class="alert-heading"><i class="bi bi-shield-check"></i> Register Post Features</h6>
                        <ul class="mb-0">
                            <li>Full tracking from origin to destination</li>
                            <li>Delivery confirmation with signature</li>
                            <li>Basic insurance coverage included</li>
                            <li>Priority handling and faster delivery</li>
                        </ul>
                    </div>
                    @endif

                    @if($receipt->itemBulk->service_type === 'slp_courier')
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bi bi-truck"></i> SLP Courier Service</h6>
                        <p class="mb-0">Fast and reliable courier delivery service with tracking capability. Your item will be delivered with care to the specified address.</p>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($receipt->itemBulk->notes)
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="text-info">Notes</h6>
                                    <p class="mb-0">{{ $receipt->itemBulk->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Keep this receipt for tracking and delivery confirmation.
                        For assistance, contact the issuing office with receipt number {{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}.
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
</style>
@endsection
