<?php

test('ekstra traing form uses a training session dropdown', function () {
    $formFile = app_path('Filament/Resources/EkstraTraings/Schemas/EkstraTraingForm.php');

    expect(file_get_contents($formFile))
        ->toContain("Select::make('training_session_id')")
        ->toContain("->relationship('trainingSession', 'name'")
        ->toContain('->searchable()')
        ->toContain('->preload()')
        ->not->toContain("TextInput::make('training_session_id')");
});

    test('ekstra traing table formats date as yyyy.mm.dd', function () {
        $tableFile = app_path('Filament/Resources/EkstraTraings/Tables/EkstraTraingsTable.php');

        expect(file_get_contents($tableFile))
        ->toContain("TextColumn::make('date')")
        ->toContain("->date('Y.m.d')")
        ->toContain("TextColumn::make('start_time')")
        ->toContain("->time('H:i')")
        ->toContain("TextColumn::make('end_time')");
    });
