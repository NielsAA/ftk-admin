<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware('admin')->get('/_test/admin-only', fn () => 'ok');
    Route::middleware('coach')->get('/_test/coach-only', fn () => 'ok');
    Route::middleware('member')->get('/_test/member-only', fn () => 'ok');
});

it('allows admin middleware for admins', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/_test/admin-only')
        ->assertOk();
});

it('denies admin middleware for non-admins', function () {
    $coach = User::factory()->coach()->create();

    $this->actingAs($coach)
        ->get('/_test/admin-only')
        ->assertForbidden();
});

it('allows coach middleware for coaches and admins', function () {
    $coach = User::factory()->coach()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($coach)
        ->get('/_test/coach-only')
        ->assertOk();

    $this->actingAs($admin)
        ->get('/_test/coach-only')
        ->assertOk();
});

it('denies coach middleware for regular users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/_test/coach-only')
        ->assertForbidden();
});

it('allows member middleware for users with member record', function () {
    $user = User::factory()->create();
    Member::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/_test/member-only')
        ->assertOk();
});

it('denies member middleware for users without member record', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/_test/member-only')
        ->assertForbidden();
});
