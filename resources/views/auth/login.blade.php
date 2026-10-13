@extends('layouts.auth')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-black text-white text-center py-4 border-0">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-3" style="width: 58px; height: 58px;">
                    <i class="fa-solid fa-dumbbell text-white fa-xl"></i>
                </div>
                <h4 class="fw-bold mb-1 tracking-wide">{{ config('gym.name', 'PREMIER FITNESS CLUB') }}</h4>
                <p class="text-secondary small mb-0">{{ config('gym.tagline', 'Gym Management System') }}</p>
            </div>

            <div class="card-body p-4 p-md-5 bg-white">
                @if(session('info'))
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="fa-solid fa-circle-info me-1"></i> {{ session('info') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-4">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control bg-light border-start-0" id="email" name="email" value="{{ old('email', 'admin@gym.local') }}" required autofocus placeholder="name@gym.local">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label small fw-bold text-muted">PASSWORD</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" class="form-control bg-light border-start-0" id="password" name="password" value="password123" required placeholder="••••••••">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                            <label class="form-check-label small text-muted" for="remember">Remember session</label>
                        </div>
                        <span class="badge bg-success-subtle text-success small border border-success-subtle">
                            <i class="fa-solid fa-wifi text-success me-1"></i> Intranet Ready
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Access Gym Portal
                    </button>
                </form>

                <!-- Demo Credentials Quick Selector -->
                <div class="mt-4 pt-3 border-top">
                    <p class="text-center text-muted small fw-bold mb-2">QUICK TEST ACCOUNTS (CLICK TO AUTOFILL):</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-truncate text-start" onclick="fillLogin('admin@gym.local', 'password123')">
                                <i class="fa-solid fa-user-shield text-danger me-1"></i> Admin
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-truncate text-start" onclick="fillLogin('manager@gym.local', 'password123')">
                                <i class="fa-solid fa-user-tie text-primary me-1"></i> Manager
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-truncate text-start" onclick="fillLogin('reception@gym.local', 'password123')">
                                <i class="fa-solid fa-headset text-warning me-1"></i> Front Desk
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-truncate text-start" onclick="fillLogin('alex.trainer@gym.local', 'password123')">
                                <i class="fa-solid fa-user-ninja text-success me-1"></i> Coach Alex
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
@endpush
