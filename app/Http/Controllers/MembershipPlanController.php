<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipPlanController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::withCount(['subscriptions' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy('price', 'asc')->get();

        return view('plans.index', compact('plans'));
    }

    public function create()
    {
        return view('plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120|unique:membership_plans,name',
            'description' => 'nullable|string',
            'duration_type' => 'required|in:days,months,years',
            'duration_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'signup_fee' => 'nullable|numeric|min:0',
            'includes_classes' => 'boolean',
            'includes_pt_sessions' => 'required|integer|min:0',
            'max_freeze_days' => 'required|integer|min:0',
            'features' => 'nullable|string', // Comma-separated or newline-separated
            'badge_color' => 'required|string|max:30',
        ]);

        // Parse features into jsonb array
        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['features']))));
        }

        MembershipPlan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'duration_type' => $validated['duration_type'],
            'duration_count' => $validated['duration_count'],
            'price' => $validated['price'],
            'signup_fee' => $validated['signup_fee'] ?? 0.00,
            'includes_classes' => $request->boolean('includes_classes'),
            'includes_pt_sessions' => $validated['includes_pt_sessions'],
            'max_freeze_days' => $validated['max_freeze_days'],
            'features' => $featuresArray,
            'badge_color' => $validated['badge_color'],
            'is_active' => true,
        ]);

        return redirect()->route('plans.index')->with('success', "Plan '{$validated['name']}' created successfully!");
    }

    public function edit(MembershipPlan $plan)
    {
        return view('plans.edit', compact('plan'));
    }

    public function update(Request $request, MembershipPlan $plan)
    {
        $validated = $request->validate([
            'name' => "required|string|max:120|unique:membership_plans,name,{$plan->id}",
            'description' => 'nullable|string',
            'duration_type' => 'required|in:days,months,years',
            'duration_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'signup_fee' => 'nullable|numeric|min:0',
            'includes_classes' => 'boolean',
            'includes_pt_sessions' => 'required|integer|min:0',
            'max_freeze_days' => 'required|integer|min:0',
            'features' => 'nullable|string',
            'badge_color' => 'required|string|max:30',
            'is_active' => 'boolean',
        ]);

        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['features']))));
        }

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration_type' => $validated['duration_type'],
            'duration_count' => $validated['duration_count'],
            'price' => $validated['price'],
            'signup_fee' => $validated['signup_fee'] ?? 0.00,
            'includes_classes' => $request->boolean('includes_classes'),
            'includes_pt_sessions' => $validated['includes_pt_sessions'],
            'max_freeze_days' => $validated['max_freeze_days'],
            'features' => $featuresArray,
            'badge_color' => $validated['badge_color'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('plans.index')->with('success', "Plan '{$plan->name}' updated successfully!");
    }

    public function toggle(MembershipPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'activated' : 'deactivated';
        return back()->with('info', "Plan '{$plan->name}' has been {$status}.");
    }
}
