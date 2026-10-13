<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Member;
use App\Services\BillingService;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['member', 'user']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('invoice_type', $request->type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'ilike', "%{$term}%")
                  ->orWhere('customer_name', 'ilike', "%{$term}%")
                  ->orWhereHas('member', function ($m) use ($term) {
                      $m->search($term);
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $totalRevenue = Invoice::where('payment_status', 'paid')->sum('total_amount');
        $unpaidTotal = Invoice::where('payment_status', 'unpaid')->sum('total_amount');

        return view('invoices.index', compact('invoices', 'totalRevenue', 'unpaidTotal'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['member', 'user', 'items']);

        // Generate offline SVG barcode
        $barcodeSvg = QrCodeService::generateBarcodeSvg($invoice->invoice_number, 240, 50);

        return view('invoices.show', compact('invoice', 'barcodeSvg'));
    }

    public function create()
    {
        $members = Member::where('status', 'active')->orderBy('first_name')->get();
        return view('invoices.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'customer_name' => 'nullable|string|max:120',
            'payment_method' => 'required|string',
            'payment_status' => 'required|in:paid,unpaid,partial',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = 0.00;
            foreach ($validated['items'] as $item) {
                $subtotal += round($item['quantity'] * $item['unit_price'], 2);
            }

            $taxRate = (float) config('gym.tax_rate_percentage', 8.5);
            $taxAmount = round(($subtotal * $taxRate) / 100, 2);
            $totalAmount = $subtotal + $taxAmount;

            $member = !empty($validated['member_id']) ? Member::find($validated['member_id']) : null;
            $customerName = $member ? $member->full_name : ($validated['customer_name'] ?? 'Guest Customer');

            $invoice = Invoice::create([
                'invoice_number' => BillingService::generateInvoiceNumber(),
                'member_id' => $member?->id,
                'user_id' => auth()->id(),
                'customer_name' => $customerName,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0.00,
                'total_amount' => $totalAmount,
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'invoice_type' => 'general',
                'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
                'due_date' => $validated['due_date'] ?? Carbon::today()->addDays(7)->toDateString(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_description' => $item['description'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => round($item['quantity'] * $item['unit_price'], 2),
                ]);
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully!");
        });
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        $invoice->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'paid_at' => now(),
        ]);

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as PAID.");
    }
}
