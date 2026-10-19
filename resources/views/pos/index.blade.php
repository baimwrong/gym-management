@extends('layouts.app')

@section('title', 'Pro-Shop Point of Sale (POS)')
@section('page_title', 'Pro-Shop POS Register')

@section('content')
<div class="row g-4">
    <!-- Left Column: Products Catalog Grid -->
    <div class="col-lg-7 col-xl-8">
        <!-- Search & Category Filter Tabs -->
        <div class="gym-card mb-4">
            <div class="gym-card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <form method="GET" action="{{ route('pos.index') }}">
                            <input type="hidden" name="category" value="{{ $category }}">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search product name, SKU, barcode..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-5 d-flex justify-content-end gap-2">
                        <a href="{{ route('pos.products') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> Inventory Catalog
                        </a>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-1 mt-3 pt-2 border-top">
                    <a href="{{ route('pos.index', ['category' => 'all']) }}" class="btn btn-sm {{ $category === 'all' ? 'btn-dark' : 'btn-light border' }}">
                        All Items
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('pos.index', ['category' => $cat]) }}" class="btn btn-sm {{ $category === $cat ? 'btn-dark' : 'btn-light border' }} text-capitalize">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-3">
            @forelse($products as $prod)
                <div class="col-sm-6 col-md-4 col-xl-3">
                    <div class="gym-card h-100 p-3 d-flex flex-column justify-content-between position-relative product-card {{ $prod->stock_quantity <= 0 ? 'opacity-50' : '' }}" 
                         data-product-id="{{ $prod->id }}"
                         data-product-name="{{ $prod->name }}"
                         data-product-price="{{ $prod->selling_price }}"
                         data-product-stock="{{ $prod->stock_quantity }}"
                         style="cursor: {{ $prod->stock_quantity > 0 ? 'pointer' : 'not-allowed' }}; transition: transform 0.15s ease;">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-secondary border text-capitalize" style="font-size: 0.65rem;">
                                    {{ $prod->category }}
                                </span>
                                @if($prod->stock_quantity <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($prod->is_low_stock)
                                    <span class="badge bg-warning text-dark">{{ $prod->stock_quantity }} left</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">{{ $prod->stock_quantity }} in stock</span>
                                @endif
                            </div>

                            <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $prod->name }}">{{ $prod->name }}</h6>
                            <small class="text-muted d-block mb-2"><code>{{ $prod->sku }}</code></small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="fs-5 fw-bold text-primary">@currency($prod->selling_price)</span>
                            <button type="button" class="btn btn-sm btn-primary rounded-circle" style="width: 32px; height: 32px; padding: 0;" {{ $prod->stock_quantity <= 0 ? 'disabled' : '' }}>
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fa-solid fa-box-open fa-2x mb-2 text-secondary"></i>
                    <p class="mb-0">No products found matching the criteria.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Interactive Cart & Checkout Panel -->
    <div class="col-lg-5 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px;">
            <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-cart-shopping me-2 text-info"></i> Active Sale Cart
                </h6>
                <button type="button" class="btn btn-sm btn-outline-light border-0 py-0" onclick="clearCart()">
                    <i class="fa-solid fa-trash-can me-1"></i> Clear
                </button>
            </div>

            <form action="{{ route('pos.checkout') }}" method="POST" id="checkout-form">
                @csrf
                <div class="card-body p-3">
                    <!-- Customer selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">CUSTOMER / MEMBER</label>
                        <select name="member_id" class="form-select form-select-sm" id="customer-select">
                            <option value="">Walk-In Guest Customer</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->member_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cart Items Container -->
                    <div id="cart-items-container" class="border rounded p-2 mb-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                        <p class="text-muted small text-center my-4" id="empty-cart-msg">Cart is empty. Tap any product to add to cart.</p>
                    </div>

                    <!-- Financial Summary -->
                    <div class="p-3 bg-light rounded border mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal:</span>
                            <strong id="cart-subtotal">{{ config('gym.currency_symbol', '$') }}0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Discount ({{ config('gym.currency_symbol', '$') }}):</span>
                            <input type="number" step="0.5" min="0" name="discount_amount" id="cart-discount" class="form-control form-control-sm text-end" style="width: 85px;" value="0" oninput="recalculateTotals()">
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Estimated Tax ({{ config('gym.tax_rate_percentage') }}%):</span>
                            <span id="cart-tax">{{ config('gym.currency_symbol', '$') }}0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 fw-bold text-dark">
                            <span>TOTAL:</span>
                            <span class="text-primary" id="cart-total">{{ config('gym.currency_symbol', '$') }}0.00</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">PAYMENT METHOD</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash Tendered</option>
                            <option value="credit_card">Credit Card (POS)</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="qris_pos">QRIS / Mobile QR</option>
                            <option value="bank_transfer">Direct Bank Transfer</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm" id="checkout-btn" disabled>
                        <i class="fa-solid fa-check-circle me-1"></i> COMPLETE SALE & PRINT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = {};
const TAX_RATE = {{ (float) config('gym.tax_rate_percentage', 8.5) }} / 100;
const CURRENCY_SYMBOL = @json(config('gym.currency_symbol', '$'));

function addToCart(id, name, price, maxStock) {
    if (cart[id]) {
        if (cart[id].qty < maxStock) {
            cart[id].qty++;
        } else {
            alert('Cannot add more: Max available stock reached (' + maxStock + ')');
        }
    } else {
        cart[id] = { id: id, name: name, price: price, maxStock: maxStock, qty: 1 };
    }
    renderCart();
}

function updateQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) {
        delete cart[id];
    } else if (cart[id].qty > cart[id].maxStock) {
        cart[id].qty = cart[id].maxStock;
        alert('Max available stock reached (' + cart[id].maxStock + ')');
    }
    renderCart();
}

function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items-container');
    const emptyMsg = document.getElementById('empty-cart-msg');
    const checkoutBtn = document.getElementById('checkout-btn');
    const items = Object.values(cart);

    if (items.length === 0) {
        container.innerHTML = '<p class="text-muted small text-center my-4" id="empty-cart-msg">Cart is empty. Tap any product to add to cart.</p>';
        checkoutBtn.disabled = true;
        recalculateTotals();
        return;
    }

    checkoutBtn.disabled = false;
    let html = '';

    items.forEach((item, index) => {
        const lineTotal = (item.price * item.qty).toFixed(2);
        const safeName = item.name.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        html += `
            <div class="d-flex align-items-center justify-content-between p-2 mb-1 bg-white rounded border small">
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
                <div class="text-truncate me-2" style="max-width: 130px;">
                    <div class="fw-bold text-dark text-truncate">${safeName}</div>
                    <small class="text-muted">${CURRENCY_SYMBOL}${item.price.toFixed(2)} each</small>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="updateQty(${item.id}, -1)">-</button>
                    <span class="fw-bold px-1">${item.qty}</span>
                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="updateQty(${item.id}, 1)">+</button>
                </div>
                <div class="fw-bold text-end" style="min-width: 55px;">
                    ${CURRENCY_SYMBOL}${lineTotal}
                </div>
                <button type="button" class="btn btn-sm text-danger border-0 p-0 ms-1" onclick="removeFromCart(${item.id})">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;
    });

    container.innerHTML = html;
    recalculateTotals();
}

function recalculateTotals() {
    let subtotal = 0;
    Object.values(cart).forEach(item => {
        subtotal += item.price * item.qty;
    });

    const discountInput = parseFloat(document.getElementById('cart-discount').value) || 0;
    const taxable = Math.max(0, subtotal - discountInput);
    const tax = taxable * TAX_RATE;
    const total = taxable + tax;

    document.getElementById('cart-subtotal').innerText = CURRENCY_SYMBOL + subtotal.toFixed(2);
    document.getElementById('cart-tax').innerText = CURRENCY_SYMBOL + tax.toFixed(2);
    document.getElementById('cart-total').innerText = CURRENCY_SYMBOL + total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function () {
            const stock = parseInt(this.dataset.productStock, 10);
            if (stock <= 0) return;
            const id = parseInt(this.dataset.productId, 10);
            const name = this.dataset.productName;
            const price = parseFloat(this.dataset.productPrice);
            addToCart(id, name, price, stock);
        });
    });
});
</script>
@endpush
