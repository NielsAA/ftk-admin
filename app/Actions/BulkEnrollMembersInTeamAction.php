<?php

namespace App\Actions;

use App\Models\Member;
use App\Models\Team;
use Illuminate\Support\Collection;

class BulkEnrollMembersInTeamAction
{
    public function __construct(
        private readonly EnrollMemberInTeamAction $enrollMemberInTeamAction,
    ) {
    }

    /**
     * @param  iterable<int, Member>  $members
     */
    public function execute(iterable $members, Team $team): int
    {
        $processedCount = 0;

        foreach ($members as $member) {
            $this->enrollMemberInTeamAction->execute($member, $team);
            $processedCount++;
        }

        return $processedCount;
    }
}
