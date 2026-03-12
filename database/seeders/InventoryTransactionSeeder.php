<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Database\Seeder;

class InventoryTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have items to attach transactions to
        $items = InventoryItem::all();

        if ($items->isEmpty()) {
            return;
        }

        $transactions = [];

        foreach ($items as $item) {
            // Seed some stock in transactions
            $transactions[] = [
                'item_id' => $item->id,
                'type' => 'stock_in',
                'qty' => rand(10, 50),
                'reference' => 'Initial purchase',
                'created_at' => now()->subDays(rand(20, 40)),
                'updated_at' => now()->subDays(rand(20, 40)),
            ];

            // Seed some stock out transactions
            $transactions[] = [
                'item_id' => $item->id,
                'type' => 'stock_out',
                'qty' => rand(5, 30),
                'reference' => 'Usage / consumption',
                'created_at' => now()->subDays(rand(5, 19)),
                'updated_at' => now()->subDays(rand(5, 19)),
            ];
        }

        foreach ($transactions as $tx) {
            InventoryTransaction::create([
                'item_id' => $tx['item_id'],
                'type' => $tx['type'],
                'qty' => $tx['qty'],
                'reference' => $tx['reference'],
                'created_at' => $tx['created_at'],
                'updated_at' => $tx['updated_at'],
            ]);
        }
    }
}

