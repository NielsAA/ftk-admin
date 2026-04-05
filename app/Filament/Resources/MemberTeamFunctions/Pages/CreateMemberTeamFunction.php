<?php

namespace App\Filament\Resources\MemberTeamFunctions\Pages;

use App\Filament\Resources\MemberTeamFunctions\MemberTeamFunctionResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateMemberTeamFunction extends CreateRecordRedirectToIndex
{
    protected static string $resource = MemberTeamFunctionResource::class;
}
