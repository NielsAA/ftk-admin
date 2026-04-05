<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['name' => 'MMA Basis', 'description' => null, 'color' => '#e09e9e'],
            ['name' => 'MMA Kamphold', 'description' => null, 'color' => '#4ae31c'],
            ['name' => 'Grapling', 'description' => null, 'color' => '#b097cc'],
            ['name' => 'Fristils brydning', 'description' => null, 'color' => '#e6da3d'],
            ['name' => 'Nina Kids', 'description' => null, 'color' => '#9991f0'],
            ['name' => 'Brydning, puslinge', 'description' => null, 'color' => '#53e6d4'],
            ['name' => 'Brydning, drenge/ungdom', 'description' => null, 'color' => '#e88ce2'],
            ['name' => 'Open Mat', 'description' => null, 'color' => '#28d1c7'],
        ])->each(function (array $trainingSession): void {
            DB::table('training_sessions')->updateOrInsert(
                ['name' => $trainingSession['name']],
                [
                    'description' => $trainingSession['description'],
                    'color' => $trainingSession['color'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        });
    }
}