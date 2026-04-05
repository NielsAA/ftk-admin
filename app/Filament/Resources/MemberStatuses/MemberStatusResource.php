<?php

namespace App\Filament\Resources\MemberStatuses;

use App\Filament\Resources\MemberStatuses\Pages\CreateMemberStatus;
use App\Filament\Resources\MemberStatuses\Pages\EditMemberStatus;
use App\Filament\Resources\MemberStatuses\Pages\ListMemberStatuses;
use App\Filament\Resources\MemberStatuses\Schemas\MemberStatusForm;
use App\Filament\Resources\MemberStatuses\Tables\MemberStatusesTable;
use App\Models\MemberStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemberStatusResource extends Resource
{
    protected static ?string $model = MemberStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MemberStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberStatusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberStatuses::route('/'),
            'create' => CreateMemberStatus::route('/create'),
            'edit' => EditMemberStatus::route('/{record}/edit'),
        ];
    }
}
