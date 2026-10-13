@extends('layouts.app')

@section('title', 'Financial & Operational Reports')
@section('page_title', 'Business Reports & Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Financial & Operational Analytics</h4>
        <p class="text-muted small mb-0">Review revenue sources, retail performance, membership intake, and attendance patterns.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export-members') }}" class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-file-csv me-1"></i> Export Members CSV
        </a>
        <a href="{{ route('reports.export-invoices') }}" class="btn btn-outline-primary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-file-csv me-1"></i> Export Invoices CSV
        </a>
    </div>
</div>

<!-- Date Interval Picker -->
<div class="gym-card mb-4">
    <div class="gym-card-body p-3">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">From:</span>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">To:</span>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="fa-solid fa-filter me-1"></i> Generate Report
                </button>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset Month</a>
            </div>
        </form>
    </div>
</div>

<!-- Executive Financial KPIs -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-sack-dollar"></i></div>
            <div>
                <div class="stat-value">@currency($totalRevenue)</div>
                <div class="stat-label">Total Paid Revenue</div>
                <small class="text-muted">During selected range</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-id-card"></i></div>
            <div>
                <div class="stat-value">@currency($membershipRevenue)</div>
                <div class="stat-label">Membership Revenue</div>
                <small class="text-muted">Recurring subscriptions</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-cash-register"></i></div>
            <div>
                <div class="stat-value">@currency($posRevenue)</div>
                <div class="stat-label">Pro-Shop Retail</div>
                <small class="text-muted">Supplements & Gear</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-receipt"></i></div>
            <div>
                <div class="stat-value">@currency($taxCollected)</div>
                <div class="stat-label">Sales Tax Collected</div>
                <small class="text-muted">{{ config('gym.tax_rate_percentage') }}% liability</small>
            </div>
        </div>
    </div>
</div>

<!-- Operational Volume KPIs -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="gym-card h-100">
            <div class="gym-card-header">
                <h6 class="fw-bold mb-0 text-slate-800">
                    <i class="fa-solid fa-credit-card text-primary me-2"></i> Revenue by Tender / Payment Method
                </h6>
            </div>
            <div class="gym-card-body">
                @if($paymentMethods->isEmpty())
                    <p class="text-muted text-center my-4">No completed sales recorded for this date range.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($paymentMethods as $method => $amount)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <i class="fa-solid fa-circle-dot text-success me-2"></i>
                                    <strong class="text-uppercase text-dark">{{ str_replace('_', ' ', $method) }}</strong>
                                </div>
                                <span class="fs-6 fw-bold text-dark">@currency($amount)</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="gym-card h-100">
            <div class="gym-card-header">
                <h6 class="fw-bold mb-0 text-slate-800">
                    <i class="fa-solid fa-chart-simple text-success me-2"></i> Operational Intake & Traffic
                </h6>
            </div>
            <div class="gym-card-body d-flex flex-column justify-content-around">
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">Turnstile Check-ins During Period</div>
                            <div class="fs-3 fw-bold text-dark">{{ $totalCheckIns }} Visits</div>
                        </div>
                        <div class="p-3 rounded-circle bg-white border text-primary">
                            <i class="fa-solid fa-door-open fa-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">New Members Enrolled</div>
                            <div class="fs-3 fw-bold text-dark">{{ $newMembersCount }} Registrations</div>
                        </div>
                        <div class="p-3 rounded-circle bg-white border text-success">
                            <i class="fa-solid fa-user-plus fa-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
