@extends('layouts.app')

@section('title', 'Clinical Encounter - ' . $patient->full_name)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Patient Dossier
            </a>
            <h3 class="fw-bold mb-0">Record Clinical Consultation (EMR)</h3>
        </div>
        <div>
            @if($appointment)
                <span class="badge bg-primary px-3 py-2 fs-6">
                    Linked to Appointment #{{ $appointment->appointment_number }}
                </span>
            @endif
        </div>
    </div>

    <!-- Patient Header Card -->
    <div class="card card-clinic mb-4 border-0 shadow-sm bg-light">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <span class="fs-5 fw-bold text-dark me-2">{{ $patient->full_name }}</span>
                    <span class="badge bg-secondary font-monospace me-2">{{ $patient->patient_code }}</span>
                    <span class="small text-muted">{{ $patient->age }} yrs &bull; {{ ucfirst($patient->gender) }} &bull; Blood: <strong>{{ $patient->blood_group ?? 'Unknown' }}</strong></span>
                </div>
                <div>
                    <span class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $patient->phone }}</span>
                </div>
            </div>

            @if($patient->allergies)
                <div class="alert alert-danger py-1 px-3 mt-2 mb-0 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-octagon-fill text-danger fs-6"></i>
                    <strong>ALLERGY WARNING:</strong> {{ $patient->allergies }}
                </div>
            @endif
        </div>
    </div>

    <form action="{{ route('consultations.store') }}" method="POST">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
        @if($appointment)
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        @endif

        <div class="row g-4">
            <!-- Left Column: Encounter Info, Vitals, Clinical Notes, Diagnosis -->
            <div class="col-lg-7">
                <!-- Encounter Meta -->
                <div class="card card-clinic mb-4">
                    <div class="card-header-clinic">
                        <span><i class="bi bi-person-badge text-primary me-2"></i> 1. Encounter Metadata</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Attending Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select" required>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ (auth()->user()->doctor?->id == $doc->id || old('doctor_id', $appointment?->doctor_id) == $doc->id) ? 'selected' : '' }}>
                                            {{ $doc->name }} ({{ $doc->specialization }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Encounter Timestamp <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="consultation_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vitals Entry During Encounter -->
                <div class="card card-clinic mb-4">
                    <div class="card-header-clinic">
                        <span><i class="bi bi-activity text-info me-2"></i> 2. Vital Signs Measurement</span>
                        <small class="text-muted">Fill if measured during this session</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Systolic BP (mmHg)</label>
                                <input type="number" name="systolic_bp" id="systolic" class="form-control" placeholder="120" value="{{ old('systolic_bp', $recentVitals?->systolic_bp) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Diastolic BP (mmHg)</label>
                                <input type="number" name="diastolic_bp" id="diastolic" class="form-control" placeholder="80" value="{{ old('diastolic_bp', $recentVitals?->diastolic_bp) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Pulse Rate (bpm)</label>
                                <input type="number" name="pulse_rate" class="form-control" placeholder="72" value="{{ old('pulse_rate', $recentVitals?->pulse_rate) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Body Temp (°C)</label>
                                <input type="number" step="0.1" name="temperature" class="form-control" placeholder="36.8" value="{{ old('temperature', $recentVitals?->temperature) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">SpO2 (%)</label>
                                <input type="number" step="0.1" name="oxygen_saturation" class="form-control" placeholder="98" value="{{ old('oxygen_saturation', $recentVitals?->oxygen_saturation) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Height (cm)</label>
                                <input type="number" step="0.1" name="height_cm" id="heightInput" class="form-control" placeholder="175" value="{{ old('height_cm', $recentVitals?->height_cm) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight_kg" id="weightInput" class="form-control" placeholder="70" value="{{ old('weight_kg', $recentVitals?->weight_kg) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Computed BMI</label>
                                <input type="text" id="bmiDisplay" class="form-control bg-light" readonly placeholder="&mdash;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinical Examination & Symptoms -->
                <div class="card card-clinic mb-4">
                    <div class="card-header-clinic">
                        <span><i class="bi bi-clipboard2-pulse text-primary me-2"></i> 3. Clinical Examination & Diagnosis</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Chief Complaint <span class="text-danger">*</span></label>
                                <textarea name="chief_complaint" class="form-control" rows="2" required placeholder="Patient presents with...">{{ old('chief_complaint', $appointment?->reason) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">History of Present Illness (HPI)</label>
                                <textarea name="history_of_present_illness" class="form-control" rows="2" placeholder="Onset, duration, severity, aggravating/relieving factors...">{{ old('history_of_present_illness') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Physical Examination Findings</label>
                                <textarea name="physical_examination" class="form-control" rows="2" placeholder="HEENT, Cardiovascular, Respiratory, Abdominal, Neurological...">{{ old('physical_examination') }}</textarea>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small">Primary Diagnosis <span class="text-danger">*</span></label>
                                <input type="text" name="primary_diagnosis" class="form-control" required placeholder="e.g. Acute Bacterial Sinusitis" value="{{ old('primary_diagnosis') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">ICD-10 Code</label>
                                <input type="text" name="icd10_code" class="form-control font-monospace" placeholder="e.g. J01.0" value="{{ old('icd10_code') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Secondary Diagnoses / Comorbidities</label>
                                <input type="text" name="secondary_diagnosis" class="form-control" placeholder="e.g. Mild dehydration, Rhinitis" value="{{ old('secondary_diagnosis') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Prescription Builder & Treatment Plan -->
            <div class="col-lg-5">
                <!-- Digital Prescription Builder -->
                <div class="card card-clinic mb-4 border-success border-opacity-25">
                    <div class="card-header bg-success bg-opacity-10 py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-success">
                            <i class="bi bi-prescription2 me-2"></i> 4. Digital Prescription (Rx)
                        </span>
                        <button type="button" class="btn btn-sm btn-success" id="addMedicineBtn">
                            <i class="bi bi-plus-lg me-1"></i> Add Medication
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div id="medicineRowsContainer">
                            <!-- Dynamic Medicine Rows will appear here -->
                            <div class="medicine-row border rounded p-3 mb-3 bg-light position-relative">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-med-btn" aria-label="Close"></button>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Medication Catalog</label>
                                    <select name="medicines[0][medicine_id]" class="form-select form-select-sm" required>
                                        <option value="">Select prescribed medicine...</option>
                                        @foreach($medicines as $med)
                                            <option value="{{ $med->id }}" data-price="{{ $med->selling_price }}">
                                                {{ $med->name }} ({{ $med->dosage_form ?? $med->unit }}) - Stock: {{ $med->total_stock }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Dosage</label>
                                        <input type="text" name="medicines[0][dosage]" class="form-control form-control-sm" placeholder="e.g. 500mg" required value="500mg">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Frequency</label>
                                        <input type="text" name="medicines[0][frequency]" class="form-control form-control-sm" placeholder="e.g. 3 times daily" required value="3x daily">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Duration (Days)</label>
                                        <input type="number" name="medicines[0][duration_days]" class="form-control form-control-sm" placeholder="7" required value="7">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Quantity (Units)</label>
                                        <input type="number" name="medicines[0][quantity]" class="form-control form-control-sm" placeholder="21" required value="21">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small">Instructions for Patient</label>
                                    <input type="text" name="medicines[0][instructions]" class="form-control form-control-sm" placeholder="e.g. Take after meals with plenty of water.">
                                </div>
                            </div>
                        </div>
                        <div class="form-text small text-muted">
                            <i class="bi bi-info-circle me-1"></i> Prescribed items will automatically be routed to the Pharmacy Dispensing Queue and added to the Patient Invoice.
                        </div>
                    </div>
                </div>

                <!-- Treatment Plan & Follow Up -->
                <div class="card card-clinic mb-4">
                    <div class="card-header-clinic">
                        <span><i class="bi bi-journal-check text-secondary me-2"></i> 5. Treatment Plan & Next Steps</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Treatment Plan & Non-Pharmacological Advice</label>
                            <textarea name="treatment_plan" class="form-control" rows="3" placeholder="Rest, hydration, physical therapy, dietary recommendations...">{{ old('treatment_plan') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Confidential Clinical Notes</label>
                            <textarea name="doctor_notes" class="form-control" rows="2" placeholder="Internal remarks for medical team...">{{ old('doctor_notes') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label fw-semibold small">Recommended Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('follow_up_date') }}">
                        </div>
                    </div>
                </div>

                <!-- Final Action Box -->
                <div class="card card-clinic bg-light border-0 p-3">
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-clinic py-3 fw-bold fs-6 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Finalize Consultation & Generate Invoice
                        </button>
                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">
                            Cancel Encounter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dynamic BMI calculator
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

        if (heightInput && weightInput) {
            heightInput.addEventListener('input', updateBmi);
            weightInput.addEventListener('input', updateBmi);
            updateBmi();
        }

        // Dynamic Medicine Rows
        let rowIndex = 1;
        const container = document.getElementById('medicineRowsContainer');
        const addBtn = document.getElementById('addMedicineBtn');

        const medicineOptionsHtml = `@foreach($medicines as $med)<option value="{{ $med->id }}">{{ addslashes($med->name) }} ({{ $med->dosage_form ?? $med->unit }}) - Stock: {{ $med->total_stock }}</option>@endforeach`;

        addBtn.addEventListener('click', function () {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'medicine-row border rounded p-3 mb-3 bg-light position-relative';
            rowDiv.innerHTML = `
                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-med-btn" aria-label="Close"></button>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Medication Catalog</label>
                    <select name="medicines[${rowIndex}][medicine_id]" class="form-select form-select-sm" required>
                        <option value="">Select prescribed medicine...</option>
                        ${medicineOptionsHtml}
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small">Dosage</label>
                        <input type="text" name="medicines[${rowIndex}][dosage]" class="form-control form-control-sm" placeholder="e.g. 500mg" required value="500mg">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Frequency</label>
                        <input type="text" name="medicines[${rowIndex}][frequency]" class="form-control form-control-sm" placeholder="e.g. 2x daily" required value="2x daily">
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small">Duration (Days)</label>
                        <input type="number" name="medicines[${rowIndex}][duration_days]" class="form-control form-control-sm" placeholder="7" required value="7">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Quantity (Units)</label>
                        <input type="number" name="medicines[${rowIndex}][quantity]" class="form-control form-control-sm" placeholder="14" required value="14">
                    </div>
                </div>
                <div>
                    <label class="form-label small">Instructions for Patient</label>
                    <input type="text" name="medicines[${rowIndex}][instructions]" class="form-control form-control-sm" placeholder="e.g. Take after meals.">
                </div>
            `;
            container.appendChild(rowDiv);
            rowIndex++;
        });

        container.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-med-btn')) {
                const rows = container.querySelectorAll('.medicine-row');
                if (rows.length > 1) {
                    e.target.closest('.medicine-row').remove();
                } else {
                    alert('At least one medicine row should be kept or left unselected.');
                }
            }
        });
    });
</script>
@endpush
@endsection
