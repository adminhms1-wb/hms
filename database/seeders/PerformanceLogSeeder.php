<?php

namespace Database\Seeders;

use App\Models\PerformanceLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PerformanceLogSeeder extends Seeder
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

        $categories = ['attendance', 'task_completion', 'customer_service', 'teamwork', 'punctuality', 'other'];
        $types = ['positive', 'negative', 'neutral'];
        
        $positiveTitles = [
            'Excellent customer service',
            'Outstanding task completion',
            'Great teamwork',
            'Perfect attendance record',
            'Exceptional punctuality',
            'Outstanding performance',
            'Going above and beyond',
            'Excellent communication skills',
            'Great problem-solving',
            'Outstanding dedication'
        ];

        $negativeTitles = [
            'Attendance issues',
            'Task completion delays',
            'Customer service concerns',
            'Teamwork improvement needed',
            'Punctuality issues',
            'Performance concerns',
            'Need for improvement',
            'Communication issues',
            'Work quality concerns',
            'Behavioral concerns'
        ];

        $neutralTitles = [
            'Monthly performance review',
            'Quarterly assessment',
            'Regular check-in',
            'Performance update',
            'Standard review',
            'Routine evaluation',
            'Progress check',
            'Status update',
            'General feedback',
            'Performance note'
        ];

        $logs = [];

        // Create performance logs for the past 6 months
        for ($month = 0; $month < 6; $month++) {
            $reviewDate = Carbon::now()->subMonths($month)->subDays(rand(0, 28));
            $staffCount = rand(3, min(8, $users->count()));
            $selectedStaff = $users->random($staffCount);
            $reviewers = $users->where('id', '!=', $selectedStaff->pluck('id')->toArray())->random(min(3, $users->count() - $staffCount));

            foreach ($selectedStaff as $staff) {
                $type = $types[array_rand($types)];
                $category = $categories[array_rand($categories)];
                $rating = $type === 'positive' ? rand(4, 5) : ($type === 'negative' ? rand(1, 2) : rand(3, 4));
                $reviewer = $reviewers->random();

                // Select title based on type
                if ($type === 'positive') {
                    $title = $positiveTitles[array_rand($positiveTitles)];
                    $description = 'Staff member has shown excellent performance in ' . $category . '. Keep up the great work!';
                } elseif ($type === 'negative') {
                    $title = $negativeTitles[array_rand($negativeTitles)];
                    $description = 'Staff member needs improvement in ' . $category . '. Please address the following concerns.';
                } else {
                    $title = $neutralTitles[array_rand($neutralTitles)];
                    $description = 'Regular performance review for ' . $category . '. Overall performance is satisfactory.';
                }

                $logs[] = [
                    'staff_id' => $staff->id,
                    'reviewed_by' => $reviewer->id,
                    'review_date' => $reviewDate->format('Y-m-d'),
                    'category' => $category,
                    'title' => $title,
                    'description' => $description,
                    'rating' => $rating,
                    'type' => $type,
                    'action_items' => $type === 'negative' ? '1. Address the concerns mentioned above\n2. Schedule follow-up meeting\n3. Provide additional training if needed' : null,
                    'created_at' => $reviewDate,
                    'updated_at' => $reviewDate,
                ];
            }
        }

        foreach ($logs as $log) {
            PerformanceLog::create($log);
        }

        $this->command->info('Performance logs seeded successfully!');
    }
}
