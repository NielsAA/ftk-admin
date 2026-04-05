<?php

use App\Enums\UserRole;
use App\Models\User;
use Filament\Panel;

it('allows admin users to access the admin panel', function () {
    $admin = new User(['role' => UserRole::Admin]);
    $panel = \Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->once()->andReturn('admin');

    expect($admin->canAccessPanel($panel))->toBeTrue();
});

it('denies non-admin users access to the admin panel', function () {
    $coach = new User(['role' => UserRole::Coach]);
    $user = new User(['role' => UserRole::User]);
    $panel = \Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->times(2)->andReturn('admin');

    expect($coach->canAccessPanel($panel))->toBeFalse();
    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('allows users to access non-admin panels', function () {
    $user = new User(['role' => UserRole::User]);
    $panel = \Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->once()->andReturn('member');

    expect($user->canAccessPanel($panel))->toBeTrue();
});
