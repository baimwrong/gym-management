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
                        <div class="fs-3 fw-bold" id="kiosk-inside-counter">{{ $currentlyInsideCount }}</div>
                        <div class="small text-uppercase text-light text-opacity-75">Inside Now</div>
                    </div>
                    <div class="vr bg-white opacity-25"></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ config('gym.operating_hours.weekday') }}</div>
                        <div class="small text-uppercase text-light text-opacity-75">Today's Hours</div>
                    </div>
                </div>
            </div>

            <!-- Instant Access Result Banner -->
            <div id="kiosk-feedback-banner" class="d-none p-3 text-center fw-bold transition-all" style="font-size: 1.1rem;"></div>

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
                <div class="list-group list-group-flush" id="live-activity-list">
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
                        <div class="text-center py-5 text-muted" id="no-scans-placeholder">
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

@push('scripts')
<script>
let audioCtx = null;
function getAudioContext() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
    return audioCtx;
}

function playScanSound(type) {
    try {
        const ctx = getAudioContext();
        if (!ctx) return;
        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);

        if (type === 'success') {
            // High-pitched pleasant double chime
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now);
            osc.frequency.exponentialRampToValueAtTime(1320, now + 0.15);
            gain.gain.setValueAtTime(0.3, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
            osc.start(now);
            osc.stop(now + 0.25);
        } else if (type === 'denied') {
            // Low sawtooth buzzer
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(160, now);
            osc.frequency.linearRampToValueAtTime(100, now + 0.35);
            gain.gain.setValueAtTime(0.4, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
            osc.start(now);
            osc.stop(now + 0.4);
        } else if (type === 'warning') {
            // Two-tone advisory alert
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(587, now);
            osc.frequency.setValueAtTime(784, now + 0.12);
            gain.gain.setValueAtTime(0.35, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
            osc.start(now);
            osc.stop(now + 0.3);
        }
    } catch (e) {
        console.warn('Audio feedback error', e);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('kiosk-form');
    const input = document.getElementById('kiosk-scan-input');
    const banner = document.getElementById('kiosk-feedback-banner');
    const counter = document.getElementById('kiosk-inside-counter');
    const list = document.getElementById('live-activity-list');
    const placeholder = document.getElementById('no-scans-placeholder');

    let bannerTimeout = null;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const codeVal = input.value.trim();
        if (!codeVal) return;

        const actionVal = document.querySelector('input[name="action"]:checked').value;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Provide immediate visual pending state
        input.disabled = true;

        fetch("{{ route('attendance.process') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                code: codeVal,
                action: actionVal
            })
        })
        .then(async response => {
            const data = await response.json();
            return { ok: response.ok, data: data };
        })
        .then(res => {
            input.disabled = false;
            input.value = '';
            input.focus();

            clearTimeout(bannerTimeout);
            banner.classList.remove('d-none', 'bg-success', 'bg-danger', 'bg-warning', 'text-white', 'text-dark');

            if (res.ok && res.data.success) {
                if (res.data.warning) {
                    playScanSound('warning');
                    banner.classList.add('bg-warning', 'text-dark');
                    banner.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i> ${res.data.message}`;
                } else {
                    playScanSound('success');
                    banner.classList.add('bg-success', 'text-white');
                    banner.innerHTML = `<i class="fa-solid fa-circle-check me-2"></i> ${res.data.message}`;
                }

                if (res.data.currently_inside !== undefined && counter) {
                    counter.innerText = res.data.currently_inside;
                }

                if (placeholder) {
                    placeholder.remove();
                }

                // Prepend scan to activity feed
                if (res.data.attendance && res.data.member) {
                    const isCheckOut = Boolean(res.data.attendance.check_out_time);
                    const timeStr = isCheckOut ? res.data.attendance.check_out_time : res.data.attendance.check_in_time;
                    const badgeHtml = isCheckOut 
                        ? `<span class="badge bg-light text-secondary border">Left (${res.data.attendance.duration || 0}m)</span>`
                        : `<span class="badge bg-success">ACTIVE IN GYM</span>`;

                    const itemHtml = `
                        <div class="list-group-item d-flex align-items-center justify-content-between p-3 bg-light-subtle">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle ${isCheckOut ? 'bg-secondary' : 'bg-success'} text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fa-solid ${isCheckOut ? 'fa-arrow-right-from-bracket' : 'fa-arrow-right-to-bracket'}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">${res.data.member.name}</div>
                                    <div class="small text-muted">
                                        <code>${res.data.member.code}</code> &bull; ${timeStr}
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                ${badgeHtml}
                            </div>
                        </div>
                    `;
                    list.insertAdjacentHTML('afterbegin', itemHtml);
                }
            } else {
                playScanSound('denied');
                banner.classList.add('bg-danger', 'text-white');
                banner.innerHTML = `<i class="fa-solid fa-circle-xmark me-2"></i> ${res.data.message || 'Access Denied'}`;
            }

            bannerTimeout = setTimeout(() => {
                banner.classList.add('d-none');
            }, 5000);
        })
        .catch(err => {
            input.disabled = false;
            input.focus();
            playScanSound('denied');
            banner.classList.remove('d-none', 'bg-success', 'bg-warning', 'text-dark');
            banner.classList.add('bg-danger', 'text-white');
            banner.innerHTML = `<i class="fa-solid fa-circle-xmark me-2"></i> Scan communication failed. Please try again.`;
        });
    });
});
</script>
@endpush
