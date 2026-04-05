<?php

use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('training weekly schedule form contains training session dropdown', function () {
    $formFile = app_path('Filament/Resources/TrainingWeeklySchedules/Schemas/TrainingWeeklyScheduleForm.php');

    expect(file_get_contents($formFile))
        ->toContain("Select::make('training_session_id')")
        ->toContain("->relationship('trainingSession', 'name')")
        ->toContain("'monday' => 'Mandag'")
        ->toContain("'sunday' => 'Soendag'")
        ->toContain("TimePicker::make('start_time')")
        ->toContain("TimePicker::make('end_time')")
        ->toContain('->seconds(false)');
});

test('training weekly schedule table formats day of week in danish', function () {
    $tableFile = app_path('Filament/Resources/TrainingWeeklySchedules/Tables/TrainingWeeklySchedulesTable.php');

    expect(file_get_contents($tableFile))
        ->toContain("'monday' => 'Mandag'")
        ->toContain("'sunday' => 'Soendag'");
});

test('training weekly schedule belongs to a training session', function () {
    $trainingSession = TrainingSession::create([
        'name' => 'Teknik hold',
    ]);

    $schedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => 'monday',
        'start_time' => '17:00:00',
        'end_time' => '18:30:00',
    ]);

    expect($schedule->trainingSession->is($trainingSession))->toBeTrue();
});