@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('appointments.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Appointments Queue
            </a>
            <h3 class="fw-bold mb-0">Book New Patient Appointment</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <!-- Patient Selection -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select select2" required>
                            <option value="">Select registered patient...</option>
                            @foreach($patients as $pat)
                                <option value="{{ $pat->id }}" {{ (old('patient_id', $selectedPatient?->id) == $pat->id) ? 'selected' : '' }}>
                                    {{ $pat->full_name }} ({{ $pat->patient_code }} &bull; {{ $pat->phone }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">
                            Can't find the patient? <a href="{{ route('patients.create') }}" target="_blank">Register a new patient first</a>.
                        </div>
                    </div>

                    <!-- Doctor Selection -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Attending Doctor <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select" required>
                            <option value="">Select medical specialist...</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }} &bull; {{ $doc->specialization }} (Fee: ${{ number_format($doc->consultation_fee, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date & Time -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Appointment Date <span class="text-danger">*</span></label>
                        <input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Appointment Time Slot <span class="text-danger">*</span></label>
                        <input type="time" name="appointment_time" class="form-control" value="{{ old('appointment_time', '09:00') }}" required>
                    </div>

                    <!-- Type & Priority -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Appointment Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="consultation" {{ old('type') == 'consultation' ? 'selected' : '' }}>General Consultation</option>
                            <option value="routine" {{ old('type') == 'routine' ? 'selected' : '' }}>Routine Checkup</option>
                            <option value="follow_up" {{ old('type') == 'follow_up' ? 'selected' : '' }}>Follow-up Visit</option>
                            <option value="emergency" {{ old('type') == 'emergency' ? 'selected' : '' }}>Urgent / Emergency Triage</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Triage Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal / Standard Priority</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent Priority</option>
                            <option value="emergency" {{ old('priority') == 'emergency' ? 'selected' : '' }}>Emergency Priority</option>
                        </select>
                    </div>

                    <!-- Reason -->
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Reason for Consultation / Presenting Complaint</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Persistent dry cough and intermittent low-grade fever for 4 days...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Internal Administrative Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Patient requests morning slot; wheelchair assistance required...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-calendar-check me-1"></i> Confirm & Book Appointment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
