@extends('layouts.app')

@section('title', $diet->title)
@section('page_title', 'Diet Plan Breakdown')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-slate-800">{{ $diet->title }}</h4>
            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                {{ $diet->target_calories }} kcal / day
            </span>
        </div>
        <p class="text-muted small mb-0">{{ $diet->description ?? 'Custom nutrition guidelines.' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('diets.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#assignDietModal">
            <i class="fa-solid fa-user-plus me-1"></i> Assign to Member
        </button>
    </div>
</div>

<!-- Macros Overview Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-fire"></i></div>
            <div>
                <div class="stat-value">{{ $diet->target_calories }}</div>
                <div class="stat-label">Daily Calories</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-drumstick-bite"></i></div>
            <div>
                <div class="stat-value">{{ $diet->protein_grams }}g</div>
                <div class="stat-label">Protein Target</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-wheat-awn"></i></div>
            <div>
                <div class="stat-value">{{ $diet->carbs_grams }}g</div>
                <div class="stat-label">Carbs Target</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger-subtle text-danger"><i class="fa-solid fa-droplet"></i></div>
            <div>
                <div class="stat-value">{{ $diet->fats_grams }}g</div>
                <div class="stat-label">Fats Target</div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Meals Breakdown -->
<div class="row g-4">
    @if(is_array($diet->meals))
        @foreach($diet->meals as $mealIndex => $meal)
            <div class="col-lg-6">
                <div class="gym-card h-100">
                    <div class="gym-card-header bg-light">
                        <div class="fw-bold text-dark">
                            <i class="fa-solid fa-utensils text-success me-2"></i> {{ $meal['meal_name'] ?? ('Meal ' . ($mealIndex + 1)) }}
                        </div>
                        <span class="badge bg-white text-dark border"><i class="fa-regular fa-clock me-1"></i>{{ $meal['time'] ?? 'Flexible' }}</span>
                    </div>
                    <div class="gym-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-gym table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Portion</th>
                                        <th>Calories</th>
                                        <th>Protein</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($meal['items'] ?? [] as $item)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $item['name'] ?? 'Food' }}</td>
                                            <td><small class="text-muted">{{ $item['portion'] ?? '-' }}</small></td>
                                            <td><span class="badge bg-light text-dark border">{{ $item['calories'] ?? 0 }} kcal</span></td>
                                            <td><span class="fw-bold text-primary">{{ $item['protein'] ?? 0 }}g</span></td>
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
        <div class="col-12 text-center py-4 text-muted">No meal breakdown defined.</div>
    @endif
</div>

<!-- Modal: Assign Diet to Member -->
<div class="modal fade" id="assignDietModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('diets.assign', $diet) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Assign Diet Plan to Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">This will duplicate this diet template and attach it directly to the member's profile.</p>
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
                    <button type="submit" class="btn btn-primary fw-bold">Assign Meal Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
