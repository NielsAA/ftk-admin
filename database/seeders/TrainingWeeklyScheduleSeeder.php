<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingWeeklyScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['training_session_name' => 'Fristils brydning', 'day_of_week' => 'wednesday', 'start_time' => '16:30:00', 'end_time' => '19:30:00', 'description' => null],
            ['training_session_name' => 'Fristils brydning', 'day_of_week' => 'friday', 'start_time' => '17:00:00', 'end_time' => '19:30:00', 'description' => null],
            ['training_session_name' => 'MMA Kamphold', 'day_of_week' => 'monday', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'description' => null],
            ['training_session_name' => 'MMA Kamphold', 'day_of_week' => 'wednesday', 'start_time' => '19:30:00', 'end_time' => '21:30:00', 'description' => null],
            ['training_session_name' => 'MMA Kamphold', 'day_of_week' => 'friday', 'start_time' => '19:30:00', 'end_time' => '22:00:00', 'description' => null],
            ['training_session_name' => 'Grapling', 'day_of_week' => 'tuesday', 'start_time' => '20:00:00', 'end_time' => '22:00:00', 'description' => null],
            ['training_session_name' => 'Grapling', 'day_of_week' => 'thursday', 'start_time' => '20:00:00', 'end_time' => '22:00:00', 'description' => null],
            ['training_session_name' => 'Brydning, drenge/ungdom', 'day_of_week' => 'tuesday', 'start_time' => '18:30:00', 'end_time' => '20:00:00', 'description' => null],
            ['training_session_name' => 'Brydning, drenge/ungdom', 'day_of_week' => 'thursday', 'start_time' => '18:30:00', 'end_time' => '20:00:00', 'description' => null],
            ['training_session_name' => 'Brydning, puslinge', 'day_of_week' => 'tuesday', 'start_time' => '17:30:00', 'end_time' => '18:30:00', 'description' => null],
            ['training_session_name' => 'Brydning, puslinge', 'day_of_week' => 'thursday', 'start_time' => '17:30:00', 'end_time' => '18:30:00', 'description' => null],
            ['training_session_name' => 'Nina Kids', 'day_of_week' => 'tuesday', 'start_time' => '16:30:00', 'end_time' => '17:30:00', 'description' => null],
            ['training_session_name' => 'MMA Basis', 'day_of_week' => 'monday', 'start_time' => '18:00:00', 'end_time' => '19:30:00', 'description' => null],
            ['training_session_name' => 'Open Mat', 'day_of_week' => 'saturday', 'start_time' => '13:00:00', 'end_time' => '17:00:00', 'description' => null],
        ])->each(function (array $schedule): void {
            $trainingSessionId = DB::table('training_sessions')
                ->where('name', $schedule['training_session_name'])
                ->value('id');

            if ($trainingSessionId === null) {
                return;
            }

            DB::table('training_weekly_schedules')->updateOrInsert(
                [
                    'training_session_id' => $trainingSessionId,
                    'day_of_week' => $schedule['day_of_week'],
                    'start_time' => $schedule['start_time'],
                ],
                [
                    'end_time' => $schedule['end_time'],
                    'description' => $schedule['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        });
    }
}