@extends('layouts.app')

@section('title', 'Members Directory')
@section('page_title', 'Member Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Members Directory</h4>
        <p class="text-muted small mb-0">Manage gym memberships, renewals, fitness metrics, and access passes.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('import.index') }}" class="btn btn-outline-success btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-file-import me-1"></i> Bulk Import CSV
        </a>
        <a href="{{ route('reports.export-members') }}" class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-file-csv me-1"></i> Export CSV
        </a>
        <a href="{{ route('members.create') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Add New Member
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="gym-card mb-4">
    <div class="gym-card-body p-3">
        <form method="GET" action="{{ route('members.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search by name, member code, email, phone, RFID..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Member Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Members</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired Subscriptions</option>
                    <option value="frozen" {{ request('status') === 'frozen' ? 'selected' : '' }}>Frozen / Paused</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="plan_id" class="form-select form-select-sm">
                    <option value="">All Plans</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ request('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter</button>
                <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Members Table -->
<div class="gym-card">
    <div class="gym-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gym table-hover mb-0">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Code & Contact</th>
                        <th>Active Plan</th>
                        <th>Validity & Days</th>
                        <th>Latest Weight / BMI</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                        {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('members.show', $member) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $member->full_name }}
                                        </a>
                                        <div class="small text-muted text-capitalize">
                                            {{ $member->gender }} &bull; {{ $member->age ? $member->age . ' yrs' : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><code class="fw-bold text-primary">{{ $member->member_code }}</code></div>
                                <small class="text-muted"><i class="fa-solid fa-phone me-1"></i>{{ $member->phone ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($member->activeSubscription)
                                    <span class="badge bg-light text-dark border">
                                        {{ $member->activeSubscription->plan->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">No Active Plan</span>
                                @endif
                            </td>
                            <td>
                                @if($member->activeSubscription)
                                    <div class="fw-semibold text-dark">Until {{ $member->activeSubscription->end_date->format('M d, Y') }}</div>
                                    <small class="{{ $member->activeSubscription->days_remaining <= 7 ? 'text-danger fw-bold' : 'text-success' }}">
                                        {{ $member->activeSubscription->days_remaining }} days left
                                    </small>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($member->latestMetric)
                                    <div class="fw-semibold text-dark">{{ $member->latestMetric->weight_kg }} kg</div>
                                    <small class="text-muted">BMI {{ $member->latestMetric->bmi ?? 'N/A' }} ({{ $member->latestMetric->bmi_category }})</small>
                                @else
                                    <span class="text-muted small">Not measured</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $member->status }}">{{ $member->status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border py-1 px-2" type="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('members.show', $member) }}">
                                                <i class="fa-solid fa-eye text-primary me-2"></i> View Profile
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('members.id-card', $member) }}" target="_blank">
                                                <i class="fa-solid fa-id-card text-success me-2"></i> Digital ID Badge
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('members.edit', $member) }}">
                                                <i class="fa-solid fa-pen text-secondary me-2"></i> Edit Details
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-users-slash fa-2x mb-2 text-secondary"></i>
                                <p class="mb-0">No gym members found matching the specified filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($members->hasPages())
        <div class="gym-card-footer p-3 border-top">
            {{ $members->links() }}
        </div>
    @endif
</div>
@endsection
