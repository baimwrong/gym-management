<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'SUPP-WHEY-01',
                'barcode' => '782910394812',
                'name' => 'Gold Standard 100% Whey Protein (Double Rich Chocolate 2kg)',
                'category' => 'supplement',
                'description' => '24g protein per scoop, 5.5g BCAAs, fast-absorbing whey isolate and concentrate blend.',
                'cost_price' => 42.00,
                'selling_price' => 69.99,
                'stock_quantity' => 28,
                'low_stock_threshold' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'SUPP-CREATINE-02',
                'barcode' => '782910394813',
                'name' => 'Creapure Micronized Creatine Monohydrate (500g)',
                'category' => 'supplement',
                'description' => '100% German Creapure pharmaceutical grade creatine for ATP regeneration and cellular hydration.',
                'cost_price' => 18.00,
                'selling_price' => 32.50,
                'stock_quantity' => 45,
                'low_stock_threshold' => 10,
                'is_active' => true,
            ],
            [
                'sku' => 'SUPP-PRE-03',
                'barcode' => '782910394814',
                'name' => 'Ghost Legend High Stim Pre-Workout (Blue Raspberry 30 Servings)',
                'category' => 'supplement',
                'description' => 'L-Citrulline, Beta-Alanine, and natural caffeine for explosive pump and laser mental focus.',
                'cost_price' => 24.50,
                'selling_price' => 44.99,
                'stock_quantity' => 18,
                'low_stock_threshold' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'BEV-ELEC-01',
                'barcode' => '782910394815',
                'name' => 'Prime Hydration Recovery Drink (Ice Pop 500ml)',
                'category' => 'beverage',
                'description' => '10% coconut water, 825mg electrolytes, zero added sugars.',
                'cost_price' => 1.20,
                'selling_price' => 3.50,
                'stock_quantity' => 96,
                'low_stock_threshold' => 20,
                'is_active' => true,
            ],
            [
                'sku' => 'BEV-PROTEIN-02',
                'barcode' => '782910394816',
                'name' => 'Barebells Protein Milkshake (Fudge 330ml, 24g Protein)',
                'category' => 'beverage',
                'description' => 'Ready-to-drink lactose-free high protein milkshake.',
                'cost_price' => 1.90,
                'selling_price' => 4.25,
                'stock_quantity' => 40,
                'low_stock_threshold' => 12,
                'is_active' => true,
            ],
            [
                'sku' => 'GEAR-STRAPS-01',
                'barcode' => '782910394817',
                'name' => 'Pro-Grip Heavy-Duty Padded Weightlifting Straps',
                'category' => 'accessory',
                'description' => 'Reinforced cotton webbing with neoprene wrist padding for heavy deadlifts and shrugs.',
                'cost_price' => 6.00,
                'selling_price' => 16.99,
                'stock_quantity' => 22,
                'low_stock_threshold' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'GEAR-BELT-02',
                'barcode' => '782910394818',
                'name' => '10mm Steel Lever Powerlifting Belt (Matte Black Leather, Size L)',
                'category' => 'accessory',
                'description' => 'Heavy genuine top-grain cowhide leather with quick-release chrome lever buckle.',
                'cost_price' => 45.00,
                'selling_price' => 89.99,
                'stock_quantity' => 8,
                'low_stock_threshold' => 3,
                'is_active' => true,
            ],
            [
                'sku' => 'APP-TEE-01',
                'barcode' => '782910394819',
                'name' => 'Club Oversized Heavyweight Cotton Tee (Black, Large)',
                'category' => 'apparel',
                'description' => '280 GSM heavyweight custom cotton with distressed gym chest insignia.',
                'cost_price' => 12.00,
                'selling_price' => 29.99,
                'stock_quantity' => 15,
                'low_stock_threshold' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SNACK-BAR-01',
                'barcode' => '782910394820',
                'name' => 'Barebells Caramel Cashew Protein Bar (55g, 20g Protein)',
                'category' => 'snack',
                'description' => 'Low sugar protein bar coated in milk chocolate and crunchy cashews.',
                'cost_price' => 1.40,
                'selling_price' => 3.25,
                'stock_quantity' => 3, // intentionally low stock for testing alerts!
                'low_stock_threshold' => 10,
                'is_active' => true,
            ],
            [
                'sku' => 'ACC-SHAKER-01',
                'barcode' => '782910394821',
                'name' => 'Stainless Steel Insulated Shaker Bottle 750ml',
                'category' => 'accessory',
                'description' => 'Double-wall vacuum insulated shaker with measurement markings and silent whisk ball.',
                'cost_price' => 8.50,
                'selling_price' => 19.99,
                'stock_quantity' => 34,
                'low_stock_threshold' => 8,
                'is_active' => true,
            ]
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(['sku' => $prod['sku']], $prod);
        }
    }
}
