<?php

use App\Models\Member;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\User;
use Stripe\StripeClient;

test('success callback without session id redirects to signup page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('member.teams.checkout.success'))
        ->assertRedirect(route('member.teams.signup'));
});

test('success callback enrolls member when stripe session is paid', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Mette',
        'lastname' => 'Nielsen',
        'email' => 'mette@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Hold Stripe',
        'number' => 'S1',
    ]);

    MemberTeamFunction::query()->create([
        'name' => 'Standard',
        'description' => 'Standard funktion',
        'default_member_function' => 1,
    ]);

    app()->bind(StripeClient::class, function () use ($team, $member) {
        return (object) [
            'checkout' => (object) [
                'sessions' => new class($team->id, $member->id) {
                    public function __construct(
                        private int $teamId,
                        private int $memberId,
                    ) {
                    }

                    public function retrieve(string $sessionId): object
                    {
                        if ($sessionId !== 'cs_test_123') {
                            throw new RuntimeException('Unknown session');
                        }

                        return (object) [
                            'payment_status' => 'paid',
                            'subscription' => 'sub_test_123',
                            'metadata' => [
                                'team_id' => (string) $this->teamId,
                                'member_id' => (string) $this->memberId,
                            ],
                        ];
                    }
                },
            ],
        ];
    });

    $this->actingAs($user)
        ->get(route('member.teams.checkout.success', ['session_id' => 'cs_test_123']))
        ->assertRedirect(route('member.teams.signup', ['checkout' => 'success']));

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $member->id,
        'team_id' => $team->id,
        'left_at' => null,
        'stripe_subscription_id' => 'sub_test_123',
    ]);
});

test('success callback enrolls member when stripe metadata has no team and member ids', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Anna',
        'lastname' => 'Jensen',
        'email' => 'anna@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Hold Query Fallback',
        'number' => 'Q1',
    ]);

    MemberTeamFunction::query()->create([
        'name' => 'Standard',
        'description' => 'Standard funktion',
        'default_member_function' => 1,
    ]);

    app()->bind(StripeClient::class, function () {
        return (object) [
            'checkout' => (object) [
                'sessions' => new class {
                    public function retrieve(string $sessionId): object
                    {
                        if ($sessionId !== 'cs_test_query_fallback') {
                            throw new RuntimeException('Unknown session');
                        }

                        return (object) [
                            'payment_status' => 'paid',
                            'subscription' => 'sub_test_query_fallback',
                            'metadata' => [
                                'is_on_session_checkout' => true,
                            ],
                            'client_reference_id' => null,
                        ];
                    }
                },
            ],
        ];
    });

    $this->actingAs($user)
        ->get(route('member.teams.checkout.success', [
            'session_id' => 'cs_test_query_fallback',
            'team_id' => $team->id,
            'member_id' => $member->id,
        ]))
        ->assertRedirect(route('member.teams.signup', ['checkout' => 'success']));

    $this->assertDatabaseHas('member_of_teams', [
        'member_id' => $member->id,
        'team_id' => $team->id,
        'left_at' => null,
        'stripe_subscription_id' => 'sub_test_query_fallback',
    ]);
});
