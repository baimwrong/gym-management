<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\MemberSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Create a new subscription and issue corresponding invoice.
     */
    public function createSubscription(Member $member, MembershipPlan $plan, array $data = [], ?User $issuer = null): MemberSubscription
    {
        return DB::transaction(function () use ($member, $plan, $data, $issuer) {
            $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::today();
            
            // Calculate end date based on duration
            $endDate = match ($plan->duration_type) {
                'days' => (clone $startDate)->addDays($plan->duration_count),
                'years' => (clone $startDate)->addYears($plan->duration_count),
                default => (clone $startDate)->addMonths($plan->duration_count),
            };

            $totalPlanPrice = $plan->price + $plan->signup_fee;

            // Create subscription record
            $subscription = MemberSubscription::create([
                'member_id' => $member->id,
                'plan_id' => $plan->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'status' => 'active',
                'price_paid' => $totalPlanPrice,
                'remaining_pt_sessions' => $plan->includes_pt_sessions,
                'is_auto_renew' => $data['is_auto_renew'] ?? false,
                'notes' => $data['notes'] ?? "Subscribed to {$plan->name}",
            ]);

            // Update member status
            $member->update(['status' => 'active']);

            // Generate Invoice
            $invoiceNumber = self::generateInvoiceNumber();
            $taxRate = (float) config('gym.tax_rate_percentage', 8.5);
            $taxAmount = round(($totalPlanPrice * $taxRate) / 100, 2);
            $totalAmount = $totalPlanPrice + $taxAmount;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'member_id' => $member->id,
                'user_id' => $issuer?->id ?? auth()->id(),
                'customer_name' => $member->full_name,
                'subtotal' => $totalPlanPrice,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0.00,
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'payment_method' => $data['payment_method'] ?? 'credit_card',
                'invoice_type' => 'membership',
                'paid_at' => now(),
                'due_date' => $startDate->toDateString(),
                'notes' => "Membership subscription fee for {$plan->name}",
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'itemable_type' => MembershipPlan::class,
                'itemable_id' => $plan->id,
                'item_description' => "Membership Plan: {$plan->name} ({$plan->formatted_duration})",
                'unit_price' => $plan->price,
                'quantity' => 1,
                'total_price' => $plan->price,
            ]);

            if ($plan->signup_fee > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'itemable_type' => MembershipPlan::class,
                    'itemable_id' => $plan->id,
                    'item_description' => "Registration / One-time Admission Fee",
                    'unit_price' => $plan->signup_fee,
                    'quantity' => 1,
                    'total_price' => $plan->signup_fee,
                ]);
            }

            return $subscription;
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = config('gym.invoice_prefix', 'INV-');
        $year = date('Y');
        $basePattern = sprintf('%s%s-', $prefix, $year);

        $latest = Invoice::where('invoice_number', 'like', $basePattern . '%')
            ->orderByRaw('LENGTH(invoice_number) DESC, invoice_number DESC')
            ->value('invoice_number');

        $nextSeq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        }

        do {
            $candidate = sprintf('%s%05d', $basePattern, $nextSeq);
            $nextSeq++;
        } while (Invoice::where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
