<?php

namespace Database\Seeders;

use App\Models\StaffProfile;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StaffProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $roles = Role::all();

        if ($users->isEmpty() || $roles->isEmpty()) {
            $this->command->warn('No users or roles found. Please run UserSeeder and RolePermissionSeeder first.');
            return;
        }

        $employeeIdCounter = 1000;
        $genders = ['male', 'female', 'other'];
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego'];

        foreach ($users as $user) {
            // Skip if profile already exists
            if (StaffProfile::where('user_id', $user->id)->exists()) {
                continue;
            }

            $role = $roles->random();
            $gender = $genders[array_rand($genders)];
            $hireDate = Carbon::now()->subMonths(rand(1, 24))->subDays(rand(1, 30));

            StaffProfile::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'employee_id' => 'EMP-' . str_pad($employeeIdCounter++, 5, '0', STR_PAD_LEFT),
                'phone' => '+1' . rand(2000000000, 9999999999),
                'address' => rand(100, 9999) . ' ' . ['Main St', 'Oak Ave', 'Park Blvd', 'First St', 'Second Ave'][array_rand(['Main St', 'Oak Ave', 'Park Blvd', 'First St', 'Second Ave'])] . ', ' . $cities[array_rand($cities)],
                'hire_date' => $hireDate,
                'birth_date' => Carbon::now()->subYears(rand(25, 55))->subDays(rand(1, 365)),
                'gender' => $gender,
                'emergency_contact_name' => ['John Doe', 'Jane Smith', 'Robert Johnson', 'Mary Williams'][array_rand(['John Doe', 'Jane Smith', 'Robert Johnson', 'Mary Williams'])],
                'emergency_contact_phone' => '+1' . rand(2000000000, 9999999999),
                'notes' => rand(0, 1) ? 'Reliable staff member' : null,
            ]);
        }

        $this->command->info('Staff profiles seeded successfully!');
    }
}
