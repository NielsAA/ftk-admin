<?php

use App\Models\GeneralSetting;
use App\Models\Member;
use App\Models\MemberOfTeam;
use App\Models\MembersCheckIn;
use App\Models\MemberStatus;
use App\Models\MemberTeamFunction;
use App\Models\Team;
use App\Models\TeamAccessToTraining;
use App\Models\TrainingSession;
use App\Models\TrainingWeeklySchedule;
use App\Models\TrialSession;
use App\Models\User;
use Illuminate\Support\Carbon;

test('authenticated user can visit training enrollment overview page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('member.training.enrollment.overview'))
        ->assertOk()
        ->assertSee('CheckIn:')
        ->assertSee('Dato')
        ->assertSee('Træning');
});

test('training enrollment overview shows checked-in members as cards for selected training and date', function () {
    $user = User::factory()->create();

    $warningStatus = MemberStatus::query()->create([
        'name' => 'i restance',
        'is_warning' => true,
    ]);

    $member = Member::query()->create([
        'user_id' => $user->id,
        'member_status_id' => $warningStatus->id,
        'firstname' => 'Mette',
        'lastname' => 'Jensen',
        'email' => 'mette@example.com',
    ]);

    $trainingSession = TrainingSession::query()->create([
        'name' => 'No-Gi',
        'description' => null,
        'color' => '#112233',
    ]);

    $weeklySchedule = TrainingWeeklySchedule::query()->create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek),
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    MembersCheckIn::query()->create([
        'member_id' => $member->id,
        'training_weekly_schedule_id' => $weeklySchedule->id,
        'ekstra_traing_id' => null,
        'check_in_date' => Carbon::today()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('member.training.enrollment.overview'))
        ->assertOk()
        ->assertSee('Mette Jensen')
        ->assertSee('i restance');
});

test('card shows green for member enrolled via MemberOfTeam', function () {
    $user = User::factory()->create();
    $member = Member::query()->create(['user_id' => $user->id, 'firstname' => 'Lars', 'lastname' => 'Knudsen', 'email' => 'lars@example.com']);
    $trainingSession = TrainingSession::query()->create(['name' => 'BJJ', 'color' => '#000']);
    $team = Team::query()->create(['name' => 'Hold A', 'number' => '1']);
    TeamAccessToTraining::query()->create(['team_id' => $team->id, 'training_session_id' => $trainingSession->id]);
    $function = MemberTeamFunction::query()->create(['name' => 'Udøver']);
    MemberOfTeam::query()->create(['member_id' => $member->id, 'team_id' => $team->id, 'member_team_function_id' => $function->id, 'joined_at' => Carbon::today(), 'left_at' => null]);
    $weeklySchedule = TrainingWeeklySchedule::query()->create(['training_session_id' => $trainingSession->id, 'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek), 'start_time' => '18:00:00', 'end_time' => '19:00:00']);
    MembersCheckIn::query()->create(['member_id' => $member->id, 'training_weekly_schedule_id' => $weeklySchedule->id, 'check_in_date' => Carbon::today()->toDateString()]);

    $this->actingAs($user)
        ->get(route('member.training.enrollment.overview'))
        ->assertOk()
        ->assertSee('Lars Knudsen')
        ->assertDontSee('Ikke tilmeldt')
        ->assertDontSee('Prøvetime');
});

test('card shows yellow with Prøvetime for member with TrialSession', function () {
    $user = User::factory()->create();
    $member = Member::query()->create(['user_id' => $user->id, 'firstname' => 'Anna', 'lastname' => 'Hansen', 'email' => 'anna@example.com']);
    $trainingSession = TrainingSession::query()->create(['name' => 'BJJ', 'color' => '#000']);
    $weeklySchedule = TrainingWeeklySchedule::query()->create(['training_session_id' => $trainingSession->id, 'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek), 'start_time' => '18:00:00', 'end_time' => '19:00:00']);
    TrialSession::query()->create(['member_id' => $member->id, 'training_session_id' => $trainingSession->id, 'trial_date' => Carbon::today()->toDateString()]);
    MembersCheckIn::query()->create(['member_id' => $member->id, 'training_weekly_schedule_id' => $weeklySchedule->id, 'check_in_date' => Carbon::today()->toDateString()]);

    $this->actingAs($user)
        ->get(route('member.training.enrollment.overview'))
        ->assertOk()
        ->assertSee('Anna Hansen')
        ->assertSee('Prøvetime');
});

test('card shows red with Ikke tilmeldt for member not enrolled in any team', function () {
    $user = User::factory()->create();
    $member = Member::query()->create(['user_id' => $user->id, 'firstname' => 'Søren', 'lastname' => 'Berg', 'email' => 'soren@example.com']);
    $trainingSession = TrainingSession::query()->create(['name' => 'BJJ', 'color' => '#000']);
    $weeklySchedule = TrainingWeeklySchedule::query()->create(['training_session_id' => $trainingSession->id, 'day_of_week' => mb_strtolower(Carbon::today()->englishDayOfWeek), 'start_time' => '18:00:00', 'end_time' => '19:00:00']);
    MembersCheckIn::query()->create(['member_id' => $member->id, 'training_weekly_schedule_id' => $weeklySchedule->id, 'check_in_date' => Carbon::today()->toDateString()]);

    $this->actingAs($user)
        ->get(route('member.training.enrollment.overview'))
        ->assertOk()
        ->assertSee('Søren Berg')
        ->assertSee('Ikke tilmeldt');
});

test('training overview loads selected date and training from shared general settings', function () {
    $user = User::factory()->create();

    $trainingSession = TrainingSession::query()->create([
        'name' => 'No-Gi',
        'description' => null,
        'color' => '#112233',
    ]);

    $selectedDate = Carbon::today()->toDateString();

    $weeklySchedule = TrainingWeeklySchedule::query()->create([
        'training_session_id' => $trainingSession->id,
        'day_of_week' => mb_strtolower(Carbon::parse($selectedDate)->englishDayOfWeek),
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'description' => null,
    ]);

    GeneralSetting::query()->create([
        'key' => 'training_enrollment_overview_filters',
        'value' => [
            'selected_date' => $selectedDate,
            'selected_training_key' => 'weekly:'.$weeklySchedule->id,
        ],
    ]);

    $this->actingAs($user)
        ->get(route('member.training.enrollment.overview'))
        ->assertOk()
        ->assertSee('CheckIn: '.Carbon::parse($selectedDate)->format('d-m-Y'))
        ->assertSee('No-Gi (18:00-19:00) - Fast hold');
});
