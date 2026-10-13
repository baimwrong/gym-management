@extends('layouts.app')

@section('title', 'Build Workout Routine')
@section('page_title', 'Create Workout Routine')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-slate-800">Build New Workout Routine</h4>
                <p class="text-muted small mb-0">Create a structured resistance training schedule for gym athletes.</p>
            </div>
            <a href="{{ route('workouts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <form action="{{ route('workouts.store') }}" method="POST">
            @csrf
            <div class="gym-card mb-4">
                <div class="gym-card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Routine Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. 4-Day Upper / Lower Strength & Hypertrophy Split">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Days Per Week</label>
                            <select name="days_per_week" class="form-select">
                                <option value="3">3 Days</option>
                                <option value="4" selected>4 Days</option>
                                <option value="5">5 Days</option>
                                <option value="6">6 Days</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Primary goal, target rep ranges, progression scheme..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Supervising Coach</label>
                            <select name="trainer_id" class="form-select">
                                <option value="">General Gym Routine</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Difficulty Level</label>
                            <select name="difficulty_level" class="form-select">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate" selected>Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                        <div class="col-12 pt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_template" id="is_template" value="1" checked>
                                <label class="form-check-label fw-semibold small" for="is_template">Save as Reusable General Template</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('workouts.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Build & Save Routine</button>
            </div>
        </form>
    </div>
</div>
@endsection
