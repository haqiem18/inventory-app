<?php

namespace App\Filament\Widgets;

use App\Models\StockBatch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TotalAssetWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Hitung total aset dari semua batch yang sisa stoknya masih ada (> 0)
        $totalAsset = StockBatch::where('quantity_remaining', '>', 0)
            ->sum(DB::raw('quantity_remaining * purchase_price'));

        return [
            Stat::make('Total Aset Persediaan', 'Rp ' . number_format($totalAsset, 0, ',', '.'))
                ->description('Total nilai modal stok aktif (FIFO Batch) 📦')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->role === 'super_admin';
    }
}