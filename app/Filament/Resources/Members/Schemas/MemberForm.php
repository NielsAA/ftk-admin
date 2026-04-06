<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Models\MemberStatus;
use App\Models\Team;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Personlige oplysninger')
                    ->schema([

                        TextInput::make('firstname')
                            ->label('Fornavn')
                            ->required(),
                        TextInput::make('lastname')
                            ->label('Efternavn')
                            ->required(),
                        TextInput::make('address')
                            ->default(null)
                            ->columnSpanFull(),
                        TextInput::make('postal_code')
                            ->default(null),
                        TextInput::make('city')
                            ->default(null),
                        DatePicker::make('birthdate'),
                        Select::make('gender')
                            ->options(['male' => 'Male', 'female' => 'Female'])
                            ->default(null),

                    ])
                    ->columns(2),

                FileUpload::make('profile_photo_path')
                    ->label('Profilbillede')
                    ->image()
                    ->disk('public')
                    ->directory('members/profile-images')
                    ->visibility('public'),

                Section::make('Medlemsoplysninger')
                    ->schema([
                        Select::make('user_id')
                            ->label('Bruger')
                            ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->default(null),
                        Select::make('member_status_id')
                            ->label('Status')
                            ->options(fn (): array => MemberStatus::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->default(null),
                        Select::make('team_ids')
                            ->label('Hold')
                            ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->default([]),
                    ])
                    ->columns(1),

                Section::make('Kontaktinformation')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->tel()
                            ->default(null),

                    ])->columns(1),

                Section::make('Betalingsoplysninger')
                    ->collapsible()
                    ->collapsed()
                    ->schema([

                        TextInput::make('stripe_id')
                            ->default(null)
                            ->disabled(),
                        TextInput::make('pm_type')
                            ->default(null)
                            ->disabled(),
                        TextInput::make('pm_last_four')
                            ->default(null)
                            ->disabled(),
                        DateTimePicker::make('trial_ends_at')
                            ->disabled(),

                    ])->columns(1),
            ])->columns(2);
    }
}
