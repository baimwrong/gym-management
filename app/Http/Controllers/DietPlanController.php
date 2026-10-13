<?php

namespace App\Http\Controllers;

use App\Models\DietPlan;
use App\Models\Member;
use App\Models\Trainer;
use Illuminate\Http\Request;

class DietPlanController extends Controller
{
    public function index()
    {
        $dietPlans = DietPlan::with(['trainer', 'member'])
            ->orderBy('is_template', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('diets.index', compact('dietPlans'));
    }

    public function create()
    {
        $trainers = Trainer::all();
        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        return view('diets.create', compact('trainers', 'members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'trainer_id' => 'nullable|exists:trainers,id',
            'member_id' => 'nullable|exists:members,id',
            'target_calories' => 'required|integer|min:1000|max:8000',
            'protein_grams' => 'required|integer|min:30|max:500',
            'carbs_grams' => 'required|integer|min:30|max:800',
            'fats_grams' => 'required|integer|min:20|max:300',
            'is_template' => 'boolean',
            'meals_json' => 'nullable|string',
        ]);

        $meals = null;
        if (!empty($validated['meals_json'])) {
            $meals = json_decode($validated['meals_json'], true);
        }

        if (empty($meals)) {
            $meals = [
                [
                    'meal_name' => 'Meal 1: High Protein Breakfast',
                    'time' => '07:30 AM',
                    'items' => [
                        ['name' => 'Whole eggs + egg whites', 'portion' => '3 whole eggs + 3 whites', 'calories' => 280, 'protein' => 28],
                        ['name' => 'Rolled Oats with Almond Milk', 'portion' => '75g dry oats', 'calories' => 300, 'protein' => 10],
                        ['name' => 'Fresh Berries', 'portion' => '1 cup', 'calories' => 70, 'protein' => 1],
                    ]
                ],
                [
                    'meal_name' => 'Meal 2: Balanced Lunch',
                    'time' => '12:30 PM',
                    'items' => [
                        ['name' => 'Grilled Chicken Breast', 'portion' => '200g cooked', 'calories' => 330, 'protein' => 62],
                        ['name' => 'Jasmine Rice or Quinoa', 'portion' => '180g cooked', 'calories' => 230, 'protein' => 5],
                        ['name' => 'Steamed Broccoli & Olive Oil', 'portion' => '150g with 10g EVOO', 'calories' => 140, 'protein' => 4],
                    ]
                ],
                [
                    'meal_name' => 'Meal 3: Pre-Workout Boost',
                    'time' => '04:00 PM',
                    'items' => [
                        ['name' => 'Rice Cakes + Natural Peanut Butter', 'portion' => '3 cakes + 2 tbsp PB', 'calories' => 280, 'protein' => 8],
                        ['name' => 'Whey Protein Shake', 'portion' => '1 scoop in water', 'calories' => 120, 'protein' => 25],
                    ]
                ],
                [
                    'meal_name' => 'Meal 4: Recovery Dinner',
                    'time' => '07:30 PM',
                    'items' => [
                        ['name' => 'Atlantic Salmon or Lean Beef', 'portion' => '200g fillet', 'calories' => 400, 'protein' => 44],
                        ['name' => 'Sweet Potato', 'portion' => '250g baked', 'calories' => 220, 'protein' => 4],
                        ['name' => 'Mixed Green Salad with Avocado', 'portion' => '1 bowl + 1/2 avocado', 'calories' => 180, 'protein' => 3],
                    ]
                ]
            ];
        }

        $diet = DietPlan::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'trainer_id' => $validated['trainer_id'] ?? null,
            'member_id' => $validated['member_id'] ?? null,
            'target_calories' => $validated['target_calories'],
            'protein_grams' => $validated['protein_grams'],
            'carbs_grams' => $validated['carbs_grams'],
            'fats_grams' => $validated['fats_grams'],
            'meals' => $meals,
            'is_template' => $request->boolean('is_template', empty($validated['member_id'])),
        ]);

        return redirect()->route('diets.show', $diet)
            ->with('success', "Diet plan '{$diet->title}' saved successfully!");
    }

    public function show(DietPlan $diet)
    {
        $diet->load(['trainer', 'member']);
        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        return view('diets.show', compact('diet', 'members'));
    }

    public function assign(Request $request, DietPlan $diet)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $member = Member::findOrFail($request->member_id);

        $newPlan = $diet->replicate();
        $newPlan->member_id = $member->id;
        $newPlan->is_template = false;
        $newPlan->title = "{$diet->title} - {$member->full_name}";
        $newPlan->save();

        return redirect()->route('members.show', $member)
            ->with('success', "Diet plan assigned to {$member->full_name}!");
    }
}
