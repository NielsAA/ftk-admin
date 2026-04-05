<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('member.profile.edit'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit medlems profil page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('member.profile.edit'));
    $response
        ->assertOk()
        ->assertSee(route('member.profile.edit', absolute: false))
        ->assertSee('Profil');
});

test('settings appears below medlems profil in sidebar', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $content = $this->get(route('member.profile.edit'))
        ->assertOk()
        ->getContent();

    expect($content)->not->toBeFalse();
    expect(strpos($content, 'Settings'))->toBeGreaterThan(strpos($content, 'Medlems Profil'));
});

test('logout action is present in sidebar area', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $content = $this->get(route('member.profile.edit'))
        ->assertOk()
        ->getContent();

    expect($content)->not->toBeFalse();
    expect(substr_count($content, 'action="'.route('logout').'"'))->toBeGreaterThan(1);
});