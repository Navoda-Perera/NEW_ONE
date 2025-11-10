<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt - Upload #{{ $upload->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        @media print {
            body {
                font-size: 8pt;
                line-height: 1.0;
                margin: 0;
                padding: 5px;
            }

            .no-print {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                page-break-after: avoid !important;
            }

            .card-header {
                background: #28a745 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 8px !important;
                text-align: center;
            }

            .card-header h3 {
                font-size: 11pt !important;
                margin-bottom: 2px !important;
            }

            .card-header p {
                font-size: 8pt !important;
                margin-bottom: 1px !important;
            }

            .card-body {
                padding: 8px !important;
            }

            .bg-light {
                background: #f8f9fa !important;
                padding: 0.3rem !important;
            }

            .bg-light h6 {
                font-size: 8pt !important;
                margin-bottom: 0.1rem !important;
            }

            .bg-light p {
                font-size: 7pt !important;
                margin-bottom: 0.05rem !important;
            }

            .mb-3, .mb-2, .mb-1 {
                margin-bottom: 0.2rem !important;
            }

            .text-center h5 {
                font-size: 8pt !important;
                margin-bottom: 0.05rem !important;
            }

            .text-center small {
                font-size: 6pt !important;
            }

            .table-sm {
                font-size: 6pt !important;
                margin-bottom: 0.2rem !important;
            }

            .table-sm td,
            .table-sm th {
                padding: 0.1rem !important;
                line-height: 1.0 !important;
                border-width: 0.5px !important;
            }

            .payment-summary {
                border: 1px solid #333 !important;
                background: #f8f9fa !important;
                padding: 0.3rem !important;
                margin-bottom: 0.2rem !important;
            }

            .payment-summary h5 {
                font-size: 8pt !important;
                margin-bottom: 0.1rem !important;
            }

            .payment-summary .h5 {
                font-size: 7pt !important;
                margin-bottom: 0 !important;
            }

            .border {
                border-width: 0.5px !important;
                padding: 0.2rem !important;
                margin-bottom: 0.2rem !important;
            }

            .border h6 {
                font-size: 7pt !important;
                margin-bottom: 0.05rem !important;
            }

            .border ul {
                font-size: 6pt !important;
                line-height: 0.9 !important;
                margin-bottom: 0 !important;
                padding-left: 12px !important;
            }

            .border-top {
                border-top: 0.5px solid #ccc !important;
                padding-top: 0.1rem !important;
            }

            .text-center small {
                font-size: 6pt !important;
                line-height: 0.9 !important;
            }

            @page {
                margin: 0.25in !important;
                size: A4 !important;
            }

            /* Force everything on single page */
            * {
                page-break-inside: avoid !important;
            }
        }

        @media screen {
            body {
                background-color: #f5f5f5;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button (only visible on screen) -->
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="bi bi-printer"></i> Print Receipt
        </button>
        <a href="{{ route('pm.view-customer-upload', $upload->id) }}" class="btn btn-secondary btn-lg ms-2">
            <i class="bi bi-arrow-left"></i> Back to Upload
        </a>
    </div>

    <!-- Receipt -->
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <!-- Receipt Header -->
        <div class="card-header text-white py-3">
            <h3 class="mb-1">
                <i class="bi bi-building me-2"></i>
                Sri Lanka Post
            </h3>
            <p class="mb-1">Official Service Receipt</p>
            <small><strong>{{ $receipt->location ? $receipt->location->name : 'Main Office' }}</strong></small>
        </div>

        <!-- Receipt Body -->
        <div class="card-body">
            <!-- Receipt Info -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded">
                        <h6 class="fw-bold text-primary mb-2">Receipt Information</h6>
                        <p class="mb-1"><strong>Receipt ID:</strong> #{{ $receipt->id }}</p>
                        <p class="mb-1"><strong>Passcode:</strong> <code class="bg-warning text-dark px-2 py-1 rounded">{{ $receipt->passcode }}</code></p>
                        <p class="mb-1"><strong>Date:</strong> {{ $receipt->created_at->format('d/m/Y') }}</p>
                        <p class="mb-0"><strong>Time:</strong> {{ $receipt->created_at->format('h:i A') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded">
                        <h6 class="fw-bold text-primary mb-2">Customer Information</h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $upload->user->name }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $upload->user->email }}</p>
                        @if($upload->user->nic)
                            <p class="mb-1"><strong>NIC:</strong> {{ $upload->user->nic }}</p>
                        @endif
                        @if($upload->user->mobile)
                            <p class="mb-0"><strong>Mobile:</strong> {{ $upload->user->mobile }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Service Summary -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="text-center bg-light p-2 rounded">
                        <h5 class="text-success mb-1">{{ $receipt->item_quantity }}</h5>
                        <small class="text-muted">Total Items</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center bg-light p-2 rounded">
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
                    <div class="text-center bg-light p-2 rounded">
                        <h5 class="text-primary mb-1">{{ $receipt->payment_type === 'cash' ? 'Cash' : ucfirst($receipt->payment_type) }}</h5>
                        <small class="text-muted">Payment Method</small>
                    </div>
                </div>
            </div>

            <!-- Items List (if space permits) -->
            @if($receipt->itemBulk && $receipt->itemBulk->items->count() > 0)
                <div class="mb-3">
                    <h6 class="fw-bold text-primary mb-2">Items Details</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="font-size: 9pt;">Barcode</th>
                                    <th style="font-size: 9pt;">Receiver</th>
                                    <th style="font-size: 9pt;">Weight</th>
                                    <th style="font-size: 9pt;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receipt->itemBulk->items as $item)
                                    <tr>
                                        <td><code style="font-size: 8pt;">{{ $item->barcode }}</code></td>
                                        <td style="font-size: 9pt;">{{ Str::limit($item->receiver_name, 20) }}</td>
                                        <td class="text-end" style="font-size: 9pt;">{{ number_format($item->weight, 0) }}g</td>
                                        <td class="text-end" style="font-size: 9pt;">{{ number_format($item->amount ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Payment Summary -->
            <div class="payment-summary border border-dark rounded mb-3">
                <h5 class="fw-bold text-center mb-2 text-dark">Payment Summary</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex justify-content-between">
                            <span>COD Amount:</span>
                            <span class="fw-bold">LKR {{ number_format($receipt->amount ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-between">
                            <span>Postage:</span>
                            <span class="fw-bold">LKR {{ number_format($receipt->postage ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <hr class="my-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success">Total Amount:</span>
                            <span class="fw-bold text-success h5 mb-0">LKR {{ number_format($receipt->total_amount ?? ($receipt->amount + $receipt->postage), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="border border-secondary rounded p-2 mb-3" style="font-size: 8pt;">
                <h6 class="fw-bold mb-1" style="font-size: 9pt;">Terms & Conditions</h6>
                <ul class="mb-0" style="font-size: 8pt; line-height: 1.1;">
                    <li>Present this receipt for item collection</li>
                    <li>Items must be collected within 30 days</li>
                    <li>Lost receipts require identity verification</li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="text-center border-top pt-2">
                <small style="font-size: 8pt;">
                    <strong>Thank you for using Sri Lanka Post</strong><br>
                    Generated: {{ $receipt->created_at->format('Y-m-d H:i') }} | Receipt #{{ $receipt->id }} | Upload #{{ $upload->id }}
                </small>
            </div>
        </div>
    </div>

    <script>
        // Auto-print on page load
        window.onload = function() {
            // Uncomment the line below to auto-print
            // window.print();
        };
    </script>
</body>
</html>
