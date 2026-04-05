<?php

namespace App\Http\Controllers;

use App\Actions\EnrollMemberInTeamAction;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Throwable;

class TeamCheckoutSuccessController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, EnrollMemberInTeamAction $enrollMemberInTeamAction)
    {
        Log::info('TeamCheckoutSuccessController invoked', ['session_id' => $request->get('session_id')]);

        $sessionId = $request->string('session_id')->toString();

        if ($sessionId === '') {
            Log::warning('No session_id provided');
            return redirect()->route('member.teams.signup');
        }

        try {
            $checkoutSession = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
            Log::info('Stripe session retrieved', ['payment_status' => $checkoutSession->payment_status]);
        } catch (Throwable $e) {
            Log::error('Failed to retrieve Stripe session', ['error' => $e->getMessage()]);
            return redirect()->route('member.teams.signup', ['checkout' => 'cancel']);
        }

        if ($checkoutSession->payment_status !== 'paid') {
            Log::warning('Payment not completed', ['status' => $checkoutSession->payment_status]);
            return redirect()->route('member.teams.signup', ['checkout' => 'cancel']);
        }

        $metadata = (array) ($checkoutSession->metadata ?? []);

        if ($metadata === [] && isset($checkoutSession['metadata'])) {
            $metadata = (array) $checkoutSession['metadata'];
        }

        $teamId = (int) ($metadata['team_id'] ?? $request->integer('team_id'));
        $memberId = (int) ($metadata['member_id'] ?? $request->integer('member_id'));

        if (($teamId < 1 || $memberId < 1) && is_string($checkoutSession->client_reference_id ?? null)) {
            [$teamFromReference, $memberFromReference] = array_pad(
                explode(':', $checkoutSession->client_reference_id, 2),
                2,
                '0'
            );

            $teamId = $teamId > 0 ? $teamId : (int) $teamFromReference;
            $memberId = $memberId > 0 ? $memberId : (int) $memberFromReference;
        }

        $stripeSubscriptionId = is_string($checkoutSession->subscription ?? null)
            ? $checkoutSession->subscription
            : null;

        Log::info('Parsed checkout identifiers', [
            'team_id' => $teamId,
            'member_id' => $memberId,
            'subscription_id' => $stripeSubscriptionId,
            'query_team_id' => $request->query('team_id'),
            'query_member_id' => $request->query('member_id'),
            'client_reference_id' => $checkoutSession->client_reference_id ?? null,
        ]);

        if ($teamId < 1 || $memberId < 1) {
            Log::warning('Invalid team or member ID from metadata');
            return redirect()->route('member.teams.signup', ['checkout' => 'cancel']);
        }

        $member = $request->user()->members()->whereKey($memberId)->first();
        $team = Team::query()->find($teamId);

        if (! $member instanceof Member || ! $team instanceof Team) {
            Log::warning('Member or Team not found', ['member_found' => $member !== null, 'team_found' => $team !== null]);
            return redirect()->route('member.teams.signup', ['checkout' => 'cancel']);
        }

        Log::info('Enrolling member', ['member_id' => $member->id, 'team_id' => $team->id]);
        $enrollMemberInTeamAction->execute($member, $team, $stripeSubscriptionId);

        return redirect()->route('member.teams.signup', ['checkout' => 'success']);
    }
}
