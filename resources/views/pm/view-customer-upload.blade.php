@extends('layouts.app')

@section('title', 'Customer Upload Details')

@section('styles')
<style>
.table-responsive {
    max-height: 70vh;
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Customer Upload #{{ $upload->id }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('pm.customer-uploads') }}">Customer Uploads</a></li>
                            <li class="breadcrumb-item active">Upload #{{ $upload->id }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('pm.customer-uploads') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Customer Uploads
                </a>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Items from {{ $upload->user->name }}</h5>
                </div>
                <div class="card-body">
                    @if($upload->associates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Receiver Details</th>
                                        <th>Service Type</th>
                                        <th>Weight</th>
                                        @php
                                            $hasCodeService = $upload->associates->contains('service_type', 'cod');
                                        @endphp
                                        @if($hasCodeService)
                                            <th>Amount</th>
                                        @endif
                                        <th>Postage</th>
                                        <th>Barcode</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upload->associates as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->receiver_name }}</strong>
                                                <br><small class="text-muted">{{ Str::limit($item->receiver_address, 40) }}</small>
                                                @if($item->contact_number)
                                                    <br><small class="text-info">{{ $item->contact_number }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $serviceType = $serviceTypeLabels[$item->service_type] ?? $item->service_type;
                                                @endphp
                                                <span class="badge bg-info">{{ $serviceType }}</span>
                                            </td>
                                            <td>
                                                @if($item->weight)
                                                    {{ number_format($item->weight) }}g
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @if($hasCodeService)
                                                <td>
                                                    @if($item->service_type === 'cod' && $item->amount)
                                                        <strong>LKR {{ number_format($item->amount, 2) }}</strong>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                @if($item->postage)
                                                    <span class="text-primary">LKR {{ number_format($item->postage, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->barcode)
                                                    <span class="badge bg-success">{{ $item->barcode }}</span>
                                                    <br><small class="text-muted">Customer provided</small>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                    <br><small class="text-muted">Will assign on accept</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $item->created_at->format('M d, Y') }}</small>
                                                <br><small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                @if($item->status === 'pending')
                                                    <div class="d-flex flex-column gap-1">
                                                        <form action="{{ route('pm.items.accept', $item->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm w-100" title="Accept">
                                                                <i class="bi bi-check-circle"></i> Accept
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('pm.items.reject', $item->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm w-100"
                                                                    onclick="return confirm('Are you sure you want to reject this item?')"
                                                                    title="Quick Reject">
                                                                <i class="bi bi-x-circle"></i> Quick Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="badge bg-{{ $item->status === 'accept' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($item->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-3">No items found in this upload.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
