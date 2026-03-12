<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $frontDeskRole = Role::where('slug', 'front_desk')->first();
        $restaurantManagerRole = Role::where('slug', 'restaurant_manager')->first();
        $roomServiceRole = Role::where('slug', 'room_service')->first();
        $housekeepingRole = Role::where('slug', 'housekeeping')->first();
        $financeRole = Role::where('slug', 'finance')->first();
        $inventoryManagerRole = Role::where('slug', 'inventory_manager')->first();
        $systemAdminRole = Role::where('slug', 'system_admin')->first();

        // Super Admin Users
        User::create([
            'name' => 'John Smith',
            'email' => 'superadmin@hotel.com',
            'mobile_number' => '+1234567890',
            'password' => Hash::make('password123'),
            'role_id' => $superAdminRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Sarah Johnson',
            'email' => 'owner@hotel.com',
            'mobile_number' => '+1234567891',
            'password' => Hash::make('password123'),
            'role_id' => $superAdminRole->id,
            'status' => 'active',
        ]);

        // Front Desk / Reception Users
        User::create([
            'name' => 'Emily Davis',
            'email' => 'frontdesk1@hotel.com',
            'mobile_number' => '+1234567892',
            'password' => Hash::make('password123'),
            'role_id' => $frontDeskRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Michael Brown',
            'email' => 'frontdesk2@hotel.com',
            'mobile_number' => '+1234567893',
            'password' => Hash::make('password123'),
            'role_id' => $frontDeskRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Jessica Wilson',
            'email' => 'reception@hotel.com',
            'mobile_number' => '+1234567894',
            'password' => Hash::make('password123'),
            'role_id' => $frontDeskRole->id,
            'status' => 'active',
        ]);

        // Restaurant Manager Users
        User::create([
            'name' => 'David Martinez',
            'email' => 'restaurant.manager@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $restaurantManagerRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Lisa Anderson',
            'email' => 'restaurant@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $restaurantManagerRole->id,
            'status' => 'active',
        ]);

        // Room Service Staff
        User::create([
            'name' => 'Robert Taylor',
            'email' => 'roomservice1@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $roomServiceRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Amanda White',
            'email' => 'roomservice2@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $roomServiceRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'James Harris',
            'email' => 'roomservice3@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $roomServiceRole->id,
            'status' => 'active',
        ]);

        // Housekeeping Staff
        User::create([
            'name' => 'Maria Garcia',
            'email' => 'housekeeping1@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $housekeepingRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Thomas Lee',
            'email' => 'housekeeping2@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $housekeepingRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Patricia Moore',
            'email' => 'housekeeping3@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $housekeepingRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Christopher Clark',
            'email' => 'housekeeping4@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $housekeepingRole->id,
            'status' => 'active',
        ]);

        // Finance / Accounts Users
        User::create([
            'name' => 'Jennifer Lewis',
            'email' => 'finance1@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $financeRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Daniel Walker',
            'email' => 'accounts@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $financeRole->id,
            'status' => 'active',
        ]);

        // Inventory Manager Users
        User::create([
            'name' => 'Nancy Hall',
            'email' => 'inventory@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $inventoryManagerRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Mark Allen',
            'email' => 'inventory.manager@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $inventoryManagerRole->id,
            'status' => 'active',
        ]);

        // System Admin Users
        User::create([
            'name' => 'Steven Young',
            'email' => 'systemadmin@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $systemAdminRole->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Karen King',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('password123'),
            'role_id' => $systemAdminRole->id,
            'status' => 'active',
        ]);
    }
}

