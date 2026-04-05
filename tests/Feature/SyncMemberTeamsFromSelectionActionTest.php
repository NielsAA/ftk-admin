<?php

use App\Actions\SyncMemberTeamsFromSelectionAction;
use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\User;

test('it enrolls member into all selected teams', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Sara',
        'lastname' => 'Hansen',
        'email' => 'sara@example.com',
    ]);

    $teamA = Team::query()->create([
        'name' => 'MMA Basis',
        'number' => 'A1',
    ]);

    $teamB = Team::query()->create([
        'name' => 'Boksning',
        'number' => 'B1',
    ]);

    MemberTeamFunction::query()->create([
        'name' => 'Medlem',
        'description' => 'Standard',
        'default_member_function' => 1,
    ]);

    app(SyncMemberTeamsFromSelectionAction::class)->execute($member, [$teamA->id, $teamB->id]);

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $member->id,
        'team_id' => $teamA->id,
        'left_at' => null,
    ]);

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $member->id,
        'team_id' => $teamB->id,
        'left_at' => null,
    ]);
});

test('it marks deselected active teams as left', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Mads',
        'lastname' => 'Nielsen',
        'email' => 'mads@example.com',
    ]);

    $teamA = Team::query()->create([
        'name' => 'MMA Basis',
        'number' => 'A1',
    ]);

    $teamB = Team::query()->create([
        'name' => 'MMA Kamp',
        'number' => 'A2',
    ]);

    MemberTeamFunction::query()->create([
        'name' => 'Medlem',
        'description' => 'Standard',
        'default_member_function' => 1,
    ]);

    app(SyncMemberTeamsFromSelectionAction::class)->execute($member, [$teamA->id, $teamB->id]);
    app(SyncMemberTeamsFromSelectionAction::class)->execute($member, [$teamB->id]);

    $activeTeamIds = MemberOfTeam::query()
        ->where('member_id', $member->id)
        ->whereNull('left_at')
        ->pluck('team_id')
        ->all();

    expect($activeTeamIds)->toBe([$teamB->id]);

    expect(
        MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->where('team_id', $teamA->id)
            ->whereNotNull('left_at')
            ->exists()
    )->toBeTrue();
});
