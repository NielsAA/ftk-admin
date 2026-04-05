<?php

namespace App\Filament\Resources\MemberTeamFunctions\Pages;

use App\Filament\Resources\MemberTeamFunctions\MemberTeamFunctionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberTeamFunctions extends ListRecords
{
    protected static string $resource = MemberTeamFunctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
