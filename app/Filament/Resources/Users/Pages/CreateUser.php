<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateUser extends CreateRecordRedirectToIndex
{
    protected static string $resource = UserResource::class;
}
