@extends('layouts.app')

@section('title', 'Order Lab Test')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('lab.orders.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Lab Orders
            </a>
            <h3 class="fw-bold mb-0">Order Diagnostic Laboratory Test</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('lab.orders.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select patient...</option>
                            @foreach($patients as $pat)
                                <option value="{{ $pat->id }}" {{ (old('patient_id', $selectedPatient?->id) == $pat->id) ? 'selected' : '' }}>
                                    {{ $pat->full_name }} ({{ $pat->patient_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Requesting Physician <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select" required>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ (auth()->user()->doctor?->id == $doc->id || old('doctor_id') == $doc->id) ? 'selected' : '' }}>
                                    {{ $doc->name }} ({{ $doc->specialization }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Diagnostic Test <span class="text-danger">*</span></label>
                        <select name="lab_test_id" class="form-select" required>
                            <option value="">Select diagnostic panel / test...</option>
                            @foreach($tests as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->name }} &bull; {{ $t->category }} (${{ number_format($t->price, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Order Timestamp <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="order_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Clinical Notes / Reason for Testing</label>
                        <textarea name="technician_notes" class="form-control" rows="3" placeholder="Symptoms, differential diagnosis, specific parameters to monitor..."></textarea>
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('lab.orders.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-eyedropper me-1"></i> Submit Diagnostic Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
