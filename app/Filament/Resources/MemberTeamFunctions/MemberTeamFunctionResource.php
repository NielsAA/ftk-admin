<?php

namespace App\Filament\Resources\MemberTeamFunctions;

use App\Filament\Resources\MemberTeamFunctions\Pages\CreateMemberTeamFunction;
use App\Filament\Resources\MemberTeamFunctions\Pages\EditMemberTeamFunction;
use App\Filament\Resources\MemberTeamFunctions\Pages\ListMemberTeamFunctions;
use App\Filament\Resources\MemberTeamFunctions\Schemas\MemberTeamFunctionForm;
use App\Filament\Resources\MemberTeamFunctions\Tables\MemberTeamFunctionsTable;
use App\Models\MemberTeamFunction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemberTeamFunctionResource extends Resource
{
    protected static ?string $model = MemberTeamFunction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MemberTeamFunctionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberTeamFunctionsTable::configure($table);
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
            'index' => ListMemberTeamFunctions::route('/'),
            'create' => CreateMemberTeamFunction::route('/create'),
            'edit' => EditMemberTeamFunction::route('/{record}/edit'),
        ];
    }
}
