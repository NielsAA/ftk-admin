<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\MemberImportTemplateController;
use App\Http\Controllers\TeamCheckoutSuccessController;
use App\Http\Controllers\TeamUnenrollController;
use App\Http\Controllers\TrainingSchedulePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class)->name('home');
Route::get('/ugeskema', TrainingSchedulePageController::class)->name('training.schedule');
Route::view('/tjek-ind', 'check-in')->name('member.check-in');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('dashboard/profil', 'pages::member-profile')->name('member.profile.edit');

    Route::livewire('dashboard/hold-tilmelding', 'pages::member-team-signup')->name('member.teams.signup');

    Route::livewire('dashboard/tilmeldingsoversigt', 'pages::training-enrollment-overview')->name('member.training.enrollment.overview');

    Route::livewire('dashboard/traeningshistorik', 'pages::member-check-in-history')->name('member.check-in.history');

    Route::post('dashboard/hold-tilmelding/{team}/checkout', CheckoutController::class)->name('member.teams.checkout');
    Route::post('dashboard/hold-tilmelding/{team}/afmeld', [TeamUnenrollController::class, 'unenroll'])->name('member.teams.unenroll');

    Route::get('dashboard/hold-tilmelding/success', TeamCheckoutSuccessController::class)->name('member.teams.checkout.success');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard/members/import-template', MemberImportTemplateController::class)
        ->name('members.import.template');
});

require __DIR__.'/settings.php';
