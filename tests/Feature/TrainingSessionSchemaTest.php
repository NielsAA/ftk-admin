<?php

use Illuminate\Support\Facades\Schema;

test('training session form contains color picker', function () {
    $formFile = app_path('Filament/Resources/TrainingSessions/Schemas/TrainingSessionForm.php');

    expect(file_get_contents($formFile))
        ->toContain("ColorPicker::make('color')");
});

test('training session form contains number of trials field', function () {
    $formFile = app_path('Filament/Resources/TrainingSessions/Schemas/TrainingSessionForm.php');

    expect(file_get_contents($formFile))
        ->toContain("TextInput::make('number_of_trials')");
});

test('training sessions table contains color column', function () {
    $tableFile = app_path('Filament/Resources/TrainingSessions/Tables/TrainingSessionsTable.php');

    expect(file_get_contents($tableFile))
        ->toContain("ColorColumn::make('color')");
});

test('training sessions table contains number of trials column', function () {
    $tableFile = app_path('Filament/Resources/TrainingSessions/Tables/TrainingSessionsTable.php');

    expect(file_get_contents($tableFile))
        ->toContain("TextColumn::make('number_of_trials')");
});

test('training sessions table has color column', function () {
    expect(Schema::hasColumn('training_sessions', 'color'))->toBeTrue();
});

test('training sessions table has number_of_trials column', function () {
    expect(Schema::hasColumn('training_sessions', 'number_of_trials'))->toBeTrue();
});

test('training weekly schedules table no longer has color column', function () {
    expect(Schema::hasColumn('training_weekly_schedules', 'color'))->toBeFalse();
});