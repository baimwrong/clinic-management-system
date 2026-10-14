@extends('layouts.app')

@section('title', 'Electronic Medical Records (EMR)')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Clinical Consultations & EMR Records</h3>
            <p class="text-muted small mb-0">Electronic Medical Records archive, clinical documentation, and diagnostic encounters.</p>
        </div>
        <div>
            <a href="{{ route('patients.index') }}" class="btn btn-clinic">
                <i class="bi bi-journal-plus me-1"></i> Start New Clinical Encounter
            </a>
        </div>
    </div>

    <!-- Search Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('consultations.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by consultation #, diagnosis, ICD-10 code, or patient name..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Search EMR</button>
                    @if($search)
                        <a href="{{ route('consultations.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table of Consultations -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-journal-medical text-primary me-2"></i> Clinical Consultation Encounters ({{ $consultations->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Encounter #</th>
                        <th>Patient</th>
                        <th>Attending Doctor</th>
                        <th>Chief Complaint</th>
                        <th>Primary Diagnosis</th>
                        <th>ICD-10</th>
                        <th>Prescription</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $cns)
                        <tr>
                            <td class="small text-secondary">
                                {{ $cns->consultation_date->format('M d, Y') }}
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $cns->consultation_date->format('h:i A') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $cns->consultation_number }}</span>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $cns->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $cns->patient->full_name }}
                                </a>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $cns->patient->patient_code }}</div>
                            </td>
                            <td>{{ $cns->doctor->name }}</td>
                            <td class="text-truncate" style="max-width: 180px;">{{ $cns->chief_complaint }}</td>
                            <td class="fw-semibold text-primary">{{ $cns->primary_diagnosis }}</td>
                            <td>
                                @if($cns->icd10_code)
                                    <span class="badge bg-info-subtle text-info border border-info font-monospace">{{ $cns->icd10_code }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($cns->prescription)
                                    <span class="badge {{ $cns->prescription->status_badge_class }}">
                                        {{ ucfirst($cns->prescription->status) }}
                                    </span>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('consultations.show', $cns) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                    <i class="bi bi-eye me-1"></i> Review Record
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary"></i>
                                No consultation records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($consultations->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $consultations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
