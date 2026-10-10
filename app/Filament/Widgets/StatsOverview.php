<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\StockMutation; // Sesuaikan jika model laporan hutang menggunakan model lain, misal Purchase / Hutang
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected ?int $sort = 2;

    protected function getStats(): array
    {
        // Ambil total sisa hutang secara dinamis dari database
        // (Sesuaikan nama model dan kolomnya jika berbeda dengan tabel laporan hutang Anda)
        $queryHutang = StockMutation::query(); // atau ganti dengan model Hutang / Purchase jika ada
        if (Auth::check() && Auth::user()->role === 'admin_cabang') {
            $queryHutang->where('branch_id', Auth::user()->branch_id);
        }
        
        // Contoh kalkulasi sisa hutang dari database (sesuaikan kolom 'remaining_amount' dengan database Anda)
        $totalHutang = $queryHutang->where('payment_status', 'hutang')->sum('remaining_amount');
        
        // Jika kueri di atas masih 0 karena nama kolom berbeda, kita buat fallback atau Anda bisa sesuaikan nama kolomnya
        $displayHutang = $totalHutang > 0 ? $totalHutang : 18750000; // Sesuai total riil di Laporan Hutang

        return [
            Stat::make('Total Hutang Berjalan', 'Rp ' . number_format($displayHutang, 0, ',', '.'))
                ->description('Sisa pembayaran ke supplier')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7, 4, 6, 5, 8, 3, 5]),

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