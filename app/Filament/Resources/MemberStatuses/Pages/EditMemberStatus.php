<?php

namespace App\Filament\Resources\MemberStatuses\Pages;

use App\Filament\Resources\MemberStatuses\MemberStatusResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class EditMemberStatus extends EditRecordRedirectToIndex
{
    protected static string $resource = MemberStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
