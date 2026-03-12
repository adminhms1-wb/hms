<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Folio;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PartialPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folios = Folio::where('status', 'open')->orWhere('status', 'closed')->get();

        if ($folios->isEmpty()) {
            $this->command->warn('No folios found. Please run FolioSeeder first.');
            return;
        }

        $methods = ['cash', 'card', 'bank_transfer', 'online'];
        $statuses = ['pending', 'paid', 'failed'];

        foreach ($folios->take(25) as $folio) {
            $numberOfPayments = rand(1, 3);
            $remainingBalance = $folio->balance;

            for ($i = 0; $i < $numberOfPayments; $i++) {
                if ($remainingBalance <= 0) {
                    break;
                }

                if ($i === $numberOfPayments - 1) {
                    $amount = $remainingBalance;
                } else {
                    $amount = rand(1000, (int)($remainingBalance * 100 * 0.6)) / 100;
                    $remainingBalance -= $amount;
                }

                $payment = Payment::create([
                    'reference_type' => 'folio',
                    'reference_id' => $folio->id,
                    'folio_id' => $folio->id,
                    'amount' => $amount,
                    'method' => $methods[array_rand($methods)],
                    'status' => $statuses[array_rand($statuses)],
                    'is_partial' => true,
                    'is_advance' => false,
                    'remaining_balance' => $remainingBalance,
                    'notes' => rand(0, 1) ? 'Partial payment received' : null,
                ]);

                // Update folio paid amount
                $folio->paid += $amount;
                $folio->balance = $folio->total - $folio->paid;
                $folio->save();
            }
        }

        $this->command->info('Created ' . Payment::where('is_partial', true)->count() . ' partial payments.');
    }
}
