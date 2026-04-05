<?php

namespace App\Filament\Resources\MemberTeamFunctions\Pages;

use App\Filament\Resources\MemberTeamFunctions\MemberTeamFunctionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class EditMemberTeamFunction extends EditRecordRedirectToIndex
{
    protected static string $resource = MemberTeamFunctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
