@extends('layouts.app')

@section('title', 'Dispense Prescription ' . $prescription->prescription_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('pharmacy.prescriptions.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Dispensing Queue
            </a>
            <h3 class="fw-bold mb-0">Dispensing Terminal: {{ $prescription->prescription_number }}</h3>
        </div>
        <div>
            <span class="badge {{ $prescription->status_badge_class }} px-3 py-2 fs-6">
                Status: {{ ucfirst(str_replace('_', ' ', $prescription->status)) }}
            </span>
        </div>
    </div>

    <!-- Patient Banner & Allergy Check -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-6 border-end">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Patient Details</span>
                    <span class="fs-5 fw-bold text-dark">{{ $prescription->patient->full_name }}</span>
                    <div class="small text-muted">
                        Code: {{ $prescription->patient->patient_code }} &bull; Age: {{ $prescription->patient->age }} yrs &bull; Gender: {{ ucfirst($prescription->patient->gender) }}
                    </div>
                </div>
                <div class="col-md-6 ps-md-4">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Prescribing Physician</span>
                    <span class="fs-5 fw-bold text-dark">{{ $prescription->doctor->name }}</span>
                    <div class="small text-muted">{{ $prescription->doctor->specialization }} &bull; License: {{ $prescription->doctor->license_number }}</div>
                </div>
            </div>

            @if($prescription->patient->allergies)
            <div class="alert alert-danger py-2 px-3 mt-3 mb-0 small d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger"></i>
                <div>
                    <strong>PHARMACIST ATTENTION REQUIRED &mdash; ALLERGIES:</strong> {{ $prescription->patient->allergies }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Items to Dispense -->
    <div class="card card-clinic mb-4">
        <div class="card-header-clinic">
            <span><i class="bi bi-prescription2 text-success me-2"></i> Prescribed Medication Items & Stock Verification</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Medication</th>
                        <th>Dosage & Frequency</th>
                        <th>Duration</th>
                        <th>Prescribed Qty</th>
                        <th>Available Stock</th>
                        <th>Inventory Availability</th>
                    </tr>
                </thead>
                <tbody>
                    @php $allInStock = true; @endphp
                    @foreach($prescription->items as $item)
                        @php 
                            $stock = $item->medicine->total_stock;
                            $hasEnough = $stock >= $item->quantity_prescribed;
                            if (!$hasEnough) $allInStock = false;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->medicine->name }}</div>
                                <small class="text-muted">{{ $item->medicine->generic_name }} ({{ $item->medicine->unit }})</small>
                            </td>
                            <td>
                                <div>{{ $item->dosage }}</div>
                                <small class="text-muted">{{ $item->frequency }}</small>
                            </td>
                            <td>{{ $item->duration_days }} days</td>
                            <td class="fw-bold fs-6">{{ $item->quantity_prescribed }} units</td>
                            <td>
                                <span class="fw-semibold {{ $hasEnough ? 'text-success' : 'text-danger' }}">
                                    {{ $stock }} units available
                                </span>
                            </td>
                            <td>
                                @if($hasEnough)
                                    <span class="badge bg-success-subtle text-success border border-success">
                                        <i class="bi bi-check-circle me-1"></i> Ready to Dispense
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger">
                                        <i class="bi bi-x-circle me-1"></i> Stock Shortage
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($prescription->status === 'dispensed')
        <!-- Dispensed Receipt Card -->
        <div class="card card-clinic border-success p-4 mb-4 bg-light">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                <div>
                    <h5 class="fw-bold text-success mb-1">Prescription Dispensed Successfully</h5>
                    <p class="mb-0 text-muted small">
                        Dispensed by Pharmacist <strong>{{ $prescription->dispensedByUser->name ?? 'Pharmacy Staff' }}</strong>
                        on {{ $prescription->dispensed_at ? $prescription->dispensed_at->format('F j, Y \a\t h:i A') : 'N/A' }}.
                        Inventory stock was automatically deducted using First-Expired First-Out (FEFO) order.
                    </p>
                </div>
            </div>
        </div>
    @elseif($prescription->status === 'pending')
        <!-- Dispense Action Form -->
        <div class="card card-clinic border-success">
            <div class="card-header bg-success bg-opacity-10 py-3">
                <span class="fw-bold text-success"><i class="bi bi-capsule-pill me-2"></i> Dispense Medication & Execute Stock Deduction</span>
            </div>
            <div class="card-body p-4">
                @if(!$allInStock)
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> One or more medications do not have sufficient stock in active, non-expired batches. Please restock before dispensing.
                    </div>
                @endif

                <form action="{{ route('pharmacy.prescriptions.dispense', $prescription) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Dispensing / Pharmacist Consultation Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Advised patient to take antibiotic with food; patient instructed on completion of course."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold" {{ !$allInStock ? 'disabled' : '' }}>
                            <i class="bi bi-check2-circle me-1"></i> Confirm Dispensing & Deduct Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
