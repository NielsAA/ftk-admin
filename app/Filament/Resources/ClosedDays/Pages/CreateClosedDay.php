<?php

namespace App\Filament\Resources\ClosedDays\Pages;

use App\Filament\Resources\ClosedDays\ClosedDayResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateClosedDay extends CreateRecordRedirectToIndex
{
    protected static string $resource = ClosedDayResource::class;
}
