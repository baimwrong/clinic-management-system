@extends('layouts.app')

@section('title', 'Edit Doctor - ' . $doctor->name)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('doctors.show', $doctor) }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to {{ $doctor->name }}
            </a>
            <h3 class="fw-bold mb-0">Edit Doctor Profile</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('doctors.update', $doctor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $doctor->user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $doctor->user->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $doctor->user->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Reset Password (leave empty to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="New password">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Specialization <span class="text-danger">*</span></label>
                        <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $doctor->specialization) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">License Number <span class="text-danger">*</span></label>
                        <input type="text" name="license_number" class="form-control font-monospace" value="{{ old('license_number', $doctor->license_number) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Qualification</label>
                        <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $doctor->qualification) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Consultation Fee ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="consultation_fee" class="form-control" value="{{ old('consultation_fee', $doctor->consultation_fee) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Room Number</label>
                        <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $doctor->room_number) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Active Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ $doctor->status === 'active' ? 'selected' : '' }}>Active Doctor</option>
                            <option value="inactive" {{ $doctor->status === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Available Start Time</label>
                        <input type="time" name="available_time_start" class="form-control" value="{{ $doctor->available_time_start }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Available End Time</label>
                        <input type="time" name="available_time_end" class="form-control" value="{{ $doctor->available_time_end }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Consultation Days</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]" value="{{ $day }}" id="edit_day_{{ $day }}" {{ in_array($day, $doctor->available_days ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="edit_day_{{ $day }}">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Bio</label>
                        <textarea name="bio" class="form-control" rows="2">{{ old('bio', $doctor->bio) }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
