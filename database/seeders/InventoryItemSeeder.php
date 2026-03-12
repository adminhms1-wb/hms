<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Coffee Beans',
                'category' => 'Food & Beverages',
                'quantity' => 120,
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'threshold' => 50,
            ],
            [
                'name' => 'Tea Bags',
                'category' => 'Food & Beverages',
                'quantity' => 240,
                'expiry_date' => now()->addMonths(4)->toDateString(),
                'threshold' => 80,
            ],
            [
                'name' => 'Bottled Water',
                'category' => 'Food & Beverages',
                'quantity' => 500,
                'expiry_date' => null,
                'threshold' => 150,
            ],
            [
                'name' => 'Bath Towels',
                'category' => 'Housekeeping',
                'quantity' => 200,
                'expiry_date' => null,
                'threshold' => 80,
            ],
            [
                'name' => 'Bed Sheets',
                'category' => 'Housekeeping',
                'quantity' => 160,
                'expiry_date' => null,
                'threshold' => 60,
            ],
            [
                'name' => 'Shampoo Bottles',
                'category' => 'Guest Amenities',
                'quantity' => 350,
                'expiry_date' => now()->addMonths(8)->toDateString(),
                'threshold' => 150,
            ],
            [
                'name' => 'Soap Bars',
                'category' => 'Guest Amenities',
                'quantity' => 600,
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'threshold' => 300,
            ],
            [
                'name' => 'Toilet Paper Rolls',
                'category' => 'Housekeeping',
                'quantity' => 400,
                'expiry_date' => null,
                'threshold' => 200,
            ],
            [
                'name' => 'Printer Paper Reams',
                'category' => 'Office Supplies',
                'quantity' => 120,
                'expiry_date' => null,
                'threshold' => 40,
            ],
            [
                'name' => 'Cleaning Detergent',
                'category' => 'Housekeeping',
                'quantity' => 90,
                'expiry_date' => now()->addMonths(10)->toDateString(),
                'threshold' => 40,
            ],
        ];

        foreach ($items as $item) {
            InventoryItem::create($item);
        }
    }
}

