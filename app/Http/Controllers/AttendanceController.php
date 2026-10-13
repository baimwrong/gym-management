<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $query = Attendance::with(['member.activeSubscription.plan', 'checkedInBy'])
            ->whereDate('check_in_time', $date);

        if ($request->filled('status')) {
            if ($request->status === 'inside') {
                $query->whereNull('check_out_time');
            } elseif ($request->status === 'checked_out') {
                $query->whereNotNull('check_out_time');
            }
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('member', function ($q) use ($term) {
                $q->search($term);
            });
        }

        $attendances = $query->orderBy('check_in_time', 'desc')->paginate(20)->withQueryString();

        // Daily stats
        $totalToday = Attendance::whereDate('check_in_time', $date)->count();
        $currentlyInside = Attendance::whereDate('check_in_time', $date)->whereNull('check_out_time')->count();

        return view('attendance.index', compact('attendances', 'date', 'totalToday', 'currentlyInside'));
    }

    public function kiosk()
    {
        $today = Carbon::today()->toDateString();
        $currentlyInsideCount = Attendance::whereDate('check_in_time', $today)->whereNull('check_out_time')->count();
        $recentCheckIns = Attendance::with('member')
            ->whereDate('check_in_time', $today)
            ->latest('check_in_time')
            ->take(10)
            ->get();

        return view('attendance.kiosk', compact('currentlyInsideCount', 'recentCheckIns'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'action' => 'required|in:check_in,check_out,toggle',
        ]);

        $searchCode = trim($request->code);
        $member = Member::where('member_code', $searchCode)
            ->orWhere('barcode_value', $searchCode)
            ->orWhere('rfid_card_number', $searchCode)
            ->orWhere('phone', $searchCode)
            ->orWhere('email', $searchCode)
            ->first();

        if (!$member) {
            return back()->with('error', "No member found matching code '{$searchCode}'!");
        }

        // Action routing
        if ($request->action === 'check_out') {
            $result = $this->attendanceService->processCheckOut($member);
            if ($result['success']) {
                return back()->with('success', $result['message']);
            }
            return back()->with('warning', $result['message']);
        }

        if ($request->action === 'check_in') {
            $result = $this->attendanceService->processCheckIn($member, 'front_desk', auth()->user());
            if ($result['success']) {
                if (!empty($result['warning'])) {
                    return back()->with('warning', $result['message']);
                }
                return back()->with('success', $result['message']);
            }
            return back()->with('error', $result['message']);
        }

        // Toggle action: if inside -> check out; if not inside -> check in
        $activeSession = Attendance::where('member_id', $member->id)
            ->whereNull('check_out_time')
            ->whereDate('check_in_time', now()->toDateString())
            ->first();

        if ($activeSession) {
            $result = $this->attendanceService->processCheckOut($member);
            return back()->with('success', $result['message']);
        } else {
            $result = $this->attendanceService->processCheckIn($member, 'front_desk', auth()->user());
            if ($result['success']) {
                if (!empty($result['warning'])) {
                    return back()->with('warning', $result['message']);
                }
                return back()->with('success', $result['message']);
            }
            return back()->with('error', $result['message']);
        }
    }

    public function checkOutById(Attendance $attendance)
    {
        if ($attendance->check_out_time) {
            return back()->with('info', 'Member has already checked out.');
        }

        $attendance->update(['check_out_time' => now()]);
        return back()->with('success', "Member {$attendance->member->full_name} checked out.");
    }
}
