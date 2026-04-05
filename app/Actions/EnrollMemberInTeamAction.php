<?php

namespace App\Actions;

use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MemberTeamFunction;
use App\Models\Team;

class EnrollMemberInTeamAction
{
    public function execute(Member $member, Team $team, ?string $stripeSubscriptionId = null): void
    {
        $activeEnrollment = MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->where('team_id', $team->id)
            ->whereNull('left_at')
            ->first();

        if ($activeEnrollment !== null) {
            if ($stripeSubscriptionId !== null && $activeEnrollment->stripe_subscription_id === null) {
                $activeEnrollment->update([
                    'stripe_subscription_id' => $stripeSubscriptionId,
                ]);
            }

            return;
        }

        $memberTeamFunctionId = $this->resolveDefaultMemberTeamFunctionId();

        MemberOfTeam::query()->create([
            'member_id' => $member->id,
            'team_id' => $team->id,
            'member_team_function_id' => $memberTeamFunctionId,
            'joined_at' => now()->toDateString(),
            'left_at' => null,
            'stripe_subscription_id' => $stripeSubscriptionId,
        ]);
    }

    protected function resolveDefaultMemberTeamFunctionId(): int
    {
        $memberTeamFunctionId = MemberTeamFunction::query()
            ->where('default_member_function', 1)
            ->value('id');

        if ($memberTeamFunctionId !== null) {
            return (int) $memberTeamFunctionId;
        }

        return (int) MemberTeamFunction::query()->create([
            'name' => 'Medlem',
            'description' => 'Standard holdfunktion',
            'default_member_function' => 1,
        ])->id;
    }
}
