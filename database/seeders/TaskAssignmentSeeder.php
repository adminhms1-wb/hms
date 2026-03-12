<?php

namespace Database\Seeders;

use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $rooms = Room::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $taskTypes = ['housekeeping', 'room_service', 'other'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        
        $housekeepingTasks = [
            'Clean room and make bed',
            'Restock bathroom supplies',
            'Vacuum carpets',
            'Dust furniture',
            'Change bed linens',
            'Clean windows',
            'Sanitize bathroom',
            'Empty trash bins',
            'Clean mirrors',
            'Organize room amenities'
        ];

        $roomServiceTasks = [
            'Deliver breakfast order',
            'Deliver lunch order',
            'Deliver dinner order',
            'Deliver room service items',
            'Set up room service table',
            'Collect room service tray',
            'Deliver special request',
            'Deliver extra towels',
            'Deliver extra pillows',
            'Deliver minibar items'
        ];

        $otherTasks = [
            'Inspect room condition',
            'Fix maintenance issue',
            'Check room amenities',
            'Update room status',
            'Prepare room for VIP guest',
            'Special cleaning request',
            'Room setup for event',
            'Inventory check',
            'Safety inspection',
            'Guest request handling'
        ];

        $tasks = [];

        // Create tasks for the next 30 days
        for ($day = 0; $day < 30; $day++) {
            $assignedDate = Carbon::now()->addDays($day);
            $taskCount = rand(5, 15);

            for ($i = 0; $i < $taskCount; $i++) {
                $taskType = $taskTypes[array_rand($taskTypes)];
                $staff = $users->random();
                $room = ($taskType === 'housekeeping' || $taskType === 'room_service') && $rooms->isNotEmpty() 
                    ? $rooms->random() 
                    : null;

                // Select task title based on type
                if ($taskType === 'housekeeping') {
                    $title = $housekeepingTasks[array_rand($housekeepingTasks)];
                } elseif ($taskType === 'room_service') {
                    $title = $roomServiceTasks[array_rand($roomServiceTasks)];
                } else {
                    $title = $otherTasks[array_rand($otherTasks)];
                }

                $status = $assignedDate->isPast() 
                    ? ($assignedDate->isBefore(Carbon::now()->subDays(2)) ? 'completed' : 'in_progress')
                    : 'pending';

                $priority = $priorities[array_rand($priorities)];
                $dueDate = $assignedDate->copy()->addDays(rand(0, 3));

                $tasks[] = [
                    'staff_id' => $staff->id,
                    'task_type' => $taskType,
                    'title' => $title . ($room ? ' - Room ' . $room->room_number : ''),
                    'description' => 'Task assigned for ' . $assignedDate->format('M d, Y'),
                    'assigned_date' => $assignedDate->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'priority' => $priority,
                    'status' => $status,
                    'room_id' => $room ? $room->id : null,
                    'notes' => rand(0, 1) ? 'Please complete on time' : null,
                    'completed_at' => $status === 'completed' ? Carbon::now()->subDays(rand(0, 2)) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($tasks as $task) {
            TaskAssignment::create($task);
        }

        $this->command->info('Task assignments seeded successfully!');
    }
}
