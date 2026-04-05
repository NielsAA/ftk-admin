<?php

namespace App\Filament\Resources\TrainingSessions\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TrainingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('number_of_trials')
                    ->label('Antal prøvetræninger')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                ColorPicker::make('color')
                    ->label('Farve')
                    ->default('#ef4444'),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
