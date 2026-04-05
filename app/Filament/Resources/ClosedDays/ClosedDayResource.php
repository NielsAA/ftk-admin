<?php

namespace App\Filament\Resources\ClosedDays;

use App\Filament\Resources\ClosedDays\Pages\CreateClosedDay;
use App\Filament\Resources\ClosedDays\Pages\EditClosedDay;
use App\Filament\Resources\ClosedDays\Pages\ListClosedDays;
use App\Filament\Resources\ClosedDays\Schemas\ClosedDayForm;
use App\Filament\Resources\ClosedDays\Tables\ClosedDaysTable;
use App\Models\ClosedDay;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClosedDayResource extends Resource
{
    protected static ?string $model = ClosedDay::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ClosedDayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClosedDaysTable::configure($table);
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
            'index' => ListClosedDays::route('/'),
            'create' => CreateClosedDay::route('/create'),
            'edit' => EditClosedDay::route('/{record}/edit'),
        ];
    }
}
