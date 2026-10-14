@extends('layouts.app')

@section('title', 'Laboratory Test Catalog')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Diagnostic Laboratory Test Catalog</h3>
            <p class="text-muted small mb-0">Standardized clinical test definitions, reference intervals, and procedural prices.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('lab.tests.create') }}" class="btn btn-clinic">
                <i class="bi bi-plus-lg me-1"></i> Catalog New Test
            </a>
            <a href="{{ route('lab.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-card-checklist me-1"></i> Orders Queue
            </a>
        </div>
    </div>

    <div class="card card-clinic">
        <div class="card-header-clinic">
            <span><i class="bi bi-card-list text-primary me-2"></i> Cataloged Laboratory Tests ({{ $tests->count() }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clinic mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Test Name</th>
                        <th>Category</th>
                        <th>Standard Reference Range</th>
                        <th>Price ($)</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tests as $test)
                        <tr>
                            <td><span class="badge bg-light text-dark border font-monospace">{{ $test->code }}</span></td>
                            <td class="fw-bold text-dark">{{ $test->name }}</td>
                            <td><span class="badge bg-light text-secondary border">{{ $test->category }}</span></td>
                            <td class="small font-monospace">{{ $test->reference_range ?? '&mdash;' }}</td>
                            <td class="fw-bold text-success">${{ number_format($test->price, 2) }}</td>
                            <td class="small text-muted text-truncate" style="max-width: 200px;">{{ $test->description ?? '&mdash;' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No diagnostic laboratory tests cataloged yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
