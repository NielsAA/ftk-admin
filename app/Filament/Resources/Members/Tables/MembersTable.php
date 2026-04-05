<?php

namespace App\Filament\Resources\Members\Tables;

use App\Actions\BulkEnrollMembersInTeamAction;
use App\Models\Member;
use App\Models\Team;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Navn')
                    ->state(fn ($record): string => trim("{$record->firstname} {$record->lastname}"))
                    ->searchable(['firstname', 'lastname'])
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy('firstname', $direction)
                        ->orderBy('lastname', $direction)),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Bruger')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('memberStatus.name')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teams')
                    ->label('Hold')
                    ->state(fn (Member $record): array => $record->memberOfTeams
                        ->whereNull('left_at')
                        ->map(fn ($memberOfTeam): ?string => $memberOfTeam->team?->name)
                        ->filter()
                        ->values()
                        ->all())
                    ->badge()
                    ->separator(',')
                    ->wrap(),
                
                TextColumn::make('birthdate')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stripe_id')
                    ->searchable()
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
                    BulkAction::make('enrollInTeam')
                        ->label('Tilmeld til hold')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Select::make('team_id')
                                ->label('Hold')
                                ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, array $data, BulkEnrollMembersInTeamAction $bulkEnrollMembersInTeamAction): void {
                            $team = Team::query()->findOrFail($data['team_id']);

                            $bulkEnrollMembersInTeamAction->execute($records, $team);
                        })
                        ->successNotificationTitle('Valgte medlemmer blev tilmeldt holdet'),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
