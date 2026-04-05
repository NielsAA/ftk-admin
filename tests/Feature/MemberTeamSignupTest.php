<?php

use App\Models\Member;
use App\Models\Team;
use App\Models\TrainingSession;
use App\Models\User;

test('member team signup layout page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertSee('Hold tilmelding')
        ->assertSee('Vaelg hold')
        ->assertSee('Udfyld medlemsformularen på medlemsprofilen for at kunne tilmelde et hold.')
        ->assertDontSee('Vaelg medlem');
});

test('member dropdown is hidden when user has exactly one member', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertDontSee('Vaelg medlem');
});

test('member dropdown is visible when user has two or more members', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.two@example.com',
    ]);

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Bo',
        'lastname' => 'Jensen',
        'email' => 'bo.two@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertSee('Vaelg medlem');
});

test('team signup menu item is visible in app navigation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
    ->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Hold tilmelding');
});

test('team signup cards show current training sessions', function () {
    $user = User::factory()->create();

    $team = Team::create([
        'name' => 'MMA Hold',
        'number' => '1',
        'description' => 'Kamp traening',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'MMA Basis',
        'description' => null,
        'color' => '#112233',
    ]);

    $team->trainingSessions()->attach($trainingSession->id);

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertSee('Giver adgang til')
        ->assertSee('MMA Basis');
});
