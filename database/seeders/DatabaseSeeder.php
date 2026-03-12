<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Seed Room Management and Inventory data
        $this->call([
            AmenitySeeder::class,
            RoomTypeSeeder::class,
            RoomSeeder::class,
            SeasonalPriceSeeder::class,
            PricingRuleSeeder::class,
            RoomTypeTimeSeeder::class,
            ReservationSeeder::class,
            InventoryStoreItemSeeder::class,
            LinenHousekeepingItemSeeder::class,
            AmenitiesConsumableItemSeeder::class,
            InventoryItemSeeder::class,
            InventoryTransactionSeeder::class,
            SupplierSeeder::class,
            StaffProfileSeeder::class,
            ShiftSeeder::class,
            ShiftScheduleSeeder::class,
            AttendanceSeeder::class,
            TaskAssignmentSeeder::class,
            PerformanceLogSeeder::class,
            // Billing & Finance seeders
            FolioSeeder::class,
            SplitBillingSeeder::class,
            AdvancePaymentSeeder::class,
            RefundSeeder::class,
            PartialPaymentSeeder::class,
            SupplierPaymentSeeder::class,
            DailyCashClosingSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
