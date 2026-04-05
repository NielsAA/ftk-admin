<?php

test('member filament form displays payment fields as read-only', function () {
    $memberFormFile = app_path('Filament/Resources/Members/Schemas/MemberForm.php');

    expect(file_get_contents($memberFormFile))
        ->toContain("TextInput::make('stripe_id')")
        ->toContain("TextInput::make('pm_type')")
        ->toContain("TextInput::make('pm_last_four')")
        ->toContain("DateTimePicker::make('trial_ends_at')")
        ->toContain("TextInput::make('stripe_id')\n                            ->default(null)\n                            ->disabled()")
        ->toContain("TextInput::make('pm_type')\n                            ->default(null)\n                            ->disabled()")
        ->toContain("TextInput::make('pm_last_four')\n                            ->default(null)\n                            ->disabled()")
        ->toContain("DateTimePicker::make('trial_ends_at')\n                            ->disabled()");
});

test('member filament form keeps selected team ids in form state', function () {
    $memberFormFile = app_path('Filament/Resources/Members/Schemas/MemberForm.php');

    expect(file_get_contents($memberFormFile))
        ->toContain("Select::make('team_ids')")
        ->toContain('->multiple()')
        ->toContain('->default([])')
        ->not->toContain('->dehydrated(false)');
});

test('member filament form no longer renders inline member history section', function () {
    $memberFormFile = app_path('Filament/Resources/Members/Schemas/MemberForm.php');

    expect(file_get_contents($memberFormFile))
        ->not->toContain("Section::make('Medlemshistorik')")
        ->not->toContain("Placeholder::make('member_team_history')")
        ->not->toContain('renderMemberTeamHistoryTable');
});
