<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('closed_days table uses training_weekly_schedule_id foreign key', function () {
    expect(Schema::hasColumn('closed_days', 'training_weekly_schedule_id'))->toBeTrue()
        ->and(Schema::hasColumn('closed_days', 'training_session_id'))->toBeFalse();
});
