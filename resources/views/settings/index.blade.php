@extends('layouts.app')

@section('title', 'System Settings & Diagnostics')
@section('page_title', 'Gym Settings & Diagnostics')

@section('content')
<div class="row g-4">
    <!-- Left Column: Business Configuration Form -->
    <div class="col-lg-7">
        <div class="gym-card">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-gear text-primary me-2"></i> Gym Brand & Business Rules
                    </h6>
                    <small class="text-muted">Configure club identity, receipts header, currency, and tax parameters</small>
                </div>
            </div>
            <div class="gym-card-body p-4">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Gym Legal Name <span class="text-danger">*</span></label>
                            <input type="text" name="gym_name" class="form-control" value="{{ $settings['gym_name'] ?? config('gym.name') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Club Slogan / Tagline</label>
                            <input type="text" name="gym_tagline" class="form-control" value="{{ $settings['gym_tagline'] ?? config('gym.tagline') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Official Phone Number</label>
                            <input type="text" name="gym_phone" class="form-control" value="{{ $settings['gym_phone'] ?? config('gym.phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Support Email Address</label>
                            <input type="email" name="gym_email" class="form-control" value="{{ $settings['gym_email'] ?? config('gym.email') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Physical Facility Address</label>
                            <input type="text" name="gym_address" class="form-control" value="{{ $settings['gym_address'] ?? config('gym.address') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="currency_symbol" class="form-control" value="{{ $settings['currency_symbol'] ?? config('gym.currency_symbol') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Sales Tax Rate (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="tax_rate_percentage" class="form-control" value="{{ $settings['tax_rate_percentage'] ?? config('gym.tax_rate_percentage') }}" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Grace Period (Days)</label>
                            <div class="input-group">
                                <input type="number" name="grace_period_days" class="form-control" value="{{ $settings['grace_period_days'] ?? config('gym.allow_grace_period_days') }}" required>
                                <span class="input-group-text">days</span>
                            </div>
                            <div class="form-text" style="font-size: 0.7rem;">Entry allowed with warning after expiration.</div>
                        </div>
                    </div>

                    <div class="pt-3 border-top text-end">
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save System Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Environment & PostgreSQL 18 Diagnostics -->
    <div class="col-lg-5">
        <div class="gym-card mb-4">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-server text-success me-2"></i> System & Database Diagnostics
                    </h6>
                    <small class="text-muted">Targeted runtime stack environment</small>
                </div>
            </div>
            <div class="gym-card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Database Engine Target:</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold fs-6">
                            <i class="fa-solid fa-database me-1"></i> PostgreSQL 18
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Application Framework:</span>
                        <span class="fw-bold text-dark">
                            <i class="fa-brands fa-laravel text-danger me-1"></i> Laravel {{ $systemInfo['laravel_version'] }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Offline Network Status:</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                            <i class="fa-solid fa-circle-check me-1"></i> 100% Offline (Local Assets)
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Frontend Asset Packaging:</span>
                        <span class="text-dark fw-semibold">Bootstrap 5.3 + FontAwesome 6 (Bundled in /public)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Database Server Status:</span>
                        <span class="text-muted text-break">{{ $systemInfo['db_server_version'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Session & Cache Drivers:</span>
                        <span class="text-dark">{{ $systemInfo['session_driver'] }} / {{ $systemInfo['cache_driver'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Storage Permissions:</span>
                        <span class="badge bg-success">Writable</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="gym-card">
            <div class="gym-card-body p-4 bg-light rounded-3">
                <h6 class="fw-bold text-dark mb-2">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i> Local Internal Network Mode
                </h6>
                <p class="small text-muted mb-0">
                    This installation is tailored for enterprise gyms and closed local networks. All CSS styling, JavaScript plugins, FontAwesome webfonts, and QR barcode utilities are loaded strictly from the local filesystem, guaranteeing that the gym turnstiles, POS registers, and admin desks operate flawlessly even during internet outages.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
