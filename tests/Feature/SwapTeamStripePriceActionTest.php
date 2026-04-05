<?php

use App\Actions\SwapTeamStripePriceAction;
use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

test('it swaps stripe price for active team subscribers and updates local subscriptions table', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Kasper',
        'lastname' => 'Hansen',
        'email' => 'kasper@example.com',
        'stripe_id' => 'cus_swap_123',
    ]);

    $team = Team::query()->create([
        'name' => 'Hold Swap',
        'number' => 'SW1',
        'stripe_price_id' => 'price_old_123',
    ]);

    $function = MemberTeamFunction::query()->create([
        'name' => 'Standard',
        'description' => 'Standard funktion',
        'default_member_function' => 1,
    ]);

    MemberOfTeam::query()->create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $function->id,
        'joined_at' => now()->toDateString(),
        'left_at' => null,
        'stripe_subscription_id' => 'sub_swap_123',
    ]);

    DB::table('subscriptions')->insert([
        'member_id' => $member->id,
        'type' => 'default',
        'stripe_id' => 'sub_swap_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_old_123',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $GLOBALS['stripe_updated_subscription'] = null;

    app()->bind(StripeClient::class, function () {
        return (object) [
            'subscriptions' => new class {
                public function retrieve(string $subscriptionId): object
                {
                    return (object) [
                        'items' => (object) [
                            'data' => [
                                (object) ['id' => 'si_swap_123'],
                            ],
                        ],
                    ];
                }

                public function update(string $subscriptionId, array $payload): object
                {
                    $GLOBALS['stripe_updated_subscription'] = [
                        'subscription_id' => $subscriptionId,
                        'payload' => $payload,
                    ];

                    return (object) [
                        'id' => $subscriptionId,
                    ];
                }
            },
        ];
    });

    $result = app(SwapTeamStripePriceAction::class)->execute($team, 'price_new_456');

    expect($result)->toBe([
        'updated' => 1,
        'failed' => 0,
        'skipped' => 0,
    ]);

    expect($GLOBALS['stripe_updated_subscription']['subscription_id'])->toBe('sub_swap_123');
    expect($GLOBALS['stripe_updated_subscription']['payload']['items'][0]['id'])->toBe('si_swap_123');
    expect($GLOBALS['stripe_updated_subscription']['payload']['items'][0]['price'])->toBe('price_new_456');

    $this->assertDatabaseHas('subscriptions', [
        'member_id' => $member->id,
        'stripe_id' => 'sub_swap_123',
        'stripe_price' => 'price_new_456',
    ]);

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'stripe_price_id' => 'price_new_456',
    ]);
});