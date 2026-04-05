<?php

namespace App\Filament\Resources\TrainingWeeklySchedules\Pages;

use App\Filament\Resources\TrainingWeeklySchedules\TrainingWeeklyScheduleResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateTrainingWeeklySchedule extends CreateRecordRedirectToIndex
{
    protected static string $resource = TrainingWeeklyScheduleResource::class;
}
