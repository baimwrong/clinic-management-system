<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $specialization = $request->input('specialization');

        $doctors = Doctor::with('user')
            ->when($search, function ($query, $search) {
                $term = '%'.strtolower($search).'%';
                $query->where(function ($sub) use ($term) {
                    $sub->whereHas('user', function ($q) use ($term) {
                        $q->whereRaw('LOWER(name) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                    })->orWhereRaw('LOWER(staff_id) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(specialization) LIKE ?', [$term]);
                });
            })
            ->when($specialization, function ($query, $spec) {
                $query->where('specialization', $spec);
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $specializations = Doctor::select('specialization')
            ->distinct()
            ->pluck('specialization');

        return view('doctors.index', compact('doctors', 'search', 'specialization', 'specializations'));
    }

    public function create(): View
    {
        return view('doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
            'specialization' => 'required|string|max:100',
            'license_number' => 'required|string|max:100|unique:doctors,license_number',
            'qualification' => 'nullable|string|max:150',
            'consultation_fee' => 'required|numeric|min:0',
            'room_number' => 'nullable|string|max:50',
            'bio' => 'nullable|string',
            'available_days' => 'nullable|array',
            'available_time_start' => 'required',
            'available_time_end' => 'required',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'doctor',
                'phone' => $validated['phone'] ?? null,
                'status' => 'active',
            ]);

            $staffId = 'DOC-'.sprintf('%04d', $user->id);

            Doctor::create([
                'user_id' => $user->id,
                'staff_id' => $staffId,
                'specialization' => $validated['specialization'],
                'license_number' => $validated['license_number'],
                'qualification' => $validated['qualification'] ?? null,
                'consultation_fee' => $validated['consultation_fee'],
                'room_number' => $validated['room_number'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'available_days' => $validated['available_days'] ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'available_time_start' => $validated['available_time_start'],
                'available_time_end' => $validated['available_time_end'],
                'status' => 'active',
            ]);

            AuditLog::log('doctor.create', "Added doctor {$user->name} ({$staffId})");
        });

        return redirect()->route('doctors.index')->with('success', 'Doctor profile created successfully.');
    }

    public function show(Doctor $doctor): View
    {
        $doctor->load([
            'user',
            'appointments' => fn ($q) => $q->with('patient')->latest('appointment_date')->take(10),
            'consultations' => fn ($q) => $q->with('patient')->latest('consultation_date')->take(10),
        ]);

        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor): View
    {
        $doctor->load('user');

        return view('doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => "required|email|unique:users,email,{$doctor->user_id}",
            'phone' => 'nullable|string|max:30',
            'specialization' => 'required|string|max:100',
            'license_number' => "required|string|max:100|unique:doctors,license_number,{$doctor->id}",
            'qualification' => 'nullable|string|max:150',
            'consultation_fee' => 'required|numeric|min:0',
            'room_number' => 'nullable|string|max:50',
            'bio' => 'nullable|string',
            'available_days' => 'nullable|array',
            'available_time_start' => 'required',
            'available_time_end' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($doctor, $validated, $request) {
            $doctor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);

            if ($request->filled('password')) {
                $doctor->user->update(['password' => Hash::make($request->password)]);
            }

            $doctor->update([
                'specialization' => $validated['specialization'],
                'license_number' => $validated['license_number'],
                'qualification' => $validated['qualification'] ?? null,
                'consultation_fee' => $validated['consultation_fee'],
                'room_number' => $validated['room_number'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'available_days' => $validated['available_days'] ?? [],
                'available_time_start' => $validated['available_time_start'],
                'available_time_end' => $validated['available_time_end'],
                'status' => $validated['status'],
            ]);

            AuditLog::log('doctor.update', "Updated doctor details for {$doctor->user->name}");
        });

        return redirect()->route('doctors.show', $doctor)->with('success', 'Doctor details updated successfully.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $name = $doctor->name;
        $doctor->update(['status' => 'inactive']);
        $doctor->user->update(['status' => 'inactive']);

        AuditLog::log('doctor.deactivate', "Deactivated doctor profile: {$name}");

        return redirect()->route('doctors.index')->with('success', "Doctor {$name} was deactivated.");
    }
}
