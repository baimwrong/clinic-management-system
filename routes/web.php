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

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Patient Management
    Route::resource('patients', PatientController::class);

    // Doctor Management
    Route::resource('doctors', DoctorController::class);

    // Appointment Management
    Route::resource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');

    // EMR Consultations
    Route::resource('consultations', ConsultationController::class);

    // Vital Signs
    Route::get('/vitals', [VitalSignController::class, 'index'])->name('vitals.index');
    Route::get('/vitals/create', [VitalSignController::class, 'create'])->name('vitals.create');
    Route::post('/vitals', [VitalSignController::class, 'store'])->name('vitals.store');
    Route::delete('/vitals/{vital_sign}', [VitalSignController::class, 'destroy'])->name('vitals.destroy');

    // Pharmacy & Inventory
    Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
        Route::resource('medicines', PharmacyController::class);
        Route::post('/medicines/{medicine}/batches', [PharmacyController::class, 'storeBatch'])->name('medicines.batches.store');
        Route::get('/prescriptions', [PharmacyController::class, 'prescriptions'])->name('prescriptions.index');
        Route::get('/prescriptions/{prescription}', [PharmacyController::class, 'showPrescription'])->name('prescriptions.show');
        Route::post('/prescriptions/{prescription}/dispense', [PharmacyController::class, 'dispensePrescription'])->name('prescriptions.dispense');
    });

    // Billing & Invoices
    Route::resource('billing', BillingController::class);
    Route::get('/billing/{invoice}/print', [BillingController::class, 'print'])->name('billing.print');
    Route::post('/billing/{invoice}/payments', [BillingController::class, 'recordPayment'])->name('billing.payments.store');
    Route::post('/billing/{invoice}/items', [BillingController::class, 'addItem'])->name('billing.items.store');

    // Laboratory & Diagnostics
    Route::prefix('lab')->as('lab.')->group(function () {
        Route::get('/tests', [LabController::class, 'index'])->name('tests.index');
        Route::get('/tests/create', [LabController::class, 'createTest'])->name('tests.create');
        Route::post('/tests', [LabController::class, 'storeTest'])->name('tests.store');

        Route::get('/orders', [LabController::class, 'orders'])->name('orders.index');
        Route::get('/orders/create', [LabController::class, 'createOrder'])->name('orders.create');
        Route::post('/orders', [LabController::class, 'storeOrder'])->name('orders.store');
        Route::get('/orders/{lab_order}', [LabController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{lab_order}/results', [LabController::class, 'recordResult'])->name('orders.results.store');
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
