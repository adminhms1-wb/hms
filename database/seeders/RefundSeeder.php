<?php

namespace Database\Seeders;

use App\Models\Refund;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Folio;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RefundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = Payment::all();
        $reservations = Reservation::all();
        $folios = Folio::all();

        if ($payments->isEmpty() && $reservations->isEmpty() && $folios->isEmpty()) {
            $this->command->warn('No payments, reservations, or folios found. Please run other seeders first.');
            return;
        }

        $refundMethods = ['original_method', 'cash', 'card', 'bank_transfer'];
        $statuses = ['pending', 'approved', 'processed', 'completed', 'cancelled'];
        $reasons = [
            'Cancellation request',
            'Service not provided',
            'Overpayment',
            'Guest dissatisfaction',
            'Double charge',
            'Booking error',
        ];

        for ($i = 0; $i < 15; $i++) {
            $payment = $payments->isNotEmpty() ? $payments->random() : null;
            $reservation = $reservations->isNotEmpty() ? $reservations->random() : null;
            $folio = $folios->isNotEmpty() ? $folios->random() : null;

            $refundAmount = $payment ? rand(1000, (int)($payment->amount * 100)) / 100 : rand(1000, 50000) / 100;

            $refund = Refund::create([
                'payment_id' => $payment ? $payment->id : null,
                'reservation_id' => $reservation ? $reservation->id : null,
                'folio_id' => $folio ? $folio->id : null,
                'reference_number' => 'REF-' . strtoupper(uniqid()),
                'refund_amount' => $refundAmount,
                'refund_method' => $refundMethods[array_rand($refundMethods)],
                'status' => $statuses[array_rand($statuses)],
                'reason' => $reasons[array_rand($reasons)],
                'refund_date' => Carbon::now()->subDays(rand(0, 30))->toDateString(),
                'processed_date' => rand(0, 1) ? Carbon::now()->subDays(rand(0, 20))->toDateString() : null,
                'notes' => rand(0, 1) ? 'Refund processed as requested' : null,
            ]);
        }

        $this->command->info('Created ' . Refund::count() . ' refunds.');
    }
}
