<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Holdoplysninger')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('number')
                            ->required(),
                        Select::make('trainingSessions')
                            ->label('Traeningssessioner')
                            ->relationship('trainingSessions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        
                        TextInput::make('price')
                            ->label('Pris')
                            ->numeric()
                            ->prefix('DKK')
                            ->step('0.01'),
                        Select::make('price_type')
                            ->label('Pris type')
                            ->options([
                                'monthly' => 'Maanedlig',
                                'yearly' => 'Aarlig',
                            ])
                            ->default('monthly'),
                        Textarea::make('description')
                            ->default(null)
                            ->columnSpanFull(),
                        FileUpload::make('photo_path')
                            ->label('Holdbillede')
                            ->image()
                            ->disk('public')
                            ->directory('teams/profile-images')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Stripe')
                    ->schema([
                        TextInput::make('stripe_price_id')
                            ->label('Stripe Price ID')
                            ->placeholder('price_...')
                            ->maxLength(255),
                        TextInput::make('stripe_product_id')
                            ->label('Stripe Product ID')
                            ->placeholder('prod_...')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
