<?php

test('member team functions table contains expected columns', function () {
    $tableFile = app_path('Filament/Resources/MemberTeamFunctions/Tables/MemberTeamFunctionsTable.php');

    expect(file_get_contents($tableFile))
        ->toContain("TextColumn::make('name')")
        ->toContain("TextColumn::make('description')")
        ->toContain("ToggleColumn::make('default_member_function')");
});
