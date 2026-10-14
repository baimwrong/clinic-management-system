@extends('layouts.app')

@section('title', 'Medical Staff & Doctors')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Medical Staff & Specialists Directory</h3>
            <p class="text-muted small mb-0">Roster of licensed physicians, clinical specialists, consult fees, and room schedules.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('doctors.create') }}" class="btn btn-clinic">
                <i class="bi bi-person-plus me-1"></i> Add Doctor Profile
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-warning text-dark">
                <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import Staff
            </a>
            @endif
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('doctors.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search doctor name, email, or staff ID..." value="{{ $search }}">
                </div>
                <div class="col-md-4">
                    <select name="specialization" class="form-select">
                        <option value="">All Clinical Specializations</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec }}" {{ $specialization == $spec ? 'selected' : '' }}>{{ $spec }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Filter</button>
                    @if($search || $specialization)
                        <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Doctors Cards Grid -->
    <div class="row g-4 mb-4">
        @forelse($doctors as $doctor)
            <div class="col-md-6 col-lg-4">
                <div class="card card-clinic h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-teal-subtle text-teal d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px; background-color: #ccfbf1; color: #0d9488;">
                                    {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">{{ $doctor->name }}</h5>
                                    <span class="badge bg-light text-primary border">{{ $doctor->specialization }}</span>
                                </div>
                            </div>
                            <span class="badge {{ $doctor->status === 'active' ? 'bg-success-subtle text-success border border-success' : 'bg-secondary' }}">
                                {{ ucfirst($doctor->status) }}
                            </span>
                        </div>

                        <div class="small text-muted mb-3">
                            <div><i class="bi bi-mortarboard me-2"></i> {{ $doctor->qualification ?? 'Medical Practitioner' }}</div>
                            <div><i class="bi bi-shield-check me-2"></i> License: <code>{{ $doctor->license_number }}</code></div>
                            <div><i class="bi bi-door-open me-2"></i> Room / Suite: {{ $doctor->room_number ?? 'General Wing' }}</div>
                            <div><i class="bi bi-envelope me-2"></i> {{ $doctor->user->email }}</div>
                            @if($doctor->user->phone)
                                <div><i class="bi bi-telephone me-2"></i> {{ $doctor->user->phone }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small d-block">Consultation Fee</span>
                            <span class="fw-bold fs-5 text-success">${{ number_format($doctor->consultation_fee, 2) }}</span>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-sm btn-outline-primary">
                                Profile
                            </a>
                            <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-light border">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-person-x fs-2 d-block mb-2 text-secondary"></i>
                No medical specialists registered yet.
            </div>
        @endforelse
    </div>

    @if($doctors->hasPages())
    <div class="card card-clinic p-3">
        {{ $doctors->links() }}
    </div>
    @endif
</div>
@endsection
