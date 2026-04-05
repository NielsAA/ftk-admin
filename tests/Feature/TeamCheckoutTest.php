<?php

use App\Models\Member;
use App\Models\Team;
use App\Models\User;

test('guest cannot access team checkout route', function () {
    $team = Team::query()->create([
        'name' => 'Hold A',
        'number' => 'A1',
        'stripe_price_id' => 'price_123',
    ]);

    $this->post(route('member.teams.checkout', $team))
        ->assertRedirect(route('login'));
});

test('checkout redirects back when stripe price id is missing', function () {
    $user = User::factory()->create();

    $member = Member::query()->create([
        'user_id' => $user->id,
        'firstname' => 'Mette',
        'lastname' => 'Nielsen',
        'email' => 'mette@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Hold B',
        'number' => 'B1',
        'stripe_price_id' => null,
    ]);

    $this->actingAs($user)
        ->from(route('member.teams.signup'))
        ->post(route('member.teams.checkout', $team), [
            'member_id' => $member->id,
        ])
        ->assertRedirect(route('member.teams.signup'))
        ->assertSessionHasErrors('team');
});

test('checkout redirects back when user has no member', function () {
    $user = User::factory()->create();

    $team = Team::query()->create([
        'name' => 'Hold C',
        'number' => 'C1',
        'stripe_price_id' => 'price_abc',
    ]);

    $this->actingAs($user)
        ->from(route('member.teams.signup'))
        ->post(route('member.teams.checkout', $team), [
            'member_id' => 999999,
        ])
        ->assertRedirect(route('member.teams.signup'))
        ->assertSessionHasErrors('team');
});

test('checkout rejects member from another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherUsersMember = Member::query()->create([
        'user_id' => $otherUser->id,
        'firstname' => 'Anden',
        'lastname' => 'Bruger',
        'email' => 'anden@example.com',
    ]);

    $team = Team::query()->create([
        'name' => 'Hold D',
        'number' => 'D1',
        'stripe_price_id' => 'price_def',
    ]);

    $this->actingAs($user)
        ->from(route('member.teams.signup'))
        ->post(route('member.teams.checkout', $team), [
            'member_id' => $otherUsersMember->id,
        ])
        ->assertRedirect(route('member.teams.signup'))
        ->assertSessionHasErrors('team');
});
