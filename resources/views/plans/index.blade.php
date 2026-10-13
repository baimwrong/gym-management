@extends('layouts.app')

@section('title', 'Membership Plans & Pricing')
@section('page_title', 'Membership Plans & Packages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Gym Membership Plans</h4>
        <p class="text-muted small mb-0">Manage packages, pricing, privileges, personal training credits, and duration.</p>
    </div>
    <a href="{{ route('plans.create') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Create New Plan
    </a>
</div>

<div class="row g-4">
    @forelse($plans as $plan)
        <div class="col-md-6 col-xl-4">
            <div class="gym-card h-100 position-relative overflow-hidden {{ !$plan->is_active ? 'opacity-75' : '' }}">
                @if(!$plan->is_active)
                    <div class="bg-secondary text-white text-center py-1 small fw-bold text-uppercase" style="font-size: 0.65rem;">
                        INACTIVE / ARCHIVED
                    </div>
                @endif
                <div class="p-4 border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-{{ $plan->badge_color }}-subtle text-{{ $plan->badge_color }} border border-{{ $plan->badge_color }}-subtle fw-bold">
                            {{ $plan->formatted_duration }}
                        </span>
                        <span class="small text-muted">
                            <i class="fa-solid fa-users text-primary me-1"></i> {{ $plan->subscriptions_count }} Active Members
                        </span>
                    </div>

                    <h4 class="fw-bold mb-2 text-dark">{{ $plan->name }}</h4>
                    <p class="text-muted small mb-3" style="min-height: 40px;">{{ $plan->description }}</p>

                    <div class="d-flex align-items-baseline gap-1">
                        <span class="fs-2 fw-extrabold text-primary">@currency($plan->price)</span>
                        <span class="text-muted small">/ {{ $plan->formatted_duration }}</span>
                    </div>
                    @if($plan->signup_fee > 0)
                        <small class="text-muted d-block mt-1">+ @currency($plan->signup_fee) admission fee</small>
                    @else
                        <small class="text-success fw-semibold d-block mt-1"><i class="fa-solid fa-check me-1"></i>No signup / admission fee</small>
                    @endif
                </div>

                <div class="p-4 d-flex flex-column justify-content-between" style="min-height: 220px;">
                    <div>
                        <div class="small fw-bold text-muted text-uppercase mb-2">INCLUDED PERKS & PRIVILEGES:</div>
                        <ul class="list-unstyled small mb-4">
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check {{ $plan->includes_classes ? 'text-success' : 'text-muted' }}"></i>
                                <span>Group fitness classes: <strong>{{ $plan->includes_classes ? 'Unlimited Access' : 'Not Included' }}</strong></span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check {{ $plan->includes_pt_sessions > 0 ? 'text-success' : 'text-muted' }}"></i>
                                <span>Personal coaching: <strong>{{ $plan->includes_pt_sessions > 0 ? $plan->includes_pt_sessions . ' Sessions Included' : 'None' }}</strong></span>
                            </li>
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-snowflake text-info"></i>
                                <span>Max freeze allowance: <strong>{{ $plan->max_freeze_days }} Days</strong></span>
                            </li>
                            @if(!empty($plan->features))
                                @foreach($plan->features as $f)
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-check text-primary"></i>
                                        <span>{{ $f }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <div class="d-flex gap-2 pt-3 border-top">
                        <a href="{{ route('plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary w-50 fw-semibold">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </a>
                        <form action="{{ route('plans.toggle', $plan) }}" method="POST" class="w-50">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $plan->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 fw-semibold">
                                <i class="fa-solid {{ $plan->is_active ? 'fa-ban' : 'fa-check' }} me-1"></i>
                                {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No membership plans created yet.</p>
        </div>
    @endforelse
</div>
@endsection
