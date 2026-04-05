<?php

namespace App\Filament\Resources\TrainingWeeklySchedules\Pages;

use App\Filament\Resources\TrainingWeeklySchedules\TrainingWeeklyScheduleResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class EditTrainingWeeklySchedule extends EditRecordRedirectToIndex
{
    protected static string $resource = TrainingWeeklyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
