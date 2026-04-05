<?php

namespace App\Actions;

use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\Team;

class SyncMemberTeamsFromSelectionAction
{
    public function __construct(
        private readonly EnrollMemberInTeamAction $enrollMemberInTeamAction,
    ) {
    }

    /**
     * @param  array<int|string>  $selectedTeamIds
     */
    public function execute(Member $member, array $selectedTeamIds): void
    {
        $selectedTeamIds = array_values(array_unique(array_map('intval', array_filter(
            $selectedTeamIds,
            fn (mixed $id): bool => filled($id),
        ))));

        $selectedTeams = Team::query()
            ->whereIn('id', $selectedTeamIds)
            ->get()
            ->keyBy('id');

        foreach ($selectedTeamIds as $teamId) {
            $team = $selectedTeams->get($teamId);

            if ($team === null) {
                continue;
            }

            $this->enrollMemberInTeamAction->execute($member, $team);
        }

        MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->whereNull('left_at')
            ->when(
                $selectedTeamIds !== [],
                fn ($query) => $query->whereNotIn('team_id', $selectedTeamIds),
                fn ($query) => $query,
            )
            ->update([
                'left_at' => now()->toDateString(),
            ]);
    }
}
