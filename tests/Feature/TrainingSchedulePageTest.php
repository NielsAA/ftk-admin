<?php

use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public training schedule page renders grouped weekly schedule', function () {
    $trainingSession = TrainingSession::create([
        'name' => 'Teknik Hold',
    ]);

    TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => 'monday',
        'start_time' => '17:00:00',
        'end_time' => '18:30:00',
        'description' => 'Begynderhold',
    ]);

    $this->get(route('training.schedule'))
        ->assertSuccessful()
        ->assertDontSee('06:00')
        ->assertSee('Ugeskema')
        ->assertSee('Mandag')
        ->assertSee('min-w-36', escape: false)
        ->assertSee('min-h-6', escape: false)
        ->assertSee('16:30')
        ->assertSee('17:00')
        ->assertSee('18:30')
        ->assertSee('Teknik Hold')
        ->assertSee('Begynderhold')
        ->assertSee('rowspan="3"', escape: false);
});

test('home page links to the public training schedule page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('training.schedule'), escape: false)
        ->assertSee('Se ugeskema');
});

test('public training schedule shows a half-hour buffer around occupied times', function () {
    $trainingSession = TrainingSession::create([
        'name' => 'Avanceret Hold',
        'color' => '#22c55e',
    ]);

    TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => 'wednesday',
        'start_time' => '17:30:00',
        'end_time' => '18:45:00',
    ]);

    $this->get(route('training.schedule'))
        ->assertSuccessful()
        ->assertSee('17:00')
        ->assertSee('17:30')
        ->assertSee('19:00')
        ->assertSee('17:30 - 18:45')
        ->assertSee('Avanceret Hold')
        ->assertSee('border-color: #22c55e', escape: false)
        ->assertSee('rowspan="3"', escape: false);
});
