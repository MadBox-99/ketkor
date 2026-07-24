<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations;

use App\Filament\Resources\Organizations\Pages\CreateOrganization;
use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Resources\Organizations\Schemas\OrganizationFormSchema;
use App\Filament\Resources\Organizations\Tables\OrganizationTable;
use App\Models\Organization;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Ügyfelek';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Szervezetek';

    protected static ?string $modelLabel = 'Szervezet';

    protected static ?string $pluralModelLabel = 'Szervezetek';

    public static function getNavigationBadge(): string
    {
        return (string) Organization::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'info';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return OrganizationFormSchema::make($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return OrganizationTable::make($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListOrganizations::route('/'),
            'create' => CreateOrganization::route('/create'),
            'edit' => EditOrganization::route('/{record}/edit'),
        ];
    }
}
