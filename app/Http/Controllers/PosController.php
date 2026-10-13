<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Member;
use App\Models\Product;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category', 'all');
        $query = Product::where('is_active', true);

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                  ->orWhere('sku', 'ilike', "%{$term}%")
                  ->orWhere('barcode', 'ilike', "%{$term}%");
            });
        }

        $products = $query->orderBy('name')->get();
        $members = Member::where('status', 'active')->orderBy('first_name')->get();
        $categories = ['supplement', 'beverage', 'apparel', 'accessory', 'snack'];

        return view('pos.index', compact('products', 'members', 'categories', 'category'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'member_id' => 'nullable|exists:members,id',
            'customer_name' => 'nullable|string|max:120',
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,qris_pos',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = 0.00;
            $itemsToProcess = [];

            // Validate inventory and prepare items
            foreach ($validated['items'] as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                $qty = (int) $itemData['quantity'];

                if ($product->stock_quantity < $qty) {
                    throw new \Exception("Insufficient stock for product '{$product->name}'. Available: {$product->stock_quantity}");
                }

                $lineTotal = round($product->selling_price * $qty, 2);
                $subtotal += $lineTotal;

                // Decrement stock
                $product->decrement('stock_quantity', $qty);

                $itemsToProcess[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'total_price' => $lineTotal,
                ];
            }

            $discount = (float) ($validated['discount_amount'] ?? 0.00);
            $taxRate = (float) config('gym.tax_rate_percentage', 8.5);
            $taxableAmount = max(0, $subtotal - $discount);
            $taxAmount = round(($taxableAmount * $taxRate) / 100, 2);
            $totalAmount = $taxableAmount + $taxAmount;

            $member = !empty($validated['member_id']) ? Member::find($validated['member_id']) : null;
            $customerName = $member ? $member->full_name : ($validated['customer_name'] ?? 'Walk-In Customer');

            $invoiceNumber = BillingService::generateInvoiceNumber();

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'member_id' => $member?->id,
                'user_id' => auth()->id(),
                'customer_name' => $customerName,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'invoice_type' => 'pos_retail',
                'paid_at' => now(),
                'notes' => $validated['notes'] ?? 'Pro-Shop Point of Sale Transaction',
            ]);

            foreach ($itemsToProcess as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'itemable_type' => Product::class,
                    'itemable_id' => $item['product']->id,
                    'item_description' => $item['product']->name,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                ]);
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Sale completed! Invoice {$invoice->invoice_number} created.");
        });
    }

    public function products(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                  ->orWhere('sku', 'ilike', "%{$term}%");
            });
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = ['supplement', 'beverage', 'apparel', 'accessory', 'snack'];

        return view('pos.products', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => 'required|string|max:60|unique:products,sku',
            'barcode' => 'nullable|string|max:100',
            'category' => 'required|in:supplement,beverage,apparel,accessory,snack',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Product::create($validated);

        return back()->with('success', "Product '{$validated['name']}' added to inventory!");
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => "required|string|max:60|unique:products,sku,{$product->id}",
            'barcode' => 'nullable|string|max:100',
            'category' => 'required|in:supplement,beverage,apparel,accessory,snack',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $product->update($validated);

        return back()->with('success', "Product '{$product->name}' updated!");
    }
}
