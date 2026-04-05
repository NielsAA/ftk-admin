<?php

namespace App\Filament\Resources\ClosedDays\Pages;

use App\Filament\Resources\ClosedDays\ClosedDayResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class EditClosedDay extends EditRecordRedirectToIndex
{
    protected static string $resource = ClosedDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
