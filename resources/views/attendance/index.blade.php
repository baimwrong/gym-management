@extends('layouts.app')

@section('title', 'Attendance Records')
@section('page_title', 'Attendance Log & History')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-users-viewfinder"></i></div>
            <div>
                <div class="stat-value">{{ $totalToday }}</div>
                <div class="stat-label">Total Visits On Selected Date</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-door-open"></i></div>
            <div>
                <div class="stat-value">{{ $currentlyInside }}</div>
                <div class="stat-label">Currently Inside</div>
            </div>
        </div>
    </div>
</div>

<div class="gym-card mb-4">
    <div class="gym-card-body p-3">
        <form method="GET" action="{{ route('attendance.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-calendar"></i></span>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Attendance Statuses</option>
                    <option value="inside" {{ request('status') === 'inside' ? 'selected' : '' }}>Currently Inside Only</option>
                    <option value="checked_out" {{ request('status') === 'checked_out' ? 'selected' : '' }}>Completed / Checked Out Only</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, code, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter</button>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="gym-card">
    <div class="gym-card-header">
        <div>
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="fa-solid fa-list-ul text-primary me-2"></i> Visits on {{ date('F d, Y', strtotime($date)) }}
            </h6>
            <small class="text-muted">Showing {{ $attendances->total() }} recorded entries</small>
        </div>
        <a href="{{ route('attendance.kiosk') }}" class="btn btn-sm btn-warning fw-semibold">
            <i class="fa-solid fa-qrcode me-1"></i> Open Fast Kiosk
        </a>
    </div>
    <div class="gym-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gym table-hover mb-0">
                <thead>
                    <tr>
                        <th>Member Code</th>
                        <th>Member Details</th>
                        <th>Current Plan</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                        <tr>
                            <td>
                                <a href="{{ route('members.show', $att->member) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $att->member->member_code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $att->member->full_name }}</div>
                                <small class="text-muted">{{ $att->member->phone ?? 'No phone' }}</small>
                            </td>
                            <td>
                                @if($att->member->activeSubscription)
                                    <span class="badge bg-light text-dark border">
                                        {{ $att->member->activeSubscription->plan->name }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">Expired</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $att->check_in_time->format('h:i A') }}</div>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem;">{{ str_replace('_', ' ', $att->check_in_method) }}</small>
                            </td>
                            <td>
                                @if($att->check_out_time)
                                    <div>{{ $att->check_out_time->format('h:i A') }}</div>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($att->duration_minutes)
                                    <span class="fw-semibold">{{ $att->duration_minutes }} min</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">In Progress</span>
                                @endif
                            </td>
                            <td>
                                @if($att->check_out_time)
                                    <span class="badge-status active">Completed</span>
                                @else
                                    <span class="badge-status occupied">Inside Gym</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$att->check_out_time)
                                    <form action="{{ route('attendance.checkout', $att) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Check Out">
                                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Check Out
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clipboard-question fa-2x mb-2 text-secondary"></i>
                                <p class="mb-0">No attendance entries recorded for this date and filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($attendances->hasPages())
        <div class="gym-card-footer p-3 border-top">
            {{ $attendances->links() }}
        </div>
    @endif
</div>
@endsection
