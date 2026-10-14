<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VitalSignController extends Controller
{
    public function index(Request $request): View
    {
        $patientId = $request->input('patient_id');

        $vitals = VitalSign::with(['patient', 'recordedByUser', 'consultation'])
            ->when($patientId, fn($q) => $q->where('patient_id', $patientId))
            ->latest('recorded_at')
            ->paginate(20)
            ->withQueryString();

        $patients = Patient::orderBy('first_name')->get();

        return view('vitals.index', compact('vitals', 'patients', 'patientId'));
    }

    public function create(Request $request): View
    {
        $patientId = $request->input('patient_id');
        $patient = $patientId ? Patient::find($patientId) : null;
        $patients = Patient::orderBy('first_name')->get();

        return view('vitals.create', compact('patients', 'patient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'recorded_at' => 'required|date',
            'systolic_bp' => 'nullable|integer|between:50,260',
            'diastolic_bp' => 'nullable|integer|between:30,160',
            'pulse_rate' => 'nullable|integer|between:30,220',
            'temperature' => 'nullable|numeric|between:30,45',
            'respiratory_rate' => 'nullable|integer|between:8,60',
            'oxygen_saturation' => 'nullable|numeric|between:50,100',
            'height_cm' => 'nullable|numeric|between:20,260',
            'weight_kg' => 'nullable|numeric|between:1,300',
            'notes' => 'nullable|string',
        ]);

        $validated['recorded_by'] = Auth::id();
        $validated['bmi'] = VitalSign::calculateBmi(
            !empty($validated['weight_kg']) ? (float)$validated['weight_kg'] : null,
            !empty($validated['height_cm']) ? (float)$validated['height_cm'] : null
        );

        $vital = VitalSign::create($validated);

        AuditLog::log('vital_signs.record', "Recorded vital signs for {$vital->patient->full_name}");

        return redirect()->route('patients.show', $vital->patient_id)
            ->with('success', 'Vital signs recorded successfully.');
    }

    public function destroy(VitalSign $vitalSign): RedirectResponse
    {
        $patientId = $vitalSign->patient_id;
        $vitalSign->delete();

        AuditLog::log('vital_signs.delete', "Deleted vital signs entry for patient ID {$patientId}");

        return back()->with('success', 'Vital sign entry removed.');
    }
}
