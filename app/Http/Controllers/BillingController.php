<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $status = $request->input('status');
        $search = $request->input('search');

        $invoices = Invoice::with(['patient', 'items'])
            ->when($user->isPatient(), function ($query) use ($user) {
                return $query->where('patient_id', $user->patient?->id);
            })
            ->when($status, fn ($q) => $q->where('payment_status', $status))
            ->when($search, function ($query, $search) {
                $term = '%'.strtolower($search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(invoice_number) LIKE ?', [$term])
                        ->orWhereHas('patient', function ($pq) use ($term) {
                            $pq->whereRaw('LOWER(first_name) LIKE ?', [$term])
                                ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                                ->orWhereRaw('LOWER(patient_code) LIKE ?', [$term]);
                        });
                });
            })
            ->latest('invoice_date')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_invoiced' => $user->isPatient() ? Invoice::where('patient_id', $user->patient?->id)->sum('total_amount') : Invoice::sum('total_amount'),
            'total_collected' => $user->isPatient() ? Invoice::where('patient_id', $user->patient?->id)->sum('paid_amount') : Invoice::sum('paid_amount'),
            'total_pending' => $user->isPatient()
                ? Invoice::where('patient_id', $user->patient?->id)->whereIn('payment_status', ['unpaid', 'partially_paid'])->sum('balance_due')
                : Invoice::whereIn('payment_status', ['unpaid', 'partially_paid'])->sum('balance_due'),
        ];

        return view('billing.index', compact('invoices', 'status', 'search', 'stats'));
    }

    public function create(Request $request): View
    {
        $patientId = $request->input('patient_id');
        $selectedPatient = $patientId ? Patient::find($patientId) : null;
        $patients = Patient::orderBy('first_name')->get();

        return view('billing.create', compact('patients', 'selectedPatient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:consultation,medicine,lab_test,procedure,other',
            'items.*.description' => 'required|string|max:200',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'patient_id' => $validated['patient_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'tax_amount' => $validated['tax_amount'] ?? 0.00,
                'discount_amount' => $validated['discount_amount'] ?? 0.00,
                'subtotal' => 0.00,
                'total_amount' => 0.00,
                'paid_amount' => 0.00,
                'balance_due' => 0.00,
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $totalPrice = (float) ($item['quantity'] * $item['unit_price']);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
            }

            $invoice->recalculateTotals();

            AuditLog::log('invoice.create', "Generated invoice {$invoice->invoice_number} for {$invoice->patient->full_name}");

            return $invoice;
        });

        return redirect()->route('billing.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function show(Invoice $invoice): View
    {
        $user = Auth::user();
        if ($user->isPatient() && $invoice->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access: You may only view your own invoices.');
        }

        $invoice->load(['patient', 'consultation.doctor.user', 'items', 'payments.recordedByUser']);

        return view('billing.show', compact('invoice'));
    }

    public function print(Invoice $invoice): View
    {
        $user = Auth::user();
        if ($user->isPatient() && $invoice->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access: You may only view your own invoices.');
        }

        $invoice->load(['patient', 'consultation.doctor.user', 'items', 'payments.recordedByUser']);

        return view('billing.print', compact('invoice'));
    }

    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.($invoice->balance_due > 0 ? $invoice->balance_due : $invoice->total_amount),
            'payment_method' => 'required|in:cash,card,insurance,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_reference' => Payment::generatePaymentReference(),
                'amount' => (float) $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'recorded_by' => Auth::id(),
                'paid_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $invoice->payment_method = $validated['payment_method'];
            $invoice->recalculateTotals();

            AuditLog::log('invoice.payment', "Recorded payment {$payment->payment_reference} of \${$validated['amount']} ({$validated['payment_method']}) for invoice {$invoice->invoice_number}");
        });

        return redirect()->route('billing.show', $invoice)
            ->with('success', "Payment of \${$validated['amount']} recorded successfully.");
    }

    public function addItem(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'item_type' => 'required|in:consultation,medicine,lab_test,procedure,other',
            'description' => 'required|string|max:200',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $totalPrice = (float) ($validated['quantity'] * $validated['unit_price']);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => $validated['item_type'],
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_price' => $totalPrice,
        ]);

        $invoice->recalculateTotals();

        return back()->with('success', 'Item added to invoice.');
    }
}
