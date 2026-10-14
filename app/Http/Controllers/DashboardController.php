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
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'pending_prescriptions' => Prescription::where('status', 'pending')->count(),
            'low_stock_medicines' => Medicine::where('is_active', true)->get()->filter->isLowStock()->count(),
            'total_revenue' => Invoice::where('payment_status', 'paid')->sum('paid_amount'),
            'today_revenue' => Invoice::whereDate('updated_at', $today)->where('payment_status', 'paid')->sum('paid_amount'),
            'unpaid_invoices' => Invoice::whereIn('payment_status', ['unpaid', 'partially_paid'])->count(),
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
        $lowStockItems = Medicine::where('is_active', true)
            ->with('batches')
            ->get()
            ->filter->isLowStock()
            ->take(5);

        // Recent Audit Logs (Admin only)
        $recentLogs = $user->isAdmin() 
            ? AuditLog::with('user')->latest('id')->take(6)->get() 
            : collect();

        // Appointment trends for chart (Last 7 days)
        $appointmentTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Appointment::whereDate('appointment_date', $date)->count();
            $appointmentTrends[] = [
                'day' => $date->format('D, M d'),
                'count' => $count,
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
