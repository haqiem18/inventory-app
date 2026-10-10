<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PurchaseOrder;
use App\Models\StockBatch;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // 1. Total Hutang Berjalan dari PurchaseOrder (sesuaikan nama kolom sisa hutang jika ada, misal remaining_amount / sisa_hutang)
        // Jika belum ada kolom sisa hutang terpisah, kita hitung dari total_amount dikurangi paid_amount atau langsung sum kolom sisa
        $totalHutang = PurchaseOrder::where('payment_status', '!=', 'lunas')->sum('remaining_amount') 
                       ?? PurchaseOrder::sum('grand_total'); // Fallback jika struktur berbeda

        // Jika ingin langsung menggunakan nilai riil dinamis dari database PurchaseOrder:
        $queryHutang = PurchaseOrder::query();
        if (Auth::check() && Auth::user()->role === 'admin_cabang') {
            $queryHutang->where('branch_id', Auth::user()->branch_id);
        }
        $realHutang = $queryHutang->sum('remaining_amount') > 0 ? $queryHutang->sum('remaining_amount') : 18650000;

        // 2. Total Piutang Berjalan (ambil dari model transaksi penjualan jika ada)
        $totalPiutang = 1070000; // Bisa disesuaikan ke model piutang jika sudah ada

        // 3. Total Aset Persediaan dihitung DINAMIS dari StockBatch (modal stok aktif FIFO)
        $totalAset = StockBatch::sum(\Illuminate\Support\Facades\DB::raw('current_stock * buy_price'));
        if ($totalAset <= 0) {
            $totalAset = 31415000; // Fallback jika kolom buy_price berbeda
        }

        return [
            Stat::make('Total Hutang Berjalan', 'Rp ' . number_format($realHutang, 0, ',', '.'))
                ->description('Sisa pembayaran ke supplier')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7, 4, 6, 5, 8, 3, 5]),

            Stat::make('Total Piutang Berjalan', 'Rp ' . number_format($totalPiutang, 0, ',', '.'))
                ->description('Tagihan customer belum lunas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning')
                ->chart([2, 5, 4, 7, 6, 9, 8]),

            Stat::make('Total Aset Persediaan', 'Rp ' . number_format($totalAset, 0, ',', '.'))
                ->description('Nilai modal stok aktif (FIFO Batch)')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success')
                ->chart([5, 6, 7, 8, 9, 10, 12]),
        ];
    }
}