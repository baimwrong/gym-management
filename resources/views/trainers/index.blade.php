@extends('layouts.app')

@section('title', 'Personal Trainers & Coaching')
@section('page_title', 'Coaching Staff')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Personal Trainers & Fitness Coaches</h4>
        <p class="text-muted small mb-0">Certified coaching staff, specialties, client allocations, and training packages.</p>
    </div>
</div>

<div class="row g-4">
    @forelse($trainers as $trainer)
        <div class="col-md-6 col-xl-4">
            <div class="gym-card h-100 position-relative overflow-hidden">
                <div class="p-4 border-bottom bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                            {{ substr($trainer->full_name, 0, 1) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $trainer->full_name }}</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                <i class="fa-solid fa-circle text-success me-1" style="font-size: 6px;"></i> Active Coach
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-4 d-flex flex-column justify-content-between" style="min-height: 240px;">
                    <div>
                        <div class="small fw-bold text-muted mb-2 text-uppercase">SPECIALTIES:</div>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach($trainer->specialties ?? [] as $spec)
                                <span class="badge bg-light text-dark border">{{ $spec }}</span>
                            @endforeach
                        </div>

                        <p class="small text-muted mb-3">{{ Str::limit($trainer->biography, 130) }}</p>

                        <div class="p-2 bg-light rounded border mb-3 small d-flex justify-content-between">
                            <span><i class="fa-solid fa-certificate text-warning me-1"></i> {{ Str::limit($trainer->certifications, 28) }}</span>
                            <strong class="text-primary">@currency($trainer->hourly_rate)/hr</strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center small text-muted">
                            <span><i class="fa-solid fa-users text-primary me-1"></i> Active 1-on-1 Clients:</span>
                            <span class="badge bg-primary rounded-pill">{{ $trainer->active_clients_count }}</span>
                        </div>
                    </div>

                    <div class="pt-3 border-top mt-3">
                        <a href="{{ route('trainers.show', $trainer) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                            <i class="fa-solid fa-user-check me-1"></i> View Clients & Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No personal trainers registered yet.</p>
        </div>
    @endforelse
</div>
@endsection
