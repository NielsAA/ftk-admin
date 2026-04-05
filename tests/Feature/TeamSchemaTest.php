<?php

use App\Models\Team;
use App\Models\TrainingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('teams table has stripe price id column', function () {
    expect(Schema::hasColumn('teams', 'stripe_price_id'))->toBeTrue();
});

test('teams table has stripe product id column', function () {
    expect(Schema::hasColumn('teams', 'stripe_product_id'))->toBeTrue();
});

test('team filament form contains stripe price id input', function () {
    $teamFormFile = app_path('Filament/Resources/Teams/Schemas/TeamForm.php');

    expect(file_get_contents($teamFormFile))
        ->toContain("TextInput::make('stripe_price_id')");
});

test('team filament form contains stripe product id input', function () {
    $teamFormFile = app_path('Filament/Resources/Teams/Schemas/TeamForm.php');

    expect(file_get_contents($teamFormFile))
        ->toContain("TextInput::make('stripe_product_id')");
});

test('team filament form contains photo field', function () {
    $teamFormFile = app_path('Filament/Resources/Teams/Schemas/TeamForm.php');

    expect(file_get_contents($teamFormFile))
    ->toContain("FileUpload::make('photo_path')")
    ->toContain("->disk('public')")
        ->toContain("->directory('teams/profile-images')")
    ->toContain("->visibility('public')")
    ->not->toContain('Placeholder::make(')
    ->not->toContain('->previewable(false)');
});

test('team filament form contains price fields', function () {
    $teamFormFile = app_path('Filament/Resources/Teams/Schemas/TeamForm.php');

    expect(file_get_contents($teamFormFile))
        ->toContain("TextInput::make('price')")
        ->toContain("Select::make('price_type')");
});

test('team filament form contains training sessions multiselect', function () {
    $teamFormFile = app_path('Filament/Resources/Teams/Schemas/TeamForm.php');

    expect(file_get_contents($teamFormFile))
        ->toContain("Select::make('trainingSessions')")
        ->toContain("->relationship('trainingSessions', 'name')")
        ->toContain('->multiple()');
});

test('teams filament table contains expected columns', function () {
    $tableFile = app_path('Filament/Resources/Teams/Tables/TeamsTable.php');

    expect(file_get_contents($tableFile))
        ->toContain("TextColumn::make('name')")
        ->toContain("TextColumn::make('number')")
        ->toContain("TextColumn::make('active_members_count')")
        ->toContain("TextColumn::make('price')")
        ->toContain("TextColumn::make('price_type')")
        ->toContain("TrashedFilter::make()");
});

test('team resource loads active members count in the query', function () {
    $resourceFile = app_path('Filament/Resources/Teams/TeamResource.php');

    expect(file_get_contents($resourceFile))
        ->toContain('->modifyQueryUsing(')
        ->toContain("'membersOfTeam as active_members_count'")
        ->toContain("->whereNull('left_at')");
});

test('team can be linked to multiple training sessions', function () {
    $team = Team::create([
        'name' => 'U15',
        'number' => '15',
    ]);

    $firstTrainingSession = TrainingSession::create([
        'name' => 'Mandag 17:00',
    ]);

    $secondTrainingSession = TrainingSession::create([
        'name' => 'Onsdag 18:30',
    ]);

    $team->trainingSessions()->sync([
        $firstTrainingSession->id,
        $secondTrainingSession->id,
    ]);

    expect($team->fresh()->trainingSessions->pluck('id')->all())
        ->toEqualCanonicalizing([
            $firstTrainingSession->id,
            $secondTrainingSession->id,
        ]);

    $this->assertDatabaseHas('team_access_to_trainings', [
        'team_id' => $team->id,
        'training_session_id' => $firstTrainingSession->id,
    ]);

    $this->assertDatabaseHas('team_access_to_trainings', [
        'team_id' => $team->id,
        'training_session_id' => $secondTrainingSession->id,
    ]);
});
