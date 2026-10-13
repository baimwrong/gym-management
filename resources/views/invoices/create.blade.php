@extends('layouts.app')

@section('title', 'Generate New Invoice')
@section('page_title', 'Create Custom Invoice')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-slate-800">Generate Custom Invoice</h4>
                <p class="text-muted small mb-0">Create manual billing for personal coaching packages, locker rentals, or custom services.</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>

        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf
            <div class="gym-card mb-4">
                <div class="gym-card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Customer / Member</label>
                            <select name="member_id" class="form-select">
                                <option value="">-- Non-Member / Walk-In --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Walk-in Customer Name (If not a member)</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="e.g. John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Status</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="paid">Paid Immediately</option>
                                <option value="unpaid">Unpaid / Send Bill</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="credit_card">Credit Card POS</option>
                                <option value="cash">Cash</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="qris_pos">QRIS / Mobile QR</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Notes / Memo</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional billing notes">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3 text-slate-800"><i class="fa-solid fa-list me-2 text-primary"></i>Invoice Line Items</h6>

                    <div id="line-items-wrapper">
                        <div class="row g-2 mb-2 line-item-row">
                            <div class="col-md-6">
                                <input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Item description" required value="Custom Training Session">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[0][quantity]" class="form-control form-control-sm text-center" placeholder="Qty" value="1" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="items[0][unit_price]" class="form-control" placeholder="Price" value="50.00" required>
                                </div>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addRow()">
                        <i class="fa-solid fa-plus me-1"></i> Add Another Line Item
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Generate Invoice</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let rowIndex = 1;
function addRow() {
    const wrapper = document.getElementById('line-items-wrapper');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 line-item-row';
    div.innerHTML = `
        <div class="col-md-6">
            <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm" placeholder="Item description" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm text-center" placeholder="Qty" value="1" min="1" required>
        </div>
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control" placeholder="Price" value="0.00" required>
            </div>
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button>
        </div>
    `;
    wrapper.appendChild(div);
    rowIndex++;
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.line-item-row');
    if (rows.length > 1) {
        btn.closest('.line-item-row').remove();
    } else {
        alert('Invoice must have at least one line item.');
    }
}
</script>
@endpush
