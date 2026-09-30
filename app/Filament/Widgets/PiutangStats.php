<?php

namespace App\Filament\Widgets;

use App\Models\StockMutation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PiutangStats extends BaseWidget
{
    protected function getStats(): array
{
    // Mulai query dengan filter dasar
    $query = StockMutation::where('sub_type', 'penjualan')
        ->where('payment_status', 'hutang');

    // Terapkan filter cabang jika user bukan Pusat/Super Admin
    if (auth()->check()) {
        $user = auth()->user();
        $isAdminPusat = in_array($user->role, ['super_admin', 'pusat']);

        if (!$isAdminPusat) {
            $query->where('branch_id', $user->branch_id);
        }
    }

    $totalPiutang = $query->get()->sum(function ($record) {
        $tagihan = $record->price * $record->quantity;
        $terbayar = $record->debtPayments()->sum('amount_paid');
        
        return max(0, $tagihan - $terbayar); 
    });

    return [
        Stat::make('Total Piutang Berjalan', 'Rp ' . number_format($totalPiutang, 0, ',', '.'))
            ->description('Total tagihan customer yang belum lunas')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('danger'),
    ];
}
}