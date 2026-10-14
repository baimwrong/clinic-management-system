@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1">User Profile & Account Settings</h3>
            <p class="text-muted small mb-0">Manage your personal credentials, contact details, and security passwords.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-clinic text-center p-4">
                <div class="rounded-circle bg-teal-subtle text-teal mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem; background-color: #ccfbf1; color: #0d9488;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div>
                    <span class="badge {{ $user->role_badge_class }} px-3 py-1">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                @if($user->isDoctor() && $user->doctor)
                <div class="mt-4 pt-3 border-top text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Staff ID:</span>
                        <span class="fw-semibold">{{ $user->doctor->staff_id }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Specialization:</span>
                        <span class="fw-semibold">{{ $user->doctor->specialization }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">License:</span>
                        <span class="fw-semibold">{{ $user->doctor->license_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Room / Office:</span>
                        <span class="fw-semibold">{{ $user->doctor->room_number ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Consultation Fee:</span>
                        <span class="fw-semibold text-success">${{ number_format($user->doctor->consultation_fee, 2) }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-person-lines-fill me-2 text-primary"></i> Personal Details</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+1-555-0100">
                            </div>

                            @if($user->isDoctor() && $user->doctor)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Office / Room Number</label>
                                <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $user->doctor->room_number) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Professional Bio</label>
                                <textarea name="bio" class="form-control" rows="3">{{ old('bio', $user->doctor->bio) }}</textarea>
                            </div>
                            @endif

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-clinic">
                                    <i class="bi bi-check2-circle me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-lock-fill me-2 text-warning"></i> Change Password</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold small">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-shield-lock me-1"></i> Update Security Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
