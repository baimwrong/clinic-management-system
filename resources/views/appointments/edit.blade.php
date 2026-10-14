@extends('layouts.app')

@section('title', 'Edit Appointment - ' . $appointment->appointment_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('appointments.show', $appointment) }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Appointment
            </a>
            <h3 class="fw-bold mb-0">Edit Appointment: {{ $appointment->appointment_number }}</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('appointments.update', $appointment) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Patient</label>
                        <input type="text" class="form-control bg-light" value="{{ $appointment->patient->full_name }} ({{ $appointment->patient->patient_code }})" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Attending Doctor</label>
                        <input type="text" class="form-control bg-light" value="{{ $appointment->doctor->name }} ({{ $appointment->doctor->specialization }})" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Appointment Date <span class="text-danger">*</span></label>
                        <input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Time Slot <span class="text-danger">*</span></label>
                        <input type="time" name="appointment_time" class="form-control" value="{{ old('appointment_time', $appointment->appointment_time) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach(['consultation', 'routine', 'follow_up', 'emergency'] as $t)
                                <option value="{{ $t }}" {{ old('type', $appointment->type) == $t ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $t)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            @foreach(['normal', 'urgent', 'emergency'] as $p)
                                <option value="{{ $p }}" {{ old('priority', $appointment->priority) == $p ? 'selected' : '' }}>
                                    {{ ucfirst($p) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled', 'no_show'] as $st)
                                <option value="{{ $st }}" {{ old('status', $appointment->status) == $st ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $st)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Reason</label>
                        <textarea name="reason" class="form-control" rows="2">{{ old('reason', $appointment->reason) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $appointment->notes) }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Appointment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
