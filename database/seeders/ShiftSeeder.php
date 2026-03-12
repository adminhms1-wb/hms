<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShiftSeeder extends Seeder
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

        // Check if shifts table exists
        if (!Schema::hasTable('shifts')) {
            $this->command->warn('shifts table does not exist. Please run migrations first.');
            return;
        }

        // Shift templates
        $shiftTemplates = [
            ['name' => 'Morning Shift', 'start_time' => '06:00:00', 'end_time' => '14:00:00', 'description' => 'Morning shift from 6 AM to 2 PM', 'is_active' => true],
            ['name' => 'Afternoon Shift', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'description' => 'Afternoon shift from 2 PM to 10 PM', 'is_active' => true],
            ['name' => 'Night Shift', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'description' => 'Night shift from 10 PM to 6 AM', 'is_active' => true],
        ];

        // Check if shifts table has staff_id (old structure)
        if (Schema::hasColumn('shifts', 'staff_id')) {
            // Old structure - create shifts for each staff member
            $createdCount = 0;
            
            foreach ($users as $user) {
                // Check if shift already exists for this user
                if (Shift::where('staff_id', $user->id)->exists()) {
                    continue;
                }

                // Assign a random shift template to each staff member
                $template = $shiftTemplates[array_rand($shiftTemplates)];
                
                try {
                    Shift::create([
                        'staff_id' => $user->id,
                        'name' => $template['name'] . ' - ' . $user->name,
                        'start_time' => $template['start_time'],
                        'end_time' => $template['end_time'],
                        'description' => $template['description'],
                        'is_active' => $template['is_active'],
                    ]);
                    $createdCount++;
                } catch (\Exception $e) {
                    // Skip if error (e.g., unique constraint)
                    continue;
                }
            }

            $this->command->info("Created {$createdCount} shifts for staff members.");
        } else {
            // New structure - create template shifts without staff_id
            $createdCount = 0;
            
            foreach ($shiftTemplates as $template) {
                // Check if shift already exists
                if (Shift::where('name', $template['name'])->exists()) {
                    continue;
                }

                try {
                    Shift::create($template);
                    $createdCount++;
                } catch (\Exception $e) {
                    // Skip if error
                    continue;
                }
            }

            $this->command->info("Created {$createdCount} shift templates.");
        }
    }
}
