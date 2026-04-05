<?php

namespace App\Filament\Resources\Teams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Hold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('number')
                    ->label('Nummer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('active_members_count')
                    ->label('Medlemmer')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Pris')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' DKK')
                    ->sortable(),
                TextColumn::make('price_type')
                    ->label('Pristype')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'monthly' => 'Maanedlig',
                        'yearly' => 'Aarlig',
                        default => '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'monthly' => 'info',
                        'yearly' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('description')
                    ->label('Beskrivelse')
                    ->limit(50)
                    ->toggleable(),
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
                TrashedFilter::make(),
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
