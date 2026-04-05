<?php

namespace App\Filament\Resources\MemberStatuses\Pages;

use App\Filament\Resources\MemberStatuses\MemberStatusResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateMemberStatus extends CreateRecordRedirectToIndex
{
    protected static string $resource = MemberStatusResource::class;
}
