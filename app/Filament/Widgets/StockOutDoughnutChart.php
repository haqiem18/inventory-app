<?php

namespace App\Filament\Widgets;

use App\Models\StockMutation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class StockOutDoughnutChart extends ChartWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): ?string
    {
        return 'Persentase Keperluan Barang Keluar';
    }

    protected function getData(): array
    {
        $query = function ($subType) {
            $q = StockMutation::where('type', 'OUT')->where('sub_type', $subType);
            if (Auth::check() && Auth::user()->role === 'admin_cabang') {
                $q->where('branch_id', Auth::user()->branch_id);
            }
            return $q->sum('quantity');
        };

        $produksi = $query('produksi');
        $penjualan = $query('penjualan');
        $retur = $query('retur');
        $mutasi = $query('mutasi');

        $total = $produksi + $penjualan + $retur + $mutasi;

        // Hitung persentase aman dari PHP
        $getPct = fn($val) => $total > 0 ? round(($val / $total) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Total Qty',
                    'data' => [$produksi, $penjualan, $retur, $mutasi],
                    'backgroundColor' => ['#ef4444', '#10b981', '#f59e0b', '#8b5cf6'],
                ],
            ],
            'labels' => [
                'Produksi (' . $getPct($produksi) . '%)',
                'Penjualan (' . $getPct($penjualan) . '%)',
                'Retur (' . $getPct($retur) . '%)',
                'Mutasi Cabang (' . $getPct($mutasi) . '%)',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}