<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Product;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    public function index()
    {
        $plans = MembershipPlan::where('is_active', true)->get();
        return view('import.index', compact('plans'));
    }

    /**
     * Download sample CSV templates for Members, Products, or Equipment.
     */
    public function downloadSample(string $type): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"sample_{$type}_import.csv\"",
        ];

        return response()->stream(function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'members') {
                fputcsv($handle, [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'gender',
                    'date_of_birth',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                    'blood_type',
                    'address',
                    'city',
                    'postal_code',
                    'plan_slug',
                    'status',
                    'joined_date'
                ]);
                fputcsv($handle, [
                    'Michael',
                    'Stone',
                    'michael.stone@example.com',
                    '+1 (555) 301-4491',
                    'male',
                    '1992-06-15',
                    'Emma Stone',
                    '+1 (555) 301-4499',
                    'O+',
                    '450 West End Avenue',
                    'Metro City',
                    '10001',
                    'silver-monthly',
                    'active',
                    date('Y-m-d')
                ]);
                fputcsv($handle, [
                    'Jessica',
                    'Taylor',
                    'jessica.t@example.com',
                    '+1 (555) 402-8812',
                    'female',
                    '1996-09-22',
                    'Robert Taylor',
                    '+1 (555) 402-8800',
                    'A+',
                    '12 Ocean Boulevard',
                    'Metro City',
                    '10002',
                    'gold-quarterly',
                    'active',
                    date('Y-m-d')
                ]);
            } elseif ($type === 'products') {
                fputcsv($handle, [
                    'sku',
                    'name',
                    'category',
                    'cost_price',
                    'selling_price',
                    'stock_quantity',
                    'low_stock_threshold',
                    'barcode',
                    'description'
                ]);
                fputcsv($handle, [
                    'SUPP-BCAA-01',
                    'Optimum Nutrition BCAA Boost 400g (Watermelon)',
                    'supplement',
                    '14.50',
                    '28.99',
                    '30',
                    '8',
                    '782910394999',
                    'Instantized BCAA powder in 2:1:1 ratio'
                ]);
                fputcsv($handle, [
                    'GEAR-KNEE-02',
                    '7mm Neoprene Competition Knee Sleeves (Pair, L)',
                    'accessory',
                    '22.00',
                    '49.99',
                    '15',
                    '4',
                    '782910395001',
                    'Heavy duty reinforced stitching for heavy squats'
                ]);
            } elseif ($type === 'equipment') {
                fputcsv($handle, [
                    'name',
                    'category',
                    'serial_number',
                    'location',
                    'purchase_date',
                    'warranty_expiry',
                    'status',
                    'notes'
                ]);
                fputcsv($handle, [
                    'Life Fitness Hammer Strength Incline Chest Press',
                    'strength',
                    'LF-INC-4401',
                    'Free Weights Sector',
                    date('Y-m-d', strtotime('-6 months')),
                    date('Y-m-d', strtotime('+18 months')),
                    'operational',
                    'Plate loaded converging movement'
                ]);
                fputcsv($handle, [
                    'Assault AirBike Classic Fan Resistance',
                    'cardio',
                    'AB-FAN-2024-11',
                    'Cardio Deck - Level 1',
                    date('Y-m-d', strtotime('-3 months')),
                    date('Y-m-d', strtotime('+21 months')),
                    'operational',
                    'Chain drive mechanism inspected'
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Process Bulk Members Import.
     */
    public function importMembers(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'default_plan_id' => 'nullable|exists:membership_plans,id',
            'payment_method' => 'required|string',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        if (!$handle) {
            return back()->with('error', 'Unable to open uploaded CSV file.');
        }

        // Read header row
        $rawHeaders = fgetcsv($handle, 4000, ',');
        if (!$rawHeaders) {
            fclose($handle);
            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        // Clean UTF-8 BOM and whitespace from headers
        $headers = array_map(function ($h) {
            $clean = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($h));
            return strtolower(trim($clean));
        }, $rawHeaders);

        $requiredFields = ['first_name', 'last_name'];
        foreach ($requiredFields as $req) {
            if (!in_array($req, $headers, true)) {
                fclose($handle);
                return back()->with('error', "Missing required column '{$req}' in CSV header.");
            }
        }

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $rowNum = 1;

        $defaultPlan = $request->filled('default_plan_id') 
            ? MembershipPlan::find($request->default_plan_id) 
            : MembershipPlan::where('is_active', true)->first();

        $allPlans = MembershipPlan::all()->keyBy('slug');

        while (($row = fgetcsv($handle, 4000, ',')) !== false) {
            $rowNum++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine(array_slice($headers, 0, count($row)), $row);

            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $email = trim($data['email'] ?? '');
            $phone = trim($data['phone'] ?? '');

            if (empty($firstName) || empty($lastName)) {
                $skippedCount++;
                $errors[] = "Row {$rowNum}: First name and Last name are required.";
                continue;
            }

            // Check if member already exists by email
            if (!empty($email) && Member::where('email', $email)->exists()) {
                $skippedCount++;
                $errors[] = "Row {$rowNum}: Member with email '{$email}' already exists. Skipped.";
                continue;
            }

            try {
                DB::transaction(function () use ($data, $firstName, $lastName, $email, $phone, $allPlans, $defaultPlan, $request, &$importedCount) {
                    $memberCode = Member::generateMemberCode();

                    $gender = in_array(strtolower(trim($data['gender'] ?? '')), ['male', 'female', 'other'], true) 
                        ? strtolower(trim($data['gender'])) 
                        : 'male';

                    $dob = !empty($data['date_of_birth']) && strtotime($data['date_of_birth']) 
                        ? date('Y-m-d', strtotime($data['date_of_birth'])) 
                        : null;

                    $status = in_array(strtolower(trim($data['status'] ?? '')), ['active', 'expired', 'frozen', 'suspended'], true)
                        ? strtolower(trim($data['status']))
                        : 'active';

                    $joinedDate = !empty($data['joined_date']) && strtotime($data['joined_date'])
                        ? date('Y-m-d', strtotime($data['joined_date']))
                        : now()->toDateString();

                    $member = Member::create([
                        'member_code' => $memberCode,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'gender' => $gender,
                        'date_of_birth' => $dob,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                        'emergency_contact_name' => trim($data['emergency_contact_name'] ?? '') ?: null,
                        'emergency_contact_phone' => trim($data['emergency_contact_phone'] ?? '') ?: null,
                        'blood_type' => trim($data['blood_type'] ?? '') ?: null,
                        'address' => trim($data['address'] ?? '') ?: null,
                        'city' => trim($data['city'] ?? '') ?: null,
                        'postal_code' => trim($data['postal_code'] ?? '') ?: null,
                        'barcode_value' => $memberCode,
                        'rfid_card_number' => 'RFID-' . substr(md5($memberCode), 0, 8),
                        'status' => $status,
                        'joined_date' => $joinedDate,
                    ]);

                    // Plan determination
                    $planSlug = trim($data['plan_slug'] ?? '');
                    $plan = ($planSlug && isset($allPlans[$planSlug])) ? $allPlans[$planSlug] : $defaultPlan;

                    if ($plan && $status === 'active') {
                        $this->billingService->createSubscription($member, $plan, [
                            'payment_method' => $request->payment_method ?? 'cash',
                            'start_date' => $joinedDate,
                        ]);
                    }

                    $importedCount++;
                });
            } catch (\Throwable $e) {
                $skippedCount++;
                $errors[] = "Row {$rowNum} ({$firstName} {$lastName}): " . $e->getMessage();
            }
        }

        fclose($handle);

        $msg = "Import finished: Successfully imported {$importedCount} members.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} rows skipped).";
        }

        return redirect()->route('members.index')
            ->with($importedCount > 0 ? 'success' : 'warning', $msg)
            ->with('import_errors', $errors);
    }

    /**
     * Process Bulk Products (POS Inventory) Import.
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to open uploaded CSV file.');
        }

        $rawHeaders = fgetcsv($handle, 4000, ',');
        if (!$rawHeaders) {
            fclose($handle);
            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        $headers = array_map(function ($h) {
            $clean = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($h));
            return strtolower(trim($clean));
        }, $rawHeaders);

        if (!in_array('sku', $headers, true) || !in_array('name', $headers, true)) {
            fclose($handle);
            return back()->with('error', "CSV must contain at least 'sku' and 'name' columns.");
        }

        $importedCount = 0;
        $updatedCount = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 4000, ',')) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) continue;
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine(array_slice($headers, 0, count($row)), $row);

            $sku = trim($data['sku'] ?? '');
            $name = trim($data['name'] ?? '');

            if (empty($sku) || empty($name)) {
                $errors[] = "Row {$rowNum}: SKU and Product name are required.";
                continue;
            }

            $category = strtolower(trim($data['category'] ?? 'supplement'));
            if (!in_array($category, ['supplement', 'beverage', 'apparel', 'accessory', 'snack'], true)) {
                $category = 'supplement';
            }

            $costPrice = (float) ($data['cost_price'] ?? 0.00);
            $sellingPrice = (float) ($data['selling_price'] ?? 0.00);
            $stockQuantity = (int) ($data['stock_quantity'] ?? 0);
            $threshold = (int) ($data['low_stock_threshold'] ?? 5);

            $existing = Product::where('sku', $sku)->first();
            if ($existing) {
                $existing->update([
                    'name' => $name,
                    'category' => $category,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'stock_quantity' => $stockQuantity,
                    'low_stock_threshold' => $threshold,
                    'barcode' => trim($data['barcode'] ?? $existing->barcode),
                    'description' => trim($data['description'] ?? $existing->description),
                    'is_active' => true,
                ]);
                $updatedCount++;
            } else {
                Product::create([
                    'sku' => $sku,
                    'name' => $name,
                    'category' => $category,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'stock_quantity' => $stockQuantity,
                    'low_stock_threshold' => $threshold,
                    'barcode' => trim($data['barcode'] ?? '') ?: null,
                    'description' => trim($data['description'] ?? '') ?: null,
                    'is_active' => true,
                ]);
                $importedCount++;
            }
        }

        fclose($handle);

        return redirect()->route('pos.products')
            ->with('success', "Products import completed: {$importedCount} new products created, {$updatedCount} existing products updated.");
    }

    /**
     * Process Bulk Equipment Import.
     */
    public function importEquipment(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to open uploaded CSV file.');
        }

        $rawHeaders = fgetcsv($handle, 4000, ',');
        if (!$rawHeaders) {
            fclose($handle);
            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        $headers = array_map(function ($h) {
            $clean = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($h));
            return strtolower(trim($clean));
        }, $rawHeaders);

        if (!in_array('name', $headers, true)) {
            fclose($handle);
            return back()->with('error', "CSV must contain a 'name' column.");
        }

        $importedCount = 0;
        $rowNum = 1;

        while (($row = fgetcsv($handle, 4000, ',')) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) continue;
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine(array_slice($headers, 0, count($row)), $row);

            $name = trim($data['name'] ?? '');
            if (empty($name)) continue;

            $category = strtolower(trim($data['category'] ?? 'strength'));
            if (!in_array($category, ['cardio', 'strength', 'free_weights', 'functional', 'recovery'], true)) {
                $category = 'strength';
            }

            Equipment::create([
                'name' => $name,
                'category' => $category,
                'serial_number' => trim($data['serial_number'] ?? '') ?: null,
                'location' => trim($data['location'] ?? 'Main Fitness Floor'),
                'purchase_date' => !empty($data['purchase_date']) && strtotime($data['purchase_date']) ? date('Y-m-d', strtotime($data['purchase_date'])) : null,
                'warranty_expiry' => !empty($data['warranty_expiry']) && strtotime($data['warranty_expiry']) ? date('Y-m-d', strtotime($data['warranty_expiry'])) : null,
                'status' => in_array(strtolower(trim($data['status'] ?? '')), ['operational', 'needs_service', 'under_maintenance', 'out_of_order'], true) ? strtolower(trim($data['status'])) : 'operational',
                'notes' => trim($data['notes'] ?? '') ?: null,
            ]);

            $importedCount++;
        }

        fclose($handle);

        return redirect()->route('equipment.index')
            ->with('success', "Equipment import completed: {$importedCount} machines/assets added to registry.");
    }
}
