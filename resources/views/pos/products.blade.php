@extends('layouts.app')

@section('title', 'Pro-Shop Inventory Catalog')
@section('page_title', 'Pro-Shop Products & Inventory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Pro-Shop Inventory Catalog</h4>
        <p class="text-muted small mb-0">Track supplements, gear, apparel, snacks, cost vs selling margins, and reorder levels.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('import.index') }}" class="btn btn-outline-success btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-file-import me-1"></i> Bulk Import CSV
        </a>
        <a href="{{ route('pos.index') }}" class="btn btn-outline-dark btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-cash-register me-1"></i> Open POS Checkout
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Product
        </button>
    </div>
</div>

<div class="gym-card mb-4">
    <div class="gym-card-body p-3">
        <form method="GET" action="{{ route('pos.products') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search product name, SKU..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Inventory Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }} class="text-capitalize">{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Filter</button>
                <a href="{{ route('pos.products') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>SKU & Product</th>
                        <th>Category</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Margin %</th>
                        <th>Stock Level</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $p->name }}</div>
                                <small class="text-muted">SKU: <code>{{ $p->sku }}</code> &bull; Barcode: {{ $p->barcode ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-capitalize">{{ $p->category }}</span>
                            </td>
                            <td>@currency($p->cost_price)</td>
                            <td class="fw-bold text-primary">@currency($p->selling_price)</td>
                            <td>
                                <span class="badge bg-success-subtle text-success border">+{{ $p->profit_margin_percentage }}%</span>
                            </td>
                            <td>
                                @if($p->stock_quantity <= 0)
                                    <span class="badge bg-danger">OUT OF STOCK (0)</span>
                                @elseif($p->is_low_stock)
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> LOW ({{ $p->stock_quantity }})</span>
                                @else
                                    <span class="fw-bold text-dark">{{ $p->stock_quantity }} units</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $p->is_active ? 'active' : 'expired' }}">
                                    {{ $p->is_active ? 'Active' : 'Archived' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $p->id }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal: Edit Product -->
                        <div class="modal fade" id="editProductModal{{ $p->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('pos.products.update', $p) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Product - {{ $p->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Product Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $p->name }}" required>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">SKU</label>
                                                    <input type="text" name="sku" class="form-control" value="{{ $p->sku }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Category</label>
                                                    <select name="category" class="form-select" required>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat }}" {{ $p->category === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Cost Price ($)</label>
                                                    <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ $p->cost_price }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Selling Price ($)</label>
                                                    <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ $p->selling_price }}" required>
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Current Stock Qty</label>
                                                    <input type="number" name="stock_quantity" class="form-control" value="{{ $p->stock_quantity }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Low Stock Threshold</label>
                                                    <input type="number" name="low_stock_threshold" class="form-control" value="{{ $p->low_stock_threshold }}" required>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="active{{ $p->id }}" value="1" {{ $p->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold" for="active{{ $p->id }}">Active for Sale</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Update Inventory</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No products found in inventory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="gym-card-footer p-3 border-top">
            {{ $products->links() }}
        </div>
    @endif
</div>

<!-- Modal: Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pos.products.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Inventory Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Barebells Protein Bar (Chocolate)">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" required placeholder="e.g. SNACK-BAR-09">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Cost Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="cost_price" class="form-control" value="5.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Selling Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" value="12.00" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Initial Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" value="20" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Low Stock Alert Threshold</label>
                            <input type="number" name="low_stock_threshold" class="form-control" value="5" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Add to Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
