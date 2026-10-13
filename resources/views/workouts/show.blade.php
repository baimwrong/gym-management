@extends('layouts.app')

@section('title', $workout->title)
@section('page_title', 'Workout Routine Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-slate-800">{{ $workout->title }}</h4>
            <span class="badge bg-primary">{{ $workout->days_per_week }} Days/Week</span>
            <span class="badge bg-secondary text-uppercase">{{ $workout->difficulty_level }}</span>
        </div>
        <p class="text-muted small mb-0">{{ $workout->description ?? 'Custom routine.' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('workouts.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#assignWorkoutModal">
            <i class="fa-solid fa-user-plus me-1"></i> Assign to Member
        </button>
    </div>
</div>

<div class="row g-4">
    @if(is_array($workout->routine_schedule))
        @foreach($workout->routine_schedule as $dayIndex => $day)
            <div class="col-lg-6">
                <div class="gym-card h-100">
                    <div class="gym-card-header bg-light">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-dumbbell text-primary me-2"></i> {{ $day['day_title'] ?? ('Day ' . ($dayIndex + 1)) }}
                        </h6>
                    </div>
                    <div class="gym-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-gym table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Exercise</th>
                                        <th>Sets</th>
                                        <th>Reps</th>
                                        <th>Rest</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($day['exercises'] ?? [] as $ex)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $ex['name'] ?? 'Exercise' }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $ex['sets'] ?? 3 }}</span></td>
                                            <td><span class="fw-semibold">{{ $ex['reps'] ?? '10-12' }}</span></td>
                                            <td><small class="text-muted">{{ $ex['rest'] ?? '60s' }}</small></td>
                                            <td><small class="text-muted">{{ $ex['notes'] ?? '-' }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12 text-center py-4 text-muted">No schedule defined.</div>
    @endif
</div>

<!-- Modal: Assign to Member -->
<div class="modal fade" id="assignWorkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('workouts.assign', $workout) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Assign Workout Routine to Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">This will duplicate this workout template and assign it directly to the member's profile.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Member</label>
                        <select name="member_id" class="form-select" required>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Assign Routine</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
