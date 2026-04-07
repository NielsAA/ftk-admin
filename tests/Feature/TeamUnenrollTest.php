<?php

use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\User;

test('guest cannot access team unenroll route', function () {
    $team = Team::query()->create([
        'name' => 'Hold U',
        'number' => 'U1',
    ]);

    $this->post(route('member.teams.unenroll', $team))
        ->assertRedirect(route('login'));
});

test('member can be unenrolled from team', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.unenroll@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Karate Hold',
        'number' => 'K1',
    ]);

    $function = MemberTeamFunction::query()->create([
        'name' => 'Medlem',
        'default_member_function' => true,
    ]);

    $enrollment = MemberOfTeam::query()->create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $function->id,
        'joined_at' => now()->toDateString(),
        'left_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('member.teams.unenroll', $team), [
            'member_id' => $member->id,
        ])
        ->assertRedirect(route('member.teams.signup'));

    expect($enrollment->fresh()->left_at)->not->toBeNull();
});

test('unenroll rejects member from another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherUsersMember = Member::query()->create([
        'user_id' => $otherUser->id,
        'firstname' => 'Anden',
        'lastname' => 'Bruger',
        'email' => 'anden.unenroll@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Judo Hold',
        'number' => 'J1',
    ]);

    $this->actingAs($user)
        ->from(route('member.teams.signup'))
        ->post(route('member.teams.unenroll', $team), [
            'member_id' => $otherUsersMember->id,
        ])
        ->assertRedirect(route('member.teams.signup'))
        ->assertSessionHasErrors('team');
});

test('unenroll still marks left_at when stripe subscription is missing locally', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.stripe.missing@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Boksning Hold',
        'number' => 'B1',
    ]);

    $function = MemberTeamFunction::query()->create([
        'name' => 'Medlem',
        'default_member_function' => true,
    ]);

    $enrollment = MemberOfTeam::query()->create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $function->id,
        'joined_at' => now()->toDateString(),
        'left_at' => null,
        'stripe_subscription_id' => 'sub_missing_123',
    ]);

    $this->actingAs($user)
        ->from(route('member.teams.signup'))
        ->post(route('member.teams.unenroll', $team), [
            'member_id' => $member->id,
        ])
        ->assertRedirect(route('member.teams.signup'));

    expect($enrollment->fresh()->left_at)->not->toBeNull();
});

test('unenroll marks all active enrollments as left for same member and team', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Mads',
        'lastname' => 'Hansen',
        'email' => 'mads.multiple@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Taekwondo Hold',
        'number' => 'T1',
    ]);

    $function = MemberTeamFunction::query()->create([
        'name' => 'Medlem',
        'default_member_function' => true,
    ]);

    $firstEnrollment = MemberOfTeam::query()->create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $function->id,
        'joined_at' => now()->toDateString(),
        'left_at' => null,
    ]);

    $secondEnrollment = MemberOfTeam::query()->create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $function->id,
        'joined_at' => now()->toDateString(),
        'left_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('member.teams.unenroll', $team), [
            'member_id' => $member->id,
        ])
        ->assertRedirect(route('member.teams.signup'));

    expect($firstEnrollment->fresh()->left_at)->not->toBeNull();
    expect($secondEnrollment->fresh()->left_at)->not->toBeNull();
});
