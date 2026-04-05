<?php

namespace App\Filament\Resources\MemberTeamFunctions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MemberTeamFunctionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Function Name')
                    ->required(),
                Textarea::make('description')
                    ->label('Function Description')
                    ->rows(3),
                Toggle::make('default_member_function')
                    ->label('Default Member Function')
                    ->required(),
            ]);
    }
}
