@extends('layouts.app')

@section('title', $medicine->name . ' - Pharmacy Item')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('pharmacy.medicines.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Pharmacy Inventory
            </a>
            <h3 class="fw-bold mb-0">{{ $medicine->name }}</h3>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-clinic" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                <i class="bi bi-plus-circle me-1"></i> Add Stock Batch
            </button>
            <a href="{{ route('pharmacy.medicines.edit', $medicine) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit Item
            </a>
        </div>
    </div>

    <!-- Medicine Summary Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <div class="col-md-3 text-center border-end">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Total Available Stock</div>
                    <div class="fs-1 fw-bold {{ $medicine->isLowStock() ? 'text-danger' : 'text-success' }}">
                        {{ $medicine->total_stock }}
                    </div>
                    <span class="badge {{ $medicine->isLowStock() ? 'bg-danger' : 'bg-success' }}">
                        {{ $medicine->unit }}s In Stock
                    </span>
                    <div class="small text-muted mt-2">Reorder Level: {{ $medicine->reorder_level }}</div>
                </div>

                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted small d-block">Medicine Code:</span>
                            <span class="fw-bold font-monospace">{{ $medicine->code }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted small d-block">Generic Name:</span>
                            <span class="fw-semibold">{{ $medicine->generic_name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted small d-block">Therapeutic Category:</span>
                            <span class="badge bg-light text-dark border">{{ $medicine->category }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted small d-block">Selling Price:</span>
                            <span class="fw-bold text-success fs-5">${{ number_format($medicine->selling_price, 2) }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted small d-block">Unit Cost Price:</span>
                            <span class="fw-semibold text-secondary">${{ number_format($medicine->unit_cost, 2) }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted small d-block">Manufacturer:</span>
                            <span>{{ $medicine->manufacturer ?? 'Not specified' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Batches Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-boxes text-primary me-2"></i> Active & Expired Inventory Batches (FEFO Tracking)</span>
            <button type="button" class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                + New Batch
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Batch Number</th>
                        <th>Purchase Date</th>
                        <th>Expiry Date</th>
                        <th>Days to Expiry</th>
                        <th>Initial Received</th>
                        <th>Remaining Stock</th>
                        <th>Supplier</th>
                        <th>Batch Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicine->batches as $batch)
                        <tr>
                            <td class="font-monospace fw-bold text-dark">{{ $batch->batch_number }}</td>
                            <td class="small text-secondary">{{ $batch->purchase_date ? $batch->purchase_date->format('M d, Y') : '&mdash;' }}</td>
                            <td class="fw-semibold {{ $batch->isExpired() ? 'text-danger' : ($batch->isExpiringSoon() ? 'text-warning' : '') }}">
                                {{ $batch->expiry_date->format('M d, Y') }}
                            </td>
                            <td>
                                @if($batch->isExpired())
                                    <span class="text-danger small fw-bold">Expired</span>
                                @else
                                    <span class="small text-muted">{{ (int)now()->diffInDays($batch->expiry_date) }} days</span>
                                @endif
                            </td>
                            <td>{{ number_format($batch->quantity) }}</td>
                            <td>
                                <span class="fw-bold fs-6 {{ $batch->remaining_quantity <= 0 ? 'text-muted' : 'text-primary' }}">
                                    {{ number_format($batch->remaining_quantity) }}
                                </span>
                            </td>
                            <td class="small">{{ $batch->supplier ?? 'Direct' }}</td>
                            <td>
                                @if($batch->isExpired())
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($batch->remaining_quantity <= 0)
                                    <span class="badge bg-secondary">Depleted</span>
                                @elseif($batch->isExpiringSoon())
                                    <span class="badge bg-warning text-dark">Expiring Soon</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success">Active Batch</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-box fs-2 d-block mb-2 text-secondary"></i>
                                No stock batches recorded. Click "Add Stock Batch" to register inventory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Stock Batch -->
<div class="modal fade" id="addBatchModal" tabindex="-1" aria-labelledby="addBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pharmacy.medicines.batches.store', $medicine) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addBatchModalLabel">
                        <i class="bi bi-plus-circle text-primary me-2"></i> Add Stock Batch for {{ $medicine->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Batch Number <span class="text-danger">*</span></label>
                        <input type="text" name="batch_number" class="form-control font-monospace" placeholder="e.g. BAT-2026-088" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Quantity Received <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" placeholder="e.g. 500" min="1" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Supplier / Pharmaceutical Distributor</label>
                        <input type="text" name="supplier" class="form-control" placeholder="e.g. Apex Health Logistics">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-clinic">
                        <i class="bi bi-check2-circle me-1"></i> Register Batch Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
