@extends('layouts.app')

@section('title', $member->full_name . ' - Profile')
@section('page_title', 'Member Profile')

@section('content')
<!-- Header Profile Banner -->
<div class="gym-card mb-4">
    <div class="gym-card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 72px; height: 72px;">
                    {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h3 class="fw-bold mb-0 text-slate-900">{{ $member->full_name }}</h3>
                        <span class="badge-status {{ $member->status }}">{{ $member->status }}</span>
                        @if($member->activeSubscription)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="fa-solid fa-crown me-1"></i> {{ $member->activeSubscription->plan->name }}
                            </span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">
                        <code>{{ $member->member_code }}</code> &bull; Joined {{ $member->joined_date?->format('M d, Y') }} &bull;
                        {{ $member->email ?? 'No email' }} &bull; {{ $member->phone ?? 'No phone' }}
                    </div>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('attendance.process') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="code" value="{{ $member->member_code }}">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm">
                        <i class="fa-solid fa-qrcode me-1"></i> Quick Turnstile Scan
                    </button>
                </form>

                <a href="{{ route('members.id-card', $member) }}" target="_blank" class="btn btn-sm btn-outline-dark fw-semibold shadow-sm">
                    <i class="fa-solid fa-id-badge me-1"></i> Digital Pass
                </a>

                <button type="button" class="btn btn-sm btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#renewModal">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Renew Plan
                </button>

                @if($member->status === 'active')
                <button type="button" class="btn btn-sm btn-outline-warning fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#freezeModal">
                    <i class="fa-solid fa-snowflake me-1"></i> Freeze
                </button>
                @endif

                <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-pen"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Member Card & Quick Specs -->
    <div class="col-lg-4">
        <!-- Offline Digital Pass Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
            <div class="id-badge-header">
                <div class="small text-uppercase tracking-wider opacity-75 fw-bold">{{ config('gym.name') }}</div>
                <h5 class="fw-bold mb-0 text-white">MEMBER ACCESS PASS</h5>
            </div>
            <div class="p-3 text-center bg-white">
                <div class="mb-3 d-inline-block p-2 bg-light rounded border">
                    {!! $qrCodeSvg !!}
                </div>
                <div class="mb-2">
                    {!! $barcodeSvg !!}
                </div>
                <div class="small text-muted">
                    RFID: <code>{{ $member->rfid_card_number ?? 'Not assigned' }}</code>
                </div>
            </div>
            <div class="p-3 bg-light border-top">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Subscription Status:</span>
                    <strong class="text-dark text-capitalize">{{ $member->status }}</strong>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Valid Through:</span>
                    <strong class="text-dark">{{ $member->activeSubscription?->end_date?->format('M d, Y') ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>

        <!-- Personal & Emergency Information -->
        <div class="gym-card mb-4">
            <div class="gym-card-header">
                <h6 class="fw-bold mb-0 text-slate-800">
                    <i class="fa-solid fa-address-card text-primary me-2"></i> Member Profile
                </h6>
            </div>
            <div class="gym-card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Gender & Age:</span>
                        <span class="fw-semibold text-capitalize">{{ $member->gender }} ({{ $member->age ?? 'N/A' }} years)</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Blood Group:</span>
                        <span class="badge bg-danger-subtle text-danger fw-bold">{{ $member->blood_type ?? 'N/A' }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Emergency Contact:</span>
                        <span class="fw-semibold">{{ $member->emergency_contact_name ?? 'N/A' }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Emergency Phone:</span>
                        <span class="fw-semibold">{{ $member->emergency_contact_phone ?? 'N/A' }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Assigned Locker:</span>
                        <span class="fw-semibold text-primary">{{ $member->locker?->locker_number ?? 'None' }}</span>
                    </li>
                </ul>

                @if(!empty($member->fitness_goals))
                    <hr>
                    <div class="fw-bold small text-muted mb-2">FITNESS GOALS:</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($member->fitness_goals as $goal)
                            <span class="badge bg-light text-dark border">{{ $goal }}</span>
                        @endforeach
                    </div>
                @endif

                @if($member->medical_notes)
                    <hr>
                    <div class="fw-bold small text-muted mb-1"><i class="fa-solid fa-notes-medical text-danger me-1"></i> MEDICAL NOTES:</div>
                    <p class="small text-muted mb-0 bg-light p-2 rounded border">{{ $member->medical_notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Tabs -->
    <div class="col-lg-8">
        <div class="gym-card">
            <div class="gym-card-header p-2 border-bottom">
                <ul class="nav nav-pills" id="memberTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-2 px-3 fw-semibold small" id="subs-tab" data-bs-toggle="tab" data-bs-target="#subs" type="button">
                            <i class="fa-solid fa-id-card me-1"></i> Subscriptions
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 px-3 fw-semibold small" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#metrics" type="button">
                            <i class="fa-solid fa-weight-scale me-1"></i> Health & Body Metrics
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 px-3 fw-semibold small" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Attendance Log
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 px-3 fw-semibold small" id="coaching-tab" data-bs-toggle="tab" data-bs-target="#coaching" type="button">
                            <i class="fa-solid fa-dumbbell me-1"></i> Coaching & Routines
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 px-3 fw-semibold small" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button">
                            <i class="fa-solid fa-receipt me-1"></i> Invoices
                        </button>
                    </li>
                </ul>
            </div>

            <div class="gym-card-body">
                <div class="tab-content" id="memberTabContent">
                    <!-- Tab 1: Subscriptions -->
                    <div class="tab-pane fade show active" id="subs" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-slate-800">Membership Subscription History</h6>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#renewModal">
                                <i class="fa-solid fa-plus me-1"></i> Add / Renew Subscription
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-gym table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Plan Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Paid Amount</th>
                                        <th>PT Sessions</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($member->subscriptions as $sub)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $sub->plan->name }}</td>
                                            <td>{{ $sub->start_date->format('M d, Y') }}</td>
                                            <td>{{ $sub->end_date->format('M d, Y') }}</td>
                                            <td class="fw-semibold">@currency($sub->price_paid)</td>
                                            <td>{{ $sub->remaining_pt_sessions }} Left</td>
                                            <td><span class="badge-status {{ $sub->status }}">{{ $sub->status }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No subscriptions on record.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Health & Body Metrics -->
                    <div class="tab-pane fade" id="metrics" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-slate-800">Body Measurements & Progress History</h6>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#logMetricModal">
                                <i class="fa-solid fa-plus me-1"></i> Log Measurement
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-gym table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Weight (kg)</th>
                                        <th>Height (cm)</th>
                                        <th>BMI</th>
                                        <th>Category</th>
                                        <th>Body Fat %</th>
                                        <th>Chest / Waist</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($member->healthMetrics as $m)
                                        <tr>
                                            <td class="fw-semibold">{{ $m->recorded_date->format('M d, Y') }}</td>
                                            <td class="fw-bold text-dark">{{ $m->weight_kg }} kg</td>
                                            <td>{{ $m->height_cm }} cm</td>
                                            <td><span class="badge bg-light text-dark border">{{ $m->bmi ?? 'N/A' }}</span></td>
                                            <td><span class="badge bg-info-subtle text-info border">{{ $m->bmi_category }}</span></td>
                                            <td>{{ $m->body_fat_percentage ? $m->body_fat_percentage . '%' : 'N/A' }}</td>
                                            <td class="small">{{ $m->chest_cm ? $m->chest_cm . 'cm' : '-' }} / {{ $m->waist_cm ? $m->waist_cm . 'cm' : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No health measurements recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: Attendance Log -->
                    <div class="tab-pane fade" id="attendance" role="tabpanel">
                        <h6 class="fw-bold mb-3 text-slate-800">Gym Access & Visit Records</h6>
                        <div class="table-responsive">
                            <table class="table table-gym table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Duration</th>
                                        <th>Gate Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($member->attendances as $att)
                                        <tr>
                                            <td class="fw-semibold">{{ $att->check_in_time->format('M d, Y') }}</td>
                                            <td>{{ $att->check_in_time->format('h:i A') }}</td>
                                            <td>{{ $att->check_out_time ? $att->check_out_time->format('h:i A') : 'Currently inside' }}</td>
                                            <td>
                                                @if($att->duration_minutes)
                                                    {{ $att->duration_minutes }} mins
                                                @else
                                                    <span class="badge bg-success">In Gym</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-light text-secondary border text-uppercase">{{ str_replace('_', ' ', $att->check_in_method) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No attendance visits recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Coaching & Routines -->
                    <div class="tab-pane fade" id="coaching" role="tabpanel">
                        <h6 class="fw-bold mb-3 text-slate-800">Assigned Personal Trainer & Workout Schedule</h6>
                        @if($member->trainerAssignments->isNotEmpty())
                            @foreach($member->trainerAssignments as $assign)
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold text-dark">
                                            <i class="fa-solid fa-user-ninja text-success me-2"></i> Coach {{ $assign->trainer->full_name }}
                                        </div>
                                        <span class="badge bg-primary">{{ $assign->package_name }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mb-1">
                                        <span>Sessions Progress:</span>
                                        <strong>{{ $assign->completed_sessions }} of {{ $assign->total_sessions }} completed</strong>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $assign->progress_percentage }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-light border small text-muted mb-3">
                                <i class="fa-solid fa-info-circle me-1"></i> No dedicated 1-on-1 personal trainer assigned to this member yet.
                            </div>
                        @endif

                        <h6 class="fw-bold mt-4 mb-2 text-slate-800">Assigned Routines & Diets</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded">
                                    <div class="fw-bold small text-muted mb-1">WORKOUT ROUTINE</div>
                                    <div class="fw-semibold">
                                        {{ $member->workoutPlans->first()?->title ?? 'Standard Gym General Routine' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded">
                                    <div class="fw-bold small text-muted mb-1">MEAL & DIET PLAN</div>
                                    <div class="fw-semibold">
                                        {{ $member->dietPlans->first()?->title ?? 'Maintenance Balanced Diet' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: Invoices -->
                    <div class="tab-pane fade" id="invoices" role="tabpanel">
                        <h6 class="fw-bold mb-3 text-slate-800">Member Billing Invoices</h6>
                        <div class="table-responsive">
                            <table class="table table-gym table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th class="text-end">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($member->invoices as $inv)
                                        <tr>
                                            <td><code class="fw-bold text-primary">{{ $inv->invoice_number }}</code></td>
                                            <td>{{ $inv->created_at->format('M d, Y') }}</td>
                                            <td><span class="badge bg-light text-secondary border text-capitalize">{{ str_replace('_', ' ', $inv->invoice_type) }}</span></td>
                                            <td class="fw-bold">@currency($inv->total_amount)</td>
                                            <td><span class="badge-status {{ $inv->payment_status }}">{{ $inv->payment_status }}</span></td>
                                            <td class="text-end">
                                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No invoices generated for this member.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Renew Membership -->
<div class="modal fade" id="renewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('members.renew', $member) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Renew Membership Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Membership Plan</label>
                        <select name="plan_id" class="form-select" required>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" {{ $member->activeSubscription?->plan_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} &mdash; @currency($p->price) ({{ $p->formatted_duration }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="credit_card">Credit Card POS</option>
                            <option value="cash">Cash Payment</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="qris_pos">QRIS / Mobile Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Renewal Start Date (Leave blank to auto-chain)</label>
                        <input type="date" name="start_date" class="form-control" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Confirm Renewal & Issue Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Freeze Membership -->
<div class="modal fade" id="freezeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('members.freeze', $member) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-snowflake text-warning me-2"></i> Pause / Freeze Membership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Temporarily suspend membership access (e.g., medical reasons, travel). The expiration date will be automatically extended by the frozen days.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Number of Days to Freeze</label>
                        <input type="number" name="days" class="form-control" min="1" max="60" value="14" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason for Freeze</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Work travel, medical recovery">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-semibold">Confirm Freeze</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Log Health Metric -->
<div class="modal fade" id="logMetricModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('members.metrics.store', $member) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Log Body Measurements & Health Metric</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Measurement Date</label>
                            <input type="date" name="recorded_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Weight (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="weight_kg" class="form-control" required placeholder="75.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Height (cm) <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="height_cm" class="form-control" value="{{ $member->latestMetric?->height_cm ?? 175 }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Body Fat %</label>
                            <input type="number" step="0.1" name="body_fat_percentage" class="form-control" placeholder="18.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Muscle Mass (kg)</label>
                            <input type="number" step="0.1" name="muscle_mass_kg" class="form-control" placeholder="38.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Chest (cm)</label>
                            <input type="number" step="0.5" name="chest_cm" class="form-control" placeholder="102">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Waist (cm)</label>
                            <input type="number" step="0.5" name="waist_cm" class="form-control" placeholder="82">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Arms (cm)</label>
                            <input type="number" step="0.5" name="arms_cm" class="form-control" placeholder="38">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Thighs (cm)</label>
                            <input type="number" step="0.5" name="thighs_cm" class="form-control" placeholder="60">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notes / Coach Observations</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Notes on progress, symmetry, or diet adherence..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold">Save Measurement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
