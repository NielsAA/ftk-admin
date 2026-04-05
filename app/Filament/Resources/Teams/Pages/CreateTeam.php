<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateTeam extends CreateRecordRedirectToIndex
{
    protected static string $resource = TeamResource::class;
}
