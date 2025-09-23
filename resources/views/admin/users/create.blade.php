@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="fw-bold text-dark mb-0">Create New User</h2>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nic" class="form-label fw-semibold">NIC Number</label>
                            <input id="nic" type="text" class="form-control @error('nic') is-invalid @enderror"
                                   name="nic" value="{{ old('nic') }}" required autocomplete="username">
                            @error('nic')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address (Optional)</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label fw-semibold">Mobile Number</label>
                            <input id="mobile" type="tel" class="form-control @error('mobile') is-invalid @enderror"
                                   name="mobile" value="{{ old('mobile') }}" required autocomplete="tel"
                                   pattern="[0-9]{10}" placeholder="0771234567">
                            @error('mobile')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Enter a 10-digit mobile number (e.g., 0771234567)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">Role</label>
                            <select id="role" class="form-select @error('role') is-invalid @enderror"
                                    name="role" required onchange="toggleLocationField()">
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="pm" {{ old('role') === 'pm' ? 'selected' : '' }}>Postmaster</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Only Admin and Postmaster roles can be created through this form.</small>
                            </div>
                        </div>

                        <div class="mb-3" id="location-field" style="display: none;">
                            <label for="location_id" class="form-label fw-semibold">Assign Post Office</label>
                            <select id="location_id" class="form-select @error('location_id') is-invalid @enderror"
                                    name="location_id">
                                <option value="">Select Post Office</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} ({{ $location->code }}) - {{ $location->city }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Postmasters must be assigned to a post office location.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password-confirm" class="form-label fw-semibold">Confirm Password</label>
                            <input id="password-confirm" type="password" class="form-control"
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active User
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLocationField() {
    const roleSelect = document.getElementById('role');
    const locationField = document.getElementById('location-field');
    const locationSelect = document.getElementById('location_id');

    if (roleSelect.value === 'pm') {
        locationField.style.display = 'block';
        locationSelect.required = true;
    } else {
        locationField.style.display = 'none';
        locationSelect.required = false;
        locationSelect.value = '';
    }
}

// Show location field if PM is already selected (e.g., on validation error)
document.addEventListener('DOMContentLoaded', function() {
    toggleLocationField();
});
</script>
@endsection
