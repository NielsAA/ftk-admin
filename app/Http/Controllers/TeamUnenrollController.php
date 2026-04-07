<?php

namespace App\Http\Controllers;

use App\Models\MemberOfTeam;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TeamUnenrollController extends Controller
{
    /**
     * Afmeld medlem fra hold (og annuller Stripe-subscription når den findes lokalt).
     */
    public function unenroll(Request $request, Team $team): RedirectResponse
    {
        $memberId = $request->integer('member_id');

        $member = $request->user()
            ->members()
            ->whereKey($memberId)
            ->first();

        if ($member === null) {
            return back()->withErrors([
                'team' => 'Du skal vaelge et gyldigt medlem for at afmelde holdet.',
            ]);
        }

        $activeEnrollments = MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->where('team_id', $team->id)
            ->whereNull('left_at')
            ->get();

        if ($activeEnrollments->isEmpty()) {
            return back()->withErrors([
                'team' => 'Medlemmet er ikke aktivt tilmeldt dette hold.',
            ]);
        }

        foreach ($activeEnrollments as $activeEnrollment) {
            if (! $activeEnrollment->stripe_subscription_id) {
                continue;
            }

            $subscription = $member
                ->subscriptions()
                ->where('stripe_id', $activeEnrollment->stripe_subscription_id)
                ->first();

            if ($subscription === null) {
                Log::warning('Stripe subscription not found locally during unenroll', [
                    'member_id' => $member->id,
                    'team_id' => $team->id,
                    'stripe_subscription_id' => $activeEnrollment->stripe_subscription_id,
                ]);

                continue;
            }

            try {
                $subscription->cancelNow();
            } catch (Throwable) {
                return back()->withErrors([
                    'team' => 'Stripe betaling kunne ikke annulleres. Afmelding blev stoppet.',
                ]);
            }
        }

        MemberOfTeam::query()
            ->where('member_id', $member->id)
            ->where('team_id', $team->id)
            ->whereNull('left_at')
            ->update([
                'left_at' => now()->toDateString(),
            ]);

        return redirect()->route('member.teams.signup');
    }
}
