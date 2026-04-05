<?php

use App\Actions\BulkEnrollMembersInTeamAction;
use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

test('it enrolls all selected members into a team', function () {
    MemberTeamFunction::query()->create([
        'name' => 'Medlem',
        'description' => 'Standard',
        'default_member_function' => 1,
    ]);

    $team = Team::query()->create([
        'name' => 'MMA Basis',
        'number' => 'A1',
    ]);

    $members = Collection::make([
        Member::query()->create([
            'user_id' => User::factory()->create()->id,
            'firstname' => 'Anna',
            'lastname' => 'Nielsen',
            'email' => 'anna@example.com',
        ]),
        Member::query()->create([
            'user_id' => User::factory()->create()->id,
            'firstname' => 'Bo',
            'lastname' => 'Hansen',
            'email' => 'bo@example.com',
        ]),
    ]);

    $processedCount = app(BulkEnrollMembersInTeamAction::class)->execute($members, $team);

    expect($processedCount)->toBe(2);

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $members[0]->id,
        'team_id' => $team->id,
        'left_at' => null,
    ]);

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $members[1]->id,
        'team_id' => $team->id,
        'left_at' => null,
    ]);
});

test('it does not create duplicate active enrollments during bulk enrollment', function () {
    MemberTeamFunction::query()->create([
        'name' => 'Medlem',
        'description' => 'Standard',
        'default_member_function' => 1,
    ]);

    $team = Team::query()->create([
        'name' => 'Boksning',
        'number' => 'B1',
    ]);

    $member = Member::query()->create([
        'user_id' => User::factory()->create()->id,
        'firstname' => 'Clara',
        'lastname' => 'Madsen',
        'email' => 'clara@example.com',
    ]);

    $action = app(BulkEnrollMembersInTeamAction::class);
    $records = Collection::make([$member]);

    $action->execute($records, $team);
    $action->execute($records, $team);

    expect(MemberOfTeam::query()
        ->where('member_id', $member->id)
        ->where('team_id', $team->id)
        ->whereNull('left_at')
        ->count())->toBe(1);
});
