@extends('layouts.app')

@section('title', 'Consultation ' . $consultation->consultation_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4 no-print">
        <div>
            <a href="{{ route('consultations.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Consultations Archive
            </a>
            <h3 class="fw-bold mb-0">Clinical Encounter Record: {{ $consultation->consultation_number }}</h3>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Print Encounter Record
            </button>
            <a href="{{ route('patients.show', $consultation->patient) }}" class="btn btn-clinic">
                <i class="bi bi-person-vcard me-1"></i> Patient Dossier
            </a>
        </div>
    </div>

    <!-- Printable Medical Record Sheet -->
    <div class="card card-clinic border-0 shadow-sm p-4 mb-4">
        <!-- Clinic & Encounter Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-hospital fs-2 text-teal" style="color: #0d9488;"></i>
                    <h4 class="fw-bold mb-0">Clinic Management System</h4>
                </div>
                <div class="text-muted small">Electronic Medical Record &bull; Official Clinical Documentation</div>
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark border font-monospace fs-6 mb-1">{{ $consultation->consultation_number }}</span>
                <div class="text-muted small">Date: {{ $consultation->consultation_date->format('F j, Y h:i A') }}</div>
            </div>
        </div>

        <!-- Patient & Doctor Summary -->
        <div class="row g-3 p-3 bg-light rounded mb-4">
            <div class="col-md-6 border-end">
                <span class="text-muted small text-uppercase fw-semibold d-block">Patient Information</span>
                <span class="fs-5 fw-bold text-dark">{{ $consultation->patient->full_name }}</span>
                <div class="small text-muted">
                    Code: <strong>{{ $consultation->patient->patient_code }}</strong> &bull;
                    Age: {{ $consultation->patient->age }} yrs ({{ ucfirst($consultation->patient->gender) }}) &bull;
                    Blood: <strong>{{ $consultation->patient->blood_group ?? 'Unknown' }}</strong>
                </div>
                <div class="small text-muted">Phone: {{ $consultation->patient->phone }}</div>
            </div>
            <div class="col-md-6 ps-md-4">
                <span class="text-muted small text-uppercase fw-semibold d-block">Attending Physician</span>
                <span class="fs-5 fw-bold text-dark">{{ $consultation->doctor->name }}</span>
                <div class="small text-muted">
                    Specialization: <strong>{{ $consultation->doctor->specialization }}</strong>
                </div>
                <div class="small text-muted">License: {{ $consultation->doctor->license_number }} &bull; Room: {{ $consultation->doctor->room_number ?? '101' }}</div>
            </div>
        </div>

        <!-- Recorded Vitals at Encounter -->
        @if($consultation->vitalSigns->isNotEmpty())
        <div class="mb-4">
            <h6 class="fw-bold text-dark text-uppercase small border-bottom pb-2">
                <i class="bi bi-activity text-info me-1"></i> Recorded Vital Signs at Encounter
            </h6>
            @php $v = $consultation->vitalSigns->first(); @endphp
            <div class="row g-2 text-center">
                <div class="col-3 col-md-2">
                    <div class="p-2 border rounded bg-white">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Blood Pressure</small>
                        <strong class="text-primary">{{ $v->blood_pressure ?? '&mdash;' }}</strong>
                    </div>
                </div>
                <div class="col-3 col-md-2">
                    <div class="p-2 border rounded bg-white">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Pulse</small>
                        <strong class="text-danger">{{ $v->pulse_rate ? $v->pulse_rate . ' bpm' : '&mdash;' }}</strong>
                    </div>
                </div>
                <div class="col-3 col-md-2">
                    <div class="p-2 border rounded bg-white">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Temp</small>
                        <strong class="text-warning">{{ $v->temperature ? $v->temperature . ' °C' : '&mdash;' }}</strong>
                    </div>
                </div>
                <div class="col-3 col-md-2">
                    <div class="p-2 border rounded bg-white">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">SpO2</small>
                        <strong class="text-info">{{ $v->oxygen_saturation ? $v->oxygen_saturation . '%' : '&mdash;' }}</strong>
                    </div>
                </div>
                <div class="col-3 col-md-2">
                    <div class="p-2 border rounded bg-white">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Weight</small>
                        <strong>{{ $v->weight_kg ? $v->weight_kg . ' kg' : '&mdash;' }}</strong>
                    </div>
                </div>
                <div class="col-3 col-md-2">
                    <div class="p-2 border rounded bg-white">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">BMI</small>
                        <strong class="text-success">{{ $v->bmi ?? '&mdash;' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Subjective & Objective Examination -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark text-uppercase small border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse text-primary me-1"></i> Clinical Evaluation
            </h6>
            <div class="row g-3">
                <div class="col-md-12">
                    <span class="text-muted small fw-semibold d-block">Chief Complaint:</span>
                    <p class="mb-2 text-dark">{{ $consultation->chief_complaint }}</p>
                </div>
                @if($consultation->history_of_present_illness)
                <div class="col-md-12">
                    <span class="text-muted small fw-semibold d-block">History of Present Illness (HPI):</span>
                    <p class="mb-2 text-dark">{{ $consultation->history_of_present_illness }}</p>
                </div>
                @endif
                @if($consultation->physical_examination)
                <div class="col-md-12">
                    <span class="text-muted small fw-semibold d-block">Physical Examination Findings:</span>
                    <p class="mb-2 text-dark">{{ $consultation->physical_examination }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Assessment / Diagnoses -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark text-uppercase small border-bottom pb-2">
                <i class="bi bi-bullseye text-danger me-1"></i> Diagnostic Assessment
            </h6>
            <div class="row g-3">
                <div class="col-md-8">
                    <span class="text-muted small fw-semibold d-block">Primary Diagnosis:</span>
                    <h5 class="fw-bold text-primary mb-0">{{ $consultation->primary_diagnosis }}</h5>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small fw-semibold d-block">ICD-10 Code:</span>
                    <span class="badge bg-light text-dark border font-monospace fs-6">{{ $consultation->icd10_code ?? 'Unspecified' }}</span>
                </div>
                @if($consultation->secondary_diagnosis)
                <div class="col-12">
                    <span class="text-muted small fw-semibold d-block">Secondary Diagnosis:</span>
                    <p class="mb-0 text-dark">{{ $consultation->secondary_diagnosis }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Prescription / Medications -->
        @if($consultation->prescription && $consultation->prescription->items->isNotEmpty())
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <h6 class="fw-bold text-dark text-uppercase small mb-0">
                    <i class="bi bi-prescription2 text-success me-1"></i> Prescribed Medications (Rx)
                </h6>
                <span class="badge {{ $consultation->prescription->status_badge_class }}">
                    {{ ucfirst(str_replace('_', ' ', $consultation->prescription->status)) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medication Name</th>
                            <th>Dosage</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Quantity Prescribed</th>
                            <th>Special Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consultation->prescription->items as $item)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $item->medicine->name }}</td>
                                <td>{{ $item->dosage }}</td>
                                <td>{{ $item->frequency }}</td>
                                <td>{{ $item->duration_days }} days</td>
                                <td class="fw-bold">{{ $item->quantity_prescribed }} {{ $item->medicine->unit }}s</td>
                                <td class="small">{{ $item->instructions ?? '&mdash;' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Treatment Plan & Notes -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark text-uppercase small border-bottom pb-2">
                <i class="bi bi-journal-check text-secondary me-1"></i> Plan of Care & Orders
            </h6>
            <div class="row g-3">
                @if($consultation->treatment_plan)
                <div class="col-12">
                    <span class="text-muted small fw-semibold d-block">Treatment Plan:</span>
                    <p class="mb-2 text-dark">{{ $consultation->treatment_plan }}</p>
                </div>
                @endif
                @if($consultation->follow_up_date)
                <div class="col-md-6">
                    <span class="text-muted small fw-semibold d-block">Recommended Follow-up:</span>
                    <span class="fw-bold text-primary">{{ $consultation->follow_up_date->format('l, F j, Y') }}</span>
                </div>
                @endif
                @if($consultation->doctor_notes)
                <div class="col-12">
                    <span class="text-muted small fw-semibold d-block">Physician's Remarks:</span>
                    <p class="mb-0 text-muted small">{{ $consultation->doctor_notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Physician Signature -->
        <div class="row pt-4 mt-4 border-top">
            <div class="col-6">
                <div class="small text-muted">Document Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
                <div class="small text-muted">Clinic Management System &bull; EMR Station</div>
            </div>
            <div class="col-6 text-end">
                <div class="d-inline-block text-center" style="min-width: 220px;">
                    <div class="border-bottom border-dark pb-4 mb-1"></div>
                    <div class="fw-bold text-dark">{{ $consultation->doctor->name }}</div>
                    <div class="small text-muted">{{ $consultation->doctor->qualification ?? 'Medical Officer' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
