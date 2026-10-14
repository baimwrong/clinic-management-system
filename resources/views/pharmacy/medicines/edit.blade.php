@extends('layouts.app')

@section('title', 'Edit Medicine - ' . $medicine->name)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('pharmacy.medicines.show', $medicine) }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to {{ $medicine->name }}
            </a>
            <h3 class="fw-bold mb-0">Edit Medicine Catalog Entry</h3>
        </div>
        <div>
            <span class="badge bg-light text-dark border font-monospace px-3 py-2 fs-6">
                {{ $medicine->code }}
            </span>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('pharmacy.medicines.update', $medicine) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Medicine Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $medicine->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Generic / API Name</label>
                        <input type="text" name="generic_name" class="form-control" value="{{ old('generic_name', $medicine->generic_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', $medicine->category) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Dispensing Unit <span class="text-danger">*</span></label>
                        <select name="unit" class="form-select" required>
                            @foreach(['Tablet', 'Capsule', 'Syrup / Bottle', 'Vial / Injection', 'Ampoule', 'Cream / Ointment', 'Drops', 'Inhaler'] as $u)
                                <option value="{{ $u }}" {{ old('unit', $medicine->unit) == $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Dosage Form</label>
                        <input type="text" name="dosage_form" class="form-control" value="{{ old('dosage_form', $medicine->dosage_form) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Manufacturer</label>
                        <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer', $medicine->manufacturer) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Unit Cost Price ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="unit_cost" class="form-control" value="{{ old('unit_cost', $medicine->unit_cost) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Unit Selling Price ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', $medicine->selling_price) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Reorder Threshold Level <span class="text-danger">*</span></label>
                        <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', $medicine->reorder_level) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Catalog Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ $medicine->is_active ? 'selected' : '' }}>Active in Catalog</option>
                            <option value="0" {{ !$medicine->is_active ? 'selected' : '' }}>Inactive / Decommissioned</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $medicine->description) }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('pharmacy.medicines.show', $medicine) }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Item
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
