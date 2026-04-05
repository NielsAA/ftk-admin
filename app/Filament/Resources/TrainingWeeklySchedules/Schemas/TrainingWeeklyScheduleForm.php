<?php

namespace App\Filament\Resources\TrainingWeeklySchedules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class TrainingWeeklyScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_session_id')
                    ->label('Traeningssession')
                    ->relationship('trainingSession', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('day_of_week')
                    ->options([
                        'monday' => 'Mandag',
                        'tuesday' => 'Tirsdag',
                        'wednesday' => 'Onsdag',
                        'thursday' => 'Torsdag',
                        'friday' => 'Fredag',
                        'saturday' => 'Loerdag',
                        'sunday' => 'Soendag',
                    ])
                    ->required(),
                TimePicker::make('start_time')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->seconds(false)
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
