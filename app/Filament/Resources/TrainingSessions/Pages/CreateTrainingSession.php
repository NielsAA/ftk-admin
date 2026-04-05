<?php

namespace App\Filament\Resources\TrainingSessions\Pages;

use App\Filament\Resources\TrainingSessions\TrainingSessionResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateTrainingSession extends CreateRecordRedirectToIndex
{
    protected static string $resource = TrainingSessionResource::class;
}
