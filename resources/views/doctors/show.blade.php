@extends('layouts.app')

@section('title', $doctor->name . ' - Medical Profile')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('doctors.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Doctors Directory
            </a>
            <h3 class="fw-bold mb-0">{{ $doctor->name }}</h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
            <a href="{{ route('appointments.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-clinic">
                <i class="bi bi-calendar-plus me-1"></i> Book With This Doctor
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Column -->
        <div class="col-lg-4">
            <div class="card card-clinic text-center p-4 mb-4">
                <div class="rounded-circle bg-teal-subtle text-teal mx-auto d-flex align-items-center justify-content-center fw-bold fs-2 mb-3" style="width: 80px; height: 80px; background-color: #ccfbf1; color: #0d9488;">
                    {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                </div>
                <h4 class="fw-bold mb-1 text-dark">{{ $doctor->name }}</h4>
                <div class="badge bg-primary px-3 py-1 mb-2">{{ $doctor->specialization }}</div>
                <p class="text-muted small">{{ $doctor->qualification ?? 'Licensed Medical Officer' }}</p>

                <div class="text-start small border-top pt-3">
                    <div class="mb-2"><strong>Staff Code:</strong> <span class="font-monospace">{{ $doctor->staff_id }}</span></div>
                    <div class="mb-2"><strong>License #:</strong> <code>{{ $doctor->license_number }}</code></div>
                    <div class="mb-2"><strong>Room:</strong> {{ $doctor->room_number ?? 'N/A' }}</div>
                    <div class="mb-2"><strong>Consultation Fee:</strong> <span class="text-success fw-bold">${{ number_format($doctor->consultation_fee, 2) }}</span></div>
                    <div class="mb-2"><strong>Email:</strong> {{ $doctor->user->email }}</div>
                    @if($doctor->user->phone)
                        <div><strong>Phone:</strong> {{ $doctor->user->phone }}</div>
                    @endif
                </div>

                @if($doctor->bio)
                    <div class="text-start border-top pt-3 mt-3 small text-muted">
                        <strong>Bio:</strong>
                        <p class="mb-0 mt-1">{{ $doctor->bio }}</p>
                    </div>
                @endif
            </div>

            <!-- Schedule Card -->
            <div class="card card-clinic p-3">
                <div class="card-header bg-white border-bottom py-2">
                    <span class="fw-bold small text-dark"><i class="bi bi-clock text-primary me-2"></i> Office Hours</span>
                </div>
                <div class="card-body p-2 small">
                    <div class="mb-2">
                        <strong>Hours:</strong> {{ \Carbon\Carbon::parse($doctor->available_time_start)->format('h:i A') }} &mdash; {{ \Carbon\Carbon::parse($doctor->available_time_end)->format('h:i A') }}
                    </div>
                    <div>
                        <strong>Days:</strong>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($doctor->available_days ?? [] as $d)
                                <span class="badge bg-light text-secondary border">{{ $d }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments & Consultations History -->
        <div class="col-lg-8">
            <!-- Scheduled Queue -->
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-calendar2-check text-primary me-2"></i> Upcoming Appointments ({{ $doctor->appointments->count() }})</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-clinic mb-0">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctor->appointments as $apt)
                                <tr>
                                    <td>{{ $apt->appointment_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('patients.show', $apt->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $apt->patient->full_name }}
                                        </a>
                                    </td>
                                    <td><span class="badge {{ $apt->priority_badge_class }}">{{ ucfirst($apt->priority) }}</span></td>
                                    <td><span class="badge {{ $apt->status_badge_class }}">{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('appointments.show', $apt) }}" class="btn btn-sm btn-outline-secondary py-0">Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No appointments scheduled for this doctor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Consultations -->
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-journal-medical text-primary me-2"></i> Recent Clinical Encounters</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($doctor->consultations as $cns)
                        <a href="{{ route('consultations.show', $cns) }}" class="list-group-item list-group-item-action p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">{{ $cns->patient->full_name }}</span>
                                <small class="text-muted">{{ $cns->consultation_date->format('M d, Y h:i A') }}</small>
                            </div>
                            <div class="small text-primary mb-1">
                                <strong>Diagnosis:</strong> {{ $cns->primary_diagnosis }} (ICD-10: {{ $cns->icd10_code ?? 'N/A' }})
                            </div>
                            <div class="small text-muted">{{ $cns->chief_complaint }}</div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted">
                            No consultations recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
