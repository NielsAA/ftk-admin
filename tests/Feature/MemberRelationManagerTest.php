<?php

test('member resource registers relation manager for member team history', function () {
    $memberResourceFile = app_path('Filament/Resources/Members/MemberResource.php');
    $relationManagerFile = app_path('Filament/Resources/Members/RelationManagers/MemberOfTeamsRelationManager.php');

    expect(file_get_contents($memberResourceFile))
        ->toContain('MemberOfTeamsRelationManager::class');

    expect(file_get_contents($relationManagerFile))
        ->toContain('protected static string $relationship = \'memberOfTeams\';')
        ->toContain('protected static ?string $title = \'Medlemshistorik\';')
        ->toContain("TextColumn::make('team.name')")
        ->toContain("TextColumn::make('memberTeamFunction.name')")
        ->toContain("TextColumn::make('joined_at')")
        ->toContain("TextColumn::make('left_at')");
});
