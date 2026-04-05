<?php

test('user filament form maps role enum cases to select options', function () {
    $userFormFile = app_path('Filament/Resources/Users/Schemas/UserForm.php');

    expect(file_get_contents($userFormFile))
        ->toContain("Select::make('role')")
        ->toContain("TextInput::make('password')")
        ->toContain('->required(fn (string $operation): bool => $operation === \'create\')')
        ->toContain("->hiddenOn('edit')")
        ->toContain('collect(UserRole::cases())')
        ->toContain('->mapWithKeys(')
        ->toContain('->default(UserRole::User->value)')
        ->not->toContain("UserRole::cases()\n                        ->pluck('name', 'value')");
});
