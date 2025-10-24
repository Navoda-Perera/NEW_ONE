@extends('layouts.app')

@section('title', 'My Accepted Items')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.services.index') }}">
            <i class="bi bi-box-seam"></i> Postal Services
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.profile') }}">
            <i class="bi bi-person"></i> Profile
        </a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('customer.services.items') }}" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h2 class="fw-bold text-dark mb-0">My Accepted Items</h2>
                </div>
                <div>
                    <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Add New Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <a href="{{ route('customer.services.items') }}"
                   class="btn btn-outline-primary">
                    All Items
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'pending']) }}"
                   class="btn btn-outline-warning">
                    Pending
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'accept']) }}"
                   class="btn btn-success">
                    Accepted
                </a>
                <a href="{{ route('customer.services.items', ['status' => 'reject']) }}"
                   class="btn btn-outline-danger">
                    Rejected
                </a>
            </div>
        </div>
    </div>

    <!-- Accepted Items Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Accepted Items - Processed by Post Master
                    </h5>
                </div>
                <div class="card-body">
                    @if($acceptedItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Barcode</th>
                                        <th>Receiver</th>
                                        <th>Weight</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Accepted On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($acceptedItems as $item)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $item->barcode }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $item->receiver_name }}</strong><br>
                                                    <small class="text-muted">{{ Str::limit($item->receiver_address, 50) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($item->weight)
                                                    <span class="fw-semibold">{{ number_format($item->weight) }}g</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-success">LKR {{ number_format($item->amount, 2) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'accept' => 'bg-success',
                                                        'dispatched' => 'bg-primary',
                                                        'delivered' => 'bg-info',
                                                        'paid' => 'bg-warning',
                                                        'returned' => 'bg-danger'
                                                    ][$item->status] ?? 'bg-secondary';

                                                    $statusLabel = [
                                                        'accept' => 'Accepted',
                                                        'dispatched' => 'Dispatched',
                                                        'delivered' => 'Delivered',
                                                        'paid' => 'Paid',
                                                        'returned' => 'Returned'
                                                    ][$item->status] ?? ucfirst($item->status);
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $item->created_at->format('M d, Y') }}</span>
                                                <br><small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"
                                                        onclick="trackItem('{{ $item->barcode }}')"
                                                        title="Track Item">
                                                    <i class="bi bi-geo-alt"></i> Track
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $acceptedItems->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No Accepted Items</h4>
                            <p class="text-muted">Your submitted items will appear here once they are accepted by the Post Master.</p>
                            <a href="{{ route('customer.services.add-single-item') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Submit New Item
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function trackItem(barcode) {
    alert('Tracking feature will be implemented for barcode: ' + barcode);
    // TODO: Implement tracking functionality
}
</script>
@endsection
