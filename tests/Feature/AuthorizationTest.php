<?php

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;

describe('role helpers', function () {
    it('identifies admin user', function () {
        $admin = User::factory()->admin()->make();

        expect($admin->isAdmin())->toBeTrue();
        expect($admin->isCoach())->toBeTrue();
        expect($admin->role)->toBe(UserRole::Admin);
    });

    it('identifies coach user', function () {
        $coach = User::factory()->coach()->make();

        expect($coach->isAdmin())->toBeFalse();
        expect($coach->isCoach())->toBeTrue();
        expect($coach->role)->toBe(UserRole::Coach);
    });

    it('identifies regular user', function () {
        $user = User::factory()->make();

        expect($user->isAdmin())->toBeFalse();
        expect($user->isCoach())->toBeFalse();
        expect($user->role)->toBe(UserRole::User);
    });

    it('identifies user with member record as member', function () {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $user->load('member');

        expect($user->isMember())->toBeTrue();
    });

    it('identifies user without member record as non-member', function () {
        $user = User::factory()->make();

        expect($user->isMember())->toBeFalse();
    });
});

describe('gates', function () {
    it('admin gate passes for admin users', function () {
        $admin = User::factory()->admin()->create();

        expect($admin->can('admin'))->toBeTrue();
    });

    it('admin gate denies non-admin users', function () {
        $coach = User::factory()->coach()->create();
        $user = User::factory()->create();

        expect($coach->can('admin'))->toBeFalse();
        expect($user->can('admin'))->toBeFalse();
    });

    it('coach gate passes for coach and admin users', function () {
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();

        expect($coach->can('coach'))->toBeTrue();
        expect($admin->can('coach'))->toBeTrue();
    });

    it('coach gate denies regular users', function () {
        $user = User::factory()->create();

        expect($user->can('coach'))->toBeFalse();
    });

    it('member gate passes for users with a member record', function () {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);
        $user->load('member');

        expect($user->can('member'))->toBeTrue();
    });

    it('member gate denies users without a member record', function () {
        $user = User::factory()->create();

        expect($user->can('member'))->toBeFalse();
    });
});

