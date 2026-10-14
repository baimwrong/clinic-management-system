@extends('layouts.app')

@section('title', 'Clinical Command Dashboard')

@section('content')
<div class="container-fluid p-0">
    <!-- Header with Quick Action Buttons -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Clinical Command Dashboard</h3>
            <p class="text-muted small mb-0">Overview of clinical operations, patient intake, triage queues, and pharmacy alerts.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('patients.create') }}" class="btn btn-clinic btn-sm">
                <i class="bi bi-person-plus me-1"></i> New Patient
            </a>
            <a href="{{ route('appointments.create') }}" class="btn btn-clinic-outline btn-sm">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-warning btn-sm fw-semibold shadow-sm text-dark">
                <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
            </a>
            @endif
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-value">{{ number_format($stats['total_patients']) }}</div>
                    <small class="text-muted"><i class="bi bi-arrow-up-short text-success"></i> +{{ $stats['new_patients_this_month'] }} this month</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="bi bi-calendar2-week-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Today's Appointments</div>
                    <div class="stat-value">{{ number_format($stats['today_appointments']) }}</div>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i> Active queue</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-capsule"></i>
                </div>
                <div>
                    <div class="stat-label">Low Stock Medicines</div>
                    <div class="stat-value text-danger">{{ number_format($stats['low_stock_medicines']) }}</div>
                    <small class="text-muted"><a href="{{ route('pharmacy.medicines.index') }}" class="text-decoration-none">Review catalog &rarr;</a></small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-label">Collected Revenue</div>
                    <div class="stat-value text-success">${{ number_format($stats['total_revenue'], 2) }}</div>
                    <small class="text-muted">${{ number_format($stats['today_revenue'], 2) }} collected today</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Row: Chart & Appointments Queue -->
    <div class="row g-4 mb-4">
        <!-- 7-Day Appointment Trends Chart (Offline Chart.js) -->
        <div class="col-lg-7">
            <div class="card card-clinic h-100">
                <div class="card-header-clinic">
                    <span><i class="bi bi-graph-up text-primary me-2"></i> Patient Appointment Inflow (Last 7 Days)</span>
                    <span class="badge bg-light text-secondary border">Realtime</span>
                </div>
                <div class="card-body p-4">
                    <canvas id="appointmentTrendChart" height="130"></canvas>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert Widget -->
        <div class="col-lg-5">
            <div class="card card-clinic h-100">
                <div class="card-header-clinic">
                    <span><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Pharmacy Stock Alerts</span>
                    <a href="{{ route('pharmacy.medicines.index') }}" class="btn btn-sm btn-outline-secondary py-0">Catalog</a>
                </div>
                <div class="card-body p-0">
                    @if($lowStockItems->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
                            All pharmaceutical inventory levels are well above critical thresholds.
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($lowStockItems as $med)
                                <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $med->name }}</div>
                                        <small class="text-muted">{{ $med->category }} &bull; {{ $med->unit }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                            Stock: {{ $med->total_stock }} / Min: {{ $med->reorder_level }}
                                        </span>
                                        <div class="mt-1">
                                            <a href="{{ route('pharmacy.medicines.show', $med) }}" class="btn btn-sm btn-link p-0 text-decoration-none small">Add Batch</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Queue & Recent Consultations -->
    <div class="row g-4">
        <!-- Today's Queue -->
        <div class="col-lg-7">
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-list-check text-info me-2"></i> Today's Appointment & Triage Queue</span>
                    <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-outline-primary py-0">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-clinic mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayAppointments as $apt)
                                <tr>
                                    <td class="fw-semibold text-secondary">
                                        {{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('patients.show', $apt->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $apt->patient->full_name }}
                                        </a>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $apt->patient->patient_code }}</div>
                                    </td>
                                    <td>{{ $apt->doctor->name }}</td>
                                    <td>
                                        <span class="badge {{ $apt->priority_badge_class }}">
                                            {{ ucfirst($apt->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $apt->status_badge_class }}">
                                            {{ ucfirst(str_replace('_', ' ', $apt->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($apt->status === 'scheduled')
                                            <form action="{{ route('appointments.status', $apt) }}" method="POST" class="d-inline">
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
                                        @else
                                            <a href="{{ route('appointments.show', $apt) }}" class="btn btn-sm btn-light py-0 px-2">
                                                View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-check fs-3 d-block mb-2 text-secondary"></i>
                                        No appointments scheduled for today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent EMR Consultations or Admin Audit Logs -->
        <div class="col-lg-5">
            @if(auth()->user()->isAdmin() && $recentLogs->isNotEmpty())
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-shield-shaded text-warning me-2"></i> Administrative Activity Trail</span>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary py-0">Full Log</a>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($recentLogs as $log)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">{{ $log->action }}</span>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="small text-dark">{{ $log->description }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">By: {{ $log->user?->name ?? 'System' }} &bull; IP: {{ $log->ip_address }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-journal-medical text-primary me-2"></i> Recent Clinical Encounters</span>
                    <a href="{{ route('consultations.index') }}" class="btn btn-sm btn-outline-secondary py-0">View All</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentConsultations as $cns)
                        <a href="{{ route('consultations.show', $cns) }}" class="list-group-item list-group-item-action p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">{{ $cns->patient->full_name }}</span>
                                <small class="text-muted">{{ $cns->consultation_date->format('M d, h:i A') }}</small>
                            </div>
                            <div class="small text-primary fw-medium mb-1">
                                <i class="bi bi-heart-pulse me-1"></i> {{ $cns->primary_diagnosis }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                Doctor: {{ $cns->doctor->name }} &bull; {{ $cns->consultation_number }}
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted">
                            No recent consultations found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('appointmentTrendChart');
        if (ctx && typeof Chart !== 'undefined') {
            const labels = @json(array_column($appointmentTrends, 'day'));
            const dataValues = @json(array_column($appointmentTrends, 'count'));

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Patient Appointments',
                        data: dataValues,
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#0d9488',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
