<?php

namespace App\Actions;

use App\Models\MemberOfTeam;
use App\Models\Team;
use Laravel\Cashier\Cashier;
use Throwable;

class SwapTeamStripePriceAction
{
    /**
     * @return array{updated:int,failed:int,skipped:int}
     */
    public function execute(Team $team, string $newStripePriceId): array
    {
        $updated = 0;
        $failed = 0;
        $skipped = 0;

        $enrollments = MemberOfTeam::query()
            ->with('member')
            ->where('team_id', $team->id)
            ->whereNull('left_at')
            ->whereNotNull('stripe_subscription_id')
            ->where('stripe_subscription_id', '!=', '')
            ->get()
            ->unique('stripe_subscription_id');

        foreach ($enrollments as $enrollment) {
            try {
                $subscription = Cashier::stripe()->subscriptions->retrieve($enrollment->stripe_subscription_id);

                $subscriptionItemId = $subscription->items->data[0]->id ?? null;

                if (! is_string($subscriptionItemId) || $subscriptionItemId === '') {
                    $skipped++;

                    continue;
                }

                Cashier::stripe()->subscriptions->update($enrollment->stripe_subscription_id, [
                    'items' => [[
                        'id' => $subscriptionItemId,
                        'price' => $newStripePriceId,
                    ]],
                    'proration_behavior' => 'create_prorations',
                ]);

                if ($enrollment->member) {
                    $enrollment->member->subscriptions()
                        ->where('stripe_id', $enrollment->stripe_subscription_id)
                        ->update(['stripe_price' => $newStripePriceId]);
                }

                $updated++;
            } catch (Throwable) {
                $failed++;
            }
        }

        $team->update([
            'stripe_price_id' => $newStripePriceId,
        ]);

        return [
            'updated' => $updated,
            'failed' => $failed,
            'skipped' => $skipped,
        ];
    }
}