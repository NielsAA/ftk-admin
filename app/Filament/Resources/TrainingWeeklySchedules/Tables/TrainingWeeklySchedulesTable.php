<?php

namespace App\Filament\Resources\TrainingWeeklySchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingWeeklySchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trainingSession.name')
                    ->label('Traeningssession')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('day_of_week')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monday' => 'Mandag',
                        'tuesday' => 'Tirsdag',
                        'wednesday' => 'Onsdag',
                        'thursday' => 'Torsdag',
                        'friday' => 'Fredag',
                        'saturday' => 'Loerdag',
                        'sunday' => 'Soendag',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('start_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
