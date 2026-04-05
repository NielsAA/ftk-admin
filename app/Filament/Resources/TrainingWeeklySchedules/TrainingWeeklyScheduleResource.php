<?php

namespace App\Filament\Resources\TrainingWeeklySchedules;

use App\Filament\Resources\TrainingWeeklySchedules\Pages\CreateTrainingWeeklySchedule;
use App\Filament\Resources\TrainingWeeklySchedules\Pages\EditTrainingWeeklySchedule;
use App\Filament\Resources\TrainingWeeklySchedules\Pages\ListTrainingWeeklySchedules;
use App\Filament\Resources\TrainingWeeklySchedules\Schemas\TrainingWeeklyScheduleForm;
use App\Filament\Resources\TrainingWeeklySchedules\Tables\TrainingWeeklySchedulesTable;
use App\Models\TrainingWeeklySchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingWeeklyScheduleResource extends Resource
{
    protected static ?string $model = TrainingWeeklySchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TrainingWeeklyScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingWeeklySchedulesTable::configure($table);
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
            'index' => ListTrainingWeeklySchedules::route('/'),
            'create' => CreateTrainingWeeklySchedule::route('/create'),
            'edit' => EditTrainingWeeklySchedule::route('/{record}/edit'),
        ];
    }
}
