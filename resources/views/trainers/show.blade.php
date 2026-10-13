@extends('layouts.app')

@section('title', $trainer->full_name . ' - Trainer Profile')
@section('page_title', 'Coach Roster')

@section('content')
<div class="gym-card mb-4">
    <div class="gym-card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 70px; height: 70px;">
                    {{ substr($trainer->full_name, 0, 1) }}
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-slate-900">{{ $trainer->full_name }}</h3>
                    <div class="text-muted small mt-1">
                        {{ $trainer->email }} &bull; {{ $trainer->phone ?? 'No phone' }} &bull; Rate: <strong>@currency($trainer->hourly_rate)/hr</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('trainers.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> All Coaches
                </a>
                <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#assignModal">
                    <i class="fa-solid fa-user-plus me-1"></i> Assign Member to Coach
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Bio & Certifications -->
    <div class="col-lg-4">
        <div class="gym-card mb-4">
            <div class="gym-card-header">
                <h6 class="fw-bold mb-0 text-slate-800">
                    <i class="fa-solid fa-award text-warning me-2"></i> Certifications & Bio
                </h6>
            </div>
            <div class="gym-card-body">
                <p class="small text-muted">{{ $trainer->biography }}</p>
                <hr>
                <div class="small fw-bold text-muted mb-2">CERTIFICATIONS:</div>
                <p class="small fw-semibold text-dark">{{ $trainer->certifications }}</p>

                <hr>
                <div class="small fw-bold text-muted mb-2">SPECIALTIES:</div>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($trainer->specialties ?? [] as $spec)
                        <span class="badge bg-light text-dark border">{{ $spec }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Assigned Clients Roster -->
    <div class="col-lg-8">
        <div class="gym-card">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-users text-primary me-2"></i> Assigned 1-on-1 Training Clients
                    </h6>
                    <small class="text-muted">Track completed sessions and log new PT appointments</small>
                </div>
            </div>
            <div class="gym-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-gym table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Client Member</th>
                                <th>Package</th>
                                <th>Sessions Progress</th>
                                <th>Status</th>
                                <th class="text-end">Log Session</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trainer->assignments as $a)
                                <tr>
                                    <td>
                                        <a href="{{ route('members.show', $a->member) }}" class="fw-bold text-primary text-decoration-none">
                                            {{ $a->member->full_name }}
                                        </a>
                                        <div class="small text-muted"><code>{{ $a->member->member_code }}</code></div>
                                    </td>
                                    <td>{{ $a->package_name }}</td>
                                    <td>
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>{{ $a->completed_sessions }} / {{ $a->total_sessions }}</span>
                                            <span>{{ $a->remaining_sessions }} left</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $a->progress_percentage }}%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $a->status }}">{{ $a->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($a->status === 'active' && $a->remaining_sessions > 0)
                                            <form action="{{ route('trainers.log-session', $a) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 fw-semibold" title="Mark 1 Session Completed">
                                                    <i class="fa-solid fa-check me-1"></i> Log Session
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No clients assigned to this coach yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Assign Member to Coach -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('trainers.assign', $trainer) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Assign Member to Coach</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Choose Member</label>
                        <select name="member_id" class="form-select" required>
                            <option value="">-- Select Member --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Coaching Package Name</label>
                        <input type="text" name="package_name" class="form-control" value="1-on-1 Performance Coaching (10 Sessions)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Total Sessions Included</label>
                        <input type="number" name="total_sessions" class="form-control" value="10" min="1" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Special Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Member targets, scheduling preferences..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Assign Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
