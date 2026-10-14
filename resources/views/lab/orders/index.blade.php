@extends('layouts.app')

@section('title', 'Laboratory Orders')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Laboratory & Diagnostic Orders</h3>
            <p class="text-muted small mb-0">Manage pathology requests, record diagnostic findings, and monitor critical flags.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('lab.orders.create') }}" class="btn btn-clinic">
                <i class="bi bi-eyedropper me-1"></i> Order Laboratory Test
            </a>
            <a href="{{ route('lab.tests.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-card-list me-1"></i> Test Catalog
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ empty($status) ? 'active' : '' }}" href="{{ route('lab.orders.index') }}">
                All Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'ordered' ? 'active' : '' }}" href="{{ route('lab.orders.index', ['status' => 'ordered']) }}">
                <i class="bi bi-clock me-1"></i> Ordered / Pending
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'completed' ? 'active' : '' }}" href="{{ route('lab.orders.index', ['status' => 'completed']) }}">
                <i class="bi bi-check-circle me-1"></i> Completed Results
            </a>
        </li>
    </ul>

    <!-- Lab Orders Table -->
    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-eyedropper text-info me-2"></i> Diagnostic Orders ({{ $orders->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Order Date</th>
                        <th>Patient</th>
                        <th>Test Name</th>
                        <th>Category</th>
                        <th>Result Flag</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $lbo)
                        <tr>
                            <td>
                                <a href="{{ route('lab.orders.show', $lbo) }}" class="font-monospace fw-bold text-dark text-decoration-none">
                                    {{ $lbo->order_number }}
                                </a>
                            </td>
                            <td>{{ $lbo->order_date->format('M d, Y h:i A') }}</td>
                            <td>
                                <a href="{{ route('patients.show', $lbo->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $lbo->patient->full_name }}
                                </a>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $lbo->patient->patient_code }}</div>
                            </td>
                            <td class="fw-bold text-dark">{{ $lbo->labTest->name }}</td>
                            <td><span class="badge bg-light text-secondary border">{{ $lbo->labTest->category }}</span></td>
                            <td>
                                @if($lbo->result_flag)
                                    <span class="badge {{ $lbo->flag_badge_class }}">
                                        {{ ucfirst($lbo->result_flag) }}
                                    </span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $lbo->status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $lbo->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('lab.orders.show', $lbo) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                    @if($lbo->status === 'ordered')
                                        Record Result &rarr;
                                    @else
                                        View Result
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-eyedropper fs-2 d-block mb-2 text-secondary"></i>
                                No laboratory orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
