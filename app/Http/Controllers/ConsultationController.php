<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\VitalSign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $search = $request->input('search');

        $consultations = Consultation::with(['patient', 'doctor.user', 'prescription'])
            ->when($search, function ($query, $search) {
                $query->where('consultation_number', 'ILIKE', "%{$search}%")
                    ->orWhere('primary_diagnosis', 'ILIKE', "%{$search}%")
                    ->orWhere('icd10_code', 'ILIKE', "%{$search}%")
                    ->orWhereHas('patient', function ($q) use ($search) {
                        $q->where('first_name', 'ILIKE', "%{$search}%")
                          ->orWhere('last_name', 'ILIKE', "%{$search}%")
                          ->orWhere('patient_code', 'ILIKE', "%{$search}%");
                    });
            })
            ->when($user->isDoctor(), function ($query) use ($user) {
                return $query->where('doctor_id', $user->doctor?->id);
            })
            ->when($user->isPatient(), function ($query) use ($user) {
                return $query->where('patient_id', $user->patient?->id);
            })
            ->latest('consultation_date')
            ->paginate(15)
            ->withQueryString();

        return view('consultations.index', compact('consultations', 'search'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $appointmentId = $request->input('appointment_id');
        $patientId = $request->input('patient_id');

        $appointment = $appointmentId ? Appointment::with(['patient', 'doctor.user'])->find($appointmentId) : null;
        $patient = $appointment ? $appointment->patient : ($patientId ? Patient::find($patientId) : null);

        if (!$patient) {
            return redirect()->route('patients.index')->with('error', 'Please select a patient to initiate consultation.');
        }

        $user = Auth::user();
        $doctors = Doctor::with('user')->where('status', 'active')->get();
        $medicines = Medicine::where('is_active', true)->with('batches')->get();
        $recentVitals = VitalSign::where('patient_id', $patient->id)->latest('recorded_at')->first();

        // Mark appointment in_consultation if coming from appointment
        if ($appointment && $appointment->status === 'scheduled') {
            $appointment->update(['status' => 'in_consultation']);
        }

        return view('consultations.create', compact('appointment', 'patient', 'doctors', 'medicines', 'recentVitals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'consultation_date' => 'required|date',
            'chief_complaint' => 'required|string',
            'history_of_present_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'primary_diagnosis' => 'required|string|max:200',
            'icd10_code' => 'nullable|string|max:50',
            'secondary_diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',

            // Vitals
            'systolic_bp' => 'nullable|integer',
            'diastolic_bp' => 'nullable|integer',
            'pulse_rate' => 'nullable|integer',
            'temperature' => 'nullable|numeric',
            'respiratory_rate' => 'nullable|integer',
            'oxygen_saturation' => 'nullable|numeric',
            'height_cm' => 'nullable|numeric',
            'weight_kg' => 'nullable|numeric',

            // Prescriptions array
            'medicines' => 'nullable|array',
            'medicines.*.medicine_id' => 'required_with:medicines|exists:medicines,id',
            'medicines.*.dosage' => 'required_with:medicines|string',
            'medicines.*.frequency' => 'required_with:medicines|string',
            'medicines.*.duration_days' => 'required_with:medicines|integer|min:1',
            'medicines.*.quantity' => 'required_with:medicines|integer|min:1',
            'medicines.*.instructions' => 'nullable|string',
        ]);

        $consultation = DB::transaction(function () use ($validated, $request) {
            $validated['consultation_number'] = Consultation::generateConsultationNumber();
            $validated['status'] = 'completed';

            $consultation = Consultation::create($validated);

            // Record Vitals if supplied
            if (!empty($validated['systolic_bp']) || !empty($validated['pulse_rate']) || !empty($validated['temperature']) || !empty($validated['weight_kg'])) {
                $bmi = VitalSign::calculateBmi(
                    !empty($validated['weight_kg']) ? (float)$validated['weight_kg'] : null,
                    !empty($validated['height_cm']) ? (float)$validated['height_cm'] : null
                );

                VitalSign::create([
                    'patient_id' => $validated['patient_id'],
                    'consultation_id' => $consultation->id,
                    'recorded_by' => Auth::id(),
                    'recorded_at' => now(),
                    'systolic_bp' => $validated['systolic_bp'] ?? null,
                    'diastolic_bp' => $validated['diastolic_bp'] ?? null,
                    'pulse_rate' => $validated['pulse_rate'] ?? null,
                    'temperature' => $validated['temperature'] ?? null,
                    'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                    'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                    'height_cm' => $validated['height_cm'] ?? null,
                    'weight_kg' => $validated['weight_kg'] ?? null,
                    'bmi' => $bmi,
                ]);
            }

            // Create Prescription and Prescription Items if medicines were prescribed
            $prescriptionItemsData = [];
            if (!empty($validated['medicines'])) {
                $prescription = Prescription::create([
                    'prescription_number' => Prescription::generatePrescriptionNumber(),
                    'consultation_id' => $consultation->id,
                    'patient_id' => $validated['patient_id'],
                    'doctor_id' => $validated['doctor_id'],
                    'status' => 'pending',
                ]);

                foreach ($validated['medicines'] as $med) {
                    if (empty($med['medicine_id'])) continue;

                    $pItem = PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $med['medicine_id'],
                        'dosage' => $med['dosage'],
                        'frequency' => $med['frequency'],
                        'duration_days' => $med['duration_days'],
                        'quantity_prescribed' => $med['quantity'],
                        'quantity_dispensed' => 0,
                        'instructions' => $med['instructions'] ?? null,
                    ]);

                    $medicine = Medicine::find($med['medicine_id']);
                    $prescriptionItemsData[] = [
                        'item_type' => 'medicine',
                        'description' => "Medication: {$medicine->name} ({$med['dosage']}, {$med['quantity']} units)",
                        'quantity' => (int)$med['quantity'],
                        'unit_price' => (float)$medicine->selling_price,
                        'total_price' => (float)($medicine->selling_price * $med['quantity']),
                    ];
                }
            }

            // Create Billing Invoice automatically
            $doctor = Doctor::find($validated['doctor_id']);
            $consultationFee = (float)($doctor->consultation_fee ?? 50.00);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'patient_id' => $validated['patient_id'],
                'consultation_id' => $consultation->id,
                'subtotal' => 0.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 0.00,
                'paid_amount' => 0.00,
                'balance_due' => 0.00,
                'payment_status' => 'unpaid',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
            ]);

            // Add consultation item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'consultation',
                'description' => "Doctor Consultation - {$doctor->name} ({$doctor->specialization})",
                'quantity' => 1,
                'unit_price' => $consultationFee,
                'total_price' => $consultationFee,
            ]);

            // Add medicine items to invoice
            foreach ($prescriptionItemsData as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                InvoiceItem::create($itemData);
            }

            $invoice->recalculateTotals();

            // Mark appointment as completed if linked
            if (!empty($validated['appointment_id'])) {
                Appointment::where('id', $validated['appointment_id'])
                    ->update(['status' => 'completed']);
            }

            AuditLog::log('consultation.create', "Completed consultation {$consultation->consultation_number} for {$consultation->patient->full_name}");

            return $consultation;
        });

        return redirect()->route('consultations.show', $consultation)
            ->with('success', "Consultation {$consultation->consultation_number} recorded successfully with prescription and invoice generated.");
    }

    public function show(Consultation $consultation): View
    {
        $consultation->load([
            'patient',
            'doctor.user',
            'appointment',
            'vitalSigns',
            'prescription.items.medicine',
            'labOrders.labTest',
            'invoice.items',
        ]);

        return view('consultations.show', compact('consultation'));
    }
}
