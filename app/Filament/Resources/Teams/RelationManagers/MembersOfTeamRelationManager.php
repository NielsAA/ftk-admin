<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Filament\Resources\Members\MemberResource;
use App\Models\MemberTeamFunction;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MembersOfTeamRelationManager extends RelationManager
{
    protected static string $relationship = 'membersOfTeam';

    protected static ?string $title = 'Medlemmer';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->whereNull('left_at')
                ->with('member.memberStatus'))
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('member.firstname')
                    ->label('Fornavn')
                    ->searchable()
                    ->url(fn ($record): ?string => $record->member
                        ? MemberResource::getUrl('edit', ['record' => $record->member])
                        : null),
                TextColumn::make('member.lastname')
                    ->label('Efternavn')
                    ->searchable()
                    ->url(fn ($record): ?string => $record->member
                        ? MemberResource::getUrl('edit', ['record' => $record->member])
                        : null),
                SelectColumn::make('member_team_function_id')
                    ->label('Funktion')
                    ->options(fn (): array => MemberTeamFunction::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                TextColumn::make('joined_at')
                    ->label('Tilmeldt')
                    ->date()
                    ->sortable(),
                TextColumn::make('left_at')
                    ->label('Udmeldt')
                    ->date()
                    ->sortable(),
                TextColumn::make('member.memberStatus.name')
                    ->label('Status')
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Ingen status')
                    ->badge()
                    ->color(fn ($record): string => data_get($record, 'member.memberStatus.is_warning') ? 'danger' : 'gray'),
            ])
            ->defaultSort('joined_at', 'desc')
            ->headerActions([])
            ->recordActions([
                Action::make('leaveTeam')
                    ->label('Udmeld')
                    ->icon('heroicon-o-user-minus')
                    ->button()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->left_at === null)
                    ->action(function ($record): void {
                        $record->update([
                            'left_at' => now(),
                        ]);
                    }),
            ])
            ->toolbarActions([]);
    }
}
