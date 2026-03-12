<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftScheduleSeeder extends Seeder
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

        // Check if shift_schedules table exists
        if (!Schema::hasTable('shift_schedules')) {
            $this->command->warn('shift_schedules table does not exist. Please run migrations first.');
            return;
        }

        // Create shift templates first if they don't exist
        $shiftTemplates = [
            ['name' => 'Morning Shift', 'start_time' => '06:00:00', 'end_time' => '14:00:00', 'description' => 'Morning shift from 6 AM to 2 PM', 'is_active' => true],
            ['name' => 'Afternoon Shift', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'description' => 'Afternoon shift from 2 PM to 10 PM', 'is_active' => true],
            ['name' => 'Night Shift', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'description' => 'Night shift from 10 PM to 6 AM', 'is_active' => true],
        ];

        // Get shifts from database (should be created by ShiftSeeder)
        $shifts = Shift::all();
        
        if ($shifts->isEmpty()) {
            $this->command->warn('No shifts found. Please run ShiftSeeder first.');
            return;
        }

        // If we still don't have shifts, create schedules without shift_id (using direct times)
        $statuses = ['scheduled', 'completed', 'cancelled', 'absent'];
        $schedules = [];

        // Create schedules for the next 30 days
        for ($day = 0; $day < 30; $day++) {
            $scheduleDate = Carbon::now()->addDays($day);

            // Assign shifts to random staff members
            $staffCount = rand(3, min(8, $users->count()));
            $selectedStaff = $users->random($staffCount);

            foreach ($selectedStaff as $staff) {
                // Skip if schedule already exists
                if (ShiftSchedule::where('staff_id', $staff->id)
                    ->where('schedule_date', $scheduleDate->format('Y-m-d'))
                    ->exists()) {
                    continue;
                }

                // Get shift for this staff member (if old structure) or random shift (if new structure)
                if (Schema::hasColumn('shifts', 'staff_id')) {
                    // Old structure - find shift for this staff member
                    $shift = $shifts->where('staff_id', $staff->id)->first();
                    if (!$shift) {
                        // If no shift for this staff, use a random one
                        $shift = $shifts->random();
                    }
                } else {
                    // New structure - use random shift template
                    $shift = $shifts->random();
                }

                $status = $day < 7 ? ($day < 2 ? 'completed' : 'scheduled') : 'scheduled';

                $shiftId = $shift->id;
                $startTime = $shift->start_time;
                $endTime = $shift->end_time;

                // Build schedule data
                $scheduleData = [
                    'staff_id' => $staff->id,
                    'shift_id' => $shiftId,
                    'schedule_date' => $scheduleDate->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Add notes if column exists
                if (Schema::hasColumn('shift_schedules', 'notes')) {
                    $scheduleData['notes'] = rand(0, 1) ? 'Regular schedule' : null;
                }
                
                $schedules[] = $scheduleData;
            }
        }

        foreach ($schedules as $schedule) {
            try {
                ShiftSchedule::create($schedule);
            } catch (\Exception $e) {
                // Skip if there's an error (e.g., unique constraint)
                continue;
            }
        }

        $this->command->info('Shift schedules seeded successfully!');
    }
}
