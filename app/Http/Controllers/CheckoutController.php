<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class CheckoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Team $team)
    {
        if (empty($team->stripe_price_id)) {
            return back()->withErrors([
                'team' => 'Dette hold har ikke et Stripe Price ID endnu.',
            ]);
        }

        $user = $request->user();
        $selectedMemberId = $request->integer('member_id');

        $member = $selectedMemberId > 0
            ? $user->members()->whereKey($selectedMemberId)->first()
            : null;

        if ($member === null) {
            return back()->withErrors([
                'team' => 'Du skal vaelge et gyldigt medlem foer checkout.',
            ]);
        }

        $subscriptionName = $team->stripe_product_id ?: 'default';

        return $member
            ->newSubscription($subscriptionName, $team->stripe_price_id)
            ->checkout([
                'success_url' => URL::route('member.teams.checkout.success').'?session_id={CHECKOUT_SESSION_ID}&team_id='.$team->id.'&member_id='.$member->id,
                'cancel_url' => URL::route('member.teams.signup', ['checkout' => 'cancel']),
                'client_reference_id' => $team->id.':'.$member->id,
            ]);
    }
}
