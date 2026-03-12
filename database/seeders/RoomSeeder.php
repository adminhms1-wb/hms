<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Hotel;
use App\Models\Amenity;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotel = Hotel::first();
        
        if (!$hotel) {
            return;
        }

        $roomTypes = RoomType::all();
        if ($roomTypes->isEmpty()) {
            return;
        }

        $amenities = Amenity::all();
        $bedTypes = ['King', 'Queen', 'Twin', 'Double', 'Single'];
        $statuses = ['available', 'reserved', 'checked_in', 'checked_out', 'maintenance'];
        $floors = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

        $rooms = [];

        // Generate rooms for each room type
        foreach ($roomTypes as $roomType) {
            $roomCount = match($roomType->name) {
                'Standard' => 20,
                'Deluxe' => 15,
                'Suite' => 10,
                'Family' => 8,
                'Executive' => 12,
                'Presidential' => 3,
                default => 10,
            };

            for ($i = 1; $i <= $roomCount; $i++) {
                $floor = $floors[array_rand($floors)];
                $roomNumber = $floor . str_pad($i, 2, '0', STR_PAD_LEFT);
                
                $rooms[] = [
                    'hotel_id' => $hotel->id,
                    'room_type_id' => $roomType->id,
                    'room_number' => $roomNumber,
                    'floor' => $floor,
                    'max_guests' => $roomType->max_guests,
                    'bed_type' => $bedTypes[array_rand($bedTypes)],
                    'smoking' => rand(0, 1) === 1,
                    'status' => $statuses[array_rand($statuses)],
                ];
            }
        }

        // Create rooms in batches
        foreach ($rooms as $roomData) {
            $room = Room::create($roomData);
            
            // Assign random amenities (2-5 amenities per room)
            $randomAmenities = $amenities->random(rand(2, min(5, $amenities->count())));
            $room->amenities()->attach($randomAmenities->pluck('id')->toArray());
        }
    }
}
