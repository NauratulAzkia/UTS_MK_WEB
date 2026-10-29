<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'owner@example.test'], [
            'name' => 'Paw Pantry Owner', 'password' => Hash::make('password'), 'role' => 'admin',
        ]);
        User::updateOrCreate(['email' => 'staff@example.test'], [
            'name' => 'Paw Pantry Staff', 'password' => Hash::make('password'), 'role' => 'staff',
        ]);

        $legacyCategory = Category::where('name', 'Umum')->first();
        if ($legacyCategory && ! Category::where('name', 'Makanan Kering')->exists()) {
            $legacyCategory->update(['name' => 'Makanan Kering']);
        }

        $dryFood = Category::firstOrCreate(['name' => 'Makanan Kering']);
        $wetFood = Category::firstOrCreate(['name' => 'Makanan Basah']);
        $vetDiet = Category::firstOrCreate(['name' => 'Diet Veteriner']);

        $legacyProduct = Product::where('sku', 'DEMO-001')->first();
        if ($legacyProduct && ! Product::where('sku', 'RC-FIT32-2KG')->exists()) {
            $legacyProduct->update([
                'category_id' => $dryFood->id,
                'sku' => 'RC-FIT32-2KG',
                'name' => 'Royal Canin Fit 32 Adult 2 kg',
                'purchase_price' => 211000,
                'selling_price' => 269000,
                'stock' => $legacyProduct->purchaseItems()->exists() || $legacyProduct->saleItems()->exists()
                    ? $legacyProduct->stock
                    : 8,
                'minimum_stock' => 5,
            ]);
        }

        $products = [
            ['category' => $dryFood, 'sku' => 'RC-FIT32-2KG', 'name' => 'Royal Canin Fit 32 Adult 2 kg', 'purchase_price' => 211000, 'selling_price' => 269000, 'stock' => 8, 'minimum_stock' => 5],
            ['category' => $vetDiet, 'sku' => 'RC-URINARY-15KG', 'name' => 'Royal Canin Veterinary Urinary S/O 1.5 kg', 'purchase_price' => 376000, 'selling_price' => 459000, 'stock' => 4, 'minimum_stock' => 3],
            ['category' => $dryFood, 'sku' => 'PP-ADULT-15KG', 'name' => 'Pro Plan Adult Chicken 1.5 kg', 'purchase_price' => 170000, 'selling_price' => 219000, 'stock' => 3, 'minimum_stock' => 5],
            ['category' => $dryFood, 'sku' => 'HILLS-ADULT-15KG', 'name' => "Hill's Science Diet Adult Chicken 1.5 kg", 'purchase_price' => 219000, 'selling_price' => 279000, 'stock' => 6, 'minimum_stock' => 4],
            ['category' => $dryFood, 'sku' => 'BRIT-ADULT-15KG', 'name' => 'Brit Premium by Nature Cat Adult Chicken 1.5 kg', 'purchase_price' => 117000, 'selling_price' => 149000, 'stock' => 10, 'minimum_stock' => 4],
            ['category' => $dryFood, 'sku' => 'MEO-GOLD-12KG', 'name' => 'Me-O Gold Adult Salmon 1.2 kg', 'purchase_price' => 68000, 'selling_price' => 89000, 'stock' => 2, 'minimum_stock' => 4],
            ['category' => $dryFood, 'sku' => 'WHISKAS-ADULT-11KG', 'name' => 'Whiskas Adult Ocean Fish 1.1 kg', 'purchase_price' => 58000, 'selling_price' => 74000, 'stock' => 20, 'minimum_stock' => 6],
            ['category' => $dryFood, 'sku' => 'FRISKIES-ADULT-11KG', 'name' => 'Purina Friskies Adult Ocean Fish 1.1 kg', 'purchase_price' => 55000, 'selling_price' => 69000, 'stock' => 10, 'minimum_stock' => 6],
            ['category' => $wetFood, 'sku' => 'RC-INSTINCTIVE-85G', 'name' => 'Royal Canin Instinctive Gravy 85 g', 'purchase_price' => 16000, 'selling_price' => 22000, 'stock' => 2, 'minimum_stock' => 8],
            ['category' => $wetFood, 'sku' => 'PP-CHICKEN-WET-85G', 'name' => 'Pro Plan Adult Chicken Wet 85 g', 'purchase_price' => 14000, 'selling_price' => 19000, 'stock' => 10, 'minimum_stock' => 8],
            ['category' => $wetFood, 'sku' => 'SHEBA-TUNA-70G', 'name' => 'Sheba Tuna Pouch 70 g', 'purchase_price' => 11000, 'selling_price' => 15000, 'stock' => 6, 'minimum_stock' => 5],
            ['category' => $wetFood, 'sku' => 'WHISKAS-POUCH-80G', 'name' => 'Whiskas Ocean Fish Pouch 80 g', 'purchase_price' => 6000, 'selling_price' => 8500, 'stock' => 3, 'minimum_stock' => 10],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['sku' => $product['sku']], [
                'category_id' => $product['category']->id,
                'name' => $product['name'],
                'purchase_price' => $product['purchase_price'],
                'selling_price' => $product['selling_price'],
                'stock' => $product['stock'],
                'minimum_stock' => $product['minimum_stock'],
            ]);
        }

        Supplier::updateOrCreate(['email' => 'supplier@example.test'], [
            'name' => 'Paw Pantry — Distributor Pakan',
            'phone' => '0800000000',
        ]);
    }
}
