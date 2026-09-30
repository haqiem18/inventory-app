<?php

namespace App\Filament\Exports;

use App\Models\StockMutation;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StockMutationExporter extends Exporter
{
    protected static ?string $model = StockMutation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('reference_number')->label('No. Referensi'),
            ExportColumn::make('mutation_date')->label('Tanggal'),
            ExportColumn::make('branch.name')->label('Cabang'),
            ExportColumn::make('product.name')->label('Barang'),
            ExportColumn::make('product.sku')->label('SKU'),
            ExportColumn::make('supplier.name')->label('Supplier'),
            ExportColumn::make('quantity')->label('Qty'),
            ExportColumn::make('price')->label('Harga Satuan'),
            ExportColumn::make('subtotal')->label('Total Harga'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock mutation export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
