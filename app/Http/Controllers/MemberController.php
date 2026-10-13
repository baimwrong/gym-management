<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberHealthMetric;
use App\Models\MembershipPlan;
use App\Models\MemberSubscription;
use App\Models\Trainer;
use App\Services\BillingService;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    public function index(Request $request)
    {
        $query = Member::with(['activeSubscription.plan', 'latestMetric']);

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Plan filter
        if ($request->filled('plan_id')) {
            $query->whereHas('activeSubscription', function ($q) use ($request) {
                $q->where('plan_id', $request->plan_id);
            });
        }

        $members = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $plans = MembershipPlan::where('is_active', true)->get();

        return view('members.index', compact('members', 'plans'));
    }

    public function create()
    {
        $plans = MembershipPlan::where('is_active', true)->get();
        $trainers = Trainer::where('is_available', true)->get();
        return view('members.create', compact('plans', 'trainers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'blood_type' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'fitness_goals' => 'nullable|array',
            'medical_notes' => 'nullable|string',
            'plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => 'required|string',
            // Initial health metrics (optional)
            'weight_kg' => 'nullable|numeric|between:30,300',
            'height_cm' => 'nullable|numeric|between:100,250',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Generate unique member code
            $year = date('Y');
            $randomSuffix = strtoupper(Str::random(4));
            $nextId = (Member::max('id') ?? 0) + 1;
            $memberCode = sprintf('GYM-%s-%04d', $year, $nextId);

            $member = Member::create([
                'member_code' => $memberCode,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'blood_type' => $validated['blood_type'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'fitness_goals' => $validated['fitness_goals'] ?? [],
                'medical_notes' => $validated['medical_notes'] ?? null,
                'barcode_value' => $memberCode,
                'status' => 'active',
                'joined_date' => now()->toDateString(),
            ]);

            // Create initial subscription & invoice
            $plan = MembershipPlan::findOrFail($validated['plan_id']);
            $this->billingService->createSubscription($member, $plan, [
                'payment_method' => $validated['payment_method'],
                'start_date' => now()->toDateString(),
            ]);

            // Save initial health metrics if provided
            if (!empty($validated['weight_kg']) && !empty($validated['height_cm'])) {
                MemberHealthMetric::create([
                    'member_id' => $member->id,
                    'recorded_date' => now()->toDateString(),
                    'weight_kg' => $validated['weight_kg'],
                    'height_cm' => $validated['height_cm'],
                    'notes' => 'Baseline enrollment measurements',
                    'recorded_by_user_id' => auth()->id(),
                ]);
            }

            return redirect()->route('members.show', $member)
                ->with('success', "Member {$member->full_name} registered successfully with code {$memberCode}!");
        });
    }

    public function show(Member $member)
    {
        $member->load([
            'subscriptions.plan',
            'healthMetrics.recordedBy',
            'attendances.checkedInBy',
            'trainerAssignments.trainer',
            'classBookings.fitnessClass',
            'locker',
            'invoices.items',
            'workoutPlans',
            'dietPlans',
        ]);

        $plans = MembershipPlan::where('is_active', true)->get();
        $trainers = Trainer::where('is_available', true)->get();

        // Generate offline QR Code and Barcode SVG
        $qrCodeSvg = QrCodeService::generateSvg($member->member_code, 150);
        $barcodeSvg = QrCodeService::generateBarcodeSvg($member->member_code, 220, 50);

        return view('members.show', compact('member', 'plans', 'trainers', 'qrCodeSvg', 'barcodeSvg'));
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'blood_type' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'required|in:active,expired,frozen,suspended,inactive',
            'fitness_goals' => 'nullable|array',
            'medical_notes' => 'nullable|string',
        ]);

        $member->update($validated);

        return redirect()->route('members.show', $member)
            ->with('success', "Member profile for {$member->full_name} updated successfully.");
    }

    public function storeMetric(Request $request, Member $member)
    {
        $validated = $request->validate([
            'recorded_date' => 'required|date',
            'weight_kg' => 'required|numeric|between:30,300',
            'height_cm' => 'required|numeric|between:100,250',
            'body_fat_percentage' => 'nullable|numeric|between:3,60',
            'muscle_mass_kg' => 'nullable|numeric|between:10,150',
            'chest_cm' => 'nullable|numeric|between:40,200',
            'waist_cm' => 'nullable|numeric|between:40,200',
            'arms_cm' => 'nullable|numeric|between:15,80',
            'thighs_cm' => 'nullable|numeric|between:20,120',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['member_id'] = $member->id;
        $validated['recorded_by_user_id'] = auth()->id();

        MemberHealthMetric::create($validated);

        return back()->with('success', 'Body measurements logged successfully!');
    }

    public function renew(Request $request, Member $member)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => 'required|string',
            'start_date' => 'nullable|date',
        ]);

        $plan = MembershipPlan::findOrFail($validated['plan_id']);
        
        // If current subscription is still active, start date defaults to tomorrow after end date
        $activeSub = $member->activeSubscription;
        $startDate = $validated['start_date'] ?? ($activeSub ? $activeSub->end_date->addDay()->toDateString() : now()->toDateString());

        $this->billingService->createSubscription($member, $plan, [
            'payment_method' => $validated['payment_method'],
            'start_date' => $startDate,
        ]);

        return back()->with('success', "Membership successfully renewed for {$plan->name}!");
    }

    public function freeze(Request $request, Member $member)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:60',
            'reason' => 'nullable|string|max:255',
        ]);

        $sub = $member->activeSubscription;
        if (!$sub) {
            return back()->with('error', 'Cannot freeze: Member does not have an active subscription.');
        }

        $freezeDays = (int) $validated['days'];
        $unfreezeDate = Carbon::today()->addDays($freezeDays);
        $newEndDate = Carbon::parse($sub->end_date)->addDays($freezeDays);

        $sub->update([
            'status' => 'frozen',
            'frozen_at' => now(),
            'unfreeze_date' => $unfreezeDate->toDateString(),
            'end_date' => $newEndDate->toDateString(),
            'notes' => ($sub->notes ?? '') . " [Frozen for {$freezeDays} days: " . ($validated['reason'] ?? 'Requested by member') . "]",
        ]);

        $member->update(['status' => 'frozen']);

        return back()->with('success', "Membership paused/frozen for {$freezeDays} days until {$unfreezeDate->format('M d, Y')}.");
    }

    public function idCard(Member $member)
    {
        $qrCodeSvg = QrCodeService::generateSvg($member->member_code, 160);
        $barcodeSvg = QrCodeService::generateBarcodeSvg($member->member_code, 220, 50);

        return view('members.id-card', compact('member', 'qrCodeSvg', 'barcodeSvg'));
    }
}
