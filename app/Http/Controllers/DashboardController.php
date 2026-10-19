<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $today = Carbon::today();

        // High-level statistics
        $stats = [
            'total_patients' => Patient::count(),
            'new_patients_this_month' => Patient::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count(),
            'total_doctors' => Doctor::where('status', 'active')->count(),
            'today_appointments' => Appointment::whereDate('appointment_date', $today)
                ->when($user->isDoctor(), fn ($q) => $q->where('doctor_id', $user->doctor?->id))
                ->when($user->isPatient(), fn ($q) => $q->where('patient_id', $user->patient?->id))
                ->count(),
            'pending_prescriptions' => Prescription::where('status', 'pending')
                ->when($user->isDoctor(), fn ($q) => $q->where('doctor_id', $user->doctor?->id))
                ->when($user->isPatient(), fn ($q) => $q->where('patient_id', $user->patient?->id))
                ->count(),
            'low_stock_medicines' => ($user->isAdmin() || $user->hasRole('pharmacist') || $user->isDoctor())
                ? Medicine::where('is_active', true)->with('batches')->get()->filter->isLowStock()->count()
                : 0,
            'total_revenue' => ($user->isAdmin() || $user->hasRole('receptionist'))
                ? Invoice::where('payment_status', 'paid')->sum('paid_amount')
                : ($user->isPatient() ? Invoice::where('patient_id', $user->patient?->id)->where('payment_status', 'paid')->sum('paid_amount') : 0),
            'today_revenue' => ($user->isAdmin() || $user->hasRole('receptionist'))
                ? Invoice::whereDate('updated_at', $today)->where('payment_status', 'paid')->sum('paid_amount')
                : ($user->isPatient() ? Invoice::where('patient_id', $user->patient?->id)->whereDate('updated_at', $today)->where('payment_status', 'paid')->sum('paid_amount') : 0),
            'unpaid_invoices' => $user->isPatient()
                ? Invoice::where('patient_id', $user->patient?->id)->whereIn('payment_status', ['unpaid', 'partially_paid'])->count()
                : Invoice::whereIn('payment_status', ['unpaid', 'partially_paid'])->count(),
        ];

        // Today's appointments queue
        $todayAppointments = Appointment::with(['patient', 'doctor.user'])
            ->whereDate('appointment_date', $today)
            ->when($user->isDoctor(), function ($query) use ($user) {
                return $query->where('doctor_id', $user->doctor?->id);
            })
            ->when($user->isPatient(), function ($query) use ($user) {
                return $query->where('patient_id', $user->patient?->id);
            })
            ->orderBy('appointment_time')
            ->take(10)
            ->get();

        // Recent consultations
        $recentConsultations = Consultation::with(['patient', 'doctor.user'])
            ->when($user->isDoctor(), function ($query) use ($user) {
                return $query->where('doctor_id', $user->doctor?->id);
            })
            ->when($user->isPatient(), function ($query) use ($user) {
                return $query->where('patient_id', $user->patient?->id);
            })
            ->latest('consultation_date')
            ->take(5)
            ->get();

        // Low stock alerts
        $lowStockItems = ($user->isAdmin() || $user->hasRole('pharmacist') || $user->isDoctor())
            ? Medicine::where('is_active', true)
                ->with('batches')
                ->get()
                ->filter->isLowStock()
                ->take(5)
            : collect();

        // Recent Audit Logs (Admin only)
        $recentLogs = $user->isAdmin()
            ? AuditLog::with('user')->latest('id')->take(6)->get()
            : collect();

        // Appointment trends for chart (Last 7 days - single grouped query)
        $startDate = Carbon::today()->subDays(6)->toDateString();
        $endDate = Carbon::today()->toDateString();

        $countsByDate = Appointment::whereDate('appointment_date', '>=', $startDate)
            ->whereDate('appointment_date', '<=', $endDate)
            ->when($user->isDoctor(), fn ($q) => $q->where('doctor_id', $user->doctor?->id))
            ->when($user->isPatient(), fn ($q) => $q->where('patient_id', $user->patient?->id))
            ->selectRaw('DATE(appointment_date) as day_date, count(*) as count')
            ->groupByRaw('DATE(appointment_date)')
            ->pluck('count', 'day_date');

        $appointmentTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $key = $date->toDateString();
            $appointmentTrends[] = [
                'day' => $date->format('D, M d'),
                'count' => (int) ($countsByDate[$key] ?? 0),
            ];
        }

        return view('dashboard.index', compact(
            'stats',
            'todayAppointments',
            'recentConsultations',
            'lowStockItems',
            'recentLogs',
            'appointmentTrends'
        ));
    }
}
