<?php

namespace App\Filament\Widgets;

use App\Models\StockMutation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HutangStats extends BaseWidget
{
    // Tambahkan method ini untuk mengatur hak akses
    public static function canView(): bool
    {
        // Hanya tampil jika user adalah 'pusat' atau 'super_admin'
        $user = auth()->user();
        return in_array($user->role, ['super_admin', 'pusat']);
    }

    protected function getStats(): array
    {
        $totalHutang = \App\Models\StockMutation::query()
            ->where('type', 'Masuk')
            ->where('payment_status', '!=', 'lunas')
            ->get()
            ->sum(function ($mutation) {
                $tagihan = $mutation->purchase_price * $mutation->quantity;
                $terbayar = $mutation->debtPayments()->sum('amount_paid');
                return max(0, $tagihan - $terbayar);
            });

        return [
            Stat::make('Total Hutang Berjalan', 'Rp ' . number_format($totalHutang, 0, ',', '.'))
                ->description('Total sisa pembayaran ke supplier')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}