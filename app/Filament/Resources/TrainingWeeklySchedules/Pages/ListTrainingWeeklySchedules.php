<?php

namespace App\Filament\Resources\TrainingWeeklySchedules\Pages;

use App\Filament\Resources\TrainingWeeklySchedules\TrainingWeeklyScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingWeeklySchedules extends ListRecords
{
    protected static string $resource = TrainingWeeklyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
