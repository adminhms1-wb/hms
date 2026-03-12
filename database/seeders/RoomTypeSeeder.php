<?php

namespace Database\Seeders;

use App\Models\RoomType;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotel = Hotel::first();
        
        if (!$hotel) {
            // Create a default hotel if none exists
            $hotel = Hotel::create([
                'name' => 'Grand Hotel',
                'address' => '123 Main Street',
                'city' => 'New York',
                'country' => 'USA',
                'tax_percentage' => 10.00,
                'service_charge' => 5.00,
            ]);
        }

        $roomTypes = [
            [
                'name' => 'Standard',
                'description' => 'Comfortable standard room with essential amenities. Perfect for budget-conscious travelers.',
                'base_price' => 99.00,
                'max_guests' => 2,
            ],
            [
                'name' => 'Deluxe',
                'description' => 'Spacious deluxe room with premium amenities and modern furnishings. Ideal for business travelers.',
                'base_price' => 149.00,
                'max_guests' => 3,
            ],
            [
                'name' => 'Suite',
                'description' => 'Luxurious suite with separate living area, premium amenities, and stunning views.',
                'base_price' => 299.00,
                'max_guests' => 4,
            ],
            [
                'name' => 'Family',
                'description' => 'Family-friendly room with multiple beds and extra space. Perfect for families with children.',
                'base_price' => 199.00,
                'max_guests' => 5,
            ],
            [
                'name' => 'Executive',
                'description' => 'Executive room with work desk, premium amenities, and exclusive access to business facilities.',
                'base_price' => 249.00,
                'max_guests' => 2,
            ],
            [
                'name' => 'Presidential',
                'description' => 'Ultra-luxurious presidential suite with private balcony, jacuzzi, and premium concierge service.',
                'base_price' => 599.00,
                'max_guests' => 6,
            ],
        ];

        foreach ($roomTypes as $roomType) {
            RoomType::create([
                'hotel_id' => $hotel->id,
                'name' => $roomType['name'],
                'description' => $roomType['description'],
                'base_price' => $roomType['base_price'],
                'max_guests' => $roomType['max_guests'],
            ]);
        }
    }
}
