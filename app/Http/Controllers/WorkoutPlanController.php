<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\WorkoutPlan;
use Illuminate\Http\Request;

class WorkoutPlanController extends Controller
{
    public function index()
    {
        $workoutPlans = WorkoutPlan::with(['trainer', 'member'])
            ->orderBy('is_template', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('workouts.index', compact('workoutPlans'));
    }

    public function create()
    {
        $trainers = Trainer::all();
        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        return view('workouts.create', compact('trainers', 'members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'trainer_id' => 'nullable|exists:trainers,id',
            'member_id' => 'nullable|exists:members,id',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'days_per_week' => 'required|integer|min:1|max:7',
            'is_template' => 'boolean',
            'schedule_json' => 'nullable|string',
        ]);

        $schedule = null;
        if (!empty($validated['schedule_json'])) {
            $schedule = json_decode($validated['schedule_json'], true);
        }

        // If no JSON provided, construct default structured split
        if (empty($schedule)) {
            $schedule = [
                [
                    'day_title' => 'Day 1: Upper Body Push (Chest & Triceps)',
                    'exercises' => [
                        ['name' => 'Barbell Bench Press', 'sets' => '4', 'reps' => '8-10', 'rest' => '90s', 'notes' => 'Pyramid up weight'],
                        ['name' => 'Incline Dumbbell Press', 'sets' => '3', 'reps' => '10-12', 'rest' => '75s', 'notes' => 'Full chest stretch'],
                        ['name' => 'Dips / Cable Flyes', 'sets' => '3', 'reps' => '12-15', 'rest' => '60s', 'notes' => 'Squeeze peak contraction'],
                        ['name' => 'Overhead Tricep Extension', 'sets' => '4', 'reps' => '12', 'rest' => '60s', 'notes' => 'Keep elbows locked'],
                    ]
                ],
                [
                    'day_title' => 'Day 2: Upper Body Pull (Back & Biceps)',
                    'exercises' => [
                        ['name' => 'Barbell Deadlifts', 'sets' => '4', 'reps' => '6', 'rest' => '120s', 'notes' => 'Brace core'],
                        ['name' => 'Lat Pulldowns / Pull-ups', 'sets' => '4', 'reps' => '8-10', 'rest' => '90s', 'notes' => 'Drive elbows down'],
                        ['name' => 'Chest-Supported Rows', 'sets' => '3', 'reps' => '10-12', 'rest' => '75s', 'notes' => 'Retract scapula'],
                        ['name' => 'Incline Dumbbell Bicep Curls', 'sets' => '4', 'reps' => '10-12', 'rest' => '60s', 'notes' => 'Controlled eccentric'],
                    ]
                ],
                [
                    'day_title' => 'Day 3: Lower Body & Core',
                    'exercises' => [
                        ['name' => 'Barbell Back Squats', 'sets' => '4', 'reps' => '8', 'rest' => '120s', 'notes' => 'Below parallel depth'],
                        ['name' => 'Romanian Deadlifts (RDL)', 'sets' => '3', 'reps' => '10', 'rest' => '90s', 'notes' => 'Hinge hips, hamstring stretch'],
                        ['name' => 'Leg Press', 'sets' => '3', 'reps' => '12', 'rest' => '75s', 'notes' => 'High foot placement'],
                        ['name' => 'Hanging Leg Raises', 'sets' => '3', 'reps' => '15', 'rest' => '45s', 'notes' => 'Controlled cadence'],
                    ]
                ]
            ];
        }

        $workout = WorkoutPlan::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'trainer_id' => $validated['trainer_id'] ?? null,
            'member_id' => $validated['member_id'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'days_per_week' => $validated['days_per_week'],
            'routine_schedule' => $schedule,
            'is_template' => $request->boolean('is_template', empty($validated['member_id'])),
        ]);

        return redirect()->route('workouts.show', $workout)
            ->with('success', "Workout plan '{$workout->title}' saved successfully!");
    }

    public function show(WorkoutPlan $workout)
    {
        $workout->load(['trainer', 'member']);
        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        return view('workouts.show', compact('workout', 'members'));
    }

    public function assign(Request $request, WorkoutPlan $workout)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $member = Member::findOrFail($request->member_id);

        // Duplicate template for specific member
        $newPlan = $workout->replicate();
        $newPlan->member_id = $member->id;
        $newPlan->is_template = false;
        $newPlan->title = "{$workout->title} - {$member->full_name}";
        $newPlan->save();

        return redirect()->route('members.show', $member)
            ->with('success', "Workout plan assigned to {$member->full_name}!");
    }
}
