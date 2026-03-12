<?php

namespace Database\Seeders;

use App\Models\SeasonalPrice;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SeasonalPriceSeeder extends Seeder
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

        // Seasonal pricing data
        $seasons = [
            // Summer Season (June - August)
            [
                'name' => 'Summer Season',
                'description' => 'Peak summer season with high demand',
                'start_date' => Carbon::create($currentYear, 6, 1),
                'end_date' => Carbon::create($currentYear, 8, 31),
                'price_multiplier' => 1.3, // 30% increase
            ],
            // Winter Holiday Season (December - January)
            [
                'name' => 'Winter Holiday',
                'description' => 'Holiday season with premium pricing',
                'start_date' => Carbon::create($currentYear, 12, 15),
                'end_date' => Carbon::create($nextYear, 1, 15),
                'price_multiplier' => 1.5, // 50% increase
            ],
            // Spring Season (March - May)
            [
                'name' => 'Spring Season',
                'description' => 'Pleasant spring weather pricing',
                'start_date' => Carbon::create($currentYear, 3, 1),
                'end_date' => Carbon::create($currentYear, 5, 31),
                'price_multiplier' => 1.15, // 15% increase
            ],
            // Fall Season (September - November)
            [
                'name' => 'Fall Season',
                'description' => 'Autumn season pricing',
                'start_date' => Carbon::create($currentYear, 9, 1),
                'end_date' => Carbon::create($currentYear, 11, 30),
                'price_multiplier' => 1.1, // 10% increase
            ],
            // New Year Special (January 1-7)
            [
                'name' => 'New Year Special',
                'description' => 'New Year celebration period',
                'start_date' => Carbon::create($nextYear, 1, 1),
                'end_date' => Carbon::create($nextYear, 1, 7),
                'price_multiplier' => 1.4, // 40% increase
            ],
            // Valentine's Day (February 10-16)
            [
                'name' => 'Valentine\'s Special',
                'description' => 'Valentine\'s Day romantic getaway',
                'start_date' => Carbon::create($currentYear, 2, 10),
                'end_date' => Carbon::create($currentYear, 2, 16),
                'price_multiplier' => 1.25, // 25% increase
            ],
        ];

        // Create seasonal prices for each room type
        foreach ($roomTypes as $roomType) {
            $basePrice = $roomType->base_price ?? 100; // Default to 100 if no base price
            
            foreach ($seasons as $season) {
                // Calculate seasonal price based on base price
                $seasonalPrice = $basePrice * $season['price_multiplier'];
                
                SeasonalPrice::create([
                    'room_type_id' => $roomType->id,
                    'name' => $season['name'],
                    'description' => $season['description'],
                    'start_date' => $season['start_date'],
                    'end_date' => $season['end_date'],
                    'price' => round($seasonalPrice, 2),
                ]);
            }
        }

        $this->command->info('Seasonal prices seeded successfully!');
    }
}
