@extends('layouts.app')

@section('title', $fitnessClass->title . ' - Roster')
@section('page_title', 'Class Roster & Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge rounded-pill text-white" style="background-color: {{ $fitnessClass->color_tag }}; width: 14px; height: 14px; padding: 0;"></span>
            <h4 class="fw-bold mb-0 text-slate-800">{{ $fitnessClass->title }}</h4>
            <span class="badge bg-light text-dark border">{{ $fitnessClass->day_of_week }}s</span>
        </div>
        <p class="text-muted small mb-0">
            {{ date('h:i A', strtotime($fitnessClass->start_time)) }} - {{ date('h:i A', strtotime($fitnessClass->end_time)) }} &bull;
            {{ $fitnessClass->room }} &bull; Coach {{ $fitnessClass->trainer?->full_name ?? 'Gym Staff' }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Timetable
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bookModal" {{ $spotsLeft <= 0 ? 'disabled' : '' }}>
            <i class="fa-solid fa-user-plus me-1"></i> Book Member Spot
        </button>
    </div>
</div>

<!-- Session Date Selector & Capacity Stat -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="gym-card p-3">
            <label class="form-label small fw-semibold text-muted">SELECT SESSION DATE</label>
            <form method="GET" action="{{ route('classes.show', $fitnessClass) }}">
                <div class="input-group input-group-sm">
                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                    <button class="btn btn-outline-primary" type="submit">Go</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="stat-value">{{ $bookings->whereIn('status', ['booked', 'attended'])->count() }} / {{ $fitnessClass->capacity }}</div>
                <div class="stat-label">Spots Reserved</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon {{ $spotsLeft > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                <i class="fa-solid {{ $spotsLeft > 0 ? 'fa-chair' : 'fa-ban' }}"></i>
            </div>
            <div>
                <div class="stat-value">{{ $spotsLeft }}</div>
                <div class="stat-label">{{ $spotsLeft > 0 ? 'Seats Available' : 'Class Sold Out' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Booked Members Roster -->
<div class="gym-card">
    <div class="gym-card-header">
        <div>
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="fa-solid fa-clipboard-check text-primary me-2"></i> Registered Members for {{ date('l, M d, Y', strtotime($date)) }}
            </h6>
            <small class="text-muted">Take attendance or manage reservations</small>
        </div>
    </div>
    <div class="gym-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gym table-hover mb-0">
                <thead>
                    <tr>
                        <th>Member Code</th>
                        <th>Member Name</th>
                        <th>Contact</th>
                        <th>Booking Time</th>
                        <th>Status</th>
                        <th class="text-end">Attendance Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr>
                            <td>
                                <a href="{{ route('members.show', $b->member) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $b->member->member_code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $b->member->full_name }}</div>
                            </td>
                            <td>{{ $b->member->phone ?? 'N/A' }}</td>
                            <td>{{ $b->created_at->format('M d, h:i A') }}</td>
                            <td>
                                @if($b->status === 'attended')
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Attended</span>
                                @elseif($b->status === 'no_show')
                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> No Show</span>
                                @else
                                    <span class="badge bg-primary">Reserved</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('classes.mark-attendance', $b) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="attended">
                                    <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" title="Mark Attended">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>

                                <form action="{{ route('classes.mark-attendance', $b) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="no_show">
                                    <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2" title="Mark No Show">
                                        <i class="fa-solid fa-user-xmark"></i>
                                    </button>
                                </form>

                                <form action="{{ route('classes.cancel', $b) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Cancel Reservation" onclick="return confirm('Cancel this booking?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <p class="mb-0">No members have reserved a spot for this session yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Book Member -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('classes.book', $fitnessClass) }}" method="POST">
                @csrf
                <input type="hidden" name="booking_date" value="{{ $date }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Book Member into Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Member</label>
                        <select name="member_id" class="form-select" required>
                            <option value="">-- Choose Member --</option>
                            @foreach($activeMembers as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Session Date</label>
                        <input type="text" class="form-control" value="{{ date('l, F d, Y', strtotime($date)) }}" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
