@extends('layouts.app')

@section('title', 'Appointment Management')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Appointment Management & Queue</h3>
            <p class="text-muted small mb-0">Organize clinic slots, schedule consultations, and manage real-time patient flow.</p>
        </div>
        <div>
            <a href="{{ route('appointments.create') }}" class="btn btn-clinic">
                <i class="bi bi-calendar-plus me-1"></i> Book New Appointment
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('appointments.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Appointment Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach(['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled', 'no_show'] as $st)
                            <option value="{{ $st }}" {{ $status == $st ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Attending Doctor</label>
                    <select name="doctor_id" class="form-select">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ $doctorId == $doc->id ? 'selected' : '' }}>
                                {{ $doc->name }} ({{ $doc->specialization }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Filter Queue</button>
                    @if($date || $status || $doctorId)
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-calendar2-check text-primary me-2"></i> Scheduled Appointments ({{ $appointments->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Appt #</th>
                        <th>Date & Time</th>
                        <th>Patient Name</th>
                        <th>Attending Doctor</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="text-end">Queue Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $apt->appointment_number }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $apt->appointment_date->format('M d, Y') }}</div>
                                <div class="text-muted small">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}</div>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $apt->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $apt->patient->full_name }}
                                </a>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $apt->patient->patient_code }}</div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $apt->doctor->name }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $apt->doctor->specialization }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border text-capitalize">{{ str_replace('_', ' ', $apt->type) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $apt->priority_badge_class }}">{{ ucfirst($apt->priority) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $apt->status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $apt->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if($apt->status === 'scheduled')
                                        <form action="{{ route('appointments.status', $apt) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="checked_in">
                                            <button type="submit" class="btn btn-sm btn-outline-info py-0 px-2" title="Check-In Patient">
                                                Check In
                                            </button>
                                        </form>
                                    @elseif($apt->status === 'checked_in')
                                        <a href="{{ route('consultations.create', ['appointment_id' => $apt->id]) }}" class="btn btn-sm btn-clinic py-0 px-2">
                                            Start EMR
                                        </a>
                                    @endif

                                    <a href="{{ route('appointments.show', $apt) }}" class="btn btn-sm btn-light border py-0 px-2" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                                No appointments found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appointments->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
