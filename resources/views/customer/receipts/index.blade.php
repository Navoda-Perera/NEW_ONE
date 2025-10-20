@extends('layouts.app')

@section('title', 'My Receipts')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-box-seam"></i> Services
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('customer.services.index') }}">All Services</a></li>
            <li><a class="dropdown-item" href="{{ route('customer.services.add-single-item') }}">Add Single Item</a></li>
            <li><a class="dropdown-item" href="{{ route('customer.services.bulk-upload') }}">Bulk Upload</a></li>
        </ul>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('customer.receipts.index') }}">
            <i class="bi bi-receipt"></i> Receipts
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('customer.tracking.index') }}">
            <i class="bi bi-search"></i> Tracking
        </a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-receipt text-success"></i> My Receipts
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Receipts</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1 text-primary">{{ $totalReceipts }}</h3>
                            <p class="mb-0 text-muted">Total Receipts</p>
                        </div>
                        <div class="text-primary opacity-50">
                            <i class="bi bi-receipt display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1 text-success">{{ $totalItems }}</h3>
                            <p class="mb-0 text-muted">Total Items</p>
                        </div>
                        <div class="text-success opacity-50">
                            <i class="bi bi-box display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search by Barcode -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-search"></i> Search Receipt by Barcode
                    </h6>
                    <form action="{{ route('customer.receipts.search') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" class="form-control" name="barcode"
                                   placeholder="Enter barcode to find receipt" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                    @if($errors->has('barcode'))
                        <small class="text-danger">{{ $errors->first('barcode') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Receipts List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">All Receipts ({{ $receipts->total() }} total)</h6>
                </div>
                <div class="card-body">
                    @if($receipts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Service Type</th>
                                        <th>Barcode</th>
                                        <th>Weight</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipts as $receipt)
                                    <tr>
                                        <td>
                                            <strong>#{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $receipt->passcode }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $receipt->itemBulk->service_type === 'slp_courier' ? 'primary' : ($receipt->itemBulk->service_type === 'cod' ? 'warning' : 'success') }}">
                                                {{ strtoupper(str_replace('_', ' ', $receipt->itemBulk->service_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($receipt->itemBulk->items->count() > 0)
                                                {{ $receipt->itemBulk->items->first()->barcode }}
                                                @if($receipt->itemBulk->items->count() > 1)
                                                    <br><small class="text-muted">+{{ $receipt->itemBulk->items->count() - 1 }} more</small>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($receipt->itemBulk->items->count() > 0)
                                                {{ $receipt->itemBulk->items->first()->weight }}g
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($receipt->itemBulk->service_type === 'cod')
                                                <strong>LKR {{ number_format($receipt->itemBulk->items->sum('amount'), 2) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ ucfirst($receipt->payment_type) }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                                <br>
                                                <small class="text-muted">No COD</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $receipt->created_at->format('Y-m-d') }}
                                            <br>
                                            <small class="text-muted">{{ $receipt->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('customer.receipts.show', $receipt->id) }}"
                                                   class="btn btn-outline-primary" title="View Receipt">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('customer.receipts.download', $receipt->id) }}"
                                                   class="btn btn-outline-success" target="_blank" title="Download/Print">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $receipts->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-receipt display-4 text-muted"></i>
                            <h5 class="text-muted mt-3">No Receipts Found</h5>
                            <p class="text-muted">You don't have any receipts yet. Create postal items to generate receipts.</p>
                            <a href="{{ route('customer.services.index') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create New Item
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
