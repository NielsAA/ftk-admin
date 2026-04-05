<?php

namespace App\Filament\Resources\TrainingSessions;

use App\Filament\Resources\TrainingSessions\Pages\CreateTrainingSession;
use App\Filament\Resources\TrainingSessions\Pages\EditTrainingSession;
use App\Filament\Resources\TrainingSessions\Pages\ListTrainingSessions;
use App\Filament\Resources\TrainingSessions\Schemas\TrainingSessionForm;
use App\Filament\Resources\TrainingSessions\Tables\TrainingSessionsTable;
use App\Models\TrainingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingSessionResource extends Resource
{
    protected static ?string $model = TrainingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TrainingSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingSessionsTable::configure($table);
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
            'index' => ListTrainingSessions::route('/'),
            'create' => CreateTrainingSession::route('/create'),
            'edit' => EditTrainingSession::route('/{record}/edit'),
        ];
    }
}
