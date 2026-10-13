@extends('layouts.app')

@section('title', 'Data Import & Migration Center')
@section('page_title', 'Bulk Data Import Center')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-slate-800">Bulk Data Import & Migration Hub</h4>
        <p class="text-muted small mb-0">Upload spreadsheets or CSV files to quickly ingest members, pro-shop products, and gym equipment into PostgreSQL 18.</p>
    </div>
</div>

@if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <h6 class="fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> Some rows encountered issues:</h6>
        <ul class="mb-0 small ps-3">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Left Navigation Tabs -->
    <div class="col-lg-3">
        <div class="gym-card p-2">
            <div class="nav flex-column nav-pills" id="importTabs" role="tablist">
                <button class="nav-link active text-start py-3 px-3 fw-semibold d-flex align-items-center gap-2" id="members-import-tab" data-bs-toggle="pill" data-bs-target="#tab-members" type="button">
                    <i class="fa-solid fa-users fs-5 text-primary"></i>
                    <div>
                        <div>Import Members</div>
                        <small class="text-muted fw-normal" style="font-size: 0.7rem;">Enroll members in bulk</small>
                    </div>
                </button>
                <button class="nav-link text-start py-3 px-3 fw-semibold d-flex align-items-center gap-2" id="products-import-tab" data-bs-toggle="pill" data-bs-target="#tab-products" type="button">
                    <i class="fa-solid fa-boxes-stacked fs-5 text-info"></i>
                    <div>
                        <div>Import Pro-Shop Stock</div>
                        <small class="text-muted fw-normal" style="font-size: 0.7rem;">Inventory, costs & prices</small>
                    </div>
                </button>
                <button class="nav-link text-start py-3 px-3 fw-semibold d-flex align-items-center gap-2" id="equipment-import-tab" data-bs-toggle="pill" data-bs-target="#tab-equipment" type="button">
                    <i class="fa-solid fa-screwdriver-wrench fs-5 text-warning"></i>
                    <div>
                        <div>Import Gym Equipment</div>
                        <small class="text-muted fw-normal" style="font-size: 0.7rem;">Machines, platform & gear</small>
                    </div>
                </button>
            </div>
        </div>

        <!-- Offline Guide Note -->
        <div class="gym-card mt-3 p-3 bg-light rounded-3">
            <h6 class="fw-bold text-dark mb-1 small"><i class="fa-solid fa-file-csv text-success me-1"></i> CSV Guidelines:</h6>
            <p class="small text-muted mb-0" style="font-size: 0.75rem;">
                Files must be formatted as comma-separated values (<code>.csv</code>). UTF-8 encoding is recommended. Download our sample templates below for pre-configured column headers.
            </p>
        </div>
    </div>

    <!-- Right Content Pane -->
    <div class="col-lg-9">
        <div class="tab-content" id="importTabsContent">
            <!-- Tab 1: Members Import -->
            <div class="tab-pane fade show active" id="tab-members" role="tabpanel">
                <div class="gym-card">
                    <div class="gym-card-header bg-light">
                        <div>
                            <h5 class="fw-bold mb-0 text-slate-800">
                                <i class="fa-solid fa-user-plus text-primary me-2"></i> Bulk Member Registration
                            </h5>
                            <small class="text-muted">Import full rosters from previous systems or spreadsheets</small>
                        </div>
                        <a href="{{ route('import.sample', 'members') }}" class="btn btn-sm btn-outline-success fw-semibold shadow-sm">
                            <i class="fa-solid fa-download me-1"></i> Download Sample CSV
                        </a>
                    </div>
                    <div class="gym-card-body p-4">
                        <form action="{{ route('import.members') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">1. Upload CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="csv_file" class="form-control form-control-lg" accept=".csv,text/csv" required>
                                <div class="form-text small">Accepted formats: <code>.csv</code>. Maximum size: 10MB.</div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Default Membership Plan</label>
                                    <select name="default_plan_id" class="form-select">
                                        @foreach($plans as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} &mdash; @currency($p->price)</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small">Applied if the <code>plan_slug</code> column is blank in the CSV row.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Initial Payment Tender</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card POS</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="bank_transfer">Direct Bank Transfer</option>
                                    </select>
                                    <div class="form-text small">Payment method recorded for generated enrollment invoices.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold small text-muted text-uppercase mb-2">Supported CSV Columns:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered small">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Column Name</th>
                                                <th>Required</th>
                                                <th>Example</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><code>first_name</code></td><td><span class="badge bg-danger">Yes</span></td><td>Michael</td><td>Member's given name</td></tr>
                                            <tr><td><code>last_name</code></td><td><span class="badge bg-danger">Yes</span></td><td>Stone</td><td>Member's surname</td></tr>
                                            <tr><td><code>email</code></td><td>No</td><td>michael@example.com</td><td>Unique email address</td></tr>
                                            <tr><td><code>phone</code></td><td>No</td><td>+1 (555) 301-4491</td><td>Contact phone number</td></tr>
                                            <tr><td><code>gender</code></td><td>No</td><td>male / female / other</td><td>Default: male</td></tr>
                                            <tr><td><code>date_of_birth</code></td><td>No</td><td>1992-06-15</td><td>Format: YYYY-MM-DD</td></tr>
                                            <tr><td><code>emergency_contact_name</code></td><td>No</td><td>Emma Stone</td><td>Emergency contact person</td></tr>
                                            <tr><td><code>emergency_contact_phone</code></td><td>No</td><td>+1 (555) 301-4499</td><td>Emergency phone</td></tr>
                                            <tr><td><code>blood_type</code></td><td>No</td><td>O+, A+, B+, AB+</td><td>Medical record</td></tr>
                                            <tr><td><code>plan_slug</code></td><td>No</td><td>silver-monthly</td><td>Slug of membership plan</td></tr>
                                            <tr><td><code>status</code></td><td>No</td><td>active / expired</td><td>Default: active</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg fw-bold px-4 shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Start Members Bulk Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Products Import -->
            <div class="tab-pane fade" id="tab-products" role="tabpanel">
                <div class="gym-card">
                    <div class="gym-card-header bg-light">
                        <div>
                            <h5 class="fw-bold mb-0 text-slate-800">
                                <i class="fa-solid fa-boxes-stacked text-info me-2"></i> Bulk Inventory & Products Import
                            </h5>
                            <small class="text-muted">Import supplement catalog, pricing, and quantities (updates existing SKUs)</small>
                        </div>
                        <a href="{{ route('import.sample', 'products') }}" class="btn btn-sm btn-outline-success fw-semibold shadow-sm">
                            <i class="fa-solid fa-download me-1"></i> Download Sample CSV
                        </a>
                    </div>
                    <div class="gym-card-body p-4">
                        <form action="{{ route('import.products') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Upload Products CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="csv_file" class="form-control form-control-lg" accept=".csv,text/csv" required>
                                <div class="form-text small">If an existing SKU is matched, its prices and stock levels will be updated automatically.</div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold small text-muted text-uppercase mb-2">Supported Product Columns:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered small">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Column</th>
                                                <th>Required</th>
                                                <th>Example</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><code>sku</code></td><td><span class="badge bg-danger">Yes</span></td><td>SUPP-WHEY-01</td><td>Unique inventory SKU identifier</td></tr>
                                            <tr><td><code>name</code></td><td><span class="badge bg-danger">Yes</span></td><td>Gold Standard Whey 2kg</td><td>Product title</td></tr>
                                            <tr><td><code>category</code></td><td>No</td><td>supplement</td><td>supplement, beverage, apparel, accessory, snack</td></tr>
                                            <tr><td><code>cost_price</code></td><td>No</td><td>42.00</td><td>Unit acquisition cost ($)</td></tr>
                                            <tr><td><code>selling_price</code></td><td>No</td><td>69.99</td><td>Retail customer selling price ($)</td></tr>
                                            <tr><td><code>stock_quantity</code></td><td>No</td><td>35</td><td>Initial units in stock</td></tr>
                                            <tr><td><code>low_stock_threshold</code></td><td>No</td><td>8</td><td>Alert warning quantity threshold</td></tr>
                                            <tr><td><code>barcode</code></td><td>No</td><td>782910394812</td><td>Product UPC / EAN barcode string</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-info text-white btn-lg fw-bold px-4 shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Start Products Bulk Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Equipment Import -->
            <div class="tab-pane fade" id="tab-equipment" role="tabpanel">
                <div class="gym-card">
                    <div class="gym-card-header bg-light">
                        <div>
                            <h5 class="fw-bold mb-0 text-slate-800">
                                <i class="fa-solid fa-screwdriver-wrench text-warning me-2"></i> Bulk Equipment & Machinery Import
                            </h5>
                            <small class="text-muted">Import commercial cardio machines, rigs, weights, and serial numbers</small>
                        </div>
                        <a href="{{ route('import.sample', 'equipment') }}" class="btn btn-sm btn-outline-success fw-semibold shadow-sm">
                            <i class="fa-solid fa-download me-1"></i> Download Sample CSV
                        </a>
                    </div>
                    <div class="gym-card-body p-4">
                        <form action="{{ route('import.equipment') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Upload Equipment CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="csv_file" class="form-control form-control-lg" accept=".csv,text/csv" required>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold small text-muted text-uppercase mb-2">Supported Equipment Columns:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered small">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Column</th>
                                                <th>Required</th>
                                                <th>Example</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><code>name</code></td><td><span class="badge bg-danger">Yes</span></td><td>Technogym Excite Live Treadmill</td><td>Machine designation</td></tr>
                                            <tr><td><code>category</code></td><td>No</td><td>cardio</td><td>cardio, strength, free_weights, functional, recovery</td></tr>
                                            <tr><td><code>serial_number</code></td><td>No</td><td>TG-RUN-2024-9901</td><td>Manufacturer serial identifier</td></tr>
                                            <tr><td><code>location</code></td><td>No</td><td>Cardio Deck - Level 1</td><td>Gym floor placement</td></tr>
                                            <tr><td><code>purchase_date</code></td><td>No</td><td>2024-01-15</td><td>Date acquired</td></tr>
                                            <tr><td><code>status</code></td><td>No</td><td>operational</td><td>operational, needs_service, under_maintenance</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning btn-lg fw-bold px-4 shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Start Equipment Bulk Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
