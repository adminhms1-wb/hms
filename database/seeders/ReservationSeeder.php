<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();
        
        if ($rooms->isEmpty()) {
            $this->command->warn('No rooms found. Please run RoomSeeder first.');
            return;
        }

        // Create some dummy guests
        $guests = [];
        $guestNames = [
            'John Smith', 'Sarah Johnson', 'Michael Brown', 'Emily Davis',
            'David Wilson', 'Jessica Martinez', 'Robert Taylor', 'Amanda Anderson',
            'James Thomas', 'Lisa Jackson', 'William White', 'Michelle Harris',
            'Richard Martin', 'Jennifer Thompson', 'Joseph Garcia', 'Ashley Martinez',
            'Charles Robinson', 'Stephanie Clark', 'Daniel Rodriguez', 'Nicole Lewis'
        ];

        foreach ($guestNames as $name) {
            $guests[] = Guest::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'phone' => '+1' . rand(2000000000, 9999999999),
                'id_number' => 'ID' . rand(100000, 999999),
            ]);
        }

        $statuses = ['confirmed', 'checked_in', 'checked_out', 'pending'];
        $reservations = [];

        // Create reservations for the next 30 days
        for ($i = 0; $i < 50; $i++) {
            $room = $rooms->random();
            $guest = $guests[array_rand($guests)];
            
            // Random check-in date between today and 30 days from now
            $checkInDate = Carbon::now()->addDays(rand(0, 30));
            // Check-out date is 1-7 days after check-in
            $checkOutDate = $checkInDate->copy()->addDays(rand(1, 7));
            
            // Skip if check-out is in the past
            if ($checkOutDate->isPast()) {
                continue;
            }

            $roomType = $room->roomType;
            $basePrice = $roomType ? $roomType->base_price : 100;
            $nights = $checkInDate->diffInDays($checkOutDate);
            $totalAmount = $basePrice * $nights;

            $reservations[] = [
                'room_id' => $room->id,
                'guest_id' => $guest->id,
                'guest_name' => $guest->name,
                'guest_email' => $guest->email,
                'guest_phone' => $guest->phone,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'number_of_guests' => rand(1, $room->max_guests),
                'total_amount' => $totalAmount,
                'status' => $statuses[array_rand($statuses)],
                'special_requests' => rand(0, 1) ? 'Late check-in requested' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Create reservations in batches
        foreach (array_chunk($reservations, 10) as $chunk) {
            Reservation::insert($chunk);
        }

        $this->command->info('Created ' . count($reservations) . ' dummy reservations.');
    }
}
