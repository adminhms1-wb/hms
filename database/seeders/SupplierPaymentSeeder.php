<?php

namespace Database\Seeders;

use App\Models\SupplierPayment;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SupplierPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::all();

        if ($suppliers->isEmpty()) {
            $this->command->warn('No suppliers found. Please run SupplierSeeder first.');
            return;
        }

        $paymentMethods = ['cash', 'card', 'bank_transfer', 'cheque'];
        $statuses = ['pending', 'paid', 'partial', 'overdue'];

        for ($i = 0; $i < 30; $i++) {
            $supplier = $suppliers->random();
            $amount = rand(5000, 100000) / 100; // $50 to $1000
            $paymentDate = Carbon::now()->subDays(rand(0, 60))->toDateString();
            $dueDate = Carbon::parse($paymentDate)->addDays(rand(15, 45))->toDateString();

            SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'invoice_number' => 'INV-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'amount' => $amount,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'status' => $statuses[array_rand($statuses)],
                'payment_date' => $paymentDate,
                'due_date' => $dueDate,
                'notes' => rand(0, 1) ? 'Payment for supplies' : null,
            ]);
        }

        $this->command->info('Created ' . SupplierPayment::count() . ' supplier payments.');
    }
}
