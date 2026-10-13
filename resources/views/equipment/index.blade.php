@extends('layouts.app')

@section('title', 'Equipment & Maintenance')
@section('page_title', 'Gym Equipment Registry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Gym Equipment & Maintenance</h4>
        <p class="text-muted small mb-0">Track treadmills, racks, cable stations, warranty expirations, and safety inspections.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('import.index') }}" class="btn btn-outline-success btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-file-import me-1"></i> Bulk Import CSV
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
            <i class="fa-solid fa-plus me-1"></i> Register New Equipment
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-dumbbell"></i></div>
            <div>
                <div class="stat-value">{{ $totalCount }}</div>
                <div class="stat-label">Registered Machines / Gear</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="stat-value">{{ $operationalCount }}</div>
                <div class="stat-label">Fully Operational</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon {{ $needsServiceCount > 0 ? 'bg-warning-subtle text-warning' : 'bg-light text-muted' }}">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="stat-value">{{ $needsServiceCount }}</div>
                <div class="stat-label">Pending Service / Repairs</div>
            </div>
        </div>
    </div>
</div>

<!-- Category Tabs Filter -->
<div class="gym-card mb-4">
    <div class="gym-card-body p-2">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link py-2 {{ $category === 'all' ? 'active' : '' }}" href="{{ route('equipment.index', ['category' => 'all']) }}">
                    All Equipment
                </a>
            </li>
            @foreach($categories as $cat)
                <li class="nav-item">
                    <a class="nav-link py-2 {{ $category === $cat ? 'active' : '' }} text-capitalize" href="{{ route('equipment.index', ['category' => $cat]) }}">
                        {{ str_replace('_', ' ', $cat) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="gym-card">
    <div class="gym-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gym table-hover mb-0">
                <thead>
                    <tr>
                        <th>Equipment Name</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Serial Number</th>
                        <th>Last Service</th>
                        <th>Next Service Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipments as $eq)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $eq->name }}</div>
                                @if($eq->notes)
                                    <small class="text-muted">{{ Str::limit($eq->notes, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border text-capitalize">
                                    {{ str_replace('_', ' ', $eq->category) }}
                                </span>
                            </td>
                            <td>{{ $eq->location }}</td>
                            <td><code>{{ $eq->serial_number ?? 'N/A' }}</code></td>
                            <td>{{ $eq->last_service_date ? $eq->last_service_date->format('M d, Y') : 'None recorded' }}</td>
                            <td>
                                @if($eq->next_service_due)
                                    <span class="{{ $eq->is_service_due ? 'text-danger fw-bold' : 'text-dark' }}">
                                        {{ $eq->next_service_due->format('M d, Y') }}
                                        @if($eq->is_service_due)
                                            <i class="fa-solid fa-triangle-exclamation ms-1" title="Service Overdue"></i>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $eq->status }}">{{ str_replace('_', ' ', $eq->status) }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#logServiceModal{{ $eq->id }}" title="Log Maintenance Service">
                                    <i class="fa-solid fa-wrench me-1"></i> Log Service
                                </button>
                            </td>
                        </tr>

                        <!-- Modal: Log Service -->
                        <div class="modal fade" id="logServiceModal{{ $eq->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('equipment.log-service', $eq) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Log Service & Safety Inspection</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-3">Equipment: <strong>{{ $eq->name }}</strong> (<code>{{ $eq->serial_number }}</code>)</p>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Service Completion Date</label>
                                                <input type="date" name="service_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Next Service Due In</label>
                                                <select name="next_service_months" class="form-select" required>
                                                    <option value="3">3 Months</option>
                                                    <option value="6" selected>6 Months</option>
                                                    <option value="12">12 Months (1 Year)</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold">Technician Notes / Parts Replaced</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Cables replaced, belt tension calibrated, lubricated bearings."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success fw-bold">Confirm Service Completed</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No equipment found matching this category.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Equipment -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('equipment.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Register Gym Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Equipment Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Life Fitness Signature Leg Press">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Serial / Model #</label>
                            <input type="text" name="serial_number" class="form-control" placeholder="e.g. LF-LP-9921">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Location / Floor Zone <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" value="Main Fitness Floor" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Warranty Expiry Date</label>
                            <input type="date" name="warranty_expiry" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Initial Status</label>
                            <select name="status" class="form-select" required>
                                <option value="operational">Operational</option>
                                <option value="needs_service">Needs Service</option>
                                <option value="under_maintenance">Under Maintenance</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Specifications, capacity, dealer notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Register Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
