<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = collect([
            [
                'name' => 'MMA Kamphold',
                'number' => '05',
                'description' => 'Kamp hold får øvede',
                'photo_path' => 'teams/profile-images/01KNBSY2AKYTZS6NW0TSQXWPD3.jpg',
                'price' => '300.00',
                'price_type' => 'monthly',
                'stripe_price_id' => 'price_1TIDhKBudtIxlMDXBLamQI3i',
                'stripe_product_id' => 'prod_UGvMjcOV7Gyn4N',
                'training_sessions' => ['MMA Kamphold', 'Fristils brydning', 'Open Mat'],
            ],
            [
                'name' => 'MMA Basis',
                'number' => '06',
                'description' => 'MMA hold for begynder og motion',
                'photo_path' => 'teams/profile-images/01KNBSZ8VAVS2DHX733X0DYFEJ.jpg',
                'price' => '250.00',
                'price_type' => 'monthly',
                'stripe_price_id' => 'price_1TIULsBudtIxlMDXPwqGaWiR',
                'stripe_product_id' => 'prod_UGlD7TmO6yLxLC',
                'training_sessions' => ['Grapling', 'Fristils brydning', 'Open Mat'],
            ],
        ]);

        $teams->each(function (array $teamData): void {
            $trainingSessionNames = $teamData['training_sessions'];
            unset($teamData['training_sessions']);

            $team = Team::query()->updateOrCreate(
                ['name' => $teamData['name']],
                $teamData,
            );

            $trainingSessionIds = DB::table('training_sessions')
                ->whereIn('name', $trainingSessionNames)
                ->pluck('id')
                ->all();

            $team->trainingSessions()->sync($trainingSessionIds);
        });
    }
}