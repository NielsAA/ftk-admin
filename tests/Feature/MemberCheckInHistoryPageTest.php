<?php

use App\Models\Member;
use App\Models\MembersCheckIn;
use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use App\Models\User;
use Illuminate\Support\Carbon;

test('authenticated user can visit member check-in history page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('member.check-in.history'))
        ->assertOk()
        ->assertSee('Træningshistorik')
        ->assertSee('Udfyld medlemsformularen på medlemsprofilen for at kunne se træningshistorik.')
        ->assertDontSee('Vælg medlem');
});

test('member dropdown is hidden on training history when user has exactly one member', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Karen',
        'lastname' => 'Madsen',
        'email' => 'karen@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('member.check-in.history'))
        ->assertOk()
        ->assertDontSee('Vælg medlem');
});

test('member dropdown is visible on training history when user has two or more members', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Karen',
        'lastname' => 'Madsen',
        'email' => 'karen.dropdown@example.com',
    ]);

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Bo',
        'lastname' => 'Nielsen',
        'email' => 'bo.dropdown@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('member.check-in.history'))
        ->assertOk()
        ->assertSee('Vælg medlem');
});

test('member check-in history shows saved check-ins', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Anders',
        'lastname' => 'Larsen',
        'email' => 'anders@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'BJJ Teknik',
        'description' => null,
        'color' => '#223344',
    ]);

    $weeklySchedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    MembersCheckIn::create([
        'member_id' => $member->id,
        'training_weekly_schedule_id' => $weeklySchedule->id,
        'ekstra_traing_id' => null,
        'check_in_date' => Carbon::today()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('member.check-in.history'))
        ->assertOk()
        ->assertSee('BJJ Teknik')
        ->assertSee('Fast hold');
});
