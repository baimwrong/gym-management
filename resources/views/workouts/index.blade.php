@extends('layouts.app')

@section('title', 'Workout Routines')
@section('page_title', 'Workout Routines & Splits')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Workout Routines & Splits</h4>
        <p class="text-muted small mb-0">Custom workout splits, exercises, sets, reps, and assignments for athletes.</p>
    </div>
    <a href="{{ route('workouts.create') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Build Routine
    </a>
</div>

<div class="row g-4">
    @forelse($workoutPlans as $w)
        <div class="col-md-6 col-xl-4">
            <div class="gym-card h-100 position-relative overflow-hidden">
                <div class="p-4 border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ $w->days_per_week }} Days / Week
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary text-uppercase small">
                            {{ $w->difficulty_level }}
                        </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $w->title }}</h5>
                    <p class="text-muted small mb-0">{{ Str::limit($w->description ?? 'Structured hypertrophy split.', 90) }}</p>
                </div>

                <div class="p-4 d-flex flex-column justify-content-between" style="min-height: 180px;">
                    <div>
                        <div class="small text-muted mb-2">
                            @if($w->is_template)
                                <span class="badge bg-info-subtle text-info border"><i class="fa-solid fa-copy me-1"></i> General Template</span>
                            @else
                                <span class="badge bg-success-subtle text-success border">
                                    <i class="fa-solid fa-user me-1"></i> For: {{ $w->member?->full_name ?? 'Member' }}
                                </span>
                            @endif
                        </div>

                        <div class="small text-muted mb-3">
                            <i class="fa-solid fa-calendar-week me-1"></i> Routine Days: <strong>{{ is_array($w->routine_schedule) ? count($w->routine_schedule) : 0 }} Days</strong>
                        </div>
                    </div>

                    <div class="pt-3 border-top">
                        <a href="{{ route('workouts.show', $w) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                            <i class="fa-solid fa-eye me-1"></i> View Exercise Breakdown
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No workout plans created yet.</p>
        </div>
    @endforelse
</div>
@endsection
