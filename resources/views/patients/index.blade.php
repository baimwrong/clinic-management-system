@extends('layouts.app')

@section('title', 'Patient Dossier Index')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Patient Dossier Registry</h3>
            <p class="text-muted small mb-0">Browse, filter, and manage medical profiles, clinical history, and patient demographics.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.create') }}" class="btn btn-clinic">
                <i class="bi bi-person-plus me-1"></i> Register New Patient
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-warning text-dark">
                <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
            </a>
            @endif
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('patients.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by patient code, name, phone, or national ID..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Filter</button>
                    @if($search)
                        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Patients List Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-people text-primary me-2"></i> Registered Patients ({{ $patients->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Patient Code</th>
                        <th>Full Name</th>
                        <th>Age / Gender</th>
                        <th>Blood Group</th>
                        <th>Contact Phone</th>
                        <th>National ID</th>
                        <th>Registration Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $patient->patient_code }}</span>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $patient->full_name }}
                                </a>
                                @if($patient->allergies)
                                    <div class="small text-danger" title="Allergies: {{ $patient->allergies }}">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ Str::limit($patient->allergies, 25) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{ $patient->age }} yrs &bull; <span class="text-capitalize">{{ $patient->gender }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger">
                                    {{ $patient->blood_group ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>{{ $patient->phone }}</td>
                            <td class="font-monospace small text-muted">{{ $patient->national_id ?? '&mdash;' }}</td>
                            <td class="small text-secondary">{{ $patient->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Options
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('patients.show', $patient) }}">
                                                <i class="bi bi-folder2-open text-primary me-2"></i> View Medical Dossier
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('consultations.create', ['patient_id' => $patient->id]) }}">
                                                <i class="bi bi-journal-plus text-success me-2"></i> New Consultation
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('vitals.create', ['patient_id' => $patient->id]) }}">
                                                <i class="bi bi-activity text-info me-2"></i> Record Vitals
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}">
                                                <i class="bi bi-calendar-plus text-warning me-2"></i> Book Appointment
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('patients.edit', $patient) }}">
                                                <i class="bi bi-pencil me-2"></i> Edit Patient Info
                                            </a>
                                        </li>
                                        @if(auth()->user()->isAdmin())
                                        <li>
                                            <form action="{{ route('patients.destroy', $patient) }}" method="POST" onsubmit="return confirm('Archive this patient record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i> Remove Record
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-2 d-block mb-2 text-secondary"></i>
                                No patients found matching your query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patients->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $patients->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
