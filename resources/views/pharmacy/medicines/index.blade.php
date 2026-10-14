@extends('layouts.app')

@section('title', 'Pharmacy & Medicines Inventory')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Pharmaceutical Catalog & Stock Inventory</h3>
            <p class="text-muted small mb-0">Monitor batch stocks, reorder thresholds, expiry dates, and unit pricing.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pharmacy.medicines.create') }}" class="btn btn-clinic">
                <i class="bi bi-capsule-pill me-1"></i> Catalog New Medicine
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-warning text-dark">
                <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import Medicines
            </a>
            @endif
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('pharmacy.medicines.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, generic name, or code..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Pharmaceutical Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Filter</button>
                    @if($search || $category)
                        <a href="{{ route('pharmacy.medicines.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Medicines Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-capsule text-primary me-2"></i> Cataloged Medications ({{ $medicines->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Medicine Brand Name</th>
                        <th>Generic Name</th>
                        <th>Category</th>
                        <th>Unit Form</th>
                        <th>Selling Price</th>
                        <th>Available Stock</th>
                        <th>Stock Alert</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $med)
                        @php $stock = $med->total_stock; @endphp
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $med->code }}</span>
                            </td>
                            <td>
                                <a href="{{ route('pharmacy.medicines.show', $med) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $med->name }}
                                </a>
                                @if($med->dosage_form)
                                    <span class="small text-muted">({{ $med->dosage_form }})</span>
                                @endif
                            </td>
                            <td class="small text-secondary">{{ $med->generic_name ?? '&mdash;' }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $med->category }}</span>
                            </td>
                            <td>{{ $med->unit }}</td>
                            <td class="fw-bold text-success">${{ number_format($med->selling_price, 2) }}</td>
                            <td>
                                <span class="fw-bold fs-6 {{ $med->isLowStock() ? 'text-danger' : 'text-dark' }}">
                                    {{ $stock }}
                                </span>
                            </td>
                            <td>
                                @if($stock <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($med->isLowStock())
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock (Min: {{ $med->reorder_level }})
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success">Healthy</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('pharmacy.medicines.show', $med) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Manage Batches & Inventory">
                                    <i class="bi bi-boxes me-1"></i> Stock Batches
                                </a>
                                <a href="{{ route('pharmacy.medicines.edit', $med) }}" class="btn btn-sm btn-light border py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-capsule fs-2 d-block mb-2 text-secondary"></i>
                                No medicines found in pharmacy catalog.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($medicines->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $medicines->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
