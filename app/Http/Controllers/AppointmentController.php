<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $date = $request->input('date');
        $status = $request->input('status');
        $doctorId = $request->input('doctor_id');

        $appointments = Appointment::with(['patient', 'doctor.user'])
            ->when($date, fn($q) => $q->whereDate('appointment_date', $date))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($doctorId, fn($q) => $q->where('doctor_id', $doctorId))
            ->when($user->isDoctor(), function ($query) use ($user) {
                return $query->where('doctor_id', $user->doctor?->id);
            })
            ->when($user->isPatient(), function ($query) use ($user) {
                return $query->where('patient_id', $user->patient?->id);
            })
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::with('user')->where('status', 'active')->get();

        return view('appointments.index', compact('appointments', 'doctors', 'date', 'status', 'doctorId'));
    }

    public function create(Request $request): View
    {
        $patientId = $request->input('patient_id');
        $selectedPatient = $patientId ? Patient::find($patientId) : null;
        $patients = Patient::orderBy('first_name')->get();
        $doctors = Doctor::with('user')->where('status', 'active')->get();

        return view('appointments.create', compact('patients', 'doctors', 'selectedPatient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'type' => 'required|in:routine,consultation,follow_up,emergency',
            'priority' => 'required|in:normal,urgent,emergency',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['appointment_number'] = Appointment::generateAppointmentNumber();
        $validated['status'] = 'scheduled';

        $appointment = Appointment::create($validated);

        AuditLog::log('appointment.create', "Booked appointment {$appointment->appointment_number} for {$appointment->patient->full_name}");

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "Appointment {$appointment->appointment_number} booked successfully.");
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor.user', 'consultation']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View
    {
        $patients = Patient::orderBy('first_name')->get();
        $doctors = Doctor::with('user')->where('status', 'active')->get();

        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'type' => 'required|in:routine,consultation,follow_up,emergency',
            'priority' => 'required|in:normal,urgent,emergency',
            'status' => 'required|in:scheduled,checked_in,in_consultation,completed,cancelled,no_show',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'cancellation_reason' => 'nullable|string',
        ]);

        $appointment->update($validated);

        AuditLog::log('appointment.update', "Updated appointment {$appointment->appointment_number}");

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,checked_in,in_consultation,completed,cancelled,no_show',
            'cancellation_reason' => 'nullable|string',
        ]);

        $appointment->update($validated);

        AuditLog::log('appointment.status_change', "Changed status of {$appointment->appointment_number} to {$validated['status']}");

        return back()->with('success', "Status updated to " . ucfirst(str_replace('_', ' ', $validated['status'])));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $num = $appointment->appointment_number;
        $appointment->delete();

        AuditLog::log('appointment.delete', "Deleted appointment {$num}");

        return redirect()->route('appointments.index')->with('success', "Appointment {$num} removed.");
    }
}
