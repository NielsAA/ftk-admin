<?php

namespace App\Filament\Resources\ClosedDays\Pages;

use App\Filament\Resources\ClosedDays\ClosedDayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClosedDays extends ListRecords
{
    protected static string $resource = ClosedDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
