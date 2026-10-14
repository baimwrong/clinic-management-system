@extends('layouts.app')

@section('title', 'Register New Patient')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('patients.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Patient Registry
            </a>
            <h3 class="fw-bold mb-0">Register New Patient</h3>
        </div>
        <div>
            <span class="badge bg-light text-dark border font-monospace px-3 py-2 fs-6">
                Assigned Code: {{ $suggestedCode }}
            </span>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('patients.store') }}" method="POST">
                @csrf

                <!-- Section: Personal Demographics -->
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-person-vcard me-2"></i> 1. Demographic & Identification
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required placeholder="e.g. Eleanor">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required placeholder="e.g. Vance">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Biological Gender <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="">Choose gender...</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="Unknown" selected>Unknown / Unspecified</option>
                            <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Contact Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required placeholder="e.g. +1-555-0199">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="e.g. patient@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">National Identity Number / Passport</label>
                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id') }}" placeholder="e.g. NID-44882200">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Residential Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Street, City, Postal Code">
                    </div>
                </div>

                <hr class="my-4">

                <!-- Section: Clinical Alert Flags & Medical History -->
                <h5 class="fw-bold text-danger mb-3">
                    <i class="bi bi-shield-exclamation me-2"></i> 2. Clinical Risk Flags & Allergies
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-danger">Known Allergies</label>
                        <textarea name="allergies" class="form-control border-danger border-opacity-50" rows="2" placeholder="e.g. Penicillin, Peanuts, Latex, Aspirin...">{{ old('allergies') }}</textarea>
                        <div class="form-text small">Crucial for prescription safety warnings.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Chronic Medical Conditions</label>
                        <textarea name="chronic_diseases" class="form-control" rows="2" placeholder="e.g. Type II Diabetes, Hypertension, Asthma...">{{ old('chronic_diseases') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Section: Emergency Contact & Insurance -->
                <h5 class="fw-bold text-info mb-3">
                    <i class="bi bi-telephone-plus me-2"></i> 3. Emergency Contact & Insurance Details
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}" placeholder="Next of kin full name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Relationship</label>
                        <input type="text" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation') }}" placeholder="e.g. Spouse, Parent, Sibling">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Emergency Phone</label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}" placeholder="+1-555-0988">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Insurance Provider</label>
                        <input type="text" name="insurance_provider" class="form-control" value="{{ old('insurance_provider') }}" placeholder="e.g. BlueCross, Aetna, Cigna">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Insurance Policy Number</label>
                        <input type="text" name="insurance_policy_number" class="form-control" value="{{ old('insurance_policy_number') }}" placeholder="e.g. POL-998877">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Additional Medical / Intake Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any specific instructions or background...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-clinic px-4">
                        <i class="bi bi-save me-1"></i> Save & Open Patient Dossier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
