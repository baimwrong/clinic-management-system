@extends('layouts.app')

@section('title', 'Bulk Import Execution Report')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('admin.bulk-import.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                &larr; Back to Bulk Import Center
            </a>
            <h3 class="fw-bold mb-0">Import Operation #{{ $bulkImport->id }} Report</h3>
        </div>
        <span class="badge {{ $bulkImport->status_badge_class }} px-3 py-2 fs-6">
            {{ ucfirst(str_replace('_', ' ', $bulkImport->status)) }}
        </span>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card card-clinic p-3 text-center">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Dataset Type</div>
                <div class="fs-5 fw-bold text-dark text-capitalize">{{ $bulkImport->import_type }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-clinic p-3 text-center">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Total Rows In File</div>
                <div class="fs-5 fw-bold text-dark">{{ number_format($bulkImport->total_rows) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-clinic p-3 text-center bg-success bg-opacity-10 border-success border-opacity-25">
                <div class="text-success small text-uppercase fw-semibold mb-1">Successfully Inserted</div>
                <div class="fs-5 fw-bold text-success">{{ number_format($bulkImport->successful_rows) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-clinic p-3 text-center {{ $bulkImport->failed_rows > 0 ? 'bg-danger bg-opacity-10 border-danger border-opacity-25' : '' }}">
                <div class="{{ $bulkImport->failed_rows > 0 ? 'text-danger' : 'text-muted' }} small text-uppercase fw-semibold mb-1">Failed Rows</div>
                <div class="fs-5 fw-bold {{ $bulkImport->failed_rows > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($bulkImport->failed_rows) }}</div>
            </div>
        </div>
    </div>

    <!-- Metadata Details -->
    <div class="card card-clinic mb-4">
        <div class="card-header-clinic">
            <span><i class="bi bi-info-circle text-primary me-2"></i> File & Execution Metadata</span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <span class="text-muted small d-block">Source Filename:</span>
                    <span class="fw-semibold font-monospace">{{ $bulkImport->filename }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small d-block">Executed By Admin:</span>
                    <span class="fw-semibold">{{ $bulkImport->admin->name }} ({{ $bulkImport->admin->email }})</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small d-block">Timestamp:</span>
                    <span class="fw-semibold">{{ $bulkImport->created_at->format('Y-m-d H:i:s T') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Breakdown (If any) -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span>
                <i class="bi bi-exclamation-octagon text-danger me-2"></i> 
                Row-by-Row Error Log 
                @if($bulkImport->failed_rows > 0)
                    <span class="badge bg-danger ms-2">{{ $bulkImport->failed_rows }} Issues Flagged</span>
                @endif
            </span>
        </div>
        <div class="card-body p-0">
            @if(empty($bulkImport->error_log))
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-check2-circle text-success fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">Clean Execution!</h5>
                    <p class="mb-0">Every record from the CSV was validated and successfully written to the database without a single error.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-clinic mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Row Number</th>
                                <th style="width: 250px;">Record Identifier / Name</th>
                                <th>Validation Failures & Reasons</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bulkImport->error_log as $errorItem)
                                <tr>
                                    <td class="fw-bold text-danger">Line {{ $errorItem['row'] ?? 'N/A' }}</td>
                                    <td class="fw-semibold text-dark">{{ $errorItem['name'] ?? 'Row ' . ($errorItem['row'] ?? '') }}</td>
                                    <td>
                                        <ul class="mb-0 ps-3 text-danger small">
                                            @foreach($errorItem['errors'] as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
