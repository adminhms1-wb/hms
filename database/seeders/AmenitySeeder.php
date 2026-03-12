<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'WiFi', 'is_paid' => false, 'price' => 0.00],
            ['name' => 'Air Conditioning', 'is_paid' => false, 'price' => 0.00],
            ['name' => 'TV', 'is_paid' => false, 'price' => 0.00],
            ['name' => 'Mini Bar', 'is_paid' => false, 'price' => 0.00],
            ['name' => 'Room Service', 'is_paid' => true, 'price' => 15.00],
            ['name' => 'Breakfast', 'is_paid' => true, 'price' => 25.00],
            ['name' => 'Safe', 'is_paid' => false, 'price' => 0.00],
            ['name' => 'Balcony', 'is_paid' => false, 'price' => 0.00],
            ['name' => 'Ocean View', 'is_paid' => true, 'price' => 50.00],
            ['name' => 'Jacuzzi', 'is_paid' => false, 'price' => 0.00],
            ['name' => 'Gym Access', 'is_paid' => true, 'price' => 20.00],
            ['name' => 'Spa Access', 'is_paid' => true, 'price' => 75.00],
            ['name' => 'Parking', 'is_paid' => true, 'price' => 10.00],
            ['name' => 'Laundry Service', 'is_paid' => true, 'price' => 30.00],
            ['name' => 'Pet Friendly', 'is_paid' => true, 'price' => 25.00],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
