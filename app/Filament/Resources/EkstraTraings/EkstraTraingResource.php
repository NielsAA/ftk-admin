<?php

namespace App\Filament\Resources\EkstraTraings;

use App\Filament\Resources\EkstraTraings\Pages\CreateEkstraTraing;
use App\Filament\Resources\EkstraTraings\Pages\EditEkstraTraing;
use App\Filament\Resources\EkstraTraings\Pages\ListEkstraTraings;
use App\Filament\Resources\EkstraTraings\Schemas\EkstraTraingForm;
use App\Filament\Resources\EkstraTraings\Tables\EkstraTraingsTable;
use App\Models\EkstraTraing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EkstraTraingResource extends Resource
{
    protected static ?string $model = EkstraTraing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EkstraTraingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EkstraTraingsTable::configure($table);
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
            'index' => ListEkstraTraings::route('/'),
            'create' => CreateEkstraTraing::route('/create'),
            'edit' => EditEkstraTraing::route('/{record}/edit'),
        ];
    }
}
