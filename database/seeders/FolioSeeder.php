<?php

namespace Database\Seeders;

use App\Models\Folio;
use App\Models\FolioItem;
use App\Models\Reservation;
use App\Models\Guest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservations = Reservation::all();
        $guests = Guest::all();

        if ($reservations->isEmpty()) {
            $this->command->warn('No reservations found. Please run ReservationSeeder first.');
            return;
        }

        $modules = ['room', 'restaurant', 'service', 'amenities'];
        $statuses = ['open', 'closed', 'cancelled'];

        foreach ($reservations->take(30) as $reservation) {
            $guest = $guests->random();
            
            $subtotal = rand(10000, 50000) / 100; // $100 to $500
            $taxRate = 10; // 10%
            $serviceChargeRate = 5; // 5%
            $taxAmount = $subtotal * ($taxRate / 100);
            $serviceCharge = $subtotal * ($serviceChargeRate / 100);
            $discount = rand(0, 2000) / 100; // $0 to $20
            $total = $subtotal + $taxAmount + $serviceCharge - $discount;
            $paid = rand(0, (int)($total * 100)) / 100;
            $balance = $total - $paid;

            $folio = Folio::create([
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'discount' => $discount,
                'total' => $total,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $statuses[array_rand($statuses)],
                'folio_date' => Carbon::now()->subDays(rand(0, 30))->toDateString(),
                'notes' => rand(0, 1) ? 'Special request handled' : null,
            ]);

            // Create folio items
            $itemCount = rand(2, 5);
            $itemDescriptions = [
                'room' => ['Room Charges', 'Room Service', 'Mini Bar'],
                'restaurant' => ['Breakfast', 'Lunch', 'Dinner', 'Room Service Order'],
                'service' => ['Spa Service', 'Laundry Service', 'Concierge Service'],
                'amenities' => ['Gym Access', 'Pool Access', 'WiFi Service'],
            ];

            $remainingAmount = $subtotal;
            for ($i = 0; $i < $itemCount; $i++) {
                $module = $modules[array_rand($modules)];
                $descriptions = $itemDescriptions[$module];
                $description = $descriptions[array_rand($descriptions)];
                
                if ($i === $itemCount - 1) {
                    $amount = $remainingAmount;
                } else {
                    $amount = rand(500, (int)($remainingAmount * 100 * 0.4)) / 100;
                    $remainingAmount -= $amount;
                }

                FolioItem::create([
                    'folio_id' => $folio->id,
                    'description' => $description,
                    'amount' => $amount,
                    'module' => $module,
                ]);
            }
        }

        $this->command->info('Created ' . Folio::count() . ' folios with items.');
    }
}
