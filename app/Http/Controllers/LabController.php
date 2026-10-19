<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LabController extends Controller
{
    public function index(): View
    {
        $tests = LabTest::orderBy('category')->orderBy('name')->get();

        return view('lab.tests.index', compact('tests'));
    }

    public function createTest(): View
    {
        return view('lab.tests.create');
    }

    public function storeTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:lab_tests,code',
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'reference_range' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $test = LabTest::create($validated);

        AuditLog::log('lab_test.create', "Cataloged lab test: {$test->name} ({$test->code})");

        return redirect()->route('lab.tests.index')->with('success', 'Lab test added successfully.');
    }

    public function orders(Request $request): View
    {
        $status = $request->input('status');

        $orders = LabOrder::with(['patient', 'doctor.user', 'labTest'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        return view('lab.orders.index', compact('orders', 'status'));
    }

    public function createOrder(Request $request): View
    {
        $patientId = $request->input('patient_id');
        $selectedPatient = $patientId ? Patient::find($patientId) : null;
        $patients = Patient::orderBy('first_name')->get();
        $doctors = Doctor::with('user')->where('status', 'active')->get();
        $tests = LabTest::orderBy('name')->get();

        return view('lab.orders.create', compact('patients', 'selectedPatient', 'doctors', 'tests'));
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'order_date' => 'required|date',
            'technician_notes' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $validated['order_number'] = LabOrder::generateOrderNumber();
            $validated['status'] = 'ordered';

            $order = LabOrder::create($validated);

            // Add test to patient's invoice or create a new invoice so revenue is not lost
            $labTest = LabTest::findOrFail($validated['lab_test_id']);
            $latestUnpaidInvoice = Invoice::where('patient_id', $validated['patient_id'])
                ->where('payment_status', 'unpaid')
                ->latest('id')
                ->first();

            if (! $latestUnpaidInvoice) {
                $latestUnpaidInvoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'patient_id' => $validated['patient_id'],
                    'consultation_id' => $validated['consultation_id'] ?? null,
                    'invoice_date' => now()->toDateString(),
                    'payment_status' => 'unpaid',
                    'subtotal' => 0.00,
                    'tax_amount' => 0.00,
                    'discount_amount' => 0.00,
                    'total_amount' => 0.00,
                    'paid_amount' => 0.00,
                    'balance_due' => 0.00,
                    'notes' => 'Auto-generated invoice for laboratory test order',
                ]);
            }

            InvoiceItem::create([
                'invoice_id' => $latestUnpaidInvoice->id,
                'item_type' => 'lab_test',
                'description' => "Laboratory Test: {$labTest->name} ({$labTest->code})",
                'quantity' => 1,
                'unit_price' => $labTest->price,
                'total_price' => $labTest->price,
            ]);
            $latestUnpaidInvoice->recalculateTotals();

            AuditLog::log('lab_order.create', "Ordered lab test {$labTest->name} for {$order->patient->full_name}");

            return $order;
        });

        return redirect()->route('lab.orders.show', $order)
            ->with('success', "Lab order {$order->order_number} created successfully.");
    }

    public function showOrder(LabOrder $labOrder): View
    {
        $user = auth()->user();
        if ($user && $user->isPatient() && $labOrder->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access: You may only view your own laboratory orders.');
        }

        $labOrder->load(['patient', 'doctor.user', 'labTest', 'consultation']);

        return view('lab.orders.show', compact('labOrder'));
    }

    public function recordResult(Request $request, LabOrder $labOrder): RedirectResponse
    {
        $validated = $request->validate([
            'result_value' => 'required|string',
            'result_flag' => 'required|in:normal,abnormal,critical',
            'technician_notes' => 'nullable|string',
        ]);

        $labOrder->update([
            'result_value' => $validated['result_value'],
            'result_flag' => $validated['result_flag'],
            'technician_notes' => $validated['technician_notes'] ?? null,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        AuditLog::log('lab_order.result', "Recorded lab result for {$labOrder->order_number} (Flag: {$validated['result_flag']})");

        return redirect()->route('lab.orders.show', $labOrder)
            ->with('success', 'Laboratory test result recorded successfully.');
    }
}
