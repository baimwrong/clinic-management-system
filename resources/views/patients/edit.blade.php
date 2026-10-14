@extends('layouts.app')

@section('title', 'Edit Patient - ' . $patient->full_name)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Patient Dossier
            </a>
            <h3 class="fw-bold mb-0">Edit Patient Record: {{ $patient->full_name }}</h3>
        </div>
        <div>
            <span class="badge bg-light text-dark border font-monospace px-3 py-2 fs-6">
                {{ $patient->patient_code }}
            </span>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('patients.update', $patient) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Demographics -->
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-person-vcard me-2"></i> 1. Demographic & Identification
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $patient->first_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $patient->last_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $patient->date_of_birth->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Biological Gender <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Contact Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">National Identity Number / Passport</label>
                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $patient->national_id) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Residential Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $patient->address) }}">
                    </div>
                </div>

                <hr class="my-4">

                <!-- Clinical Risk Flags -->
                <h5 class="fw-bold text-danger mb-3">
                    <i class="bi bi-shield-exclamation me-2"></i> 2. Clinical Risk Flags & Allergies
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-danger">Known Allergies</label>
                        <textarea name="allergies" class="form-control border-danger border-opacity-50" rows="2">{{ old('allergies', $patient->allergies) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Chronic Medical Conditions</label>
                        <textarea name="chronic_diseases" class="form-control" rows="2">{{ old('chronic_diseases', $patient->chronic_diseases) }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Emergency Contact & Insurance -->
                <h5 class="fw-bold text-info mb-3">
                    <i class="bi bi-telephone-plus me-2"></i> 3. Emergency Contact & Insurance Details
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Relationship</label>
                        <input type="text" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation', $patient->emergency_contact_relation) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Emergency Phone</label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Insurance Provider</label>
                        <input type="text" name="insurance_provider" class="form-control" value="{{ old('insurance_provider', $patient->insurance_provider) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Insurance Policy Number</label>
                        <input type="text" name="insurance_policy_number" class="form-control" value="{{ old('insurance_policy_number', $patient->insurance_policy_number) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Additional Medical / Intake Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $patient->notes) }}</textarea>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-clinic px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Patient Information
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
