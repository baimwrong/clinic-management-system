<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Receipt {{ $invoice->invoice_number }} - Clinic CMS</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; color: #1e293b; background: #fff; }
        .receipt-container { max-width: 800px; margin: 0 auto; padding: 2rem; }
        @media print {
            .no-print { display: none !important; }
            .receipt-container { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt-container">
        <!-- Print Toolbar -->
        <div class="no-print d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded border">
            <div>
                <strong>Official Receipt &bull; {{ $invoice->invoice_number }}</strong>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                <button onclick="window.close()" class="btn btn-sm btn-outline-secondary">
                    Close Window
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-hospital fs-1 text-teal" style="color: #0d9488;"></i>
                    <h3 class="fw-bold mb-0">Clinic Medical Center</h3>
                </div>
                <div class="small text-muted mt-1">
                    Primary Healthcare & Clinical Specialist Center<br>
                    Internal Medical Network &bull; Intranet Station
                </div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold text-uppercase mb-1">Official Invoice</h4>
                <div class="font-monospace fw-bold fs-5 text-primary">{{ $invoice->invoice_number }}</div>
                <div class="small text-muted">Date: {{ $invoice->invoice_date->format('F j, Y') }}</div>
                <div class="badge {{ $invoice->payment_status === 'paid' ? 'bg-success' : 'bg-danger' }} text-uppercase mt-1 px-3 py-1">
                    {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                </div>
            </div>
        </div>

        <!-- Billed To & Clinic Details -->
        <div class="row mb-4">
            <div class="col-6">
                <span class="small text-muted text-uppercase fw-bold d-block mb-1">Patient Details (Billed To):</span>
                <h5 class="fw-bold mb-1">{{ $invoice->patient->full_name }}</h5>
                <div class="small text-muted">Patient ID: <strong>{{ $invoice->patient->patient_code }}</strong></div>
                <div class="small text-muted">Phone: {{ $invoice->patient->phone }}</div>
                @if($invoice->patient->address)
                    <div class="small text-muted">{{ $invoice->patient->address }}</div>
                @endif
            </div>
            <div class="col-6 text-end">
                <span class="small text-muted text-uppercase fw-bold d-block mb-1">Billing Summary:</span>
                <div class="small text-muted">Payment Channel: <strong class="text-uppercase">{{ $invoice->payment_method ?? 'Pending' }}</strong></div>
                @if($invoice->due_date)
                    <div class="small text-muted">Payment Due: {{ $invoice->due_date->format('M d, Y') }}</div>
                @endif
                @if($invoice->consultation)
                    <div class="small text-muted">Encounter: {{ $invoice->consultation->consultation_number }}</div>
                @endif
            </div>
        </div>

        <!-- Itemized Table -->
        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Item Description</th>
                    <th>Category</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 120px;">Price</th>
                    <th class="text-end" style="width: 120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="fw-semibold">{{ $item->description }}</td>
                        <td class="small text-capitalize">{{ str_replace('_', ' ', $item->item_type) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-semibold">Subtotal:</td>
                    <td class="text-end fw-semibold">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td colspan="5" class="text-end text-muted">Tax / Surcharge:</td>
                    <td class="text-end text-muted">+${{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->discount_amount > 0)
                <tr>
                    <td colspan="5" class="text-end text-success">Discount / Copay:</td>
                    <td class="text-end text-success">-${{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="fs-6">
                    <td colspan="5" class="text-end fw-bold">Total Invoiced:</td>
                    <td class="text-end fw-bold">${{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold text-success">Amount Paid:</td>
                    <td class="text-end fw-bold text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr class="fs-5">
                    <td colspan="5" class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-dark' }}">Outstanding Balance:</td>
                    <td class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-dark' }}">${{ number_format($invoice->balance_due, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature & Footer -->
        <div class="row pt-4 mt-5 border-top">
            <div class="col-6">
                <p class="small text-muted mb-0">
                    Thank you for choosing Clinic Medical Center.<br>
                    For inquiries, contact the billing department.
                </p>
            </div>
            <div class="col-6 text-end">
                <div class="d-inline-block text-center" style="min-width: 200px;">
                    <div class="border-bottom border-dark pb-4 mb-1"></div>
                    <div class="fw-bold small">Authorized Clinic Cashier</div>
                    <div class="text-muted small">Clinic Revenue Division</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
