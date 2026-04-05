<?php

namespace App\Filament\Resources\Members\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberOfTeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'memberOfTeams';

    protected static ?string $title = 'Medlemshistorik';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('team.name')
                    ->label('Hold')
                    ->searchable(),
                TextColumn::make('memberTeamFunction.name')
                    ->label('Funktion')
                    ->searchable(),
                TextColumn::make('joined_at')
                    ->label('Tilmeldt')
                    ->date()
                    ->sortable(),
                TextColumn::make('left_at')
                    ->label('Afmeldt')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn ($record): string => $record->left_at === null ? 'Aktiv' : 'Afmeldt')
                    ->badge(),
            ])
            ->defaultSort('joined_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
