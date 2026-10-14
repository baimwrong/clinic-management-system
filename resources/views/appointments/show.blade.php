@extends('layouts.app')

@section('title', 'Appointment ' . $appointment->appointment_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('appointments.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Appointments Queue
            </a>
            <h3 class="fw-bold mb-0">Appointment {{ $appointment->appointment_number }}</h3>
        </div>
        <div class="d-flex gap-2">
            <span class="badge {{ $appointment->status_badge_class }} px-3 py-2 fs-6">
                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
            </span>
            <span class="badge {{ $appointment->priority_badge_class }} px-3 py-2 fs-6">
                {{ ucfirst($appointment->priority) }} Priority
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Appointment Card -->
        <div class="col-lg-8">
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-calendar2-event text-primary me-2"></i> Appointment Details</span>
                    @if($appointment->status === 'checked_in')
                        <a href="{{ route('consultations.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-clinic btn-sm">
                            <i class="bi bi-journal-medical me-1"></i> Start EMR Consultation
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="text-muted small">Appointment Date:</div>
                            <div class="fs-5 fw-bold text-dark">{{ $appointment->appointment_date->format('l, F j, Y') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Scheduled Time:</div>
                            <div class="fs-5 fw-bold text-dark">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Type:</div>
                            <div class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $appointment->type) }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Attending Specialist:</div>
                            <div class="fw-semibold">{{ $appointment->doctor->name }} ({{ $appointment->doctor->specialization }})</div>
                            <small class="text-muted">Office / Room: {{ $appointment->doctor->room_number ?? 'General Wing' }}</small>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Presenting Complaint / Reason:</div>
                            <div class="p-3 bg-light rounded mt-1">
                                {{ $appointment->reason ?? 'Routine clinical follow-up.' }}
                            </div>
                        </div>
                        @if($appointment->notes)
                        <div class="col-12">
                            <div class="text-muted small">Internal Notes:</div>
                            <div class="p-3 bg-light rounded mt-1 small">
                                {{ $appointment->notes }}
                            </div>
                        </div>
                        @endif

                        @if($appointment->consultation)
                        <div class="col-12">
                            <div class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-0">
                                <div>
                                    <i class="bi bi-check2-circle fs-4 me-2"></i>
                                    <strong>Consultation Completed:</strong> Encounter #{{ $appointment->consultation->consultation_number }}
                                </div>
                                <a href="{{ route('consultations.show', $appointment->consultation) }}" class="btn btn-sm btn-outline-success">
                                    View EMR Record &rarr;
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Transition Controls -->
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-toggles text-secondary me-2"></i> Update Appointment Status</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('appointments.status', $appointment) }}" method="POST" class="d-flex flex-wrap gap-2">
                        @csrf
                        @method('PATCH')
                        @if($appointment->status !== 'checked_in' && $appointment->status !== 'completed')
                            <button type="submit" name="status" value="checked_in" class="btn btn-outline-info">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Patient Checked In
                            </button>
                        @endif

                        @if($appointment->status !== 'in_consultation' && $appointment->status !== 'completed')
                            <button type="submit" name="status" value="in_consultation" class="btn btn-outline-warning text-dark">
                                <i class="bi bi-person-workspace me-1"></i> In Consultation Room
                            </button>
                        @endif

                        @if($appointment->status !== 'completed')
                            <button type="submit" name="status" value="completed" class="btn btn-outline-success">
                                <i class="bi bi-check-all me-1"></i> Mark Completed
                            </button>
                            <button type="submit" name="status" value="no_show" class="btn btn-outline-secondary">
                                <i class="bi bi-person-x me-1"></i> Mark No-Show
                            </button>
                            <button type="submit" name="status" value="cancelled" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i> Cancel Appointment
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Patient Mini Dossier Sidebar -->
        <div class="col-lg-4">
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-person text-primary me-2"></i> Patient Information</span>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle bg-teal-subtle text-teal mx-auto d-flex align-items-center justify-content-center fw-bold fs-4 mb-2" style="width: 56px; height: 56px; background-color: #ccfbf1; color: #0d9488;">
                        {{ strtoupper(substr($appointment->patient->first_name, 0, 1) . substr($appointment->patient->last_name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $appointment->patient->full_name }}</h5>
                    <p class="text-muted small mb-3">{{ $appointment->patient->patient_code }} &bull; {{ $appointment->patient->age }} yrs</p>

                    @if($appointment->patient->allergies)
                        <div class="alert alert-danger py-2 small text-start mb-3">
                            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Allergies:</strong><br>
                            {{ $appointment->patient->allergies }}
                        </div>
                    @endif

                    <div class="text-start small border-top pt-3">
                        <div class="mb-2"><strong>Phone:</strong> {{ $appointment->patient->phone }}</div>
                        <div class="mb-2"><strong>Blood Group:</strong> {{ $appointment->patient->blood_group ?? 'Unknown' }}</div>
                        <div class="mb-2"><strong>Emergency Contact:</strong> {{ $appointment->patient->emergency_contact_name ?? 'N/A' }} ({{ $appointment->patient->emergency_contact_phone ?? 'N/A' }})</div>
                    </div>

                    <a href="{{ route('patients.show', $appointment->patient) }}" class="btn btn-clinic-outline btn-sm w-100 mt-3">
                        Open Full Medical Dossier &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
