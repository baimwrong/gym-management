<?php

namespace App\Http\Controllers;

use App\Models\ClassBooking;
use App\Models\FitnessClass;
use App\Models\Member;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FitnessClassController extends Controller
{
    public function index(Request $request)
    {
        $selectedDay = $request->input('day', Carbon::now()->format('l'));
        $classes = FitnessClass::with(['trainer', 'bookings'])
            ->where('is_active', true)
            ->when($selectedDay !== 'all', function ($q) use ($selectedDay) {
                $q->where('day_of_week', $selectedDay);
            })
            ->orderBy('start_time')
            ->get();

        $trainers = Trainer::where('is_available', true)->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('classes.index', compact('classes', 'selectedDay', 'trainers', 'daysOfWeek'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string',
            'trainer_id' => 'nullable|exists:trainers,id',
            'room' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1|max:100',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced,all_levels',
            'color_tag' => 'required|string|max:20',
        ]);

        FitnessClass::create($validated);

        return back()->with('success', "Class '{$validated['title']}' created successfully!");
    }

    public function show(Request $request, FitnessClass $fitnessClass)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        
        $fitnessClass->load('trainer');
        $bookings = ClassBooking::with('member')
            ->where('class_id', $fitnessClass->id)
            ->whereDate('booking_date', $date)
            ->get();

        $activeMembers = Member::where('status', 'active')->orderBy('first_name')->get();

        $bookedCount = $bookings->whereIn('status', ['booked', 'attended'])->count();
        $spotsLeft = max(0, $fitnessClass->capacity - $bookedCount);

        return view('classes.show', compact('fitnessClass', 'bookings', 'date', 'activeMembers', 'spotsLeft'));
    }

    public function book(Request $request, FitnessClass $fitnessClass)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'booking_date' => 'required|date',
        ]);

        $member = Member::with('currentSubscription.plan')->findOrFail($validated['member_id']);

        // Verify member status
        if ($member->status !== 'active') {
            return back()->with('error', "Member {$member->full_name} is {$member->status} and cannot register for classes.");
        }

        // Verify subscription & plan eligibility
        $currentSub = $member->currentSubscription;
        if (!$currentSub) {
            return back()->with('error', "Member {$member->full_name} does not have an active membership subscription.");
        }

        if (!$currentSub->plan || !$currentSub->plan->includes_classes) {
            $planName = $currentSub->plan?->name ?? 'None';
            return back()->with('error', "Member's current plan ('{$planName}') does not include fitness classes.");
        }

        // Verify day of week match
        $bookingDay = Carbon::parse($validated['booking_date'])->format('l');
        if ($fitnessClass->day_of_week && $fitnessClass->day_of_week !== $bookingDay) {
            return back()->with('error', "This class runs on {$fitnessClass->day_of_week}s, but the selected booking date is a {$bookingDay}.");
        }

        return DB::transaction(function () use ($fitnessClass, $validated, $member) {
            // Lock class row to serialize concurrent capacity checks
            $lockedClass = FitnessClass::where('id', $fitnessClass->id)->lockForUpdate()->first();

            $bookedCount = ClassBooking::where('class_id', $lockedClass->id)
                ->whereDate('booking_date', $validated['booking_date'])
                ->whereIn('status', ['booked', 'attended'])
                ->lockForUpdate()
                ->count();

            if ($bookedCount >= $lockedClass->capacity) {
                return back()->with('error', "Class is full! Maximum capacity of {$lockedClass->capacity} reached.");
            }

            // Check duplicate
            $existing = ClassBooking::where('class_id', $lockedClass->id)
                ->where('member_id', $member->id)
                ->whereDate('booking_date', $validated['booking_date'])
                ->first();

            if ($existing) {
                return back()->with('warning', "Member {$member->full_name} is already registered for this session.");
            }

            ClassBooking::create([
                'class_id' => $lockedClass->id,
                'member_id' => $member->id,
                'booking_date' => $validated['booking_date'],
                'status' => 'booked',
            ]);

            return back()->with('success', "Member {$member->full_name} booked into class successfully!");
        });
    }

    public function markAttendance(Request $request, ClassBooking $booking)
    {
        $status = $request->input('status', 'attended');
        $booking->update([
            'status' => $status,
            'attended_at' => $status === 'attended' ? now() : null,
        ]);

        return back()->with('success', "Booking status updated to '{$status}'.");
    }

    public function cancelBooking(ClassBooking $booking)
    {
        $booking->delete();
        return back()->with('info', 'Booking cancelled successfully.');
    }
}
