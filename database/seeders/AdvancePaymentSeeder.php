<?php

namespace Database\Seeders;

use App\Models\AdvancePayment;
use App\Models\Reservation;
use App\Models\Guest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AdvancePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservations = Reservation::all();
        $guests = Guest::all();

        if ($guests->isEmpty()) {
            $this->command->warn('No guests found. Please run ReservationSeeder first.');
            return;
        }

        $paymentMethods = ['cash', 'card', 'bank_transfer', 'online', 'cheque'];
        $statuses = ['pending', 'confirmed', 'applied', 'refunded'];

        for ($i = 0; $i < 20; $i++) {
            $guest = $guests->random();
            $reservation = $reservations->isNotEmpty() ? $reservations->random() : null;

            AdvancePayment::create([
                'reservation_id' => $reservation ? $reservation->id : null,
                'guest_id' => $guest->id,
                'reference_number' => 'ADV-' . strtoupper(uniqid()),
                'amount' => rand(5000, 50000) / 100, // $50 to $500
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'status' => $statuses[array_rand($statuses)],
                'payment_date' => Carbon::now()->subDays(rand(0, 60))->toDateString(),
                'notes' => rand(0, 1) ? 'Advance payment for reservation' : null,
            ]);
        }

        $this->command->info('Created ' . AdvancePayment::count() . ' advance payments.');
    }
}
