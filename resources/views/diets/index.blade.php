@extends('layouts.app')

@section('title', 'Nutrition & Diet Plans')
@section('page_title', 'Nutrition & Meal Plans')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Nutrition & Diet Plans</h4>
        <p class="text-muted small mb-0">Daily calorie targets, macronutrient splits (Protein/Carbs/Fats), and structured meal timing.</p>
    </div>
    <a href="{{ route('diets.create') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Create Diet Plan
    </a>
</div>

<div class="row g-4">
    @forelse($dietPlans as $d)
        <div class="col-md-6 col-xl-4">
            <div class="gym-card h-100 position-relative overflow-hidden">
                <div class="p-4 border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                            <i class="fa-solid fa-fire me-1"></i> {{ $d->target_calories }} kcal
                        </span>
                        @if($d->is_template)
                            <span class="badge bg-info-subtle text-info border">Template</span>
                        @else
                            <span class="badge bg-light text-dark border">{{ $d->member?->full_name ?? 'Member' }}</span>
                        @endif
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $d->title }}</h5>
                    <p class="text-muted small mb-0">{{ Str::limit($d->description ?? 'Nutritional guidelines.', 80) }}</p>
                </div>

                <div class="p-4 d-flex flex-column justify-content-between" style="min-height: 190px;">
                    <div>
                        <div class="small fw-bold text-muted mb-2 text-uppercase">TARGET MACROS:</div>
                        <div class="row g-2 text-center mb-3">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded border">
                                    <div class="fw-bold text-primary">{{ $d->protein_grams }}g</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Protein</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded border">
                                    <div class="fw-bold text-warning">{{ $d->carbs_grams }}g</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Carbs</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded border">
                                    <div class="fw-bold text-danger">{{ $d->fats_grams }}g</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Fats</small>
                                </div>
                            </div>
                        </div>

                        <div class="small text-muted mb-3">
                            <i class="fa-solid fa-bowl-food me-1"></i> Daily Meals: <strong>{{ is_array($d->meals) ? count($d->meals) : 0 }} Meals</strong>
                        </div>
                    </div>

                    <div class="pt-3 border-top">
                        <a href="{{ route('diets.show', $d) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                            <i class="fa-solid fa-eye me-1"></i> View Meal Breakdown
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No diet plans created yet.</p>
        </div>
    @endforelse
</div>
@endsection
