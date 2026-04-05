<?php

use App\Models\Team;
use App\Models\TrainingSession;
use App\Models\User;

test('returns a successful response', function () {
    $mmaBasisTeam = Team::query()->create([
        'name' => 'MMA Basis',
        'number' => '06',
        'description' => 'MMA hold for begynder og motion',
        'photo_path' => 'teams/profile-images/mma-basis.jpg',
        'price' => 250,
        'price_type' => 'monthly',
    ]);

    $mmaKampholdTeam = Team::query()->create([
        'name' => 'MMA Kamphold',
        'number' => '05',
        'description' => 'Kamp hold for ovede',
        'price' => 300,
        'price_type' => 'monthly',
    ]);

    $grapling = TrainingSession::query()->create([
        'name' => 'Grapling',
    ]);

    $openMat = TrainingSession::query()->create([
        'name' => 'Open Mat',
    ]);

    $mmaBasisTeam->trainingSessions()->attach($grapling->id);
    $mmaKampholdTeam->trainingSessions()->attach($openMat->id);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(route('member.check-in'), escape: false)
        ->assertSee('Tjek ind')
        ->assertSee('MMA Basis')
        ->assertSee('MMA Kamphold')
        ->assertSee('250')
        ->assertSee('300')
        ->assertSee('Inkluderede traeningssessions')
        ->assertSee('Grapling')
        ->assertSee('Open Mat')
        ->assertSee('teams/profile-images/mma-basis.jpg');
});

    test('authenticated users see logout button in frontpage menu', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Log ud');
    });