<?php

namespace Database\Seeders;

use App\Models\MemberStatus;
use Illuminate\Database\Seeder;

class MemberStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MemberStatus::query()->updateOrCreate([
            'name' => 'i restance',
        ], [
            'is_warning' => true,
        ]);
    }
}
