@extends('layouts.app')

@section('title', 'Group Fitness Classes')
@section('page_title', 'Classes & Weekly Schedule')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Classes & Group Timetable</h4>
        <p class="text-muted small mb-0">Browse weekly group workouts, view capacity, and book members into sessions.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createClassModal">
        <i class="fa-solid fa-plus me-1"></i> Add New Class
    </button>
</div>

<!-- Day Tabs Filter -->
<div class="gym-card mb-4">
    <div class="gym-card-body p-2">
        <ul class="nav nav-pills nav-fill">
            <li class="nav-item">
                <a class="nav-link py-2 {{ $selectedDay === 'all' ? 'active' : '' }}" href="{{ route('classes.index', ['day' => 'all']) }}">
                    All Days
                </a>
            </li>
            @foreach($daysOfWeek as $day)
                <li class="nav-item">
                    <a class="nav-link py-2 {{ $selectedDay === $day ? 'active' : '' }}" href="{{ route('classes.index', ['day' => $day]) }}">
                        {{ $day }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="row g-4">
    @forelse($classes as $c)
        <div class="col-md-6 col-xl-4">
            <div class="gym-card h-100 position-relative overflow-hidden">
                <div style="height: 6px; background-color: {{ $c->color_tag }};"></div>
                <div class="gym-card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-calendar-day me-1 text-primary"></i> {{ $c->day_of_week }}
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size: 0.7rem;">
                                {{ str_replace('_', ' ', $c->difficulty_level) }}
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">{{ $c->title }}</h5>
                        <p class="text-muted small mb-3">{{ $c->description ?? 'High performance group session.' }}</p>

                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted"><i class="fa-regular fa-clock me-1"></i> Time:</span>
                                <strong class="text-dark small">
                                    {{ date('h:i A', strtotime($c->start_time)) }} - {{ date('h:i A', strtotime($c->end_time)) }}
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i> Room:</span>
                                <strong class="text-dark small">{{ $c->room }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted"><i class="fa-solid fa-user-ninja me-1"></i> Instructor:</span>
                                <strong class="text-dark small">{{ $c->trainer?->full_name ?? 'Gym Staff' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="fa-solid fa-users me-1"></i> Capacity:</span>
                                <strong class="text-dark small">{{ $c->capacity }} Spots Max</strong>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top">
                        <a href="{{ route('classes.show', $c) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                            <i class="fa-solid fa-clipboard-user me-1"></i> View Roster & Book Spots
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="fa-solid fa-calendar-xmark fa-2x text-muted mb-2"></i>
            <p class="text-muted">No group classes scheduled for this day.</p>
        </div>
    @endforelse
</div>

<!-- Modal: Create New Class -->
<div class="modal fade" id="createClassModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('classes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Schedule New Fitness Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Class Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. HIIT Power Blast">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Color Tag</label>
                            <input type="color" name="color_tag" class="form-control form-control-color w-100" value="#2563eb">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="What members can expect from this workout..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Day of Week <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-select" required>
                                @foreach($daysOfWeek as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Coach / Instructor</label>
                            <select name="trainer_id" class="form-select">
                                <option value="">Select Instructor (Optional)</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }} ({{ implode(', ', array_slice($t->specialties ?? [], 0, 2)) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Studio / Room <span class="text-danger">*</span></label>
                            <input type="text" name="room" class="form-control" value="Main Studio A" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="08:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" value="09:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Max Capacity (Members) <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control" value="25" min="1" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Difficulty Level</label>
                            <select name="difficulty_level" class="form-select">
                                <option value="all_levels">All Levels Welcome</option>
                                <option value="beginner">Beginner Friendly</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced Athletes Only</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Class Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
