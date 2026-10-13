@extends('layouts.app')

@section('title', 'Create Nutrition & Diet Plan')
@section('page_title', 'Create Nutrition Plan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-slate-800">Create Nutrition & Diet Plan</h4>
                <p class="text-muted small mb-0">Set energy requirements, target macronutrients, and meal guidelines.</p>
            </div>
            <a href="{{ route('diets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <form action="{{ route('diets.store') }}" method="POST">
            @csrf
            <div class="gym-card mb-4">
                <div class="gym-card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Diet Plan Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. 2600 kcal Clean Lean Bulk High-Protein Plan">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Diet approach, hydration goals, supplementation timing..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Target Calories (kcal) <span class="text-danger">*</span></label>
                            <input type="number" name="target_calories" class="form-control" value="{{ old('target_calories', 2500) }}" min="1000" max="8000" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Protein (g) <span class="text-danger">*</span></label>
                            <input type="number" name="protein_grams" class="form-control" value="{{ old('protein_grams', 180) }}" min="30" max="500" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Carbs (g) <span class="text-danger">*</span></label>
                            <input type="number" name="carbs_grams" class="form-control" value="{{ old('carbs_grams', 250) }}" min="30" max="800" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Fats (g) <span class="text-danger">*</span></label>
                            <input type="number" name="fats_grams" class="form-control" value="{{ old('fats_grams', 70) }}" min="20" max="300" required>
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
                <a href="{{ route('diets.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Build & Save Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
