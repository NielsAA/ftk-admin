<?php

use Database\Seeders\TrainingSessionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('training session seeder seeds the current training sessions', function () {
    $this->seed(TrainingSessionSeeder::class);

    expect(
        \App\Models\TrainingSession::query()->orderBy('name')->pluck('name')->all()
    )->toEqual([
        'Brydning, drenge/ungdom',
        'Brydning, puslinge',
        'Fristils brydning',
        'Grapling',
        'MMA Basis',
        'MMA Kamphold',
        'Nina Kids',
        'Open Mat',
    ]);

    $this->assertDatabaseHas('training_sessions', [
        'name' => 'MMA Basis',
        'color' => '#e09e9e',
    ]);

    $this->assertDatabaseHas('training_sessions', [
        'name' => 'Open Mat',
        'color' => '#28d1c7',
    ]);
});