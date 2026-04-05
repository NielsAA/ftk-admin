<?php

test('members filament table shows the related user name column', function () {
    $membersTableFile = app_path('Filament/Resources/Members/Tables/MembersTable.php');

    expect(file_get_contents($membersTableFile))
        ->toContain("TextColumn::make('user.name')")
        ->toContain("->label('Bruger')")
        ->toContain("TextColumn::make('teams')")
        ->toContain("->label('Hold')")
        ->toContain("BulkAction::make('enrollInTeam')")
        ->toContain("Select::make('team_id')")
        ->toContain('->badge()')
        ->toContain('->searchable()')
        ->not->toContain("TextColumn::make('user_id')");
});