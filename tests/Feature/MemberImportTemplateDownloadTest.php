<?php

use App\Filament\Imports\MemberImporter;
use App\Models\User;

it('redirects guests from member import template download', function () {
    $response = $this->get(route('members.import.template'));

    $response->assertRedirect(route('login'));
});

it('forbids non-admin users from member import template download', function () {
    $coach = User::factory()->coach()->create();

    $this->actingAs($coach)
        ->get(route('members.import.template'))
        ->assertForbidden();
});

it('allows admin users to download member import template csv', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get(route('members.import.template'));

    $response->assertOk();
    $response->assertDownload('members-import-skabelon.csv');

    $content = $response->streamedContent();
    $expectedHeader = implode(';', MemberImporter::templateColumns());

    expect($content)->toStartWith("\xEF\xBB\xBF");
    expect($content)->toContain($expectedHeader);
});
