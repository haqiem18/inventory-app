<?php

namespace App\Filament\Widgets;

use App\Models\StockMutation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class StockOutDoughnutChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected function getHeading(): ?string
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

        return [
            'datasets' => [
                [
                    'label' => 'Total Qty',
                    'data' => [$query('produksi'), $query('penjualan'), $query('retur'), $query('mutasi')],
                    'backgroundColor' => ['#ef4444', '#10b981', '#f59e0b', '#8b5cf6'],
                ],
            ],
            'labels' => ['Produksi', 'Penjualan', 'Retur', 'Mutasi Cabang'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}