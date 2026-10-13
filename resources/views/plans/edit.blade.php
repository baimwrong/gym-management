@extends('layouts.app')

@section('title', 'Edit Membership Plan - ' . $plan->name)
@section('page_title', 'Edit Membership Package')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-slate-800">Edit Membership Plan</h4>
                <p class="text-muted small mb-0">Modifying settings for <strong>{{ $plan->name }}</strong></p>
            </div>
            <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Plans
            </a>
        </div>

        <form action="{{ route('plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="gym-card mb-4">
                <div class="gym-card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Badge Accent Color</label>
                            <select name="badge_color" class="form-select">
                                <option value="primary" {{ $plan->badge_color === 'primary' ? 'selected' : '' }}>Blue (Primary)</option>
                                <option value="success" {{ $plan->badge_color === 'success' ? 'selected' : '' }}>Green (Success)</option>
                                <option value="warning" {{ $plan->badge_color === 'warning' ? 'selected' : '' }}>Amber (Warning)</option>
                                <option value="danger" {{ $plan->badge_color === 'danger' ? 'selected' : '' }}>Red (Danger)</option>
                                <option value="info" {{ $plan->badge_color === 'info' ? 'selected' : '' }}>Cyan (Info)</option>
                                <option value="secondary" {{ $plan->badge_color === 'secondary' ? 'selected' : '' }}>Gray (Secondary)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Plan Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Duration Period <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="duration_count" class="form-control" value="{{ old('duration_count', $plan->duration_count) }}" min="1" required>
                                <select name="duration_type" class="form-select" style="max-width: 140px;">
                                    <option value="months" {{ $plan->duration_type === 'months' ? 'selected' : '' }}>Months</option>
                                    <option value="days" {{ $plan->duration_type === 'days' ? 'selected' : '' }}>Days</option>
                                    <option value="years" {{ $plan->duration_type === 'years' ? 'selected' : '' }}>Years</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Plan Price ($) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $plan->price) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Signup / Admission Fee ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="signup_fee" class="form-control" value="{{ old('signup_fee', $plan->signup_fee) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Included Personal Training Sessions</label>
                            <input type="number" name="includes_pt_sessions" class="form-control" value="{{ old('includes_pt_sessions', $plan->includes_pt_sessions) }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Max Freeze Allowance (Days)</label>
                            <input type="number" name="max_freeze_days" class="form-control" value="{{ old('max_freeze_days', $plan->max_freeze_days) }}" min="0">
                        </div>
                        <div class="col-md-6 pt-md-4">
                            <div class="form-check form-switch pt-2">
                                <input class="form-check-input" type="checkbox" name="includes_classes" id="includes_classes" value="1" {{ $plan->includes_classes ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="includes_classes">Includes Group Fitness Classes</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Key Features / Perks (One per line)</label>
                            <textarea name="features" class="form-control" rows="4">{{ is_array($plan->features) ? implode("\n", $plan->features) : '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="is_active">Plan is Active & Available for Subscriptions</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Update Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
