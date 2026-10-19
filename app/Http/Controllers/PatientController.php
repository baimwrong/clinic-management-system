<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $patients = Patient::query()
            ->when($search, function ($query, $search) {
                $term = '%'.strtolower($search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(patient_code) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(phone) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(national_id) LIKE ?', [$term]);
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients', 'search'));
    }

    public function create(): View
    {
        $suggestedCode = Patient::generatePatientCode();

        return view('patients.create', compact('suggestedCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'national_id' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_policy_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['patient_code'] = Patient::generatePatientCode();

        $patient = Patient::create($validated);

        AuditLog::log('patient.create', "Registered new patient: {$patient->full_name} ({$patient->patient_code})");

        return redirect()->route('patients.show', $patient)
            ->with('success', "Patient {$patient->full_name} successfully registered.");
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'vitalSigns' => fn ($q) => $q->latest('recorded_at')->take(10),
            'appointments' => fn ($q) => $q->with('doctor.user')->latest('appointment_date'),
            'consultations' => fn ($q) => $q->with(['doctor.user', 'prescription.items.medicine', 'vitalSigns'])->latest('consultation_date'),
            'prescriptions' => fn ($q) => $q->with(['doctor.user', 'items.medicine'])->latest('id'),
            'labOrders' => fn ($q) => $q->with(['doctor.user', 'labTest'])->latest('order_date'),
            'invoices' => fn ($q) => $q->with('items')->latest('invoice_date'),
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'national_id' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_policy_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $patient->update($validated);

        AuditLog::log('patient.update', "Updated patient record: {$patient->full_name} ({$patient->patient_code})");

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient details updated successfully.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $name = $patient->full_name;
        $code = $patient->patient_code;

        $patient->delete();

        AuditLog::log('patient.delete', "Deleted patient record: {$name} ({$code})");

        return redirect()->route('patients.index')
            ->with('success', "Patient {$name} ({$code}) was removed successfully.");
    }
}
