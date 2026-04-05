<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MemberStatusSeeder::class,
            MemberTeamFunctionSeeder::class,
            TrainingSessionSeeder::class,
            TrainingWeeklyScheduleSeeder::class,
            TeamSeeder::class,
        ]);

        User::query()->updateOrCreate([
            'email' => 'admin@admin.dk',
        ], [
            'name' => 'admin',
            'password' => Hash::make('12345678'),
            'role' => UserRole::Admin,
        ]);

        User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
            'role' => UserRole::User,
        ]);
    }
}
