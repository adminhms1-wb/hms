<?php

namespace Database\Seeders;

use App\Models\RoomTypeTime;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all room types
        $roomTypes = RoomType::all();
        
        if ($roomTypes->isEmpty()) {
            $this->command->warn('No room types found. Please run RoomTypeSeeder first.');
            return;
        }

        // Room type specific check-in/check-out times
        $timeConfigurations = [
            [
                'room_type_name' => 'Standard',
                'checkin_time' => '14:00',
                'checkout_time' => '11:00',
                'early_checkin_allowed' => false,
                'early_checkin_time' => null,
                'late_checkout_allowed' => false,
                'late_checkout_time' => null,
                'late_checkout_fee' => 0,
            ],
            [
                'room_type_name' => 'Deluxe',
                'checkin_time' => '15:00',
                'checkout_time' => '12:00',
                'early_checkin_allowed' => true,
                'early_checkin_time' => '13:00',
                'late_checkout_allowed' => true,
                'late_checkout_time' => '14:00',
                'late_checkout_fee' => 25.00,
            ],
            [
                'room_type_name' => 'Suite',
                'checkin_time' => '15:00',
                'checkout_time' => '12:00',
                'early_checkin_allowed' => true,
                'early_checkin_time' => '12:00',
                'late_checkout_allowed' => true,
                'late_checkout_time' => '15:00',
                'late_checkout_fee' => 50.00,
            ],
            [
                'room_type_name' => 'Family',
                'checkin_time' => '14:00',
                'checkout_time' => '11:00',
                'early_checkin_allowed' => true,
                'early_checkin_time' => '13:00',
                'late_checkout_allowed' => false,
                'late_checkout_time' => null,
                'late_checkout_fee' => 0,
            ],
            [
                'room_type_name' => 'Executive',
                'checkin_time' => '15:00',
                'checkout_time' => '12:00',
                'early_checkin_allowed' => true,
                'early_checkin_time' => '13:00',
                'late_checkout_allowed' => true,
                'late_checkout_time' => '14:00',
                'late_checkout_fee' => 35.00,
            ],
            [
                'room_type_name' => 'Presidential',
                'checkin_time' => '16:00',
                'checkout_time' => '13:00',
                'early_checkin_allowed' => true,
                'early_checkin_time' => '12:00',
                'late_checkout_allowed' => true,
                'late_checkout_time' => '16:00',
                'late_checkout_fee' => 100.00,
            ],
        ];

        // Create time configurations for matching room types
        foreach ($roomTypes as $roomType) {
            // Find matching configuration
            $config = null;
            foreach ($timeConfigurations as $timeConfig) {
                if (strtolower($roomType->name) === strtolower($timeConfig['room_type_name'])) {
                    $config = $timeConfig;
                    break;
                }
            }

            // If no specific config found, use default
            if (!$config) {
                $config = [
                    'checkin_time' => '14:00',
                    'checkout_time' => '11:00',
                    'early_checkin_allowed' => false,
                    'early_checkin_time' => null,
                    'late_checkout_allowed' => false,
                    'late_checkout_time' => null,
                    'late_checkout_fee' => 0,
                ];
            }

            // Check if room type already has a time configuration
            $existing = RoomTypeTime::where('room_type_id', $roomType->id)->first();
            if (!$existing) {
                RoomTypeTime::create([
                    'room_type_id' => $roomType->id,
                    'checkin_time' => $config['checkin_time'],
                    'checkout_time' => $config['checkout_time'],
                    'early_checkin_allowed' => $config['early_checkin_allowed'],
                    'early_checkin_time' => $config['early_checkin_time'],
                    'late_checkout_allowed' => $config['late_checkout_allowed'],
                    'late_checkout_time' => $config['late_checkout_time'],
                    'late_checkout_fee' => $config['late_checkout_fee'],
                ]);
            }
        }

        $this->command->info('Room type times seeded successfully!');
    }
}
