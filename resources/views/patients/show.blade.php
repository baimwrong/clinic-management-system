@extends('layouts.app')

@section('title', $patient->full_name . ' - Medical Dossier')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner -->
    <div class="card card-clinic mb-4 border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal-subtle text-teal d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 68px; height: 68px; background-color: #ccfbf1; color: #0d9488;">
                        {{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="fw-bold mb-0 text-dark">{{ $patient->full_name }}</h3>
                            <span class="badge bg-light text-dark border font-monospace">{{ $patient->patient_code }}</span>
                        </div>
                        <div class="text-muted small mt-1">
                            <span><i class="bi bi-calendar3 me-1"></i> DOB: {{ $patient->date_of_birth->format('M d, Y') }} ({{ $patient->age }} yrs)</span>
                            <span class="mx-2">&bull;</span>
                            <span class="text-capitalize"><i class="bi bi-gender-ambiguous me-1"></i> {{ $patient->gender }}</span>
                            <span class="mx-2">&bull;</span>
                            <span><i class="bi bi-droplet-half text-danger me-1"></i> Blood: <strong>{{ $patient->blood_group ?? 'Unknown' }}</strong></span>
                            <span class="mx-2">&bull;</span>
                            <span><i class="bi bi-telephone me-1"></i> {{ $patient->phone }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('consultations.create', ['patient_id' => $patient->id]) }}" class="btn btn-clinic btn-sm">
                        <i class="bi bi-journal-plus me-1"></i> Start Consultation
                    </a>
                    <a href="{{ route('vitals.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-info btn-sm text-dark">
                        <i class="bi bi-activity me-1"></i> Log Vitals
                    </a>
                    <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar-plus me-1"></i> Book Appt
                    </a>
                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                </div>
            </div>

            <!-- Clinical Safety Warning Alert (Allergies) -->
            @if($patient->allergies)
            <div class="alert alert-danger d-flex align-items-center gap-2 mt-3 mb-0 py-2 small" role="alert">
                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger"></i>
                <div>
                    <strong>CRITICAL ALLERGY ALERT:</strong> {{ $patient->allergies }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4" id="dossierTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button">
                <i class="bi bi-person-lines-fill me-1"></i> Profile & Vitals
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="consultations-tab" data-bs-toggle="pill" data-bs-target="#consultations" type="button">
                <i class="bi bi-journal-medical me-1"></i> Consultations ({{ $patient->consultations->count() }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="prescriptions-tab" data-bs-toggle="pill" data-bs-target="#prescriptions" type="button">
                <i class="bi bi-prescription2 me-1"></i> Prescriptions ({{ $patient->prescriptions->count() }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="lab-tab" data-bs-toggle="pill" data-bs-target="#lab" type="button">
                <i class="bi bi-eyedropper me-1"></i> Lab Orders ({{ $patient->labOrders->count() }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="billing-tab" data-bs-toggle="pill" data-bs-target="#billing" type="button">
                <i class="bi bi-receipt me-1"></i> Invoices ({{ $patient->invoices->count() }})
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="dossierTabContent">
        <!-- 1. Overview & Vitals -->
        <div class="tab-pane fade show active" id="overview">
            <div class="row g-4 mb-4">
                <!-- Latest Vitals Panel -->
                <div class="col-lg-8">
                    <div class="card card-clinic h-100">
                        <div class="card-header-clinic">
                            <span><i class="bi bi-activity text-info me-2"></i> Current Clinical Vitals</span>
                            <a href="{{ route('vitals.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-outline-info text-dark py-0">New Measurement</a>
                        </div>
                        <div class="card-body p-4">
                            @if($patient->vitalSigns->isNotEmpty())
                                @php $latestVital = $patient->vitalSigns->first(); @endphp
                                <div class="text-muted small mb-3">
                                    Last measured: <strong>{{ $latestVital->recorded_at->format('M d, Y h:i A') }}</strong>
                                    by {{ $latestVital->recordedByUser->name ?? 'Staff' }}
                                </div>
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-3">
                                        <div class="vital-display-item">
                                            <div class="vital-val text-primary">{{ $latestVital->blood_pressure ?? '&mdash;' }}</div>
                                            <div class="vital-lbl">Blood Pressure</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="vital-display-item">
                                            <div class="vital-val text-danger">{{ $latestVital->pulse_rate ? $latestVital->pulse_rate . ' bpm' : '&mdash;' }}</div>
                                            <div class="vital-lbl">Pulse / Heart Rate</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="vital-display-item">
                                            <div class="vital-val text-warning">{{ $latestVital->temperature ? $latestVital->temperature . ' °C' : '&mdash;' }}</div>
                                            <div class="vital-lbl">Body Temp</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="vital-display-item">
                                            <div class="vital-val text-info">{{ $latestVital->oxygen_saturation ? $latestVital->oxygen_saturation . '%' : '&mdash;' }}</div>
                                            <div class="vital-lbl">Oxygen SpO2</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <div class="vital-display-item">
                                            <div class="vital-val">{{ $latestVital->weight_kg ? $latestVital->weight_kg . ' kg' : '&mdash;' }}</div>
                                            <div class="vital-lbl">Weight</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <div class="vital-display-item">
                                            <div class="vital-val">{{ $latestVital->height_cm ? $latestVital->height_cm . ' cm' : '&mdash;' }}</div>
                                            <div class="vital-lbl">Height</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <div class="vital-display-item">
                                            <div class="vital-val text-success">{{ $latestVital->bmi ?? '&mdash;' }}</div>
                                            <div class="vital-lbl">Calculated BMI</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-activity fs-1 d-block mb-2 text-secondary"></i>
                                    No vital measurements recorded yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact & Demographics Card -->
                <div class="col-lg-4">
                    <div class="card card-clinic h-100">
                        <div class="card-header-clinic">
                            <span><i class="bi bi-shield-check text-secondary me-2"></i> Background Information</span>
                        </div>
                        <div class="card-body p-3 small">
                            <div class="mb-3">
                                <span class="text-muted d-block">Chronic Illnesses:</span>
                                <span class="fw-semibold">{{ $patient->chronic_diseases ?? 'None declared' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted d-block">Emergency Contact:</span>
                                <span class="fw-semibold">{{ $patient->emergency_contact_name ?? 'N/A' }}</span>
                                @if($patient->emergency_contact_phone)
                                    <div>{{ $patient->emergency_contact_relation }} &bull; {{ $patient->emergency_contact_phone }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <span class="text-muted d-block">Insurance:</span>
                                <span class="fw-semibold">{{ $patient->insurance_provider ?? 'Self-pay' }}</span>
                                @if($patient->insurance_policy_number)
                                    <div>Policy: <code>{{ $patient->insurance_policy_number }}</code></div>
                                @endif
                            </div>
                            <div>
                                <span class="text-muted d-block">Residential Address:</span>
                                <span>{{ $patient->address ?? 'Not specified' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Consultations Tab -->
        <div class="tab-pane fade" id="consultations">
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-journal-medical text-primary me-2"></i> Clinical Consultation History</span>
                    <a href="{{ route('consultations.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-clinic py-0">New Encounter</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clinic mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Encounter #</th>
                                    <th>Attending Doctor</th>
                                    <th>Chief Complaint</th>
                                    <th>Primary Diagnosis</th>
                                    <th>ICD-10</th>
                                    <th class="text-end">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patient->consultations as $cns)
                                    <tr>
                                        <td>{{ $cns->consultation_date->format('M d, Y') }}</td>
                                        <td class="font-monospace fw-semibold">{{ $cns->consultation_number }}</td>
                                        <td>{{ $cns->doctor->name }}</td>
                                        <td class="text-truncate" style="max-width: 200px;">{{ $cns->chief_complaint }}</td>
                                        <td class="fw-semibold text-primary">{{ $cns->primary_diagnosis }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $cns->icd10_code ?? '&mdash;' }}</span></td>
                                        <td class="text-end">
                                            <a href="{{ route('consultations.show', $cns) }}" class="btn btn-sm btn-outline-secondary py-0">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No consultations recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Prescriptions Tab -->
        <div class="tab-pane fade" id="prescriptions">
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-prescription2 text-success me-2"></i> Prescription Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clinic mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Prescription #</th>
                                    <th>Prescribed By</th>
                                    <th>Medications</th>
                                    <th>Dispensing Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patient->prescriptions as $rx)
                                    <tr>
                                        <td>{{ $rx->created_at->format('M d, Y') }}</td>
                                        <td class="font-monospace fw-semibold">{{ $rx->prescription_number }}</td>
                                        <td>{{ $rx->doctor->name }}</td>
                                        <td>
                                            @foreach($rx->items as $it)
                                                <div class="small">
                                                    &bull; <strong>{{ $it->medicine->name }}</strong>: {{ $it->dosage }}, {{ $it->frequency }} ({{ $it->quantity_prescribed }} units)
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge {{ $rx->status_badge_class }}">
                                                {{ ucfirst(str_replace('_', ' ', $rx->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('pharmacy.prescriptions.show', $rx) }}" class="btn btn-sm btn-outline-secondary py-0">Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No prescriptions issued for this patient.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Lab Orders Tab -->
        <div class="tab-pane fade" id="lab">
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-eyedropper text-info me-2"></i> Laboratory Tests & Results</span>
                    <a href="{{ route('lab.orders.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-outline-info text-dark py-0">Order Lab Test</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clinic mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Test Name</th>
                                    <th>Category</th>
                                    <th>Result Flag</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patient->labOrders as $lbo)
                                    <tr>
                                        <td class="font-monospace fw-semibold">{{ $lbo->order_number }}</td>
                                        <td>{{ $lbo->order_date->format('M d, Y') }}</td>
                                        <td class="fw-semibold text-dark">{{ $lbo->labTest->name }}</td>
                                        <td>{{ $lbo->labTest->category }}</td>
                                        <td>
                                            @if($lbo->result_flag)
                                                <span class="badge {{ $lbo->flag_badge_class }}">
                                                    {{ ucfirst($lbo->result_flag) }}
                                                </span>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $lbo->status_badge_class }}">
                                                {{ ucfirst(str_replace('_', ' ', $lbo->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('lab.orders.show', $lbo) }}" class="btn btn-sm btn-outline-secondary py-0">Inspect</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No laboratory orders recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Invoices Tab -->
        <div class="tab-pane fade" id="billing">
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-receipt text-success me-2"></i> Billing History</span>
                    <a href="{{ route('billing.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-outline-success py-0">New Invoice</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clinic mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Balance Due</th>
                                    <th>Payment Status</th>
                                    <th class="text-end">Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patient->invoices as $inv)
                                    <tr>
                                        <td class="font-monospace fw-semibold">{{ $inv->invoice_number }}</td>
                                        <td>{{ $inv->invoice_date->format('M d, Y') }}</td>
                                        <td class="fw-bold">${{ number_format($inv->total_amount, 2) }}</td>
                                        <td class="text-success">${{ number_format($inv->paid_amount, 2) }}</td>
                                        <td class="text-danger fw-semibold">${{ number_format($inv->balance_due, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $inv->payment_status_badge_class }}">
                                                {{ ucfirst(str_replace('_', ' ', $inv->payment_status)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('billing.show', $inv) }}" class="btn btn-sm btn-outline-secondary py-0">View</a>
                                            <a href="{{ route('billing.print', $inv) }}" target="_blank" class="btn btn-sm btn-light border py-0">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No invoices generated for this patient.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
