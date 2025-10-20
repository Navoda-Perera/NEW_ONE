<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }} - Download</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            body {
                font-size: 12px;
            }
            .no-print {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }

        .receipt-header {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            border: 1px solid #000;
            padding: 3px 8px;
            display: inline-block;
        }

        .amount-box {
            border: 2px solid #000;
            padding: 10px;
            text-align: center;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- Print/Download Controls -->
        <div class="no-print mb-3 text-center">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="bi bi-printer"></i> Print Receipt
            </button>
            <a href="{{ route('customer.receipts.show', $receipt->id) }}" class="btn btn-secondary btn-lg ms-2">
                <i class="bi bi-arrow-left"></i> Back to View
            </a>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-lg ms-2">
                <i class="bi bi-x-circle"></i> Close
            </button>
        </div>

        <!-- Receipt Content -->
        <div class="card">
            <div class="card-body">
                <!-- Header -->
                <div class="receipt-header text-center">
                    <h2 class="mb-2"><strong>SRI LANKA POST</strong></h2>
                    <h4 class="mb-1">OFFICIAL RECEIPT</h4>
                    <p class="mb-1"><strong>{{ $receipt->location->name ?? 'POST OFFICE' }}</strong></p>
                    <p class="mb-0">Receipt #{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>

                <!-- Service Type and Date -->
                <div class="row mb-4">
                    <div class="col-6">
                        <p><strong>Service Type:</strong></p>
                        <div class="border p-2 text-center">
                            <strong>{{ strtoupper(str_replace('_', ' ', $receipt->itemBulk->service_type)) }}</strong>
                        </div>
                    </div>
                    <div class="col-6 text-end">
                        <p><strong>Date & Time:</strong></p>
                        <p>{{ $receipt->created_at->format('Y-m-d') }}</p>
                        <p>{{ $receipt->created_at->format('H:i:s') }}</p>
                    </div>
                </div>

                <!-- Customer & Office Info -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h6><strong>CUSTOMER INFORMATION</strong></h6>
                        <p class="mb-1"><strong>Sender:</strong> {{ $receipt->itemBulk->sender_name }}</p>
                        @if($receipt->itemBulk->items->count() > 0)
                            @php $firstItem = $receipt->itemBulk->items->first(); @endphp
                            <p class="mb-1"><strong>Receiver:</strong> {{ $firstItem->receiver_name }}</p>
                            <p class="mb-0"><strong>Mobile:</strong> {{ $firstItem->smsSents->first()->receiver_mobile ?? 'N/A' }}</p>
                        @endif
                    </div>
                    <div class="col-6">
                        <h6><strong>OFFICE INFORMATION</strong></h6>
                        <p class="mb-1"><strong>Location:</strong> {{ $receipt->location->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Postmaster:</strong> {{ $receipt->itemBulk->creator->name ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Passcode:</strong> {{ $receipt->passcode }}</p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6><strong>ITEM DETAILS</strong></h6>
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Barcode</th>
                                    <th>Receiver</th>
                                    <th>Address</th>
                                    <th>Weight</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receipt->itemBulk->items as $item)
                                <tr>
                                    <td>
                                        <div class="barcode-text">{{ $item->barcode }}</div>
                                    </td>
                                    <td>
                                        {{ $item->receiver_name }}
                                        @if($item->receiver_mobile)
                                            <br><small>{{ $item->receiver_mobile }}</small>
                                        @endif
                                    </td>
                                    <td><small>{{ \Str::limit($item->receiver_address, 50) }}</small></td>
                                    <td>{{ $item->weight }}g</td>
                                    <td>LKR {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="row mb-4">
                    <div class="col-6 offset-6">
                        <div class="amount-box">
                            <h6><strong>PAYMENT SUMMARY</strong></h6>

                                @php
                                    // Calculate totals from actual items
                                    $totalCodAmount = $receipt->itemBulk->items->sum('amount');
                                @endphp

                                @if($receipt->itemBulk->service_type === 'cod')
                                <div class="d-flex justify-content-between">
                                    <span>COD Amount:</span>
                                    <span>LKR {{ number_format($totalCodAmount, 2) }}</span>
                                </div>
                                <hr>
                            @endif

                            <div class="d-flex justify-content-between">
                                <span>No. of Items:</span>
                                <span>{{ $receipt->itemBulk->items->count() }}</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <strong>Total Amount:</strong>
                                <strong>LKR {{ number_format($totalCodAmount, 2) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between mt-2">
                                <span>Payment:</span>
                                <span>{{ strtoupper($receipt->payment_type) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Specific Information -->
                @if($receipt->itemBulk->service_type === 'cod')
                <div class="alert alert-warning p-2 mb-3">
                    <strong>COD Service:</strong> Amount LKR {{ number_format($totalCodAmount, 2) }}
                    will be collected from receiver upon delivery.
                </div>
                @endif

                @if($receipt->itemBulk->service_type === 'register_post')
                <div class="alert alert-success p-2 mb-3">
                    <strong>Register Post:</strong> Includes tracking, delivery confirmation, and basic insurance coverage.
                </div>
                @endif

                <!-- Terms and Conditions -->
                <div class="border-top pt-3 mb-3">
                    <h6><strong>TERMS & CONDITIONS</strong></h6>
                    <div class="row">
                        <div class="col-6">
                            <small>
                                • This receipt is required for all inquiries and claims<br>
                                • Keep this receipt until delivery is completed<br>
                                • Items are subject to postal regulations
                            </small>
                        </div>
                        <div class="col-6">
                            <small>
                                • Delivery time varies by service type and destination<br>
                                • For support, contact customer service<br>
                                • Report any issues within 30 days
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center border-top pt-3">
                    <small>
                        <strong>Thank you for using Sri Lanka Post services</strong><br>
                        Generated: {{ $receipt->created_at->format('Y-m-d H:i:s') }} |
                        Receipt ID: {{ $receipt->id }}<br>
                        For tracking visit: www.slpost.lk | Customer Service: 1911
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Optional: Auto-print when page loads
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
