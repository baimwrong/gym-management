@extends('layouts.app')

@section('title', 'Register New Gym Member')
@section('page_title', 'New Member Enrollment')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-slate-800">Enroll New Gym Member</h4>
                <p class="text-muted small mb-0">Fill in member details, select a membership subscription plan, and issue enrollment invoice.</p>
            </div>
            <a href="{{ route('members.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Directory
            </a>
        </div>

        <form action="{{ route('members.store') }}" method="POST">
            @csrf

            <!-- Section 1: Personal Info -->
            <div class="gym-card mb-4">
                <div class="gym-card-header">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-user text-primary me-2"></i> 1. Personal & Contact Information
                    </h6>
                </div>
                <div class="gym-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required placeholder="e.g. Marcus">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required placeholder="e.g. Vance">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Blood Type</label>
                            <select name="blood_type" class="form-select">
                                <option value="">Select (Optional)</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="member@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Residential Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Street address">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">City & Postal</label>
                            <div class="input-group">
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="City">
                                <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code') }}" placeholder="ZIP">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Emergency Contact -->
            <div class="gym-card mb-4">
                <div class="gym-card-header">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-heart-pulse text-danger me-2"></i> 2. Emergency Contact
                    </h6>
                </div>
                <div class="gym-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Person Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}" placeholder="e.g. Sarah Miller (Spouse)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}" placeholder="+1 (555) 999-9999">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Membership Plan & Billing -->
            <div class="gym-card mb-4">
                <div class="gym-card-header">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-tags text-warning me-2"></i> 3. Select Membership Plan & Payment
                    </h6>
                </div>
                <div class="gym-card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Choose Membership Plan <span class="text-danger">*</span></label>
                            <select name="plan_id" class="form-select form-select-lg" required>
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}" {{ old('plan_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} &mdash; @currency($p->price) ({{ $p->formatted_duration }}) {{ $p->signup_fee > 0 ? '+ $' . $p->signup_fee . ' Signup fee' : '(No signup fee)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Initial Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-lg" required>
                                <option value="credit_card">Credit Card (POS Terminal)</option>
                                <option value="cash">Cash Payment</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Direct Bank Transfer</option>
                                <option value="qris_pos">QRIS / Mobile Wallet</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Initial Health Baseline -->
            <div class="gym-card mb-4">
                <div class="gym-card-header">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-weight-scale text-success me-2"></i> 4. Baseline Body Metrics & Fitness Goals (Optional)
                    </h6>
                </div>
                <div class="gym-card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Starting Weight (kg)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="weight_kg" class="form-control" value="{{ old('weight_kg', 75.0) }}" placeholder="75.0">
                                <span class="input-group-text">kg</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Height (cm)</label>
                            <div class="input-group">
                                <input type="number" step="0.5" name="height_cm" class="form-control" value="{{ old('height_cm', 175.0) }}" placeholder="175.0">
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Primary Fitness Goals</label>
                            <div class="d-flex flex-wrap gap-2 pt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="Hypertrophy" id="g1" checked>
                                    <label class="form-check-label small" for="g1">Muscle Building</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="Fat Loss" id="g2" checked>
                                    <label class="form-check-label small" for="g2">Fat Loss</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="Strength" id="g3">
                                    <label class="form-check-label small" for="g3">Max Strength</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Medical Notes & Health Considerations</label>
                            <textarea name="medical_notes" class="form-control" rows="2" placeholder="List any past surgeries, joint injuries, heart conditions, or allergies..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('members.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-check me-2"></i> Complete Enrollment & Generate Pass
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
