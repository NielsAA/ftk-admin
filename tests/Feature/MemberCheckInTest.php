<?php

use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MembersCheckIn;
use App\Models\MemberStatus;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\EkstraTraing;
use App\Models\ClosedDay;
use App\Models\TrialSession;
use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

test('member check in page is displayed', function () {
    $this->get(route('member.check-in'))
        ->assertOk()
        ->assertSee('Tjek ind');
});

test('member can check in to today weekly training', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sanne',
        'lastname' => 'Nielsen',
        'email' => 'sanne@example.com',
    ]);

    $team = Team::create([
        'name' => 'MMA Hold',
        'number' => '1',
        'description' => null,
    ]);

    $memberTeamFunction = MemberTeamFunction::create([
        'name' => 'Udøver',
        'description' => null,
        'default_member_function' => 1,
    ]);

    MemberOfTeam::create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $memberTeamFunction->id,
        'joined_at' => now()->subDays(5)->toDateString(),
        'left_at' => null,
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'MMA Basis',
        'description' => null,
        'color' => '#123456',
    ]);

    $team->trainingSessions()->attach($trainingSession->id);

    $weeklySchedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    $this->actingAs($user);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Sanne')
        ->call('selectMember', $member->id)
        ->assertSet('showTrainingModal', true)
        ->assertSet('selectedMemberLabel', 'Sanne Nielsen')
        ->assertSet('memberSearch', 'Sanne Nielsen')
        ->call('checkIn', 'weekly:'.$weeklySchedule->id)
        ->assertSet('showTrainingModal', true)
        ->assertSet('checkInMessage', 'You are now checked in for today training.');

    $this->assertDatabaseHas('members_check_ins', [
        'member_id' => $member->id,
        'training_weekly_schedule_id' => $weeklySchedule->id,
        'check_in_date' => Carbon::today()->toDateString(),
    ]);
});

test('member can only be checked in once per weekly schedule per day', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Mads',
        'lastname' => 'Jensen',
        'email' => 'mads@example.com',
    ]);

    $team = Team::create([
        'name' => 'Brydning',
        'number' => '2',
        'description' => null,
    ]);

    $memberTeamFunction = MemberTeamFunction::create([
        'name' => 'Udøver',
        'description' => null,
        'default_member_function' => 1,
    ]);

    MemberOfTeam::create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $memberTeamFunction->id,
        'joined_at' => now()->subDays(10)->toDateString(),
        'left_at' => null,
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'Fristil',
        'description' => null,
        'color' => '#654321',
    ]);

    $team->trainingSessions()->attach($trainingSession->id);

    $weeklySchedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'description' => null,
    ]);

    $this->actingAs($user);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Mads')
        ->call('selectMember', $member->id)
        ->call('checkIn', 'weekly:'.$weeklySchedule->id)
        ->call('checkIn', 'weekly:'.$weeklySchedule->id);

    expect(MembersCheckIn::query()->count())->toBe(1);
});

test('check in widget shows members matching search', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    Member::create([
        'user_id' => $firstUser->id,
        'firstname' => 'Anna',
        'lastname' => 'Andersen',
        'email' => 'anna@example.com',
    ]);

    Member::create([
        'user_id' => $secondUser->id,
        'firstname' => 'Bo',
        'lastname' => 'Birk',
        'email' => 'bo@example.com',
    ]);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Ann')
        ->assertSet('filteredMembers.0.label', 'Anna Andersen')
        ->assertCount('filteredMembers', 1);
});

test('selecting member from search results opens training modal', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Anna',
        'lastname' => 'Andersen',
        'email' => 'anna@example.com',
        'profile_photo_path' => 'members/anna.jpg',
    ]);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Anna')
        ->call('selectMember', $member->id)
        ->assertSet('showTrainingModal', true)
        ->assertSet('selectedMemberLabel', 'Anna Andersen')
        ->assertSet('selectedMemberAvatarUrl', url('storage/members/anna.jpg'))
        ->assertSet('filteredMembers', []);
});

test('popup shows all todays trainings even when member is not enrolled', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Peter',
        'lastname' => 'Larsen',
        'email' => 'peter@example.com',
    ]);

    $weeklyTrainingSession = TrainingSession::create([
        'name' => 'Kickboxing',
        'description' => null,
        'color' => '#111111',
    ]);

    $extraTrainingSession = TrainingSession::create([
        'name' => 'Open Mat',
        'description' => null,
        'color' => '#222222',
    ]);

    TrainingWeeklySchedule::create([
        'training_session_id' => $weeklyTrainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    EkstraTraing::create([
        'training_session_id' => $extraTrainingSession->id,
        'date' => Carbon::today()->toDateString(),
        'start_time' => '20:00:00',
        'end_time' => '21:00:00',
        'description' => null,
    ]);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Peter')
        ->call('selectMember', $member->id)
        ->assertSet('showTrainingModal', true)
        ->assertCount('todayTrainings', 2)
        ->assertSee('Kickboxing (18:00-19:00)')
        ->assertSee('Open Mat (20:00-21:00) - ekstra')
        ->assertSee('Ekstra');
});

test('popup marks enrolled trainings and non-enrolled trainings', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Mikkel',
        'lastname' => 'Hansen',
        'email' => 'mikkel@example.com',
    ]);

    $team = Team::create([
        'name' => 'Junior Hold',
        'number' => '9',
        'description' => null,
    ]);

    $memberTeamFunction = MemberTeamFunction::create([
        'name' => 'Udøver',
        'description' => null,
        'default_member_function' => 1,
    ]);

    MemberOfTeam::create([
        'member_id' => $member->id,
        'team_id' => $team->id,
        'member_team_function_id' => $memberTeamFunction->id,
        'joined_at' => now()->subDays(2)->toDateString(),
        'left_at' => null,
    ]);

    $enrolledSession = TrainingSession::create([
        'name' => 'Boksning Intro',
        'description' => null,
        'color' => '#334455',
    ]);

    $notEnrolledSession = TrainingSession::create([
        'name' => 'Muay Thai Open',
        'description' => null,
        'color' => '#556677',
    ]);

    $team->trainingSessions()->attach($enrolledSession->id);

    TrainingWeeklySchedule::create([
        'training_session_id' => $enrolledSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'description' => null,
    ]);

    TrainingWeeklySchedule::create([
        'training_session_id' => $notEnrolledSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Mikkel')
        ->call('selectMember', $member->id)
        ->assertSet('showTrainingModal', true)
        ->assertSee('Boksning Intro (17:00-18:00)')
        ->assertSee('Muay Thai Open (18:00-19:00)')
        ->assertSee('Tilmeldt')
        ->assertSee('Ikke tilmeldt');
});

test('popup header is clearly marked for warning member status', function () {
    $warningStatus = MemberStatus::create([
        'name' => 'Kontingent i restance',
        'is_warning' => true,
    ]);

    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Jonas',
        'lastname' => 'Madsen',
        'email' => 'jonas@example.com',
    ]);

    $member->forceFill([
        'member_status_id' => $warningStatus->id,
    ])->save();

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Jonas')
        ->call('selectMember', $member->id)
        ->assertSet('showTrainingModal', true)
        ->assertSet('selectedMemberHasWarningStatus', true)
        ->assertSet('selectedMemberStatusName', 'Kontingent i restance')
        ->assertSee('Advarsel')
        ->assertSee('Kontingent i restance');
});

test('popup marks training already checked in today', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Sara',
        'lastname' => 'Kristensen',
        'email' => 'sara@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'No-Gi Basis',
        'description' => null,
        'color' => '#223344',
    ]);

    $weeklySchedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '19:00:00',
        'end_time' => '20:00:00',
        'description' => null,
    ]);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Sara')
        ->call('selectMember', $member->id)
        ->call('checkIn', 'weekly:'.$weeklySchedule->id)
        ->set('memberSearch', 'Sara')
        ->call('selectMember', $member->id)
        ->assertSee('No-Gi Basis (19:00-20:00)')
        ->assertSee('Tjekket ind i dag')
        ->assertSee('Tjek')
        ->assertSee('ud')
        ->call('checkOut', 'weekly:'.$weeklySchedule->id);

    $this->assertDatabaseMissing('members_check_ins', [
        'member_id' => $member->id,
        'training_weekly_schedule_id' => $weeklySchedule->id,
        'check_in_date' => Carbon::today()->toDateString(),
    ]);
});

test('closed weekly training is marked and cannot be checked in', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Lasse',
        'lastname' => 'Mikkelsen',
        'email' => 'lasse@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'Submission Wrestling',
        'description' => null,
        'color' => '#445566',
    ]);

    $weeklySchedule = TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '16:00:00',
        'end_time' => '17:00:00',
        'description' => null,
    ]);

    ClosedDay::create([
        'training_weekly_schedule_id' => $weeklySchedule->id,
        'date' => Carbon::today()->toDateString(),
        'reason' => 'Holiday',
    ]);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Lasse')
        ->call('selectMember', $member->id)
        ->assertSee('Submission Wrestling')
        ->assertSee('Lukket')
        ->call('checkIn', 'weekly:'.$weeklySchedule->id)
        ->assertSet('checkInMessage', 'This training is closed today.');

    $this->assertDatabaseMissing('members_check_ins', [
        'member_id' => $member->id,
        'training_weekly_schedule_id' => $weeklySchedule->id,
        'check_in_date' => Carbon::today()->toDateString(),
    ]);
});

test('trial training for today is marked and shown as enrolled', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'firstname' => 'Freja',
        'lastname' => 'Lund',
        'email' => 'freja@example.com',
    ]);

    $trainingSession = TrainingSession::create([
        'name' => 'Begynder Grappling',
        'description' => null,
        'color' => '#778899',
    ]);

    TrainingWeeklySchedule::create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '18:30:00',
        'end_time' => '19:30:00',
        'description' => null,
    ]);

    TrialSession::create([
        'training_session_id' => $trainingSession->id,
        'member_id' => $member->id,
        'trial_date' => Carbon::today()->toDateString(),
    ]);

    Livewire::test('member-check-in-widget')
        ->set('memberSearch', 'Freja')
        ->call('selectMember', $member->id)
        ->assertSee('Begynder Grappling')
        ->assertSee('Prøvetræning')
        ->assertSee('Tilmeldt');
});
