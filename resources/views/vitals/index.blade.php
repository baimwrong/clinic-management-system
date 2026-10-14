@extends('layouts.app')

@section('title', 'Triage & Vital Signs')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Nursing Triage & Vital Signs Log</h3>
            <p class="text-muted small mb-0">Record physiological measurements, compute BMI, and track patient vitals history.</p>
        </div>
        <div>
            <a href="{{ route('vitals.create') }}" class="btn btn-clinic">
                <i class="bi bi-activity me-1"></i> Record New Vital Signs
            </a>
        </div>
    </div>

    <!-- Filter by Patient -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('vitals.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <select name="patient_id" class="form-select">
                        <option value="">Filter by patient...</option>
                        @foreach($patients as $pat)
                            <option value="{{ $pat->id }}" {{ $patientId == $pat->id ? 'selected' : '' }}>
                                {{ $pat->full_name }} ({{ $pat->patient_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Filter</button>
                    @if($patientId)
                        <a href="{{ route('vitals.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Vitals Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-activity text-info me-2"></i> Clinical Vital Signs Measurements ({{ $vitals->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Recorded At</th>
                        <th>Patient</th>
                        <th>BP (mmHg)</th>
                        <th>Pulse (bpm)</th>
                        <th>Temp (°C)</th>
                        <th>SpO2</th>
                        <th>Weight & Height</th>
                        <th>BMI</th>
                        <th>Logged By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vitals as $v)
                        <tr>
                            <td class="small text-secondary">
                                {{ $v->recorded_at->format('M d, Y') }}
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $v->recorded_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $v->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $v->patient->full_name }}
                                </a>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $v->patient->patient_code }}</div>
                            </td>
                            <td>
                                @if($v->blood_pressure)
                                    <span class="fw-bold text-primary">{{ $v->blood_pressure }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($v->pulse_rate)
                                    <span class="text-danger fw-semibold">{{ $v->pulse_rate }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($v->temperature)
                                    <span class="{{ $v->temperature >= 38 ? 'text-danger fw-bold' : '' }}">{{ $v->temperature }} °C</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($v->oxygen_saturation)
                                    <span class="{{ $v->oxygen_saturation < 95 ? 'text-danger fw-bold' : 'text-info' }}">{{ $v->oxygen_saturation }}%</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $v->weight_kg ? $v->weight_kg . ' kg' : '' }}
                                    {{ $v->height_cm ? ' / ' . $v->height_cm . ' cm' : '' }}
                                </small>
                            </td>
                            <td>
                                @if($v->bmi)
                                    <span class="badge bg-light text-dark border">{{ $v->bmi }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $v->recordedByUser->name ?? 'Staff' }}</td>
                            <td class="text-end">
                                <form action="{{ route('vitals.destroy', $v) }}" method="POST" onsubmit="return confirm('Delete this measurement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-activity fs-2 d-block mb-2 text-secondary"></i>
                                No vital sign measurements recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vitals->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $vitals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
