<?php

namespace App\Filament\Imports;

use App\Models\Organization;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Hash;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('organization')
                ->relationship(),

        ];
    }

    public function resolveRecord(): ?User
    {
        $organization = Organization::firstOrCreate(['name' => $this->data['organization'] ?? null]);

        return User::firstOrNew([
            'email' => $this->data['email'],
            'organization_id' => $organization->id,
            'name' => $this->data['name'] ?? null,
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
        ]);

        /*  return new User; */
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
