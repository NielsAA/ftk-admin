<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Imports\MemberImporter;
use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download CSV skabelon')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('members.import.template'))
                ->openUrlInNewTab(),
            ImportAction::make()
                ->label('Importer medlemmer')
                ->importer(MemberImporter::class)
                ->csvDelimiter(';'),
            CreateAction::make(),
        ];
    }
}
