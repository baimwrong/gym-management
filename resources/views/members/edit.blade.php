@extends('layouts.app')

@section('title', 'Edit Member - ' . $member->full_name)
@section('page_title', 'Edit Member Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-slate-800">Edit Member Profile</h4>
                <p class="text-muted small mb-0">Updating information for <strong>{{ $member->full_name }}</strong> (<code>{{ $member->member_code }}</code>)</p>
            </div>
            <a href="{{ route('members.show', $member) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Cancel & View Profile
            </a>
        </div>

        <form action="{{ route('members.update', $member) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="gym-card mb-4">
                <div class="gym-card-header">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="fa-solid fa-user-pen text-primary me-2"></i> Profile & Status
                    </h6>
                </div>
                <div class="gym-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $member->first_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $member->last_name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="male" {{ $member->gender === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $member->gender === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $member->gender === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ $member->date_of_birth?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Membership Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $member->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ $member->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="frozen" {{ $member->status === 'frozen' ? 'selected' : '' }}>Frozen / Paused</option>
                                <option value="suspended" {{ $member->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="inactive" {{ $member->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $member->emergency_contact_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $member->emergency_contact_phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Blood Group</label>
                            <input type="text" name="blood_type" class="form-control" value="{{ old('blood_type', $member->blood_type) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $member->address) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $member->city) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $member->postal_code) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Medical History & Restrictions</label>
                            <textarea name="medical_notes" class="form-control" rows="2">{{ old('medical_notes', $member->medical_notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('members.show', $member) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
