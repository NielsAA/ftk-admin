<?php

use Illuminate\Support\Facades\Schema;

test('member_of_teams table has stripe subscription id column', function () {
    expect(Schema::hasColumn('member_of_teams', 'stripe_subscription_id'))->toBeTrue();
});
