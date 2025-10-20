<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $receipt->id }} - Print</title>
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
            .page-break {
                page-break-after: always;
            }
        }

        .receipt-header {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            border: 2px solid #000;
            padding: 5px 10px;
            display: inline-block;
        }

        .amount-box {
            border: 2px solid #000;
            padding: 10px;
            text-align: center;
            background-color: #f8f9fa;
        }

        .service-badge {
            font-size: 18px;
            padding: 8px 16px;
            border: 2px solid #000;
            background-color: #fff;
            color: #000;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- Print Button -->
        <div class="no-print mb-3 text-center">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="bi bi-printer"></i> Print Receipt
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">
                <i class="bi bi-x-circle"></i> Close
            </button>
        </div>

        <!-- Receipt Content -->
        <div class="card">
            <div class="card-body">
                <!-- Header -->
                <div class="receipt-header text-center">
                    <h2 class="mb-2"><strong>SRI LANKA POST</strong></h2>
                    <h4 class="mb-1">POSTAL SERVICE RECEIPT</h4>
                    <p class="mb-1"><strong>{{ $receipt->location->name ?? 'POST OFFICE' }}</strong></p>
                    <p class="mb-0">Receipt #{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>

                <!-- Service Type and Date -->
                <div class="row mb-4">
                    <div class="col-6">
                        <p><strong>Service Type:</strong></p>
                        <div class="service-badge">
                            {{ strtoupper(str_replace('_', ' ', $receipt->itemBulk->service_type)) }}
                        </div>
                    </div>
                    <div class="col-6 text-end">
                        <p><strong>Date & Time:</strong></p>
                        <p>{{ $receipt->created_at->format('Y-m-d') }}</p>
                        <p>{{ $receipt->created_at->format('H:i:s') }}</p>
                    </div>
                </div>

                <!-- Sender & Receiver Info -->
                @php $item = $receipt->itemBulk->items->first(); @endphp
                <div class="row mb-4">
                    <div class="col-6">
                        <h6><strong>SENDER DETAILS</strong></h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $receipt->itemBulk->sender_name }}</p>
                        <p class="mb-0"><strong>PM:</strong> {{ $receipt->itemBulk->creator->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-6">
                        <h6><strong>RECEIVER DETAILS</strong></h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $item->receiver_name }}</p>
                        <p class="mb-1"><strong>Mobile:</strong> {{ $item->smsSents->first()->receiver_mobile ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Address:</strong> {{ $item->receiver_address }}</p>
                    </div>
                </div>

                <!-- Item Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6><strong>ITEM DETAILS</strong></h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
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

                            @if($receipt->itemBulk->service_type === 'cod')
                                @php
                                    $item = $receipt->itemBulk->items->first();
                                    // Parse COD details from notes
                                    $notes = $receipt->itemBulk->notes;
                                    preg_match('/COD Amount: LKR ([\d.]+)/', $notes, $codMatches);
                                    preg_match('/Postage: LKR ([\d.]+)/', $notes, $postageMatches);
                                    $codAmount = isset($codMatches[1]) ? $codMatches[1] : 0;
                                    $postage = isset($postageMatches[1]) ? $postageMatches[1] : 0;
                                @endphp
                                <div class="d-flex justify-content-between">
                                    <span>COD Amount:</span>
                                    <span>LKR {{ number_format($codAmount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Postage:</span>
                                    <span>LKR {{ number_format($postage, 2) }}</span>
                                </div>
                                <hr>
                            @endif

                            <div class="d-flex justify-content-between">
                                <span>No. of Items:</span>
                                <span>{{ $receipt->item_quantity }}</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <strong>Total Amount:</strong>
                                <strong>LKR {{ number_format($receipt->amount, 2) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between mt-2">
                                <span>Payment:</span>
                                <span>{{ strtoupper($receipt->payment_type) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Office Information -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h6><strong>OFFICE INFORMATION</strong></h6>
                        <p class="mb-1"><strong>Location:</strong> {{ $receipt->location->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Postmaster:</strong> {{ $receipt->itemBulk->creator->name ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Passcode:</strong> {{ $receipt->passcode }}</p>
                    </div>
                    <div class="col-6">
                        @if($receipt->itemBulk->service_type === 'cod')
                        <div class="alert alert-warning p-2">
                            <small><strong>COD Instructions:</strong><br>
                            Amount LKR {{ number_format($item->cod_amount ?? 0, 2) }} will be collected from receiver upon delivery.</small>
                        </div>
                        @endif

                        @if($receipt->itemBulk->service_type === 'register_post')
                        <div class="alert alert-success p-2">
                            <small><strong>Registered Post:</strong><br>
                            Includes tracking and delivery confirmation with basic insurance coverage.</small>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="border-top pt-3">
                    <h6><strong>TERMS & CONDITIONS</strong></h6>
                    <div class="row">
                        <div class="col-6">
                            <small>
                                • This receipt is required for tracking and claims<br>
                                • Keep this receipt until delivery is completed<br>
                                • Contact office with receipt number for inquiries
                            </small>
                        </div>
                        <div class="col-6">
                            <small>
                                • Items are subject to postal regulations<br>
                                • Delivery time depends on distance and service type<br>
                                • For complaints, contact customer service
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4 border-top pt-3">
                    <small>
                        <strong>Thank you for using Sri Lanka Post services</strong><br>
                        Generated: {{ $receipt->created_at->format('Y-m-d H:i:s') }} |
                        Receipt ID: {{ $receipt->id }} |
                        System Generated
                    </small>
                </div>
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
