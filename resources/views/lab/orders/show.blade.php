@extends('layouts.app')

@section('title', 'Lab Order ' . $labOrder->order_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('lab.orders.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Lab Orders
            </a>
            <h3 class="fw-bold mb-0">Lab Order: {{ $labOrder->order_number }}</h3>
        </div>
        <div class="d-flex gap-2">
            <span class="badge {{ $labOrder->status_badge_class }} px-3 py-2 fs-6">
                {{ ucfirst(str_replace('_', ' ', $labOrder->status)) }}
            </span>
            @if($labOrder->result_flag)
                <span class="badge {{ $labOrder->flag_badge_class }} px-3 py-2 fs-6">
                    Flag: {{ ucfirst($labOrder->result_flag) }}
                </span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Test Details -->
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-eyedropper text-info me-2"></i> Diagnostic Test Information</span>
                    @if($labOrder->status !== 'completed')
                        <button type="button" class="btn btn-clinic btn-sm" data-bs-toggle="modal" data-bs-target="#recordResultModal">
                            <i class="bi bi-pencil-square me-1"></i> Record Test Result
                        </button>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Test Name:</span>
                            <span class="fs-5 fw-bold text-dark">{{ $labOrder->labTest->name }}</span>
                            <div class="small text-muted">{{ $labOrder->labTest->category }} &bull; Code: {{ $labOrder->labTest->code }}</div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">Order Timestamp:</span>
                            <span class="fw-semibold">{{ $labOrder->order_date->format('M d, Y h:i A') }}</span>
                            @if($labOrder->completed_at)
                                <div class="small text-success">Completed: {{ $labOrder->completed_at->format('M d, Y h:i A') }}</div>
                            @endif
                        </div>
                        @if($labOrder->labTest->reference_range)
                        <div class="col-12">
                            <div class="p-3 bg-light rounded small">
                                <span class="fw-semibold text-secondary d-block mb-1">Standard Reference Range:</span>
                                <code>{{ $labOrder->labTest->reference_range }}</code>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Result Card -->
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-file-medical text-primary me-2"></i> Diagnostic Findings & Results</span>
                </div>
                <div class="card-body p-4">
                    @if($labOrder->status === 'completed')
                        <div class="p-3 rounded mb-3 {{ $labOrder->result_flag === 'critical' ? 'bg-danger bg-opacity-10 border border-danger' : 'bg-light' }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold fs-6">Result Value:</span>
                                <span class="badge {{ $labOrder->flag_badge_class }} fs-6">
                                    {{ ucfirst($labOrder->result_flag) }} Result
                                </span>
                            </div>
                            <div class="fs-5 fw-bold font-monospace text-dark mb-0">
                                {{ $labOrder->result_value }}
                            </div>
                        </div>

                        @if($labOrder->technician_notes)
                            <div class="small text-muted">
                                <strong>Technician Remarks / Interpretation:</strong>
                                <p class="mb-0 mt-1">{{ $labOrder->technician_notes }}</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-hourglass-split fs-2 d-block mb-2 text-warning"></i>
                            Diagnostic analysis in progress. Click "Record Test Result" once processed.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Patient & Doctor Info -->
        <div class="col-lg-4">
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-person text-primary me-2"></i> Patient Information</span>
                </div>
                <div class="card-body p-3 small">
                    <div class="mb-2"><strong>Patient:</strong> {{ $labOrder->patient->full_name }}</div>
                    <div class="mb-2"><strong>Patient Code:</strong> {{ $labOrder->patient->patient_code }}</div>
                    <div class="mb-2"><strong>Age/Gender:</strong> {{ $labOrder->patient->age }} yrs, {{ ucfirst($labOrder->patient->gender) }}</div>
                    <div class="mb-3"><strong>Phone:</strong> {{ $labOrder->patient->phone }}</div>
                    <a href="{{ route('patients.show', $labOrder->patient) }}" class="btn btn-outline-primary btn-sm w-100">
                        View Patient Dossier &rarr;
                    </a>
                </div>
            </div>

            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-person-badge text-secondary me-2"></i> Ordering Physician</span>
                </div>
                <div class="card-body p-3 small">
                    <div class="mb-1"><strong>Doctor:</strong> {{ $labOrder->doctor->name }}</div>
                    <div class="text-muted">{{ $labOrder->doctor->specialization }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Result -->
<div class="modal fade" id="recordResultModal" tabindex="-1" aria-labelledby="recordResultModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lab.orders.results.store', $labOrder) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="recordResultModalLabel">
                        <i class="bi bi-eyedropper text-info me-2"></i> Record Diagnostic Result
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Quantitative / Qualitative Result Value <span class="text-danger">*</span></label>
                        <textarea name="result_value" class="form-control" rows="3" required placeholder="e.g. Hemoglobin: 14.2 g/dL, WBC: 7,500 /mcL, Platelets: 250,000 /mcL...">{{ old('result_value', $labOrder->result_value) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Result Clinical Flag <span class="text-danger">*</span></label>
                        <select name="result_flag" class="form-select" required>
                            <option value="normal" {{ old('result_flag', $labOrder->result_flag) == 'normal' ? 'selected' : '' }}>Normal (Within reference parameters)</option>
                            <option value="abnormal" {{ old('result_flag', $labOrder->result_flag) == 'abnormal' ? 'selected' : '' }}>Abnormal (Outside normal range)</option>
                            <option value="critical" {{ old('result_flag', $labOrder->result_flag) == 'critical' ? 'selected' : '' }}>Critical (Requires urgent clinical alert)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label small fw-semibold">Technician Observations / Method Notes</label>
                        <textarea name="technician_notes" class="form-control" rows="2" placeholder="e.g. Analyzed on Automated Hematology Analyzer Sysmex XN-550">{{ old('technician_notes', $labOrder->technician_notes) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-clinic">
                        <i class="bi bi-check2-circle me-1"></i> Save & Certify Result
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
