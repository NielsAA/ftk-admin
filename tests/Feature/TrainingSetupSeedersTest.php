<?php

use App\Models\Team;
use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use Database\Seeders\TeamSeeder;
use Database\Seeders\TrainingSessionSeeder;
use Database\Seeders\TrainingWeeklyScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('training weekly schedule seeder seeds the current schedules', function () {
    $this->seed(TrainingSessionSeeder::class);
    $this->seed(TrainingWeeklyScheduleSeeder::class);

    expect(TrainingWeeklySchedule::query()->count())->toBe(14);

    $this->assertDatabaseHas('training_weekly_schedules', [
        'day_of_week' => 'monday',
        'start_time' => '18:00:00',
        'end_time' => '19:30:00',
        'training_session_id' => TrainingSession::query()->where('name', 'MMA Basis')->value('id'),
    ]);

    $this->assertDatabaseHas('training_weekly_schedules', [
        'day_of_week' => 'saturday',
        'start_time' => '13:00:00',
        'end_time' => '17:00:00',
        'training_session_id' => TrainingSession::query()->where('name', 'Open Mat')->value('id'),
    ]);
});

test('team seeder seeds teams and training session links', function () {
    $this->seed(TrainingSessionSeeder::class);
    $this->seed(TeamSeeder::class);

    expect(Team::query()->count())->toBe(2);

    $mmaKamphold = Team::query()->where('name', 'MMA Kamphold')->firstOrFail();

    expect($mmaKamphold->trainingSessions()->pluck('name')->all())
        ->toEqualCanonicalizing(['MMA Kamphold', 'Fristils brydning', 'Open Mat']);

    $this->assertDatabaseHas('teams', [
        'name' => 'MMA Basis',
        'number' => '06',
        'price' => '250.00',
    ]);
});