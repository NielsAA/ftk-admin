<?php

namespace App\Filament\Resources\EkstraTraings\Pages;

use App\Filament\Resources\EkstraTraings\EkstraTraingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEkstraTraings extends ListRecords
{
    protected static string $resource = EkstraTraingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
