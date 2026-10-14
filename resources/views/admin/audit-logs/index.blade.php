@extends('layouts.app')

@section('title', 'System Audit Logs')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">System Security & Clinical Audit Trail</h3>
            <p class="text-muted small mb-0">Immutable compliance logs tracking user actions, logins, bulk imports, and data changes.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border px-3 py-2">
            <i class="bi bi-shield-lock me-1"></i> Security Protocol Active
        </span>
    </div>

    <!-- Filter Card -->
    <div class="card card-clinic mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by description or user..." value="{{ $search }}">
                </div>
                <div class="col-md-4">
                    <select name="action" class="form-select">
                        <option value="">All Security Actions</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ $action == $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-clinic flex-grow-1">Filter</button>
                    @if($search || $action)
                        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-shield-check text-primary me-2"></i> Audit Records ({{ $logs->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User Operator</th>
                        <th>Action</th>
                        <th>Event Description</th>
                        <th>Client IP</th>
                        <th>Device / User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small text-secondary" style="white-space: nowrap;">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="fw-semibold text-dark">{{ $log->user->name }}</div>
                                    <span class="badge {{ $log->user->role_badge_class }}" style="font-size: 0.65rem;">
                                        {{ ucfirst($log->user->role) }}
                                    </span>
                                @else
                                    <span class="text-muted small">System / Guest</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.75rem;">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="small text-dark">{{ $log->description }}</td>
                            <td class="font-monospace small text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?? '&mdash;' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-slash fs-2 d-block mb-2 text-secondary"></i>
                                No audit log records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
