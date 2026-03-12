<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
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

        $categories = [
            'Cleaning Supplies',
            'Food & Beverage',
            'Maintenance',
            'Utilities',
            'Office Supplies',
            'Marketing',
            'Insurance',
            'Legal',
            'Training',
            'Equipment',
        ];

        $paymentMethods = ['cash', 'card', 'bank_transfer', 'cheque'];
        $statuses = ['pending', 'paid', 'approved'];

        for ($i = 0; $i < 40; $i++) {
            $supplier = rand(0, 1) ? $suppliers->random() : null;
            $category = $categories[array_rand($categories)];

            Expense::create([
                'title' => $category . ' - ' . ($i + 1),
                'category' => $category,
                'supplier_id' => $supplier ? $supplier->id : null,
                'amount' => rand(1000, 50000) / 100, // $10 to $500
                'date' => Carbon::now()->subDays(rand(0, 60))->toDateString(),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'status' => $statuses[array_rand($statuses)],
                'notes' => rand(0, 1) ? 'Expense for ' . strtolower($category) : null,
            ]);
        }

        $this->command->info('Created ' . Expense::count() . ' expenses.');
    }
}
