<?php

use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use App\Models\TrialSession;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('member profile page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Vedligehold dine medlemsoplysninger')
        ->assertSee('Gem prøvetræning');
});

test('member profile shows required information text when user has no members', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('De nedenstående oplysninger er nødvendige for medlemskab af Fightteam Kolding.')
        ->assertDontSee('Opret nyt medlem');
});

test('member profile hides dropdown when user has one member and still shows create button', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.one@example.com',
    ]);

    $this->actingAs($user);

    $this->get(route('member.profile.edit'))
        ->assertOk()
        ->assertDontSee('Vælg medlem')
        ->assertSee('Opret nyt medlem');
});

test('eligible new member sees trial booking button and helper text', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.nielsen@example.com',
    ]);

    $this->actingAs($user);

    $this->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Tilmeld prøvetræning')
        ->assertSee('Som nyt medlem har du mulighed for at booke en prøvetræning');
});

test('trial popup heading shows selected member name', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne.nielsen@example.com',
    ]);

    $this->actingAs($user);

    $this->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Tilmeld prøvetræning - Sanne Nielsen');
});

test('trial training is saved in trial_sessions table from member profile', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'Prøvehold',
        'description' => null,
        'color' => '#123456',
    ]);

    $trialDate = Carbon::parse('next monday')->toDateString();

    $trainingWeeklySchedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => 'monday',
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::member-profile')
        ->set('selected_member_id', (string) $member->id)
        ->set('showTrialTrainingModal', true)
        ->set('trial_date', $trialDate)
        ->set('trial_training_schedule_id', (string) $trainingWeeklySchedule->id)
        ->call('bookTrialTraining')
        ->assertHasNoErrors()
        ->assertSet('showTrialTrainingModal', false);

    $this->assertDatabaseHas('trial_sessions', [
        'member_id' => $member->id,
        'training_session_id' => $trainingSession->id,
    ]);

    expect(TrialSession::query()->count())->toBe(1);
});

test('trial session can be deleted from member profile list', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Lars',
        'lastname' => 'Madsen',
        'email' => 'lars@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'Grapling',
        'description' => null,
        'color' => '#112233',
    ]);

    $trialSession = TrialSession::create([
        'member_id' => $member->id,
        'training_session_id' => $trainingSession->id,
        'trial_date' => now()->addDays(2)->toDateString(),
    ]);

    $this->actingAs($user);

    Livewire::test('pages::member-profile')
        ->set('selected_member_id', (string) $member->id)
        ->call('deleteTrialSession', $trialSession->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('trial_sessions', [
        'id' => $trialSession->id,
    ]);
});

test('member can only sign up for one trial training', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Ida',
        'lastname' => 'Møller',
        'email' => 'ida@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'MMA Basis',
        'description' => null,
        'color' => '#345678',
    ]);

    TrialSession::create([
        'member_id' => $member->id,
        'training_session_id' => $trainingSession->id,
        'trial_date' => now()->addDay()->toDateString(),
    ]);

    $trialDate = Carbon::parse('next monday')->toDateString();

    $schedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => 'monday',
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::member-profile')
        ->set('selected_member_id', (string) $member->id)
        ->set('showTrialTrainingModal', true)
        ->set('trial_date', $trialDate)
        ->set('trial_training_schedule_id', (string) $schedule->id)
        ->call('bookTrialTraining')
        ->assertHasErrors(['trial_training_schedule_id']);

    expect(TrialSession::query()->where('member_id', $member->id)->count())->toBe(1);
});

test('trial training is blocked for members with membership history', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Nanna',
        'lastname' => 'Lund',
        'email' => 'nanna@example.com',
    ]);

    $team = Team::create([
        'name' => 'MMA Hold',
        'number' => '1',
        'description' => null,
    ]);

    $memberTeamFunction = MemberTeamFunction::create([
        'name' => 'Fighter',
        'description' => null,
        'default_member_function' => 1,
    ]);

    MemberOfTeam::create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $memberTeamFunction->id,
        'joined_at' => now()->subMonth()->toDateString(),
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'Brydning',
        'description' => null,
        'color' => '#778899',
    ]);

    $trialDate = Carbon::parse('next monday')->toDateString();

    $schedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => 'monday',
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::member-profile')
        ->set('selected_member_id', (string) $member->id)
        ->set('showTrialTrainingModal', true)
        ->set('trial_date', $trialDate)
        ->set('trial_training_schedule_id', (string) $schedule->id)
        ->call('bookTrialTraining')
        ->assertHasErrors(['trial_training_schedule_id']);

    $this->assertDatabaseCount('trial_sessions', 0);
});

test('member with membership history does not see trial booking button or helper text', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Nanna',
        'lastname' => 'Lund',
        'email' => 'nanna.second@example.com',
    ]);

    $team = Team::create([
        'name' => 'MMA Hold 2',
        'number' => '2',
        'description' => null,
    ]);

    $memberTeamFunction = MemberTeamFunction::create([
        'name' => 'Elev',
        'description' => null,
        'default_member_function' => 1,
    ]);

    MemberOfTeam::create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $memberTeamFunction->id,
        'joined_at' => now()->subMonth()->toDateString(),
    ]);

    $this->actingAs($user);

    $this->get(route('member.profile.edit'))
        ->assertOk()
        ->assertDontSee('Som nyt medlem har du mulighed for at booke en prøvetræning');

    Livewire::test('pages::member-profile')
        ->assertSet('canBookTrialTraining', false);
});

test('member profile shows latest trial training summary for selected member', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Pia',
        'lastname' => 'Jensen',
        'email' => 'pia@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'MMA Basis',
        'description' => null,
        'color' => '#abcdef',
    ]);

    TrialSession::create([
        'member_id' => $member->id,
        'training_session_id' => $trainingSession->id,
        'trial_date' => now()->addDays(3)->toDateString(),
    ]);

    $this->actingAs($user);

    $this->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Husk du er tilmeldt prøvetræning')
        ->assertSee('MMA Basis');
});

test('member profile information can be updated', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Ole',
        'lastname' => 'Hansen',
        'email' => 'ole@example.com',
        'phone' => '11111111',
        'address' => 'Gammel Vej 1',
        'postal_code' => '8000',
        'city' => 'Aarhus',
        'birthdate' => '1990-01-01',
        'gender' => 'male',
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::member-profile')
        ->set('firstname', 'Oline')
        ->set('lastname', 'Jensen')
        ->set('email', 'oline@example.com')
        ->set('phone', '22222222')
        ->set('address', 'Ny Vej 2')
        ->set('postal_code', '8200')
        ->set('city', 'Aarhus N')
        ->set('birthdate', '1992-03-04')
        ->set('gender', 'female')
        ->call('save');

    $response->assertHasNoErrors();

    expect($member->refresh())
        ->firstname->toBe('Oline')
        ->lastname->toBe('Jensen')
        ->email->toBe('oline@example.com')
        ->phone->toBe('22222222')
        ->address->toBe('Ny Vej 2')
        ->postal_code->toBe('8200')
        ->city->toBe('Aarhus N')
        ->gender->toBe('female');

    expect($member->birthdate?->format('Y-m-d'))->toBe('1992-03-04');
});

test('member profile record is created when the user does not have one', function () {
    $user = User::factory()->create(['name' => 'Mette Nielsen', 'email' => 'mette@example.com']);

    $this->actingAs($user);

    $response = Livewire::test('pages::member-profile')
        ->set('firstname', 'Mette')
        ->set('lastname', 'Nielsen')
        ->set('email', 'mette.member@example.com')
        ->set('phone', '33333333')
        ->set('address', 'Klubvej 3')
        ->set('postal_code', '5000')
        ->set('city', 'Odense')
        ->set('birthdate', '1995-06-07')
        ->set('gender', 'female')
        ->call('save');

    $response->assertHasNoErrors();

    $member = $user->members()->first();

    $this->assertModelExists($member);

    expect($member)
        ->firstname->toBe('Mette')
        ->lastname->toBe('Nielsen')
        ->email->toBe('mette.member@example.com')
        ->user_id->toBe($user->id);
});

test('user can switch between their linked members in the profile form', function () {
    $user = User::factory()->create();

    $firstMember = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Anna',
        'lastname' => 'Først',
        'email' => 'anna@example.com',
    ]);

    $secondMember = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Bo',
        'lastname' => 'Anden',
        'email' => 'bo@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::member-profile')
        ->set('selected_member_id', (string) $secondMember->id)
        ->assertSet('firstname', 'Bo')
        ->assertSet('lastname', 'Anden')
        ->assertSet('email', 'bo@example.com');
});

test('user can create a new member even when they already have members', function () {
    $user = User::factory()->create();

    Member::create([
        'user_id' => $user->id,
        'firstname' => 'Eksisterende',
        'lastname' => 'Medlem',
        'email' => 'eksisterende@example.com',
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::member-profile')
        ->call('createNewMember')
        ->set('firstname', 'Nyt')
        ->set('lastname', 'Medlem')
        ->set('email', 'nyt.medlem@example.com')
        ->call('save');

    $response
        ->assertHasNoErrors()
        ->assertSet('canBookTrialTraining', true);

    expect($user->members()->count())->toBe(2);
    expect($user->members()->where('email', 'nyt.medlem@example.com')->exists())->toBeTrue();
});

test('member can be deleted from edit mode', function () {
    $user = User::factory()->create();

    $memberToKeep = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Anna',
        'lastname' => 'Behold',
        'email' => 'anna.keep@example.com',
    ]);

    $memberToDelete = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Bo',
        'lastname' => 'Slet',
        'email' => 'bo.delete@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::member-profile')
        ->set('selected_member_id', (string) $memberToDelete->id)
        ->call('startEditing')
        ->call('deleteSelectedMember')
        ->assertHasNoErrors()
        ->assertSet('selected_member_id', (string) $memberToKeep->id);

    $this->assertSoftDeleted('members', [
        'id' => $memberToDelete->id,
    ]);
});

test('member profile image can be uploaded', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Kim',
        'lastname' => 'Larsen',
        'email' => 'kim@example.com',
    ]);

    $this->actingAs($user);

    $file = UploadedFile::fake()->create('profil.jpg', 120, 'image/jpeg');

    $response = Livewire::test('pages::member-profile')
        ->set('profile_photo', $file)
        ->call('save');

    $response->assertHasNoErrors();

    $member->refresh();

    expect($member->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($member->profile_photo_path);
});