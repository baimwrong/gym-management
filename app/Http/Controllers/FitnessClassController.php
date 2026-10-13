<?php

namespace App\Http\Controllers;

use App\Models\ClassBooking;
use App\Models\FitnessClass;
use App\Models\Member;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            'start_time' => 'required',
            'end_time' => 'required',
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
            ->where('booking_date', $date)
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

        // Check capacity
        $bookedCount = ClassBooking::where('class_id', $fitnessClass->id)
            ->where('booking_date', $validated['booking_date'])
            ->whereIn('status', ['booked', 'attended'])
            ->count();

        if ($bookedCount >= $fitnessClass->capacity) {
            return back()->with('error', "Class is full! Maximum capacity of {$fitnessClass->capacity} reached.");
        }

        // Check duplicate
        $existing = ClassBooking::where('class_id', $fitnessClass->id)
            ->where('member_id', $validated['member_id'])
            ->where('booking_date', $validated['booking_date'])
            ->first();

        if ($existing) {
            return back()->with('warning', 'This member is already registered for this session.');
        }

        ClassBooking::create([
            'class_id' => $fitnessClass->id,
            'member_id' => $validated['member_id'],
            'booking_date' => $validated['booking_date'],
            'status' => 'booked',
        ]);

        return back()->with('success', 'Member booked into class successfully!');
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
