<?php

namespace App\Filament\Imports;

use App\Enums\ProductCategory;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;

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
                ->relationship('tool', 'name'),
        ];
    }

    public function resolveRecord(): ?Product
    {

        $this->data['tool'] = $this->options['selectedBrand'];
        if ($this->options['updateExisting'] ?? false) {
            return Product::firstOrNew([
                'serial_number' => $this->data['serial_number'],
            ]);
        }

        return new Product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('selectedBrand')
                ->options(ProductCategory::class)
                ->default(ProductCategory::FERROLI)
                ->required()
                ->label('Select Product Brand'),
            Checkbox::make('updateExisting')
                ->label('Update existing records'),
        ];
    }
}
