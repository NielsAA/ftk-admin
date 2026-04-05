<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class EditRecordRedirectToIndex extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
