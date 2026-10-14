@extends('layouts.app')

@section('title', 'Catalog Medicine')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('pharmacy.medicines.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Pharmacy Inventory
            </a>
            <h3 class="fw-bold mb-0">Catalog New Pharmaceutical Item</h3>
        </div>
        <div>
            <span class="badge bg-light text-dark border font-monospace px-3 py-2 fs-6">
                Assigned Code: {{ $suggestedCode }}
            </span>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('pharmacy.medicines.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Medicine Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Augmentin 625mg">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Generic / Active Pharmaceutical Ingredient</label>
                        <input type="text" name="generic_name" class="form-control" value="{{ old('generic_name') }}" placeholder="e.g. Amoxicillin + Clavulanic Acid">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Therapeutic Category <span class="text-danger">*</span></label>
                        <input type="text" name="category" class="form-control" value="{{ old('category') }}" required placeholder="e.g. Antibiotic, Analgesic, Antihypertensive">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Dispensing Unit <span class="text-danger">*</span></label>
                        <select name="unit" class="form-select" required>
                            <option value="Tablet" selected>Tablet</option>
                            <option value="Capsule">Capsule</option>
                            <option value="Syrup / Bottle">Syrup / Bottle</option>
                            <option value="Vial / Injection">Vial / Injection</option>
                            <option value="Ampoule">Ampoule</option>
                            <option value="Cream / Ointment">Cream / Ointment</option>
                            <option value="Drops">Drops</option>
                            <option value="Inhaler">Inhaler</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Dosage Form & Strength</label>
                        <input type="text" name="dosage_form" class="form-control" value="{{ old('dosage_form') }}" placeholder="e.g. 500mg, 100ml, 2% w/w">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Manufacturer / Pharmaceutical Lab</label>
                        <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer') }}" placeholder="e.g. Pfizer, GSK, Novartis">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Unit Cost Price ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="unit_cost" class="form-control" value="{{ old('unit_cost', '0.50') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Unit Selling Price ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', '1.20') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Reorder Threshold Level <span class="text-danger">*</span></label>
                        <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', '20') }}" required>
                        <div class="form-text small">Triggers low-stock alert when remaining quantity drops below this.</div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Description & Clinical Usage Notes</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Indications, contraindications, storage conditions...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('pharmacy.medicines.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-save me-1"></i> Catalog Item
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
