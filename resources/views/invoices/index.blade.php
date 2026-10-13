@extends('layouts.app')

@section('title', 'Billing & Invoices')
@section('page_title', 'Invoices & Financial Billing')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Invoices & Financial Transactions</h4>
        <p class="text-muted small mb-0">Record and review member billing, POS sales, payments, and receipts.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export-invoices') }}" class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-file-csv me-1"></i> Export Invoices CSV
        </a>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Create Custom Invoice
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
            <div>
                <div class="stat-value">@currency($totalRevenue)</div>
                <div class="stat-label">Total Paid Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-danger-subtle text-danger"><i class="fa-solid fa-clock"></i></div>
            <div>
                <div class="stat-value">@currency($unpaidTotal)</div>
                <div class="stat-label">Outstanding / Unpaid</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="gym-card mb-4">
    <div class="gym-card-body p-3">
        <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search invoice #, customer name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid / Pending</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="membership" {{ request('type') === 'membership' ? 'selected' : '' }}>Membership</option>
                    <option value="pos_retail" {{ request('type') === 'pos_retail' ? 'selected' : '' }}>POS Retail</option>
                    <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="gym-card">
    <div class="gym-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gym table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Issued Date</th>
                        <th>Customer / Member</th>
                        <th>Type</th>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv) }}" class="fw-bold text-primary text-decoration-none">
                                    <code>{{ $inv->invoice_number }}</code>
                                </a>
                            </td>
                            <td>{{ $inv->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($inv->member)
                                    <a href="{{ route('members.show', $inv->member) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $inv->customer_name }}
                                    </a>
                                @else
                                    <span class="fw-semibold text-dark">{{ $inv->customer_name }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-capitalize">
                                    {{ str_replace('_', ' ', $inv->invoice_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted text-uppercase">
                                    {{ str_replace('_', ' ', $inv->payment_method) }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold fs-6 text-dark">@currency($inv->total_amount)</span>
                            </td>
                            <td>
                                <span class="badge-status {{ $inv->payment_status }}">
                                    {{ $inv->payment_status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View & Print Invoice">
                                    <i class="fa-solid fa-file-invoice text-primary"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No invoices found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="gym-card-footer p-3 border-top">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
