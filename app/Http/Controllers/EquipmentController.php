<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category', 'all');
        $query = Equipment::query();

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equipments = $query->orderBy('name')->get();

        $totalCount = Equipment::count();
        $operationalCount = Equipment::where('status', 'operational')->count();
        $needsServiceCount = Equipment::whereIn('status', ['needs_service', 'under_maintenance', 'out_of_order'])->count();
        $categories = ['cardio', 'strength', 'free_weights', 'functional', 'recovery'];

        return view('equipment.index', compact('equipments', 'category', 'categories', 'totalCount', 'operationalCount', 'needsServiceCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|in:cardio,strength,free_weights,functional,recovery',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'required|string|max:100',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'next_service_due' => 'nullable|date',
            'status' => 'required|in:operational,needs_service,under_maintenance,out_of_order',
            'notes' => 'nullable|string',
        ]);

        Equipment::create($validated);

        return back()->with('success', "Equipment '{$validated['name']}' added to inventory!");
    }

    public function updateStatus(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:operational,needs_service,under_maintenance,out_of_order',
            'notes' => 'nullable|string',
        ]);

        $equipment->update($validated);

        return back()->with('success', "Status for {$equipment->name} updated to {$validated['status']}.");
    }

    public function logService(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'next_service_months' => 'required|integer|min:1|max:24',
            'notes' => 'nullable|string',
        ]);

        $nextDue = Carbon::parse($validated['service_date'])->addMonths((int) $validated['next_service_months']);

        $equipment->update([
            'last_service_date' => $validated['service_date'],
            'next_service_due' => $nextDue->toDateString(),
            'status' => 'operational',
            'notes' => ($equipment->notes ?? '') . " [Service completed on {$validated['service_date']}: " . ($validated['notes'] ?? 'Routine inspection') . "]",
        ]);

        return back()->with('success', "Maintenance service recorded! Equipment marked Operational. Next service due on {$nextDue->format('M d, Y')}.");
    }
}
