<?php

namespace Database\Seeders;

use App\Models\SplitBilling;
use App\Models\SplitBillingItem;
use App\Models\Folio;
use App\Models\Guest;
use Illuminate\Database\Seeder;

class SplitBillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folios = Folio::where('status', 'open')->get();
        $guests = Guest::all();

        if ($folios->isEmpty()) {
            $this->command->warn('No open folios found. Please run FolioSeeder first.');
            return;
        }

        if ($guests->isEmpty()) {
            $this->command->warn('No guests found. Please run ReservationSeeder first.');
            return;
        }

        $splitTypes = ['equal', 'percentage', 'amount'];
        $statuses = ['pending', 'completed', 'cancelled'];

        foreach ($folios->take(10) as $folio) {
            $splitType = $splitTypes[array_rand($splitTypes)];
            $numberOfSplits = rand(2, 4);
            $totalAmount = $folio->total;

            $splitBilling = SplitBilling::create([
                'folio_id' => $folio->id,
                'split_type' => $splitType,
                'number_of_splits' => $numberOfSplits,
                'total_amount' => $totalAmount,
                'status' => $statuses[array_rand($statuses)],
                'notes' => rand(0, 1) ? 'Split bill request' : null,
            ]);

            // Create split billing items
            $selectedGuests = $guests->random($numberOfSplits);
            $remainingAmount = $totalAmount;

            foreach ($selectedGuests as $index => $guest) {
                if ($splitType === 'equal') {
                    $amount = $totalAmount / $numberOfSplits;
                    $percentage = 100 / $numberOfSplits;
                } elseif ($splitType === 'percentage') {
                    if ($index === $numberOfSplits - 1) {
                        $percentage = 100 - (($numberOfSplits - 1) * (100 / $numberOfSplits));
                    } else {
                        $percentage = 100 / $numberOfSplits;
                    }
                    $amount = $totalAmount * ($percentage / 100);
                } else { // amount
                    if ($index === $numberOfSplits - 1) {
                        $amount = $remainingAmount;
                    } else {
                        $amount = rand(500, (int)($remainingAmount * 100 * 0.4)) / 100;
                        $remainingAmount -= $amount;
                    }
                    $percentage = ($amount / $totalAmount) * 100;
                }

                SplitBillingItem::create([
                    'split_billing_id' => $splitBilling->id,
                    'guest_id' => $guest->id,
                    'guest_name' => $guest->name,
                    'amount' => $amount,
                    'percentage' => $percentage,
                    'payment_status' => rand(0, 1) ? 'paid' : 'pending',
                ]);
            }
        }

        $this->command->info('Created ' . SplitBilling::count() . ' split billings.');
    }
}
