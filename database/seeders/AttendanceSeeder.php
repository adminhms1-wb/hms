<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ShiftSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $shiftSchedules = ShiftSchedule::where('schedule_date', '<=', Carbon::now()->format('Y-m-d'))
            ->where('schedule_date', '>=', Carbon::now()->subDays(14)->format('Y-m-d'))
            ->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $statuses = ['present', 'absent', 'late', 'half_day', 'on_leave'];
        $checkInTypes = ['manual', 'biometric'];
        $attendanceRecords = [];

        // Create attendance for past 14 days
        for ($day = 0; $day < 14; $day++) {
            $attendanceDate = Carbon::now()->subDays($day);
            $dayOfWeek = $attendanceDate->dayOfWeek;

            // Skip weekends for some staff (optional)
            $staffToProcess = $users->random(rand(5, min(10, $users->count())));

            foreach ($staffToProcess as $staff) {
                // Check table structure - old table uses 'date', new uses 'attendance_date'
                $dateColumn = \Illuminate\Support\Facades\Schema::hasColumn('attendance', 'attendance_date') ? 'attendance_date' : 'date';
                
                // Skip if attendance already exists
                $exists = \Illuminate\Support\Facades\DB::table('attendance')
                    ->where('staff_id', $staff->id)
                    ->where($dateColumn, $attendanceDate->format('Y-m-d'))
                    ->whereNull('deleted_at')
                    ->exists();
                    
                if ($exists) {
                    continue;
                }

                // Find related shift schedule
                $shiftSchedule = $shiftSchedules->where('staff_id', $staff->id)
                    ->where('schedule_date', $attendanceDate->format('Y-m-d'))
                    ->first();

                // Old table uses different status values
                $oldStatuses = ['present', 'absent', 'leave', 'late'];
                $newStatuses = ['present', 'absent', 'late', 'half_day', 'on_leave'];
                
                // Check which status values are available
                $availableStatuses = \Illuminate\Support\Facades\Schema::hasColumn('attendance', 'check_in_time') 
                    ? $newStatuses 
                    : $oldStatuses;
                    
                $status = $availableStatuses[array_rand($availableStatuses)];
                
                // Build attendance record based on table structure
                $attendanceRecord = [
                    'staff_id' => $staff->id,
                ];
                
                // Add date column (old uses 'date', new uses 'attendance_date')
                if ($dateColumn === 'attendance_date') {
                    $attendanceRecord['attendance_date'] = $attendanceDate->format('Y-m-d');
                    $attendanceRecord['shift_schedule_id'] = $shiftSchedule ? $shiftSchedule->id : null;
                    
                    // Generate check-in/out times based on status (new structure)
                    $checkInTime = null;
                    $checkOutTime = null;
                    
                    if ($status === 'present') {
                        $checkInTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . ($shiftSchedule ? $shiftSchedule->start_time : '09:00:00'))
                            ->addMinutes(rand(-15, 30))->format('H:i:s');
                        $checkOutTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . ($shiftSchedule ? $shiftSchedule->end_time : '17:00:00'))
                            ->addMinutes(rand(-30, 15))->format('H:i:s');
                    } elseif ($status === 'late') {
                        $checkInTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . ($shiftSchedule ? $shiftSchedule->start_time : '09:00:00'))
                            ->addMinutes(rand(30, 120))->format('H:i:s');
                        $checkOutTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . ($shiftSchedule ? $shiftSchedule->end_time : '17:00:00'))
                            ->addMinutes(rand(-30, 15))->format('H:i:s');
                    } elseif ($status === 'half_day') {
                        $checkInTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . ($shiftSchedule ? $shiftSchedule->start_time : '09:00:00'))
                            ->addMinutes(rand(-15, 30))->format('H:i:s');
                        $checkOutTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' 13:00:00')
                            ->addMinutes(rand(-30, 15))->format('H:i:s');
                    }
                    
                    $attendanceRecord['check_in_time'] = $checkInTime;
                    $attendanceRecord['check_out_time'] = $checkOutTime;
                    $attendanceRecord['check_in_type'] = $checkInTypes[array_rand($checkInTypes)];
                } else {
                    // Old structure
                    $attendanceRecord['date'] = $attendanceDate->format('Y-m-d');
                }
                
                $attendanceRecord['status'] = $status;
                
                // Add notes only if column exists
                if (Schema::hasColumn('attendance', 'notes')) {
                    $attendanceRecord['notes'] = rand(0, 1) ? 'Regular attendance' : null;
                }
                
                $attendanceRecord['created_at'] = now();
                $attendanceRecord['updated_at'] = now();

                $attendanceRecords[] = $attendanceRecord;
            }
        }

        foreach ($attendanceRecords as $record) {
            Attendance::create($record);
        }

        $this->command->info('Attendance records seeded successfully!');
    }
}
