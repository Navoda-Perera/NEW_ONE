@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-3" title="Back to User Management">
                    <i class="bi bi-arrow-left me-1"></i>Users
                </a>
                <h2 class="fw-bold text-dark mb-0">Edit User: {{ $user->name }}</h2>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nic" class="form-label fw-semibold">NIC Number</label>
                            <input id="nic" type="text" class="form-control @error('nic') is-invalid @enderror"
                                   name="nic" value="{{ old('nic', $user->nic) }}" required autocomplete="username">
                            @error('nic')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address (Optional)</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $user->email) }}" autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label fw-semibold">Mobile Number</label>
                            <input id="mobile" type="tel" class="form-control @error('mobile') is-invalid @enderror"
                                   name="mobile" value="{{ old('mobile', $user->mobile) }}" required autocomplete="tel"
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
                            <label for="user_type" class="form-label fw-semibold">User Type</label>
                            <select id="user_type" class="form-select @error('user_type') is-invalid @enderror"
                                    name="user_type" required>
                                <option value="internal" {{ old('user_type', $user->user_type) === 'internal' ? 'selected' : '' }}>Internal</option>
                                <option value="external" {{ old('user_type', $user->user_type) === 'external' ? 'selected' : '' }}>External</option>
                            </select>
                            @error('user_type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Internal users have system access, external users are customers.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">Role</label>
                            <select id="role" class="form-select @error('role') is-invalid @enderror"
                                    name="role" required onchange="toggleLocationField()">
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="pm" {{ old('role', $user->role) === 'pm' ? 'selected' : '' }}>Postmaster</option>
                                <option value="postman" {{ old('role', $user->role) === 'postman' ? 'selected' : '' }}>Postman</option>
                                <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Customer</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3" id="location-field" style="display: {{ in_array($user->role, ['pm', 'postman']) ? 'block' : 'none' }};">
                            <label for="location_id" class="form-label fw-semibold">Assign Post Office</label>
                            <select id="location_id" class="form-select @error('location_id') is-invalid @enderror"
                                    name="location_id">
                                <option value="">Select Post Office</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id', $user->location_id) == $location->id ? 'selected' : '' }}>
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
                                <small class="text-muted">Postmasters and Postmen must be assigned to a post office location.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">New Password (Leave blank to keep current)</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Leave empty if you don't want to change the password.</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password-confirm" class="form-label fw-semibold">Confirm New Password</label>
                            <input id="password-confirm" type="password" class="form-control"
                                   name="password_confirmation" autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active User
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-pencil-square me-2"></i>Update User
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

    if (roleSelect.value === 'pm' || roleSelect.value === 'postman') {
        locationField.style.display = 'block';
        locationSelect.required = true;
    } else {
        locationField.style.display = 'none';
        locationSelect.required = false;
        locationSelect.value = '';
    }
}

// Show location field if PM/Postman is already selected
document.addEventListener('DOMContentLoaded', function() {
    toggleLocationField();
});
</script>
@endsection