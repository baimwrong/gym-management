@extends('layouts.app')

@section('title', 'Fast Check-In Turnstile Kiosk')
@section('page_title', 'Access Control Turnstile')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-lg-6 col-xl-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="kiosk-badge-card text-center p-4">
                <div class="d-inline-flex p-3 rounded-circle bg-white text-dark mb-3 shadow">
                    <i class="fa-solid fa-qrcode fa-2xl text-primary"></i>
                </div>
                <h3 class="fw-bold mb-1">SCAN MEMBER PASS</h3>
                <p class="text-light text-opacity-75 small mb-0">Use barcode scanner, tap RFID card, or enter Member ID</p>

                <div class="mt-4 p-3 bg-white bg-opacity-10 rounded-3 d-flex justify-content-around align-items-center">
                    <div>
                        <div class="fs-3 fw-bold">{{ $currentlyInsideCount }}</div>
                        <div class="small text-uppercase text-light text-opacity-75">Inside Now</div>
                    </div>
                    <div class="vr bg-white opacity-25"></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ config('gym.operating_hours.weekday') }}</div>
                        <div class="small text-uppercase text-light text-opacity-75">Today's Hours</div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 p-md-5 bg-white">
                <form action="{{ route('attendance.process') }}" method="POST" id="kiosk-form">
                    @csrf
                    <div class="mb-4">
                        <label for="kiosk-scan-input" class="form-label fw-bold text-muted small">MEMBER BARCODE / CODE / PHONE</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-barcode text-primary"></i></span>
                            <input type="text" 
                                   class="form-control bg-light border-start-0 fw-bold fs-4 text-center tracking-wide" 
                                   id="kiosk-scan-input" 
                                   name="code" 
                                   placeholder="e.g. GYM-2026-1001" 
                                   required 
                                   autofocus 
                                   autocomplete="off">
                        </div>
                        <div class="form-text text-center small text-muted mt-2">
                            <i class="fa-solid fa-keyboard me-1"></i> Press Enter to instantly validate and log entry
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small d-block text-center">ACCESS ACTION</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="action" id="action-toggle" value="toggle" checked>
                            <label class="btn btn-outline-primary fw-semibold" for="action-toggle">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Smart Auto
                            </label>

                            <input type="radio" class="btn-check" name="action" id="action-checkin" value="check_in">
                            <label class="btn btn-outline-success fw-semibold" for="action-checkin">
                                <i class="fa-solid fa-right-to-bracket me-1"></i> Check In
                            </label>

                            <input type="radio" class="btn-check" name="action" id="action-checkout" value="check_out">
                            <label class="btn btn-outline-danger fw-semibold" for="action-checkout">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Check Out
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm py-3">
                        <i class="fa-solid fa-circle-check me-2"></i> PROCESS ACCESS
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <span class="text-muted small">Need to register a walk-in or new member?</span>
                    <a href="{{ route('members.create') }}" class="btn btn-sm btn-link text-decoration-none fw-bold">
                        New Member Registration &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Scans Feed -->
    <div class="col-lg-6 col-xl-5">
        <div class="gym-card h-100">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-tower-broadcast text-success me-2"></i> Live Gate Activity
                    </h6>
                    <small class="text-muted">Today's real-time entries & exits</small>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="fa-solid fa-circle fa-beat-fade me-1" style="font-size: 8px;"></i> Live
                </span>
            </div>
            <div class="gym-card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentCheckIns as $item)
                        <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle {{ $item->check_out_time ? 'bg-secondary' : 'bg-success' }} text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fa-solid {{ $item->check_out_time ? 'fa-arrow-right-from-bracket' : 'fa-arrow-right-to-bracket' }}"></i>
                                </div>
                                <div>
                                    <a href="{{ route('members.show', $item->member) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $item->member->full_name }}
                                    </a>
                                    <div class="small text-muted">
                                        <code>{{ $item->member->member_code }}</code> &bull; {{ $item->check_in_time->format('h:i A') }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                @if($item->check_out_time)
                                    <span class="badge bg-light text-secondary border">Left ({{ $item->duration_minutes }}m)</span>
                                @else
                                    <span class="badge bg-success">ACTIVE IN GYM</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-id-card fa-2x mb-2 text-secondary"></i>
                            <p class="mb-0">No entries recorded yet today.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
