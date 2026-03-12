<?php

namespace Database\Seeders;

use App\Models\DailyCashClosing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DailyCashClosingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $statuses = ['open', 'closed', 'verified'];

        // Create cash closings for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $closingDate = Carbon::now()->subDays($i)->toDateString();
            
            $openingCash = rand(50000, 200000) / 100; // $500 to $2000
            $cashReceived = rand(100000, 500000) / 100; // $1000 to $5000
            $cashPaid = rand(20000, 100000) / 100; // $200 to $1000
            $cardReceived = rand(50000, 300000) / 100; // $500 to $3000
            $bankTransferReceived = rand(30000, 200000) / 100; // $300 to $2000
            $onlineReceived = rand(20000, 150000) / 100; // $200 to $1500
            
            $expectedCash = $openingCash + $cashReceived - $cashPaid;
            $actualCash = $expectedCash + rand(-5000, 5000) / 100; // Small variance
            $difference = $actualCash - $expectedCash;

            DailyCashClosing::create([
                'closing_date' => $closingDate,
                'user_id' => $user->id,
                'opening_cash' => $openingCash,
                'cash_received' => $cashReceived,
                'cash_paid' => $cashPaid,
                'card_received' => $cardReceived,
                'bank_transfer_received' => $bankTransferReceived,
                'online_received' => $onlineReceived,
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'difference' => $difference,
                'status' => $i < 5 ? 'open' : ($statuses[array_rand($statuses)]),
                'notes' => rand(0, 1) ? 'Daily cash closing completed' : null,
            ]);
        }

        $this->command->info('Created ' . DailyCashClosing::count() . ' daily cash closings.');
    }
}
