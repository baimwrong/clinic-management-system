<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes with Rate Limiting
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Dashboard (accessible to all authenticated users, UI scoped to role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Doctor & Staff Management
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::middleware('role:admin')->group(function () {
        Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
        Route::post('/doctors', [DoctorController::class, 'store'])->name('doctors.store');
        Route::get('/doctors/{doctor}/edit', [DoctorController::class, 'edit'])->name('doctors.edit');
        Route::put('/doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
        Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy'])->name('doctors.destroy');
    });
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.show');

    // Patient Management
    Route::middleware('role:admin,doctor,nurse,receptionist')->group(function () {
        Route::resource('patients', PatientController::class)->except(['destroy']);
    });
    Route::middleware('role:admin')->group(function () {
        Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
    });

    // Appointment Management
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::middleware('role:admin,receptionist')->group(function () {
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    });

    // EMR Consultations
    Route::middleware('role:admin,doctor')->group(function () {
        Route::get('/consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
        Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
        Route::get('/consultations/{consultation}/edit', [ConsultationController::class, 'edit'])->name('consultations.edit');
        Route::put('/consultations/{consultation}', [ConsultationController::class, 'update'])->name('consultations.update');
        Route::delete('/consultations/{consultation}', [ConsultationController::class, 'destroy'])->name('consultations.destroy');
    });
    Route::middleware('role:admin,doctor,nurse,patient')->group(function () {
        Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('/consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
    });

    // Vital Signs & Triage
    Route::middleware('role:admin,doctor,nurse')->group(function () {
        Route::get('/vitals', [VitalSignController::class, 'index'])->name('vitals.index');
        Route::get('/vitals/create', [VitalSignController::class, 'create'])->name('vitals.create');
        Route::post('/vitals', [VitalSignController::class, 'store'])->name('vitals.store');
    });
    Route::middleware('role:admin')->group(function () {
        Route::delete('/vitals/{vital_sign}', [VitalSignController::class, 'destroy'])->name('vitals.destroy');
    });

    // Pharmacy & Inventory
    Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
        // Prescription Queue & Dispensing
        Route::middleware('role:admin,pharmacist,doctor')->group(function () {
            Route::get('/prescriptions', [PharmacyController::class, 'prescriptions'])->name('prescriptions.index');
            Route::get('/prescriptions/{prescription}', [PharmacyController::class, 'showPrescription'])->name('prescriptions.show');
        });
        Route::middleware('role:admin,pharmacist')->group(function () {
            Route::resource('medicines', PharmacyController::class);
            Route::post('/medicines/{medicine}/batches', [PharmacyController::class, 'storeBatch'])->name('medicines.batches.store');
            Route::post('/prescriptions/{prescription}/dispense', [PharmacyController::class, 'dispensePrescription'])->name('prescriptions.dispense');
        });
    });

    // Billing & Invoices
    Route::middleware('role:admin,receptionist')->group(function () {
        Route::get('/billing/create', [BillingController::class, 'create'])->name('billing.create');
        Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
        Route::get('/billing/{invoice}/edit', [BillingController::class, 'edit'])->name('billing.edit');
        Route::put('/billing/{invoice}', [BillingController::class, 'update'])->name('billing.update');
        Route::delete('/billing/{invoice}', [BillingController::class, 'destroy'])->name('billing.destroy');
        Route::post('/billing/{invoice}/payments', [BillingController::class, 'recordPayment'])->name('billing.payments.store');
        Route::post('/billing/{invoice}/items', [BillingController::class, 'addItem'])->name('billing.items.store');
    });
    Route::middleware('role:admin,receptionist,doctor,patient')->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/{invoice}', [BillingController::class, 'show'])->name('billing.show');
        Route::get('/billing/{invoice}/print', [BillingController::class, 'print'])->name('billing.print');
    });

    // Laboratory & Diagnostics
    Route::prefix('lab')->as('lab.')->group(function () {
        Route::get('/tests', [LabController::class, 'index'])->name('tests.index');
        Route::middleware('role:admin,doctor')->group(function () {
            Route::get('/tests/create', [LabController::class, 'createTest'])->name('tests.create');
            Route::post('/tests', [LabController::class, 'storeTest'])->name('tests.store');
            Route::get('/orders/create', [LabController::class, 'createOrder'])->name('orders.create');
            Route::post('/orders', [LabController::class, 'storeOrder'])->name('orders.store');
            Route::post('/orders/{lab_order}/results', [LabController::class, 'recordResult'])->name('orders.results.store');
        });
        Route::middleware('role:admin,doctor,nurse,patient')->group(function () {
            Route::get('/orders', [LabController::class, 'orders'])->name('orders.index');
            Route::get('/orders/{lab_order}', [LabController::class, 'showOrder'])->name('orders.show');
        });
    });

    // Admin Exclusive Routes
    Route::middleware('role:admin')->prefix('admin')->as('admin.')->group(function () {
        // Bulk Import Data Hub
        Route::get('/bulk-import', [BulkImportController::class, 'index'])->name('bulk-import.index');
        Route::get('/bulk-import/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('bulk-import.template');
        Route::post('/bulk-import', [BulkImportController::class, 'import'])->name('bulk-import.process');
        Route::get('/bulk-import/{bulk_import}', [BulkImportController::class, 'show'])->name('bulk-import.show');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
