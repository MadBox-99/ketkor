<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('owner_name')
                ->rules(['max:200']),
            ImportColumn::make('installer_name')
                ->rules(['max:200']),
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('city')
                ->rules(['max:200']),
            ImportColumn::make('street')
                ->rules(['max:200']),
            ImportColumn::make('zip')
                ->rules(['max:4']),
            ImportColumn::make('purchase_place')
                ->rules(['max:200']),
            ImportColumn::make('serial_number')
                ->requiredMapping()
                ->rules(['required', 'max:200']),
            ImportColumn::make('comments')
                ->rules(['max:500']),
            ImportColumn::make('installation_date')
                ->rules(['date']),
            ImportColumn::make('warrantee_date')
                ->rules(['date']),
            ImportColumn::make('purchase_date')
                ->rules(['date']),
            ImportColumn::make('tool')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): ?Product
    {

        return new Product($this->data);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
