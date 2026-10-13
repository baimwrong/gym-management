@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page_title', 'Invoice Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 btn-no-print">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Invoice: {{ $invoice->invoice_number }}</h4>
        <p class="text-muted small mb-0">Issued on {{ $invoice->created_at->format('F d, Y \a\t h:i A') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> All Invoices
        </a>
        <button type="button" onclick="window.print()" class="btn btn-dark btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-print me-1"></i> Print / Download PDF
        </button>
        @if($invoice->payment_status !== 'paid')
            <button type="button" class="btn btn-success btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#markPaidModal">
                <i class="fa-solid fa-check me-1"></i> Record Payment
            </button>
        @endif
    </div>
</div>

<!-- A4 Print-Ready Invoice Card -->
<div class="gym-card shadow-sm p-4 p-md-5 bg-white rounded-3 mx-auto" style="max-width: 850px;">
    <!-- Invoice Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-dumbbell text-primary fa-2x"></i>
                <h3 class="fw-black mb-0 tracking-tight text-slate-900">{{ config('gym.name') }}</h3>
            </div>
            <p class="text-muted small mb-1">{{ config('gym.tagline') }}</p>
            <p class="text-muted small mb-0">
                {{ config('gym.address') }}, {{ config('gym.city') }} {{ config('gym.postal_code') }}<br>
                Phone: {{ config('gym.phone') }} &bull; Email: {{ config('gym.email') }}<br>
                Tax ID: {{ config('gym.tax_number') }}
            </p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold text-uppercase tracking-wider text-secondary mb-1">INVOICE</h2>
            <div class="fs-5 fw-bold text-dark font-monospace mb-2">{{ $invoice->invoice_number }}</div>
            <span class="badge-status {{ $invoice->payment_status }} fs-6 px-3 py-1">
                {{ strtoupper($invoice->payment_status) }}
            </span>
        </div>
    </div>

    <!-- Invoice Details Metadata -->
    <div class="row g-4 mb-4 pb-2">
        <div class="col-sm-6">
            <div class="text-muted small fw-bold text-uppercase mb-1">BILLED TO:</div>
            <h5 class="fw-bold mb-1 text-dark">{{ $invoice->customer_name }}</h5>
            @if($invoice->member)
                <p class="small text-muted mb-0">
                    Member Code: <code>{{ $invoice->member->member_code }}</code><br>
                    Phone: {{ $invoice->member->phone ?? 'N/A' }}<br>
                    Email: {{ $invoice->member->email ?? 'N/A' }}
                </p>
            @else
                <p class="small text-muted mb-0">Walk-in Customer / Retail Buyer</p>
            @endif
        </div>
        <div class="col-sm-6 text-sm-end">
            <div class="text-muted small fw-bold text-uppercase mb-1">PAYMENT DETAILS:</div>
            <ul class="list-unstyled small text-muted mb-0">
                <li>Date Issued: <strong class="text-dark">{{ $invoice->created_at->format('M d, Y') }}</strong></li>
                @if($invoice->due_date)
                    <li>Due Date: <strong class="text-dark">{{ $invoice->due_date->format('M d, Y') }}</strong></li>
                @endif
                <li>Payment Method: <strong class="text-dark text-uppercase">{{ str_replace('_', ' ', $invoice->payment_method) }}</strong></li>
                @if($invoice->paid_at)
                    <li>Paid At: <strong class="text-success">{{ $invoice->paid_at->format('M d, Y h:i A') }}</strong></li>
                @endif
                @if($invoice->user)
                    <li>Issued By: <span>{{ $invoice->user->name }}</span></li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="py-2">Item Description</th>
                    <th class="text-center py-2" style="width: 100px;">Qty</th>
                    <th class="text-end py-2" style="width: 140px;">Unit Price</th>
                    <th class="text-end py-2" style="width: 140px;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-3">
                            <span class="fw-semibold text-dark">{{ $item->item_description }}</span>
                        </td>
                        <td class="text-center py-3">{{ $item->quantity }}</td>
                        <td class="text-end py-3">@currency($item->unit_price)</td>
                        <td class="text-end py-3 fw-bold text-dark">@currency($item->total_price)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals Summary & Barcode -->
    <div class="row align-items-center mb-5">
        <div class="col-sm-6 mb-3 mb-sm-0 text-center text-sm-start">
            <div class="d-inline-block">
                {!! $barcodeSvg !!}
            </div>
            <div class="text-muted small mt-2">
                Thank you for training with {{ config('gym.name') }}!
            </div>
        </div>
        <div class="col-sm-6">
            <div class="p-3 bg-light rounded border">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Subtotal:</span>
                    <strong class="text-dark">@currency($invoice->subtotal)</strong>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="d-flex justify-content-between small text-success mb-1">
                        <span>Discount Applied:</span>
                        <strong>-@currency($invoice->discount_amount)</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Sales Tax ({{ config('gym.tax_rate_percentage') }}%):</span>
                    <strong class="text-dark">@currency($invoice->tax_amount)</strong>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 fs-5 fw-bold text-dark">
                    <span>Total Amount:</span>
                    <span class="text-primary">@currency($invoice->total_amount)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Footer -->
    <div class="border-top pt-3 text-muted small text-center" style="font-size: 0.75rem;">
        All membership fees and pro-shop items are subject to the standard {{ config('gym.name') }} Terms & Conditions. For billing inquiries, contact {{ config('gym.email') }}.
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Record Payment for {{ $invoice->invoice_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Amount to collect: <strong class="fs-5 text-primary d-block">@currency($invoice->total_amount)</strong></p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Tendered Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card POS</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="qris_pos">QRIS / Mobile QR</option>
                            <option value="bank_transfer">Direct Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Confirm & Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
