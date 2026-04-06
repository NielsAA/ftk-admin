<?php

use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('member team signup layout page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertSee('Hold tilmelding')
        ->assertSee('Vaelg hold')
        ->assertSee('Udfyld medlemsformularen på medlemsprofilen for at kunne tilmelde et hold.')
        ->assertDontSee('Vaelg medlem');
});

test('member dropdown is hidden when user has exactly one member', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertDontSee('Vaelg medlem');
});

test('member dropdown is visible when user has two or more members', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.two@example.com',
    ]);

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Bo',
        'lastname' => 'Jensen',
        'email' => 'bo.two@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertSee('Vaelg medlem');
});

test('team signup menu item is visible in app navigation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Hold tilmelding');
});

test('team signup cards show current training sessions', function () {
    $user = User::factory()->create();

    $team = Team::create([
        'name' => 'MMA Hold',
        'number' => '1',
        'description' => 'Kamp traening',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'MMA Basis',
        'description' => null,
        'color' => '#112233',
    ]);

    $team->trainingSessions()->attach($trainingSession->id);

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertSee('Giver adgang til')
        ->assertSee('MMA Basis');
});

test('active stripe subscriptions table is shown with active subscriptions', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.sub@example.com',
    ]);

    $team = Team::create([
        'name' => 'Brydning Hold',
        'number' => 'BH1',
    ]);

    $function = MemberTeamFunction::create([
        'name' => 'Medlem',
        'default_member_function' => true,
    ]);

    MemberOfTeam::create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $function->id,
        'joined_at' => now()->toDateString(),
        'left_at' => null,
        'stripe_subscription_id' => 'sub_active_123',
    ]);

    DB::table('subscriptions')->insert([
        'member_id' => $member->id,
        'type' => 'default',
        'stripe_id' => 'sub_active_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_active_123',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('member.teams.signup'))
        ->assertOk()
        ->assertSee('Aktive Stripe subscriptions')
        ->assertSee('Brydning Hold')
        ->assertSee('sub_active_123')
        ->assertSee('price_active_123')
        ->assertSee('active');
});
