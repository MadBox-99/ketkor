<?php

declare(strict_types=1);

namespace App\Filament\Resources\AccessTokens;

use App\Filament\Resources\AccessTokens\Pages\CreateAccessToken;
use App\Filament\Resources\AccessTokens\Pages\EditAccessToken;
use App\Filament\Resources\AccessTokens\Pages\ListAccessTokens;
use App\Filament\Resources\AccessTokens\Schemas\AccessTokenFormSchema;
use App\Filament\Resources\AccessTokens\Tables\AccessTokenTable;
use App\Models\AccessToken;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AccessTokenResource extends Resource
{
    protected static ?string $model = AccessToken::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return AccessTokenFormSchema::make($schema);
    }

    public static function table(Table $table): Table
    {
        return AccessTokenTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccessTokens::route('/'),
            'create' => CreateAccessToken::route('/create'),
            'edit' => EditAccessToken::route('/{record}/edit'),
        ];
    }
}
