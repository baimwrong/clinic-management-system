@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('billing.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Invoices List
            </a>
            <h3 class="fw-bold mb-0">Create Clinical Invoice</h3>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-body p-4">
            <form action="{{ route('billing.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Billed Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select patient...</option>
                            @foreach($patients as $pat)
                                <option value="{{ $pat->id }}" {{ (old('patient_id', $selectedPatient?->id) == $pat->id) ? 'selected' : '' }}>
                                    {{ $pat->full_name }} ({{ $pat->patient_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>

                    <!-- Line Items Section -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-check me-2 text-primary"></i> Itemized Charges</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addInvoiceItemBtn">
                                <i class="bi bi-plus-lg me-1"></i> Add Line Item
                            </button>
                        </div>

                        <div id="invoiceItemsContainer">
                            <div class="invoice-item-row row g-2 align-items-end mb-3 p-3 bg-light rounded position-relative">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item-btn" aria-label="Close"></button>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Item Type</label>
                                    <select name="items[0][item_type]" class="form-select form-select-sm" required>
                                        <option value="consultation">Doctor Consultation</option>
                                        <option value="procedure">Clinical Procedure</option>
                                        <option value="medicine">Medication / Pharmacy</option>
                                        <option value="lab_test">Laboratory Diagnostic Test</option>
                                        <option value="other">Medical Supplies / Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Description</label>
                                    <input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="e.g. Outpatient Consultation" required value="General Medical Consultation">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Quantity</label>
                                    <input type="number" name="items[0][quantity]" class="form-control form-control-sm qty-input" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Unit Price ($)</label>
                                    <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm price-input" value="50.00" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Discount, Tax & Notes -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Discount Amount ($)</label>
                        <input type="number" step="0.01" name="discount_amount" class="form-control" value="0.00" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Tax Amount ($)</label>
                        <input type="number" step="0.01" name="tax_amount" class="form-control" value="0.00" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Billing Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Covered 80% by corporate insurance">
                    </div>

                    <div class="col-12 text-end">
                        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-clinic px-4">
                            <i class="bi bi-save me-1"></i> Generate & Issue Invoice
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let itemIndex = 1;
        const container = document.getElementById('invoiceItemsContainer');
        const addBtn = document.getElementById('addInvoiceItemBtn');

        addBtn.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'invoice-item-row row g-2 align-items-end mb-3 p-3 bg-light rounded position-relative';
            div.innerHTML = `
                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item-btn" aria-label="Close"></button>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Item Type</label>
                    <select name="items[${itemIndex}][item_type]" class="form-select form-select-sm" required>
                        <option value="procedure">Clinical Procedure</option>
                        <option value="medicine">Medication / Pharmacy</option>
                        <option value="lab_test">Laboratory Diagnostic Test</option>
                        <option value="consultation">Doctor Consultation</option>
                        <option value="other">Medical Supplies / Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Description</label>
                    <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" placeholder="e.g. Wound dressing / Blood test" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Quantity</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm qty-input" value="1" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Unit Price ($)</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm price-input" value="15.00" min="0" required>
                </div>
            `;
            container.appendChild(div);
            itemIndex++;
        });

        container.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item-btn')) {
                const rows = container.querySelectorAll('.invoice-item-row');
                if (rows.length > 1) {
                    e.target.closest('.invoice-item-row').remove();
                } else {
                    alert('An invoice must contain at least one line item.');
                }
            }
        });
    });
</script>
@endpush
@endsection
