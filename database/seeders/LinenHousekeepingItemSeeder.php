<?php

namespace Database\Seeders;

use App\Models\LinenHousekeepingItem;
use Illuminate\Database\Seeder;

class LinenHousekeepingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'code' => 'LIN-001',
                'name' => 'Bed Sheets (Single)',
                'category' => 'Bed Linen',
                'category_id' => 1,
                'stock' => 120,
                'min_stock' => 80,
                'unit' => 'sets',
                'description' => 'Premium cotton bed sheets for single beds'
            ],
            [
                'code' => 'LIN-002',
                'name' => 'Bed Sheets (Double)',
                'category' => 'Bed Linen',
                'category_id' => 1,
                'stock' => 150,
                'min_stock' => 100,
                'unit' => 'sets',
                'description' => 'Premium cotton bed sheets for double beds'
            ],
            [
                'code' => 'LIN-003',
                'name' => 'Pillowcases',
                'category' => 'Bed Linen',
                'category_id' => 1,
                'stock' => 300,
                'min_stock' => 200,
                'unit' => 'pcs',
                'description' => 'Standard pillowcases'
            ],
            [
                'code' => 'LIN-004',
                'name' => 'Bath Towels',
                'category' => 'Bath Linen',
                'category_id' => 2,
                'stock' => 200,
                'min_stock' => 150,
                'unit' => 'pcs',
                'description' => 'Premium bath towels'
            ],
            [
                'code' => 'LIN-005',
                'name' => 'Hand Towels',
                'category' => 'Bath Linen',
                'category_id' => 2,
                'stock' => 250,
                'min_stock' => 180,
                'unit' => 'pcs',
                'description' => 'Standard hand towels'
            ],
            [
                'code' => 'LIN-006',
                'name' => 'Face Towels',
                'category' => 'Bath Linen',
                'category_id' => 2,
                'stock' => 180,
                'min_stock' => 120,
                'unit' => 'pcs',
                'description' => 'Small face towels'
            ],
            [
                'code' => 'LIN-007',
                'name' => 'Bathrobes',
                'category' => 'Bath Linen',
                'category_id' => 2,
                'stock' => 80,
                'min_stock' => 60,
                'unit' => 'pcs',
                'description' => 'Hotel bathrobes'
            ],
            [
                'code' => 'HK-001',
                'name' => 'Vacuum Cleaner',
                'category' => 'Housekeeping Tools',
                'category_id' => 4,
                'stock' => 8,
                'min_stock' => 5,
                'unit' => 'pcs',
                'description' => 'Commercial vacuum cleaner'
            ],
            [
                'code' => 'HK-002',
                'name' => 'Mop Buckets',
                'category' => 'Housekeeping Tools',
                'category_id' => 4,
                'stock' => 15,
                'min_stock' => 10,
                'unit' => 'pcs',
                'description' => 'Heavy-duty mop buckets'
            ],
            [
                'code' => 'HK-003',
                'name' => 'Cleaning Carts',
                'category' => 'Housekeeping Tools',
                'category_id' => 4,
                'stock' => 12,
                'min_stock' => 8,
                'unit' => 'pcs',
                'description' => 'Housekeeping service carts'
            ],
            [
                'code' => 'CS-001',
                'name' => 'Laundry Detergent',
                'category' => 'Cleaning Supplies',
                'category_id' => 3,
                'stock' => 45,
                'min_stock' => 30,
                'unit' => 'kg',
                'description' => 'Commercial laundry detergent'
            ],
            [
                'code' => 'CS-002',
                'name' => 'Fabric Softener',
                'category' => 'Cleaning Supplies',
                'category_id' => 3,
                'stock' => 30,
                'min_stock' => 25,
                'unit' => 'liters',
                'description' => 'Fabric softener for laundry'
            ],
            [
                'code' => 'LIN-008',
                'name' => 'Duvet Covers',
                'category' => 'Bed Linen',
                'category_id' => 1,
                'stock' => 100,
                'min_stock' => 70,
                'unit' => 'pcs',
                'description' => 'Duvet covers for beds'
            ],
            [
                'code' => 'LIN-009',
                'name' => 'Mattress Protectors',
                'category' => 'Bed Linen',
                'category_id' => 1,
                'stock' => 90,
                'min_stock' => 60,
                'unit' => 'pcs',
                'description' => 'Waterproof mattress protectors'
            ],
            [
                'code' => 'LIN-010',
                'name' => 'Bath Mats',
                'category' => 'Bath Linen',
                'category_id' => 2,
                'stock' => 60,
                'min_stock' => 40,
                'unit' => 'pcs',
                'description' => 'Non-slip bath mats'
            ]
        ];

        foreach ($items as $item) {
            LinenHousekeepingItem::create($item);
        }
    }
}
