<?php

namespace Database\Seeders;

use App\Models\MemberTeamFunction;
use Illuminate\Database\Seeder;

class MemberTeamFunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MemberTeamFunction::query()->updateOrCreate([
            'name' => 'member',
        ], [
            'description' => null,
            'default_member_function' => 1,
        ]);

        MemberTeamFunction::query()->updateOrCreate([
            'name' => 'coach',
        ], [
            'description' => null,
            'default_member_function' => 0,
        ]);

        MemberTeamFunction::query()->updateOrCreate([
            'name' => 'team leader',
        ], [
            'description' => null,
            'default_member_function' => 0,
        ]);
    }
}
