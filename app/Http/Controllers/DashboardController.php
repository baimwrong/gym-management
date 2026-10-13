<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FitnessClass;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\MemberSubscription;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // High-level KPI metrics
        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();
        
        $todayCheckIns = Attendance::whereDate('check_in_time', $today)->count();
        $currentlyInside = Attendance::whereDate('check_in_time', $today)
            ->whereNull('check_out_time')
            ->count();

        $monthlyRevenue = Invoice::where('payment_status', 'paid')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');

        $expiringSubscriptions = MemberSubscription::where('status', 'active')
            ->whereBetween('end_date', [$today->toDateString(), (clone $today)->addDays(7)->toDateString()])
            ->count();

        $lowStockCount = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();

        // Monthly Revenue Trend (Last 6 Months)
        $revenueLabels = [];
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = (clone $month)->startOfMonth();
            $monthEnd = (clone $month)->endOfMonth();
            
            $revenueLabels[] = $month->format('M Y');
            $revenueData[] = (float) Invoice::where('payment_status', 'paid')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_amount');
        }

        // Hourly Attendance Distribution for Today
        $hourlyDistribution = array_fill(6, 16, 0); // 6 AM to 9 PM
        $todayAttendances = Attendance::whereDate('check_in_time', $today)->get();
        foreach ($todayAttendances as $att) {
            $hour = (int) $att->check_in_time->format('G');
            if (isset($hourlyDistribution[$hour])) {
                $hourlyDistribution[$hour]++;
            }
        }
        $hourlyLabels = [];
        $hourlyCounts = [];
        foreach ($hourlyDistribution as $h => $cnt) {
            $hourlyLabels[] = date('ga', mktime($h, 0, 0, 1, 1));
            $hourlyCounts[] = $cnt;
        }

        // Membership Plans breakdown (Doughnut)
        $plansBreakdown = MembershipPlan::withCount(['subscriptions' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $planLabels = $plansBreakdown->pluck('name')->toArray();
        $planCounts = $plansBreakdown->pluck('subscriptions_count')->toArray();

        // Recent check-ins
        $recentCheckIns = Attendance::with('member')
            ->latest('check_in_time')
            ->take(8)
            ->get();

        // Today's Fitness Classes
        $dayOfWeek = Carbon::now()->format('l');
        $classesToday = FitnessClass::with('trainer')
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        return view('dashboard.index', compact(
            'totalMembers',
            'activeMembers',
            'todayCheckIns',
            'currentlyInside',
            'monthlyRevenue',
            'expiringSubscriptions',
            'lowStockCount',
            'revenueLabels',
            'revenueData',
            'hourlyLabels',
            'hourlyCounts',
            'planLabels',
            'planCounts',
            'recentCheckIns',
            'classesToday'
        ));
    }
}
