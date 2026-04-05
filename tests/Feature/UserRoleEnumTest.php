<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user role enum contains expected roles', function () {
    expect(UserRole::cases())
        ->toHaveCount(3)
        ->and(UserRole::User->value)->toBe('user')
        ->and(UserRole::Coach->value)->toBe('coach')
        ->and(UserRole::Admin->value)->toBe('admin');
});

test('user role is cast to enum', function () {
    $user = User::factory()->create([
        'role' => UserRole::Coach,
    ]);

    expect($user->fresh()->role)
        ->toBe(UserRole::Coach);
});