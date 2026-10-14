@extends('layouts.app')

@section('title', 'Billing & Invoices')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Billing & Invoices Registry</h3>
            <p class="text-muted small mb-0">Track clinical service charges, prescription bills, payments, and balances.</p>
        </div>
        <div>
            <a href="{{ route('billing.create') }}" class="btn btn-clinic">
                <i class="bi bi-receipt me-1"></i> Create New Invoice
            </a>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="stat-label">Total Invoiced</div>
                    <div class="stat-value">${{ number_format($stats['total_invoiced'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-label">Total Collected</div>
                    <div class="stat-value text-success">${{ number_format($stats['total_collected'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="stat-label">Pending Balance Due</div>
                    <div class="stat-value text-danger">${{ number_format($stats['total_pending'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('billing.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by invoice number or patient name..." value="{{ $search }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Payment Statuses</option>
                        <option value="unpaid" {{ $status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ $status == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Filter</button>
                    @if($search || $status)
                        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-receipt-cutoff text-primary me-2"></i> Invoices List ({{ $invoices->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Subtotal</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Balance Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('billing.show', $inv) }}" class="fw-bold font-monospace text-dark text-decoration-none">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $inv->invoice_date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('patients.show', $inv->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $inv->patient->full_name }}
                                </a>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $inv->patient->patient_code }}</div>
                            </td>
                            <td>${{ number_format($inv->subtotal, 2) }}</td>
                            <td class="fw-bold">${{ number_format($inv->total_amount, 2) }}</td>
                            <td class="text-success">${{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-muted' }}">
                                ${{ number_format($inv->balance_due, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $inv->payment_status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $inv->payment_status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('billing.show', $inv) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                    View
                                </a>
                                <a href="{{ route('billing.print', $inv) }}" target="_blank" class="btn btn-sm btn-light border py-0 px-2" title="Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-2 d-block mb-2 text-secondary"></i>
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
