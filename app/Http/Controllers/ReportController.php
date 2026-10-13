<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MemberSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Invoices within date range
        $invoices = Invoice::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->get();

        $totalRevenue = $invoices->where('payment_status', 'paid')->sum('total_amount');
        $taxCollected = $invoices->where('payment_status', 'paid')->sum('tax_amount');
        $membershipRevenue = $invoices->where('payment_status', 'paid')->where('invoice_type', 'membership')->sum('total_amount');
        $posRevenue = $invoices->where('payment_status', 'paid')->where('invoice_type', 'pos_retail')->sum('total_amount');

        // Attendance stats
        $totalCheckIns = Attendance::whereBetween('check_in_time', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->count();

        // New member registrations
        $newMembersCount = Member::whereBetween('joined_date', [$startDate, $endDate])->count();

        // Breakdown by payment method
        $paymentMethods = $invoices->where('payment_status', 'paid')
            ->groupBy('payment_method')
            ->map(fn($group) => $group->sum('total_amount'));

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalRevenue',
            'taxCollected',
            'membershipRevenue',
            'posRevenue',
            'totalCheckIns',
            'newMembersCount',
            'paymentMethods'
        ));
    }

    public function exportMembers(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="gym_members_export_'.date('Y-m-d').'.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Member Code', 'First Name', 'Last Name', 'Email', 'Phone', 'Gender', 'Status', 'Joined Date', 'Active Plan', 'Subscription End']);

            Member::with('activeSubscription.plan')->chunk(100, function ($members) use ($handle) {
                foreach ($members as $m) {
                    fputcsv($handle, [
                        $m->member_code,
                        $m->first_name,
                        $m->last_name,
                        $m->email,
                        $m->phone,
                        $m->gender,
                        $m->status,
                        $m->joined_date?->format('Y-m-d'),
                        $m->activeSubscription?->plan?->name ?? 'None',
                        $m->activeSubscription?->end_date?->format('Y-m-d') ?? 'N/A',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function exportInvoices(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="gym_invoices_export_'.date('Y-m-d').'.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice #', 'Date', 'Customer', 'Type', 'Subtotal', 'Tax', 'Discount', 'Total', 'Status', 'Payment Method']);

            Invoice::chunk(100, function ($invoices) use ($handle) {
                foreach ($invoices as $inv) {
                    fputcsv($handle, [
                        $inv->invoice_number,
                        $inv->created_at->format('Y-m-d H:i'),
                        $inv->customer_name,
                        $inv->invoice_type,
                        $inv->subtotal,
                        $inv->tax_amount,
                        $inv->discount_amount,
                        $inv->total_amount,
                        $inv->payment_status,
                        $inv->payment_method,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
