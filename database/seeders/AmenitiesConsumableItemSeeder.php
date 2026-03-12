<?php

namespace Database\Seeders;

use App\Models\AmenitiesConsumableItem;
use Illuminate\Database\Seeder;

class AmenitiesConsumableItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'code' => 'AMN-001',
                'name' => 'Shampoo (Bottles)',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 200,
                'min_stock' => 150,
                'unit' => 'bottles',
                'description' => 'Premium hotel shampoo bottles'
            ],
            [
                'code' => 'AMN-002',
                'name' => 'Conditioner (Bottles)',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 180,
                'min_stock' => 130,
                'unit' => 'bottles',
                'description' => 'Premium hotel conditioner bottles'
            ],
            [
                'code' => 'AMN-003',
                'name' => 'Body Wash (Bottles)',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 190,
                'min_stock' => 140,
                'unit' => 'bottles',
                'description' => 'Premium body wash bottles'
            ],
            [
                'code' => 'AMN-004',
                'name' => 'Body Lotion (Bottles)',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 170,
                'min_stock' => 120,
                'unit' => 'bottles',
                'description' => 'Moisturizing body lotion bottles'
            ],
            [
                'code' => 'AMN-005',
                'name' => 'Soap Bars',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 500,
                'min_stock' => 400,
                'unit' => 'pcs',
                'description' => 'Premium soap bars'
            ],
            [
                'code' => 'AMN-006',
                'name' => 'Toothbrushes',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 300,
                'min_stock' => 200,
                'unit' => 'pcs',
                'description' => 'Disposable toothbrushes'
            ],
            [
                'code' => 'AMN-007',
                'name' => 'Toothpaste',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 250,
                'min_stock' => 180,
                'unit' => 'tubes',
                'description' => 'Travel-size toothpaste tubes'
            ],
            [
                'code' => 'AMN-008',
                'name' => 'Cotton Swabs',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 400,
                'min_stock' => 300,
                'unit' => 'packs',
                'description' => 'Cotton swab packs'
            ],
            [
                'code' => 'AMN-009',
                'name' => 'Shower Caps',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 350,
                'min_stock' => 250,
                'unit' => 'pcs',
                'description' => 'Disposable shower caps'
            ],
            [
                'code' => 'AMN-010',
                'name' => 'Coffee Pods',
                'category' => 'Beverage Amenities',
                'category_id' => 2,
                'stock' => 1000,
                'min_stock' => 800,
                'unit' => 'pods',
                'description' => 'Single-serve coffee pods'
            ],
            [
                'code' => 'AMN-011',
                'name' => 'Tea Bags',
                'category' => 'Beverage Amenities',
                'category_id' => 2,
                'stock' => 800,
                'min_stock' => 600,
                'unit' => 'bags',
                'description' => 'Assorted tea bags'
            ],
            [
                'code' => 'AMN-012',
                'name' => 'Sugar Packets',
                'category' => 'Beverage Amenities',
                'category_id' => 2,
                'stock' => 2000,
                'min_stock' => 1500,
                'unit' => 'packets',
                'description' => 'Sugar packets for beverages'
            ],
            [
                'code' => 'AMN-013',
                'name' => 'Creamer Packets',
                'category' => 'Beverage Amenities',
                'category_id' => 2,
                'stock' => 1500,
                'min_stock' => 1000,
                'unit' => 'packets',
                'description' => 'Non-dairy creamer packets'
            ],
            [
                'code' => 'AMN-014',
                'name' => 'Bottled Water',
                'category' => 'Beverage Amenities',
                'category_id' => 2,
                'stock' => 500,
                'min_stock' => 400,
                'unit' => 'bottles',
                'description' => 'Premium bottled water'
            ],
            [
                'code' => 'AMN-015',
                'name' => 'Slippers',
                'category' => 'Room Amenities',
                'category_id' => 3,
                'stock' => 200,
                'min_stock' => 150,
                'unit' => 'pairs',
                'description' => 'Disposable hotel slippers'
            ],
            [
                'code' => 'AMN-016',
                'name' => 'Shoe Shine Cloths',
                'category' => 'Room Amenities',
                'category_id' => 3,
                'stock' => 150,
                'min_stock' => 100,
                'unit' => 'pcs',
                'description' => 'Shoe shine cloths'
            ],
            [
                'code' => 'AMN-017',
                'name' => 'Laundry Bags',
                'category' => 'Room Amenities',
                'category_id' => 3,
                'stock' => 300,
                'min_stock' => 200,
                'unit' => 'pcs',
                'description' => 'Disposable laundry bags'
            ],
            [
                'code' => 'AMN-018',
                'name' => 'Tissue Boxes',
                'category' => 'Room Amenities',
                'category_id' => 3,
                'stock' => 400,
                'min_stock' => 300,
                'unit' => 'boxes',
                'description' => 'Facial tissue boxes'
            ],
            [
                'code' => 'AMN-019',
                'name' => 'Toilet Paper Rolls',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 600,
                'min_stock' => 500,
                'unit' => 'rolls',
                'description' => 'Premium toilet paper rolls'
            ],
            [
                'code' => 'AMN-020',
                'name' => 'Hand Sanitizer',
                'category' => 'Bathroom Amenities',
                'category_id' => 1,
                'stock' => 250,
                'min_stock' => 200,
                'unit' => 'bottles',
                'description' => 'Hand sanitizer bottles'
            ]
        ];

        foreach ($items as $item) {
            AmenitiesConsumableItem::create($item);
        }
    }
}
