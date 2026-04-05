<?php

namespace App\Filament\Resources\EkstraTraings\Schemas;

use App\Models\TrainingSession;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class EkstraTraingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_session_id')
                    ->label('Traeningssession')
                    ->relationship('trainingSession', 'name', fn ($query) => $query->orderBy('name'))
                    ->getOptionLabelFromRecordUsing(fn (TrainingSession $record): string => $record->name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                DatePicker::make('date')
                    ->required(),
                TimePicker::make('start_time')
                    ->required(),
                TimePicker::make('end_time'),
                TextInput::make('description')
                    ->default(null),
            ]);
    }
}
