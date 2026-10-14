@extends('layouts.app')

@section('title', 'Add Doctor Profile')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('doctors.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Doctors Directory
            </a>
            <h3 class="fw-bold mb-0">Register Medical Specialist</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('doctors.store') }}" method="POST">
                @csrf
                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-fill me-2"></i> 1. Account & Credentials</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Full Name (including prefix) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Dr. Arthur Pendelton">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="e.g. arthur.p@clinic.local">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Initial Login Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" value="password123" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Contact Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1-555-0188">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-teal mb-3" style="color: #0d9488;"><i class="bi bi-mortarboard-fill me-2"></i> 2. Clinical Practice & Licensing</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Clinical Specialization <span class="text-danger">*</span></label>
                        <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" required placeholder="e.g. Cardiology, Pediatrics, General Practice">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Medical License Number <span class="text-danger">*</span></label>
                        <input type="text" name="license_number" class="form-control font-monospace" value="{{ old('license_number') }}" required placeholder="e.g. MD-LIC-998822">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Qualifications & Degrees</label>
                        <input type="text" name="qualification" class="form-control" value="{{ old('qualification') }}" placeholder="e.g. MD, FACP, MBBS">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Standard Consultation Fee ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="consultation_fee" class="form-control" value="{{ old('consultation_fee', '60.00') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Office / Clinic Room Number</label>
                        <input type="text" name="room_number" class="form-control" value="{{ old('room_number', 'Room 102') }}" placeholder="e.g. Suite 201">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Professional Biography / Specialty Background</label>
                        <textarea name="bio" class="form-control" rows="2" placeholder="Experience, board certifications, sub-specialties...">{{ old('bio') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-fill me-2"></i> 3. Clinical Schedule & Office Hours</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Consultation Days</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]" value="{{ $day }}" id="day_{{ $day }}" {{ in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="day_{{ $day }}">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="available_time_start" class="form-control" value="08:00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="available_time_end" class="form-control" value="17:00" required>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-clinic px-4">
                        <i class="bi bi-save me-1"></i> Register Medical Specialist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
