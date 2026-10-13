@extends('layouts.app')

@section('title', 'Locker Room Management')
@section('page_title', 'Lockers & Key Allocations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Locker Management Matrix</h4>
        <p class="text-muted small mb-0">Monitor locker allocations, rentals, expiry dates, and maintenance across locker zones.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-lock"></i></div>
            <div>
                <div class="stat-value">{{ $totalLockers }}</div>
                <div class="stat-label">Total Gym Lockers</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-user-lock"></i></div>
            <div>
                <div class="stat-value">{{ $occupiedCount }}</div>
                <div class="stat-label">Occupied / Rented</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-lock-open"></i></div>
            <div>
                <div class="stat-value">{{ $availableCount }}</div>
                <div class="stat-label">Available for Rent</div>
            </div>
        </div>
    </div>
</div>

<!-- Lockers Matrix by Zone -->
@foreach($zones as $zone)
    <div class="gym-card mb-4">
        <div class="gym-card-header">
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="fa-solid fa-door-closed text-primary me-2"></i> {{ $zone }}
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-success-subtle text-success border">Green = Available</span>
                <span class="badge bg-primary-subtle text-primary border">Blue = Occupied</span>
                <span class="badge bg-warning-subtle text-warning border">Yellow = Maintenance</span>
            </div>
        </div>
        <div class="gym-card-body">
            <div class="locker-matrix-grid">
                @foreach($lockers->where('zone', $zone) as $l)
                    <div class="locker-box {{ $l->status }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fs-5 fw-extrabold text-dark">{{ $l->locker_number }}</span>
                            <i class="fa-solid {{ $l->status === 'available' ? 'fa-lock-open text-success' : ($l->status === 'occupied' ? 'fa-lock text-primary' : 'fa-wrench text-warning') }}"></i>
                        </div>

                        @if($l->status === 'occupied' && $l->assignedMember)
                            <div class="small fw-semibold text-truncate text-dark" title="{{ $l->assignedMember->full_name }}">
                                {{ $l->assignedMember->full_name }}
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.65rem;">
                                Exp: {{ $l->expires_at?->format('M d, Y') ?? 'N/A' }}
                            </small>
                            <form action="{{ route('lockers.release', $l) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100 py-0" style="font-size: 0.7rem;" onclick="return confirm('Release locker {{ $l->locker_number }}?')">
                                    Release
                                </button>
                            </form>
                        @elseif($l->status === 'available')
                            <small class="text-success fw-bold d-block mb-2">Available</small>
                            <button type="button" class="btn btn-sm btn-primary w-100 py-0" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#assignLockerModal{{ $l->id }}">
                                Assign
                            </button>
                        @else
                            <small class="text-warning fw-bold d-block mb-2">In Repair</small>
                            <form action="{{ route('lockers.set-status', $l) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="available">
                                <button type="submit" class="btn btn-sm btn-outline-success w-100 py-0" style="font-size: 0.7rem;">
                                    Mark Ready
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Modal: Assign Locker -->
                    <div class="modal fade" id="assignLockerModal{{ $l->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('lockers.assign', $l) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Assign Locker {{ $l->locker_number }} ({{ $l->zone }})</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Select Member</label>
                                            <select name="member_id" class="form-select" required>
                                                <option value="">-- Choose Member --</option>
                                                @foreach($members as $m)
                                                    <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Rental Duration (Months)</label>
                                            <select name="duration_months" class="form-select" required>
                                                <option value="1">1 Month (@currency($l->rental_fee_monthly))</option>
                                                <option value="3" selected>3 Months (@currency($l->rental_fee_monthly * 3))</option>
                                                <option value="6">6 Months (@currency($l->rental_fee_monthly * 6))</option>
                                                <option value="12">12 Months (@currency($l->rental_fee_monthly * 12))</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary fw-bold">Assign Key</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
@endsection
