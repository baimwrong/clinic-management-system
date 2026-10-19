<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $filter = $request->input('filter'); // 'low_stock', 'expiring'

        $medicines = Medicine::with('batches')
            ->when($search, function ($query, $search) {
                $term = '%'.strtolower($search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(generic_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(code) LIKE ?', [$term]);
                });
            })
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Medicine::select('category')->distinct()->pluck('category');

        return view('pharmacy.medicines.index', compact('medicines', 'search', 'category', 'categories', 'filter'));
    }

    public function create(): View
    {
        $suggestedCode = Medicine::generateMedicineCode();

        return view('pharmacy.medicines.create', compact('suggestedCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'generic_name' => 'nullable|string|max:150',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'dosage_form' => 'nullable|string|max:50',
            'manufacturer' => 'nullable|string|max:150',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['code'] = Medicine::generateMedicineCode();
        $validated['is_active'] = true;

        $medicine = Medicine::create($validated);

        AuditLog::log('medicine.create', "Added medicine: {$medicine->name} ({$medicine->code})");

        return redirect()->route('pharmacy.medicines.show', $medicine)
            ->with('success', "Medicine {$medicine->name} cataloged successfully.");
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load(['batches' => fn ($q) => $q->orderBy('expiry_date')]);

        return view('pharmacy.medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine): View
    {
        return view('pharmacy.medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'generic_name' => 'nullable|string|max:150',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'dosage_form' => 'nullable|string|max:50',
            'manufacturer' => 'nullable|string|max:150',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        $medicine->update($validated);

        AuditLog::log('medicine.update', "Updated medicine: {$medicine->name}");

        return redirect()->route('pharmacy.medicines.show', $medicine)
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $name = $medicine->name;
        $medicine->update(['is_active' => false]);

        AuditLog::log('medicine.deactivate', "Deactivated medicine: {$name}");

        return redirect()->route('pharmacy.medicines.index')
            ->with('success', "Medicine {$name} deactivated.");
    }

    // Batches
    public function storeBatch(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'supplier' => 'nullable|string|max:150',
        ]);

        $validated['medicine_id'] = $medicine->id;
        $validated['remaining_quantity'] = $validated['quantity'];

        MedicineBatch::create($validated);

        AuditLog::log('medicine_batch.add', "Added stock batch {$validated['batch_number']} ({$validated['quantity']} units) for {$medicine->name}");

        return back()->with('success', 'Stock batch added successfully.');
    }

    // Prescriptions queue & dispensing
    public function prescriptions(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $prescriptions = Prescription::with(['patient', 'doctor.user', 'items.medicine.batches'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('pharmacy.prescriptions.index', compact('prescriptions', 'status'));
    }

    public function showPrescription(Prescription $prescription): View
    {
        $user = auth()->user();
        if ($user && $user->isPatient() && $prescription->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access: You may only view your own prescriptions.');
        }

        $prescription->load(['patient', 'doctor.user', 'items.medicine.batches', 'consultation']);

        return view('pharmacy.prescriptions.show', compact('prescription'));
    }

    public function dispensePrescription(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->status === 'dispensed') {
            return back()->with('error', 'This prescription has already been dispensed.');
        }

        DB::beginTransaction();
        try {
            foreach ($prescription->items as $item) {
                $needed = $item->quantity_prescribed;
                $medicine = $item->medicine;

                // Find non-expired batches ordered by earliest expiry date (FEFO principle: First-Expired First-Out)
                $batches = MedicineBatch::where('medicine_id', $medicine->id)
                    ->where('expiry_date', '>', now())
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                $totalAvailable = $batches->sum('remaining_quantity');
                if ($totalAvailable < $needed) {
                    DB::rollBack();

                    return back()->with('error', "Insufficient stock for {$medicine->name}. Required: {$needed}, Available in active batches: {$totalAvailable}. Please restock before dispensing.");
                }

                $stillNeeded = $needed;
                foreach ($batches as $batch) {
                    if ($stillNeeded <= 0) {
                        break;
                    }

                    if ($batch->remaining_quantity >= $stillNeeded) {
                        $batch->remaining_quantity -= $stillNeeded;
                        $batch->save();
                        $stillNeeded = 0;
                    } else {
                        $stillNeeded -= $batch->remaining_quantity;
                        $batch->remaining_quantity = 0;
                        $batch->save();
                    }
                }

                $item->update(['quantity_dispensed' => $needed]);
            }

            $prescription->update([
                'status' => 'dispensed',
                'dispensed_by' => Auth::id(),
                'dispensed_at' => now(),
                'notes' => $request->input('notes'),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Dispensing failed: '.$e->getMessage());
        }

        AuditLog::log('prescription.dispense', "Dispensed prescription {$prescription->prescription_number} for {$prescription->patient->full_name}");

        return redirect()->route('pharmacy.prescriptions.show', $prescription)
            ->with('success', "Prescription {$prescription->prescription_number} successfully dispensed and inventory deducted.");
    }
}
