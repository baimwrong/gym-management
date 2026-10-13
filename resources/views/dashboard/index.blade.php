@extends('layouts.app')

@section('title', 'Executive Dashboard')
@section('page_title', 'Operational Overview')

@section('content')
<div class="row g-3 mb-4">
    <!-- Active Members KPI -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-value">{{ $activeMembers }}</div>
                <div class="stat-label">Active Members</div>
                <small class="text-muted">{{ $totalMembers }} Total Registered</small>
            </div>
        </div>
    </div>

    <!-- Currently Inside / Today's Checkins -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success">
                <i class="fa-solid fa-person-walking-arrow-right"></i>
            </div>
            <div>
                <div class="stat-value">{{ $currentlyInside }}</div>
                <div class="stat-label">Inside Gym Now</div>
                <small class="text-success fw-semibold"><i class="fa-solid fa-check me-1"></i>{{ $todayCheckIns }} Check-ins Today</small>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info-subtle text-info">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div class="stat-value">@currency($monthlyRevenue)</div>
                <div class="stat-label">Monthly Revenue</div>
                <small class="text-muted">{{ date('F Y') }} Total</small>
            </div>
        </div>
    </div>

    <!-- Expiring Soon / Inventory alerts -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning-subtle text-warning">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <div class="stat-value">{{ $expiringSubscriptions }}</div>
                <div class="stat-label">Expiring (7 Days)</div>
                @if($lowStockCount > 0)
                    <small class="text-danger fw-semibold"><i class="fa-solid fa-boxes-packing me-1"></i>{{ $lowStockCount }} Low Stock Items</small>
                @else
                    <small class="text-success">Inventory Healthy</small>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
        <div class="gym-card h-100">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-chart-area text-primary me-2"></i> Monthly Revenue Trend
                    </h6>
                    <small class="text-muted">Recorded revenue across past 6 months</small>
                </div>
                <span class="badge bg-light text-dark border">Currency: {{ config('gym.currency_code', 'USD') }}</span>
            </div>
            <div class="gym-card-body">
                <div style="height: 280px; position: relative;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Membership Plan Breakdown Chart -->
    <div class="col-lg-4">
        <div class="gym-card h-100">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-chart-pie text-info me-2"></i> Plan Distribution
                    </h6>
                    <small class="text-muted">Active subscriptions by tier</small>
                </div>
            </div>
            <div class="gym-card-body d-flex flex-column align-items-center justify-content-center">
                <div style="height: 240px; width: 100%; position: relative;">
                    <canvas id="planChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row: Hourly Distribution & Today's Classes -->
<div class="row g-4 mb-4">
    <!-- Hourly Attendance Distribution -->
    <div class="col-lg-7">
        <div class="gym-card h-100">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-fire text-danger me-2"></i> Peak Gym Hours (Today)
                    </h6>
                    <small class="text-muted">Attendance check-in frequency by hour of the day</small>
                </div>
                <a href="{{ route('attendance.kiosk') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-door-open me-1"></i> Open Kiosk
                </a>
            </div>
            <div class="gym-card-body">
                <div style="height: 230px; position: relative;">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduled Classes Today -->
    <div class="col-lg-5">
        <div class="gym-card h-100">
            <div class="gym-card-header">
                <div>
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-calendar-check text-success me-2"></i> Classes Scheduled Today
                    </h6>
                    <small class="text-muted">{{ date('l, M d') }}</small>
                </div>
                <a href="{{ route('classes.index') }}" class="btn btn-sm btn-link text-decoration-none">Full Timetable</a>
            </div>
            <div class="gym-card-body p-0">
                @if($classesToday->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-calendar-xmark fa-2x mb-2 text-secondary"></i>
                        <p class="mb-0">No group classes scheduled for today.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($classesToday as $c)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="badge rounded-pill text-white" style="background-color: {{ $c->color_tag }}; width: 12px; height: 12px; padding: 0;"></div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $c->title }}</div>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-location-dot me-1"></i>{{ $c->room }} &bull; Coach {{ $c->trainer?->full_name ?? 'Staff' }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-light text-dark border fw-bold">
                                        {{ date('h:i A', strtotime($c->start_time)) }} - {{ date('h:i A', strtotime($c->end_time)) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Check-ins Table -->
<div class="gym-card">
    <div class="gym-card-header">
        <div>
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Recent Turnstile / Kiosk Activity
            </h6>
            <small class="text-muted">Real-time check-ins recorded at the front gate</small>
        </div>
        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary">View All Logs</a>
    </div>
    <div class="gym-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gym table-hover mb-0">
                <thead>
                    <tr>
                        <th>Member Code</th>
                        <th>Member Name</th>
                        <th>Check-in Time</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCheckIns as $att)
                        <tr>
                            <td>
                                <a href="{{ route('members.show', $att->member) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $att->member->member_code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $att->member->full_name }}</div>
                                <small class="text-muted">{{ $att->member->phone ?? 'No phone' }}</small>
                            </td>
                            <td>
                                <div>{{ $att->check_in_time->format('h:i A') }}</div>
                                <small class="text-muted">{{ $att->check_in_time->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $att->check_in_method) }}
                                </span>
                            </td>
                            <td>
                                @if($att->check_out_time)
                                    <span class="badge-status active">Checked Out ({{ $att->duration_minutes }}m)</span>
                                @else
                                    <span class="badge-status occupied">
                                        <i class="fa-solid fa-circle text-success" style="font-size: 6px;"></i> Inside Gym
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$att->check_out_time)
                                    <form action="{{ route('attendance.checkout', $att) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Check Out Member">
                                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Check Out
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No attendance activity recorded yet today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Monthly Revenue Chart (Chart.js Offline)
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueLabels) !!},
            datasets: [{
                label: 'Monthly Revenue ($)',
                data: {!! json_encode($revenueData) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: function(val) { return '$' + val; }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Membership Plan Breakdown (Doughnut)
    const ctxPlan = document.getElementById('planChart').getContext('2d');
    new Chart(ctxPlan, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($planLabels) !!},
            datasets: [{
                data: {!! json_encode($planCounts) !!},
                backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            },
            cutout: '68%'
        }
    });

    // 3. Hourly Attendance Distribution (Bar Chart)
    const ctxHourly = document.getElementById('hourlyChart').getContext('2d');
    new Chart(ctxHourly, {
        type: 'bar',
        data: {
            labels: {!! json_encode($hourlyLabels) !!},
            datasets: [{
                label: 'Check-ins',
                data: {!! json_encode($hourlyCounts) !!},
                backgroundColor: '#f59e0b',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
