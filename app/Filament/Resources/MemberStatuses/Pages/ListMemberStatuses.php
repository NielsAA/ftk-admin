<?php

namespace App\Filament\Resources\MemberStatuses\Pages;

use App\Filament\Resources\MemberStatuses\MemberStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberStatuses extends ListRecords
{
    protected static string $resource = MemberStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
