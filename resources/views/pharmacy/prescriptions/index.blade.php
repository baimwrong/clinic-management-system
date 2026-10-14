@extends('layouts.app')

@section('title', 'Pharmacy Dispensing Queue')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Pharmacy Prescription Dispensing Queue</h3>
            <p class="text-muted small mb-0">Fulfill doctor orders, verify stock availability, and dispense medications.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pharmacy.medicines.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-capsule-pill me-1"></i> View Inventory
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ route('pharmacy.prescriptions.index', ['status' => 'pending']) }}">
                <i class="bi bi-hourglass-split me-1"></i> Pending Dispense
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'dispensed' ? 'active' : '' }}" href="{{ route('pharmacy.prescriptions.index', ['status' => 'dispensed']) }}">
                <i class="bi bi-check-circle me-1"></i> Dispensed Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'all' ? 'active' : '' }}" href="{{ route('pharmacy.prescriptions.index', ['status' => 'all']) }}">
                <i class="bi bi-collection me-1"></i> All Prescriptions
            </a>
        </li>
    </ul>

    <!-- Prescriptions Queue Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-prescription2 text-success me-2"></i> Prescriptions List ({{ $prescriptions->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Prescription #</th>
                        <th>Patient Name</th>
                        <th>Prescribing Doctor</th>
                        <th>Medication Items</th>
                        <th>Dispensing Status</th>
                        <th class="text-end">Fulfillment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $rx)
                        <tr>
                            <td class="small text-secondary">
                                {{ $rx->created_at->format('M d, Y') }}
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $rx->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $rx->prescription_number }}</span>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $rx->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $rx->patient->full_name }}
                                </a>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $rx->patient->patient_code }}</div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $rx->doctor->name }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $rx->doctor->specialization }}</div>
                            </td>
                            <td>
                                <div class="small">
                                    @foreach($rx->items as $it)
                                        <div>&bull; <strong>{{ $it->medicine->name }}</strong> ({{ $it->quantity_prescribed }} {{ $it->medicine->unit }}s)</div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $rx->status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $rx->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('pharmacy.prescriptions.show', $rx) }}" class="btn btn-sm {{ $rx->status === 'pending' ? 'btn-success' : 'btn-outline-secondary' }} py-1 px-3">
                                    @if($rx->status === 'pending')
                                        <i class="bi bi-check-lg me-1"></i> Dispense Rx
                                    @else
                                        <i class="bi bi-eye me-1"></i> Review
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-prescription2 fs-2 d-block mb-2 text-secondary"></i>
                                No prescriptions found for this queue filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prescriptions->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $prescriptions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
