@extends('layouts.auth')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9 col-lg-7 col-xl-6">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-teal-subtle text-teal rounded-circle p-3 mb-2" style="width: 64px; height: 64px; background-color: #ccfbf1; color: #0d9488;">
                <i class="bi bi-hospital fs-1"></i>
            </div>
            <h2 class="fw-bold mb-1 text-dark">Clinic Management System</h2>
            <p class="text-muted small">Hospital & Clinic Enterprise Management System</p>
        </div>

        <div class="card card-clinic border-0 shadow-lg mb-4">
            <div class="card-body p-4 p-md-5">
                <h4 class="card-title fw-bold mb-3 text-dark">Sign In to Your Station</h4>
                <p class="text-muted small mb-4">Enter your hospital credentials to access the internal medical network.</p>

                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small text-muted">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-secondary"></i></span>
                            <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', 'admin@clinic.local') }}" required autofocus placeholder="name@clinic.local">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold small text-muted">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-secondary"></i></span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" value="password123" required placeholder="Enter password">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label small text-muted" for="remember">Remember this station</label>
                        </div>
                        <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i> Internal Intranet</span>
                    </div>

                    <button type="submit" class="btn btn-clinic w-100 py-2 fs-6 fw-semibold shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Authenticate & Enter
                    </button>
                </form>
            </div>
        </div>

        <!-- Demo Accounts Quick Fill Helper for Evaluation -->
        <div class="card card-clinic border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2">
                <small class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;"><i class="bi bi-key-fill text-warning me-1"></i> Demo Quick Login Credentials (Password: <code>password123</code>)</small>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start quick-fill" data-email="admin@clinic.local">
                            <span class="badge bg-danger mb-1 d-inline-block">Admin</span>
                            <div class="text-truncate small">admin@clinic.local</div>
                        </button>
                    </div>
                    <div class="col-6 col-md-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start quick-fill" data-email="doctor@clinic.local">
                            <span class="badge bg-primary mb-1 d-inline-block">Doctor</span>
                            <div class="text-truncate small">doctor@clinic.local</div>
                        </button>
                    </div>
                    <div class="col-6 col-md-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start quick-fill" data-email="nurse@clinic.local">
                            <span class="badge bg-info text-dark mb-1 d-inline-block">Nurse</span>
                            <div class="text-truncate small">nurse@clinic.local</div>
                        </button>
                    </div>
                    <div class="col-6 col-md-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start quick-fill" data-email="pharmacist@clinic.local">
                            <span class="badge bg-success mb-1 d-inline-block">Pharmacist</span>
                            <div class="text-truncate small">pharmacist@clinic.local</div>
                        </button>
                    </div>
                    <div class="col-6 col-md-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start quick-fill" data-email="receptionist@clinic.local">
                            <span class="badge bg-warning text-dark mb-1 d-inline-block">Reception</span>
                            <div class="text-truncate small">receptionist@clinic.local</div>
                        </button>
                    </div>
                    <div class="col-6 col-md-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start quick-fill" data-email="patient@clinic.local">
                            <span class="badge bg-secondary mb-1 d-inline-block">Patient</span>
                            <div class="text-truncate small">patient@clinic.local</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.quick-fill').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('email').value = this.dataset.email;
            document.getElementById('password').value = 'password123';
        });
    });
</script>
@endpush
@endsection
