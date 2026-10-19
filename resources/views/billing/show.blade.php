@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4 no-print">
        <div>
            <a href="{{ route('billing.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Invoices List
            </a>
            <h3 class="fw-bold mb-0">Invoice {{ $invoice->invoice_number }}</h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('billing.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Print Formal Receipt
            </a>
            @if($invoice->balance_due > 0)
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="bi bi-cash-stack me-1"></i> Record Payment
                </button>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Invoice Content -->
        <div class="col-lg-8">
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic">
                    <span><i class="bi bi-receipt text-primary me-2"></i> Invoice Overview</span>
                    <span class="badge {{ $invoice->payment_status_badge_class }} px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Billed To Patient:</span>
                            <a href="{{ route('patients.show', $invoice->patient) }}" class="fs-5 fw-bold text-dark text-decoration-none">
                                {{ $invoice->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $invoice->patient->patient_code }} &bull; Phone: {{ $invoice->patient->phone }}</div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">Invoice Date:</span>
                            <span class="fw-bold">{{ $invoice->invoice_date->format('M d, Y') }}</span>
                            @if($invoice->due_date)
                                <div class="small text-muted">Due Date: {{ $invoice->due_date->format('M d, Y') }}</div>
                            @endif
                            @if($invoice->payment_method)
                                <div class="small text-muted">Method: <strong class="text-uppercase">{{ $invoice->payment_method }}</strong></div>
                            @endif
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Type</th>
                                    <th>Service / Medication Description</th>
                                    <th class="text-center" style="width: 80px;">Qty</th>
                                    <th class="text-end" style="width: 120px;">Unit Price</th>
                                    <th class="text-end" style="width: 120px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-secondary border text-capitalize">
                                                {{ str_replace('_', ' ', $item->item_type) }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $item->description }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold">${{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-semibold">Subtotal:</td>
                                    <td class="text-end fw-semibold">${{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-muted">Taxes & Fees:</td>
                                    <td class="text-end text-muted">+${{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-success">Discount / Copay:</td>
                                    <td class="text-end text-success">-${{ number_format($invoice->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="fs-6">
                                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                    <td class="text-end fw-bold text-dark">${{ number_format($invoice->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-semibold text-success">Total Amount Paid:</td>
                                    <td class="text-end fw-bold text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                                </tr>
                                <tr class="fs-5">
                                    <td colspan="4" class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-muted' }}">Balance Remaining:</td>
                                    <td class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-muted' }}">${{ number_format($invoice->balance_due, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($invoice->notes)
                        <div class="p-3 bg-light rounded small text-muted">
                            <strong>Invoice Notes:</strong> {{ $invoice->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment History Ledger -->
            <div class="card card-clinic mb-4">
                <div class="card-header-clinic d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-clock-history text-primary me-2"></i> Payment Transactions History</span>
                    <span class="badge bg-light text-dark border">{{ $invoice->payments->count() }} {{ \Illuminate\Support\Str::plural('Transaction', $invoice->payments->count()) }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt / Ref #</th>
                                    <th>Date & Time</th>
                                    <th>Method</th>
                                    <th>Received By</th>
                                    <th>Notes</th>
                                    <th class="text-end">Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->payments as $payment)
                                    <tr>
                                        <td class="fw-bold font-monospace text-primary">{{ $payment->payment_reference }}</td>
                                        <td>{{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : $payment->created_at->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border text-uppercase">
                                                {{ str_replace('_', ' ', $payment->payment_method) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->recordedByUser->name ?? 'System' }}</td>
                                        <td class="small text-muted">{{ $payment->notes ?? '—' }}</td>
                                        <td class="text-end fw-bold text-success">+${{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-wallet2 fs-3 d-block mb-1"></i>
                                            No payment transactions recorded for this invoice yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($invoice->payment_status !== 'paid')
            <!-- Add Additional Item Box -->
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-plus-circle text-primary me-2"></i> Append Additional Charge Item</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('billing.items.store', $invoice) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Type</label>
                            <select name="item_type" class="form-select form-select-sm" required>
                                <option value="procedure">Clinical Procedure</option>
                                <option value="lab_test">Laboratory Test</option>
                                <option value="medicine">Medication</option>
                                <option value="other">Other Supply</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm" required placeholder="e.g. Suture removal / IV drip">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Qty</label>
                            <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Unit Price ($)</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control form-control-sm" required placeholder="25.00">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-clinic btn-sm w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Actions & Summary -->
        <div class="col-lg-4">
            <div class="card card-clinic mb-4 text-center p-4">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Balance Due</div>
                <div class="fs-1 fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }} mb-2">
                    ${{ number_format($invoice->balance_due, 2) }}
                </div>
                @if($invoice->balance_due > 0)
                    <button type="button" class="btn btn-success w-100 py-2 fw-semibold mb-2" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="bi bi-cash-stack me-1"></i> Receive Payment
                    </button>
                @else
                    <div class="badge bg-success-subtle text-success border border-success p-2 mb-2 w-100">
                        <i class="bi bi-check-circle-fill me-1"></i> Paid in Full
                    </div>
                @endif
                <a href="{{ route('billing.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-printer me-1"></i> Print Formal Receipt
                </a>
            </div>

            <!-- Linked Encounter -->
            @if($invoice->consultation)
            <div class="card card-clinic">
                <div class="card-header-clinic">
                    <span><i class="bi bi-journal-medical text-primary me-2"></i> Linked Clinical Encounter</span>
                </div>
                <div class="card-body p-3 small">
                    <div class="mb-2"><strong>Encounter:</strong> {{ $invoice->consultation->consultation_number }}</div>
                    <div class="mb-2"><strong>Physician:</strong> {{ $invoice->consultation->doctor->name }}</div>
                    <div class="mb-3"><strong>Diagnosis:</strong> {{ $invoice->consultation->primary_diagnosis }}</div>
                    <a href="{{ route('consultations.show', $invoice->consultation) }}" class="btn btn-outline-primary btn-sm w-100">
                        View Clinical Record &rarr;
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
@if($invoice->balance_due > 0)
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('billing.payments.store', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="recordPaymentModalLabel">
                        <i class="bi bi-cash-stack text-success me-2"></i> Record Patient Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold" value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" min="0.01" required>
                        <div class="form-text small">Maximum payable: ${{ number_format($invoice->balance_due, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Instrument / Channel <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" selected>Cash Payment</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="insurance">Insurance / Third Party Settlement</option>
                            <option value="bank_transfer">Direct Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Transaction Reference / Receipt Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Card Authorization code #98124, POS Terminal 2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Complete & Log Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
