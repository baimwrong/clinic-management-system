@extends('layouts.app')

@section('title', 'Record Vital Signs')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('vitals.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Vitals Log
            </a>
            <h3 class="fw-bold mb-0">Record Clinical Vital Signs</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('vitals.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select patient...</option>
                            @foreach($patients as $pat)
                                <option value="{{ $pat->id }}" {{ (old('patient_id', $patient?->id) == $pat->id) ? 'selected' : '' }}>
                                    {{ $pat->full_name }} ({{ $pat->patient_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Measurement Timestamp <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="recorded_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <!-- Blood Pressure -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Systolic BP (mmHg)</label>
                        <input type="number" name="systolic_bp" class="form-control" placeholder="120" value="{{ old('systolic_bp') }}">
                        <div class="form-text small">Normal: 90 - 120</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Diastolic BP (mmHg)</label>
                        <input type="number" name="diastolic_bp" class="form-control" placeholder="80" value="{{ old('diastolic_bp') }}">
                        <div class="form-text small">Normal: 60 - 80</div>
                    </div>

                    <!-- Pulse & Temp -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Pulse / Heart Rate (bpm)</label>
                        <input type="number" name="pulse_rate" class="form-control" placeholder="72" value="{{ old('pulse_rate') }}">
                        <div class="form-text small">Normal: 60 - 100 bpm</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Body Temperature (°C)</label>
                        <input type="number" step="0.1" name="temperature" class="form-control" placeholder="36.8" value="{{ old('temperature') }}">
                        <div class="form-text small">Normal: 36.5 - 37.5 °C</div>
                    </div>

                    <!-- Respiratory & Oxygen -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Respiratory Rate (breaths/min)</label>
                        <input type="number" name="respiratory_rate" class="form-control" placeholder="16" value="{{ old('respiratory_rate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Oxygen SpO2 (%)</label>
                        <input type="number" step="0.1" name="oxygen_saturation" class="form-control" placeholder="98" value="{{ old('oxygen_saturation') }}">
                        <div class="form-text small">Normal: 95% - 100%</div>
                    </div>

                    <!-- Height & Weight & BMI -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Height (cm)</label>
                        <input type="number" step="0.1" name="height_cm" id="heightInput" class="form-control" placeholder="175" value="{{ old('height_cm') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight_kg" id="weightInput" class="form-control" placeholder="70" value="{{ old('weight_kg') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Computed BMI</label>
                        <input type="text" id="bmiDisplay" class="form-control bg-light" readonly placeholder="Calculates dynamically">
                    </div>

                    <div class="col-md-9">
                        <label class="form-label fw-semibold small">Clinical Triage Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Patient rested 5 mins prior to blood pressure reading..." value="{{ old('notes') }}">
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('vitals.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Vital Signs
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const heightInput = document.getElementById('heightInput');
        const weightInput = document.getElementById('weightInput');
        const bmiDisplay = document.getElementById('bmiDisplay');

        function updateBmi() {
            const h = parseFloat(heightInput.value);
            const w = parseFloat(weightInput.value);
            if (h > 0 && w > 0) {
                const heightM = h / 100;
                const bmi = (w / (heightM * heightM)).toFixed(1);
                bmiDisplay.value = bmi;
            } else {
                bmiDisplay.value = '';
            }
        }

        heightInput.addEventListener('input', updateBmi);
        weightInput.addEventListener('input', updateBmi);
    });
</script>
@endpush
@endsection
