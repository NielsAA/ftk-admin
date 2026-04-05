<?php

use App\Actions\EnrollMemberInTeamAction;
use App\Models\Member;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\User;

test('it creates an active member_of_teams row', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Niels',
        'lastname' => 'Hansen',
        'email' => 'niels@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'MMA Basis',
        'number' => 'A1',
    ]);

    MemberTeamFunction::query()->create([
        'name' => 'Standard',
        'description' => 'Standard funktion',
        'default_member_function' => 1,
    ]);

    app(EnrollMemberInTeamAction::class)->execute($member, $team, 'sub_action_123');

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $member->id,
        'team_id' => $team->id,
        'left_at' => null,
        'stripe_subscription_id' => 'sub_action_123',
    ]);
});

test('it does not create duplicate active enrollments', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Ida',
        'lastname' => 'Jensen',
        'email' => 'ida@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'MMA Kamp',
        'number' => 'B1',
    ]);

    MemberTeamFunction::query()->create([
        'name' => 'Standard',
        'description' => 'Standard funktion',
        'default_member_function' => 1,
    ]);

    $action = app(EnrollMemberInTeamAction::class);
    $action->execute($member, $team);
    $action->execute($member, $team);

    expect(
        \App\Models\MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->where('team_id', $team->id)
            ->whereNull('left_at')
            ->count()
    )->toBe(1);
});

test('it creates and assigns a default member function when none exists', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Lars',
        'lastname' => 'Madsen',
        'email' => 'lars@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'No-Gi',
        'number' => 'C1',
    ]);

    app(EnrollMemberInTeamAction::class)->execute($member, $team);

    $defaultFunction = MemberTeamFunction::query()
        ->where('default_member_function', 1)
        ->first();

    expect($defaultFunction)->not->toBeNull();

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $defaultFunction->id,
        'left_at' => null,
    ]);
});
