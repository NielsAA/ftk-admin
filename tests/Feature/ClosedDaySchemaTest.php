<?php

test('closed day form uses date dependent training weekly schedule select', function () {
    $formFile = app_path('Filament/Resources/ClosedDays/Schemas/ClosedDayForm.php');

    expect(file_get_contents($formFile))
        ->toContain("DatePicker::make('date')")
        ->toContain('->live()')
        ->toContain('->afterStateUpdated(fn (Set $set): mixed => $set(\'training_weekly_schedule_id\', null))')
        ->toContain("Select::make('training_weekly_schedule_id')")
        ->toContain('->disabled(fn (Get $get): bool => blank($get(\'date\')))')
        ->toContain('->where(\'day_of_week\', $dayOfWeek)')
        ->toContain('->with(\'trainingSession\')');
});

test('closed day table shows training session relation instead of raw foreign key', function () {
    $tableFile = app_path('Filament/Resources/ClosedDays/Tables/ClosedDaysTable.php');

    expect(file_get_contents($tableFile))
        ->toContain("TextColumn::make('trainingWeeklySchedule.trainingSession.name')")
        ->toContain("->label('Traeningssession')")
        ->not->toContain("TextColumn::make('training_weekly_schedule_id')");
});
