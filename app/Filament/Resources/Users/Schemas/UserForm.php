<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->hiddenOn('edit'),
                Select::make('role')
                    ->options(collect(UserRole::cases())
                        ->mapWithKeys(fn (UserRole $role): array => [$role->value => ucfirst($role->value)])
                        ->all())
                    ->default(UserRole::User->value)
                    ->required(),
            ]);
    }
}
