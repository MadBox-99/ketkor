<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('owner_name'),
            ExportColumn::make('installer_name'),
            ExportColumn::make('user_id'),
            ExportColumn::make('city'),
            ExportColumn::make('street'),
            ExportColumn::make('zip'),
            ExportColumn::make('purchase_place'),
            ExportColumn::make('serial_number'),
            ExportColumn::make('comments'),
            ExportColumn::make('installation_date'),
            ExportColumn::make('warrantee_date'),
            ExportColumn::make('purchase_date'),
            ExportColumn::make('tool.name'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
