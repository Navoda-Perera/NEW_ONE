{{-- Debug information --}}
@if(config('app.debug'))
    <div class="small text-muted mb-2">
        Debug: CurrentUser = {{ $currentUser ? 'Found' : 'NULL' }}
        | Auth User = {{ auth('pm')->user() ? 'Found' : 'NULL' }}
        @if($currentUser)
            | Location = {{ $currentUser->location ? 'Found' : 'NULL' }}
            | User ID = {{ $currentUser->id }}
            | Location ID = {{ $currentUser->location_id ?? 'NULL' }}
        @endif
        @if(auth('pm')->user())
            | Auth Name = {{ auth('pm')->user()->name }}
            | Auth Location ID = {{ auth('pm')->user()->location_id ?? 'NULL' }}
        @endif
    </div>
@endif

{{-- Use auth('pm')->user() directly if $currentUser is null --}}
@php
    $user = $currentUser ?? auth('pm')->user();
    if ($user && !$user->relationLoaded('location')) {
        $user->load('location');
    }
@endphp

@if($user && $user->location)
    <div class="card bg-light border-primary" style="min-width: 300px;">
        <div class="card-body p-3">
            <h6 class="card-title mb-2">
                <i class="bi bi-geo-alt-fill text-primary"></i>
            </h6>
            <div class="text-dark">
                <strong>{{ $user->location->name }}</strong>
            </div>
            <div class="small text-muted">
                Code: {{ $user->location->code }} | {{ $user->location->city }}, {{ $user->location->province }}
            </div>
            <div class="small text-muted">
                <i class="bi bi-telephone"></i> {{ $user->location->phone ?? 'N/A' }}
            </div>
        </div>
    </div>
@elseif($user)
    <div class="card bg-warning border-warning" style="min-width: 300px;">
        <div class="card-body p-3">
            <h6 class="card-title mb-2">
                <i class="bi bi-exclamation-triangle"></i> No Assignment
            </h6>
            <div class="text-dark">
                No post office assigned
            </div>
            <div class="small text-muted">
                Contact administrator for assignment
            </div>
        </div>
    </div>
@else
    <div class="card bg-danger border-danger" style="min-width: 300px;">
        <div class="card-body p-3">
            <h6 class="card-title mb-2">
                <i class="bi bi-exclamation-triangle"></i> Authentication Error
            </h6>
            <div class="text-white">
                User session error
            </div>
            <div class="small text-light">
                Please log in again
            </div>
        </div>
    </div>
@endif
