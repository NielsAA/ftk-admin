<?php

namespace App\Filament\Resources\EkstraTraings\Pages;

use App\Filament\Resources\EkstraTraings\EkstraTraingResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class EditEkstraTraing extends EditRecordRedirectToIndex
{
    protected static string $resource = EkstraTraingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
