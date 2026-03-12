<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PricingRuleSeeder extends Seeder
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

        // Current year for date calculations
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        // Weekend Pricing Rules
        $weekendRules = [
            [
                'name' => 'Friday Premium',
                'rule_type' => 'weekend',
                'day_of_week' => 'friday',
                'price_multiplier' => 1.15, // 15% increase
                'is_active' => true,
            ],
            [
                'name' => 'Saturday Premium',
                'rule_type' => 'weekend',
                'day_of_week' => 'saturday',
                'price_multiplier' => 1.20, // 20% increase
                'is_active' => true,
            ],
            [
                'name' => 'Sunday Premium',
                'rule_type' => 'weekend',
                'day_of_week' => 'sunday',
                'price_multiplier' => 1.10, // 10% increase
                'is_active' => true,
            ],
        ];

        // Peak Season Rules
        $peakRules = [
            [
                'name' => 'Summer Peak Season',
                'rule_type' => 'peak',
                'start_date' => Carbon::create($currentYear, 6, 15),
                'end_date' => Carbon::create($currentYear, 8, 31),
                'price_multiplier' => 1.35, // 35% increase
                'is_active' => true,
            ],
            [
                'name' => 'Winter Peak Season',
                'rule_type' => 'peak',
                'start_date' => Carbon::create($currentYear, 12, 20),
                'end_date' => Carbon::create($nextYear, 1, 10),
                'price_multiplier' => 1.40, // 40% increase
                'is_active' => true,
            ],
            [
                'name' => 'Spring Break',
                'rule_type' => 'peak',
                'start_date' => Carbon::create($currentYear, 3, 15),
                'end_date' => Carbon::create($currentYear, 4, 15),
                'price_multiplier' => 1.30, // 30% increase
                'is_active' => true,
            ],
        ];

        // Holiday Rules
        $holidayRules = [
            [
                'name' => 'New Year Holiday',
                'rule_type' => 'holiday',
                'start_date' => Carbon::create($nextYear, 1, 1),
                'end_date' => Carbon::create($nextYear, 1, 3),
                'price_multiplier' => 1.50, // 50% increase
                'is_active' => true,
            ],
            [
                'name' => 'Christmas Holiday',
                'rule_type' => 'holiday',
                'start_date' => Carbon::create($currentYear, 12, 24),
                'end_date' => Carbon::create($currentYear, 12, 26),
                'price_multiplier' => 1.45, // 45% increase
                'is_active' => true,
            ],
            [
                'name' => 'Independence Day',
                'rule_type' => 'holiday',
                'start_date' => Carbon::create($currentYear, 7, 3),
                'end_date' => Carbon::create($currentYear, 7, 5),
                'price_multiplier' => 1.25, // 25% increase
                'is_active' => true,
            ],
            [
                'name' => 'Thanksgiving Holiday',
                'rule_type' => 'holiday',
                'start_date' => Carbon::create($currentYear, 11, 27),
                'end_date' => Carbon::create($currentYear, 11, 30),
                'price_multiplier' => 1.30, // 30% increase
                'is_active' => true,
            ],
        ];

        // Create pricing rules for each room type
        foreach ($roomTypes as $roomType) {
            // Weekend rules - apply to all room types
            foreach ($weekendRules as $rule) {
                PricingRule::create([
                    'room_type_id' => $roomType->id,
                    'rule_type' => $rule['rule_type'],
                    'name' => $rule['name'],
                    'day_of_week' => $rule['day_of_week'],
                    'price_multiplier' => $rule['price_multiplier'],
                    'is_active' => $rule['is_active'],
                ]);
            }

            // Peak season rules - apply to all room types
            foreach ($peakRules as $rule) {
                PricingRule::create([
                    'room_type_id' => $roomType->id,
                    'rule_type' => $rule['rule_type'],
                    'name' => $rule['name'],
                    'start_date' => $rule['start_date'],
                    'end_date' => $rule['end_date'],
                    'price_multiplier' => $rule['price_multiplier'],
                    'is_active' => $rule['is_active'],
                ]);
            }

            // Holiday rules - apply to all room types
            foreach ($holidayRules as $rule) {
                PricingRule::create([
                    'room_type_id' => $roomType->id,
                    'rule_type' => $rule['rule_type'],
                    'name' => $rule['name'],
                    'start_date' => $rule['start_date'],
                    'end_date' => $rule['end_date'],
                    'price_multiplier' => $rule['price_multiplier'],
                    'is_active' => $rule['is_active'],
                ]);
            }
        }

        // Create some general rules (no specific room type - applies to all)
        $generalRules = [
            [
                'name' => 'Weekend Special - All Types',
                'rule_type' => 'weekend',
                'day_of_week' => 'saturday',
                'price_multiplier' => 1.18, // 18% increase
                'is_active' => true,
            ],
            [
                'name' => 'Holiday Weekend - All Types',
                'rule_type' => 'weekend',
                'day_of_week' => 'sunday',
                'price_multiplier' => 1.12, // 12% increase
                'is_active' => true,
            ],
        ];

        foreach ($generalRules as $rule) {
            PricingRule::create([
                'room_type_id' => null, // Applies to all room types
                'rule_type' => $rule['rule_type'],
                'name' => $rule['name'],
                'day_of_week' => $rule['day_of_week'],
                'price_multiplier' => $rule['price_multiplier'],
                'is_active' => $rule['is_active'],
            ]);
        }

        $this->command->info('Pricing rules seeded successfully!');
    }
}
