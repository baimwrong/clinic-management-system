@extends('layouts.app')

@section('title', 'Add Lab Test')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('lab.tests.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Lab Tests Catalog
            </a>
            <h3 class="fw-bold mb-0">Catalog New Laboratory Test</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('lab.tests.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Test Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. LAB-CBC" required value="{{ old('code') }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Test Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Complete Blood Count (CBC) with Differential" required value="{{ old('name') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Department / Category <span class="text-danger">*</span></label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Hematology, Biochemistry, Microbiology" required value="{{ old('category') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Standard Test Fee ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="25.00" required value="{{ old('price', '30.00') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Standard Biological Reference Range</label>
                        <input type="text" name="reference_range" class="form-control font-monospace" placeholder="e.g. 4.5 - 11.0 x 10^3 / µL" value="{{ old('reference_range') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Test Description & Preparation Instructions</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Fasting requirements, specimen collection protocols...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('lab.tests.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-save me-1"></i> Catalog Lab Test
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
