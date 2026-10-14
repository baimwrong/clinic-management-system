@extends('layouts.app')

@section('title', 'Admin Bulk Data Import Center')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-cloud-arrow-up text-warning me-2"></i> Admin Bulk Data Import Center
            </h3>
            <p class="text-muted small mb-0">High-throughput CSV bulk onboarding for Patients, Pharmaceutical Catalog & Stock, and Medical Staff.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2">
                <i class="bi bi-filetype-csv me-1"></i> Standard CSV &bull; UTF-8
            </span>
        </div>
    </div>

    <!-- Download Sample Templates Section -->
    <div class="card card-clinic mb-4 border-warning border-opacity-50">
        <div class="card-header bg-warning bg-opacity-10 py-3 d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark">
                <i class="bi bi-download me-2 text-warning"></i> Step 1: Download Standard Pre-formatted CSV Templates
            </span>
            <small class="text-muted">Use these templates to ensure columns align perfectly</small>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 bg-light h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-people-fill fs-4 text-primary"></i>
                                <span class="fw-bold">Patients Template</span>
                            </div>
                            <p class="small text-muted mb-3">
                                Includes demographics, contact details, blood group, allergies, chronic conditions, and emergency contacts.
                            </p>
                        </div>
                        <a href="{{ route('admin.bulk-import.template', 'patients') }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Patients.csv
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3 bg-light h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-capsule-pill fs-4 text-success"></i>
                                <span class="fw-bold">Medicines & Stock Template</span>
                            </div>
                            <p class="small text-muted mb-3">
                                Includes medicine catalog (brand, generic, category, unit, prices) plus initial batch numbers, quantities & expiry dates.
                            </p>
                        </div>
                        <a href="{{ route('admin.bulk-import.template', 'medicines') }}" class="btn btn-outline-success btn-sm w-100">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Medicines.csv
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3 bg-light h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-badge fs-4 text-info"></i>
                                <span class="fw-bold">Staff & Doctors Template</span>
                            </div>
                            <p class="small text-muted mb-3">
                                Onboard doctors, nurses, pharmacists, and receptionists with credentials, specializations, and license numbers.
                            </p>
                        </div>
                        <a href="{{ route('admin.bulk-import.template', 'staff') }}" class="btn btn-outline-info btn-sm w-100 text-dark">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Staff.csv
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Upload CSV Section -->
    <div class="card card-clinic mb-4">
        <div class="card-header-clinic">
            <span><i class="bi bi-upload text-primary me-2"></i> Step 2: Select Dataset & Upload File</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.bulk-import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">1. Choose Dataset Type</label>
                        <select name="import_type" id="importTypeSelect" class="form-select form-select-lg" required>
                            <option value="patients" selected>Patients Records (Demographics & Medical History)</option>
                            <option value="medicines">Pharmacy Inventory (Medicines & Batch Stock)</option>
                            <option value="staff">Clinical Staff & Doctors (User Accounts & Credentials)</option>
                        </select>
                        <div class="form-text mt-2 small text-muted">
                            The system will automatically validate headers, row structures, data types, and transactional consistency.
                        </div>
                    </div>

                    <div class="col-md-7">
                        <label class="form-label fw-semibold small">2. Upload CSV File</label>
                        <div class="input-group input-group-lg">
                            <input type="file" name="file" class="form-control" accept=".csv,text/csv,text/plain" required>
                            <button type="submit" class="btn btn-clinic px-4 fw-semibold">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Run Bulk Import
                            </button>
                        </div>
                        <div class="form-text mt-2 small text-muted">
                            Supported format: <code>.csv</code> (Comma Separated Values) up to 10 MB.
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Step 3: Past Bulk Imports History -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-clock-history text-secondary me-2"></i> Bulk Import Audit Trail & Execution Logs</span>
            <span class="badge bg-light text-dark border">{{ $imports->total() }} Total Import Operations</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Admin User</th>
                        <th>Import Dataset</th>
                        <th>Source File</th>
                        <th>Total Rows</th>
                        <th>Successful</th>
                        <th>Failed</th>
                        <th>Execution Status</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $imp)
                        <tr>
                            <td class="text-secondary small">
                                {{ $imp->created_at->format('M d, Y h:i A') }}
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $imp->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="fw-semibold text-dark">{{ $imp->admin->name }}</td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.75rem;">
                                    {{ $imp->import_type }}
                                </span>
                            </td>
                            <td class="font-monospace small text-truncate" style="max-width: 180px;" title="{{ $imp->filename }}">
                                {{ $imp->filename }}
                            </td>
                            <td class="fw-bold">{{ number_format($imp->total_rows) }}</td>
                            <td>
                                <span class="text-success fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i>{{ number_format($imp->successful_rows) }}
                                </span>
                            </td>
                            <td>
                                @if($imp->failed_rows > 0)
                                    <span class="text-danger fw-semibold">
                                        <i class="bi bi-x-circle me-1"></i>{{ number_format($imp->failed_rows) }}
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $imp->status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $imp->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.bulk-import.show', $imp) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                    <i class="bi bi-search me-1"></i> Review Log
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-cloud-arrow-up fs-2 d-block mb-2 text-secondary"></i>
                                No bulk data imports recorded yet. Download a sample CSV template above to begin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($imports->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $imports->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
