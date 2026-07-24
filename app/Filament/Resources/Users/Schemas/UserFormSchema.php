<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255),
                Select::make('organization_id')
                    ->label('Szervezet')
                    ->relationship('organization', 'name'),
                Select::make('roles')
                    ->label('Szerepkörök')
                    ->multiple()
                    ->preload()
                    ->relationship('roles', 'name'),
                DateTimePicker::make('email_verified_at')
                    ->label('E-mail megerősítve'),
                TextInput::make('password')
                    ->label('Jelszó')
                    ->revealable()
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Toggle::make('maintenance_reminders_enabled')
                    ->label('Karbantartás emlékeztető küldése')
                    ->default(true),
            ]);
    }
}
