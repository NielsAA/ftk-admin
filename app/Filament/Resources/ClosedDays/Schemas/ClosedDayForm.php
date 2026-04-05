<?php

namespace App\Filament\Resources\ClosedDays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use App\Models\TrainingWeeklySchedule;
use Carbon\Carbon;

class ClosedDayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('training_weekly_schedule_id', null)),
                Select::make('training_weekly_schedule_id')
                    ->label('Traeningssession (ugedag)')
                    ->required()
                    ->disabled(fn (Get $get): bool => blank($get('date')))
                    ->placeholder('Vaelg dato foerst')
                    ->options(function (Get $get): array {
                        $date = $get('date');

                        if (blank($date)) {
                            return [];
                        }

                        $dayOfWeek = strtolower(Carbon::parse($date)->englishDayOfWeek);

                        return TrainingWeeklySchedule::query()
                            ->with('trainingSession')
                            ->where('day_of_week', $dayOfWeek)
                            ->orderBy('start_time')
                            ->get()
                            ->mapWithKeys(fn (TrainingWeeklySchedule $schedule): array => [
                                $schedule->id => sprintf(
                                    '%s (%s-%s)',
                                    $schedule->trainingSession?->name ?? 'Ukendt',
                                    substr((string) $schedule->start_time, 0, 5),
                                    substr((string) $schedule->end_time, 0, 5),
                                ),
                            ])
                            ->all();
                    })
                    ->searchable(),
                TextInput::make('reason')
                    ->default(null),
            ]);
    }
}
