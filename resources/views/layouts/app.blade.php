<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Clinic Management System') }}</title>
    <!-- Offline Intranet Assets: 100% Local -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/clinic.css') }}">
    @stack('styles')
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- Sidebar Navigation -->
        <aside class="clinic-sidebar d-flex flex-column">
            <div class="brand">
                <i class="bi bi-hospital"></i>
                <div>
                    <span class="fs-5">Clinic CMS</span>
                    <span class="badge bg-teal-subtle text-teal border border-teal text-uppercase" style="font-size: 0.65rem; display:block; letter-spacing: 0.05em; color: #2dd4bf;">Clinic OS</span>
                </div>
            </div>

            <nav class="flex-grow-1 overflow-y-auto py-2">
                <div class="nav-section-title">Core Operations</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Patients Dossier</span>
                </a>
                <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check"></i>
                    <span>Appointments</span>
                </a>

                <div class="nav-section-title">Clinical & Care</div>
                <a href="{{ route('consultations.index') }}" class="nav-link {{ request()->routeIs('consultations.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-medical"></i>
                    <span>Consultations (EMR)</span>
                </a>
                <a href="{{ route('vitals.index') }}" class="nav-link {{ request()->routeIs('vitals.*') ? 'active' : '' }}">
                    <i class="bi bi-activity"></i>
                    <span>Triage & Vitals</span>
                </a>
                <a href="{{ route('lab.orders.index') }}" class="nav-link {{ request()->routeIs('lab.*') ? 'active' : '' }}">
                    <i class="bi bi-eyedropper"></i>
                    <span>Lab & Diagnostics</span>
                </a>

                <div class="nav-section-title">Pharmacy & Billing</div>
                <a href="{{ route('pharmacy.medicines.index') }}" class="nav-link {{ request()->routeIs('pharmacy.medicines.*') ? 'active' : '' }}">
                    <i class="bi bi-capsule-pill"></i>
                    <span>Medicines Inventory</span>
                </a>
                <a href="{{ route('pharmacy.prescriptions.index') }}" class="nav-link {{ request()->routeIs('pharmacy.prescriptions.*') ? 'active' : '' }}">
                    <i class="bi bi-prescription2"></i>
                    <span>Dispensing Queue</span>
                </a>
                <a href="{{ route('billing.index') }}" class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Billing & Invoices</span>
                </a>

                @if(auth()->user()->isAdmin())
                <div class="nav-section-title">Administration</div>
                <a href="{{ route('admin.bulk-import.index') }}" class="nav-link text-warning {{ request()->routeIs('admin.bulk-import.*') ? 'active text-white' : '' }}">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Bulk Import Data</span>
                    <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.65rem;">Admin</span>
                </a>
                <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Medical Staff</span>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Audit Logs</span>
                </a>
                @endif
            </nav>

            <!-- Current User Card -->
            <div class="p-3 border-top border-secondary border-opacity-25 mt-auto">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-weight: 600;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden flex-grow-1">
                        <div class="text-white text-truncate fw-semibold small">{{ auth()->user()->name }}</div>
                        <span class="badge {{ auth()->user()->role_badge_class }}" style="font-size: 0.65rem;">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="clinic-main">
            <!-- Top Navbar -->
            <header class="clinic-navbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1" style="font-size: 0.75rem;">
                            <i class="bi bi-hdd-network me-1"></i> Intranet Offline Ready
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1" style="font-size: 0.75rem;">
                            <i class="bi bi-database me-1"></i> PostgreSQL 18
                        </span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted small d-none d-lg-block">
                        <i class="bi bi-clock me-1"></i> {{ now()->format('l, F j, Y') }}
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">Signed in as {{ ucfirst(auth()->user()->role) }}</h6></li>
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i> My Profile</a></li>
                            @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('admin.bulk-import.index') }}"><i class="bi bi-cloud-arrow-up me-2"></i> Bulk Import Center</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.audit-logs.index') }}"><i class="bi bi-shield-check me-2"></i> System Logs</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="clinic-content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-x-circle-fill fs-5 text-danger"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Please correct the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-muted small text-center text-md-start d-flex flex-column flex-md-row justify-content-between align-items-center no-print">
                <div>
                    <strong>Clinic Management System</strong> &copy; {{ date('Y') }} &mdash; Production Ready Intranet Edition
                </div>
                <div class="text-secondary mt-1 mt-md-0">
                    Laravel 13 &bull; PostgreSQL 18 &bull; Bootstrap 5.3 &bull; Standalone Offline
                </div>
            </footer>
        </div>
    </div>

    <!-- Offline Intranet Scripts: 100% Local -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
    @stack('scripts')
</body>
</html>
