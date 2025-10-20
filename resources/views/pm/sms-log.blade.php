@extends('layouts.app')

@section('title', 'SMS Log')

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.items.pending') }}">
            <i class="bi bi-hourglass-split me-2"></i>Pending Items
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.bulk-upload') }}">
            <i class="bi bi-upload me-2"></i>Bulk Upload
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('pm.sms-log') }}">
            <i class="bi bi-chat-dots me-2"></i>SMS Log
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pm.customers.index') }}">
            <i class="bi bi-people me-2"></i>Customers
        </a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-dots me-2"></i>SMS Notifications Log
                    </h5>
                    <div class="text-muted">
                        Total Records: {{ $smsLogs->total() }}
                    </div>
                </div>
                <div class="card-body">
                    @if($smsLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Item ID</th>
                                        <th>Sender Mobile</th>
                                        <th>Receiver Mobile</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($smsLogs as $sms)
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $sms->created_at->format('M d, Y') }}<br>
                                                    {{ $sms->created_at->format('h:i A') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ $sms->item_id }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="bi bi-phone me-1"></i>
                                                {{ $sms->sender_mobile }}
                                            </td>
                                            <td>
                                                <i class="bi bi-phone-fill me-1"></i>
                                                {{ $sms->receiver_mobile }}
                                            </td>
                                            <td>
                                                @switch($sms->status)
                                                    @case('accept')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Accepted
                                                        </span>
                                                        @break
                                                    @case('addbeat')
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-plus-circle me-1"></i>Add Beat
                                                        </span>
                                                        @break
                                                    @case('delivered')
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-truck me-1"></i>Delivered
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">
                                                            {{ ucfirst($sms->status) }}
                                                        </span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $smsLogs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No SMS records found</h5>
                            <p class="text-muted">SMS notifications will appear here when items are accepted.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
