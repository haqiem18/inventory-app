<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\StockMutation;
use App\Models\Hutang; // Sesuaikan dengan model Hutang/Piutang Anda
use App\Models\Piutang;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Hitung data atau ambil dari model
        return [
            Stat::make('Total Hutang Berjalan', 'Rp 16.050.000')
                ->description('Sisa pembayaran ke supplier')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7, 4, 6, 5, 8, 3, 5]), // Sparkline mini chart bawaan

            Stat::make('Total Piutang Berjalan', 'Rp 1.090.000')
                ->description('Tagihan customer belum lunas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning')
                ->chart([2, 5, 4, 7, 6, 9, 8]),

            Stat::make('Total Aset Persediaan', 'Rp 31.415.000')
                ->description('Nilai modal stok aktif (FIFO Batch)')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success')
                ->chart([5, 6, 7, 8, 9, 10, 12]),
        ];
    }
}